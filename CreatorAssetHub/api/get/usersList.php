<?php

$output = [];

$usersIds = [];

$request_parts = $qColumns = $qColumnsParameters = $qConditions = $qTables = [];

$getPage = $getResultsPerPage = $getSort = $getDirection = null;

$getMine = false;

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
                    'domain' => 'creatorassethub.user',
                    'reason' => 'wrongValue',
                    'location' => 'parameters',
                    'locationPart' => 'other',
                ];
            }
            break;
        case 'page':
            if (is_numeric($get) && $get > 1)
                $getPage = $get;
            break;
        case 'resultsPerPage':
            if (is_numeric($get))
            {
                if ($get < 10)
                    $getResultsPerPage = 10;
                else if ($get > 50)
                    $getResultsPerPage = 50;
                else
                    $getResultsPerPage = $get;
            }
            break;
        case 'id':
            if (is_string($get))
            {
                $ids = [];
                foreach (array_unique(explode(',', $get)) as $id)
                {
                    if (is_numeric($id))
                    {
                        $usersIds[] = $id;
                        $ids[] = 'ud.`user_id` = ?';
                        $qConditions[] = [ 'parameter' => $id ];
                    }
                    else
                    {
                        $output['error']['errors'][] = [
                            'message' => $key,
                            'domain' => 'creatorassethub.user',
                            'reason' => 'wrongValue',
                            'location' => 'parameters',
                            'locationPart' => 'other',
                        ];
                    }
                }

                $qConditions[] = [ 'condition' => implode(' OR ', $ids), ];
            }
            else
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.user',
                    'reason' => 'wrongValue',
                    'location' => 'parameters',
                    'locationPart' => 'other',
                ];
            }
            break;
        case 'mine':
            if ($get !== "")
            {
                $get = filter_var($get, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($get !== null)
                    $getMine = $get;
                else
                {
                    $output['error']['errors'][] = [
                        'message' => $key,
                        'domain' => 'creatorassethub.user',
                        'reason' => 'wrongValue',
                        'location' => 'parameters',
                        'locationPart' => 'other',
                    ];
                }
            }
            else
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.user',
                    'reason' => 'wrongValue',
                    'location' => 'parameters',
                    'locationPart' => 'other',
                ];
            }
            break;
        case 'sort':
            switch ($get) {
                case 'points':

                    // $getSort = 'ORDER BY `r_create_date`';

                    break;
                case 'views':
                    
                    // $getSort = 'ORDER BY `r_views`';

                    break;
                default:
                    $output['error']['errors'][] = [
                        'message' => $key,
                        'domain' => 'creatorassethub.materials',
                        'reason' => 'wrongValue',
                        'location' => 'parameters',
                        'locationPart' => 'other',
                    ];
                    break;
            }
            break;
        case 'direction':
            if ($get === 'asc' || $get === 'desc')
                $getDirection = $get;
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
}
else if (!$getMine && !$request_parts)
{
    $output['error'] = [
        'code' => '400',
        'message' => 'The required parameter is missing.',
        'errors' => [[
            'message' => 'The required parameter is missing.',
            'domain' => 'creatorassethub.phrases',
            'reason' => 'parameterNotFound',
            'location' => 'parameters',
            'locationPart' => 'other',
        ]],
    ];

    header("HTTP/1.1 400 Bad Request");
}
else if ($getMine && $usersIds)
{
    $output['error'] = [
        'code' => '400',
        'message' => 'Incompatible parameters specified in the request: mine, id.',
        'errors' => [[
            'message' => 'Incompatible parameters specified in the request: mine, id.',
            'domain' => 'creatorassethub.phrases',
            'reason' => 'incompatibleParameters',
            'location' => 'parameters',
            'locationPart' => 'other',
        ]],
    ];

    header("HTTP/1.1 400 Bad Request");
}
else
{
    $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');


    // НАЧАЛЬНАЯ НАСТРОЙКА

    if ($getPage === null)
        $getPage = 1;
    
    if ($getResultsPerPage === null)
        $getResultsPerPage = 20;
    
    if ($getDirection === null)
        $getDirection = 'DESC';

    if ($getSort !== null)
        $getSort .= " $getDirection";
    
    $resultsShift = $getPage * $getResultsPerPage - $getResultsPerPage;

    $qColumns[] = 'ud.`user_id`';

    // НАЧАЛЬНАЯ НАСТРОЙКА


    foreach ($request_parts as $part)
    {
        switch ($part)
        {
            case 'snippet':

                array_push($qColumns,
                    'ud.`email`',
                    'ud.`name`',
                    // 'SUBSTRING(ud.`about`, 1, 500) AS `previewAbout`',
                    'ud.`about`',
                    'ud.`role`',
                    'ud.`verification`',
                    'ud.`last_date`',
                    'u.`reg_date`',
                    'u.`points`',
                );

                $qTables[] = 'LEFT JOIN `users` u ON u.`id`=ud.`user_id`';

                break;
            case 'picture':

                $qColumns[] = 'ud.`picture`';

                break;
            case 'statistics':

                array_push($qColumns,
                    'u.`points`',
                    '(SELECT COUNT(*) FROM `phrases` p LEFT JOIN `materials` m ON m.`id`=p.`material_id` WHERE m.`user_id` = ud.`user_id` AND p.`access` = 2 AND p.`anonym` IS false) AS `total_phrases_count`',
                    // '(SELECT COUNT(*) FROM `` WHERE  = ud.`user_id`) AS `total_items_count`',
                    // '(SELECT COUNT(*) FROM `` WHERE  = ud.`user_id`) AS `total_views_count`',
                );

                $qTables[] = 'LEFT JOIN `users` u ON u.`id`=ud.`user_id`';

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
        if ($getMine)
        {
            if (isset($_SESSION['user']['id']))
            {
                $output = [
                    'kind' => 'users#account',
                    'id' => $_SESSION['user']['id'],
                ];

                foreach ($request_parts as $part)
                {
                    switch ($part)
                    {
                        case 'snippet':

                            $query = "SELECT ud.`about`, ud.`last_date`, u.`reg_date`, u.`points` FROM `users_data` ud LEFT JOIN `users` u ON u.`id`=ud.`user_id` WHERE ud.`user_id` = ?;";

                            $rows = libSqlQuery($link, $query, $_SESSION['user']['id']);
                
                            if (is_array($rows) && $rows)
                            {
                                $query = "SELECT `display_name`, `url` FROM `users_links` WHERE `user_id` = ?;";
                                
                                $links = libSqlQuery($link, $query, $_SESSION['user']['id']);

                                $userLinks = [];

                                if (is_array($rows))
                                {
                                    foreach ($links as $link)
                                    {
                                        $userLinks[] = [
                                            'title' => $link['users_links_display_name'],
                                            'url' => $link['users_links_url'],
                                        ];
                                    }
                                }
                                else
                                {
                                    $output['error'] = array(
                                        'code' => '520',
                                        'message' => "Cannot get user personal links.",
                                        'errors' => [[
                                            'message' => 'Cannot get user personal links.',
                                            'domain' => 'creatorassethub.user',
                                            'reason' => 'unknownError',
                                        ]],
                                    );
                        
                                    header("HTTP/1.1 520 Unknown Error");
                                }

                                $output['snippet'] = [
                                    'email' => $_SESSION['user']['email'] ?? '',
                                    'name' => $_SESSION['user']['name'] ?? '',
                                    'registeredAt' => $rows[0]['u_reg_date'],
                                    'lastDateOnline' => $rows[0]['ud_last_date'],
                                    'profileUrl' => 'http://localhost/fans/'.$_SESSION['user']['id'],
                                    'about' => (string)$rows[0]['ud_about'],
                                    'personalLinks' => $userLinks,
                                    'isUserVerificated' => $_SESSION['user']['verification'] ?? '',
                                    'isUserModerator' => $_SESSION['user']['role'] ? true : false,
                                ];
                            }
                            else
                            {
                                $output['error'] = array(
                                    'code' => '520',
                                    'message' => "Cannot get snippet.",
                                    'errors' => [[
                                        'message' => 'Cannot get user snippet.',
                                        'domain' => 'creatorassethub.user',
                                        'reason' => 'unknownError',
                                    ]],
                                );
                    
                                header("HTTP/1.1 520 Unknown Error");
                            }
                            
                            break;
                        case 'picture':

                            $query = "SELECT `picture` FROM `users_data` WHERE `user_id` = ?;";

                            $rows = libSqlQuery($link, $query, $_SESSION['user']['id']);
                
                            if (is_array($rows))
                            {
                                if ($rows && $rows[0]['users_data_picture'])
                                {
                                    $userPictureOriginal = [
                                        'inBytes' => 'data:image/jpg;charset=utf8;base64,'.base64_encode($rows[0]['users_data_picture']),
                                    ];
                                }
                                else
                                    $userPictureOriginal = [];

                                $output['picture'] = [
                                    'userPictureOriginal' => $userPictureOriginal,
                                ];
                            }
                            else
                            {
                                $output['error'] = array(
                                    'code' => '520',
                                    'message' => "Cannot get user picture.",
                                    'errors' => [[
                                        'message' => 'Cannot get user picture.',
                                        'domain' => 'creatorassethub.user',
                                        'reason' => 'unknownError',
                                    ]],
                                );
                    
                                header("HTTP/1.1 520 Unknown Error");
                            }

                            break;
                        case 'statistics':
                            
                            $query = "SELECT (SELECT `points` FROM `users` WHERE `id` = ?) AS `total_points`, (SELECT COUNT(*) FROM `phrases` p LEFT JOIN `materials` m ON m.`id`=p.`material_id` WHERE m.`user_id` = ? AND p.`access` = 2 AND p.`anonym` IS false) AS `total_phrases_count`;";

                            $rows = libSqlQuery($link, $query, $_SESSION['user']['id'], $_SESSION['user']['id']);
                
                            if (is_array($rows) && $rows[0])
                            {
                                $output['statistics'] = [
                                    'totalPoints' => $rows[0]['total_points'],
                                    'totalPhrasesCount' => $rows[0]['total_phrases_count'],
                                    // 'userTotalViewCount' => $rows[0]['total_items_count'],
                                ];
                            }
                    }
                }

                header("HTTP/1.1 200 OK");
            }
            else
            {
                header("HTTP/1.1 401 Unauthorized");

                $output = array(
                    'error' => [
                        'code' => '401',
                        'message' => 'User did not authorize yet.',
                        'errors' => [[
                            'message' => 'User did not authorize yet.',
                            'domain' => 'creatorassethub.user',
                            'reason' => 'userUnauthorized',
                        ]],
                    ],
                );
            }
        }
        else
        {
            $getUsersResult = [];



            $cols = implode(', ', array_unique($qColumns));
            $tables = implode(' ', array_unique($qTables));
            $conditions = count($qConditions) ? 'WHERE '.implode(' AND ', array_map(fn($c) => "($c)", array_column($qConditions, 'condition'))) : '';

            $query = "SELECT COUNT(*) AS 'total' FROM `users_data` ud $tables $conditions;";
            // echo "$query\n";
            // var_dump(array_column($qConditions, 'parameter'));
            // exit();
            $rows = libSqlQuery($link, $query, ...array_column($qConditions, 'parameter'));
            
            $totalResults = $rows ? $rows[0]['total'] : 0;

            $query = "SELECT $cols FROM `users_data` ud $tables $conditions $getSort LIMIT $resultsShift, $getResultsPerPage;";
            // echo "$query\n";
            // var_dump(array_merge($qColumnsParameters, array_column($qConditions, 'parameter')));
            // exit();
            $rows = libSqlQuery($link, $query, ...array_merge($qColumnsParameters, array_column($qConditions, 'parameter')));

            if (is_array($rows))
            {
                foreach ($rows as $row) {
                    $item = [
                        'kind' => 'users#user',
                        'id' => $row['ud_user_id'],
                    ];

                    foreach ($request_parts as $part)
                    {
                        switch ($part)
                        {
                            case 'snippet':

                                $name = $row['ud_name'] ? $row['ud_name'] : mb_substr($row['ud_email'], 0, strpos($row['ud_email'], '@'));

                                $query = "SELECT `display_name`, `url` FROM `users_links` WHERE `user_id` = ?;";
                                
                                $rows = libSqlQuery($link, $query, $row['ud_user_id']);

                                $userLinks = [];

                                if (is_array($rows))
                                {
                                    foreach ($rows as $row)
                                    {
                                        $userLinks[] = [
                                            'title' => $row['users_links_display_name'],
                                            'url' => $row['users_links_url'],
                                        ];
                                    }
                                }
                                
                                $item['snippet'] = [
                                    'registeredAt' => $row['u_reg_date'],
                                    'lastDateOnline' => $row['ud_last_date'],
                                    'displayName' => $name,
                                    'profileUrl' => 'http://localhost/fans/'.$row['ud_user_id'],
                                    'about' => (string)$row['ud_about'],
                                    'personalLinks' => $userLinks,
                                    'isVerificated' => (bool)$row['ud_verification'],
                                    'isModerator' => $row['ud_role'] ? true : false,
                                ];
                
                                break;
                            case 'picture':

                                if ($row['ud_picture'])
                                {
                                    $userPictureOriginal = [
                                        'inBytes' => 'data:image/jpg;charset=utf8;base64,'.base64_encode($row['ud_picture']),
                                    ];
                                }
                                else
                                    $userPictureOriginal = [];

                                $item['picture'] = [
                                    'userPictureOriginal' => $userPictureOriginal,
                                ];

                                break;
                            case 'statistics':

                                $item['statistics'] = [
                                    'totalPoints' => $row['u_points'],
                                    'totalPhrasesCount' => $row['total_phrases_count'],
                                    // 'totalViewCount' => $row['total_items_count'],
                                ];
                        }
                    }


                    $getUsersResult[] = $item;
                }
            }

            
            $output = array(
                'kind' => 'users#usersListResponse',
                'pageInfo' => [
                    'total' => $totalResults,
                    'resultsPerPage' => $getResultsPerPage,
                ],
                'items' => $getUsersResult,
            ) + $output;
        
            header("HTTP/1.1 200 OK");
        }
    }

    mysqli_close($link);
}