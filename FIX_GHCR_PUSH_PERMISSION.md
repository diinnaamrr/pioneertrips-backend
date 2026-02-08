# 🔐 إصلاح مشكلة GHCR Push Permission

## المشكلة / Problem
```
ERROR: failed to push ghcr.io/osama21245/wayak-back:main: denied: permission_denied
```

**السبب:** `GITHUB_TOKEN` لا يملك صلاحية push إلى package في حساب `osama21245` إذا كان الـ repository owner مختلف.

---

## ✅ الحلول / Solutions

### الحل 1: استخدام Personal Access Token (موصى به)

1. **إنشاء Personal Access Token:**
   - اذهب إلى: https://github.com/settings/tokens
   - اضغط "Generate new token (classic)"
   - الاسم: `GHCR_Push_Token`
   - اختر scopes:
     - ✅ `write:packages` (للpush)
     - ✅ `read:packages` (للpull)
   - اضغط "Generate token"
   - **انسخ الـ Token**

2. **إضافة إلى GitHub Secrets:**
   - اذهب إلى: `https://github.com/YOUR_REPO/settings/secrets/actions`
   - اضغط "New repository secret"
   - الاسم: `GHCR_TOKEN`
   - القيمة: الصق الـ Token
   - اضغط "Add secret"

3. **التحقق:**
   - Workflow سيستخدم `GHCR_TOKEN` تلقائياً
   - إذا لم يكن موجوداً، سيستخدم `GITHUB_TOKEN` كبديل

---

### الحل 2: التأكد من Repository Owner

إذا كان الـ repository owner هو `osama21245`، يجب أن يعمل `GITHUB_TOKEN`:

```yaml
# في workflow
username: ${{ github.repository_owner }}  # سيستخدم osama21245 تلقائياً
```

لكن تم تحديثه لاستخدام `osama21245` مباشرة.

---

### الحل 3: استخدام GITHUB_TOKEN مع Repository Owner

إذا كان الـ repository في حساب `osama21245`:

```yaml
username: ${{ github.repository_owner }}
password: ${{ secrets.GITHUB_TOKEN }}
```

---

## 🔧 التعديلات المطبقة

تم تحديث workflow لـ:
1. استخدام `osama21245` كـ username
2. محاولة استخدام `GHCR_TOKEN` أولاً
3. إذا لم يكن موجوداً، استخدام `GITHUB_TOKEN` كبديل

---

## ✅ Checklist

- [ ] إنشاء Personal Access Token مع `write:packages`
- [ ] إضافة `GHCR_TOKEN` إلى GitHub Secrets
- [ ] التأكد من أن الـ repository owner صحيح
- [ ] Push كود جديد لاختبار workflow

---

**Date:** December 17, 2025  
**Status:** ✅ GHCR Push Permission Fix

