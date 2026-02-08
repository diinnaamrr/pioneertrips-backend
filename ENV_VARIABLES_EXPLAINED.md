# How Environment Variables Work in Docker Compose

## Understanding Lines 47-50 in docker-compose.prod.yml

```yaml
db:
  environment:
    MYSQL_DATABASE: ${DB_DATABASE:-wayak_db}
    MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    MYSQL_PASSWORD: ${DB_PASSWORD}
    MYSQL_USER: ${DB_USERNAME:-wayak_user}
```

## How Docker Compose Reads These Values

### Syntax: `${VARIABLE_NAME:-default_value}`

Docker Compose looks for variables in this order:

1. **`.env` file** (in the same directory as docker-compose.yml)
2. **Environment variables** (set in your shell)
3. **Default value** (after `:-` if provided)

### Example Breakdown:

#### Line 47: `MYSQL_DATABASE: ${DB_DATABASE:-wayak_db}`
- Looks for `DB_DATABASE` in `.env` file
- If found: Uses that value
- If NOT found: Uses default `wayak_db`

#### Line 48: `MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}`
- Looks for `DB_ROOT_PASSWORD` in `.env` file
- **No default value** - MUST be in `.env` file
- If missing: Container will fail to start

#### Line 49: `MYSQL_PASSWORD: ${DB_PASSWORD}`
- Looks for `DB_PASSWORD` in `.env` file
- **No default value** - MUST be in `.env` file
- If missing: Container will fail to start

#### Line 50: `MYSQL_USER: ${DB_USERNAME:-wayak_user}`
- Looks for `DB_USERNAME` in `.env` file
- If found: Uses that value
- If NOT found: Uses default `wayak_user`

---

## What Should Be in Your `.env` File on Server

Create `/var/www/wayak-prod/.env` with:

```env
# Required - No defaults
DB_ROOT_PASSWORD=your-secure-root-password
DB_PASSWORD=your-secure-database-password

# Optional - Has defaults
DB_DATABASE=wayak_db          # Default: wayak_db
DB_USERNAME=wayak_user        # Default: wayak_user

# Docker Image
IMAGE_TAG=latest

# Nginx
NGINX_PORT=80
NGINX_SSL_PORT=443
```

---

## How It Works Step-by-Step

### 1. On Your Server:

```bash
cd /var/www/wayak-prod
nano .env
```

Add:
```env
DB_DATABASE=wayak_production
DB_USERNAME=wayak_admin
DB_PASSWORD=super-secure-password-123
DB_ROOT_PASSWORD=root-secure-password-456
```

### 2. When You Run:

```bash
docker compose -f docker-compose.prod.yml up -d
```

### 3. Docker Compose Does:

1. Reads `.env` file
2. Finds `DB_DATABASE=wayak_production`
3. Replaces `${DB_DATABASE:-wayak_db}` with `wayak_production`
4. Passes `MYSQL_DATABASE=wayak_production` to MySQL container

### 4. Result:

MySQL container starts with:
- Database name: `wayak_production` (from your `.env`)
- User: `wayak_admin` (from your `.env`)
- Password: `super-secure-password-123` (from your `.env`)
- Root password: `root-secure-password-456` (from your `.env`)

---

## Visual Flow Diagram

```
┌─────────────────┐
│  .env file on   │
│     server      │
│                 │
│ DB_DATABASE=... │
│ DB_PASSWORD=... │
└────────┬────────┘
         │
         │ Docker Compose reads
         ▼
┌─────────────────────────────┐
│  docker-compose.prod.yml    │
│                             │
│ MYSQL_DATABASE:             │
│   ${DB_DATABASE:-wayak_db}  │
│                             │
│ MYSQL_PASSWORD:             │
│   ${DB_PASSWORD}            │
└────────┬────────────────────┘
         │
         │ Substitutes values
         ▼
┌─────────────────────────────┐
│   MySQL Container           │
│                             │
│ MYSQL_DATABASE=wayak_prod   │
│ MYSQL_PASSWORD=secure123    │
└─────────────────────────────┘
```

---

## Important Notes

### ✅ Variables WITH defaults (`:-default`):
- `DB_DATABASE` - Has default `wayak_db`
- `DB_USERNAME` - Has default `wayak_user`
- **Can be omitted** from `.env` if you want to use defaults

### ⚠️ Variables WITHOUT defaults:
- `DB_ROOT_PASSWORD` - **MUST** be in `.env`
- `DB_PASSWORD` - **MUST** be in `.env`
- **Will fail** if missing

### 🔒 Security:
- `.env` file should have permissions: `chmod 600 .env`
- Never commit `.env` to Git (already in `.gitignore`)
- Use strong, unique passwords for production

---

## Testing Your Setup

### Check if variables are loaded:

```bash
# On server
cd /var/www/wayak-prod

# Test variable substitution (without starting containers)
docker compose -f docker-compose.prod.yml config | grep MYSQL

# Should show your actual values, not ${...}
```

### Verify MySQL container:

```bash
# Check if container started successfully
docker logs wayak_db

# Connect to MySQL
docker exec -it wayak_db mysql -uwayak_user -p
# Enter password from your .env file
```

---

## Troubleshooting

### Error: "Variable is not set"
- **Problem**: Variable in docker-compose.yml has no default and is missing from `.env`
- **Solution**: Add the variable to `.env` file

### Error: "Container fails to start"
- **Problem**: Required variables missing or incorrect
- **Solution**: Check `.env` file has all required variables

### Variables not updating:
- **Problem**: Changed `.env` but containers still using old values
- **Solution**: Restart containers: `docker compose -f docker-compose.prod.yml up -d`

