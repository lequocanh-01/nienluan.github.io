<?php
// Kiểm tra xem user đã đăng nhập với vai trò nào (admin hoặc user)
if (isset($_SESSION['ADMIN']) || isset($_SESSION['USER'])) {
    // Lấy tên người dùng từ session
    $namelogin = isset($_SESSION['ADMIN']) ? $_SESSION['ADMIN'] : $_SESSION['USER'];

    // Hiển thị thông tin chào mừng và thời gian đăng nhập gần nhất
    if (isset($_COOKIE[$namelogin])) {
        echo "Xin Chào " . $namelogin . '</br>';
        echo "Lần đăng nhập gần nhất : " . $_COOKIE[$namelogin];
    }
}

echo '</br>';

// Hiển thị thông báo thành công/thất bại/chờ
if (isset($_GET['result'])) {
    if ($_GET['result'] == 'ok') {
        ?>
<img src="img_LQA/Success.png" height="50px">
<?php
    } else {
        ?>
<img src="img_LQA/Fail.png" height="50px">
<?php
    }
} else {
    ?>
<img src="img_LQA/Wait.png" height="50px">
<?php
}
?>