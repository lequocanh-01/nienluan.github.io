<?php
session_start();
require '../../elements_LQA/mod/userCls.php';
require '../../elements_LQA/mod/giohangCls.php';

$requestAction = isset($_REQUEST['reqact']) ? $_REQUEST['reqact'] : '';

if ($requestAction) {
    switch ($requestAction) {
        case 'addnew':
            // xử lý thêm
            $username = $_REQUEST['username'];
            $password = $_REQUEST['password'];
            $hoten = $_REQUEST['hoten'];
            $gioitinh = $_REQUEST['gioitinh'];
            $ngaysinh = $_REQUEST['ngaysinh'];
            $dienthoai = $_REQUEST['dienthoai'];
            $diachi = $_REQUEST['diachi'];
            $userObj = new user();

            // Kiểm tra username đã tồn tại chưa
            if ($userObj->UserCheckUsername($username)) {
                // Kiểm tra nếu là AJAX request
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại']);
                    exit();
                } else {
                    header('Location: ../../index.php?req=userview&result=username_exists');
                    exit();
                }
            }

            $kq = $userObj->UserAdd($username, $password, $hoten, $gioitinh, $ngaysinh, $diachi, $dienthoai);

            // Kiểm tra nếu là AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                if ($kq) {
                    echo json_encode(['success' => true, 'message' => 'Thêm người dùng thành công']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Thêm người dùng thất bại']);
                }
                exit();
            } else {
                if ($kq) {
                    header('Location: ../../index.php?req=userview&result=ok');
                } else {
                    header('Location: ../../index.php?req=userview&result=notok');
                }
            }
            break;

        case 'changepassword':
            // Kiểm tra đăng nhập
            if (!isset($_SESSION['USER']) && !isset($_SESSION['ADMIN'])) {
                echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện chức năng này']);
                exit();
            }

            // Lấy dữ liệu từ form
            $iduser = isset($_POST['iduser']) ? $_POST['iduser'] : '';
            $passwordold = isset($_POST['passwordold']) ? $_POST['passwordold'] : '';
            $passwordnew = isset($_POST['passwordnew']) ? $_POST['passwordnew'] : '';

            // Log để debug
            error_log("Change password request - ID: $iduser, Old Pass: $passwordold, New Pass: $passwordnew");

            // Validate dữ liệu
            if (empty($iduser) || empty($passwordold) || empty($passwordnew)) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
                exit();
            }

            // Đổi mật khẩu
            $userObj = new user();
            $result = $userObj->UserChangePassword($iduser, $passwordold, $passwordnew);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác']);
            }
            exit();
            break;

        case 'deleteuser':
            $iduser = $_REQUEST['iduser'];
            $userObj = new user();
            $user = $userObj->UserGetByid($iduser);

            // Kiểm tra quyền admin
            if (!isset($_SESSION['ADMIN'])) {
                header('location: ../../index.php?req=userview&result=not_authorized');
                exit();
            }

            // Kiểm tra nếu là tài khoản admin
            if ($user && $user->username === 'admin') {
                $admin_password = isset($_REQUEST['admin_password']) ? $_REQUEST['admin_password'] : '';

                // Kiểm tra mật khẩu admin từ database
                if (!$userObj->UserCheckLogin('admin', $admin_password)) {
                    header('location: ../../index.php?req=userview&result=invalid_admin_pass');
                    exit();
                }
            }

            $kq = $userObj->UserDelete($iduser);
            if ($kq) {
                header('location: ../../index.php?req=userview&result=ok');
            } else {
                header('location: ../../index.php?req=userview&result=notok');
            }
            break;

        case 'setlock':
            $iduser = $_REQUEST['iduser'];
            $setlock = $_REQUEST['setlock'];
            $userObj = new user();
            $user = $userObj->UserGetbyId($iduser);

            // Kiểm tra nếu là tài khoản admin
            if ($user && $user->username === 'admin') {
                $admin_password = isset($_REQUEST['admin_password']) ? $_REQUEST['admin_password'] : '';

                // Kiểm tra mật khẩu admin từ database
                if (!$userObj->UserCheckLogin('admin', $admin_password)) {
                    header('location: ../../index.php?req=userview&result=invalid_admin_pass');
                    exit();
                }
            }

            $newStatus = $setlock == 1 ? 0 : 1;
            $kq = $userObj->UserSetActive($iduser, $newStatus);
            if ($kq) {
                header('location: ../../index.php?req=userview&result=ok');
            } else {
                header('location: ../../index.php?req=userview&result=notok');
            }
            break;

        case 'updateuser':
            $iduser = $_REQUEST['iduser'];
            $username = $_REQUEST['username'];
            $password = $_REQUEST['password'];
            $hoten = $_REQUEST['hoten'];
            $gioitinh = $_REQUEST['gioitinh'];
            $ngaysinh = $_REQUEST['ngaysinh'];
            $diachi = $_REQUEST['diachi'];
            $dienthoai = $_REQUEST['dienthoai'];
            $verify_password = isset($_REQUEST['verify_password']) ? $_REQUEST['verify_password'] : '';

            $userObj = new user();
            $user = $userObj->UserGetbyId($iduser);

            if (!$user) {
                header('location: ../../index.php?req=userview&result=user_not_found');
                exit();
            }

            // Kiểm tra nếu là tài khoản admin
            if ($user->username === 'admin') {
                // Kiểm tra mật khẩu xác thực
                if ($verify_password !== 'lequocanh') {
                    header('location: ../../index.php?req=userview&result=invalid_verify_pass');
                    exit();
                }

                // Nếu không nhập mật khẩu mới, giữ nguyên mật khẩu cũ
                if (empty($password)) {
                    $password = $user->password;
                }
            }

            // Validate dữ liệu
            if (empty($username) || empty($hoten) || empty($ngaysinh) || empty($diachi) || empty($dienthoai)) {
                header('location: ../../index.php?req=userview&result=missing_data');
                exit();
            }

            // Kiểm tra username đã tồn tại chưa (trừ username hiện tại)
            if ($username !== $user->username && $userObj->UserCheckUsername($username)) {
                header('Location: ../../index.php?req=userview&result=username_exists');
                exit();
            }

            $result = $userObj->UserUpdate($username, $password, $hoten, $gioitinh, $ngaysinh, $diachi, $dienthoai, $iduser);

            if ($result) {
                header('location: ../../index.php?req=userview&result=ok');
            } else {
                header('location: ../../index.php?req=userview&result=failed');
            }
            exit();

        case 'checklogin':
            $username = $_REQUEST['username'];
            $password = $_REQUEST['password'];
            $userObj = new user();
            $kq = $userObj->UserCheckLogin($username, $password);
            if ($kq) {
                if ($username == 'admin') {
                    $_SESSION['ADMIN'] = $username;
                    // Chuyển giỏ hàng từ session sang database
                    $giohang = new GioHang();
                    $giohang->migrateSessionCartToDatabase($username);

                    // Kiểm tra xem có URL chuyển hướng sau đăng nhập không
                    if (isset($_SESSION['redirect_after_login'])) {
                        $redirect_url = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                        header('location: ' . $redirect_url);
                    } else {
                        header('location: ../../index.php?req=userview&result=ok');
                    }
                } else {
                    $_SESSION['USER'] = $username;
                    // Chuyển giỏ hàng từ session sang database
                    $giohang = new GioHang();
                    $giohang->migrateSessionCartToDatabase($username);
                    // Đặt cookie sau khi đăng nhập thành công
                    $time_login = date('h:i - d/m/Y');
                    setcookie($username, $time_login, time() + (86400 * 30), '/');

                    // Kiểm tra xem có URL chuyển hướng sau đăng nhập không
                    if (isset($_SESSION['redirect_after_login'])) {
                        $redirect_url = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                        header('location: ' . $redirect_url);
                    } else {
                        header('location: ../../../index.php');
                    }
                }
            } else {
                header('location: ../../userLogin.php?error=1');
            }
            break;

        case 'userlogout':
            // Ghi log để debug
            error_log("Đang xử lý đăng xuất...");

            $time_login = date('h:i - d/m/Y');
            $namelogin = '';

            if (isset($_SESSION['USER'])) {
                $namelogin = $_SESSION['USER'];
                error_log("Đăng xuất USER: " . $namelogin);
            }
            if (isset($_SESSION['ADMIN'])) {
                $namelogin = $_SESSION['ADMIN'];
                error_log("Đăng xuất ADMIN: " . $namelogin);
            }

            // Chỉnh sửa tên cookie
            $namelogin = str_replace(' ', '-', $namelogin);
            $namelogin = str_replace('"', '', $namelogin);
            setcookie($namelogin, $time_login, time() + (86400 * 30), '/'); // 1 tháng

            // Xóa session
            unset($_SESSION['USER']);
            unset($_SESSION['ADMIN']);
            session_destroy();

            error_log("Đã xóa session, chuyển hướng người dùng...");

            // Lưu trữ thông tin trước khi xóa session
            $isAdmin = isset($_SESSION['ADMIN']);

            // Chuyển hướng về trang chủ sau khi đăng xuất
            if ($isAdmin) {
                error_log("Chuyển hướng đến trang admin");
                header('location: ../../index.php');
            } else {
                error_log("Chuyển hướng đến trang chủ");
                header('location: ../../../index.php');
            }
            exit(); // Đảm bảo dừng thực thi script sau khi chuyển hướng
            break;

        case 'checkadmin':
            $admin_password = isset($_REQUEST['admin_password']) ? $_REQUEST['admin_password'] : '';

            // Kiểm tra mật khẩu admin
            if ($admin_password === 'lequocanh') {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu không chính xác']);
            }
            exit();
            break;

        default:
            header('Location: ../../index.php?req=userview');
            break;
    }
} else {
    header('Location: ../../index.php?req=userview');
}
