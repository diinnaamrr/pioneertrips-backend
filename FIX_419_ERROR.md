# Fix 419 Page Expired Error in Production

## 🔴 Problem
After deploying to production, you get **419 Page Expired** error when trying to login to dashboard.

## 🔍 Root Causes

The 419 error occurs when Laravel's CSRF token validation fails. Common causes:

1. **APP_KEY changed or missing** - Most common cause
   - If APP_KEY changes, all existing sessions and CSRF tokens become invalid
   - New APP_KEY means old encrypted cookies can't be decrypted

2. **Config cache issues**
   - Old config cache contains outdated session settings
   - `.env` changes not reflected because config is cached

3. **Session configuration**
   - `SESSION_SECURE_COOKIE` not set correctly for HTTPS
   - `SESSION_DOMAIN` mismatch
   - Cookie settings incompatible with reverse proxy

4. **Compiled views cache**
   - Old CSRF tokens cached in compiled Blade views
   - Forms still reference old tokens

## ✅ Solution (Already Applied)

The production workflow (`.github/workflows/prod.yml`) has been updated to:

### 1. Preserve APP_KEY
- Backs up `.env` before deployment
- Verifies APP_KEY exists and is not empty
- Only generates new APP_KEY if missing

### 2. Clear All Caches Properly
```bash
# Order matters!
1. Delete config cache file directly
2. Clear config cache
3. Clear all other caches
4. Delete compiled views
5. Restart container
```

### 3. Ensure .env is Loaded
- Copies `.env` to container multiple times:
  - After container starts
  - After cache clear
  - After container restart

### 4. Verify Configuration
- Checks APP_KEY is loaded
- Verifies session settings
- Confirms APP_URL is correct

## 🛠️ Manual Fix (If Needed)

If you still get 419 errors after deployment:

### Step 1: Check APP_KEY
```bash
cd /var/www/wayak-prod
docker exec wayak_app grep "^APP_KEY=" .env
```

**If APP_KEY is missing or changed:**
```bash
# Restore from backup
cp .env.backup.* .env

# Or generate new one (WARNING: This will invalidate all sessions!)
docker exec wayak_app php artisan key:generate --force
```

### Step 2: Clear All Caches
```bash
docker exec wayak_app php artisan optimize:clear
docker exec wayak_app rm -f bootstrap/cache/config.php
docker exec wayak_app find storage/framework/views -name "*.php" -delete
docker restart wayak_app
```

### Step 3: Verify Session Config
```bash
docker exec wayak_app grep -E "SESSION_|APP_URL" .env
```

Ensure:
- `SESSION_SECURE_COOKIE=true` (if using HTTPS)
- `SESSION_SECURE_COOKIE=null` or `false` (if using HTTP)
- `APP_URL` matches your actual domain

### Step 4: Check TrustProxies
If behind reverse proxy (CloudPanel, etc.), ensure `TrustProxies` middleware is configured:
- Already set to `'*'` in `app/Http/Middleware/TrustProxies.php` ✅

## 📋 Checklist for Production Deployment

- [ ] `.env` file exists and has APP_KEY
- [ ] APP_KEY is not empty or "null"
- [ ] SESSION_SECURE_COOKIE is set correctly for HTTPS/HTTP
- [ ] APP_URL matches production domain
- [ ] Config cache is cleared after deployment
- [ ] Compiled views are deleted
- [ ] Container is restarted after .env changes

## 🔐 Security Notes

1. **Never commit APP_KEY to Git** - It's in `.gitignore` ✅
2. **Backup .env before deployment** - Workflow does this automatically ✅
3. **Don't regenerate APP_KEY unnecessarily** - It invalidates all sessions
4. **Use environment-specific .env files** - Don't share between dev/prod

## 🧪 Testing After Fix

1. Clear browser cookies for the domain
2. Try to login again
3. Check browser console for CSRF errors
4. Verify session cookie is set correctly:
   ```javascript
   // In browser console
   document.cookie
   // Should see: wayak_session=...
   ```

## 📝 Additional Debugging

If 419 persists:

```bash
# Check Laravel logs
docker exec wayak_app tail -f storage/logs/laravel.log

# Check session files
docker exec wayak_app ls -la storage/framework/sessions

# Verify CSRF token in form
# View page source, look for: <meta name="csrf-token" content="...">
```

## 🎯 Summary

The workflow now handles all these issues automatically:
- ✅ Preserves APP_KEY
- ✅ Clears caches in correct order
- ✅ Ensures .env is loaded
- ✅ Verifies configuration
- ✅ Restarts containers properly

If you still get 419 errors, check the deployment logs for warnings about APP_KEY.

