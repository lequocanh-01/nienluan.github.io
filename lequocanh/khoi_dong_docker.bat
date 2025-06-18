@echo off
echo ========================================
echo     KHOI DONG DOCKER CONTAINERS
echo ========================================
echo.

echo Dang kiem tra Docker Desktop...
docker --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker chua duoc cai dat hoac chua chay!
    echo Vui long:
    echo 1. Cai dat Docker Desktop
    echo 2. Khoi dong Docker Desktop
    echo 3. Chay lai script nay
    pause
    exit /b 1
)

echo [OK] Docker da san sang!
echo.

echo Dang dung cac container cu...
docker-compose down

echo.
echo Dang khoi dong cac container moi...
docker-compose up -d

echo.
echo Dang cho MySQL khoi dong hoan tat...
timeout /t 10 /nobreak >nul

echo.
echo Kiem tra trang thai containers:
docker-compose ps

echo.
echo ========================================
echo     THONG TIN TRUY CAP
echo ========================================
echo Website: http://localhost:8888
echo phpMyAdmin: http://localhost:8889
echo Kiem tra ket noi: http://localhost:8888/kiem_tra_ket_noi.php
echo Kiem tra thoi gian: http://localhost:8888/kiem_tra_thoi_gian.php
echo ========================================
echo.

echo Nhan phim bat ky de dong...
pause
