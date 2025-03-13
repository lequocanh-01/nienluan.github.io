<head>
    <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../public_files/mycss.css">
</head>

<body>

    <div class="admin-title">Quản lý người dùng</div>
    <hr>

    <?php
    require './elements_LQA/mod/userCls.php';
    $userObj = new user();
    $list_user = $userObj->UserGetAll();
    $l = count($list_user);

    // Thống kê
    $totalUsers = count($list_user);
    $activeUsers = 0;
    $last30DaysLogins = 0;
    $newUsersThisMonth = 0;

    foreach ($list_user as $u) {
        if ($u->setlock == 1) $activeUsers++;

        // Đếm người dùng đăng nhập trong 30 ngày
        if (isset($_COOKIE[$u->username])) {
            $lastLogin = strtotime($_COOKIE[$u->username]);
            if ((time() - $lastLogin) <= (30 * 24 * 60 * 60)) {
                $last30DaysLogins++;
            }
        }

        // Đếm người dùng mới trong tháng này
        if (isset($u->ngaydangki)) {
            $registerDate = strtotime($u->ngaydangki);
            if (date('Y-m', $registerDate) === date('Y-m')) {
                $newUsersThisMonth++;
            }
        }
    }
    ?>

    <!-- Dashboard Cards -->
    <div class="admin-dashboard">
        <div class="dashboard-cards">
            <div class="dashboard-card primary">
                <div class="card-content">
                    <div class="card-info">
                        <h4>Tổng số người dùng</h4>
                        <h2><?php echo $totalUsers; ?></h2>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="dashboard-card success">
                <div class="card-content">
                    <div class="card-info">
                        <h4>Người dùng hoạt động</h4>
                        <h2><?php echo $activeUsers; ?></h2>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>

            <div class="dashboard-card info">
                <div class="card-content">
                    <div class="card-info">
                        <h4>Đăng nhập 30 ngày qua</h4>
                        <h2><?php echo $last30DaysLogins; ?></h2>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                </div>
            </div>

            <div class="dashboard-card warning">
                <div class="card-content">
                    <div class="card-info">
                        <h4>Người dùng mới tháng này</h4>
                        <h2><?php echo $newUsersThisMonth; ?></h2>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr />
    <div class="admin-form">
        <h3>Thêm người dùng mới</h3>
        <form name="newuser" id="formreg" method="post" action='./elements_LQA/mUser/userAct.php?reqact=addnew'>
            <table class="form-table">
                <tr>
                    <td>Tên đăng nhập:</td>
                    <td><input type="text" name="username" required /></td>
                </tr>
                <tr>
                    <td>Mật khẩu:</td>
                    <td><input type="password" name="password" required /></td>
                </tr>
                <tr>
                    <td>Họ tên:</td>
                    <td><input type="text" name="hoten" required /></td>
                </tr>
                <tr>
                    <td>Giới tính:</td>
                    <td>
                        Nam<input type="radio" name="gioitinh" value="1" checked="true" />
                        Nữ<input type="radio" name="gioitinh" value="0" />
                    </td>
                </tr>
                <tr>
                    <td>Ngày sinh:</td>
                    <td><input type="date" name="ngaysinh" required /></td>
                </tr>
                <tr>
                    <td>Địa chỉ:</td>
                    <td><input type="text" name="diachi" required /></td>
                </tr>
                <tr>
                    <td>Điện thoại:</td>
                    <td><input type="tel" name="dienthoai" pattern="[0-9]{10}" required /></td>
                </tr>
                <tr>
                    <td colspan="2" class="form-actions">
                        <button type="submit" class="btn btn-primary">Tạo mới</button>
                        <button type="reset" class="btn btn-secondary">Làm lại</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <hr />
    <div class="content_user">
        <div class="admin-info">
            Tổng số người dùng: <b><?php echo $l; ?></b>
        </div>

        <div class="table-responsive">
            <table class="content-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Mật khẩu</th>
                        <th>Họ tên</th>
                        <th>Giới tính</th>
                        <th>Ngày sinh</th>
                        <th>Địa chỉ</th>
                        <th>Điện thoại</th>
                        <th>Trạng thái</th>
                        <th>Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($l > 0) {
                        foreach ($list_user as $u) {
                            $isAdmin = ($u->username === 'admin');
                    ?>
                            <tr>
                                <td><?php echo $u->iduser; ?></td>
                                <td><?php echo $u->username; ?></td>
                                <td>
                                    <div class="password-field">
                                        <span class="password-dots">••••••••</span>
                                        <span class="password-text" style="display: none;">
                                            <?php echo htmlspecialchars($u->password); ?>
                                        </span>
                                        <i class="fas fa-eye toggle-password" style="cursor: pointer; margin-left: 5px;"></i>
                                    </div>
                                </td>
                                <td><?php echo $u->hoten; ?></td>
                                <td><?php echo $u->gioitinh; ?></td>
                                <td><?php echo $u->ngaysinh; ?></td>
                                <td><?php echo $u->diachi; ?></td>
                                <td><?php echo $u->dienthoai; ?></td>
                                <td align="center">
                                    <?php if (isset($_SESSION['ADMIN'])) { ?>
                                        <a href='./elements_LQA/mUser/userAct.php?reqact=setlock&iduser=<?php echo $u->iduser; ?>&setlock=<?php echo $u->setlock; ?>'
                                            class="status-icon">
                                            <img src="<?php echo $u->setlock == 1 ? './img_LQA/Unlock.png' : './img_LQA/Lock.png'; ?>"
                                                class="iconimg" alt="Trạng thái">
                                        </a>
                                    <?php } else { ?>
                                        <img src="<?php echo $u->setlock == 1 ? './img_LQA/Unlock.png' : './img_LQA/Lock.png'; ?>"
                                            class="iconimg" alt="Trạng thái">
                                    <?php } ?>
                                </td>
                                <td class="action-buttons">
                                    <?php if (isset($_SESSION['ADMIN'])) { ?>
                                        <a href='./elements_LQA/mUser/userAct.php?reqact=deleteuser&iduser=<?php echo $u->iduser; ?>'
                                            class="admin-action"
                                            data-username="<?php echo $u->username; ?>"
                                            onclick="return confirmDelete('<?php echo $u->username; ?>');">
                                            <img src="./img_LQA/Delete.png" class="iconimg" alt="Delete">
                                        </a>
                                    <?php } else { ?>
                                        <img src="./img_LQA/Delete.png" class="iconimg disabled" alt="Delete">
                                    <?php } ?>

                                    <?php if (isset($_SESSION['ADMIN']) || (isset($_SESSION['USER']) && $_SESSION['USER'] == $u->username)) { ?>
                                        <a href='javascript:void(0);'
                                            class="admin-action update-user"
                                            data-username="<?php echo $u->username; ?>"
                                            data-userid="<?php echo $u->iduser; ?>"
                                            data-admin-password="<?php echo isset($_GET['admin_password']) ? $_GET['admin_password'] : ''; ?>">
                                            <img src="./img_LQA/Update.png" class="iconimg" alt="Update">
                                        </a>
                                    <?php } else { ?>
                                        <img src="./img_LQA/Update.png" class="iconimg disabled" alt="Update">
                                    <?php } ?>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (isset($_GET['result'])): ?>
        <?php
        $message = '';
        $alertClass = 'alert-danger';

        switch ($_GET['result']) {
            case 'ok':
                $message = 'Thao tác thành công!';
                $alertClass = 'alert-success';
                break;
            case 'notok':
                $message = 'Có lỗi xảy ra!';
                break;
            case 'not_authorized':
                $message = 'Bạn không có quyền thực hiện thao tác này!';
                break;
            case 'invalid_verify_pass':
                $message = 'Mật khẩu xác thực không chính xác!';
                break;
            case 'username_exists':
                $message = 'Tên đăng nhập đã tồn tại!';
                break;
            case 'missing_data':
                $message = 'Vui lòng điền đầy đủ thông tin!';
                break;
        }
        ?>
        <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</body>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
    integrity="sha384-oBqDVmMz4fnFO9gyb6g5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
    integrity="sha384-1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1n1" crossorigin="anonymous"></script>
<script></script>
<script src="../../js_LQA/jscript.js"></script>