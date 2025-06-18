# 🚀 HƯỚNG DẪN SỬ DỤNG - ANDROID WITH MYSQL

## ✅ SETUP HOÀN TẤT!

Container Docker đã được tạo và khởi động thành công với các thông tin sau:

### 🌐 URLs Truy Cập

| Service | URL | Mô tả |
|---------|-----|-------|
| **Website** | http://localhost:8890 | Trang chủ dự án Android |
| **phpMyAdmin** | http://localhost:8891 | Quản lý database MySQL |
| **API Base** | http://localhost:8890/api/ | API endpoints cho Android |

### 🗄️ Thông Tin Database

```
Host: mysql (trong Docker) / localhost:3307 (từ bên ngoài)
Database: android_db
Username: root
Password: android123
Port: 3306 (internal) / 3307 (external)
```

### 📱 Container Names

- **android-apache-php** - Web server (PHP 8.2 + Apache)
- **android-mysql** - Database server (MySQL 8.0)
- **android-phpmyadmin** - Database management tool

## 🔧 Các Lệnh Quản Lý

### Khởi động containers
```bash
cd "Android with MySQL"
docker-compose up -d
```

### Dừng containers
```bash
docker-compose down
```

### Xem logs
```bash
docker logs android-apache-php
docker logs android-mysql
docker logs android-phpmyadmin
```

### Rebuild containers
```bash
docker-compose down
docker-compose up -d --build
```

## 📁 Cấu Trúc Dự Án

```
Android with MySQL/
├── src/                    # Source code PHP
│   ├── index.php          # Trang chủ
│   ├── config/
│   │   └── database.php   # Cấu hình DB
│   └── api/
│       └── index.php      # API router
├── uploads/               # File uploads
├── database/              # MySQL data
├── docker-compose.yml     # Docker config
└── Dockerfile            # PHP/Apache image
```

## 🧪 Test Kết Nối

1. **Website**: http://localhost:8890
2. **PHP Info**: http://localhost:8890/info.php
3. **Test DB**: http://localhost:8890/test_connection.php
4. **API Status**: http://localhost:8890/api/status

## 📡 API Endpoints Có Sẵn

- `GET /api/status` - Thông tin trạng thái API
- `GET /api/test` - Test kết nối database

## 🔄 Backup & Restore Database

### Backup
```bash
docker exec android-mysql mysqldump -u root -pandroid123 android_db > backup.sql
```

### Restore
```bash
docker exec -i android-mysql mysql -u root -pandroid123 android_db < backup.sql
```

## 🛠️ Development

### Thêm code PHP mới
- Đặt file trong thư mục `src/`
- Truy cập qua http://localhost:8890/ten_file.php

### Thêm API endpoint mới
- Tạo file trong `src/api/`
- Thêm route trong `src/api/index.php`

### Kết nối database từ PHP
```php
require_once 'config/database.php';
$database = new Database();
$conn = $database->getConnection();
```

## 🚨 Troubleshooting

### Container không khởi động
```bash
docker-compose down
docker-compose up -d --build
```

### Không kết nối được database
1. Kiểm tra container MySQL: `docker logs android-mysql`
2. Đợi 30 giây để MySQL khởi tạo
3. Test connection: http://localhost:8890/test_connection.php

### Port bị chiếm
- Thay đổi ports trong `docker-compose.yml`
- Restart Docker Desktop

## 📞 Hỗ Trợ

- Kiểm tra logs container khi có lỗi
- Sử dụng phpMyAdmin để debug database
- Test API với Postman hoặc curl
- Xem PHP errors trong container logs

---

**🎉 Chúc mừng! Dự án Android with MySQL đã sẵn sàng để phát triển!**
