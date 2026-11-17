<?php
    require_once '../../service/config.php';
    require_once '../../service/bflib.php';
	$phrid = decrypt($_POST['x'], $phridkey);
    if ($phrid === false)
        return;

    if (isset($_SESSION['user']['id']))
    {
        $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером');

        $userid = data_querify($_SESSION['user']['id'], $link);
        $phrid = data_querify($phrid, $link);

        $query = "CALL `phrsave`($userid,$phrid);";
        
        $result = mysqli_query($link, $query) or die('Не удалось загрузить данные.');

        if ($result)
        {
            $row = mysqli_fetch_assoc($result);
            if ($row) echo $row['phrres'];
        }
    }
?>