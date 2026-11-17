<?php

$output = [];

$request_parts = [];

$qColumns = $qConditions = $qTables = [];


foreach ($_GET as $key => $get)
{
    switch ($key)
    {
        case 'part':
            if (is_string($get))
                $request_parts = array_unique(explode(',', $get));
            else
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.materials',
                    'reason' => 'wrongValue',
                    'location' => 'parameters',
                    'locationPart' => 'other',
                ];
            }
            break;
        default:
            $output['error']['errors'][] = [
                'message' => $key,
                'domain' => 'creatorassethub.user',
                'reason' => 'unknownParameter',
                'location' => 'parameters',
                'locationPart' => 'other',
            ];
            break;
    }
}

if (isset($output['error']))
{
    $output['error'] = array(
        'code' => '400',
        'message' => 'The invalid Request URI.',
    ) + $output['error'];

    header("HTTP/1.1 400 Bad Request");
    return;
}
else if (!is_object($data))
{
    $output['error'] = [
        'code' => '400',
        'message' => 'The invalid Request Body.',
        'errors' => [[
            'message' => 'The invalid Request Body.',
            'domain' => 'creatorassethub.phrases',
            'reason' => 'wrongValue',
            'location' => 'data',
        ]],
    ];

    header("HTTP/1.1 400 Bad Request");
    return;
}


$dataEmail = $dataPassword = null;


foreach ($data as $key => $part)
{
    switch ($key)
    {
        case 'snippet':
            if (!is_object($part))
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.phrases',
                    'reason' => 'wrongValue',
                    'location' => 'data',
                ];
                break;
            }

            foreach ($part as $part_key => $parameter)
            {
                switch ($part_key)
                {
                    case 'email':
                        if (filter_var($parameter, FILTER_VALIDATE_EMAIL) !== false)
                            $dataEmail = $parameter;
                        else
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.user',
                                'reason' => 'wrongValue',
                                'location' => 'data',
                                'locationPart' => $key,
                            ];
                        }
                        break;
                    case 'password':
                        if (is_string($parameter))
                            $dataPassword = $parameter;
                        else
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.user',
                                'reason' => 'wrongValue',
                                'location' => 'data',
                                'locationPart' => $key,
                            ];
                        }
                        break;
                    default:
                        $output['error']['errors'][] = [
                            'message' => $part_key,
                            'domain' => 'creatorassethub.phrases',
                            'reason' => 'unknownData',
                            'location' => 'data',
                            'locationPart' => $key,
                        ];
                        break;
                }
            }
            break;
        default:
            $output['error']['errors'][] = [
                'message' => $key,
                'domain' => 'creatorassethub.phrases',
                'reason' => 'unknownData',
                'location' => 'data',
            ];
            break;
    }
}



if (isset($output['error']))
{
    $output['error'] = array(
        'code' => '400',
        'message' => 'The invalid Request Body.',
    ) + $output['error'];

    header("HTTP/1.1 400 Bad Request");
}
else if (isset($_SESSION['user']['id']))
{
    $output = array(
        'kind' => 'users#account',
        'error' => [
            'code' => '200',
            'message' => 'User is already authorized.',
            'errors' => [[
                'message' => 'User is already authorized.',
                'domain' => 'creatorassethub.user',
                'reason' => 'userAuthorized',
                'location' => 'data',
            ]],
        ],
    );

    foreach ($request_parts as $part)
    {
        switch ($part)
        {
            case 'snippet':

                $output['snippet'] = [
                    'userEmail' => $_SESSION['user']['email'] ?? '',
                    'userName' => $_SESSION['user']['name'] ?? '',
                    'isUserVerificated' => $_SESSION['user']['verification'],
                    'isUserModerator' => $_SESSION['user']['role'] ? true : false,
                ];
                
                break;
            default:
                $output['error']['errors'][] = [
                    'message' => $part,
                    'domain' => 'creatorassethub.user',
                    'reason' => 'unknownPart',
                    'location' => 'part',
                    'locationPart' => 'parameter',
                ];
                break;
        }
    }

    header("HTTP/1.1 200 OK");
}
else
{
    $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');



    // НАЧАЛЬНАЯ НАСТРОЙКА

    array_push($qColumns,
        'ud.`user_id`',
        'ud.`password`',
        'ud.`role`',
        'ud.`verification`',
        'ud.`name`',
    );

    $qConditions[] = [ 'condition' => 'ud.`email` = ?', 'parameter' => $dataEmail, ];

    // НАЧАЛЬНАЯ НАСТРОЙКА



    foreach ($request_parts as $part)
    {
        switch ($part)
        {
            case 'snippet':
                break;
            default:
                $output['error']['errors'][] = [
                    'message' => $part,
                    'domain' => 'creatorassethub.user',
                    'reason' => 'unknownPart',
                    'location' => 'part',
                    'locationPart' => 'parameter',
                ];
                break;
        }
    }

    if (isset($output['error']))
    {
        $output = array('error' => $output['error']);

        $output['error'] = array(
            'code' => '400',
            'message' => 'The invalid Request URI.',
        ) + $output['error'];

        header("HTTP/1.1 400 Bad Request");
    }
    else
    {
        $cols = implode(', ', array_unique($qColumns));
        $tables = implode(' ', array_unique($qTables));

        $conditions = count($qConditions) ? 'WHERE '.implode(' AND ', array_map(fn($c) => "($c)", array_column($qConditions, 'condition'))) : '';

        $query = "SELECT $cols FROM `users_data` ud $tables $conditions;";
        
        $rows = libSqlQuery($link, $query, ...array_column($qConditions, 'parameter'));
        
        if ($rows && $rows[0])
        {
            if (password_verify($dataPassword, $rows[0]['ud_password']) === true)
            {
                $_SESSION['user']['id'] = $rows[0]['ud_user_id'];
                $_SESSION['user']['email'] = $dataEmail;
                $_SESSION['user']['name'] = $rows[0]['ud_name'];
                $_SESSION['user']['verification'] = (bool)$rows[0]['ud_verification'];
                $_SESSION['user']['role'] = $rows[0]['ud_role'];
    
                $output = array(
                    'kind' => 'users#account',
                    'id' => $rows[0]['ud_user_id'],
                );
    
                foreach ($request_parts as $part)
                {
                    switch ($part)
                    {
                        case 'snippet':
    
                            $output['snippet'] = [
                                'userEmail' => $dataEmail,
                                'userName' => $rows[0]['ud_name'],
                                'isUserVerificated' => (bool)$rows[0]['ud_verification'],
                                'isUserModerator' => $rows[0]['ud_role'] ? true : false,
                            ];
    
                            break;
                        default:
                            break;
                    }
                }
    
                header("HTTP/1.1 200 OK");
            }
            else
            {
                $output['error'] = [
                    'code' => '404',
                    'message' => 'The user not found or autentification not passed.',
                    'errors' => [[
                        'message' => 'The user not found or autentification not passed.',
                        'domain' => 'creatorassethub.user',
                        'reason' => 'userNotFound',
                    ]],
                ];
    
                header("HTTP/1.1 404 Not Found");
            }
        }
        else
        {
            $output['error'] = [
                'code' => '404',
                'message' => 'The user not found or autentification not passed.',
                'errors' => [[
                    'message' => 'The user not found or autentification not passed.',
                    'domain' => 'creatorassethub.user',
                    'reason' => 'userNotFound',
                ]],
            ];

            header("HTTP/1.1 404 Not Found");
        }
    }

    mysqli_close($link);
}