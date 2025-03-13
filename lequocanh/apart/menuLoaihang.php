<?php
require 'administrator/elements_LQA/mod/loaihangCls.php';
$current_id = isset($_GET['reqView']) ? $_GET['reqView'] : null;
?>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="./index.php">
            <img src="./administrator/img_LQA/Home.png" alt="Home" width="24" height="24"
                class="d-inline-block align-text-top me-2">
            Home
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="nav nav-pills main-menu">
                <?php
                $obj = new loaihang();
                $list_lh = $obj->LoaihangGetAll();
                
                // Hiển thị tất cả các mục
                foreach($list_lh as $v) {
                    $active_class = ($current_id == $v->idloaihang) ? 'active' : '';
                ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_class; ?> d-flex align-items-center"
                            href="./index.php?reqView=<?php echo $v->idloaihang; ?>">
                            <img src="data:image/png;base64,<?php echo $v->hinhanh; ?>" 
                                alt="<?php echo $v->tenloaihang; ?>"
                                width="20" height="20" class="me-2">
                            <span><?php echo $v->tenloaihang; ?></span>
                        </a>
                    </li>
                <?php
                }
                ?>
            </ul>
        </div>
    </div>
</nav>