# API Quick Start Guide

Get your mobile API up and running in minutes!

## Prerequisites

-   Laravel CRM installed and configured
-   Database migrated and seeded
-   PHP 8.2+ and Composer installed

---

## Step 1: Verify Installation

Make sure all controllers and routes are in place:

```bash
cd /path/to/laravel-crm

# Check if API files exist
ls -la app/Http/Controllers/Api/V1/
ls -la app/Http/Resources/Api/V1/
cat routes/api.php
```

---

## Step 2: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize
```

---

## Step 3: Configure CORS (Important for Mobile)

Edit `config/cors.php`:

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // For development only! Use specific domains in production

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
```

---

## Step 4: Start the Server

```bash
# For development
php artisan serve

# Output should show:
# Starting Laravel development server: http://127.0.0.1:8000
```

Or configure your local web server (Apache/Nginx).

---

## Step 5: Test the API

### 1. Test Health Endpoint

```bash
curl http://127.0.0.1:8000/api/health
```

**Expected Response:**

```json
{
    "success": true,
    "message": "API is running",
    "version": "1.0.0",
    "timestamp": "2024-12-02T10:30:00+00:00"
}
```

### 2. Test Login

```bash
curl -X POST http://127.0.0.1:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "admin123"
  }'
```

**Expected Response:**

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      ...
    },
    "token": "1|abc123xyz..."
  }
}
```

**Save your token!** You'll need it for authenticated requests.

### 3. Test Authenticated Endpoint (Get Leads)

Replace `YOUR_TOKEN_HERE` with the token from login:

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/leads?per_page=5" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Step 6: Create Test Data (Optional)

If you don't have data in your database:

```bash
php artisan tinker
```

Then in tinker:

```php
// Create a test lead
$user = \Webkul\User\Models\User::first();
$pipeline = \Webkul\Lead\Models\Pipeline::first();
$stage = $pipeline->stages->first();

\Webkul\Lead\Models\Lead::create([
    'title' => 'Test Lead from API',
    'description' => 'This is a test lead',
    'lead_value' => 50000,
    'status' => 'new',
    'user_id' => $user->id,
    'lead_pipeline_id' => $pipeline->id,
    'lead_pipeline_stage_id' => $stage->id,
]);

// Create a test person
\Webkul\Contact\Models\Person::create([
    'name' => 'John Doe',
    'emails' => ['john@example.com'],
    'contact_numbers' => ['+1234567890'],
    'job_title' => 'CEO',
    'user_id' => $user->id,
]);

exit
```

---

## Step 7: Test All Endpoints

### Dashboard

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/dashboard?type=overview" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Get Profile

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/auth/profile" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Get Persons

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/persons" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Get Activities

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/activities?upcoming=1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Get Products

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/products" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Get Quotes

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/quotes" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Create a Lead

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/leads" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "New Mobile Lead",
    "description": "Created from mobile API",
    "lead_value": 25000,
    "status": "new",
    "user_id": 1,
    "lead_pipeline_id": 1,
    "lead_pipeline_stage_id": 1
  }'
```

---

## Step 8: Using Postman

### Import Collection

Create a new Postman collection with these settings:

**Collection Variables:**

-   `base_url`: `http://127.0.0.1:8000/api/v1`
-   `token`: (will be set after login)

**Request Examples:**

1. **Login** (POST)

    - URL: `{{base_url}}/login`
    - Body (JSON):
        ```json
        {
            "email": "admin@example.com",
            "password": "admin123"
        }
        ```
    - Test Script:
        ```javascript
        if (pm.response.code === 200) {
            var jsonData = pm.response.json();
            pm.collectionVariables.set("token", jsonData.data.token);
        }
        ```

2. **Get Leads** (GET)

    - URL: `{{base_url}}/leads`
    - Headers: `Authorization: Bearer {{token}}`

3. **Create Lead** (POST)
    - URL: `{{base_url}}/leads`
    - Headers: `Authorization: Bearer {{token}}`
    - Body: (see example above)

---

## Step 9: Production Deployment Checklist

Before deploying to production:

### Security

-   [ ] Set `APP_ENV=production` in `.env`
-   [ ] Set `APP_DEBUG=false` in `.env`
-   [ ] Generate new `APP_KEY`: `php artisan key:generate`
-   [ ] Update CORS `allowed_origins` to your actual domains
-   [ ] Set Sanctum token expiration in `config/sanctum.php`
-   [ ] Enable HTTPS (SSL certificate)
-   [ ] Set up proper firewall rules

### Performance

-   [ ] Run `composer install --optimize-autoloader --no-dev`
-   [ ] Run `php artisan config:cache`
-   [ ] Run `php artisan route:cache`
-   [ ] Run `php artisan view:cache`
-   [ ] Set up Redis for caching and sessions
-   [ ] Configure queue workers: `php artisan queue:work`

### Monitoring

-   [ ] Set up error logging (Sentry, Bugsnag, etc.)
-   [ ] Configure rate limiting
-   [ ] Set up API monitoring
-   [ ] Configure backup system

### Documentation

-   [ ] Document all API endpoints
-   [ ] Create API versioning strategy
-   [ ] Provide API changelog

---

## Common Issues & Solutions

### Issue: "Route not found"

**Solution:**

```bash
php artisan route:clear
php artisan optimize
```

### Issue: "Unauthenticated" error

**Possible causes:**

1. Token not included in request
2. Token expired
3. Invalid token format

**Solution:**

-   Make sure you're including: `Authorization: Bearer YOUR_TOKEN`
-   Check if token is still valid (login again if needed)

### Issue: CORS error from mobile app

**Solution:**
Update `config/cors.php` to allow your app's requests (see Step 3)

### Issue: 500 Internal Server Error

**Solution:**

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Or enable debug mode temporarily
# In .env: APP_DEBUG=true (NEVER in production!)
```

### Issue: "Personal access client not found"

**Solution:**

```bash
php artisan passport:install
# or for Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

---

## Testing Checklist

Test each endpoint:

-   [ ] ✅ Health check works
-   [ ] ✅ Login returns token
-   [ ] ✅ Profile retrieval works
-   [ ] ✅ Dashboard statistics load
-   [ ] ✅ Leads CRUD operations
-   [ ] ✅ Persons CRUD operations
-   [ ] ✅ Activities CRUD operations
-   [ ] ✅ Products CRUD operations
-   [ ] ✅ Quotes CRUD operations
-   [ ] ✅ Logout works
-   [ ] ✅ Token refresh works
-   [ ] ✅ Pagination works
-   [ ] ✅ Search filters work
-   [ ] ✅ Validation errors return properly
-   [ ] ✅ 404 errors handled correctly
-   [ ] ✅ 401 unauthorized handled

---

## Performance Tips

### 1. Eager Loading

The API controllers already use eager loading to prevent N+1 queries:

```php
$query = $this->leadRepository->with(['user', 'person', 'stage']);
```

### 2. Pagination

All list endpoints support pagination:

```
GET /api/v1/leads?per_page=20&page=2
```

### 3. Selective Loading

Request only the data you need by filtering relationships in your Flutter app.

### 4. Caching

Consider implementing caching for frequently accessed data:

```php
Cache::remember('dashboard_stats', 3600, function () {
    return $this->dashboardHelper->getOverAllStats();
});
```

---

## Next Steps

1. **Build Flutter App**: Follow `FLUTTER_INTEGRATION_GUIDE.md`
2. **Read Full Docs**: Check `API_DOCUMENTATION.md`
3. **Customize**: Add more endpoints as needed
4. **Test Thoroughly**: Write automated tests
5. **Deploy**: Follow production checklist above

---

## Support

-   **Laravel Docs**: https://laravel.com/docs
-   **Sanctum Docs**: https://laravel.com/docs/sanctum
-   **API Issues**: Check `storage/logs/laravel.log`

## Quick Reference

| Endpoint               | Method | Auth Required |
| ---------------------- | ------ | ------------- |
| `/api/health`          | GET    | No            |
| `/api/v1/login`        | POST   | No            |
| `/api/v1/auth/profile` | GET    | Yes           |
| `/api/v1/auth/logout`  | POST   | Yes           |
| `/api/v1/dashboard`    | GET    | Yes           |
| `/api/v1/leads`        | GET    | Yes           |
| `/api/v1/leads/{id}`   | GET    | Yes           |
| `/api/v1/leads`        | POST   | Yes           |
| `/api/v1/leads/{id}`   | PUT    | Yes           |
| `/api/v1/leads/{id}`   | DELETE | Yes           |
| `/api/v1/persons`      | GET    | Yes           |
| `/api/v1/activities`   | GET    | Yes           |
| `/api/v1/products`     | GET    | Yes           |
| `/api/v1/quotes`       | GET    | Yes           |

**Default Credentials:**

-   Email: `admin@example.com`
-   Password: `admin123`

---

🎉 **You're all set!** Start building your Flutter app now.
