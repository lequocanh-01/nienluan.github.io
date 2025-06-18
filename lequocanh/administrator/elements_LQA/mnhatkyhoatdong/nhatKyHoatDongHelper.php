<?php

/**
 * File: nhatKyHoatDongHelper.php
 * Helper để ghi nhật ký hoạt động từ các file khác
 */

// Xác định đường dẫn tới file nhatKyHoatDongCls.php
$possible_paths = array(
    dirname(__FILE__) . '/../mod/nhatKyHoatDongCls.php',
    dirname(dirname(dirname(__FILE__))) . '/elements_LQA/mod/nhatKyHoatDongCls.php',
    dirname(dirname(dirname(dirname(__FILE__)))) . '/administrator/elements_LQA/mod/nhatKyHoatDongCls.php'
);

$nhatKyFile = null;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        $nhatKyFile = $path;
        break;
    }
}

if ($nhatKyFile === null) {
    // Ghi log lỗi nếu không tìm thấy file
    error_log("Không thể tìm thấy file nhatKyHoatDongCls.php");

    /**
     * Hàm giả để tránh lỗi khi không tìm thấy file
     */
    function ghiNhatKyHoatDong($username, $hanhDong, $doiTuong, $doiTuongId = null, $chiTiet = '')
    {
        error_log("Không thể ghi nhật ký hoạt động: $username, $hanhDong, $doiTuong, $doiTuongId, $chiTiet");
        return false;
    }
} else {
    require_once $nhatKyFile;

    /**
     * Hàm ghi nhật ký hoạt động
     * 
     * @param string $username Tên đăng nhập của người dùng
     * @param string $hanhDong Hành động thực hiện (thêm, sửa, xóa, đăng nhập, v.v.)
     * @param string $doiTuong Đối tượng tác động (sản phẩm, đơn hàng, người dùng, v.v.)
     * @param int $doiTuongId ID của đối tượng (nếu có)
     * @param string $chiTiet Chi tiết về hành động
     * @return int|bool ID của bản ghi mới hoặc false nếu có lỗi
     */
    function ghiNhatKyHoatDong($username, $hanhDong, $doiTuong, $doiTuongId = null, $chiTiet = '')
    {
        // Khởi tạo đối tượng NhatKyHoatDong
        $nhatKyObj = new NhatKyHoatDong();

        // Ghi nhật ký
        return $nhatKyObj->ghiNhatKy($username, $hanhDong, $doiTuong, $doiTuongId, $chiTiet);
    }
}

/**
 * Hàm ghi nhật ký đăng nhập
 * 
 * @param string $username Tên đăng nhập của người dùng
 * @return int|bool ID của bản ghi mới hoặc false nếu có lỗi
 */
function ghiNhatKyDangNhap($username)
{
    return ghiNhatKyHoatDong($username, 'Đăng nhập', 'Hệ thống', null, 'Đăng nhập vào hệ thống');
}

/**
 * Hàm ghi nhật ký đăng xuất
 * 
 * @param string $username Tên đăng nhập của người dùng
 * @return int|bool ID của bản ghi mới hoặc false nếu có lỗi
 */
function ghiNhatKyDangXuat($username)
{
    return ghiNhatKyHoatDong($username, 'Đăng xuất', 'Hệ thống', null, 'Đăng xuất khỏi hệ thống');
}

/**
 * Hàm ghi nhật ký thêm mới
 * 
 * @param string $username Tên đăng nhập của người dùng
 * @param string $doiTuong Đối tượng tác động (sản phẩm, đơn hàng, người dùng, v.v.)
 * @param int $doiTuongId ID của đối tượng
 * @param string $chiTiet Chi tiết về hành động
 * @return int|bool ID của bản ghi mới hoặc false nếu có lỗi
 */
function ghiNhatKyThemMoi($username, $doiTuong, $doiTuongId, $chiTiet = '')
{
    return ghiNhatKyHoatDong($username, 'Thêm mới', $doiTuong, $doiTuongId, $chiTiet);
}

/**
 * Hàm ghi nhật ký cập nhật
 * 
 * @param string $username Tên đăng nhập của người dùng
 * @param string $doiTuong Đối tượng tác động (sản phẩm, đơn hàng, người dùng, v.v.)
 * @param int $doiTuongId ID của đối tượng
 * @param string $chiTiet Chi tiết về hành động
 * @return int|bool ID của bản ghi mới hoặc false nếu có lỗi
 */
function ghiNhatKyCapNhat($username, $doiTuong, $doiTuongId, $chiTiet = '')
{
    return ghiNhatKyHoatDong($username, 'Cập nhật', $doiTuong, $doiTuongId, $chiTiet);
}

/**
 * Hàm ghi nhật ký xóa
 * 
 * @param string $username Tên đăng nhập của người dùng
 * @param string $doiTuong Đối tượng tác động (sản phẩm, đơn hàng, người dùng, v.v.)
 * @param int $doiTuongId ID của đối tượng
 * @param string $chiTiet Chi tiết về hành động
 * @return int|bool ID của bản ghi mới hoặc false nếu có lỗi
 */
function ghiNhatKyXoa($username, $doiTuong, $doiTuongId, $chiTiet = '')
{
    return ghiNhatKyHoatDong($username, 'Xóa', $doiTuong, $doiTuongId, $chiTiet);
}

/**
 * Hàm ghi nhật ký xem
 * 
 * @param string $username Tên đăng nhập của người dùng
 * @param string $doiTuong Đối tượng tác động (sản phẩm, đơn hàng, người dùng, v.v.)
 * @param int $doiTuongId ID của đối tượng
 * @param string $chiTiet Chi tiết về hành động
 * @return int|bool ID của bản ghi mới hoặc false nếu có lỗi
 */
function ghiNhatKyXem($username, $doiTuong, $doiTuongId, $chiTiet = '')
{
    return ghiNhatKyHoatDong($username, 'Xem danh sách', $doiTuong, $doiTuongId, $chiTiet);
}

/**
 * Hàm ghi nhật ký duyệt
 * 
 * @param string $username Tên đăng nhập của người dùng
 * @param string $doiTuong Đối tượng tác động (đơn hàng, phiếu nhập, v.v.)
 * @param int $doiTuongId ID của đối tượng
 * @param string $chiTiet Chi tiết về hành động
 * @return int|bool ID của bản ghi mới hoặc false nếu có lỗi
 */
function ghiNhatKyDuyet($username, $doiTuong, $doiTuongId, $chiTiet = '')
{
    return ghiNhatKyHoatDong($username, 'duyệt', $doiTuong, $doiTuongId, $chiTiet);
}

/**
 * Hàm ghi nhật ký hủy
 * 
 * @param string $username Tên đăng nhập của người dùng
 * @param string $doiTuong Đối tượng tác động (đơn hàng, phiếu nhập, v.v.)
 * @param int $doiTuongId ID của đối tượng
 * @param string $chiTiet Chi tiết về hành động
 * @return int|bool ID của bản ghi mới hoặc false nếu có lỗi
 */
function ghiNhatKyHuy($username, $doiTuong, $doiTuongId, $chiTiet = '')
{
    return ghiNhatKyHoatDong($username, 'hủy', $doiTuong, $doiTuongId, $chiTiet);
}

/**
 * Hàm ghi nhật ký phân quyền
 * 
 * @param string $username Tên đăng nhập của người dùng
 * @param string $doiTuong Đối tượng tác động (người dùng, nhân viên, v.v.)
 * @param int $doiTuongId ID của đối tượng
 * @param string $chiTiet Chi tiết về hành động
 * @return int|bool ID của bản ghi mới hoặc false nếu có lỗi
 */
function ghiNhatKyPhanQuyen($username, $doiTuong, $doiTuongId, $chiTiet = '')
{
    return ghiNhatKyHoatDong($username, 'phân quyền', $doiTuong, $doiTuongId, $chiTiet);
}
