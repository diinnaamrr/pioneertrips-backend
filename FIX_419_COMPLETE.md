# Complete Fix for 419 Page Expired Error

## 🔴 Problem
After deployment, getting **419 Page Expired** error when trying to login to admin dashboard.

## 🔍 Root Causes

1. **CSP blocking JavaScript** - Content Security Policy prevents eval() and CSRF token reading
2. **APP_KEY changed** - Most common cause
3. **Session configuration** - SESSION_SECURE_COOKIE, SESSION_DOMAIN issues
4. **Config cache** - Old cached config with wrong settings
5. **Compiled views** - Old CSRF tokens cached in compiled Blade views

## ✅ Complete Solution

### Step 1: Update GlobalMiddleware (Already Done ✅)
- Removes ALL CSP headers completely
- No CSP restrictions for admin routes

### Step 2: Verify APP_KEY on Server
```bash
cd /var/www/wayak-prod

# Check APP_KEY exists and is not empty
docker exec wayak_app grep "^APP_KEY=" .env

# If APP_KEY is missing or "null", restore from backup
cp .env.backup.* .env

# Or generate new one (WARNING: invalidates all sessions)
docker exec wayak_app php artisan key:generate --force
```

### Step 3: Clear All Caches
```bash
# Delete config cache file directly
docker exec wayak_app rm -f bootstrap/cache/config.php

# Clear all Laravel caches
docker exec wayak_app php artisan optimize:clear

# Delete compiled views (important!)
docker exec wayak_app find storage/framework/views -name "*.php" -delete

# Restart container
docker restart wayak_app
```

### Step 4: Verify Session Configuration
```bash
# Check session settings in .env
docker exec wayak_app grep -E "SESSION_|APP_URL" .env
```

Ensure:
- `SESSION_SECURE_COOKIE=true` (if using HTTPS)
- `SESSION_SECURE_COOKIE=null` or `false` (if using HTTP)
- `APP_URL` matches your production domain exactly
- `SESSION_DOMAIN` is not set (or matches your domain)

### Step 5: Clear Browser Data
**CRITICAL**: After server fixes, you MUST:
1. Clear all cookies for the domain
2. Clear browser cache
3. Hard refresh (Ctrl+Shift+R or Cmd+Shift+R)

### Step 6: Test CSRF Token
Open browser console on login page:
```javascript
// Check CSRF token exists
document.querySelector('meta[name="csrf-token"]')?.content

// Check session cookie
document.cookie
```

## 🛠️ Production Workflow Fixes (Already Applied ✅)

The workflow now:
- ✅ Backs up .env before deployment
- ✅ Verifies APP_KEY exists
- ✅ Clears config cache first
- ✅ Clears all caches in correct order
- ✅ Deletes compiled views
- ✅ Restarts container
- ✅ Copies .env multiple times

## 🔐 Session Configuration Best Practices

### For HTTPS (Production):
```env
APP_URL=https://your-domain.com
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=null
```

### For HTTP (Development):
```env
APP_URL=http://localhost:8000
SESSION_SECURE_COOKIE=null
SESSION_DOMAIN=null
```

## 🧪 Debugging Steps

### 1. Check Server Logs
```bash
docker exec wayak_app tail -f storage/logs/laravel.log
```

Look for:
- CSRF token mismatch errors
- Session errors
- APP_KEY errors

### 2. Check Browser Console
- Open DevTools → Console
- Look for CSP errors
- Look for CSRF token errors

### 3. Check Network Tab
- Open DevTools → Network
- Submit login form
- Check request headers:
  - `X-CSRF-TOKEN` should be present
  - `Cookie` should include session cookie
- Check response:
  - Should be 200 (success) or 302 (redirect)
  - If 419, CSRF token is invalid

### 4. Verify CSRF Token in Form
View page source, look for:
```html
<meta name="csrf-token" content="...">
```

In login form, should have:
```html
<input type="hidden" name="_token" value="...">
```

## 🎯 Quick Fix Script

Run this on production server:

```bash
cd /var/www/wayak-prod

# Backup
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Verify APP_KEY
if ! docker exec wayak_app grep -q "^APP_KEY=base64:" .env; then
  echo "⚠️  APP_KEY missing or invalid!"
  echo "Restoring from backup or generating new..."
  # Restore from latest backup
  cp .env.backup.* .env 2>/dev/null || docker exec wayak_app php artisan key:generate --force
fi

# Clear everything
docker exec wayak_app rm -f bootstrap/cache/config.php
docker exec wayak_app php artisan optimize:clear
docker exec wayak_app find storage/framework/views -name "*.php" -delete

# Restart
docker restart wayak_app

echo "✅ Done! Now clear browser cookies and try again."
```

## 📝 Common Mistakes

1. ❌ **Not clearing browser cookies** - Old session cookies conflict
2. ❌ **APP_KEY changed** - All sessions become invalid
3. ❌ **Config cache not cleared** - Old settings persist
4. ❌ **SESSION_SECURE_COOKIE mismatch** - HTTPS vs HTTP mismatch
5. ❌ **CSP blocking JavaScript** - CSRF token can't be read

## ✅ Checklist

- [ ] APP_KEY exists and is not empty
- [ ] Config cache deleted
- [ ] All caches cleared
- [ ] Compiled views deleted
- [ ] Container restarted
- [ ] Browser cookies cleared
- [ ] Browser cache cleared
- [ ] Hard refresh done
- [ ] CSRF token visible in page source
- [ ] Session cookie set in browser

## 🎯 Summary

**The fix involves:**
1. ✅ Removing CSP (done in GlobalMiddleware)
2. ✅ Preserving APP_KEY (done in workflow)
3. ✅ Clearing caches properly (done in workflow)
4. ✅ Clearing browser data (YOU must do this)

**After deployment, always:**
1. Clear browser cookies
2. Clear browser cache  
3. Hard refresh
4. Try login again

If still not working, check server logs and browser console for specific errors.

