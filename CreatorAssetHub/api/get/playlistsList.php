<?php

$output = [];

$getPage = $getResultsPerPage = $request_parts = $getUserId = $getMine = $getSort = $getDirection = null;

$qColumns = $qColumnsParameters = $qConditions = $qTables = [];

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
                    'domain' => 'creatorassethub.playlists',
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
        case 'userId':
            if (is_numeric($get))
                $getUserId = $get;
            else
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.playlists',
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
                if ($get === true) // может принимать только значение true
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
            switch ($get)
            {
                case 'views':
                    
                    $getSort = 'ORDER BY up.`views`';

                    break;
                default:
                    $output['error']['errors'][] = [
                        'message' => $key,
                        'domain' => 'creatorassethub.playlists',
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
                    'domain' => 'creatorassethub.playlists',
                    'reason' => 'wrongValue',
                    'location' => 'parameters',
                    'locationPart' => 'other',
                ];
            }
            break;
        default:
            $output['error']['errors'][] = [
                'message' => $key,
                'domain' => 'creatorassethub.playlists',
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
else if ($request_parts === null || ($getUserId === null && $getMine === null))
{
    $output['error'] = [
        'code' => '400',
        'message' => 'The required parameter is missing.',
        'errors' => [[
            'message' => 'The required parameter is missing.',
            'domain' => 'creatorassethub.playlists',
            'reason' => 'parameterNotFound',
            'location' => 'parameters',
            'locationPart' => 'other',
        ]],
    ];

    header("HTTP/1.1 400 Bad Request");
}
else if ($getUserId !== null && $getMine !== null)
{
    $output['error'] = [
        'code' => '400',
        'message' => 'Incompatible parameters specified in the request: userId, mine.',
        'errors' => [[
            'message' => 'Incompatible parameters specified in the request: userId, mine.',
            'domain' => 'creatorassethub.playlists',
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

    if ($getMine === true)
    {
        if (isset($_SESSION['user']['id']))
        {
            $qConditions[] = [ 'condition' => 'up.`user_id` = ?', 'parameter' => $_SESSION['user']['id'], ];
            $getUserId = $_SESSION['user']['id'];
        }
        else
        {
            $output = array(
                'error' => [
                    'code' => '401',
                    'message' => 'User did not authorize yet.',
                    'errors' => [[
                        'message' => 'User did not authorize yet.',
                        'domain' => 'creatorassethub.playlists',
                        'reason' => 'userUnauthorized',
                    ]],
                ],
            );

            header("HTTP/1.1 401 Unauthorized");
            return;
        }
    }
    else
    {
        $query = "SELECT true FROM `users_data` ud WHERE ud.`user_id` = ?;";

        $rows = libSqlQuery($link, $query, $getUserId);
        
        if (!$rows || !$rows[0]['TRUE'])
        {
            $output = array(
                'error' => [
                    'code' => '404',
                    'message' => 'User not found.',
                    'errors' => [[
                        'message' => 'User not found.',
                        'domain' => 'creatorassethub.playlists',
                        'reason' => 'userNotFound',
                        'location' => 'userId',
                        'locationPart' => 'parameter',
                    ]],
                ],
            );

            header("HTTP/1.1 404 Not Found");
            return;
        }

        array_push($qConditions,
            [ 'condition' => 'up.`user_id` = ?', 'parameter' => $getUserId, ],
            [ 'condition' => 'up.`access` = 1 OR up.`access` = 2', ],
        );
    }

    if ($getPage === null)
        $getPage = 1;
    
    if ($getResultsPerPage === null)
        $getResultsPerPage = 20;
    
    if ($getSort === null)
        $getSort = 'ORDER BY up.`updated`';
    
    if ($getDirection === null)
        $getDirection = 'DESC';

    $resultsShift = $getPage * $getResultsPerPage - $getResultsPerPage;

    $qColumns[] = 'up.`id`';

    // НАЧАЛЬНАЯ НАСТРОЙКА


    foreach ($request_parts as $part)
    {
        switch ($part)
        {
            case 'snippet':

                array_push($qColumns,
                    'up.`date`',
                    'up.`user_id`',
                    'up.`updated`',
                    'up.`title`',
                    'up.`description`',
                );

                break;
            case 'contentDetails':

                $qColumns[] = 'up.`resource_type`';

                if ($getMine === true)
                    $qColumns[] = 'up.`access`';
                
                break;
            case 'userDetails':

                array_push($qColumns,
                    'ud.`user_id`',
                    'ud.`name`',
                    'ud.`email`',
                    'ud.`role`',
                    'ud.`verification`',
                );
                
                $qTables[] = 'LEFT JOIN `users_data` ud ON ud.`user_id`=up.`user_id`';
                
                break;
            case 'statistics':

                $qColumns[] = 'up.`views`';
                
                break;
            default:
                $output['error']['errors'][] = [
                    'message' => $part,
                    'domain' => 'creatorassethub.playlists',
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
        $getPlaylistsResult = [];
        
        $cols = implode(', ', array_unique($qColumns));
        $tables = implode(' ', array_unique($qTables));
        $conditions = count($qConditions) ? 'WHERE '.implode(' AND ', array_map(fn($c) => "($c)", array_column($qConditions, 'condition'))) : '';

        $query = "SELECT COUNT(DISTINCT up.`id`) AS 'total' FROM `user_playlists` up $tables $conditions;";
        
        $rows = libSqlQuery($link, $query, ...array_column($qConditions, 'parameter'));
        
        $totalResults = $rows ? $rows[0]['total'] : 0;
        
        $query = "SELECT $cols FROM `user_playlists` up $tables $conditions GROUP BY up.`id` $getSort $getDirection LIMIT $resultsShift, $getResultsPerPage;";
        
        $rows = libSqlQuery($link, $query, ...array_merge($qColumnsParameters, array_column($qConditions, 'parameter')));
        
        if (is_array($rows))
        {
            foreach ($rows as $row)
            {
                $item = array(
                    'kind' => 'playlists#playlist',
                    'id' => $row['up_id'],
                );
    
                foreach ($request_parts as $part)
                {
                    switch ($part)
                    {
                        case 'snippet':
                            
                            $item['snippet'] = [
                                'publishedAt' => $row['up_date'],
                                'userId' => $getUserId,
                                'title' => $row['up_title'],
                                'description' => $row['up_description'],
                                'lastUpdated' => $row['up_updated'],
                            ];
                            
                            break;
                        case 'contentDetails':
    
                            $item['contentDetails'] = [
                                'resourceType' => $row['up_resource_type'],
                            ];
    
                            if ($getMine === true)
                                $item['contentDetails']['accessMode'] = $row['up_access'];
                            
                            break;
                        case 'userDetails':
    
                            $item['userDetails'] = [
                                'userId' => $row['ud_user_id'],
                                'userName' => $row['ud_name'] ? $row['ud_name'] : substr($row['ud_email'], 0, strpos($row['ud_email'], '@')),
                                'verificationStatus' => $row['ud_verification'],
                                'moderationStatus' => $row['ud_role'] ? true : false,
                            ];
                            
                            break;
                        case 'statistics':
    
                            $item['statistics'] = [
                                'viewCount' => $row['up_views'],
                            ];
                            
                            break;
                        default:
                            break;
                    }
                }
    
                $getPlaylistsResult[] = $item;
            }
        }
        
        
        mysqli_close($link);

        $output = array(
            'kind' => 'playlists#playlistListResponse',
            'pageInfo' => [
                'total' => $totalResults,
                'resultsPerPage' => $getResultsPerPage,
            ],
            'items' => $getPlaylistsResult,
        ) + $output;

        header("HTTP/1.1 200 OK");
    }
}