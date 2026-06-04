# POS DiBimbing – Backend Handoff Documentation

**Last Updated:** 4 June 2026
**Framework:** Laravel 11
**Architecture:** Clean Architecture + Domain-Driven Design (DDD)

## Project Overview

POS DiBimbing adalah sistem Point-of-Sale untuk UMKM retail dengan arsitektur Clean Architecture + DDD.

### Status Implementasi Saat Ini

✅ Auth & Role Management  
✅ Product & Category Management  
✅ Inventory & Stock Movement  
✅ Cashier Session  
✅ Open Bill  
✅ Add / Update / Delete Item Open Bill  
✅ Checkout Cash  
✅ Direct Checkout Midtrans  
✅ Open Bill Checkout Midtrans  
✅ Midtrans VT Integration  
✅ Midtrans Webhook Handling  
✅ Check Midtrans Status API  
✅ Sales History & Receipt

### Modul Belum Dikerjakan

❌ Rack Management  
❌ Bill of Material (BOM)  
❌ Product Type (Consumable / Service / Storeable)

---

## Backend Architecture

Presentation Layer
- Controllers
- Requests
- Resources

Application Layer
- Use Cases
- DTOs

Domain Layer
- Repository Interfaces
- Business Rules

Infrastructure Layer
- Repository Implementations
- Eloquent Models
- Third Party Integrations (Midtrans)

Database:
- users
- products
- categories
- cashier_sessions
- sales
- sale_items
- payments
- stock_movements

---

## POS Flow

### Direct Checkout Cash

Cashier → Cart → Checkout Cash → PAID → Stock Deducted → Receipt

### Direct Checkout Midtrans

Cashier → Cart → Checkout Midtrans
→ Generate VT URL
→ PENDING_PAYMENT
→ Customer Pay
→ Midtrans Settlement
→ PAID
→ Stock Deducted
→ Receipt

### Open Bill Flow

Open Bill
→ Add Item
→ Update Item
→ Delete Item
→ Checkout Cash / Midtrans
→ Payment Success
→ PAID
→ Receipt

---

## Midtrans Integration

Environment:

MIDTRANS_SERVER_KEY
MIDTRANS_CLIENT_KEY
MIDTRANS_PRODUCTION

Implemented Components:

- MidtransService
- DirectCheckoutMidtransUseCase
- OpenBillCheckoutMidtransUseCase
- MidtransWebhookUseCase
- CheckMidtransStatusUseCase

Webhook Status Mapping:

settlement / capture
→ PAID

expire
→ EXPIRED + CANCELLED

cancel / deny
→ FAILED + CANCELLED

When payment becomes PAID:

- Update Sale Status
- Update Payment Status
- Deduct Inventory Stock
- Create Stock Movement
- Update Cashier Session Summary

---

## Frontend Integration Guide (Next.js)

### Checkout Midtrans

Endpoint:

POST /api/v1/pos/checkout/midtrans

or

POST /api/v1/sales/{id}/checkout/midtrans

Response:

- payment_url
- snap_token

Example:

Customer selects QRIS
→ FE redirects to payment_url
→ Midtrans VT opens
→ Customer pays

---

### After Customer Returns From VT

Recommended Flow:

1. User returns to POS screen
2. FE calls:

POST /api/v1/sales/{id}/midtrans/check-status

3. Backend checks Midtrans API
4. Backend runs MidtransWebhookUseCase internally
5. Backend returns updated Sale

If PAID:

- Show Success Screen
- Print Receipt
- Clear Cart / Close Open Bill

---

## Future Modules

### Rack Management

Purpose:

Storeable products must be assigned to rack locations.

Example:

Rack A1
Rack A2
Rack B1

Benefits:

- Easier stock lookup
- Physical inventory tracking

---

### Bill of Material (BOM)

Purpose:

Consumable products consume ingredients.

Example:

Ice Coffee
- Coffee Bean 20 gr
- Milk 100 ml
- Ice Cube 1 portion

When sold:

Ingredient stock automatically deducted.

---

### Product Type

Storeable
- Has stock
- Assigned to Rack

Consumable
- Uses BOM
- Deducts ingredient stock

Service
- No stock
- No rack

---

## Final Notes

Backend MVP POS is functionally complete for:

- Retail
- Coffee Shop
- Small UMKM

Remaining work is focused on:

1. Rack Management
2. BOM
3. Product Type

Everything else including Midtrans payment gateway integration is already implemented and ready for FE integration.
