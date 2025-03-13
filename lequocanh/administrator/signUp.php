<?php
require_once './elements_LQA/mod/userCls.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new user();
    
    // Lấy dữ liệu từ form
    $username = $_POST['username'];
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];
    $gender = $_POST['gender'] === 'male' ? '1' : '0'; // Chuyển đổi gender sang 1/0
    $birthdate = $_POST['birthdate'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    // Kiểm tra username đã tồn tại chưa
    if ($user->UserCheckUsername($username)) {
        $error = "Tên đăng nhập đã tồn tại!";
    } else {
        // Thực hiện đăng ký sử dụng UserAdd
        $result = $user->UserAdd(
            $username,
            $password,
            $fullname,
            $gender,
            $birthdate,
            $address,
            $phone
        );

        if ($result) {
            // Đăng ký thành công, chuyển hướng đến trang đăng nhập
            header("Location: userLogin.php?register=success");
            exit();
        } else {
            $error = "Có lỗi xảy ra trong quá trình đăng ký!";
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <title>Đăng ký</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .signup-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            padding: 2rem;
            position: relative;
        }

        .signup-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .signup-header h2 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .signup-header p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .form-floating {
            margin-bottom: 1rem;
        }

        .form-floating input {
            border-radius: 10px;
            border: 2px solid #eee;
            padding: 1rem 0.75rem;
        }

        .form-floating input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .form-floating label {
            padding: 1rem 0.75rem;
        }

        .btn-signup {
            width: 100%;
            padding: 0.8rem;
            border-radius: 10px;
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            border: none;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #eee;
        }

        .divider::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #eee;
        }

        .social-signup {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .social-btn {
            flex: 1;
            padding: 0.8rem;
            border-radius: 10px;
            border: 2px solid #eee;
            background: white;
            color: #333;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        .social-btn i {
            font-size: 1.2rem;
        }

        .login-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }

        .login-link a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .login-link a:hover {
            color: #0a58ca;
        }

        .login-link a::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            background: #0d6efd;
            left: 0;
            bottom: -2px;
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .login-link a:hover::after {
            transform: scaleX(1);
        }

        /* Animation cho form validation */
        .form-floating input.is-invalid {
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .signup-container {
                padding: 1.5rem;
            }

            .social-signup {
                flex-direction: column;
            }
        }

        /* Thêm style cho select giới tính */
        .form-select {
            border-radius: 10px;
            border: 2px solid #eee;
            padding: 1rem 0.75rem;
            height: calc(3.5rem + 2px);
        }

        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        /* Style cho date input */
        input[type="date"] {
            min-height: calc(3.5rem + 2px);
        }
    </style>
</head>

<body>
    <div class="signup-container">
        <div class="signup-header">
            <h2>Đăng Ký Tài Khoản</h2>
            <p>Vui lòng điền đầy đủ thông tin để tạo tài khoản</p>
        </div>

        <form id="signupForm" method="POST" action="">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="form-floating">
                <input type="text" class="form-control" id="username" name="username" placeholder="Tên đăng nhập">
                <label for="username"><i class="fas fa-user me-2"></i>Tên đăng nhập</label>
            </div>

            <div class="form-floating">
                <input type="password" class="form-control" id="password" name="password" placeholder="Mật khẩu">
                <label for="password"><i class="fas fa-lock me-2"></i>Mật khẩu</label>
            </div>

            <div class="form-floating">
                <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Họ tên">
                <label for="fullname"><i class="fas fa-file-signature me-2"></i>Họ tên</label>
            </div>

            <div class="form-floating">
                <select class="form-select" id="gender" name="gender">
                    <option value="">Chọn giới tính</option>
                    <option value="male">Nam</option>
                    <option value="female">Nữ</option>
                </select>
                <label for="gender"><i class="fas fa-venus-mars me-2"></i>Giới tính</label>
            </div>

            <div class="form-floating">
                <input type="date" class="form-control" id="birthdate" name="birthdate">
                <label for="birthdate"><i class="fas fa-calendar-alt me-2"></i>Ngày sinh</label>
            </div>

            <div class="form-floating">
                <input type="text" class="form-control" id="address" name="address" placeholder="Địa chỉ">
                <label for="address"><i class="fas fa-map-marker-alt me-2"></i>Địa chỉ</label>
            </div>

            <div class="form-floating">
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Điện thoại">
                <label for="phone"><i class="fas fa-phone me-2"></i>Điện thoại</label>
            </div>

            <button type="submit" class="btn btn-signup mt-4">Đăng ký</button>
        </form>

        <div class="login-link">
            Đã có tài khoản? <a href="http://localhost:3000/administrator/userLogin.php"><i class="fas fa-sign-in-alt me-1"></i>Đăng nhập ngay</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#signupForm').on('submit', function(e) {
                let isValid = true;
                $('.form-control, .form-select').removeClass('is-invalid');

                // Validate username
                if ($('#username').val().trim() === '') {
                    $('#username').addClass('is-invalid');
                    isValid = false;
                }

                // Validate password
                if ($('#password').val().length < 6) {
                    $('#password').addClass('is-invalid');
                    isValid = false;
                }

                // Validate fullname
                if ($('#fullname').val().trim() === '') {
                    $('#fullname').addClass('is-invalid');
                    isValid = false;
                }

                // Validate gender
                if ($('#gender').val() === '') {
                    $('#gender').addClass('is-invalid');
                    isValid = false;
                }

                // Validate birthdate
                if ($('#birthdate').val() === '') {
                    $('#birthdate').addClass('is-invalid');
                    isValid = false;
                }

                // Validate address
                if ($('#address').val().trim() === '') {
                    $('#address').addClass('is-invalid');
                    isValid = false;
                }

                // Validate phone (10 digits)
                const phoneRegex = /^[0-9]{10}$/;
                if (!phoneRegex.test($('#phone').val())) {
                    $('#phone').addClass('is-invalid');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    alert('Vui lòng điền đầy đủ thông tin và đúng định dạng');
                } else {
                    // Form is valid, allow submission
                    return true;
                }
            });

            // Remove invalid class on input
            $('.form-control, .form-select').on('input change', function() {
                $(this).removeClass('is-invalid');
            });
        });
    </script>
</body>

</html>