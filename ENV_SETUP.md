# Environment Variables Setup Guide

## Why NOT include .env in Docker image?

### ❌ Security Risks:
1. **Secrets in image layers**: If you `COPY .env` in Dockerfile, secrets are stored in image layers
2. **Registry exposure**: Images pushed to GHCR contain those secrets
3. **Immutable**: Can't change secrets without rebuilding entire image
4. **Version control**: Even if removed later, secrets remain in image history

### ✅ Best Practices:

## Option 1: Use .env file on server (Current Setup) ✅

**Pros:**
- Easy to manage
- Standard Laravel approach
- Can change values without rebuilding

**Cons:**
- Need to manage .env file on server
- Must ensure .env is not in Git

**Setup:**
```bash
# On server, create .env file
cd /var/www/wayak-prod
nano .env
# Add all your variables
```

The docker-compose.yml uses `env_file: - .env` to load it at runtime.

---

## Option 2: Environment Variables Only (No .env file) 🔒

**Pros:**
- Most secure
- No file to manage
- Works with secrets managers (AWS Secrets Manager, HashiCorp Vault, etc.)

**Cons:**
- More setup required
- Need to set many variables

**Setup:**

### On Server - Set environment variables:

```bash
# Option A: Export in shell session
export APP_NAME="Wayak"
export APP_ENV="production"
export APP_KEY="base64:your-key"
export DB_DATABASE="wayak_db"
export DB_USERNAME="wayak_user"
export DB_PASSWORD="secure-password"
# ... etc

# Then run docker-compose
docker compose -f docker-compose.prod.env-only.yml up -d
```

### Option B: Use systemd environment file (Recommended for production)

Create `/etc/systemd/system/wayak.env`:
```bash
APP_NAME=Wayak
APP_ENV=production
APP_KEY=base64:your-key
DB_DATABASE=wayak_db
DB_USERNAME=wayak_user
DB_PASSWORD=secure-password
# ... all other variables
```

Then source it:
```bash
source /etc/systemd/system/wayak.env
docker compose -f docker-compose.prod.env-only.yml up -d
```

### Option C: Use Docker secrets (Most secure for production)

```bash
# Create secrets
echo "your-db-password" | docker secret create db_password -
echo "your-app-key" | docker secret create app_key -

# Use in docker-compose (requires Docker Swarm)
```

---

## Option 3: Use Secrets Manager (Enterprise) 🏢

### AWS Secrets Manager:
```bash
# Install AWS CLI
# Fetch secrets and set as env vars
aws secretsmanager get-secret-value --secret-id wayak/prod | jq -r .SecretString | while IFS='=' read key value; do export $key=$value; done
```

### HashiCorp Vault:
```bash
vault kv get -format=json secret/wayak/prod | jq -r '.data.data | to_entries | .[] | "\(.key)=\(.value)"' > .env
```

---

## Recommendation

**For most projects**: Use **Option 1** (`.env` file on server)
- Simple and secure enough
- Standard Laravel practice
- Easy to manage

**For high-security requirements**: Use **Option 2** (Environment variables)
- No files to manage
- Works with secrets managers
- Better for compliance

**Current setup uses Option 1** - which is the standard approach and perfectly secure as long as:
- ✅ `.env` is in `.gitignore`
- ✅ `.env` is only on server (not in image)
- ✅ Server has proper file permissions (chmod 600 .env)
- ✅ Only authorized users can access server

---

## Quick Security Checklist

- [ ] `.env` is in `.gitignore`
- [ ] `.env` is NOT in Dockerfile
- [ ] `.env` file permissions: `chmod 600 .env`
- [ ] Server SSH keys are secure
- [ ] Database passwords are strong
- [ ] Regular backups of database
- [ ] Monitor access logs

