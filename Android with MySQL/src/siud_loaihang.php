<?php
header("Content-Type: application/json; charset=UTF-8"); // lay du lieu json duoc client gui ve
// du lieu gui ve se duoc luu vao bien doi tuong $obj
$obj = json_decode(file_get_contents('php://input'));

// chuan bi ket noi mysql

$opt = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);

$connect = new PDO("mysql:host=mysql;dbname=qlsanpham;charset=utf8", "root", "android123");

// phat hien phuong thuc yeu cau $_SERVER['REQUEST_METHOD']

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $tenloai  = $obj->tenloai;
    $hinhanhB64 = $obj->hinhanh;          // Base64 từ app
    $hinhanhBin = base64_decode($hinhanhB64);

    $sql = "INSERT INTO loaihang(tenloai, hinhanh) VALUES(?,?)";
    $insert = $connect->prepare($sql);
    $data   = array($tenloai, $hinhanhBin);
    $ok     = $insert->execute($data);

    echo json_encode(array("result" => $ok ? "Insert OK" : "Insert Fail"));
}

if ($_SERVER['REQUEST_METHOD'] == "PUT") {

    $sql = "UPDATE loaihang SET tenloai = ? WHERE loaihang.idloaihang = ? ";
    $update = $connect->prepare($sql);
    $data = array($obj->tenloai, $obj->idloaihang);
    $kq = $update->execute($data);

    $jsonreturn = array("result" => $kq);
    echo json_encode($jsonreturn);
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {

    if (isset($_GET['ID'])) {
        $ID = $_GET['ID'];
        $sql = "SELECT idloaihang, tenloai, COALESCE(TO_BASE64(hinhanh), '') as hinhanh FROM loaihang WHERE loaihang.idloaihang = ?";
        $select = $connect->prepare($sql);
        $data = array($ID);
        $select->setFetchMode(PDO::FETCH_OBJ);
        $select->execute($data);
        $obj_get = $select->fetch();

        echo json_encode($obj_get);
    } else {
        $sql = "SELECT idloaihang, tenloai, COALESCE(TO_BASE64(hinhanh), '') as hinhanh FROM loaihang";
        $select = $connect->prepare($sql);
        $select->setFetchMode(PDO::FETCH_OBJ);
        $select->execute();
        $obj_get_list = $select->fetchAll();

        echo json_encode($obj_get_list);
    }
}

if ($_SERVER['REQUEST_METHOD'] == "DELETE") {

    $idloaihang = $_GET['IDLOAIHANG'];

    $sql = "DELETE FROM loaihang WHERE loaihang.idloaihang = ?";
    $delete = $connect->prepare($sql);
    $data = array($idloaihang);
    $kq = $delete->execute($data);

    if ($kq)
        echo "Delete OK";
    else
        echo "Delete not OK";
}