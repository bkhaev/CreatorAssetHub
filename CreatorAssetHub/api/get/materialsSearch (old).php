<?php

$output = [];

$getPage = $getResultsPerPage = $request_parts = $getKinds = $getQuaries = $getImageTypes = $getImageBackgroundTypes = $getVideoTypes = $getFrameMinWidth = $getFrameMinHeight = $getFrameMaxWidth = $getFrameMaxHeight = $getSort = $getDirection = null;

$qColumns = $qConditions = $qTables = [];

$qImageConditions = $qVideoConditions = $qMusicConditions = $qSoundConditions = [];

// $materialKindList = ['materials#image', 'materials#video', 'materials#music', 'materials#sound'];

if (isset($_GET['kind']))
{
    if (is_string($_GET['kind']))
    {
        $getKinds = array_unique(array_map(fn($p) => mb_strtolower($p), explode(',', $_GET['kind'])));
        $qKindConditions = [];
        foreach ($getKinds as $kind)
        {
            switch ($kind) {
                case 'materials#image':
                    $qKindConditions[] = 'r.`id` IN (SELECT `images`.`material_id` FROM `images`)';
                    break;
                case 'materials#video':
                    $qKindConditions[] = 'r.`id` IN (SELECT `videos`.`material_id` FROM `videos`)';
                    break;
                case 'materials#music':
                    $qKindConditions[] = 'r.`id` IN (SELECT `music`.`material_id` FROM `music`)';
                    break;
                case 'materials#sound':
                    $qKindConditions[] = 'r.`id` IN (SELECT `sounds`.`material_id` FROM `sounds`)';
                    break;
                default:
                    $output['error'] = [
                        'code' => '400',
                        'message' => 'The invalid Request URI.',
                        'errors' => [[
                            'message' => $kind,
                            'domain' => 'creatorassethub.materials',
                            'reason' => 'unknownKind',
                            'location' => 'parameters',
                            'locationPart' => 'kind',
                        ]],
                    ];

                    header("HTTP/1.1 400 Bad Request");
                    return;
                    break;
            }
        }

        array_push($qConditions,
            [ 'condition' => implode(' OR ', $qKindConditions), ],
        );
    }
    else
    {
        $output['error'] = [
            'code' => '400',
            'message' => 'The invalid Request URI.',
            'errors' => [[
                'message' => 'kind',
                'domain' => 'creatorassethub.materials',
                'reason' => 'wrongValue',
                'location' => 'parameters',
                'locationPart' => 'other',
            ]],
        ];

        header("HTTP/1.1 400 Bad Request");
        return;
    }
}
else
{
    $output['error'] = [
        'code' => '400',
        'message' => 'The required parameter is missing.',
        'errors' => [[
            'message' => 'The kind parameter is missing.',
            'domain' => 'creatorassethub.materials',
            'reason' => 'parameterNotFound',
            'location' => 'parameters',
            'locationPart' => 'other',
        ]],
    ];

    header("HTTP/1.1 400 Bad Request");
    return;
}

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
        case 'k':
            if (is_string($get))
            {
                $getMaterials = array_unique(array_map(fn($p) => mb_strtolower($p), explode(',', $get)));
                $materials = [];
                foreach ($getMaterials as $material)
                {
                    if (array_search('materials#image', $getKinds) !== false)
                    {
                        $materials[] = 'i.`title` LIKE ?';
                        array_push($qConditions,
                            [ 'parameter' => "%$material%", ],
                        );

                        array_push($qTables,
                            'LEFT JOIN `images` i ON i.`material_id`=r.`id`',
                        );
                    }
                    if (array_search('materials#video', $getKinds) !== false)
                    {
                        $materials[] = 'v.`title` LIKE ?';
                        array_push($qConditions,
                            [ 'parameter' => "%$material%", ],
                        );

                        array_push($qTables,
                            'LEFT JOIN `videos` v ON v.`material_id`=r.`id`',
                        );
                    }
                    if (array_search('materials#music', $getKinds) !== false)
                    {
                        $materials[] = 'm.`title` LIKE ?';
                        array_push($qConditions,
                            [ 'parameter' => "%$material%", ],
                        );

                        array_push($qTables,
                            'LEFT JOIN `music` m ON m.`material_id`=r.`id`',
                        );
                    }
                    if (array_search('materials#sound', $getKinds) !== false)
                    {
                        $materials[] = 's.`title` LIKE ?';
                        array_push($qConditions,
                            [ 'parameter' => "%$material%", ],
                        );

                        array_push($qTables,
                            'LEFT JOIN `sounds` s ON s.`material_id`=r.`id`',
                        );
                    }
                }

                array_push($qConditions,
                    [ 'condition' => implode(' OR ', $materials), ],
                );
            }
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
        case 'imageType':
            if (is_string($get))
            {
                if (array_search('materials#image', $getKinds) !== false)
                {
                    $getImageTypes = array_unique(array_map(fn($p) => mb_strtolower($p), explode(',', $get)));
                    $types = [];
                    foreach ($getImageTypes as $type)
                    {
                        if (array_search('materials#image', $getKinds) !== false)
                        {
                            $types[] = 'i.`type` = ?';
                            array_push($qImageConditions,
                                [ 'parameter' => $type, ],
                            );
        
                            array_push($qTables,
                                'LEFT JOIN `images` i ON i.`material_id`=r.`id`',
                            );
                        }
                    }
                    
                    array_push($qImageConditions,
                        [ 'condition' => implode(' OR ', $types), ],
                    );
                }
            }
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
        case 'imageBackground':
            if (is_string($get))
            {
                if (array_search('materials#image', $getKinds) !== false)
                {
                    $getImageBackgroundTypes = array_unique(array_map(fn($p) => mb_strtolower($p), explode(',', $get)));
                    $types = [];
                    foreach ($getImageBackgroundTypes as $key => $type)
                    {
                        if ($type === 'transparent')
                        {
                            $getImageBackgroundTypes[$key] = 1;
                            $type = 1;
                        }
                        else if ($type === 'isolated')
                        {
                            $getImageBackgroundTypes[$key] = 2;
                            $type = 2;
                        }
                        else
                        {
                            $output['error']['errors'][] = [
                                'message' => $type,
                                'domain' => 'creatorassethub.materials',
                                'reason' => 'wrongValue',
                                'location' => 'parameters',
                                'locationPart' => 'imageBackground',
                            ];
                            break;
                        }

                        if (array_search('materials#image', $getKinds) !== false)
                        {
                            $types[] = 'i.`background` = ?';
                            array_push($qImageConditions,
                                [ 'parameter' => $type, ],
                            );
        
                            array_push($qTables,
                                'LEFT JOIN `images` i ON i.`material_id`=r.`id`',
                            );
                        }
                    }
                    
                    array_push($qImageConditions,
                        [ 'condition' => implode(' OR ', $types), ],
                    );
                }
            }
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
        case 'videoType':
            if (is_string($get))
            {
                if (array_search('materials#video', $getKinds) !== false)
                {
                    $getVideoTypes = array_unique(array_map(fn($p) => mb_strtolower($p), explode(',', $get)));
                    $types = [];
                    foreach ($getVideoTypes as $type)
                    {
                        if (array_search('materials#video', $getKinds) !== false)
                        {
                            $types[] = 'v.`type` = ?';
                            array_push($qVideoConditions,
                                [ 'parameter' => $type, ],
                            );
        
                            array_push($qTables,
                                'LEFT JOIN `videos` v ON v.`material_id`=r.`id`',
                            );
                        }
                    }
                    
                    array_push($qVideoConditions,
                        [ 'condition' => implode(' OR ', $types), ],
                    );
                }
            }
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
        case 'frameMinWidth':
            if (is_numeric($get))
            {
                if (array_search('materials#image', $getKinds) !== false || array_search('materials#video', $getKinds) !== false)
                {
                    $getFrameMinWidth = $get;
                    
                    array_push($qImageConditions,
                        [ 'condition' => 'f.`width` >= ?', 'parameter' => $get, ],
                    );
                    array_push($qVideoConditions,
                        [ 'condition' => 'f.`width` >= ?', 'parameter' => $get, ],
                    );

                    array_push($qTables,
                        'LEFT JOIN `files_of_materials` fm ON fm.`material_id`=r.`id`',
                        'LEFT JOIN `files` f ON f.`id`=fm.`file_id`',
                    );
                }
            }
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
        case 'frameMinHeight':
            if (is_numeric($get))
            {
                if (array_search('materials#image', $getKinds) !== false || array_search('materials#video', $getKinds) !== false)
                {
                    $getFrameMinHeight = $get;
                    
                    array_push($qImageConditions,
                        [ 'condition' => 'f.`height` >= ?', 'parameter' => $get, ],
                    );
                    array_push($qVideoConditions,
                        [ 'condition' => 'f.`height` >= ?', 'parameter' => $get, ],
                    );

                    array_push($qTables,
                        'LEFT JOIN `files_of_materials` fm ON fm.`material_id`=r.`id`',
                        'LEFT JOIN `files` f ON f.`id`=fm.`file_id`',
                    );
                }
            }
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
        case 'frameMaxWidth':
            if (is_numeric($get))
            {
                if (array_search('materials#image', $getKinds) !== false || array_search('materials#video', $getKinds) !== false)
                {
                    $getFrameMaxWidth = $get;
                    
                    array_push($qImageConditions,
                        [ 'condition' => 'f.`width` <= ?', 'parameter' => $get, ],
                    );
                    array_push($qVideoConditions,
                        [ 'condition' => 'f.`width` <= ?', 'parameter' => $get, ],
                    );

                    array_push($qTables,
                        'LEFT JOIN `files_of_materials` fm ON fm.`material_id`=r.`id`',
                        'LEFT JOIN `files` f ON f.`id`=fm.`file_id`',
                    );
                }
            }
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
        case 'frameMaxHeight':
            if (is_numeric($get))
            {
                if (array_search('materials#image', $getKinds) !== false || array_search('materials#video', $getKinds) !== false)
                {
                    $getFrameMaxHeight = $get;
                    
                    array_push($qImageConditions,
                        [ 'condition' => 'f.`height` <= ?', 'parameter' => $get, ],
                    );
                    array_push($qVideoConditions,
                        [ 'condition' => 'f.`height` <= ?', 'parameter' => $get, ],
                    );

                    array_push($qTables,
                        'LEFT JOIN `files_of_materials` fm ON fm.`material_id`=r.`id`',
                        'LEFT JOIN `files` f ON f.`id`=fm.`file_id`',
                    );
                }
            }
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
        case 'sort':
            switch ($get) {
                case 'latest':

                    $getSort = 'ORDER BY r.`date`';

                    break;
                case 'views':
                    
                    $getSort = 'ORDER BY r.`views`';

                    break;
                case 'downloads':
                    
                    $getSort = 'ORDER BY f.`downloads`';

                    array_push($qTables,
                        'LEFT JOIN `files_of_materials` fm ON fm.`material_id`=r.`id`',
                        'LEFT JOIN `files` f ON f.`id`=fm.`file_id`',
                    );

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
            if ($key !== 'kind')
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.materials',
                    'reason' => 'unknownParameter',
                    'location' => 'parameters',
                    'locationPart' => 'other',
                ];
            }
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
else if (array_search(null, [$request_parts], true) !== false)
{
    $output['error'] = [
        'code' => '400',
        'message' => 'The required parameter is missing.',
        'errors' => [[
            'message' => 'The required parameter is missing.',
            'domain' => 'creatorassethub.materials',
            'reason' => 'parameterNotFound',
            'location' => 'parameters',
            'locationPart' => 'other',
        ]],
    ];

    header("HTTP/1.1 400 Bad Request");
}
else
{
    $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');

    $PermissionImagesVideosEditDelete = $PermissionMusicEditDelete = $PermissionSoundsEditDelete = false;

    if (isset($_SESSION['user']['id']))
    {
        $query = [];
        if (array_search('materials#image', $getKinds) !== false || array_search('materials#video', $getKinds) !== false)
            $query[] = "(SELECT true FROM `users_data` ud LEFT JOIN `user_permissions` up ON up.`bit` & ud.`role` WHERE ud.`user_id` = ? AND up.`name` = 'ImagesVideos-Edit-Delete') AS `ImagesVideos-Edit-Delete`";
        if (array_search('materials#music', $getKinds) !== false)
            $query[] = "(SELECT true FROM `users_data` ud LEFT JOIN `user_permissions` up ON up.`bit` & ud.`role` WHERE ud.`user_id` = ? AND up.`name` = 'Music-Edit-Delete') AS `Music-Edit-Delete`";
        if (array_search('materials#sound', $getKinds) !== false)
            $query[] = "(SELECT true FROM `users_data` ud LEFT JOIN `user_permissions` up ON up.`bit` & ud.`role` WHERE ud.`user_id` = ? AND up.`name` = 'Sounds-Edit-Delete') AS `Sounds-Edit-Delete`";

        if ($query)
        {
            $rows = libSqlQuery($link, 'SELECT '.implode(',', $query), array_fill(0, count($query), $_SESSION['user']['id']));

            if ($rows)
            {
                if (isset($rows[0]['ImagesVideos-Edit-Delete']) && $rows[0]['ImagesVideos-Edit-Delete'])
                    $PermissionImagesVideosEditDelete = true;
                if (isset($rows[0]['Music-Edit-Delete']) && $rows[0]['Music-Edit-Delete'])
                    $PermissionMusicEditDelete = true;
                if (isset($rows[0]['Sounds-Edit-Delete']) && $rows[0]['Sounds-Edit-Delete'])
                    $PermissionSoundsEditDelete = true;
            }
        }
    }



    // НАЧАЛЬНАЯ НАСТРОЙКА

    if ($getPage === null)
        $getPage = 1;
    
    if ($getResultsPerPage === null)
        $getResultsPerPage = 20;
    
    if ($getSort === null)
        $getSort = 'ORDER BY r.`date`';
    
    if ($getDirection === null)
        $getDirection = 'DESC'; // ВНИМАТЕЛЬНО: НАПРАВЛЕНИЕ БЕЗ ORDER BY ВЫВЕДЕТ ОШИБКУ!

    $resultsShift = $getPage * $getResultsPerPage - $getResultsPerPage;


    $imageConditions = $videoConditions = $musicConditions = $soundConditions = '';
    if ($qImageConditions)
    {
        $imageConditions = implode(' AND ', array_map(fn($c) => "($c)", array_column($qImageConditions, 'condition')));
        foreach (array_column($qImageConditions, 'parameter') as $value)
        {
            array_push($qConditions,
                [ 'parameter' => $value, ],
            );
        }
    }
    if ($qVideoConditions)
    {
        $videoConditions = implode(' AND ', array_map(fn($c) => "($c)", array_column($qVideoConditions, 'condition')));
        foreach (array_column($qVideoConditions, 'parameter') as $value)
        {
            array_push($qConditions,
                [ 'parameter' => $value, ],
            );
        }
    }
    if ($qMusicConditions)
    {
        $musicConditions = implode(' AND ', array_map(fn($c) => "($c)", array_column($qMusicConditions, 'condition')));
        foreach (array_column($qMusicConditions, 'parameter') as $value)
        {
            array_push($qConditions,
                [ 'parameter' => $value, ],
            );
        }
    }
    if ($qSoundConditions)
    {
        $soundConditions = implode(' AND ', array_map(fn($c) => "($c)", array_column($qSoundConditions, 'condition')));
        foreach (array_column($qSoundConditions, 'parameter') as $value)
        {
            array_push($qConditions,
                [ 'parameter' => $value, ],
            );
        }
    }

    
    array_push($qColumns,
        'CASE WHEN r.`id` IN (SELECT `images`.`material_id` FROM `images`) THEN \'image\' WHEN r.`id` IN (SELECT `videos`.`material_id` FROM `videos`) THEN \'video\' WHEN r.`id` IN (SELECT `music`.`material_id` FROM `music`) THEN \'music\' WHEN r.`id` IN (SELECT `sounds`.`material_id` FROM `sounds`) THEN \'sound\' ELSE \'unknown\' END AS `material_type`',
        'r.`id`',
    );

    array_push($qConditions,
        [ 'condition' => 'r.`access` IS true', ],
        [ 'condition' => implode(' OR ', array_map(fn($c) => "($c)", array_filter([$imageConditions, $videoConditions, $musicConditions, $soundConditions]))), ],
    );

    // НАЧАЛЬНАЯ НАСТРОЙКА


    foreach ($request_parts as $part)
    {
        switch ($part)
        {
            case 'snippet':

                array_push($qColumns,
                    'r.`date`',
                    'f.`file_name`',
                    'f.`width`',
                    'f.`height`',
                    'f.`duration`',
                    'f.`fps`',
                );

                array_push($qTables,
                    'LEFT JOIN `files_of_materials` fm ON fm.`material_id`=r.`id`',
                    'LEFT JOIN `files` f ON f.`id`=fm.`file_id`',
                );
            
                if (array_search('materials#image', $getKinds) !== false)
                {
                    array_push($qColumns,
                        'i.`title`',
                    );
                        
                    array_push($qTables,
                        'LEFT JOIN `images` i ON i.`material_id`=r.`id`',
                    );
                }

                if (array_search('materials#video', $getKinds) !== false)
                {
                    array_push($qColumns,
                        'v.`title`',
                    );
                        
                    array_push($qTables,
                        'LEFT JOIN `videos` v ON v.`material_id`=r.`id`',
                    );
                }

                if (array_search('materials#music', $getKinds) !== false)
                {
                    array_push($qColumns,
                        'm.`title`',
                        'm.`vocals`',
                        'm.`explicit`',
                        'm.`yt_free_use`',
                        'mr.`title`',
                        'mr.`date`',
                        'mr.`picture_name`',
                    );
                        
                    array_push($qTables,
                        'LEFT JOIN `music` m ON m.`material_id`=r.`id`',
                        'LEFT JOIN `m_releases` mr ON mr.`id`=m.`release_id`',
                    );
                }

                if (array_search('materials#sound', $getKinds) !== false)
                {
                    array_push($qColumns,
                        's.`title`',
                        'p.`id`',
                        'p.`name`',
                    );
                        
                    array_push($qTables,
                        'LEFT JOIN `sounds` s ON s.`material_id`=r.`id`',
                        'LEFT JOIN `persons` p ON p.`id`=s.`supplier_id`',
                    );
                }

                break;
            case 'contentDetails':
                
                if ($PermissionImagesVideosEditDelete || $PermissionMusicEditDelete || $PermissionSoundsEditDelete)
                {
                    array_push($qColumns,
                        'r.`notes`',
                    );
                }

                if (isset($_SESSION['user']['id']))
                {
                    $playlists = [];
                    if (array_search('materials#image', $getKinds) !== false)
                        $playlists[] = 'up.title=\'Понравившиеся изображения\'';
                    if (array_search('materials#video', $getKinds) !== false)
                        $playlists[] = 'up.title=\'Понравившиеся видео\'';
                    if (array_search('materials#video', $getKinds) !== false)
                        $playlists[] = 'up.title=\'Понравившаяся музыка\'';
                    if (array_search('materials#video', $getKinds) !== false)
                        $playlists[] = 'up.title=\'Понравившиеся звуки\'';

                    if ($playlists)
                        $playlists = 'AND ('.implode(' OR ', $playlists).')';
                    else
                        $playlists = '';

                    array_push($qColumns,
                        "(SELECT true FROM `materials_of_playlists` mp LEFT JOIN `user_playlists` up ON up.`id`=mp.`playlist_id` WHERE mp.material_id = p.material_id $playlists AND up.user_id = ?) AS `isSaved`",
                    );

                    array_push($qConditions,
                        [ 'parameter' => $_SESSION['user']['id'], ],
                    );
                }
                
                break;
            case 'statistics':

                array_push($qColumns,
                    'r.`views`',
                );
                
                break;
            default:
                $output['error']['errors'][] = [
                    'message' => $part,
                    'domain' => 'creatorassethub.materials',
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
        $getMaterialsResult = [];
        
        $cols = implode(', ', array_unique($qColumns));
        $tables = implode(' ', array_unique($qTables));

        $conditions = count($qConditions) ? 'WHERE '.implode(' AND ', array_map(fn($c) => "($c)", array_column($qConditions, 'condition'))) : '';

        $query = "SELECT COUNT(DISTINCT r.`id`) AS 'total' FROM `materials` r $tables $conditions;";
        // echo "$query\n";
        // var_dump(array_column($qConditions, 'parameter'));
        // exit();
        $rows = libSqlQuery($link, $query, ...array_column($qConditions, 'parameter'));
        // echo "$query\n";
        // exit();
        $totalResults = $rows ? $rows[0]['total'] : 0;
        
        $query = "SELECT $cols FROM `materials` r $tables $conditions GROUP BY r.`id` $getSort $getDirection LIMIT $resultsShift, $getResultsPerPage;";
        // echo "$query\n<br>";
        // var_dump(array_column($qConditions, 'parameter'));
        // exit();
        $rows = libSqlQuery($link, $query, ...array_column($qConditions, 'parameter'));
        
        if ($rows)
        {
            foreach ($rows as $row) // $rows as $key => $row
            {
                if ($row['material_type'] === 'image')
                    $materialKind = 'materials#image';
                else if ($row['material_type'] === 'video')
                    $materialKind = 'materials#video';
                else if ($row['material_type'] === 'music')
                    $materialKind = 'materials#music';
                else if ($row['material_type'] === 'sound')
                    $materialKind = 'materials#sound';
                else
                    $materialKind = '';

                $item = array(
                    'kind' => $materialKind,
                    'id' => $row['r_id'],
                );
    
                foreach ($request_parts as $part)
                {
                    switch ($part)
                    {
                        case 'snippet':

                            $item['snippet'] = [
                                'publishedAt' => $row['r_date'],
                                'previewFile' => [
                                    'fileName' => $row['f_file_name'],
                                ],
                            ];

                            if ($materialKind === 'materials#image')
                            {
                                $item['snippet']['title'] = $row['i_title'];
                                $item['snippet']['previewFile']['width'] = $row['f_width'];
                                $item['snippet']['previewFile']['height'] = $row['f_height'];
                            }
                            else if ($materialKind === 'materials#video')
                            {
                                $item['snippet']['title'] = $row['v_title'];
                                $item['snippet']['previewFile']['width'] = $row['f_width'];
                                $item['snippet']['previewFile']['height'] = $row['f_height'];
                                $item['snippet']['previewFile']['duration'] = $row['f_duration'];
                                $item['snippet']['previewFile']['fps'] = $row['f_fps'];
                            }
                            else if ($materialKind === 'materials#music')
                            {
                                $item['snippet']['title'] = $row['m_title'];
                                $item['snippet']['hasVocals'] = $row['m_vocals'];
                                $item['snippet']['hasExplicit'] = $row['m_explicit'];
                                $item['snippet']['isYoutubeFreeUsable'] = $row['m_yt_free_use'];
                                $item['snippet']['release'] = [
                                    'releaseDate' => $row['mr_date'],
                                    'releaseTitle' => $row['mr_title'],
                                    'releasePicture' => $row['mr_picture_name'],
                                ];
                                $item['snippet']['previewFile']['duration'] = $row['f_duration'];
                            }
                            else if ($materialKind === 'materials#sound')
                            {
                                $item['snippet']['title'] = $row['s_title'];
                                $item['snippet']['previewFile']['duration'] = $row['f_duration'];
                                $item['snippet']['supplier'] = [
                                    'supplierId' => $row['p_id'],
                                    'supplierTitle' => $row['p_name'],
                                ];
                            }
                            
                            break;
                        case 'contentDetails':
    
                            if ($PermissionImagesVideosEditDelete || $PermissionMusicEditDelete || $PermissionSoundsEditDelete)
                            {
                                $item['contentDetails']['notes'] = $row['r_notes'];
                            }

                            if (isset($_SESSION['user']['id']))
                            {
                                $item['contentDetails']['isSavedInFavorites'] = $row['isSaved'];
                            }
                            
                            break;
                        case 'statistics':
    
                            $item['statistics'] = [
                                'viewCount' => $row['r_views'],
                            ];
                            
                            break;
                        default:
                            break;
                    }
                }
    
                $getMaterialsResult[] = $item;
            }
        }
        
        
        mysqli_close($link);

        $output = array(
            'kind' => 'materials#materialsListResponse',
            'pageInfo' => [
                'total' => $totalResults,
                'resultsPerPage' => $getResultsPerPage,
            ],
            'items' => $getMaterialsResult,
        ) + $output;

        header("HTTP/1.1 200 OK");
    }
}