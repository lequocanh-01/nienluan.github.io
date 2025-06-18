<?php
session_start();
require_once 'elements_LQA/mod/database.php';
require_once 'elements_LQA/mod/nhatKyHoatDongCls.php';

echo "<h1>🔍 TEST NÚT XEM CHI TIẾT</h1>";

try {
    $nhatKyObj = new NhatKyHoatDong();
    
    // Lấy ID của bản ghi đầu tiên
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $stmt = $conn->query("SELECT id FROM nhat_ky_hoat_dong ORDER BY thoi_gian DESC LIMIT 1");
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($record) {
        $testId = $record['id'];
        echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h2>Test với ID: $testId</h2>";
        
        // Test method getActivityById
        $activity = $nhatKyObj->getActivityById($testId);
        
        if ($activity) {
            echo "<p>✅ Method getActivityById hoạt động!</p>";
            echo "<h3>Dữ liệu trả về:</h3>";
            echo "<pre>" . print_r($activity, true) . "</pre>";
            
            // Test URL chi tiết
            echo "<h3>Test URL chi tiết:</h3>";
            $detailUrl = "elements_LQA/mnhatkyhoatdong/getActivityDetail.php?id=$testId";
            echo "<p><a href='$detailUrl' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Mở chi tiết trong tab mới</a></p>";
            
        } else {
            echo "<p>❌ Method getActivityById không trả về dữ liệu</p>";
        }
        echo "</div>";
        
        // Test JavaScript function
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h2>Test JavaScript Function:</h2>";
        echo "<button class='btn btn-sm btn-info' onclick='viewActivityDetail($testId)'>🔍 Test nút Xem chi tiết</button>";
        echo "</div>";
        
    } else {
        echo "<p>❌ Không có dữ liệu để test</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3 style='color: #721c24;'>❌ Lỗi: " . $e->getMessage() . "</h3>";
    echo "</div>";
}

echo "<div style='margin: 20px 0;'>";
echo "<a href='index.php?req=nhatKyHoatDongTichHop&tab=chitiet' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📋 Quay lại nhật ký</a>";
echo "</div>";
?>

<script>
function viewActivityDetail(id) {
    console.log('viewActivityDetail called with ID:', id);
    
    // Kiểm tra xem modal có tồn tại không
    if (typeof $('#activityDetailModal').modal === 'function') {
        console.log('Using Bootstrap modal');
        
        // Load nội dung chi tiết
        $.get('elements_LQA/mnhatkyhoatdong/getActivityDetail.php?id=' + id, function(data) {
            $('#activityDetailContent').html(data);
            $('#activityDetailModal').modal('show');
        }).fail(function() {
            alert('Lỗi khi tải thông tin chi tiết!');
        });
    } else {
        console.log('Modal not available, opening in new window');
        // Fallback: mở trong cửa sổ mới
        window.open('elements_LQA/mnhatkyhoatdong/getActivityDetail.php?id=' + id, '_blank', 'width=800,height=600');
    }
}

// Test jQuery
$(document).ready(function() {
    console.log('jQuery loaded:', typeof $ !== 'undefined');
    console.log('Bootstrap modal available:', typeof $.fn.modal !== 'undefined');
});
</script>

<!-- Bootstrap CSS và JS nếu chưa có -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Modal cho chi tiết -->
<div class="modal fade" id="activityDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết nhật ký hoạt động</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="activityDetailContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Đang tải...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
