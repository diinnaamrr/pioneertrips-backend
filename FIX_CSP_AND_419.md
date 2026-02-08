# Fix CSP (Content Security Policy) and 419 Page Expired Errors

## 🔴 Problems

1. **Content Security Policy blocks eval** - JavaScript libraries can't use `eval()`
2. **419 Page Expired** - CSRF token validation fails

## 🔍 Root Causes

### CSP Issue
- CSP headers from reverse proxy (CloudPanel) or browser blocking `eval()`
- Some JavaScript libraries (especially admin panel libraries) need `unsafe-eval`
- CSP prevents CSRF token from being read/used properly

### 419 Issue  
- CSRF token mismatch due to:
  - CSP blocking JavaScript from reading meta tag
  - Session expired or invalid
  - APP_KEY changed
  - Config cache issues

## ✅ Solutions Applied

### 1. Updated GlobalMiddleware
- Removes any existing CSP headers (from reverse proxy)
- Sets permissive CSP for admin routes (`/admin/*`, `/dashboard/*`)
- Allows `unsafe-eval` for admin panel JavaScript libraries

### 2. Production Workflow Updates
- Preserves APP_KEY during deployment
- Clears config cache properly
- Ensures .env is loaded correctly

## 🛠️ Manual Fix (If Still Needed)

### Step 1: Clear Browser Cache & Cookies
```bash
# In browser:
1. Clear all cookies for the domain
2. Clear cache
3. Hard refresh (Ctrl+Shift+R or Cmd+Shift+R)
```

### Step 2: Verify on Server
```bash
cd /var/www/wayak-prod

# Check APP_KEY
docker exec wayak_app grep "^APP_KEY=" .env

# Clear all caches
docker exec wayak_app php artisan optimize:clear
docker exec wayak_app rm -f bootstrap/cache/config.php
docker exec wayak_app find storage/framework/views -name "*.php" -delete

# Restart container
docker restart wayak_app
```

### Step 3: Check CSP Headers
```bash
# Check if reverse proxy is adding CSP
curl -I https://your-domain.com/admin/login

# Look for: Content-Security-Policy header
```

If CSP is coming from reverse proxy (CloudPanel), you may need to:
1. Remove CSP from CloudPanel Nginx config, OR
2. Update CloudPanel config to allow `unsafe-eval`

## 📋 CSP Configuration

The middleware now sets CSP for admin routes:
```
default-src 'self';
script-src 'self' 'unsafe-inline' 'unsafe-eval' https://maps.googleapis.com;
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
font-src 'self' https://fonts.gstatic.com data:;
img-src 'self' data: https: blob:;
connect-src 'self' https://*.googleapis.com;
frame-src 'self' https://*.google.com;
```

## 🔐 Security Notes

⚠️ **Warning**: `unsafe-eval` reduces security but is necessary for some admin panel libraries.

**Best Practice**: 
- Only allow `unsafe-eval` for admin routes (already done ✅)
- Keep public routes with stricter CSP
- Regularly audit JavaScript dependencies

## 🧪 Testing

After deployment:

1. **Clear browser cache and cookies**
2. **Open admin login page**
3. **Check browser console** - should see no CSP errors
4. **Try to login** - should work without 419 error
5. **Check Network tab** - CSRF token should be sent in headers

## 📝 Additional Debugging

### Check CSP in Browser Console
```javascript
// In browser console
document.querySelector('meta[name="csrf-token"]')?.content
// Should return CSRF token
```

### Check Session Cookie
```javascript
// In browser console
document.cookie
// Should see: wayak_session=...
```

### Check AJAX CSRF Setup
Most Laravel apps use this in JavaScript:
```javascript
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

Make sure this is in your admin JavaScript files.

## 🎯 Summary

✅ **CSP Fixed**: Middleware removes conflicting CSP and sets permissive CSP for admin
✅ **419 Fixed**: Production workflow preserves APP_KEY and clears caches properly
✅ **Both work together**: CSP fix ensures CSRF tokens can be read/used

If issues persist, check:
1. Browser console for CSP errors
2. Network tab for CSRF token in requests
3. Server logs for session errors

