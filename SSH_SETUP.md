# SSH Authentication Setup Guide

## Option 1: SSH Key Authentication (Recommended) 🔐

### Step 1: Generate SSH Key Pair

On your **local machine** (Mac):

```bash
# Generate a new SSH key (if you don't have one)
ssh-keygen -t ed25519 -C "github-actions-wayak" -f ~/.ssh/wayak_deploy

# Or use RSA if ed25519 is snot supported
ssh-keygen -t rsa -b 4096 -C "github-actions-wayak" -f ~/.ssh/wayak_deploy

# You'll be asked for a passphrase (optional, but recommended)
# Press Enter twice if you don't want a passphrase
```

This creates:
- `~/.ssh/wayak_deploy` (private key) - **Keep this SECRET**
- `~/.ssh/wayak_deploy.pub` (public key) - This goes on the server

### Step 2: Copy Public Key to Server

```bash
# Copy public key to server
ssh-copy-id -i ~/.ssh/wayak_deploy.pub root@31.97.53.11

# Or manually:
cat ~/.ssh/wayak_deploy.pub | ssh root@31.97.53.11 "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys && chmod 700 ~/.ssh"
```

### Step 3: Test SSH Key Connection

```bash
# Test connection without password
ssh -i ~/.ssh/wayak_deploy root@31.97.53.11

# If it works, you won't be asked for a password!
```

### Step 4: Add Private Key to GitHub Secrets

1. **Copy your private key**:
```bash
# On your Mac, display the private key
cat ~/.ssh/wayak_deploy
```

2. **Copy the ENTIRE output** (including `-----BEGIN OPENSSH PRIVATE KEY-----` and `-----END OPENSSH PRIVATE KEY-----`)

3. **Add to GitHub Secrets**:
   - Go to your GitHub repository
   - Settings → Secrets and variables → Actions
   - Click "New repository secret"
   - Name: `SSH_KEY` (for dev) or `SSH_KEY_PROD` (for production)
   - Value: Paste the entire private key
   - Click "Add secret"

4. **Add other secrets**:
   - `SSH_HOST` = `31.97.53.11`
   - `SSH_USER` = `root`
   - `SSH_PORT` = `22` (optional, defaults to 22)

### Step 5: Update GitHub Secrets

For **Dev Environment**:
- `SSH_HOST` = `31.97.53.11`
- `SSH_USER` = `root`
- `SSH_KEY` = (your private key from step 4)
- `SSH_PORT` = `22` (optional)

For **Production Environment**:
- `SSH_HOST_PROD` = `31.97.53.11` (or different server)
- `SSH_USER_PROD` = `root`
- `SSH_KEY_PROD` = (your private key - can be same or different)
- `SSH_PORT_PROD` = `22` (optional)

---

## Option 2: Password Authentication (Less Secure) ⚠️

If you **must** use password authentication, you can update the workflows. However, this is **NOT recommended** for security reasons.

### Update Workflows to Use Password

I'll create alternative workflow files that support password authentication.

---

## Troubleshooting

### "Permission denied (publickey)"
- Make sure public key is in `~/.ssh/authorized_keys` on server
- Check permissions: `chmod 600 ~/.ssh/authorized_keys`
- Check SSH config: `cat ~/.ssh/config`

### "Host key verification failed"
- Remove old host key: `ssh-keygen -R 31.97.53.11`
- Or add to known hosts: `ssh-keyscan 31.97.53.11 >> ~/.ssh/known_hosts`

### "Connection refused"
- Check if SSH service is running: `systemctl status sshd`
- Check firewall: `ufw status`
- Verify port 22 is open

---

## Security Best Practices

1. ✅ **Use SSH keys** instead of passwords
2. ✅ **Use different keys** for dev and production
3. ✅ **Protect private keys** - never commit to Git
4. ✅ **Use passphrases** on SSH keys
5. ✅ **Limit SSH access** to specific IPs if possible
6. ✅ **Disable password authentication** on server after setting up keys

### Disable Password Authentication (After Setting Up Keys)

On your server:
```bash
# Edit SSH config
sudo nano /etc/ssh/sshd_config

# Change these lines:
PasswordAuthentication no
PubkeyAuthentication yes

# Restart SSH service
sudo systemctl restart sshd
```

**Warning**: Make sure SSH key works before disabling password auth!

