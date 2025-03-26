@echo off
echo Đang cấp quyền ghi cho thư mục...

REM Tạo thư mục uploads nếu chưa tồn tại
if not exist "D:\PHP_WS\lequocanh\administrator\uploads\" (
    mkdir "D:\PHP_WS\lequocanh\administrator\uploads\"
    echo Đã tạo thư mục uploads
)

REM Cấp quyền đầy đủ cho thư mục uploads
icacls "D:\PHP_WS\lequocanh\administrator\uploads" /grant Everyone:(OI)(CI)F
icacls "D:\PHP_WS\lequocanh\administrator" /grant Everyone:(OI)(CI)M

echo.
echo Đã hoàn tất cấp quyền. Vui lòng chạy lại ứng dụng.
pause 