<?php
require_once __DIR__ . '/../mod/donvitinhCls.php';

// Debugging - show all input
$debug = [];
$debug['POST'] = $_POST;
$debug['GET'] = $_GET;
$debug['REQUEST'] = $_REQUEST;

// Try to get ID from various sources
$idDonViTinh = isset($_POST['idDonViTinh']) ? $_POST['idDonViTinh'] : (isset($_GET['idDonViTinh']) ? $_GET['idDonViTinh'] : (isset($_REQUEST['idDonViTinh']) ? $_REQUEST['idDonViTinh'] : null));

// If still no ID, try alternative forms
if (!$idDonViTinh) {
    if (isset($_POST['data-id'])) {
        $idDonViTinh = $_POST['data-id'];
    } elseif (isset($_GET['data-id'])) {
        $idDonViTinh = $_GET['data-id'];
    }
}

$debug['ID detected'] = $idDonViTinh;

// Output debug if requested
if (isset($_GET['debug']) || isset($_POST['debug'])) {
    echo "<pre>";
    print_r($debug);
    echo "</pre>";
}

if (!$idDonViTinh) {
    echo json_encode([
        'success' => false,
        'message' => "Không tìm thấy ID đơn vị tính",
        'debug' => $debug
    ]);
    exit;
}

$donViTinhObj = new DonViTinh();
$getDonViTinhUpdate = $donViTinhObj->donvitinhGetbyId($idDonViTinh);

if (!$getDonViTinhUpdate) {
    echo json_encode([
        'success' => false,
        'message' => "Không tìm thấy đơn vị tính với ID: " . htmlspecialchars($idDonViTinh),
        'debug' => $debug
    ]);
    exit;
}
?>

<div class="update-form-container">
    <div class="update-header">
        <h3>Cập nhật đơn vị tính</h3>
        <span class="close-btn" id="close-btn">X</span>
    </div>

    <form name="updatedonvitinh" id="updatedonvitinh" method="post" action="./elements_LQA/mdonvitinh/donvitinhAct.php?reqact=updatedonvitinh">
        <input type="hidden" name="idDonViTinh" value="<?php echo $getDonViTinhUpdate->idDonViTinh; ?>" />

        <div class="form-group">
            <label>ID:</label>
            <div><?php echo htmlspecialchars($idDonViTinh); ?></div>
        </div>

        <div class="form-group">
            <label>Tên Đơn Vị Tính:</label>
            <input type="text" class="form-control" name="tenDonViTinh" value="<?php echo htmlspecialchars($getDonViTinhUpdate->tenDonViTinh); ?>" required />
        </div>

        <div class="form-group">
            <label>Mô Tả:</label>
            <textarea class="form-control" name="moTa" rows="3"><?php echo htmlspecialchars($getDonViTinhUpdate->moTa); ?></textarea>
        </div>

        <div class="form-group">
            <label>Ghi Chú:</label>
            <textarea class="form-control" name="ghiChu" rows="3"><?php echo htmlspecialchars($getDonViTinhUpdate->ghiChu); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Cập nhật</button>
        </div>
    </form>
</div>

<script>
    // Add console logging for debugging
    console.log("Update button clicked for mdonvitinh with ID", <?php echo json_encode($idDonViTinh); ?>);
    console.log("Update form loaded successfully");
    
    // Add multiple methods to close the popup
    document.getElementById('close-btn').addEventListener('click', function() {
        console.log("Close button clicked");
        
        // Method 1: Emit custom event for parent window to handle closing
        if (window.parent) {
            window.parent.postMessage('closeUpdateForm', '*');
            console.log("Sent closeUpdateForm message to parent");
        }
        
        // Method 2: Use direct DOM manipulation if possible
        var parentPopup = document.getElementById('w_update_dvt');
        if (parentPopup) {
            parentPopup.style.display = 'none';
            console.log("Closed popup via DOM ID");
        }
        
        // Method 3: Use jQuery if available
        if (window.jQuery) {
            $("#w_update_dvt").hide();
            console.log("Closed popup via jQuery");
            
            // Try to find any modal that might contain this form
            $(".modal-window, .update-form-container").closest("div[id^='w_update']").hide();
            console.log("Attempted to close all potential parent containers");
        }
    });
    
    // Handle form submission with AJAX
    document.getElementById('updatedonvitinh').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log("Form submitted");
        
        var formData = new FormData(this);
        
        // Use jQuery AJAX for consistency with rest of application
        $.ajax({
            url: "./elements_LQA/mdonvitinh/donvitinhAct.php?reqact=updatedonvitinh",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log("Update successful, response:", response);
                
                // Close any popup container
                $("#w_update_dvt").hide();
                $(".modal-window, .update-form-container").closest("div[id^='w_update']").hide();
                
                // Reload page to show updated data
                window.location.href = "index.php?req=donvitinhview&t=" + new Date().getTime();
            },
            error: function(xhr, status, error) {
                console.error("Update error:", error, xhr.responseText);
                alert("Có lỗi xảy ra khi cập nhật đơn vị tính: " + error);
            }
        });
    });
</script>