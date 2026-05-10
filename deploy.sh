#!/bin/bash
# ============================================================
# SMM Elite — Oracle Cloud ARM Ubuntu 22.04 Deploy Script
# Run as: bash deploy.sh
# ============================================================

set -e
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
info()    { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${GREEN}[OK]${NC}   $1"; }
warn()    { echo -e "${YELLOW}[WARN]${NC} $1"; }
error()   { echo -e "${RED}[ERR]${NC}  $1"; exit 1; }

# ── Config — edit these before running ──────────────────────
GITHUB_REPO="https://github.com/ALEXINXIDE/smm-panel.git"
APP_DIR="/var/www/smm-panel"
DB_NAME="smm_panel"
DB_USER="smm_user"
DB_PASS="$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 24)"
REDIS_PASS="$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 24)"
APP_KEY=""   # leave blank — generated automatically
PHP_VER="8.2"
# ────────────────────────────────────────────────────────────

PUBLIC_IP=$(curl -s ifconfig.me 2>/dev/null || curl -s api.ipify.org)
info "Detected public IP: $PUBLIC_IP"

# ── 1. System update ─────────────────────────────────────────
info "Updating system packages..."
sudo apt-get update -qq
sudo apt-get upgrade -y -qq
sudo apt-get install -y -qq \
    curl wget git unzip zip software-properties-common \
    apt-transport-https ca-certificates gnupg lsb-release \
    ufw fail2ban htop
success "System updated"

# ── 2. PHP 8.2 ───────────────────────────────────────────────
info "Installing PHP ${PHP_VER}..."
sudo add-apt-repository -y ppa:ondrej/php >/dev/null 2>&1
sudo apt-get update -qq
sudo apt-get install -y -qq \
    php${PHP_VER}-fpm \
    php${PHP_VER}-cli \
    php${PHP_VER}-pgsql \
    php${PHP_VER}-mbstring \
    php${PHP_VER}-xml \
    php${PHP_VER}-curl \
    php${PHP_VER}-zip \
    php${PHP_VER}-bcmath \
    php${PHP_VER}-redis \
    php${PHP_VER}-gd \
    php${PHP_VER}-intl \
    php${PHP_VER}-opcache \
    php${PHP_VER}-tokenizer \
    php${PHP_VER}-ctype \
    php${PHP_VER}-fileinfo
success "PHP ${PHP_VER} installed"

# ── 3. Configure OPcache ─────────────────────────────────────
info "Configuring OPcache + JIT..."
sudo tee /etc/php/${PHP_VER}/fpm/conf.d/99-opcache.ini >/dev/null << EOF
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
opcache.jit_buffer_size=128M
opcache.jit=tracing
EOF
success "OPcache configured"

# ── 4. Composer ──────────────────────────────────────────────
info "Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --quiet
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
success "Composer installed"

# ── 5. Node.js 20 ────────────────────────────────────────────
info "Installing Node.js 20..."
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - >/dev/null 2>&1
sudo apt-get install -y -qq nodejs
success "Node.js $(node -v) installed"

# ── 6. PostgreSQL 16 ─────────────────────────────────────────
info "Installing PostgreSQL 16..."
sudo sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
wget -qO- https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo tee /etc/apt/trusted.gpg.d/pgdg.asc >/dev/null
sudo apt-get update -qq
sudo apt-get install -y -qq postgresql-16
sudo systemctl enable postgresql --quiet
sudo systemctl start postgresql

# Create DB and user
info "Creating database..."
sudo -u postgres psql -q << PSQL
CREATE DATABASE ${DB_NAME};
CREATE USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASS}';
GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};
ALTER DATABASE ${DB_NAME} OWNER TO ${DB_USER};
PSQL
success "PostgreSQL 16 ready — DB: ${DB_NAME}, User: ${DB_USER}"

# ── 7. Redis ─────────────────────────────────────────────────
info "Installing Redis 7..."
sudo add-apt-repository -y ppa:redislabs/redis >/dev/null 2>&1
sudo apt-get update -qq
sudo apt-get install -y -qq redis-server
sudo tee -a /etc/redis/redis.conf >/dev/null << EOF

requirepass ${REDIS_PASS}
maxmemory 512mb
maxmemory-policy allkeys-lru
appendonly yes
EOF
sudo systemctl enable redis-server --quiet
sudo systemctl restart redis-server
success "Redis 7 ready"

# ── 8. Nginx ─────────────────────────────────────────────────
info "Installing Nginx..."
sudo apt-get install -y -qq nginx
sudo systemctl enable nginx --quiet

# Write Nginx config
sudo tee /etc/nginx/sites-available/smm-panel >/dev/null << EOF
server {
    listen 80;
    server_name ${PUBLIC_IP} _;

    root ${APP_DIR}/public;
    index index.php;

    client_max_body_size 64M;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css application/json application/javascript
               text/xml application/xml text/javascript;

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        try_files \$uri =404;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php${PHP_VER}-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
    }

    location ~ /\.(?!well-known).* { deny all; }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
}
EOF

sudo ln -sf /etc/nginx/sites-available/smm-panel /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
success "Nginx configured"

# ── 9. PHP-FPM tuning ────────────────────────────────────────
info "Tuning PHP-FPM for 24GB RAM..."
sudo tee /etc/php/${PHP_VER}/fpm/pool.d/www.conf >/dev/null << EOF
[www]
user = www-data
group = www-data
listen = /var/run/php/php${PHP_VER}-fpm.sock
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
request_terminate_timeout = 300
EOF
sudo systemctl restart php${PHP_VER}-fpm
success "PHP-FPM tuned (50 workers)"

# ── 10. Clone repo ───────────────────────────────────────────
info "Cloning repository..."
sudo mkdir -p ${APP_DIR}
sudo chown -R $USER:$USER /var/www
git clone ${GITHUB_REPO} ${APP_DIR}
cd ${APP_DIR}
success "Repo cloned"

# ── 11. Composer install ─────────────────────────────────────
info "Installing PHP dependencies..."
cd ${APP_DIR}
composer install --no-dev --optimize-autoloader --quiet
success "Composer dependencies installed"

# ── 12. Node / Vite build ────────────────────────────────────
info "Building frontend assets..."
cd ${APP_DIR}
npm ci --silent
npm run build
success "Frontend assets built"

# ── 13. Generate .env ────────────────────────────────────────
info "Generating .env file..."
cd ${APP_DIR}
cp .env.example .env 2>/dev/null || touch .env

APP_KEY_GEN=$(php artisan key:generate --show 2>/dev/null || openssl rand -base64 32)

cat > ${APP_DIR}/.env << EOF
APP_NAME="SMM Elite"
APP_ENV=production
APP_KEY=base64:${APP_KEY_GEN}
APP_DEBUG=false
APP_URL=http://${PUBLIC_IP}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=${REDIS_PASS}
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@smmelite.com
MAIL_FROM_NAME="SMM Elite"

BROADCAST_DRIVER=log
FILESYSTEM_DISK=local

SESSION_LIFETIME=120
SESSION_ENCRYPT=false

EXCHANGE_RATE_API=https://open.er-api.com/v6/latest/USD
EOF

# Regenerate key properly
php artisan key:generate --force
success ".env generated"

# ── 14. Storage & permissions ────────────────────────────────
info "Setting permissions..."
sudo mkdir -p ${APP_DIR}/storage/framework/{sessions,views,cache/data}
sudo mkdir -p ${APP_DIR}/storage/logs
sudo mkdir -p ${APP_DIR}/bootstrap/cache
sudo chown -R www-data:www-data ${APP_DIR}/storage ${APP_DIR}/bootstrap/cache
sudo chmod -R 775 ${APP_DIR}/storage ${APP_DIR}/bootstrap/cache
sudo chown -R $USER:www-data ${APP_DIR}
sudo chmod -R 750 ${APP_DIR}
sudo chmod -R 775 ${APP_DIR}/storage ${APP_DIR}/bootstrap/cache
success "Permissions set"

# ── 15. Laravel setup ────────────────────────────────────────
info "Running Laravel setup..."
cd ${APP_DIR}
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
success "Laravel setup complete"

# ── 16. Queue worker (systemd) ───────────────────────────────
info "Setting up queue worker service..."
sudo tee /etc/systemd/system/smm-worker.service >/dev/null << EOF
[Unit]
Description=SMM Panel Queue Worker
After=network.target postgresql.service redis-server.service

[Service]
User=www-data
Group=www-data
WorkingDirectory=${APP_DIR}
ExecStart=/usr/bin/php artisan queue:work redis \
    --queue=payments,default,emails,notifications \
    --sleep=3 \
    --tries=3 \
    --timeout=90 \
    --max-jobs=500 \
    --max-time=3600
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal
SyslogIdentifier=smm-worker

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload
sudo systemctl enable smm-worker
sudo systemctl start smm-worker
success "Queue worker running"

# ── 17. Scheduler (systemd timer) ───────────────────────────
info "Setting up Laravel scheduler..."
sudo tee /etc/systemd/system/smm-scheduler.service >/dev/null << EOF
[Unit]
Description=SMM Panel Scheduler

[Service]
Type=oneshot
User=www-data
WorkingDirectory=${APP_DIR}
ExecStart=/usr/bin/php artisan schedule:run
EOF

sudo tee /etc/systemd/system/smm-scheduler.timer >/dev/null << EOF
[Unit]
Description=Run SMM Panel Scheduler every minute

[Timer]
OnCalendar=*:*:00
Persistent=true

[Install]
WantedBy=timers.target
EOF

sudo systemctl daemon-reload
sudo systemctl enable smm-scheduler.timer
sudo systemctl start smm-scheduler.timer
success "Scheduler running every minute"

# ── 18. Firewall ─────────────────────────────────────────────
info "Configuring firewall..."
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable
success "Firewall configured (ports 22, 80, 443)"

# ── 19. Fail2ban ─────────────────────────────────────────────
info "Configuring Fail2ban..."
sudo tee /etc/fail2ban/jail.local >/dev/null << EOF
[DEFAULT]
bantime  = 3600
findtime = 600
maxretry = 5
destemail = root@localhost

[sshd]
enabled = true

[nginx-http-auth]
enabled = true
EOF
sudo systemctl enable fail2ban --quiet
sudo systemctl restart fail2ban
success "Fail2ban enabled"

# ── 20. Oracle Cloud firewall (iptables) ─────────────────────
info "Opening Oracle Cloud iptables rules..."
# Oracle has its own iptables rules on top of UFW
sudo iptables -I INPUT -p tcp --dport 80 -j ACCEPT
sudo iptables -I INPUT -p tcp --dport 443 -j ACCEPT
sudo netfilter-persistent save 2>/dev/null || \
    sudo apt-get install -y -qq iptables-persistent && \
    sudo netfilter-persistent save
success "Oracle iptables rules saved"

# ── Done ─────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║           SMM Elite deployed successfully!           ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${BLUE}URL:${NC}           http://${PUBLIC_IP}"
echo -e "  ${BLUE}App directory:${NC} ${APP_DIR}"
echo -e "  ${BLUE}DB name:${NC}       ${DB_NAME}"
echo -e "  ${BLUE}DB user:${NC}       ${DB_USER}"
echo -e "  ${BLUE}DB password:${NC}   ${DB_PASS}"
echo -e "  ${BLUE}Redis password:${NC} ${REDIS_PASS}"
echo ""
echo -e "${YELLOW}IMPORTANT — Save these credentials somewhere safe!${NC}"
echo -e "${YELLOW}Also add them to your Railway env if needed.${NC}"
echo ""
echo -e "  Services status:"
echo -e "    nginx:       $(sudo systemctl is-active nginx)"
echo -e "    php-fpm:     $(sudo systemctl is-active php${PHP_VER}-fpm)"
echo -e "    postgresql:  $(sudo systemctl is-active postgresql)"
echo -e "    redis:       $(sudo systemctl is-active redis-server)"
echo -e "    smm-worker:  $(sudo systemctl is-active smm-worker)"
echo -e "    scheduler:   $(sudo systemctl is-active smm-scheduler.timer)"
echo ""
echo -e "  Useful commands:"
echo -e "    sudo systemctl status smm-worker"
echo -e "    sudo tail -f ${APP_DIR}/storage/logs/laravel.log"
echo -e "    sudo nginx -t && sudo systemctl reload nginx"
echo ""
