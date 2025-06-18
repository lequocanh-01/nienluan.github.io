<?php
// Script chạy qua terminal để sửa vai trò nhân viên
require_once 'elements_LQA/mod/nhanvienCls.php';
require_once 'elements_LQA/mod/userRoleCls.php';
require_once 'elements_LQA/mod/roleCls.php';

// Script để tự động gán vai trò staff cho những user đã có trong bảng nhân viên nhưng chưa có vai trò

echo "🔧 Script sửa vai trò nhân viên - Chạy qua terminal\n";
echo "Đang kiểm tra và sửa vai trò cho các nhân viên...\n\n";

try {
    $nhanVienObj = new NhanVien();
    $userRoleObj = new UserRole();
    $roleObj = new Role();

    // Lấy tất cả nhân viên
    $allStaff = $nhanVienObj->nhanvienGetAll();

    $fixed = 0;
    $skipped = 0;
    $errors = 0;

    echo "┌─────────┬─────────────────┬─────────────────┬─────────────────┬─────────────────┬─────────────────┐\n";
    echo "│ ID User │ Tên NV          │ Username        │ Trạng thái      │ Hành động       │ Kết quả         │\n";
    echo "├─────────┼─────────────────┼─────────────────┼─────────────────┼─────────────────┼─────────────────┤\n";

    foreach ($allStaff as $staff) {
        echo "<tr>";
        echo "<td style='padding: 8px; text-align: center;'>" . htmlspecialchars($staff->iduser ?? 'N/A') . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($staff->tenNV) . "</td>";

        if (!empty($staff->iduser)) {
            // Lấy thông tin user
            require_once 'elements_LQA/mod/userCls.php';
            $userObj = new User();
            $userInfo = $userObj->UserGetbyId($staff->iduser);
            $username = $userInfo ? $userInfo->username : 'Unknown';

            echo "<td style='padding: 8px;'>" . htmlspecialchars($username) . "</td>";

            // Kiểm tra xem user này đã có vai trò staff chưa
            $hasStaffRole = $roleObj->userHasRole($staff->iduser, 'staff');

            if ($hasStaffRole) {
                echo "<td style='padding: 8px; color: green;'>✓ Đã có vai trò staff</td>";
                echo "<td style='padding: 8px; color: blue;'>Không cần sửa</td>";
                echo "<td style='padding: 8px; color: blue;'>Bỏ qua</td>";
                $skipped++;
            } else {
                echo "<td style='padding: 8px; color: red;'>✗ Chưa có vai trò staff</td>";
                echo "<td style='padding: 8px; color: orange;'>Đang gán vai trò staff...</td>";

                // Gán vai trò staff
                $result = $userRoleObj->assignStaffRole($staff->iduser);

                if ($result) {
                    echo "<td style='padding: 8px; color: green; font-weight: bold;'>✓ ĐÃ GÁN THÀNH CÔNG</td>";
                    $fixed++;
                } else {
                    echo "<td style='padding: 8px; color: red; font-weight: bold;'>✗ LỖI KHI GÁN</td>";
                    $errors++;
                }
            }
        } else {
            echo "<td style='padding: 8px;'>-</td>";
            echo "<td style='padding: 8px; color: orange;'>Không có liên kết user</td>";
            echo "<td style='padding: 8px; color: orange;'>Không thể gán vai trò</td>";
            echo "<td style='padding: 8px; color: orange;'>Bỏ qua</td>";
            $skipped++;
        }
        echo "</tr>";
    }

    echo "</table>";

    echo "<div style='background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>📊 Tổng kết:</h3>";
    echo "<ul style='font-size: 16px;'>";
    echo "<li><strong style='color: green;'>Đã sửa:</strong> $fixed nhân viên</li>";
    echo "<li><strong style='color: blue;'>Bỏ qua:</strong> $skipped nhân viên</li>";
    echo "<li><strong style='color: red;'>Lỗi:</strong> $errors nhân viên</li>";
    echo "</ul>";
    echo "</div>";

    if ($fixed > 0) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>✅ HOÀN THÀNH!</h3>";
        echo "<p><strong>Đã gán thành công vai trò staff cho $fixed nhân viên!</strong></p>";
        echo "<p>Vui lòng refresh lại trang danh sách vai trò để xem kết quả.</p>";
        echo "</div>";
    }

    if ($errors > 0) {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>⚠️ CÓ LỖI!</h3>";
        echo "<p><strong>Có $errors lỗi xảy ra. Vui lòng kiểm tra lại.</strong></p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ LỖI HỆ THỐNG!</h3>";
    echo "<p>Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<div style='margin: 30px 0; text-align: center;'>";
echo "<a href='index.php?req=danhSachVaiTroView' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>📋 Xem danh sách vai trò</a>";
echo "<a href='index.php?req=nhanvienview' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>👥 Xem danh sách nhân viên</a>";
echo "</div>";

// Tự động xóa file này sau khi chạy xong
echo "<script>";
echo "setTimeout(function() {";
echo "  if (confirm('Script đã chạy xong. Bạn có muốn xóa file script này không?')) {";
echo "    fetch('fix_staff_roles_now.php?delete=1');";
echo "    alert('File script đã được xóa.');";
echo "  }";
echo "}, 3000);";
echo "</script>";

// Xử lý xóa file
if (isset($_GET['delete']) && $_GET['delete'] == '1') {
    unlink(__FILE__);
    echo "File đã được xóa.";
    exit;
}
