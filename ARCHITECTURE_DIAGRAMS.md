# Architecture Diagrams & Visual Guide

## 🏗️ System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                     🌐 CLIENT (Web/Mobile)                       │
└────────────────────────────┬────────────────────────────────────┘
                             │ HTTP
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                  📍 PRESENTATION LAYER (HTTP)                    │
│  ┌────────────────┐  ┌──────────────┐  ┌──────────────────┐    │
│  │   Routes       │  │  Controllers │  │  Resources/DTOs  │    │
│  │  (api.php)     │  │  (.../Api/)  │  │  (Responses)     │    │
│  └────────────────┘  └──────────────┘  └──────────────────┘    │
│         ↕                    ↕                    ↕              │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │            Form Request Validation Layer                │   │
│  │     (Http/Requests/{Feature}/Store*Request.php)        │   │
│  └─────────────────────────────────────────────────────────┘   │
└────────────────────────────┬────────────────────────────────────┘
                             │ Dependency Injection
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                  🟠 APPLICATION LAYER                            │
│  ┌──────────────────────┐  ┌────────────────────────────────┐  │
│  │   Use Cases          │  │   Data Transfer Objects        │  │
│  │ (Create, Read,       │  │   (DTOs - CreateXDTO,         │  │
│  │  Update, Delete)     │  │    UpdateXDTO, etc)           │  │
│  │ ({Action}{Entity}    │  │ (Application/{Feature}/DTOs/) │  │
│  │  UseCase.php)        │  │                                │  │
│  └──────────────────────┘  └────────────────────────────────┘  │
│         │                                                        │
│         └────────────────────┬─────────────────────────────────│
│                              │ Uses Interface                   │
│                              ▼                                  │
│                     ┌─────────────────┐                        │
│                     │  Orchestrates   │                        │
│                     │  Domain Logic   │                        │
│                     └─────────────────┘                        │
└────────────────────────────┬────────────────────────────────────┘
                             │ Depends on Interface
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                  🔴 DOMAIN LAYER (Core Business)                │
│  ┌────────────────────────────────────────────────────────┐   │
│  │      Repository Interfaces                              │   │
│  │  (Domain/{Feature}/Repositories/*RepositoryInterface)   │   │
│  │                                                          │   │
│  │  • ProductRepositoryInterface                           │   │
│  │  • CategoryRepositoryInterface                          │   │
│  │  • PaymentRepositoryInterface                           │   │
│  │  • etc...                                               │   │
│  │                                                          │   │
│  │  These DEFINE contracts, not implementation            │   │
│  └────────────────────────────────────────────────────────┘   │
│                                                                  │
│  Pure business rules, NO external dependencies                 │
└────────────────────────────┬────────────────────────────────────┘
                             │ Implemented by
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                  🟢 INFRASTRUCTURE LAYER                         │
│  ┌────────────────────────────────────────────────────────┐   │
│  │  Repository Implementations (Concrete)                 │   │
│  │  (Infrastructure/Persistence/Eloquent/Repositories/)   │   │
│  │                                                          │   │
│  │  class ProductRepository                               │   │
│  │    implements ProductRepositoryInterface { ... }       │   │
│  └────────────────────────────────────────────────────────┘   │
│                             ↓                                   │
│  ┌────────────────────────────────────────────────────────┐   │
│  │  Eloquent Models                                       │   │
│  │  (Models/ or Infrastructure/Persistence/Eloquent/)     │   │
│  │                                                          │   │
│  │  class Product extends Model { ... }                   │   │
│  │  class Category extends Model { ... }                  │   │
│  └────────────────────────────────────────────────────────┘   │
│                             ↓                                   │
│  ┌────────────────────────────────────────────────────────┐   │
│  │  Third-Party Services                                  │   │
│  │  (Infrastructure/ThirdParty/)                           │   │
│  │                                                          │   │
│  │  • Midtrans Payment Integration                        │   │
│  │  • Email Service                                       │   │
│  │  • Logging Service                                     │   │
│  └────────────────────────────────────────────────────────┘   │
└────────────────────────────┬────────────────────────────────────┘
                             │ SQL Queries
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                  💾 DATABASE                                     │
│  ┌────────────────────────────────────────────────────────┐   │
│  │  PostgreSQL / MySQL / SQLite                           │   │
│  │                                                          │   │
│  │  products, categories, payments, users, etc.           │   │
│  │                                                          │   │
│  │  Created via Migrations (database/migrations/)         │   │
│  └────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📊 Data Flow: Create Product

```
┌─────────────────────────────────────────────────────────────────────┐
│  1. HTTP REQUEST                                                     │
│     POST /api/products                                              │
│     { "name": "Nike Shoes", "price": 500000, "category_id": 1 }   │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│  2. ROUTING (routes/api.php)                                        │
│     Route::post('/products', [ProductController::class, 'store'])  │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│  3. PRESENTATION LAYER                                              │
│     ProductController::store()                                      │
│                                                                      │
│     ┌─────────────────────────────────────────────────────────┐   │
│     │ StoreProductRequest validates input:                    │   │
│     │ - name: required|string|max:255                        │   │
│     │ - price: required|numeric|min:0                        │   │
│     │ - category_id: required|exists:categories              │   │
│     └─────────────────────────────────────────────────────────┘   │
│                                                                      │
│     $validated = $request->validated();                            │
│     // returns: ['name' => 'Nike...', 'price' => 500000, ...]    │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│  4. CREATE DTO                                                      │
│     CreateProductDTO::from($validated)                             │
│                                                                      │
│     DTO object created:                                            │
│     {                                                               │
│         name: "Nike Shoes",                                        │
│         price: 500000,                                             │
│         category_id: 1                                             │
│     }                                                               │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│  5. APPLICATION LAYER                                               │
│     CreateProductUseCase::execute(dto)                             │
│                                                                      │
│     public function execute(CreateProductDTO $dto): Product {      │
│         // Orchestrate business logic here                         │
│         // Check business rules, etc.                              │
│         return $this->repository->create($dto->toArray());        │
│     }                                                               │
│                                                                      │
│     DTO converted to array for repository                          │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│  6. DOMAIN LAYER (Interface)                                        │
│     ProductRepositoryInterface::create([...])                      │
│                                                                      │
│     Interface just defines the contract, not the implementation    │
│     "I need to create a product in some storage"                  │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│  7. INFRASTRUCTURE LAYER                                            │
│     ProductRepository::create([...])                               │
│     (implements ProductRepositoryInterface)                        │
│                                                                      │
│     Concrete implementation:                                       │
│     {                                                               │
│         return $this->model->create([                             │
│             'name' => 'Nike Shoes',                               │
│             'price' => 500000,                                    │
│             'category_id' => 1                                    │
│         ]);                                                         │
│     }                                                               │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│  8. ELOQUENT MODEL                                                  │
│     Product::create([...])                                         │
│                                                                      │
│     Uses mass assignment to set fillable properties:               │
│     $fillable = ['name', 'price', 'category_id', ...]            │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│  9. DATABASE                                                        │
│     INSERT INTO products (name, price, category_id) VALUES (...)   │
│                                                                      │
│     Database generates ID and timestamps                           │
│     Returns new Product instance with id, created_at, etc.        │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│  10. BACK UP THE STACK                                              │
│      Product object returned back through all layers               │
│                                                                      │
│      Repository → UseCase → Controller                             │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│  11. FORMAT RESPONSE                                                │
│      ProductResource::toArray($product)                            │
│                                                                      │
│      Transforms Eloquent model to readable JSON:                   │
│      {                                                               │
│          "id": 123,                                                │
│          "name": "Nike Shoes",                                     │
│          "price": 500000,                                          │
│          "category_id": 1,                                         │
│          "created_at": "2026-05-30T10:15:00Z"                    │
│      }                                                               │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│  12. API RESPONSE                                                   │
│      ApiResponse::created(                                         │
│          data: ProductResource,                                    │
│          message: 'Product created successfully'                   │
│      )                                                               │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│  13. HTTP RESPONSE                                                  │
│      Status: 201 Created                                           │
│      Content-Type: application/json                                │
│                                                                      │
│      {                                                               │
│          "success": true,                                          │
│          "data": {                                                 │
│              "id": 123,                                            │
│              "name": "Nike Shoes",                                 │
│              "price": 500000,                                      │
│              "category_id": 1,                                     │
│              "created_at": "2026-05-30T10:15:00Z"                │
│          },                                                         │
│          "message": "Product created successfully"                 │
│      }                                                               │
└────────────────────┬────────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────────┐
│  BACK TO CLIENT                                                     │
│  ✅ Operation successful!                                          │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 🔗 Dependency Flow (How Layers Depend on Each Other)

```
                ↑ DEPENDS ON
                │
                
    Presentation Layer
         (HTTP)
              │
              │ imports & uses
              ▼
    Application Layer  ◄─── Orchestrates
      (Use Cases)       
              │
              │ depends on interface
              ▼
    Domain Layer  ◄────────── Defines contracts
    (Interfaces)
              ▲
              │ implemented by
              │
    Infrastructure Layer  ◄─── Technical details
      (Repositories)
              │
              │ uses
              ▼
    Database & External Services
```

**Key Rule:** Each layer can depend on layers BELOW it, but NEVER on layers ABOVE it.

```
✅ OK:
- Controller uses UseCase
- UseCase uses Repository Interface
- Repository Implementation uses Eloquent Model
- Eloquent Model queries Database

❌ NOT OK:
- Domain layer uses Eloquent (NO!)
- UseCase imports Controller (NO!)
- Repository Interface depends on HTTP (NO!)
```

---

## 📁 Folder Structure by Feature (Product Example)

```
Project Root
│
├── app/
│   ├── Domain/
│   │   └── Product/
│   │       └── Repositories/
│   │           └── ProductRepositoryInterface.php
│   │
│   ├── Application/
│   │   └── Product/
│   │       ├── UseCases/
│   │       │   ├── CreateProductUseCase.php
│   │       │   ├── UpdateProductUseCase.php
│   │       │   ├── ListProductsUseCase.php
│   │       │   ├── ShowProductUseCase.php
│   │       │   ├── SearchProductsForPosUseCase.php
│   │       │   └── DeactivateProductUseCase.php
│   │       └── DTOs/
│   │           ├── CreateProductDTO.php
│   │           └── UpdateProductDTO.php
│   │
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   └── ProductController.php
│   │   ├── Requests/Product/
│   │   │   ├── StoreProductRequest.php
│   │   │   └── UpdateProductRequest.php
│   │   └── Resources/Product/
│   │       └── ProductResource.php
│   │
│   ├── Models/
│   │   └── Product.php
│   │
│   └── Infrastructure/
│       └── Persistence/
│           └── Eloquent/
│               ├── Models/
│               │   └── Product.php (alternative location)
│               └── Repositories/
│                   └── ProductRepository.php
│
├── database/
│   └── migrations/
│       └── 2026_05_23_152253_create_products_table.php
│
├── routes/
│   └── api.php
│
└── app/
    └── Providers/
        └── AppServiceProvider.php (bindings here)
```

---

## 🔀 Communication Between Layers

```
┌─────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                        │
│  Talks to: Application Layer only                           │
│  Via: Dependency Injection (constructor)                    │
│                                                              │
│  Example:                                                   │
│  public function store(                                     │
│      StoreProductRequest $request,                          │
│      CreateProductUseCase $useCase  ◄─── Injected          │
│  ) { ... }                                                  │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                   APPLICATION LAYER                          │
│  Talks to: Domain Layer (interfaces) only                   │
│  Via: Constructor dependency injection                      │
│                                                              │
│  Example:                                                   │
│  public function __construct(                               │
│      private readonly ProductRepositoryInterface $repo ◄─── │
│  ) { }                                                      │
│                                                              │
│  $product = $this->repo->create([...]);                    │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                     DOMAIN LAYER                             │
│  Talks to: No one (interfaces are contracts)               │
│  Via: Interface definitions                                 │
│                                                              │
│  Example:                                                   │
│  interface ProductRepositoryInterface {                     │
│      public function create(array $data): Product;          │
│  }                                                          │
│                                                              │
│  PURE business rules, no external dependencies              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                  INFRASTRUCTURE LAYER                        │
│  Talks to: Domain Layer (implements interfaces)             │
│  Via: Eloquent Models, Database, External APIs             │
│                                                              │
│  Example:                                                   │
│  class ProductRepository implements                         │
│      ProductRepositoryInterface {                           │
│      public function create(array $data): Product {         │
│          return $this->model->create($data);               │
│      }                                                       │
│  }                                                          │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎬 UseCase Lifecycle

```
UseCase instantiated
    ↓
Constructor runs
    ↓ (Dependency Injection resolves)
    ├─→ Repository Interface injected
    └─→ Other services injected
    ↓
execute() method called
    ↓
    ├─→ Validate input (business rules)
    ├─→ Call repository methods
    ├─→ Transform/process data
    ├─→ Execute business logic
    └─→ Return result
    ↓
Controller receives result
    ↓
Response formatted & returned
    ↓
HTTP response sent to client
```

---

## 🏷️ Naming Convention Flowchart

```
What are you building?
    │
    ├─ Something that validates form input?
    │   └─ → StoreProductRequest.php, UpdateProductRequest.php
    │         (Http/Requests/{Feature}/)
    │
    ├─ Something that executes a business action?
    │   └─ → CreateProductUseCase.php, UpdateProductUseCase.php
    │         (Application/{Feature}/UseCases/)
    │
    ├─ Something that transfers data between layers?
    │   └─ → CreateProductDTO.php, UpdateProductDTO.php
    │         (Application/{Feature}/DTOs/)
    │
    ├─ Something that handles HTTP requests?
    │   └─ → ProductController.php
    │         (Http/Controllers/Api/)
    │
    ├─ Something that formats JSON responses?
    │   └─ → ProductResource.php
    │         (Http/Resources/{Feature}/)
    │
    ├─ Something that defines data access contract?
    │   └─ → ProductRepositoryInterface.php
    │         (Domain/{Feature}/Repositories/)
    │
    ├─ Something that implements data access?
    │   └─ → ProductRepository.php
    │         (Infrastructure/Persistence/Eloquent/Repositories/)
    │
    ├─ Something that represents database table?
    │   └─ → Product.php
    │         (Models/ or Infrastructure/Persistence/Eloquent/Models/)
    │
    └─ Something that modifies database schema?
        └─ → 2026_05_23_152253_create_products_table.php
              (database/migrations/)
```

---

## 🧩 How Files Connect

```
ProductController.php
        │
        ├─ imports StoreProductRequest
        ├─ imports CreateProductUseCase
        ├─ imports CreateProductDTO
        ├─ imports ProductResource
        └─ imports ApiResponse
        
CreateProductUseCase
        │
        ├─ imports ProductRepositoryInterface
        ├─ imports CreateProductDTO
        └─ calls repository.create()
        
ProductRepository
        │
        ├─ implements ProductRepositoryInterface
        ├─ imports Product Model
        └─ uses Eloquent methods

AppServiceProvider.php
        │
        └─ binds ProductRepositoryInterface → ProductRepository

routes/api.php
        │
        └─ routes to ProductController::store
```

---

## 📈 Complexity vs. Feature Size

```
Small Feature (1-2 endpoints)
├─ 1 UseCase
├─ 1-2 DTOs
├─ 1 Controller
├─ 1 Form Request
├─ 1 Repository
└─ 1-2 Models

Medium Feature (3-5 endpoints)
├─ 3-5 UseCases
├─ 3-5 DTOs
├─ 1 Controller
├─ 3-5 Form Requests
├─ 1 Repository (with multiple methods)
└─ 1-2 Models

Large Feature (5+ endpoints + complex logic)
├─ 5+ UseCases
├─ 5+ DTOs
├─ Multiple Controllers (if needed)
├─ 5+ Form Requests
├─ Multiple Repositories
├─ 2+ Models
└─ Domain Services for shared logic
```

---

## 🚀 Quick Start: New Feature Checklist

```
Adding new feature "Discount"
    ↓
□ Create Domain/Payment/Repositories/DiscountRepositoryInterface.php
    ↓
□ Create Application/Payment/UseCases/Create|Update|DeleteDiscountUseCase.php
    ↓
□ Create Application/Payment/DTOs/Create|UpdateDiscountDTO.php
    ↓
□ Create Http/Requests/Payment/StoreDiscountRequest.php
    ↓
□ Create Http/Resources/Payment/DiscountResource.php
    ↓
□ Create Http/Controllers/Api/DiscountController.php
    ↓
□ Create Models/Discount.php
    ↓
□ Create Infrastructure/.../Repositories/DiscountRepository.php
    ↓
□ Create database/migrations/..._create_discounts_table.php
    ↓
□ Register binding in AppServiceProvider.php
    ↓
□ Add routes in routes/api.php
    ↓
□ Run migration: php artisan migrate
    ↓
✅ Test the endpoints!
```

---

## 🔐 Security: Where to Validate

```
Input Validation
    ↓
    ├─ Form Level: StoreProductRequest.php
    │  └─ Type checking, required fields, format validation
    │
    ├─ Business Level: UseCase.php
    │  └─ Business rules, authorization, resource ownership
    │
    └─ Database Level: Model & Migration
       └─ Unique constraints, foreign keys, defaults

Authorization
    ├─ Middleware (routes/api.php)
    │  └─ auth:sanctum checks
    │
    └─ UseCase or Controller
       └─ Check if user owns resource

Data Sanitization
    └─ Model casting & accessor/mutator
```

---

**Save this for reference when building new features!**
