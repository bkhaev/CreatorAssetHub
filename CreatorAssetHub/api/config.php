<?php
   $host = 'localhost';
   $database = 'bfdb';
   $user = 'root';
   $password = '';
   $link = mysqli_connect($host, $user, $password, $database)
      or die("Ошибка ".mysqli_error($link));
   // mysqli_set_charset($link, "utf8mb4");
   mysqli_close($link);
?>