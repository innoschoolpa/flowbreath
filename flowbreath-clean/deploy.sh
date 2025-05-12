#!/bin/bash

# 오류 발생 시 스크립트 중단
set -e

# 환경 변수 로드
if [ -f ".env.production" ]; then
    export $(cat .env.production | grep -v '^#' | xargs)
else
    echo "Error: .env.production file not found"
    exit 1
fi

# 배포 시작 시간 기록
echo "Deployment started at $(date)"

# Git pull
echo "Pulling latest changes..."
git pull origin main

# Composer 의존성 설치
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

# 데이터베이스 마이그레이션
echo "Running database migrations..."
php bin/migrate.php migrate

# 파일 권한 설정
echo "Setting file permissions..."
chmod -R 755 .
chmod -R 777 storage/logs
chmod -R 777 storage/cache
chmod 644 .env.production

# 캐시 정리
echo "Clearing cache..."
rm -rf storage/cache/*

# Apache 재시작 (필요한 경우)
# echo "Restarting Apache..."
# sudo service apache2 restart

# 배포 완료 시간 기록
echo "Deployment completed at $(date)"

# 상태 확인
echo "Checking application status..."
curl -s -o /dev/null -w "%{http_code}" https://flowbreath.io/health

# 슬랙 알림 (선택사항)
# if [ $? -eq 0 ]; then
#     curl -X POST -H 'Content-type: application/json' \
#     --data '{"text":"Successfully deployed FlowBreath"}' \
#     $SLACK_WEBHOOK_URL
# fi 