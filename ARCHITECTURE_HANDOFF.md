# Project Architecture Handoff Document
## POS DiBimbing - Clean Architecture + Domain-Driven Design

**Last Updated:** May 30, 2026  
**Architecture Pattern:** Clean Architecture + DDD (Domain-Driven Design)  
**Framework:** Laravel 11

---

## 📋 Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Layer Responsibilities](#layer-responsibilities)
3. [Directory Structure](#directory-structure)
4. [Request Flow](#request-flow)
5. [Core Concepts](#core-concepts)
6. [Adding New Features](#adding-new-features)
7. [Important Files & Conventions](#important-files--conventions)
8. [Troubleshooting Guide](#troubleshooting-guide)

---

## 🏗️ Architecture Overview

Proyek ini menggunakan **Clean Architecture** dengan prinsip **Domain-Driven Design (DDD)**. Tujuan utamanya adalah:

- **Separation of Concerns**: Business logic terpisah dari framework implementation
- **Testability**: Mudah untuk unit test setiap layer secara terpisah
- **Maintainability**: Kode terorganisir dan mudah dipahami
- **Scalability**: Mudah untuk menambah fitur baru

### Arsitektur Berlapis (Layered Architecture)

```
┌─────────────────────────────────────┐
│     Presentation Layer (HTTP)       │ <- Controllers, Requests, Resources
├─────────────────────────────────────┤
│     Application Layer (Use Cases)   │ <- Business Orchestration
├─────────────────────────────────────┤
│     Domain Layer (Business Logic)   │ <- Core Business Rules
├─────────────────────────────────────┤
│  Infrastructure Layer (External)    │ <- Database, Cache, External Services
└─────────────────────────────────────┘
```

**Alur Dependency:** Lapisan atas bergantung pada lapisan bawah, BUKAN sebaliknya.

---

## 📝 Layer Responsibilities

### 1️⃣ **Domain Layer** (`app/Domain/`)
**Tanggung Jawab:** Pure business logic, framework-agnostic

Folder struktur:
```
Domain/
├── Category/
│   └── Repositories/
│       └── CategoryRepositoryInterface.php
├── Inventory/
├── Payment/
├── Product/
│   └── Repositories/
│       └── ProductRepositoryInterface.php
├── User/
└── Common/
```

**Apa yang ada di layer ini:**
- **Repository Interfaces** (`*RepositoryInterface.php`): Kontrak untuk akses data
  ```php
  interface ProductRepositoryInterface {
      public function paginate(array $filters = []): LengthAwarePaginator;
      public function findById(int $id): ?Product;
      public function create(array $data): Product;
      public function update(Product $product, array $data): Product;
  }
  ```
- **Entities/Models bisnis** (jika ada): Representasi object bisnis
- **Value Objects**: Immutable objects yang merepresentasikan nilai (optional di project ini)

**Yang TIDAK ada di layer ini:**
- ❌ Eloquent Model
- ❌ Database queries
- ❌ HTTP requests/responses
- ❌ Framework-specific code

**Prinsip Penting:**
- Domain layer adalah **pure PHP code**, bisa di-copy ke project lain
- Tidak boleh ada dependency terhadap Laravel

---

### 2️⃣ **Application Layer** (`app/Application/`)
**Tanggung Jawab:** Orchestrate business logic, use case implementation

Folder struktur:
```
Application/
├── Product/
│   ├── UseCases/
│   │   ├── CreateProductUseCase.php
│   │   ├── UpdateProductUseCase.php
│   │   ├── ListProductsUseCase.php
│   │   ├── ShowProductUseCase.php
│   │   ├── SearchProductsForPosUseCase.php
│   │   └── DeactivateProductUseCase.php
│   └── DTOs/
│       ├── CreateProductDTO.php
│       └── UpdateProductDTO.php
├── Category/
├── Inventory/
├── Payment/
├── User/
├── Auth/
└── Common/
```

**Apa yang ada di layer ini:**

- **Use Cases** (`*UseCase.php`): Implementasi satu business action/flow
  ```php
  class CreateProductUseCase {
      public function __construct(
          private readonly ProductRepositoryInterface $repository
      ) {}
      
      public function execute(CreateProductDTO $dto): Product {
          return $this->repository->create($dto->toArray());
      }
  }
  ```
  
- **Data Transfer Objects (DTOs)**: Untuk transfer data antar layer
  ```php
  class CreateProductDTO {
      public function __construct(
          public readonly string $name,
          public readonly float $price,
          public readonly int $category_id,
      ) {}
  }
  ```

**Karakteristik:**
- Satu UseCase = Satu business action
- UseCase menerima DTO sebagai input
- UseCase menggunakan Repository untuk akses data
- UseCase return Eloquent Model atau custom object

**Yang TIDAK ada:**
- ❌ HTTP-specific code
- ❌ Direct database queries
- ❌ Framework middleware/decorators

---

### 3️⃣ **Presentation Layer** (`app/Http/`)
**Tanggung Jawab:** Handle HTTP requests dan format responses

```
Http/
├── Controllers/
│   ├── Api/
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   └── ...
│   └── Controller.php (base class)
├── Requests/
│   ├── Product/
│   │   ├── StoreProductRequest.php
│   │   └── UpdateProductRequest.php
│   └── ...
├── Resources/
│   ├── Product/
│   │   └── ProductResource.php
│   └── ...
└── Responses/
    └── ApiResponse.php
```

**Apa yang ada di layer ini:**

- **Controllers** (`ProductController.php`): HTTP entry point
  ```php
  class ProductController extends Controller {
      public function store(
          StoreProductRequest $request,
          CreateProductUseCase $useCase
      ): JsonResponse {
          $dto = CreateProductDTO::from($request->validated());
          $product = $useCase->execute($dto);
          return ApiResponse::created(
              data: new ProductResource($product)
          );
      }
  }
  ```

- **Form Requests** (`StoreProductRequest.php`): Validasi input
  ```php
  class StoreProductRequest extends FormRequest {
      public function rules(): array {
          return [
              'name' => 'required|string|max:255',
              'price' => 'required|numeric|min:0',
          ];
      }
  }
  ```

- **Resources** (`ProductResource.php`): Format response data
  ```php
  class ProductResource extends JsonResource {
      public function toArray($request): array {
          return [
              'id' => $this->id,
              'name' => $this->name,
              'price' => $this->price,
          ];
      }
  }
  ```

- **API Response Helper** (`ApiResponse.php`): Consistent response format

**Tanggung Jawab:**
- ✅ Receive HTTP request
- ✅ Validate input menggunakan FormRequest
- ✅ Convert request data to DTO
- ✅ Call appropriate UseCase
- ✅ Format response using Resource
- ✅ Return JSON response

---

### 4️⃣ **Infrastructure Layer** (`app/Infrastructure/`)
**Tanggung Jawab:** Technical implementation, external integrations

```
Infrastructure/
├── Persistence/
│   └── Eloquent/
│       ├── Models/
│       │   ├── Product.php
│       │   ├── Category.php
│       │   └── ...
│       └── Repositories/
│           ├── ProductRepository.php
│           └── CategoryRepository.php
└── ThirdParty/
    ├── Midtrans/
    └── ...
```

**Apa yang ada di layer ini:**

- **Eloquent Models** (`app/Models/Product.php`): Database representation
  - Hanya untuk database mapping
  - BUKAN entity bisnis

- **Repository Implementations** (`ProductRepository.php`): Concrete implementation dari Domain Repository Interface
  ```php
  class ProductRepository implements ProductRepositoryInterface {
      public function __construct(private readonly Product $model) {}
      
      public function findById(int $id): ?Product {
          return $this->model->find($id);
      }
      
      public function create(array $data): Product {
          return $this->model->create($data);
      }
  }
  ```

- **Third-party Integrations** (`Midtrans/`, dll)
- **Database Migrations** (`database/migrations/`)
- **Seeders & Factories** (`database/factories/`, `database/seeders/`)

**Karakteristik:**
- Mengimplementasikan interface dari Domain Layer
- Menggunakan Eloquent Model untuk database access
- Berisikan framework-specific code

---

## 📂 Directory Structure Details

```
pos-backend/
│
├── app/                          # Application code
│   ├── Domain/                   # 🔴 Domain Layer (Business Logic)
│   │   ├── Category/
│   │   ├── Inventory/
│   │   ├── Payment/
│   │   ├── Product/
│   │   │   └── Repositories/     # Repository Interfaces
│   │   ├── User/
│   │   └── Common/
│   │
│   ├── Application/              # 🟠 Application Layer (Use Cases)
│   │   ├── Auth/
│   │   ├── Category/
│   │   │   ├── UseCases/         # Business use cases
│   │   │   └── DTOs/             # Data transfer objects
│   │   ├── Inventory/
│   │   ├── Payment/
│   │   ├── Product/
│   │   ├── User/
│   │   └── Common/
│   │
│   ├── Http/                     # 🟡 Presentation Layer (HTTP)
│   │   ├── Controllers/
│   │   │   ├── Api/              # API controllers
│   │   │   └── Controller.php    # Base controller
│   │   ├── Requests/             # Form request validation
│   │   │   ├── Product/
│   │   │   └── Category/
│   │   ├── Resources/            # Response formatters
│   │   │   └── Product/
│   │   └── Responses/            # Response helpers
│   │
│   ├── Infrastructure/           # 🟢 Infrastructure Layer
│   │   ├── Persistence/
│   │   │   └── Eloquent/
│   │   │       ├── Models/       # Eloquent models
│   │   │       └── Repositories/ # Repository implementations
│   │   └── ThirdParty/           # External integrations
│   │
│   ├── Models/                   # Eloquent models (root)
│   ├── Providers/                # Service providers
│   └── Exceptions/               # Custom exceptions
│
├── bootstrap/                    # Laravel bootstrap
├── config/                       # Configuration files
├── database/
│   ├── migrations/               # Database migrations
│   ├── seeders/                  # Database seeders
│   └── factories/                # Model factories
├── resources/
│   ├── views/
│   ├── css/
│   └── js/
├── routes/
│   ├── api.php                   # API routes
│   ├── web.php                   # Web routes
│   └── console.php               # Console commands
├── storage/                      # Application storage
├── tests/                        # Unit & feature tests
├── vendor/                       # Composer dependencies
├── .env                          # Environment variables
├── artisan                       # Laravel CLI
├── composer.json                 # PHP dependencies
└── package.json                  # Node dependencies
```

---

## 🔄 Request Flow

Berikut adalah perjalanan request dari masuk hingga response keluar:

### Contoh: Membuat Product Baru

```
1. HTTP REQUEST
   └─> POST /api/products
       { "name": "Product A", "price": 50000, "category_id": 1 }

2. ROUTING (routes/api.php)
   └─> Route::post('/products', [ProductController::class, 'store'])

3. PRESENTATION LAYER
   └─> ProductController::store()
       ├─> StoreProductRequest validates input
       │   (rules: name required, price numeric, etc.)
       │
       └─> Controller converts request to DTO
           { CreateProductDTO($name, $price, $category_id) }

4. APPLICATION LAYER
   └─> CreateProductUseCase::execute(DTO)
       ├─> Orchestrate business logic
       └─> Call repository->create()

5. DOMAIN LAYER
   └─> ProductRepositoryInterface (contract)
       "I need to create a product"

6. INFRASTRUCTURE LAYER
   └─> ProductRepository (implementation)
       ├─> Use Eloquent Model
       ├─> Insert into database
       └─> Return Product instance

7. BACK UP THE STACK
   └─> Application layer gets Product
       └─> Presentation layer gets Product
           ├─> Format using ProductResource
           ├─> Wrap in ApiResponse
           └─> Return JSON

8. HTTP RESPONSE
   └─> 201 Created
       {
           "success": true,
           "data": {
               "id": 1,
               "name": "Product A",
               "price": 50000,
               "category_id": 1
           },
           "message": "Product created successfully"
       }
```

### Contoh: Mengambil Product

```
GET /api/products?page=1&per_page=10
    ↓
ProductController::index(ListProductsUseCase)
    ↓
ListProductsUseCase::execute(filters, perPage)
    ↓
ProductRepository::paginate(filters, perPage)
    ↓
Eloquent Query → Database
    ↓
Return Product Collection
    ↓
ProductResource::collection()
    ↓
ApiResponse::pagination()
    ↓
HTTP Response (200 OK)
```

---

## 💡 Core Concepts

### Dependency Injection

Semua dependency di-inject melalui constructor:

```php
class CreateProductUseCase {
    public function __construct(
        private readonly ProductRepositoryInterface $repository
    ) {}
}

// Di controller:
public function store(
    StoreProductRequest $request,
    CreateProductUseCase $useCase  // <- Laravel auto-inject
): JsonResponse {}
```

**Mengapa?** Mudah untuk testing, loose coupling, dan IoC container management.

### Repository Pattern

Domain layer mendefinisikan interface, Infrastructure layer mengimplementasikannya:

```php
// Domain Layer (Interface)
interface ProductRepositoryInterface {
    public function findById(int $id): ?Product;
}

// Infrastructure Layer (Implementation)
class ProductRepository implements ProductRepositoryInterface {
    public function findById(int $id): ?Product {
        return Product::find($id);
    }
}

// Binding di ServiceProvider
$this->app->bind(
    ProductRepositoryInterface::class,
    ProductRepository::class
);
```

**Mengapa?** Business logic tidak tergantung pada database implementation. Mudah untuk swap implementation (ganti ke MongoDB, REST API, dll).

### DTO (Data Transfer Object)

Transfer data antar layer:

```php
// Dari controller
$dto = CreateProductDTO::from($request->validated());

// Ke use case
$product = $useCase->execute($dto);

// Benefits:
// - Type-safe
// - Clear input requirements
// - Easier to test
```

### Use Case Pattern

Satu use case = satu business action:

```php
// Good: Satu use case, satu tanggung jawab
class CreateProductUseCase { execute(...) }
class UpdateProductUseCase { execute(...) }
class ListProductsUseCase { execute(...) }

// Bad: Multiple concerns dalam satu use case
class ProductUseCase { 
    create(...) 
    update(...) 
    list(...) 
}
```

---

## 🆕 Adding New Features

Step-by-step guide untuk menambah fitur baru (misalnya: **Create Discount**).

### Step 1: Create Domain Layer Interfaces

```bash
# File: app/Domain/Payment/Repositories/DiscountRepositoryInterface.php

<?php
namespace App\Domain\Payment\Repositories;

interface DiscountRepositoryInterface {
    public function findById(int $id): ?Discount;
    public function findByCode(string $code): ?Discount;
    public function create(array $data): Discount;
    public function update(Discount $discount, array $data): Discount;
}
```

**Poin Penting:**
- Definisikan kontrak/interface untuk data access
- Jangan ada implementation detail (Eloquent, etc)
- Focus pada business needs

### Step 2: Create Application Layer Use Cases

```bash
# File: app/Application/Payment/UseCases/CreateDiscountUseCase.php

<?php
namespace App\Application\Payment\UseCases;

use App\Application\Payment\DTOs\CreateDiscountDTO;
use App\Domain\Payment\Repositories\DiscountRepositoryInterface;

class CreateDiscountUseCase {
    public function __construct(
        private readonly DiscountRepositoryInterface $repository
    ) {}
    
    public function execute(CreateDiscountDTO $dto): Discount {
        // Validasi business rules
        if ($this->repository->findByCode($dto->code) !== null) {
            throw new DuplicateDiscountCodeException();
        }
        
        // Create discount
        return $this->repository->create($dto->toArray());
    }
}
```

```bash
# File: app/Application/Payment/DTOs/CreateDiscountDTO.php

<?php
namespace App\Application\Payment\DTOs;

class CreateDiscountDTO {
    public function __construct(
        public readonly string $code,
        public readonly string $description,
        public readonly float $discount_percentage,
        public readonly int $min_purchase_amount,
    ) {}
    
    public static function from(array $data): self {
        return new self(
            code: $data['code'],
            description: $data['description'],
            discount_percentage: $data['discount_percentage'],
            min_purchase_amount: $data['min_purchase_amount'],
        );
    }
    
    public function toArray(): array {
        return [
            'code' => $this->code,
            'description' => $this->description,
            'discount_percentage' => $this->discount_percentage,
            'min_purchase_amount' => $this->min_purchase_amount,
        ];
    }
}
```

### Step 3: Create Form Request & Validation

```bash
# File: app/Http/Requests/Payment/StoreDiscountRequest.php

<?php
namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiscountRequest extends FormRequest {
    public function rules(): array {
        return [
            'code' => 'required|string|unique:discounts',
            'description' => 'required|string|max:255',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'min_purchase_amount' => 'required|numeric|min:0',
        ];
    }
    
    public function messages(): array {
        return [
            'code.unique' => 'Discount code already exists',
            'discount_percentage.max' => 'Discount cannot exceed 100%',
        ];
    }
}
```

### Step 4: Create API Resource

```bash
# File: app/Http/Resources/Payment/DiscountResource.php

<?php
namespace App\Http\Resources\Payment;

use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'description' => $this->description,
            'discount_percentage' => $this->discount_percentage,
            'min_purchase_amount' => $this->min_purchase_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

### Step 5: Create Controller

```bash
# File: app/Http/Controllers/Api/DiscountController.php

<?php
namespace App\Http\Controllers\Api;

use App\Application\Payment\DTOs\CreateDiscountDTO;
use App\Application\Payment\UseCases\CreateDiscountUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StoreDiscountRequest;
use App\Http\Resources\Payment\DiscountResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class DiscountController extends Controller {
    public function store(
        StoreDiscountRequest $request,
        CreateDiscountUseCase $useCase
    ): JsonResponse {
        $dto = CreateDiscountDTO::from($request->validated());
        $discount = $useCase->execute($dto);
        
        return ApiResponse::created(
            data: new DiscountResource($discount),
            message: 'Discount created successfully'
        );
    }
}
```

### Step 6: Create Infrastructure Layer (Repository Implementation)

```bash
# File: app/Infrastructure/Persistence/Eloquent/Repositories/DiscountRepository.php

<?php
namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Payment\Repositories\DiscountRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Discount;

class DiscountRepository implements DiscountRepositoryInterface {
    public function __construct(private readonly Discount $model) {}
    
    public function findById(int $id): ?Discount {
        return $this->model->find($id);
    }
    
    public function findByCode(string $code): ?Discount {
        return $this->model->where('code', $code)->first();
    }
    
    public function create(array $data): Discount {
        return $this->model->create($data);
    }
    
    public function update(Discount $discount, array $data): Discount {
        $discount->update($data);
        return $discount;
    }
}
```

### Step 7: Create Eloquent Model

```bash
# File: app/Models/Discount.php (atau app/Infrastructure/Persistence/Eloquent/Models/Discount.php)

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model {
    protected $fillable = [
        'code',
        'description',
        'discount_percentage',
        'min_purchase_amount',
    ];
    
    protected $casts = [
        'discount_percentage' => 'float',
        'min_purchase_amount' => 'float',
    ];
}
```

### Step 8: Create Migration

```bash
# File: database/migrations/2026_05_30_xxxxxx_create_discounts_table.php

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->text('description');
            $table->decimal('discount_percentage', 5, 2);
            $table->decimal('min_purchase_amount', 12, 2);
            $table->timestamps();
        });
    }
    
    public function down(): void {
        Schema::dropIfExists('discounts');
    }
};
```

### Step 9: Register Repository Binding

```bash
# File: app/Providers/AppServiceProvider.php

<?php
namespace App\Providers;

use App\Domain\Payment\Repositories\DiscountRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\DiscountRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(
            DiscountRepositoryInterface::class,
            DiscountRepository::class
        );
    }
}
```

### Step 10: Create Routes

```php
# routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/discounts', [\App\Http\Controllers\Api\DiscountController::class, 'store']);
});
```

### Step 11: Test Everything

```bash
# Run migration
php artisan migrate

# Test API endpoint
POST /api/discounts
{
    "code": "SUMMER2026",
    "description": "Summer discount",
    "discount_percentage": 15,
    "min_purchase_amount": 100000
}

# Expected response: 201 Created
```

---

## 📋 Important Files & Conventions

### Key Configuration Files

| File | Purpose |
|------|---------|
| `config/app.php` | Application configuration |
| `config/database.php` | Database connection |
| `config/auth.php` | Authentication config |
| `app/Providers/AppServiceProvider.php` | Register service bindings |
| `routes/api.php` | API route definitions |

### Naming Conventions

| Layer | Pattern | Example |
|-------|---------|---------|
| Domain Repositories | `{Entity}RepositoryInterface.php` | `ProductRepositoryInterface.php` |
| Use Cases | `{Action}{Entity}UseCase.php` | `CreateProductUseCase.php` |
| DTOs | `{Action}{Entity}DTO.php` | `CreateProductDTO.php` |
| Controllers | `{Entity}Controller.php` | `ProductController.php` |
| Form Requests | `{Action}{Entity}Request.php` | `StoreProductRequest.php` |
| Resources | `{Entity}Resource.php` | `ProductResource.php` |
| Repositories (Impl) | `{Entity}Repository.php` | `ProductRepository.php` |
| Eloquent Models | `{Entity}.php` | `Product.php` |
| Migrations | `YYYY_MM_DD_xxxxxx_{action}.php` | `2026_05_30_144035_create_products_table.php` |

### File Organization

```
Feature-based Organization by Domain:
- Domain/Category/ → Semua business logic kategori
- Domain/Product/ → Semua business logic produk
- Application/Category/ → Semua use cases kategori
- Application/Product/ → Semua use cases produk
- Http/Controllers/Api/CategoryController.php
- Http/Requests/Category/
- Http/Resources/Category/
```

**Benefit:** Mudah menemukan file yang related (co-located).

---

## 🔧 Important Coding Patterns

### 1. Always Use Repository Interface in Application Layer

```php
// ❌ BAD - Direct model access
class CreateProductUseCase {
    public function execute(...) {
        $product = Product::create($data);
    }
}

// ✅ GOOD - Through interface
class CreateProductUseCase {
    public function __construct(
        private readonly ProductRepositoryInterface $repository
    ) {}
    
    public function execute(...) {
        $product = $this->repository->create($data);
    }
}
```

### 2. Always Validate in Form Request

```php
// ❌ BAD - Validation in controller
public function store(Request $request) {
    $validated = $request->validate([...]);
}

// ✅ GOOD - Validation in Form Request
public function store(StoreProductRequest $request) {
    $validated = $request->validated();
}
```

### 3. Always Use DTO for Data Transfer

```php
// ❌ BAD - Raw array
$useCase->execute($request->all());

// ✅ GOOD - Typed DTO
$dto = CreateProductDTO::from($request->validated());
$useCase->execute($dto);
```

### 4. Always Format Response Using Resource

```php
// ❌ BAD - Return model directly
return response()->json($product);

// ✅ GOOD - Use Resource
return ApiResponse::created(
    data: new ProductResource($product)
);
```

---

## 🐛 Troubleshooting Guide

### Problem: "Class not found" error

**Cause:** Repository binding not registered  
**Solution:** Check `AppServiceProvider::register()`

```php
$this->app->bind(
    ProductRepositoryInterface::class,
    ProductRepository::class
);
```

### Problem: Business logic in Controller

**Cause:** Putting logic directly in controller  
**Solution:** Move to UseCase in Application layer

```php
// Move this logic:
public function store(Request $request, CreateProductUseCase $useCase) {
    // Call useCase instead
    $useCase->execute(...);
}
```

### Problem: Circular dependencies

**Cause:** Layer depends on layer above it  
**Solution:** Always follow dependency rule: `Presentation → Application → Domain ← Infrastructure`

### Problem: Hard to test

**Cause:** Tight coupling to concrete implementations  
**Solution:** 
- Use dependency injection
- Use interfaces for contracts
- Mock repositories in tests

### Problem: Where to put feature X?

**Decision tree:**
1. **Pure business rules?** → Domain Layer
2. **Orchestrate multiple domains?** → Application Layer (UseCase)
3. **HTTP handling?** → Presentation Layer (Controller)
4. **Database/External service?** → Infrastructure Layer (Repository)

---

## 📚 Related Files to Review

When working with each layer, check these key files:

### Domain Layer Examples
- `app/Domain/Product/Repositories/ProductRepositoryInterface.php`

### Application Layer Examples
- `app/Application/Product/UseCases/CreateProductUseCase.php`
- `app/Application/Product/DTOs/CreateProductDTO.php`

### Presentation Layer Examples
- `app/Http/Controllers/Api/ProductController.php`
- `app/Http/Requests/Product/StoreProductRequest.php`
- `app/Http/Resources/Product/ProductResource.php`

### Infrastructure Layer Examples
- `app/Infrastructure/Persistence/Eloquent/Models/Product.php`
- `app/Infrastructure/Persistence/Eloquent/Repositories/ProductRepository.php`
- `database/migrations/2026_05_23_152253_create_products_table.php`

### Service Provider & Bindings
- `app/Providers/AppServiceProvider.php`

### Routes
- `routes/api.php`

---

## ✅ Checklist: Before Committing

Sebelum push code, pastikan:

- [ ] Repository interface dibuat di Domain Layer
- [ ] Repository implementation di Infrastructure Layer
- [ ] UseCase dibuat di Application Layer
- [ ] DTO dibuat untuk data transfer
- [ ] Form Request dibuat untuk validation
- [ ] Controller menggunakan UseCase
- [ ] Resource dibuat untuk response format
- [ ] Repository binding registered di AppServiceProvider
- [ ] Route dibuat
- [ ] Migration dibuat (jika ada database changes)
- [ ] Model dibuat (jika ada database changes)
- [ ] Naming conventions followed
- [ ] No business logic in controller
- [ ] No direct model access in UseCase (use repository)
- [ ] Tests written (optional but recommended)

---

## 🚀 Quick Commands

```bash
# Create new migration
php artisan make:migration create_table_name

# Run migration
php artisan migrate

# Rollback migration
php artisan migrate:rollback

# Tinker (interactive shell)
php artisan tinker

# Run tests
php artisan test

# Clear cache
php artisan cache:clear

# Check routes
php artisan route:list | grep api
```

---

## 📞 Questions & Support

Jika ada pertanyaan tentang architecture:
1. Review file example yang sesuai
2. Follow the patterns yang sudah ada
3. Pastikan dependency rules diikuti
4. Test sebelum push code

---

**Document Version:** 1.0  
**Last Updated:** May 30, 2026  
**Maintained By:** Development Team
