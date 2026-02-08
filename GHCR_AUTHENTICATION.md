# GHCR Authentication Setup (Secure Method)

## Why NOT Make Image Public?

Making Docker images public can be a security risk if:
- ❌ Your repository is private
- ❌ Image contains any sensitive information
- ❌ You want to control who can pull your images
- ❌ You're building commercial/proprietary software

**However**, if your code is already public (open source), making the image public is usually fine since:
- ✅ Application code is already visible
- ✅ Secrets are in `.env` (not in image)
- ✅ No sensitive data in the image itself

---

## Secure Solution: Use Personal Access Token (PAT)

### Step 1: Create GitHub Personal Access Token

1. Go to: https://github.com/settings/tokens
2. Click **"Generate new token (classic)"**
3. Give it a name: `GHCR_Docker_Login`
4. Select expiration: Choose based on your needs (90 days, 1 year, or no expiration)
5. Select scopes:
   - ✅ `read:packages` (to pull images)
   - ✅ `write:packages` (if you want to push from server too)
6. Click **"Generate token"**
7. **Copy the token immediately** (you won't see it again!)

### Step 2: Add Token to GitHub Secrets

1. Go to your repository → **Settings** → **Secrets and variables** → **Actions**
2. Click **"New repository secret"**
3. Name: `GHCR_TOKEN`
4. Value: Paste your Personal Access Token
5. Click **"Add secret"**

### Step 3: Login on Server (One-Time Setup)

SSH to your server and login to GHCR:

```bash
ssh root@31.97.53.11

# Login to GHCR (replace YOUR_TOKEN with your actual token)
echo "YOUR_GITHUB_PERSONAL_ACCESS_TOKEN" | docker login ghcr.io -u osama21245 --password-stdin

# Verify login
docker pull ghcr.io/osama21245/wayak:latest
```

**Note**: Docker login persists, so you only need to do this once (or if you change the token).

---

## Alternative: Use GitHub Actions Token (Automatic)

The workflow already uses `GITHUB_TOKEN` for pushing images. For pulling on the server, you have two options:

### Option A: Use PAT (Recommended)
- More control
- Can revoke independently
- Works even if repository access changes

### Option B: Make Image Public
- Simplest
- No authentication needed
- Fine if code is already public

---

## Security Best Practices

### ✅ DO:
- Use Personal Access Tokens with minimal scopes
- Set token expiration dates
- Rotate tokens regularly
- Store tokens in GitHub Secrets (never in code)
- Use different tokens for dev/prod if needed

### ❌ DON'T:
- Commit tokens to Git
- Share tokens publicly
- Use tokens with excessive permissions
- Store tokens in plain text files
- Use the same token everywhere

---

## Workflow Authentication

The workflows are now configured to:
1. **Build & Push**: Uses `GITHUB_TOKEN` (automatic, provided by GitHub Actions)
2. **Deploy & Pull**: Uses `GHCR_TOKEN` secret (your Personal Access Token)

If `GHCR_TOKEN` is not set, the workflow will try to pull without authentication (works if image is public).

---

## Quick Setup Checklist

- [ ] Create GitHub Personal Access Token
- [ ] Add `GHCR_TOKEN` to GitHub Secrets
- [ ] Login to GHCR on server: `echo "TOKEN" | docker login ghcr.io -u USERNAME --password-stdin`
- [ ] Test: `docker pull ghcr.io/osama21245/wayak:latest`
- [ ] Push to trigger workflow

---

## Troubleshooting

### "unauthorized" error
- Check if token has `read:packages` scope
- Verify token hasn't expired
- Make sure you're using the correct username

### "denied" error
- Check if image exists in GHCR
- Verify package permissions
- Make sure token has correct scopes

### Token expired
- Generate new token
- Update `GHCR_TOKEN` secret
- Re-login on server

