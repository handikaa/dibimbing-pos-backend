# Quick Reference Guide - Architecture Pattern

## 🎯 One-Minute Overview

**4 Layers = 4 Responsibilities:**

```
┌──────────────────────────────────────┐
│  🟡 PRESENTATION (HTTP Entry Point)  │ ← Controller, Request, Resource
│      Validate & Format               │
├──────────────────────────────────────┤
│  🟠 APPLICATION (Orchestration)      │ ← UseCase, DTO
│      What to do                      │
├──────────────────────────────────────┤
│  🔴 DOMAIN (Business Rules)          │ ← Interface (what, not how)
│      Why & When                      │
├──────────────────────────────────────┤
│  🟢 INFRASTRUCTURE (Implementation)  │ ← Model, Repository, Database
│      How to do it                    │
└──────────────────────────────────────┘
```

---

## 📂 Which File Goes Where?

| What You Need | Where to Put It | Example |
|---|---|---|
| Data access contract | `Domain/{Feature}/Repositories/` | `ProductRepositoryInterface.php` |
| Business action/workflow | `Application/{Feature}/UseCases/` | `CreateProductUseCase.php` |
| Input validation | `Http/Requests/{Feature}/` | `StoreProductRequest.php` |
| Response formatting | `Http/Resources/{Feature}/` | `ProductResource.php` |
| HTTP endpoint handler | `Http/Controllers/Api/` | `ProductController.php` |
| Data transfer | `Application/{Feature}/DTOs/` | `CreateProductDTO.php` |
| Database model | `Models/` or `Infrastructure/Persistence/Eloquent/Models/` | `Product.php` |
| Data access implementation | `Infrastructure/Persistence/Eloquent/Repositories/` | `ProductRepository.php` |
| Database schema | `database/migrations/` | `2026_05_30_create_products_table.php` |

---

## 🔄 Request to Response Flow

```
1. HTTP Request comes in
        ↓
2. Route → ProductController
        ↓
3. Controller receives StoreProductRequest (auto-validated)
        ↓
4. Controller creates DTO from validated data
        ↓
5. Controller calls UseCase with DTO
        ↓
6. UseCase calls Repository (via interface)
        ↓
7. Repository executes database query
        ↓
8. Model returned back up the chain
        ↓
9. Controller formats with Resource
        ↓
10. Return ApiResponse (JSON)
        ↓
11. HTTP Response 200/201
```

---

## 💻 Minimal Code Template

### 1. Domain Layer - Repository Interface

```php
<?php
// app/Domain/{Feature}/Repositories/{Entity}RepositoryInterface.php

namespace App\Domain\{Feature}\Repositories;

interface {Entity}RepositoryInterface
{
    public function findById(int $id): ?{Entity};
    public function create(array $data): {Entity};
    public function update({Entity} ${entity}, array $data): {Entity};
    public function delete({Entity} ${entity}): bool;
}
```

### 2. Application Layer - UseCase

```php
<?php
// app/Application/{Feature}/UseCases/Create{Entity}UseCase.php

namespace App\Application\{Feature}\UseCases;

use App\Application\{Feature}\DTOs\Create{Entity}DTO;
use App\Domain\{Feature}\Repositories\{Entity}RepositoryInterface;

class Create{Entity}UseCase
{
    public function __construct(
        private readonly {Entity}RepositoryInterface $repository
    ) {}

    public function execute(Create{Entity}DTO $dto): {Entity}
    {
        return $this->repository->create($dto->toArray());
    }
}
```

### 3. Application Layer - DTO

```php
<?php
// app/Application/{Feature}/DTOs/Create{Entity}DTO.php

namespace App\Application\{Feature}\DTOs;

class Create{Entity}DTO
{
    public function __construct(
        public readonly string $field1,
        public readonly string $field2,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            field1: $data['field1'],
            field2: $data['field2'],
        );
    }

    public function toArray(): array
    {
        return [
            'field1' => $this->field1,
            'field2' => $this->field2,
        ];
    }
}
```

### 4. Presentation Layer - Form Request

```php
<?php
// app/Http/Requests/{Feature}/Store{Entity}Request.php

namespace App\Http\Requests\{Feature};

use Illuminate\Foundation\Http\FormRequest;

class Store{Entity}Request extends FormRequest
{
    public function rules(): array
    {
        return [
            'field1' => 'required|string|max:255',
            'field2' => 'required|string',
        ];
    }
}
```

### 5. Presentation Layer - Resource

```php
<?php
// app/Http/Resources/{Feature}/{Entity}Resource.php

namespace App\Http\Resources\{Feature};

use Illuminate\Http\Resources\Json\JsonResource;

class {Entity}Resource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'field1' => $this->field1,
            'field2' => $this->field2,
            'created_at' => $this->created_at,
        ];
    }
}
```

### 6. Presentation Layer - Controller

```php
<?php
// app/Http/Controllers/Api/{Entity}Controller.php

namespace App\Http\Controllers\Api;

use App\Application\{Feature}\DTOs\Create{Entity}DTO;
use App\Application\{Feature}\UseCases\Create{Entity}UseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\{Feature}\Store{Entity}Request;
use App\Http\Resources\{Feature}\{Entity}Resource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class {Entity}Controller extends Controller
{
    public function store(
        Store{Entity}Request $request,
        Create{Entity}UseCase $useCase
    ): JsonResponse {
        $dto = Create{Entity}DTO::from($request->validated());
        ${entity} = $useCase->execute($dto);

        return ApiResponse::created(
            data: new {Entity}Resource(${entity}),
            message: '{Entity} created successfully'
        );
    }
}
```

### 7. Infrastructure Layer - Repository Implementation

```php
<?php
// app/Infrastructure/Persistence/Eloquent/Repositories/{Entity}Repository.php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\{Feature}\Repositories\{Entity}RepositoryInterface;
use App\Models\{Entity};

class {Entity}Repository implements {Entity}RepositoryInterface
{
    public function __construct(private readonly {Entity} $model) {}

    public function findById(int $id): ?{Entity}
    {
        return $this->model->find($id);
    }

    public function create(array $data): {Entity}
    {
        return $this->model->create($data);
    }

    public function update({Entity} ${entity}, array $data): {Entity}
    {
        ${entity}->update($data);
        return ${entity};
    }

    public function delete({Entity} ${entity}): bool
    {
        return ${entity}->delete();
    }
}
```

### 8. Infrastructure Layer - Eloquent Model

```php
<?php
// app/Models/{Entity}.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {Entity} extends Model
{
    protected $fillable = [
        'field1',
        'field2',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
```

### 9. Service Provider - Binding

```php
// app/Providers/AppServiceProvider.php

public function register(): void
{
    $this->app->bind(
        \App\Domain\{Feature}\Repositories\{Entity}RepositoryInterface::class,
        \App\Infrastructure\Persistence\Eloquent\Repositories\{Entity}Repository::class
    );
}
```

### 10. Routes

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/{features}', [
        \App\Http\Controllers\Api\{Entity}Controller::class, 'store'
    ]);
});
```

---

## ✅ Checklist: Sebelum Push

- [ ] Repository interface dibuat
- [ ] Repository implementation dibuat
- [ ] UseCase dibuat
- [ ] DTO dibuat (jika ada data transfer)
- [ ] Form Request dibuat (untuk validasi)
- [ ] Resource dibuat (untuk response)
- [ ] Controller dibuat & menggunakan UseCase
- [ ] Binding didaftarkan di AppServiceProvider
- [ ] Eloquent Model dibuat
- [ ] Migration dibuat
- [ ] Routes didaftarkan
- [ ] Naming conventions diikuti
- [ ] No business logic in controller
- [ ] No Eloquent import in UseCase

---

## 🚨 Common Mistakes to Avoid

### ❌ DON'T

```php
// 1. Direct Eloquent import in UseCase
use App\Models\Product;
class CreateProductUseCase {
    public function execute(...) {
        Product::create(...);  // ❌ WRONG!
    }
}

// 2. Business logic in Controller
class ProductController {
    public function store(...) {
        $this->validate(...);
        $product = Product::create(...);  // ❌ WRONG!
    }
}

// 3. Return raw Model in Controller
public function show($id) {
    return Product::find($id);  // ❌ WRONG!
}

// 4. Validation in UseCase
class CreateProductUseCase {
    public function execute(array $data) {
        if (empty($data['name'])) { ... }  // ❌ WRONG!
    }
}

// 5. Repository usage in Controller
class ProductController {
    public function store(...) {
        $this->repository->create(...);  // ❌ WRONG! Use UseCase!
    }
}
```

### ✅ DO

```php
// 1. Use Repository interface
use App\Domain\Product\Repositories\ProductRepositoryInterface;
class CreateProductUseCase {
    public function __construct(
        private readonly ProductRepositoryInterface $repository
    ) {}
    
    public function execute($dto) {
        return $this->repository->create(...);  // ✅ CORRECT!
    }
}

// 2. Business logic in UseCase
class CreateProductUseCase {
    public function execute(CreateProductDTO $dto): Product {
        // Business logic here
        return $this->repository->create($dto->toArray());
    }
}

// 3. Format response with Resource
public function show(ShowProductUseCase $useCase): JsonResponse {
    $product = $useCase->execute($id);
    return ApiResponse::success(
        data: new ProductResource($product)  // ✅ CORRECT!
    );
}

// 4. Validation in Form Request
class StoreProductRequest extends FormRequest {
    public function rules() {
        return [
            'name' => 'required|string',  // ✅ CORRECT!
        ];
    }
}

// 5. Use UseCase in Controller
class ProductController {
    public function store(
        StoreProductRequest $request,
        CreateProductUseCase $useCase  // ✅ CORRECT!
    ) {
        $dto = CreateProductDTO::from($request->validated());
        $product = $useCase->execute($dto);
    }
}
```

---

## 🎓 When to Use What

| Situation | Use This | Example |
|---|---|---|
| Need to validate form input | Form Request | `StoreProductRequest` |
| Need to execute business logic | UseCase | `CreateProductUseCase` |
| Need to access database | Repository Interface | `ProductRepositoryInterface` |
| Need to transfer data | DTO | `CreateProductDTO` |
| Need to format response | Resource | `ProductResource` |
| Need to handle HTTP | Controller | `ProductController` |
| Need database implementation | Repository + Model | `ProductRepository` + `Product` |

---

## 📚 File Locations Quick Map

```
Project Root
│
├── app/Domain/{Feature}/Repositories/
│   └── {Entity}RepositoryInterface.php
│
├── app/Application/{Feature}/
│   ├── UseCases/
│   │   └── {Action}{Entity}UseCase.php
│   └── DTOs/
│       └── {Action}{Entity}DTO.php
│
├── app/Http/
│   ├── Controllers/Api/
│   │   └── {Entity}Controller.php
│   ├── Requests/{Feature}/
│   │   └── {Action}{Entity}Request.php
│   └── Resources/{Feature}/
│       └── {Entity}Resource.php
│
├── app/Models/
│   └── {Entity}.php
│
├── app/Infrastructure/Persistence/Eloquent/
│   ├── Models/
│   │   └── {Entity}.php (alternative location)
│   └── Repositories/
│       └── {Entity}Repository.php
│
├── app/Providers/
│   └── AppServiceProvider.php
│
├── database/migrations/
│   └── YYYY_MM_DD_XXXXX_{action}.php
│
└── routes/
    └── api.php
```

---

## 🔗 Cross-File References

When creating new feature, these files should know about each other:

1. **Controller** imports → UseCase + DTO + FormRequest + Resource
2. **UseCase** imports → Repository Interface + DTO
3. **Repository Impl** imports → Repository Interface + Model
4. **AppServiceProvider** imports → Repository Interface + Repository Implementation
5. **Routes** imports → Controller

**Example dependency chain:**
```
Controller → UseCase → RepositoryInterface → RepositoryImpl → Model
```

---

## 🆘 Troubleshooting

**Q: Where should I put X?**
- Database access → Domain (interface) + Infrastructure (impl)
- Business rule/workflow → Application (UseCase)
- User interaction handling → Presentation (Controller)
- External service call → Infrastructure (ThirdParty)

**Q: My UseCase is getting too big**
- Split into multiple smaller UseCases
- Each UseCase = one business action

**Q: How to share logic between UseCases?**
- Create a Domain Service or helper class
- Put in a base UseCase class
- Or extract to repository method

**Q: Where to put validation?**
- Input validation → Form Request
- Business validation → UseCase (throw exception if invalid)

---

## 📝 Naming Rules (Must Follow)

```
Controllers: {Entity}Controller.php
UseCases: {Action}{Entity}UseCase.php
DTOs: {Action}{Entity}DTO.php
Requests: {Action}{Entity}Request.php
Resources: {Entity}Resource.php
Repositories: {Entity}RepositoryInterface.php
Models: {Entity}.php
Migrations: YYYY_MM_DD_XXXXXX_{description}.php
```

**Examples:**
- `ProductController.php` ← `Entity = Product, Action = (list/show)`
- `CreateProductUseCase.php` ← `Action = Create, Entity = Product`
- `StoreProductRequest.php` ← `Action = Store, Entity = Product`

---

**Keep this file handy when building new features!**
