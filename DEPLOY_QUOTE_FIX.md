# Fix Quote Download 404 Error

## Issue
The quote download route was returning 404 because route order was incorrect. More specific routes (`/{id}/download`) must come before generic routes (`/{id}`).

## Steps to Deploy

1. **Push the updated code to your server:**
   ```bash
   git add routes/api.php
   git commit -m "Fix quote download route order"
   git push
   ```

2. **On your Hostinger server, SSH in and run:**
   ```bash
   cd public_html/laravel-crm
   git pull
   php artisan route:clear
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Verify the route is registered:**
   ```bash
   php artisan route:list | grep quotes.download
   ```

You should see:
```
GET|HEAD  api/v1/quotes/{id}/download  api.v1.quotes.download
```

## Why This Happened
Laravel matches routes in the order they're defined. When `/quotes/2/download` was requested:
- ❌ Old order: `/{id}` matched first, treating `id = "2/download"` → 404
- ✅ New order: `/{id}/download` matches first → Success

The fix is now in place. After clearing caches on the server, the download should work!

