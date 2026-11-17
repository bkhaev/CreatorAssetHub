<?php

$output = [];

$getPage = $getResultsPerPage = $request_parts = $getPlaylistId = $getMine = $getPhrases = $getAuthors = $getTones = $getEmotions = $getSort = $getDirection = null;

$getAccessModes = [];

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
                    'domain' => 'creatorassethub.phrases',
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
        case 'phrase':
            if (is_string($get))
            {
                $getPhrases = array_unique(array_map(fn($p) => mb_strtolower($p), explode(',', $get)));
                $phrases = [];
                foreach ($getPhrases as $phrase) {
                    $phrases[] = 'p.`phrase` LIKE ?';
                    $qConditions[] = [ 'parameter' => "%$phrase%", ];
                }

                $qConditions[] = [ 'condition' => implode(' OR ', $phrases), ];
            }
            else
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.phrases',
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
                        'domain' => 'creatorassethub.phrases',
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
                    'domain' => 'creatorassethub.phrases',
                    'reason' => 'wrongValue',
                    'location' => 'parameters',
                    'locationPart' => 'other',
                ];
            }
            break;
        case 'accessMode':
            if (is_string($get))
            {
                $getAccessModes = array_unique(explode(',', $get));
                $modes = [];
                foreach ($getAccessModes as $mode) {
                    switch ($mode) {
                        case 'open':
                            $modes[] = 'p.`access` = 2';
                            break;
                        case 'shared':
                            $modes[] = 'p.`access` = 1';
                            break;
                        case 'private':
                            $modes[] = 'p.`access` = 0';
                            break;
                        default:
                            $output['error']['errors'][] = [
                                'message' => $key,
                                'domain' => 'creatorassethub.phrases',
                                'reason' => 'wrongValue',
                                'location' => 'parameters',
                                'locationPart' => 'other',
                            ];
                            break;
                    }
                }

                $qConditions[] = [ 'condition' => implode(' OR ', $modes), ];
            }
            else
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.phrases',
                    'reason' => 'wrongValue',
                    'location' => 'parameters',
                    'locationPart' => 'other',
                ];
            }
            break;
        case 'playlistId':
            if (is_numeric($get))
                $getPlaylistId = $get;
            else
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.phrases',
                    'reason' => 'wrongValue',
                    'location' => 'parameters',
                    'locationPart' => 'other',
                ];
            }
            break;
        case 'author':
            if (is_string($get))
            {
                $getAuthors = array_unique(explode(',', $get));
                $authors = [];
                foreach ($getAuthors as $author) {
                    $authors[] = 'a.`name` = ?';
                    $qConditions[] = [ 'parameter' => $author ];
                }
                
                $qConditions[] = [ 'condition' => implode(' OR ', $authors), ];

                array_push($qTables,
                    'LEFT JOIN `authors_of_phrases` af ON af.`phrase_id`=p.`material_id`',
                    'LEFT JOIN `persons` a ON a.`id`=af.`person_id`',
                );
            }
            else
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.phrases',
                    'reason' => 'wrongValue',
                    'location' => 'parameters',
                    'locationPart' => 'other',
                ];
            }
            break;
        case 'tone':
            if (is_string($get))
            {
                $getTones = array_unique(explode(',', $get));
                foreach ($getTones as $tone) {
                    switch ($tone) {
                        case 'low':
                            $qConditions[] = [ 'condition' => 'p.`tone` = \'L\' OR p.`tone` = \'LM\' OR p.`tone` = \'LH\'', ];
                            break;
                        case 'medium':
                            $qConditions[] = [ 'condition' => 'p.`tone` = \'M\' OR p.`tone` = \'LM\' OR p.`tone` = \'MH\'', ];
                            break;
                        case 'high':
                            $qConditions[] = [ 'condition' => 'p.`tone` = \'H\' OR p.`tone` = \'LH\' OR p.`tone` = \'MH\'', ];
                            break;
                        default:
                            $output['error']['errors'][] = [
                                'message' => $key,
                                'domain' => 'creatorassethub.phrases',
                                'reason' => 'wrongValue',
                                'location' => 'parameters',
                                'locationPart' => 'other',
                            ];
                            break;
                    }
                }
            }
            else
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.phrases',
                    'reason' => 'wrongValue',
                    'location' => 'parameters',
                    'locationPart' => 'other',
                ];
            }
            break;
        case 'emotion':
            if (is_string($get))
            {
                $getEmotions = array_unique(explode(',', $get));
                $emotions = [];
                foreach ($getEmotions as $emotion) {
                    $emotions[] = 'p.`emotion` = ?';
                    $qConditions[] = [ 'parameter' => $emotion, ];
                }
                
                $qConditions[] = [ 'condition' => implode(' OR ', $emotions), ];
            }
            else
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.phrases',
                    'reason' => 'wrongValue',
                    'location' => 'parameters',
                    'locationPart' => 'other',
                ];
            }
            break;
        case 'sort':
            switch ($get) {
                case 'latest':

                    $getSort = 'ORDER BY m.`date`';

                    $qTables[] = 'LEFT JOIN `materials` m ON m.`id`=p.`material_id`';

                    break;
                case 'views':
                    
                    $getSort = 'ORDER BY m.`views`';

                    $qTables[] = 'LEFT JOIN `materials` m ON m.`id`=p.`material_id`';

                    break;
                case 'saved':
                    
                    $getSort = 'ORDER BY (SELECT COUNT(*) FROM `materials_of_playlists` mp LEFT JOIN `user_playlists` up ON up.`id`=mp.`playlist_id` WHERE mp.material_id=p.material_id AND up.title=\'Понравившиеся фразы\')';

                    break;
                case 'approved':
                    
                    $getSort = 'ORDER BY p.`approved`';

                    break;
                default:
                    $output['error']['errors'][] = [
                        'message' => $key,
                        'domain' => 'creatorassethub.phrases',
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
                    'domain' => 'creatorassethub.phrases',
                    'reason' => 'wrongValue',
                    'location' => 'parameters',
                    'locationPart' => 'other',
                ];
            }
            break;
        default:
            $output['error']['errors'][] = [
                'message' => $key,
                'domain' => 'creatorassethub.phrases',
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
else if ($request_parts === null)
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
else if ($getMine !== null && $getPlaylistId !== null)
{
    $output['error'] = [
        'code' => '400',
        'message' => 'Incompatible parameters specified in the request: playlistId, mine.',
        'errors' => [[
            'message' => 'Incompatible parameters specified in the request: playlistId, mine.',
            'domain' => 'creatorassethub.phrases',
            'reason' => 'incompatibleParameters',
            'location' => 'parameters',
            'locationPart' => 'other',
        ]],
    ];

    header("HTTP/1.1 400 Bad Request");
}
else if ($getMine === null && $getAccessModes)
{
    $output['error'] = [
        'code' => '400',
        'message' => 'The invalid Request URI.',
        'errors' => [[
            'message' => implode(',', $getAccessModes),
            'domain' => 'creatorassethub.phrases',
            'reason' => 'unknownParameter',
            'location' => 'parameters',
            'locationPart' => 'other',
        ]],
    ];

    header("HTTP/1.1 400 Bad Request");
}
else // part=snippet &page=6 &phrase=утро%2Bдоброе%2Cпросыпайся%2Cвставай &author=[скрыто] &emotion=средний &direction=asc
{
    $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');

    $PermissionPhrasesEditDelete = false;

    if (isset($_SESSION['user']['id']))
    {
        $query = "SELECT true FROM `users_data` ud LEFT JOIN `user_permissions` up ON up.`bit` & ud.`role` WHERE ud.`user_id` = ? AND up.`name` = 'Phrases-Edit-Delete';";

        $rows = libSqlQuery($link, $query, $_SESSION['user']['id']);
        
        if ($rows && $rows[0]['TRUE'])
            $PermissionPhrasesEditDelete = true;
    }



    // НАЧАЛЬНАЯ НАСТРОЙКА

    if ($getMine === true)
    {
        if (isset($_SESSION['user']['id'])) {
            $qConditions[] = [ 'condition' => 'm.`user_id` = ?', 'parameter' => $_SESSION['user']['id'], ];
            $qTables[] = 'LEFT JOIN `materials` m ON m.`id`=p.`material_id`';
        }
        else
        {
            $output = array(
                'error' => [
                    'code' => '401',
                    'message' => 'User did not authorize yet.',
                    'errors' => [[
                        'message' => 'User did not authorize yet.',
                        'domain' => 'creatorassethub.phrases',
                        'reason' => 'userUnauthorized',
                    ]],
                ],
            );

            header("HTTP/1.1 401 Unauthorized");
        }
    }

    if ($getPlaylistId)
    {
        $query = "SELECT up.`access`, up.`user_id` FROM `user_playlists` up WHERE up.`id` = ?;";

        $rows = libSqlQuery($link, $query, $getPlaylistId);
        
        if (is_array($rows))
        {
            if ($rows)
            {
                if ($rows[0]['up_access'] === 1 || $rows[0]['up_access'] === 2 || (isset($_SESSION['user']['id']) && $rows[0]['up_user_id'] === $_SESSION['user']['id']))
                {
                    $qConditions[] = [ 'condition' => 'mp.`playlist_id` = ?', 'parameter' => $getPlaylistId, ];

                    $qTables[] = 'LEFT JOIN `materials_of_playlists` mp ON mp.`material_id`=p.`material_id`';
                }
                else
                {
                    $output['error'] = array(
                        'code' => '403',
                        'message' => "The requester is not allowed to access the playlist.",
                        'errors' => [[
                            'message' => "The requester is not allowed to access the playlist.",
                            'domain' => 'creatorassethub.phrases',
                            'reason' => 'phrasesForbidden',
                        ]],
                    );
        
                    header("HTTP/1.1 403 Forbidden");
                    return;
                }
            }
            else
            {
                $output = array(
                    'error' => [
                        'code' => '404',
                        'message' => 'Playlist not found.',
                        'errors' => [[
                            'message' => 'Playlist not found.',
                            'domain' => 'creatorassethub.phrases',
                            'reason' => 'playlistNotFound',
                            'location' => 'playlisId',
                            'locationPart' => 'parameter',
                        ]],
                    ],
                );
    
                header("HTTP/1.1 404 Not Found");
                return;
            }
        }
    }

    if ($getPage === null)
        $getPage = 1;
    
    if ($getResultsPerPage === null)
        $getResultsPerPage = 20;
    
    if ($getSort === null)
    {
        $getSort = 'ORDER BY m.`date`';

        $qTables[] = 'LEFT JOIN `materials` m ON m.`id`=p.`material_id`';
    }
    
    if ($getDirection === null)
        $getDirection = 'DESC'; // ВНИМАТЕЛЬНО: НАПРАВЛЕНИЕ БЕЗ ORDER BY ВЫВЕДЕТ ОШИБКУ!

    $resultsShift = $getPage * $getResultsPerPage - $getResultsPerPage;

    $qColumns[] = 'p.`material_id`';

    array_push($qConditions,
        [ 'condition' => 'm.`access` IS true', ],
        [ 'condition' => 'p.`access` = 2', ],
    );

    // НАЧАЛЬНАЯ НАСТРОЙКА


    foreach ($request_parts as $part)
    {
        switch ($part)
        {
            case 'snippet':

                array_push($qColumns,
                    'm.`date`',
                    'm.`user_id`',
                    'p.`anonym`',
                    'p.`phrase`',
                    'p.`tone`',
                    'p.`emotion`',
                    'p.`camera`',
                    's.`url`',
                    's.`time`',
                    'fm.`material_id`',
                    // ,'(SELECT GROUP_CONCAT(synonym SEPARATOR \';\') FROM `phrases_synonyms` ps WHERE ps.phrase_id=p.material_id) AS `synonyms`'
                );

                array_push($qTables,
                    'LEFT JOIN `materials` m ON m.`id`=p.`material_id`',
                    'LEFT JOIN `sources` s ON s.`material_id`=p.`material_id`',
                    'LEFT JOIN `files_of_materials` fm ON fm.`material_id`=p.`material_id`',
                );

                break;
            case 'contentDetails':

                array_push($qColumns,
                    'p.`approved`',
                    'p.`access`',
                );
                
                if ($PermissionPhrasesEditDelete)
                {
                    $qColumns[] = 'm.`notes`';

                    $qTables[] = 'LEFT JOIN `materials` m ON m.`id`=p.`material_id`';
                }

                if (isset($_SESSION['user']['id']))
                {
                    $qColumns[] = '(SELECT CONCAT(mp.`id`, \',\', IF(mp.`notes` IS NULL, \'\', mp.`notes`)) FROM `materials_of_playlists` mp LEFT JOIN `user_playlists` up ON up.`id`=mp.`playlist_id` WHERE mp.`material_id`=p.`material_id` AND up.title=\'Понравившиеся фразы\' AND up.user_id=?) AS `saved_id_note`';

                    $qColumnsParameters[] = $_SESSION['user']['id'];
                }
                
                break;
            case 'userDetails':

                array_push($qColumns,
                    'p.`anonym`',
                    'ud.`user_id`',
                    'ud.`email`',
                    'ud.`name`',
                    'ud.`role`',
                    'ud.`verification`',
                );
                
                array_push($qTables,
                    'LEFT JOIN `materials` m ON m.`id`=p.`material_id`',
                    'LEFT JOIN `users_data` ud ON ud.`user_id`=m.`user_id`',
                );

                if ($PermissionPhrasesEditDelete)
                {
                    $qColumns[] = 'u.`id`';

                    $qTables[] = 'LEFT JOIN `users` u ON u.`id`=m.`user_id`';
                }
                
                break;
            case 'statistics':

                $qColumns[] = 'm.`views`';

                $qTables[] = 'LEFT JOIN `materials` m ON m.`id`=p.`material_id`';
                
                break;
            default:
                $output['error']['errors'][] = [
                    'message' => $part,
                    'domain' => 'creatorassethub.phrases',
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
        $getPhrasesResult = [];
        
        $cols = implode(', ', array_unique($qColumns));
        $tables = implode(' ', array_unique($qTables));
        $conditions = count($qConditions) ? 'WHERE '.implode(' AND ', array_map(fn($c) => "($c)", array_column($qConditions, 'condition'))) : '';

        $query = "SELECT COUNT(DISTINCT p.`material_id`) AS 'total' FROM `phrases` p $tables $conditions;";
        $rows = libSqlQuery($link, $query, ...array_column($qConditions, 'parameter'));
        $totalResults = $rows ? $rows[0]['total'] : 0;
        
        $query = "SELECT $cols FROM `phrases` p $tables $conditions GROUP BY p.`material_id` $getSort $getDirection LIMIT $resultsShift, $getResultsPerPage;";
        $rows = libSqlQuery($link, $query, ...array_merge($qColumnsParameters, array_column($qConditions, 'parameter')));
        
        if (is_array($rows))
        {
            foreach ($rows as $row) // $rows as $key => $row
            {
                $item = array(
                    'kind' => 'phrases#phrase',
                    'id' => $row['p_material_id'],
                );
    
                foreach ($request_parts as $part)
                {
                    switch ($part)
                    {
                        case 'snippet':

                            $userId = $row['p_anonym'] === 1 ? '' : $row['m_user_id'];

                            if ($row['s_url'] !== null)
                            {
                                $previewSource = [
                                    'url' => libTryYoutubeTimedUrl($row['s_url'], $row['s_time']),
                                    'time' => $row['s_time'],
                                ];
                            }
                            else if ($row['fm_material_id'] !== null)
                            {
                                $previewSource = [
                                    'url' => "http://localhost/embed/{$row['p_material_id']}",
                                ];
                            }
                            else
                                $previewSource = [];

                            $phraseAuthors = [];

                            $query = "SELECT p.`id`, p.`name` FROM `persons` p LEFT JOIN `authors_of_phrases` ap ON ap.person_id=p.id WHERE ap.phrase_id={$row['p_material_id']};";

                            $rows1 = libSqlQuery($link, $query);
                            
                            if (is_array($rows1))
                            {
                                foreach ($rows1 as $row1)
                                {
                                    $phraseAuthors[] = [
                                        'kind' => 'phrases#author',
                                        'authorId' => $row1['p_id'],
                                        'authorName' => $row1['p_name'],
                                    ];
                                }
                            }
                            
                            switch ($row['p_tone']) {
                                case 'L':
                                    $phraseTone = 'низкий';
                                    break;
                                case 'LM':
                                    $phraseTone = 'низко-средний';
                                    break;
                                case 'M':
                                    $phraseTone = 'средний';
                                    break;
                                case 'MH':
                                    $phraseTone = 'средне-высокий';
                                    break;
                                case 'H':
                                    $phraseTone = 'высокий';
                                    break;
                                case 'LH':
                                    $phraseTone = 'низко-высокий';
                                    break;
                                default:
                                    $phraseTone = '';
                                    break;
                            }
                            
                            $item['snippet'] = [
                                'publishedAt' => $row['m_date'],
                                'userId' => $userId,
                                'isUserAnonym' => (bool)$row['p_anonym'],
                                'phrase' => $row['p_phrase'],
                                'authors' => $phraseAuthors,
                                'tone' => $phraseTone,
                                'emotion' => $row['p_emotion'],
                                'isCameraUsed' => $row['p_camera'],
                                'previewSource' => $previewSource,
                            ];
                            
                            break;
                        case 'contentDetails':

                            $accessMode = '';
                            if ($row['p_access'] === 0)
                                $accessMode = 'ограниченный';
                            else if ($row['p_access'] === 1)
                                $accessMode = 'по ссылке';
                            else if ($row['p_access'] === 2)
                                $accessMode = 'открытый';
    
                            $item['contentDetails'] = [
                                'contentStatus' => $row['p_approved'] === 1 ? 'проверено' : 'не проверено',
                                'accessMode' => $accessMode,
                            ];
    
                            if ($PermissionPhrasesEditDelete)
                            {
                                $item['contentDetails']['notes'] = $row['m_notes'];
                            }

                            if (isset($_SESSION['user']['id']))
                            {
                                if ($row['saved_id_note'])
                                {
                                    $savedIdNote = explode(',', $row['saved_id_note']);
                                    
                                    $savedInFavoritesId = [
                                        'id' => (int)$savedIdNote[0],
                                        'note' => $savedIdNote[1],
                                    ];
                                }
                                else
                                    $savedInFavoritesId = [];
                                
                                $item['contentDetails']['savedInFavoritesId'] = $savedInFavoritesId;
                            }
                            
                            break;
                        case 'userDetails':
    
                            if ($row['p_anonym'] === 1 || !$row['ud_user_id'])
                            {
                                $item['userDetails'] = [];
                            }
                            else
                            {
                                $item['userDetails'] = [
                                    'userId' => $row['ud_user_id'],
                                    'userName' => $row['ud_name'] ? $row['ud_name'] : substr($row['ud_email'], 0, strpos($row['ud_email'], '@')),
                                    'verificationStatus' => $row['ud_verification'],
                                    'moderationStatus' => $row['ud_role'] ? true : false,
                                ];
                            }

                            if ($PermissionPhrasesEditDelete)
                            {
                                $item['userDetails']['resourceId'] = [
                                    'kind' => 'users#account',
                                    'userId' => $row['u_id'],
                                ];
                            }
                            
                            break;
                        case 'statistics':
    
                            $item['statistics'] = [
                                'viewCount' => $row['m_views'],
                            ];
                            
                            break;
                        default:
                            break;
                    }
                }
    
                $getPhrasesResult[] = $item;
            }
        }
        
        
        mysqli_close($link);

        $output = array(
            'kind' => 'phrases#phrasesListResponse',
            'pageInfo' => [
                'total' => $totalResults,
                'resultsPerPage' => $getResultsPerPage,
            ],
            'items' => $getPhrasesResult,
        ) + $output;

        header("HTTP/1.1 200 OK");
    }
}