# EcoBrew Café — API Documentation

**Base URL:** `http://192.168.101.21:8000` 
**Format:** JSON  
**Authentication:** JWT Bearer Token  

---

## Authentication

### POST /api/login
Login and receive a JWT token.

**Auth required:** No

**Request body:**
```json
{
    "username": "youruser",
    "password": "yourpassword"
}
```

**Success response — 200 OK:**
```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGci..."
}
```

**Error response — 401 Unauthorized:**
```json
{
    "code": 401,
    "message": "Invalid credentials."
}
```

---

### POST /api/register
Register a new customer account.

**Auth required:** No

**Request body:**
```json
{
    "username": "juandelacruz",
    "email": "juan@email.com",
    "password": "secret123",
    "full_name": "Juan dela Cruz"
}
```

**Success response — 201 Created:**
```json
{
    "message": "Registration successful! Please verify your email."
}
```

**Error response — 400 Bad Request:**
```json
{
    "message": "Email already in use."
}
```

---

## Products

### GET /api/products
Get all products. Optionally filter by category.

**Auth required:** No

**Query params:**
- `category` (optional) — Filter by category ID

**Example:** `GET /api/products?category=1`

**Success response — 200 OK:**
```json
[
    {
        "id": 1,
        "name": "Iced Matcha Latte",
        "description": "Premium matcha with oat milk",
        "price": 150.00,
        "image": "http://192.168.101.21:8000/uploads/images/matcha.jpg",
        "size": "Medium",
        "quantity": 50,
        "category": {
            "id": 1,
            "name": "Drinks"
        }
    }
]
```

---

### GET /api/categories
Get all product categories.

**Auth required:** No

**Success response — 200 OK:**
```json
[
    {
        "id": 1,
        "name": "Drinks",
        "description": "Cold and hot beverages"
    }
]
```

---

## Orders

### POST /api/orders
Place a new order.

**Auth required:** Yes (Bearer token)

**Request body:**
```json
{
    "product_id": 1,
    "quantity": 2,
    "customer_name": "Juan dela Cruz",
    "customer_email": "juan@email.com",
    "customer_phone": "09123456789",
    "customer_address": "123 Main St, Manila"
}
```

**Success response — 201 Created:**
```json
{
    "status": "success",
    "message": "Order placed successfully.",
    "order": {
        "id": 10,
        "product": "Iced Matcha Latte",
        "quantity": 2,
        "total_price": 300.00,
        "status": "Pending",
        "created_at": "2026-05-15 10:30:00",
        "customer": {
            "id": 5,
            "name": "Juan dela Cruz",
            "email": "juan@email.com"
        }
    }
}
```

**Error responses:**

422 — Missing or invalid fields:
```json
{
    "status": "error",
    "message": "Validation failed.",
    "errors": ["product_id is required.", "quantity is required."]
}
```

404 — Product not found:
```json
{
    "status": "error",
    "message": "Product not found."
}
```

400 — Insufficient stock:
```json
{
    "status": "error",
    "message": "Insufficient stock.",
    "available": 1,
    "requested": 5
}
```

---

### GET /api/orders
Get all orders for the authenticated user.

**Auth required:** Yes (Bearer token)

**Success response — 200 OK:**
```json
{
    "status": "success",
    "customer": "Juan dela Cruz",
    "total": 2,
    "orders": [
        {
            "id": 10,
            "product": {
                "id": 1,
                "name": "Iced Matcha Latte",
                "image": "http://192.168.101.21:8000/uploads/images/matcha.jpg"
            },
            "quantity": 2,
            "total_price": 300.00,
            "status": "Pending",
            "created_at": "2026-05-15 10:30:00"
        }
    ]
}
```

---

### GET /api/orders/{id}
Get a single order by ID.

**Auth required:** Yes (Bearer token)

**Success response — 200 OK:**
```json
{
    "status": "success",
    "order": {
        "id": 10,
        "product": {
            "id": 1,
            "name": "Iced Matcha Latte",
            "price": 150.00,
            "image": "http://192.168.101.21:8000/uploads/images/matcha.jpg"
        },
        "quantity": 2,
        "total_price": 300.00,
        "status": "Pending",
        "created_at": "2026-05-15 10:30:00",
        "customer": {
            "id": 5,
            "name": "Juan dela Cruz",
            "email": "juan@email.com",
            "phone": "09123456789",
            "address": "123 Main St, Manila"
        }
    }
}
```

**Error response — 404 Not Found:**
```json
{
    "status": "error",
    "message": "Order not found."
}
```

---

### PATCH /api/orders/{id}/cancel
Cancel a pending order.

**Auth required:** Yes (Bearer token)

**Success response — 200 OK:**
```json
{
    "status": "success",
    "message": "Order cancelled successfully.",
    "order": {
        "id": 10,
        "status": "Cancelled"
    }
}
```

**Error response — 400 Bad Request:**
```json
{
    "status": "error",
    "message": "Only pending orders can be cancelled.",
    "current_status": "Completed"
}
```

---

## Profile

### GET /api/profile
Get the authenticated user's profile and order stats.

**Auth required:** Yes (Bearer token)

**Success response — 200 OK:**
```json
{
    "status": "success",
    "user": {
        "id": 3,
        "username": "juandelacruz",
        "email": "juan@email.com",
        "full_name": "Juan dela Cruz",
        "roles": ["ROLE_USER"],
        "is_verified": true,
        "created_at": "2026-01-10 08:00:00",
        "stats": {
            "total_orders": 5,
            "total_spent": 1250.00
        }
    }
}
```

---

### PUT /api/profile
Update the authenticated user's profile.

**Auth required:** Yes (Bearer token)

**Request body** (at least one field required):
```json
{
    "full_name": "Juan dela Cruz",
    "phone": "09123456789",
    "address": "123 Main St, Manila"
}
```

**Success response — 200 OK:**
```json
{
    "status": "success",
    "message": "Profile updated successfully.",
    "full_name": "Juan dela Cruz",
    "email": "juan@email.com"
}
```

**Error response — 422 Unprocessable Entity:**
```json
{
    "status": "error",
    "message": "At least one field is required: full_name, phone, or address."
}
```

---

## HTTP Status Codes Reference

| Code | Meaning |
|------|---------|
| 200 | OK — Request successful |
| 201 | Created — Resource created successfully |
| 400 | Bad Request — Invalid input |
| 401 | Unauthorized — Missing or invalid token |
| 404 | Not Found — Resource does not exist |
| 422 | Unprocessable Entity — Validation failed |
| 500 | Internal Server Error — Server-side error |