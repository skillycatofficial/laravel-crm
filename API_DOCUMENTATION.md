# CRM Mobile API Documentation (v1.0)

## Base URL
```
http://your-domain.com/api/v1
```

## Authentication
This API uses Laravel Sanctum for authentication. Include the bearer token in the Authorization header for all protected endpoints.

```
Authorization: Bearer {your_token_here}
```

---

## Table of Contents
1. [Authentication](#authentication-endpoints)
2. [Dashboard](#dashboard-endpoints)
3. [Leads](#leads-endpoints)
4. [Persons (Contacts)](#persons-endpoints)
5. [Activities](#activities-endpoints)
6. [Products](#products-endpoints)
7. [Quotes](#quotes-endpoints)
8. [Common Response Format](#common-response-format)
9. [Error Handling](#error-handling)

---

## Authentication Endpoints

### 1. Login
**POST** `/api/v1/login`

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "admin123",
  "device_name": "iPhone 12" // Optional
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "image_url": "http://domain.com/storage/users/profile.jpg",
      "status": 1,
      "role": {
        "id": 1,
        "name": "Administrator",
        "permission_type": "all"
      }
    },
    "token": "1|abc123xyz..."
  }
}
```

### 2. Logout
**POST** `/api/v1/auth/logout`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### 3. Get Profile
**GET** `/api/v1/auth/profile`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "image_url": "http://domain.com/storage/users/profile.jpg",
    "status": 1,
    "view_permission": "global",
    "role": {
      "id": 1,
      "name": "Administrator",
      "permission_type": "all"
    },
    "groups": []
  }
}
```

### 4. Update Profile
**PUT** `/api/v1/auth/profile`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "name": "Updated Name",
  "email": "newemail@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

### 5. Refresh Token
**POST** `/api/v1/auth/refresh-token`

**Headers:** `Authorization: Bearer {token}`

---

## Dashboard Endpoints

### Get Dashboard Statistics
**GET** `/api/v1/dashboard?type={type}`

**Query Parameters:**
- `type` (optional): `overview`, `revenue`, `leads`, `revenue-by-sources`, `revenue-by-types`, `top-products`, `top-persons`, `open-leads-by-states`

**Example:**
```
GET /api/v1/dashboard?type=overview
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "revenue": {
      "total_won_revenue": {
        "previous": 50000,
        "current": 75000,
        "progress": 50
      },
      "total_lost_revenue": {
        "previous": 10000,
        "current": 5000,
        "progress": -50
      }
    },
    "overall": {
      "total_leads": {
        "previous": 100,
        "current": 150,
        "progress": 50
      },
      "average_lead_value": { ... },
      "total_quotations": { ... },
      "total_persons": { ... }
    }
  },
  "date_range": "Jan 1 - Jan 31"
}
```

---

## Leads Endpoints

### 1. Get All Leads
**GET** `/api/v1/leads`

**Query Parameters:**
- `per_page` (default: 15): Number of results per page
- `search`: Search in title and description
- `status`: Filter by status (new, contacted, qualified, negotiation, won, lost)
- `pipeline_id`: Filter by pipeline
- `stage_id`: Filter by stage
- `user_id`: Filter by assigned user

**Example:**
```
GET /api/v1/leads?per_page=20&status=new&search=important
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "New Business Opportunity",
      "description": "Potential client for web development",
      "lead_value": 50000,
      "status": "new",
      "expected_close_date": "2024-12-31",
      "rotten_days": 5,
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "person": {
        "id": 10,
        "name": "Jane Smith",
        "emails": ["jane@company.com"],
        "contact_numbers": ["+1234567890"]
      },
      "stage": {
        "id": 1,
        "name": "New",
        "code": "new"
      },
      "products_count": 3,
      "activities_count": 5
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

### 2. Get Single Lead
**GET** `/api/v1/leads/{id}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "New Business Opportunity",
    "description": "Detailed description...",
    "lead_value": 50000,
    "status": "new",
    "person": { ... },
    "user": { ... },
    "tags": [
      {
        "id": 1,
        "name": "Hot Lead",
        "color": "#FF0000"
      }
    ],
    "products": [...],
    "activities": [...],
    "quotes": [...]
  }
}
```

### 3. Create Lead
**POST** `/api/v1/leads`

**Request Body:**
```json
{
  "title": "New Lead Title",
  "description": "Lead description",
  "lead_value": 50000,
  "status": "new",
  "expected_close_date": "2024-12-31",
  "person_id": 10,
  "user_id": 1,
  "lead_source_id": 1,
  "lead_type_id": 1,
  "lead_pipeline_id": 1,
  "lead_pipeline_stage_id": 1
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Lead created successfully",
  "data": { ... }
}
```

### 4. Update Lead
**PUT** `/api/v1/leads/{id}`

**Request Body:** (Same as Create, all fields optional)

### 5. Delete Lead
**DELETE** `/api/v1/leads/{id}`

### 6. Update Lead Stage
**PUT** `/api/v1/leads/{id}/stage`

**Request Body:**
```json
{
  "lead_pipeline_stage_id": 2
}
```

---

## Persons Endpoints

### 1. Get All Persons
**GET** `/api/v1/persons`

**Query Parameters:**
- `per_page` (default: 15)
- `search`: Search by name or job title
- `organization_id`: Filter by organization

### 2. Get Single Person
**GET** `/api/v1/persons/{id}`

### 3. Create Person
**POST** `/api/v1/persons`

**Request Body:**
```json
{
  "name": "John Smith",
  "emails": ["john@example.com", "john.smith@company.com"],
  "contact_numbers": ["+1234567890", "+0987654321"],
  "job_title": "CEO",
  "organization_id": 5,
  "user_id": 1
}
```

### 4. Update Person
**PUT** `/api/v1/persons/{id}`

### 5. Delete Person
**DELETE** `/api/v1/persons/{id}`

---

## Activities Endpoints

### 1. Get All Activities
**GET** `/api/v1/activities`

**Query Parameters:**
- `per_page` (default: 15)
- `type`: Filter by type (call, meeting, lunch, task)
- `is_done`: Filter by completion status (0 or 1)
- `user_id`: Filter by user
- `lead_id`: Filter by lead
- `person_id`: Filter by person
- `upcoming`: Get only upcoming activities (1)

**Example:**
```
GET /api/v1/activities?upcoming=1&is_done=0&per_page=10
```

### 2. Get Single Activity
**GET** `/api/v1/activities/{id}`

### 3. Create Activity
**POST** `/api/v1/activities`

**Request Body:**
```json
{
  "title": "Client Meeting",
  "type": "meeting",
  "location": "Conference Room A",
  "comment": "Discuss project requirements",
  "schedule_from": "2024-12-25 10:00:00",
  "schedule_to": "2024-12-25 11:00:00",
  "user_id": 1,
  "is_done": false,
  "lead_ids": [1, 2],
  "person_ids": [10]
}
```

### 4. Update Activity
**PUT** `/api/v1/activities/{id}`

### 5. Delete Activity
**DELETE** `/api/v1/activities/{id}`

### 6. Toggle Activity Done Status
**POST** `/api/v1/activities/{id}/toggle-done`

---

## Products Endpoints

### 1. Get All Products
**GET** `/api/v1/products`

**Query Parameters:**
- `per_page` (default: 15)
- `search`: Search in name, SKU, or description

### 2. Get Single Product
**GET** `/api/v1/products/{id}`

### 3. Create Product
**POST** `/api/v1/products`

**Request Body:**
```json
{
  "name": "Product Name",
  "sku": "PROD-001",
  "description": "Product description",
  "quantity": 100,
  "price": 99.99
}
```

### 4. Update Product
**PUT** `/api/v1/products/{id}`

### 5. Delete Product
**DELETE** `/api/v1/products/{id}`

---

## Quotes Endpoints

### 1. Get All Quotes
**GET** `/api/v1/quotes`

**Query Parameters:**
- `per_page` (default: 15)
- `search`: Search in subject or description
- `user_id`: Filter by user
- `person_id`: Filter by person

### 2. Get Single Quote
**GET** `/api/v1/quotes/{id}`

### 3. Create Quote
**POST** `/api/v1/quotes`

**Request Body:**
```json
{
  "subject": "Website Development Quote",
  "description": "Quote for building e-commerce website",
  "user_id": 1,
  "person_id": 10,
  "billing_address": {
    "address": "123 Main St",
    "city": "New York",
    "state": "NY",
    "country": "USA",
    "postcode": "10001"
  },
  "shipping_address": { ... },
  "discount_percent": 10,
  "discount_amount": 1000,
  "tax_amount": 800,
  "adjustment_amount": 0,
  "sub_total": 10000,
  "grand_total": 9800,
  "expired_at": "2024-12-31 23:59:59",
  "items": [
    {
      "product_id": 1,
      "name": "Website Development",
      "quantity": 1,
      "price": 10000,
      "amount": 10000,
      "discount_amount": 1000,
      "tax_amount": 800,
      "total": 9800
    }
  ]
}
```

### 4. Update Quote
**PUT** `/api/v1/quotes/{id}`

### 5. Delete Quote
**DELETE** `/api/v1/quotes/{id}`

---

## Common Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully", // Optional
  "data": { ... } // or [ ... ] for collections
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

---

## Error Handling

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password must be at least 6 characters."
    ]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Lead not found"
}
```

### Server Error (500)
```json
{
  "message": "Server Error",
  "exception": "Error details..." // Only in debug mode
}
```

---

## Rate Limiting
- API endpoints are rate-limited to 60 requests per minute per user.
- When limit is exceeded, you'll receive a 429 status code.

---

## Testing the API

### Using cURL
```bash
# Login
curl -X POST http://your-domain.com/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}'

# Get Leads (with token)
curl -X GET http://your-domain.com/api/v1/leads \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

### Using Postman
1. Import the API endpoints
2. Set up environment variable for `base_url` and `token`
3. Use `{{base_url}}` and `{{token}}` in your requests

---

## Best Practices for Flutter Integration

1. **Store Token Securely**: Use `flutter_secure_storage` package
2. **Handle Token Expiration**: Implement automatic token refresh
3. **Implement Retry Logic**: For failed requests
4. **Cache Data**: Use local database (Hive/SQLite) for offline support
5. **Use Dio Interceptors**: For automatic token injection and error handling
6. **Implement Pull-to-Refresh**: For better UX
7. **Show Loading States**: While fetching data
8. **Handle Pagination**: Load more data as user scrolls

---

## Support
For issues or questions, contact: your-email@example.com

