<?php
    require_once '../../service/config.php';
    require_once '../../service/bflib.php';
	$phrId = $_POST['x'];
	
	$warning1 = "Упс... Произошла маленькая неувязочка в системе :) Просим сообщить об этом администрации сайта.\nИдентификатор инцидента: ";
	
    $outinfo;

    $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером');
    
    $phrId = data_querify(decrypt($phrId, $phridkey));

    $query = "SELECT `phrases`.`date`, `phrases`.`access`, `phrases`.`unnamed`, `phrases`.`approved`, `phrases`.`phrase`, `phrases`.`tags`, `phrases`.`author`, `phrases`.`tones`, `phrases`.`emotions`, `phrases`.`webcam`, `phrases`.`timestamp`, `phrases`.`title`, `phrases`.`url`, `phrases`.`views`, (SELECT COUNT(*) FROM `tagged phrases` WHERE `tagged phrases`.`phrid` = $phrId) AS `svdcount`, `phrases attachment`.`title` AS `files`, `phrases attachment`.`filename`, `users`.`id` AS `userid`, `users`.`email`, `users`.`verification`, `users`.`role`, `users`.`name`, `users`.`picture`, `users`.`points`";
    if (isset($_SESSION['user']['id']))
    {
        $userid = data_querify($_SESSION['user']['id']);
        $query = $query.", `tagged phrases`.`id` AS `istagged` FROM `phrases` LEFT JOIN `phrases attachment` ON (`phrases`.`id` = `phrases attachment`.`phrid`) LEFT JOIN `users` ON (`phrases`.`userid` = `users`.`id`) LEFT JOIN `tagged phrases` ON (`tagged phrases`.`userid` = $userid AND `phrases`.`id` = `tagged phrases`.`phrid`) WHERE `phrases`.`id` = $phrId";
    }
    else
        $query = $query." FROM `phrases` LEFT JOIN `phrases attachment` ON (`phrases`.`id` = `phrases attachment`.`phrid`) LEFT JOIN `users` ON (`phrases`.`userid` = `users`.`id`) WHERE `phrases`.`id` = $phrId";
    $result = mysqli_query($link, $query) or die('Не удалось загрузить данные.');
    
    if($result)
    {
        $row = mysqli_fetch_assoc($result);

        if ($row)
        {
            $isowner = false;
            if (isset($_SESSION['user']['id']))
            {
                if ($_SESSION['user']['id'] == $row['userid'])
                {
                    $isowner = true;
                    $outinfo = $row['access']."*2*"; // фраза данного пользователя
                }
                else if ((integer)$row['access'] === 1)
                {
                    echo "1*"; // доступ к фразе ограничен
                    return 0;
                }
                else
                    $outinfo = (integer)$row['access']."*1*"; // доступ разрешён, пользователь авторизован
                
                if (empty($row['istagged']))
                {
                    // $_SESSION['phrases']['issaved'] = false;
                    $outinfo .= "*"; //  фраза не сохранена
                }
                else
                {
                    // $_SESSION['phrases']['issaved'] = true;
                    $outinfo .= "1*"; //  фраза сохранена
                }
            }
            else
            {
                if ((integer)$row['access'] === 1)
                {
                    echo "1*"; // доступ к фразе ограничен
                    return 0;
                }
                else
                    $outinfo = (integer)$row['access']."***"; // доступ разрешён, пользователь НЕ авторизован
            }
            
            if (($row['userid'] !== null && $row['unnamed'] == false) || $isowner === true)
            {
                if ($row['unnamed'] == false)
                    $userid = $row['userid'];
                else
                    $userid = false;
                
                $outinfo .= ($row['name'] === null) ? $userid."*".substr($row['email'], 0, strpos($row['email'], '@'))."*".$row['verification']."*" : $userid."*".$row['name']."*".$row['verification']."*";
                if ($row['role'] == "manager" || $row['role'] == "admin")
                    $outinfo .= '1*';
                else
                    $outinfo .= '*';
                
                if ($row['picture'] === null)
                    $outinfo .= '*';
                else
                    $outinfo .= 'data:image/jpg;charset=utf8;base64,'.base64_encode($row['picture']).'*';
            }
            else
                $outinfo .= "*****";
            
            $outinfo .= substr($row['date'], 0, 10)."*".$row['approved']."*".$row['phrase']."*".$row['tags']."*".$row['author']."*".$row['tones']."*".$row['emotions']."*".$row['webcam']."*".$row['timestamp']."*".$row['title']."*".$row['url']."*".$row['views']."*".$row['svdcount']."*".$row['filename'].":".$row['files']."?";
            
            while($row = mysqli_fetch_assoc($result))
                $outinfo .= $row['filename'].":".$row['files']."?";

            if (!(isset($_SESSION['phrases']['views'])))
                $_SESSION['phrases']['views'] = "*";
            if (strpos($_SESSION['phrases']['views'], "*{$_POST['x']}*") === false)
            {
                $query = "UPDATE `phrases` SET `views` = `views` + 1 WHERE `id` = $phrId";
                $result = mysqli_query($link, $query) or die('Не удалось загрузить данные.');

                if ($result) $_SESSION['phrases']['views'] = $_SESSION['phrases']['views']."{$_POST['x']}*";
            }
        }
        else
            $outinfo = ""; //  третий случай
    }
    else
        $outinfo = $warning1."PR-6"; //  четвёртый случай
    
    echo $outinfo;
    
    mysqli_close($link);
?>