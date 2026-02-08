# Import Database on Server

## Step 1: Upload SQL File to Server

From your **local machine**, upload the SQL file:

```bash
# From your local machine (Mac)
cd /Users/osamagamil/Desktop/wayak-app.com

# Upload SQL file to server
scp 127_0_0_1.sql root@31.97.53.11:/var/www/wayak-prod/127_0_0_1.sql
```

## Step 2: Import SQL File into Database

SSH to your server and import:

```bash
# SSH to server
ssh root@31.97.53.11

# Go to project directory
cd /var/www/wayak-prod

# Import SQL file into database
docker exec -i wayak_db mysql -uroot -proot < 127_0_0_1.sql

# Or if you need to specify database name:
docker exec -i wayak_db mysql -uroot -proot wayak < 127_0_0_1.sql
```

## Alternative: Import via MySQL Command Line

```bash
# SSH to server
ssh root@31.97.53.11
cd /var/www/wayak-prod

# Connect to MySQL
docker exec -it wayak_db mysql -uroot -proot

# In MySQL prompt:
mysql> DROP DATABASE IF EXISTS wayak;
mysql> CREATE DATABASE wayak CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql> EXIT;

# Import SQL file
docker exec -i wayak_db mysql -uroot -proot wayak < 127_0_0_1.sql
```

## Verify Import

```bash
# Check databases
docker exec -it wayak_db mysql -uroot -proot -e "SHOW DATABASES;"

# Check tables in wayak database
docker exec -it wayak_db mysql -uroot -proot -e "USE wayak; SHOW TABLES;" | head -20

# Check specific table (e.g., business_settings)
docker exec -it wayak_db mysql -uroot -proot -e "USE wayak; SELECT COUNT(*) FROM business_settings;"
```

## Update .env File

Make sure your `.env` file uses the correct database:

```bash
cd /var/www/wayak-prod
nano .env
```

Check these lines:
```env
DB_DATABASE=wayak
DB_USERNAME=wayak_user
DB_PASSWORD=root
DB_ROOT_PASSWORD=root
```

## One-Liner (Complete Process)

From your **local machine**:

```bash
# Upload and import in one go
cd /Users/osamagamil/Desktop/wayak-app.com
scp 127_0_0_1.sql root@31.97.53.11:/tmp/ && \
ssh root@31.97.53.11 "cd /var/www/wayak-prod && docker exec -i wayak_db mysql -uroot -proot < /tmp/127_0_0_1.sql && rm /tmp/127_0_0_1.sql"
```

## Troubleshooting

### "Access denied" error
- Make sure you're using the correct root password
- Check `.env` file has `DB_ROOT_PASSWORD=root`

### "Database doesn't exist"
- The SQL file might create the database, or you need to create it first:
```bash
docker exec -i wayak_db mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS wayak CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### "Table already exists"
- Drop and recreate database:
```bash
docker exec -i wayak_db mysql -uroot -proot -e "DROP DATABASE IF EXISTS wayak; CREATE DATABASE wayak CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
docker exec -i wayak_db mysql -uroot -proot wayak < 127_0_0_1.sql
```

