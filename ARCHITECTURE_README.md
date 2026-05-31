# 📚 POS DiBimbing - Architecture Documentation

Welcome to the POS DiBimbing project documentation! This folder contains comprehensive guides about the architecture and structure of this Laravel project.

## 📖 Documentation Files

### 1. 🎯 **Quick Reference Guide** (START HERE!)
📄 **File:** [`QUICK_REFERENCE.md`](./QUICK_REFERENCE.md)

**Best for:** Quick lookups while coding  
**Contains:**
- 1-minute overview of architecture
- File location cheat sheet
- Common mistakes & how to fix them
- Minimal code templates for each layer
- Naming conventions quick map

**When to use:** When you're building a new feature and need quick answers

---

### 2. 📘 **Complete Architecture Handoff**
📄 **File:** [`ARCHITECTURE_HANDOFF.md`](./ARCHITECTURE_HANDOFF.md)

**Best for:** Understanding the full architecture  
**Contains:**
- Detailed explanation of all 4 layers
- Each layer's responsibilities & what goes where
- Complete request flow with examples
- Step-by-step guide to adding new features
- Important files & conventions
- Troubleshooting guide
- Pre-commit checklist

**When to use:** 
- First time learning the architecture
- Understanding how a specific flow works
- Detailed implementation guidance

---

### 3. 🎨 **Architecture Diagrams & Visual Guide**
📄 **File:** [`ARCHITECTURE_DIAGRAMS.md`](./ARCHITECTURE_DIAGRAMS.md)

**Best for:** Visual learners  
**Contains:**
- System architecture diagram (all layers)
- Complete data flow visualization (Create Product example)
- Dependency flow & rules
- Communication between layers
- UseCase lifecycle
- Naming convention flowchart
- Feature checklist

**When to use:** When you want to visualize how everything connects

---

## 🏗️ Quick Architecture Overview

This project uses **Clean Architecture + Domain-Driven Design (DDD)**.

```
┌─────────────────────────────────┐
│  🟡 PRESENTATION LAYER (HTTP)   │ ← Controllers, Requests, Resources
├─────────────────────────────────┤
│  🟠 APPLICATION LAYER (Logic)   │ ← UseCases, DTOs
├─────────────────────────────────┤
│  🔴 DOMAIN LAYER (Rules)        │ ← Repository Interfaces
├─────────────────────────────────┤
│  🟢 INFRASTRUCTURE (Details)    │ ← Models, Repositories, Database
└─────────────────────────────────┘
```

**Key Principle:** Each layer depends only on the layer BELOW it.

---

## 🚀 Getting Started

### First Time Setup?

1. **Read:** [`QUICK_REFERENCE.md`](./QUICK_REFERENCE.md) - 5 minutes
2. **Read:** [`ARCHITECTURE_HANDOFF.md`](./ARCHITECTURE_HANDOFF.md) - 20 minutes
3. **Review:** [`ARCHITECTURE_DIAGRAMS.md`](./ARCHITECTURE_DIAGRAMS.md) - 10 minutes

### Adding a New Feature?

1. Go to [`QUICK_REFERENCE.md`](./QUICK_REFERENCE.md) → "Minimal Code Template"
2. Or read detailed guide in [`ARCHITECTURE_HANDOFF.md`](./ARCHITECTURE_HANDOFF.md) → "Adding New Features"
3. Reference the diagrams in [`ARCHITECTURE_DIAGRAMS.md`](./ARCHITECTURE_DIAGRAMS.md) if needed

### Confused About Something?

1. Check [`QUICK_REFERENCE.md`](./QUICK_REFERENCE.md) → "Troubleshooting" section
2. Read related section in [`ARCHITECTURE_HANDOFF.md`](./ARCHITECTURE_HANDOFF.md)
3. Look at diagrams in [`ARCHITECTURE_DIAGRAMS.md`](./ARCHITECTURE_DIAGRAMS.md)

---

## 📁 Project Structure

```
pos-backend/
│
├── app/
│   ├── Domain/                    # 🔴 Business Rules (Interfaces)
│   ├── Application/               # 🟠 Use Cases & Orchestration
│   ├── Http/                      # 🟡 Presentation Layer
│   │   ├── Controllers/Api/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Models/                    # Eloquent Models
│   ├── Infrastructure/            # 🟢 Technical Implementation
│   │   ├── Persistence/
│   │   └── ThirdParty/
│   └── Providers/
│
├── database/
│   ├── migrations/                # Database schema
│   ├── seeders/                   # Database seeds
│   └── factories/                 # Model factories
│
├── routes/
│   └── api.php                    # API routes
│
├── tests/                         # Unit & Feature tests
│
└── Documentation Files:
    ├── QUICK_REFERENCE.md         # ⭐ START HERE
    ├── ARCHITECTURE_HANDOFF.md    # Complete guide
    ├── ARCHITECTURE_DIAGRAMS.md   # Visual diagrams
    └── README.md                  # This file
```

---

## 🎯 Quick Commands

```bash
# Create new migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback migration
php artisan migrate:rollback

# Run tests
php artisan test

# Interactive shell
php artisan tinker

# Check routes
php artisan route:list | grep api
```

---

## ✅ Coding Standards

### Naming Conventions

| What | Pattern | Example |
|------|---------|---------|
| Repository Interface | `{Entity}RepositoryInterface.php` | `ProductRepositoryInterface.php` |
| UseCase | `{Action}{Entity}UseCase.php` | `CreateProductUseCase.php` |
| DTO | `{Action}{Entity}DTO.php` | `CreateProductDTO.php` |
| Controller | `{Entity}Controller.php` | `ProductController.php` |
| Form Request | `{Action}{Entity}Request.php` | `StoreProductRequest.php` |
| Resource | `{Entity}Resource.php` | `ProductResource.php` |
| Repository | `{Entity}Repository.php` | `ProductRepository.php` |
| Model | `{Entity}.php` | `Product.php` |

### Key Rules to Follow

✅ **DO:**
- Use dependency injection for all dependencies
- Keep business logic in UseCases, not controllers
- Validate input in FormRequest
- Use Repository interface in Application layer
- Format responses with Resources
- Use DTOs for data transfer between layers

❌ **DON'T:**
- Import Eloquent Model in UseCase
- Put business logic in Controller
- Access database directly in Controller
- Return raw Model from Controller
- Skip validation using FormRequest

---

## 📚 Layer Responsibilities at a Glance

| Layer | Responsibility | Files |
|-------|---|---|
| **Presentation (HTTP)** | Handle HTTP requests, validate input, format responses | Controllers, FormRequests, Resources |
| **Application** | Orchestrate business logic, execute use cases | UseCases, DTOs |
| **Domain** | Define business contracts/interfaces | RepositoryInterfaces |
| **Infrastructure** | Implement data access, external services | Repositories, Models, Migrations |

---

## 🔄 Request Flow Summary

```
1. HTTP Request arrives
   ↓
2. Route → Controller
   ↓
3. FormRequest validates input
   ↓
4. Controller creates DTO
   ↓
5. Controller calls UseCase with DTO
   ↓
6. UseCase orchestrates business logic
   ↓
7. UseCase calls Repository (via interface)
   ↓
8. Repository accesses database via Eloquent Model
   ↓
9. Model returned back up the chain
   ↓
10. Controller formats with Resource
    ↓
11. HTTP Response (JSON) returned to client
```

---

## 🆘 Common Questions

**Q: Where do I put file X?**  
A: Check the file location chart in [`QUICK_REFERENCE.md`](./QUICK_REFERENCE.md)

**Q: How do I add a new feature?**  
A: Follow the step-by-step guide in [`ARCHITECTURE_HANDOFF.md`](./ARCHITECTURE_HANDOFF.md) → "Adding New Features"

**Q: What's the difference between layers?**  
A: Read [`ARCHITECTURE_HANDOFF.md`](./ARCHITECTURE_HANDOFF.md) → "Layer Responsibilities"

**Q: Why can't I use the Model in UseCase?**  
A: Read about dependency rules in [`ARCHITECTURE_DIAGRAMS.md`](./ARCHITECTURE_DIAGRAMS.md) → "Dependency Flow"

**Q: I'm getting an error, how do I debug?**  
A: Check troubleshooting section in [`QUICK_REFERENCE.md`](./QUICK_REFERENCE.md) or [`ARCHITECTURE_HANDOFF.md`](./ARCHITECTURE_HANDOFF.md)

---

## 🎓 Learning Path

**For beginners:**
1. Read: [`QUICK_REFERENCE.md`](./QUICK_REFERENCE.md) (5 min)
2. Look at: [`ARCHITECTURE_DIAGRAMS.md`](./ARCHITECTURE_DIAGRAMS.md) (10 min)
3. Read: [`ARCHITECTURE_HANDOFF.md`](./ARCHITECTURE_HANDOFF.md) (30 min)
4. Create a simple feature following templates

**For intermediate:**
1. Skip to: [`QUICK_REFERENCE.md`](./QUICK_REFERENCE.md) templates
2. Use: [`ARCHITECTURE_HANDOFF.md`](./ARCHITECTURE_HANDOFF.md) for reference
3. Build features using patterns in existing code

**For advanced:**
1. Focus on: Domain-Driven Design principles
2. Optimize: Repository queries, caching strategies
3. Extend: Add Domain Services for complex logic

---

## 🔗 Related Resources

- **Laravel Documentation:** https://laravel.com/docs
- **DDD Concepts:** https://martinfowler.com/bliki/DomainDrivenDesign.html
- **Clean Architecture:** https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html

---

## ✨ Best Practices

### Before Committing Code

- [ ] All files follow naming conventions
- [ ] No business logic in controllers
- [ ] No Eloquent models imported in UseCases
- [ ] All inputs validated in FormRequest
- [ ] All responses formatted with Resources
- [ ] Repository binding registered in AppServiceProvider
- [ ] Migration created for database changes
- [ ] Code follows existing patterns
- [ ] Tests pass (if applicable)

### Code Review Checklist

- [ ] Dependencies flow correctly (top to bottom only)
- [ ] UseCase has single responsibility
- [ ] No circular dependencies
- [ ] Error handling implemented
- [ ] Naming follows conventions
- [ ] DTOs properly structured
- [ ] Resources format data correctly

---

## 📞 Need Help?

1. **Check documentation** → Start with [`QUICK_REFERENCE.md`](./QUICK_REFERENCE.md)
2. **Review examples** → Look at existing Product feature
3. **Check diagrams** → Visual understanding in [`ARCHITECTURE_DIAGRAMS.md`](./ARCHITECTURE_DIAGRAMS.md)
4. **Ask team** → Discuss with development team

---

## 📝 Documentation Maintenance

These documents are living documentation. If you:
- Find an error → Fix it
- Find something unclear → Clarify it
- Add new patterns → Document them
- Create new conventions → Update naming section

Keep this documentation up-to-date with your code!

---

## 🎉 Welcome to the Team!

Now that you understand the architecture, you're ready to:
- ✅ Build new features with confidence
- ✅ Understand existing code structure
- ✅ Maintain code quality
- ✅ Collaborate effectively with the team

**Happy coding! 🚀**

---

**Last Updated:** May 30, 2026  
**Version:** 1.0  
**Maintained by:** Development Team
