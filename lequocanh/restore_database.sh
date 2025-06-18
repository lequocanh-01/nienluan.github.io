#!/bin/bash
# Script khôi phục database tự động

echo "🔄 Bắt đầu khôi phục database..."

# Kiểm tra Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker không được cài đặt"
    exit 1
fi

# Kiểm tra container
if ! docker ps | grep -q lequocanh_mysql; then
    echo "❌ Container MySQL không chạy"
    echo "Khởi động container..."
    docker-compose up -d mysql
    sleep 10
fi

# Tìm file backup mới nhất
BACKUP_FILE=$(ls -t backup_*.sql 2>/dev/null | head -n1)

if [ -z "$BACKUP_FILE" ]; then
    echo "❌ Không tìm thấy file backup"
    exit 1
fi

echo "📁 Sử dụng file backup: $BACKUP_FILE"

# Khôi phục database
echo "🔄 Đang khôi phục..."
docker exec -i lequocanh_mysql mysql -u root -ppw trainingdb < "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo "✅ Khôi phục thành công!"
else
    echo "❌ Khôi phục thất bại!"
    exit 1
fi

echo "🎉 Hoàn thành!"
