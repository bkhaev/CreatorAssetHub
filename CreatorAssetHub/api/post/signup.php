<?php

$output = [];

$dataVerification = $dataRole = null;

$qUsersDataColumns = [];

$passwordHashOptions = [
    'cost' => 12,
];

function validPassword($password)
{
    $chr_pass = "a-zA-Z0-9`~!@#$%^&*()_+-={}|:;<>?,.";
    $lpass = strlen($password);
    if ($lpass > 7 && preg_match("/^[$chr_pass]+$/", $password))
    {
        $low = false;
        $upper = false;
        $digit = false;
        for ($i = 0; $i < $lpass; $i++)
        {
            if ($password[$i] >= '0' && $password[$i] <= '9')
                $digit = true;
            else
            {
                if ($password[$i] === strtolower($password[$i]))
                    $lower = true;
                if ($password[$i] === strtoupper($password[$i]))
                    $upper = true;
            }
            if ($digit && $upper && $lower)
                return true;
        }
    }
    return false;
}


foreach ($_GET as $key => $get)
{
    switch ($key)
    {
        default:
            $output['error']['errors'][] = [
                'message' => $key,
                'domain' => 'creatorassethub.user',
                'reason' => 'unknownParameter',
                'location' => 'parameters',
                'locationPart' => 'other',
            ];
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


$dataEmail = $dataName = $dataPassword = null;


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
                    case 'name':
                        if (is_string($parameter) && mb_strlen($parameter) <= 25)
                            $dataName = $parameter;
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
                        if (is_string($parameter) && validPassword($parameter))
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
    $output['error'] = array(
        'code' => '403',
        'message' => 'User has no permissions to registration new user.',
        'errors' => [[
            'message' => 'User has no permissions to registration new user.',
            'domain' => 'creatorassethub.user',
            'reason' => 'actionForbidden',
        ]],
    );

    header("HTTP/1.1 403 Forbidden");
}
else
{
    $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');


    $dataVerification = false;
    $dataRole = 0;



    $query = "SELECT true FROM `users_data` WHERE `email`=?;";
        
    $rows = libSqlQuery($link, $query, $dataEmail);

    if (is_array($rows))
    {
        if ($rows)
        {
            $output['error'] = array(
                'code' => '409',
                'message' => "The user with this login is already signed up.",
                'errors' => [[
                    'message' => 'The user with this login is already signed up.',
                    'domain' => 'creatorassethub.user',
                    'reason' => 'userConflict',
                ]],
            );
    
            header("HTTP/1.1 409 Conflict");
            return;
        }
    }


    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    mysqli_begin_transaction($link);

    try
    {
        $query = "INSERT INTO `users`() VALUES ();";
        
        $result = libSqlQuery($link, $query);

        if ($result)
        {
            $passwordHash = password_hash($dataPassword, PASSWORD_BCRYPT, $passwordHashOptions);

            array_push($qUsersDataColumns,
                [ 'column' => '`user_id`', 'parameter' => $result, ],
                [ 'column' => '`email`', 'parameter' => $dataEmail, ],
                [ 'column' => '`password`', 'parameter' => $passwordHash, ],
            );

            if ($dataName)
                $qUsersDataColumns[] = [ 'column' => '`name`', 'parameter' => $dataName, ];

            
            $cols = implode(', ', array_column($qUsersDataColumns, 'column'));
            $parameters = implode(',', array_fill(0, count($qUsersDataColumns), '?'));
            
            $query = "INSERT INTO `users_data`($cols) VALUES ($parameters);";
        
            libSqlQuery($link, $query, ...array_column($qUsersDataColumns, 'parameter'));

            $_SESSION['user']['id'] = $result;
            $_SESSION['user']['email'] = $dataEmail;
            $_SESSION['user']['name'] = $dataName;
            $_SESSION['user']['verification'] = $dataVerification;
            $_SESSION['user']['role'] = $dataRole;

            $output = array(
                'kind' => 'users#account',
            );

            mysqli_commit($link);
        }
        else
        {
            $output['error'] = array(
                'code' => '520',
                'message' => "Unknown error was been occured when new author was adding.",
                'errors' => [
                    [
                        'message' => 'New user was not been registered.',
                        'domain' => 'creatorassethub.user',
                        'reason' => 'unknownError',
                    ],
                ],
            );

            header("HTTP/1.1 520 Unknown Error");
            return;
        }
        
    }
    catch (mysqli_sql_exception $exception)
    {
        mysqli_rollback($link);

        $output['error'] = array(
            'code' => '500',
            'message' => "Server error was occured when it tried to registrate new user.",
            'errors' => [
                [
                    'message' => (string)$exception,
                    'domain' => 'creatorassethub.phrases',
                    'reason' => 'serverError',
                ],
            ],
        );

        header("HTTP/1.1 500 Internal Server Error");
    }
}