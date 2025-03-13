<div align="center">Cập nhật loại hàng</div>
<hr>
<?php
require '../../elements_LQA/mod/loaihangCls.php';
$idloaihang = $_REQUEST['idloaihang'];
echo $idloaihang;

$lhobj = new loaihang();
$getLhUpdate = $lhobj->LoaihangGetbyId($idloaihang);
// echo $getUserUpdate->hoten;

?>

<div>
    <form name="updateloaihang" id="formupdatelh" method="post" action='./elements_LQA/mloaihang/loaihangAct.php?reqact=updateloaihang' enctype="multipart/form-data">
        <input type="hidden" name="idloaihang" value="<?php echo $getLhUpdate->idloaihang;  ?>" />
        <input type="hidden" name="hinhanh" value="<?php echo $getLhUpdate->hinhanh;  ?>" />
        <table>
            <tr>
                <td>Tên loại hàng</td>
                <td><input type="text" name="tenloaihang" value="<?php echo $getLhUpdate->tenloaihang;
                                                                    ?>" /></td>
            </tr>
            <tr>
                <td>Mô tả</td>
                <td><input type="text" size="50" name="mota" value="<?php echo $getLhUpdate->mota;
                                                                    ?>" /></td>
            </tr>
            <tr>
                <td>Hình ảnh</td>
                <td>
                    <img width="150px" src="data:image/png;base64,<?php echo $getLhUpdate->hinhanh ?>"><br>
                    <input type="file" name="fileimage">
                </td>
            </tr>

            <tr>
                <td><input type="submit" id="btnsubmit" value="Update" size="50" /></td>
                <td><b id="noteForm"></b></td>
            </tr>
        </table>
    </form>
</div>

<script>
$(document).ready(function() {
    $("#formupdatelh").submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response.includes('ok')) {
                    $("#noteForm").html("Cập nhật thành công!");
                    // Reload trang sau 1 giây
                    setTimeout(function() {
                        window.location.href = "index.php?req=loaihangview&result=ok";
                    }, 1000);
                } else {
                    $("#noteForm").html("Có lỗi xảy ra!");
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });
});
</script>