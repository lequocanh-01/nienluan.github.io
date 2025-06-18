<?php
// Script chạy qua terminal để sửa vai trò nhân viên
require_once 'elements_LQA/mod/nhanvienCls.php';
require_once 'elements_LQA/mod/userRoleCls.php';
require_once 'elements_LQA/mod/roleCls.php';
require_once 'elements_LQA/mod/userCls.php';

echo "🔧 Script sửa vai trò nhân viên - Chạy qua terminal\n";
echo "Đang kiểm tra và sửa vai trò cho các nhân viên...\n\n";

try {
    $nhanVienObj = new NhanVien();
    $userRoleObj = new UserRole();
    $roleObj = new Role();
    $userObj = new User();
    
    // Lấy tất cả nhân viên
    $allStaff = $nhanVienObj->nhanvienGetAll();
    
    $fixed = 0;
    $skipped = 0;
    $errors = 0;
    
    echo "┌─────────┬─────────────────┬─────────────────┬─────────────────┬─────────────────┐\n";
    echo "│ ID User │ Tên NV          │ Username        │ Trạng thái      │ Kết quả         │\n";
    echo "├─────────┼─────────────────┼─────────────────┼─────────────────┼─────────────────┤\n";
    
    foreach ($allStaff as $staff) {
        $idUser = str_pad($staff->iduser ?? 'N/A', 7, ' ', STR_PAD_BOTH);
        $tenNV = str_pad(substr($staff->tenNV, 0, 13), 15, ' ', STR_PAD_BOTH);
        
        if (!empty($staff->iduser)) {
            // Lấy thông tin user
            $userInfo = $userObj->UserGetbyId($staff->iduser);
            $username = $userInfo ? substr($userInfo->username, 0, 13) : 'Unknown';
            $username = str_pad($username, 15, ' ', STR_PAD_BOTH);
            
            // Kiểm tra xem user này đã có vai trò staff chưa
            $hasStaffRole = $roleObj->userHasRole($staff->iduser, 'staff');
            
            if ($hasStaffRole) {
                $trangThai = str_pad('✓ Có staff', 15, ' ', STR_PAD_BOTH);
                $ketQua = str_pad('Bỏ qua', 15, ' ', STR_PAD_BOTH);
                $skipped++;
            } else {
                $trangThai = str_pad('✗ Chưa có', 15, ' ', STR_PAD_BOTH);
                
                // Gán vai trò staff
                $result = $userRoleObj->assignStaffRole($staff->iduser);
                
                if ($result) {
                    $ketQua = str_pad('✓ ĐÃ GÁN', 15, ' ', STR_PAD_BOTH);
                    $fixed++;
                } else {
                    $ketQua = str_pad('✗ LỖI', 15, ' ', STR_PAD_BOTH);
                    $errors++;
                }
            }
        } else {
            $username = str_pad('-', 15, ' ', STR_PAD_BOTH);
            $trangThai = str_pad('Không liên kết', 15, ' ', STR_PAD_BOTH);
            $ketQua = str_pad('Bỏ qua', 15, ' ', STR_PAD_BOTH);
            $skipped++;
        }
        
        echo "│ $idUser │ $tenNV │ $username │ $trangThai │ $ketQua │\n";
    }
    
    echo "└─────────┴─────────────────┴─────────────────┴─────────────────┴─────────────────┘\n\n";
    
    echo "📊 Tổng kết:\n";
    echo "✅ Đã sửa: $fixed nhân viên\n";
    echo "⏭️  Bỏ qua: $skipped nhân viên\n";
    echo "❌ Lỗi: $errors nhân viên\n\n";
    
    if ($fixed > 0) {
        echo "🎉 HOÀN THÀNH!\n";
        echo "Đã gán thành công vai trò staff cho $fixed nhân viên!\n";
        echo "Vui lòng refresh lại trang danh sách vai trò để xem kết quả.\n\n";
    }
    
    if ($errors > 0) {
        echo "⚠️  CÓ LỖI!\n";
        echo "Có $errors lỗi xảy ra. Vui lòng kiểm tra lại.\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ LỖI HỆ THỐNG!\n";
    echo "Lỗi: " . $e->getMessage() . "\n";
}

echo "Script hoàn thành!\n";
?>
