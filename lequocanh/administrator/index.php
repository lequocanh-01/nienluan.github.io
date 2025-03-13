<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="stylecss_LQA/mycss.css" />
    <script type="text/javascript" src="js_LQA/jquery-3.7.1.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="js_LQA/jscript.js"></script>
    <title>Your Website LQA</title>
</head>

<body>
    <?php
    if (!isset($_SESSION['USER']) && !isset($_SESSION['ADMIN'])) {
        header('location:UserLogin.php');
    }
    ?>
    <div id="top_div">
        <?php
        require './elements_LQA/top.php'
        ?>
    </div>
    <div class="row">
        <div id="left_div">
            <?php
            require './elements_LQA/left.php'
            ?>
        </div>
        <div id="center_div">
            <?php
            require './elements_LQA/center.php';
            ?>
        </div>

    </div>
    <div id="right_div"></div>
    <div id="bottom_div"></div>
    <div id="signoutbutton">
        <a href="./elements_LQA/mUser/userAct.php?reqact=userlogout">
            <img src="./img_LQA/Logout.png" class="iconbutton">
        </a>
    </div>

</body>

</html>