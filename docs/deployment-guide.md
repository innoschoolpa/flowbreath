# SQL Resource Management System - 배포 가이드

## 시스템 요구사항

### 서버 요구사항
- CPU: 2코어 이상 (권장: 4코어)
- 메모리: 4GB 이상 (권장: 8GB)
- 디스크: 20GB 이상 (SSD 권장)
- OS: Ubuntu 20.04 LTS 이상

### 소프트웨어 요구사항
- PHP 8.0+
- MySQL 5.7+
- Nginx 1.18+
- Redis 6.0+ (선택사항)
- Composer 2.0+
- Git

## 설치 단계

### 1. 시스템 업데이트
```bash
sudo apt update
sudo apt upgrade -y
```

### 2. PHP 설치
```bash
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.0-fpm php8.0-mysql php8.0-mbstring php8.0-xml php8.0-curl php8.0-zip php8.0-gd php8.0-redis
```

### 3. MySQL 설치
```bash
sudo apt install mysql-server
sudo mysql_secure_installation
```

### 4. Nginx 설치
```bash
sudo apt install nginx
```

### 5. Redis 설치 (선택사항)
```bash
sudo apt install redis-server
sudo systemctl enable redis-server
```

### 6. Composer 설치
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## 애플리케이션 배포

### 1. 코드 배포
```bash
# 프로젝트 디렉토리 생성
sudo mkdir -p /var/www/flowbreath
sudo chown -R $USER:$USER /var/www/flowbreath

# 코드 클론
git clone https://github.com/your-repo/flowbreath.git /var/www/flowbreath
cd /var/www/flowbreath

# 의존성 설치
composer install --no-dev --optimize-autoloader
```

### 2. 환경 설정
```bash
# 환경 설정 파일 복사
cp .env.example .env

# 환경 설정 편집
nano .env

# 애플리케이션 키 생성
php artisan key:generate
```

### 3. 데이터베이스 설정
```bash
# 데이터베이스 생성
mysql -u root -p
CREATE DATABASE flowbreath;
CREATE USER 'flowbreath'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON flowbreath.* TO 'flowbreath'@'localhost';
FLUSH PRIVILEGES;
exit

# 마이그레이션 실행
php artisan migrate
```

### 4. Nginx 설정
```nginx
# /etc/nginx/sites-available/flowbreath
server {
    listen 80;
    server_name flowbreath.example.com;
    root /var/www/flowbreath/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:;";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # 대용량 파일 업로드 설정
    client_max_body_size 10M;
    client_body_buffer_size 128k;
}
```

```bash
# Nginx 설정 활성화
sudo ln -s /etc/nginx/sites-available/flowbreath /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 5. PHP-FPM 설정
```ini
# /etc/php/8.0/fpm/php.ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
max_input_time = 300
default_socket_timeout = 300
error_reporting = E_ALL
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
```

### 6. 권한 설정
```bash
# 저장소 권한 설정
sudo chown -R www-data:www-data /var/www/flowbreath
sudo chmod -R 755 /var/www/flowbreath
sudo chmod -R 775 /var/www/flowbreath/storage
sudo chmod -R 775 /var/www/flowbreath/bootstrap/cache

# 로그 디렉토리 생성
sudo mkdir -p /var/log/php
sudo chown -R www-data:www-data /var/log/php
```

## SSL 설정

### 1. Certbot 설치
```bash
sudo apt install certbot python3-certbot-nginx
```

### 2. SSL 인증서 발급
```bash
sudo certbot --nginx -d flowbreath.example.com
```

## 성능 최적화

### 1. PHP 옵티마이저 설치
```bash
sudo apt install php8.0-opcache
```

### 2. OPcache 설정
```ini
# /etc/php/8.0/fpm/conf.d/10-opcache.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.enable_cli=1
```

### 3. MySQL 설정
```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1
```

### 4. Redis 설정 (선택사항)
```ini
# /etc/redis/redis.conf
maxmemory 1gb
maxmemory-policy allkeys-lru
```

## 모니터링 설정

### 1. 로그 설정
```bash
# 로그 디렉토리 생성
sudo mkdir -p /var/log/flowbreath
sudo chown -R www-data:www-data /var/log/flowbreath

# 로그 로테이션 설정
sudo nano /etc/logrotate.d/flowbreath
```

```conf
/var/log/flowbreath/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        systemctl reload php8.0-fpm
    endscript
}
```

### 2. 모니터링 도구 설치
```bash
# Prometheus 설치
sudo apt install prometheus

# Node Exporter 설치
sudo apt install prometheus-node-exporter

# Grafana 설치
sudo apt install grafana
```

## 백업 설정

### 1. 백업 스크립트 생성
```bash
sudo nano /usr/local/bin/backup-flowbreath.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/flowbreath"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# 데이터베이스 백업
mysqldump -u flowbreath -p'your_password' flowbreath | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# 파일 백업
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/flowbreath

# 30일 이상 된 백업 삭제
find $BACKUP_DIR -type f -mtime +30 -delete
```

```bash
# 스크립트 실행 권한 설정
sudo chmod +x /usr/local/bin/backup-flowbreath.sh

# 크론 작업 추가
sudo crontab -e
```

```cron
0 2 * * * /usr/local/bin/backup-flowbreath.sh
```

## 보안 설정

### 1. 방화벽 설정
```bash
# UFW 설치
sudo apt install ufw

# 기본 정책 설정
sudo ufw default deny incoming
sudo ufw default allow outgoing

# 필요한 포트 허용
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https

# 방화벽 활성화
sudo ufw enable
```

### 2. 보안 강화
```bash
# fail2ban 설치
sudo apt install fail2ban

# fail2ban 설정
sudo nano /etc/fail2ban/jail.local
```

```ini
[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 3600
```

## 운영 체크리스트

### 1. 일일 점검 사항
- [ ] 시스템 로그 확인
- [ ] 백업 상태 확인
- [ ] 디스크 사용량 확인
- [ ] 메모리 사용량 확인
- [ ] CPU 사용량 확인
- [ ] 데이터베이스 연결 상태 확인

### 2. 주간 점검 사항
- [ ] 보안 업데이트 설치
- [ ] 로그 파일 분석
- [ ] 백업 복구 테스트
- [ ] 성능 모니터링 리포트 검토
- [ ] SSL 인증서 만료일 확인

### 3. 월간 점검 사항
- [ ] 시스템 전체 백업
- [ ] 데이터베이스 최적화
- [ ] 보안 감사
- [ ] 성능 튜닝
- [ ] 용량 계획 검토

## 문제 해결

### 1. 로그 확인
```bash
# Nginx 로그
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# PHP-FPM 로그
sudo tail -f /var/log/php8.0-fpm.log

# 애플리케이션 로그
tail -f /var/www/flowbreath/storage/logs/laravel.log
```

### 2. 일반적인 문제
1. 권한 문제
   ```bash
   sudo chown -R www-data:www-data /var/www/flowbreath
   sudo chmod -R 755 /var/www/flowbreath
   sudo chmod -R 775 /var/www/flowbreath/storage
   ```

2. 메모리 부족
   ```bash
   # PHP 메모리 제한 확인
   php -i | grep memory_limit
   
   # MySQL 메모리 사용량 확인
   mysqladmin -u root -p extended-status | grep -i mem
   ```

3. 디스크 공간 부족
   ```bash
   # 디스크 사용량 확인
   df -h
   
   # 큰 파일 찾기
   sudo find /var/www/flowbreath -type f -size +100M
   ```

## 업데이트 절차

### 1. 사전 준비
- 현재 버전 백업
- 데이터베이스 백업
- 사용자에게 공지

### 2. 업데이트 실행
```bash
cd /var/www/flowbreath

# 코드 업데이트
git pull origin main

# 의존성 업데이트
composer install --no-dev --optimize-autoloader

# 캐시 초기화
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 데이터베이스 마이그레이션
php artisan migrate

# 권한 재설정
sudo chown -R www-data:www-data /var/www/flowbreath
sudo chmod -R 755 /var/www/flowbreath
```

### 3. 서비스 재시작
```bash
sudo systemctl restart php8.0-fpm
sudo systemctl restart nginx
```

### 4. 확인 사항
- 애플리케이션 정상 작동 확인
- 로그 에러 확인
- 성능 모니터링
- 사용자 피드백 수집 