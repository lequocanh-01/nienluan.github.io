# Cấu hình Docker cho Project

## Container Ports

- **Apache/PHP**: `localhost:8888`
- **phpMyAdmin**: `localhost:8889`
- **MySQL**: Internal container port

## URLs chính

- Website: `http://localhost:8888/`
- Admin panel: `http://localhost:8888/admin/`
- phpMyAdmin: `http://localhost:8889/`

## Files quan trọng

- `kiem_tra_tong_hop_gop_bang.php`
- `kiem_tra_va_cap_nhat_database.php`
- `test_web.php`

## Container Names (FIXED - DO NOT CREATE NEW ONES)

- **php_ws** (main container)
- **apache-php-1** c7c808dc03a6 (port 8888:80)
- **mysql-1** a22cbccec15e
- **phpmyadmin** b038d97ad66c (port 8889:80)

## Testing Protocol

- **ALWAYS AUTO-TEST** after creating/modifying files
- **NO ASKING** - just do it automatically
- Test immediately after any code changes
