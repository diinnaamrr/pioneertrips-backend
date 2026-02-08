# 🔐 إصلاح GHCR Authentication في GitHub Actions

## المشكلة / Problem
```
Login prior to pull:
Log in with your Docker ID or email address to push and pull images from Docker Hub...
```

المشكلة: `GHCR_TOKEN` غير موجود في GitHub Secrets.

---

## ✅ الحلول / Solutions

### الحل 1: إضافة GHCR_TOKEN إلى GitHub Secrets (موصى به)

1. **إنشاء Personal Access Token:**
   - اذهب إلى: https://github.com/settings/tokens
   - اضغط "Generate new token (classic)"
   - الاسم: `GHCR_Docker_Login`
   - اختر scope: `read:packages` (للقراءة) أو `write:packages` (للقراءة والكتابة)
   - اضغط "Generate token"
   - **انسخ الـ Token** (لن تراه مرة أخرى!)

2. **إضافة إلى GitHub Secrets:**
   - اذهب إلى: `https://github.com/fassla-software/wayak/settings/secrets/actions`
   - اضغط "New repository secret"
   - الاسم: `GHCR_TOKEN`
   - القيمة: الصق الـ Token الذي نسخته
   - اضغط "Add secret"

3. **التحقق:**
   - ادفع كود جديد إلى `main` branch
   - GitHub Actions سيعمل تلقائياً

---

### الحل 2: جعل الصورة Public في GHCR (أسهل)

1. اذهب إلى: https://github.com/fassla-software/wayak/packages
2. ابحث عن package `wayak`
3. اضغط على Package settings
4. انتقل إلى "Danger Zone"
5. اضغط "Change visibility" → Make it public

**بعد ذلك:** لا حاجة لـ token، الصورة ستكون متاحة للجميع.

---

### الحل 3: استخدام GITHUB_TOKEN (محدود)

`GITHUB_TOKEN` المدمج يعمل فقط للـ repository نفسه. إذا كانت الصورة في نفس الـ repository، يمكن استخدامه:

```yaml
# في workflow
echo "${{ secrets.GITHUB_TOKEN }}" | docker login ghcr.io -u ${{ github.actor }} --password-stdin
```

**ملاحظة:** هذا يعمل فقط إذا كانت الصورة في نفس الـ repository.

---

## 🔧 التعديلات المطبقة

تم تعديل `.github/workflows/prod.yml` لـ:
1. محاولة استخدام `GHCR_TOKEN` أولاً
2. إذا لم يكن موجوداً، استخدام `GITHUB_TOKEN`
3. إذا فشل كلاهما، محاولة بناء الصورة محلياً من Dockerfile

---

## ✅ Checklist

- [ ] إنشاء Personal Access Token
- [ ] إضافة `GHCR_TOKEN` إلى GitHub Secrets
- [ ] أو جعل الصورة public في GHCR
- [ ] اختبار workflow بعد push

---

**Date:** December 17, 2025  
**Status:** ✅ GHCR Workflow Fix

