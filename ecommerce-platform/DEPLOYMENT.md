# E-Commerce Platform - Deployment Guide

## Production Environment Setup

### 1. Server Requirements

**Minimum Specs:**
- CPU: 2 cores
- RAM: 2 GB
- Storage: 20 GB SSD
- OS: Ubuntu 20.04+ / CentOS 8+

**Recommended Specs:**
- CPU: 4+ cores
- RAM: 8+ GB
- Storage: 100 GB SSD
- OS: Ubuntu 22.04 LTS

### 2. Server Setup

#### Install Dependencies

```bash
sudo apt update && sudo apt upgrade -y

# PHP
sudo apt install php8.1-cli php8.1-fpm php8.1-mysql \
  php8.1-redis php8.1-curl php8.1-xml php8.1-mbstring -y

# MySQL
sudo apt install mysql-server -y

# Node.js
curl -sL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# Composer
curl -sS https://getcomposer.org/installer | sudo php -- \
  --install-dir=/usr/local/bin --filename=composer

# Nginx
sudo apt install nginx -y

# Redis
sudo apt install redis-server -y

# Git
sudo apt install git -y
```

### 3. Domain & SSL Setup

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Generate SSL certificate
sudo certbot certonly --standalone -d yourdomain.com

# Auto-renewal
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

### 4. Database Setup

```bash
# Create database and user
mysql -u root -p << EOF
CREATE DATABASE ecommerce_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ecommerce'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON ecommerce_prod.* TO 'ecommerce'@'localhost';
FLUSH PRIVILEGES;
EOF
```

### 5. Application Deployment

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/deeveelop3r/ecommerce-platform.git
cd ecommerce-platform/backend

# Install PHP dependencies
sudo composer install --no-dev --optimize-autoloader

# Set permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 storage bootstrap/cache
sudo chmod -R 775 storage/logs

# Create .env file
sudo cp .env.example .env
sudo nano .env  # Configure production settings

# Generate key
sudo php artisan key:generate

# Run migrations
sudo php artisan migrate --force

# Seed data (if needed)
sudo php artisan db:seed --force

# Build frontend
cd ../frontend
npm install --production
npm run build
```

### 6. Nginx Configuration

Create `/etc/nginx/sites-available/ecommerce`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    root /var/www/ecommerce-platform;
    index index.php index.html;

    # Frontend
    location / {
        try_files $uri $uri/ /index.html;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Static files with caching
    location ~* \.(js|css|png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # API
    location /api/ {
        proxy_pass http://localhost:8000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }

    # Backend PHP
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /storage/ {
        deny all;
    }
}
```

Enable configuration:
```bash
sudo ln -s /etc/nginx/sites-available/ecommerce /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7. Process Manager Setup

Install Supervisor:
```bash
sudo apt install supervisor -y
```

Create `/etc/supervisor/conf.d/ecommerce.conf`:

```ini
[program:ecommerce-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ecommerce-platform/backend/artisan queue:work redis
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/ecommerce-queue.log

[program:ecommerce-websocket]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ecommerce-platform/backend/artisan websocket:serve
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/ecommerce-websocket.log
```

Start Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### 8. Monitoring & Logging

```bash
# Check logs
tail -f storage/logs/laravel.log

# Monitor processes
htop

# Check disk space
df -h

# Database backups
mysqldump -u ecommerce -p ecommerce_prod > /backups/ecommerce_$(date +%Y%m%d).sql
```

### 9. Production Checklist

- [ ] SSL certificate installed
- [ ] Database backup configured
- [ ] Redis cache running
- [ ] Payment gateway configured
- [ ] Email service configured
- [ ] Monitoring alerts set up
- [ ] Rate limiting configured
- [ ] CORS properly configured
- [ ] Security headers enabled
- [ ] Logs monitored and rotated

### 10. CI/CD Pipeline

Using GitHub Actions:

```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Deploy to server
      run: |
        mkdir -p ~/.ssh
        echo "${{ secrets.DEPLOY_KEY }}" > ~/.ssh/deploy_key
        chmod 600 ~/.ssh/deploy_key
        ssh-keyscan -H ${{ secrets.SERVER_IP }} >> ~/.ssh/known_hosts
        
        ssh -i ~/.ssh/deploy_key deploy@${{ secrets.SERVER_IP }} 'cd /var/www/ecommerce-platform && git pull && cd backend && composer install && php artisan migrate --force'
```

## Performance Optimization

1. **Database**
   - Add indexes on frequently queried columns
   - Use query caching
   - Optimize slow queries

2. **Caching**
   - Use Redis for session storage
   - Cache product data
   - Cache API responses

3. **CDN**
   - Upload images to CDN
   - Use CloudFlare or similar service

4. **Frontend**
   - Minify CSS/JS
   - Lazy load images
   - Use compression

## Rollback Procedure

```bash
# Check git history
git log --oneline -10

# Rollback to previous version
git revert <commit-hash>
git push

# Or switch to previous tag
git checkout tags/v1.0.0
php artisan migrate:rollback
```

## Support

For deployment issues, contact: devops@ecommerce-platform.com
