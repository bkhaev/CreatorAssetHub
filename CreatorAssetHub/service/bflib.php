<?php

// Creator Asset Hub Helper Functions Library



$libHashTagBadSymbols = [' ', '#', ';', ':', '@', '"', '=', '-', '+', '*', '/', '%', '!', "?", '<', '>', '{', '}', '[', ']', '(', ')'];

$libPersonNameBadSymbols = [';', ':', '.', '@', '"', '=', '-', '+', '*', '/', '%', '!', "?", '<', '>', '{', '}', '[', ']', '(', ')', '\\', '|'];



// Выполняет SQL-запрос в базу данных, используя подготовленные запросы
function libSqlQuery($link, $query, ...$parameters)
{
    $stmt = mysqli_prepare($link, $query);

    if (count($parameters))
    {
        $types = '';
        foreach($parameters as &$parameter)
        {
            if(is_int($parameter))
                $types .= 'i';
            else if (is_float($parameter))
                $types .= 'd';
            else if (is_string($parameter))
                $types .= 's';
            else if (is_bool($parameter))
            {
                $parameter = (int)$parameter;
                $types .= 'i';
            }
            else
                $types .= 'b'; //blob and unknown
        }
        mysqli_stmt_bind_param($stmt, $types, ...$parameters);
    }

    mysqli_stmt_execute($stmt);

    $result = true;
    
    switch (strtoupper(substr(trim($query, '('), 0, 4))) {
        case 'SELE':
            $resultmeta = mysqli_stmt_result_metadata($stmt);
            $columns_vars = [];
            while ($field = mysqli_fetch_field($resultmeta))
            {
                $varName = $field->table ? $field->table.'_'.$field->name : $field->name;
                $columns[] = $varName;
                $columns_vars[] = &${$varName};
            }

            mysqli_stmt_bind_result($stmt, ...$columns_vars);
            
            $rows = [];
            while (mysqli_stmt_fetch($stmt))
            {
                $row = [];
                foreach ($columns as $column)
                {
                    // echo "$column\n";
                    $row[$column] = ${$column};
                }
                $rows[] = $row;
            }

            $result = $rows;
            
            break;
        case 'CALL':
            $result = mysqli_stmt_get_result($stmt);
            $result = mysqli_fetch_all($result);
            break;
        case 'INSE':
            $result = mysqli_insert_id($link);
            break;
        case 'UPDA':
            $result = mysqli_affected_rows($link);
            break;
        case 'DELE':
            $result = mysqli_affected_rows($link);
            break;
        default:
            break;
    }
    
    // $result = mysqli_stmt_get_result($stmt);
    // mysqli_free_result($result);
    mysqli_stmt_close($stmt);
    return $result;
}



// Выполняет множественное добавление строк в базу данных, используя подготовленные запросы
function libSqlInsert($link, $query, $data)
{
    $stmt = mysqli_prepare($link, $query);

    $types = '';
    foreach($data[0] as $k => $column)
    {
        if(is_int($column))
            $types .= 'i';
        else if (is_float($column))
            $types .= 'd';
        else if (is_string($column))
            $types .= 's';
        else if (is_bool($column))
        {
            $column = (int)$column;
            $types .= 'i';
        }
        else
            $types .= 'b'; //blob and unknown
    }

    $parameters = [];
    foreach ($data[0] as $key => $parameter)
        $parameters[$key] = $parameter;
    
    mysqli_stmt_bind_param($stmt, $types, ...$parameters);
    
    foreach ($data as $insert)
    {
        foreach ($insert as $key => $parameter)
            $parameters[$key] = $parameter;
        
        mysqli_stmt_execute($stmt);
    }

    mysqli_stmt_close($stmt);
    return true;
}



// Получить ссылку на YouTube-видео с началом времени.
// Если это не YouTube-видео, будет возвращён тот же URL.
// URL должен быть подготовлен заранее в правильной форме: https://www.youtube.com/watch?v=[id]
function libTryYoutubeTimedUrl($url, $time = 0)
{
    if (substr($url, 0, 32) === 'https://www.youtube.com/watch?v=')
    {
        $youtubeVideoId = substr($url, 32);
        if (is_numeric($time))
            return "https://www.youtube.com/embed/$youtubeVideoId?start=$time";
        else
            return "https://www.youtube.com/embed/$youtubeVideoId";
    }
    else
        return $url;
}



// Создаёт ключ для редактирования фразы
// Можно передать параметр чтобы получить код в расшифрованном виде
function libGeneratePhraseEditKey(&$code = null) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $codeL = $alphabet[rand(0, strlen($alphabet) - 1)].date('P')[2].((isset($_SESSION['user']['id'])) ? sprintf("%02d", substr($_SESSION['user']['id'], -2)) : '00');
    $codeR = $alphabet[rand(0, strlen($alphabet) - 1)].$alphabet[rand(0, strlen($alphabet) - 1)].rand(0, 9).rand(0, 9);
    if ($code !== null) $code = "$codeL:$codeR";
    return md5($codeL.$codeR);
}