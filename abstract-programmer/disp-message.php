<!DOCTYPE html>
<html>
<head>
    <title>Display GET Parameter</title>
</head>
<body>
    <?php
    if (isset($_GET['message'])) {
        $message = $_GET['message'];
        echo "<p>" . $message . "</p>";
    } else {
        echo "<p>No message provided!</p>";
    }
    ?>
</body>
</html>