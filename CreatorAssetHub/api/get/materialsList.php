<?php

$output = [];

$getPage = $getResultsPerPage = $request_parts = $getPlaylistId = $getKinds = $getQuaries = $getImageTypes = $getImageBackgroundTypes = $getVideoTypes = $getFrameMinWidth = $getFrameMinHeight = $getFrameMaxWidth = $getFrameMaxHeight = $getSort = $getDirection = null;

$qImageColumns = $qImageColumnsParameters = $qImageConditions = $qImageTables = [];
$qVideoColumns = $qVideoColumnsParameters = $qVideoConditions = $qVideoTables = [];
$qMusicColumns = $qMusicColumnsParameters = $qMusicConditions = $qMusicTables = [];
$qSoundColumns = $qSoundColumnsParameters = $qSoundConditions = $qSoundTables = [];

$materialKindList = ['materials#image', 'materials#video', 'materials#music', 'materials#sound'];

// Добавляет к столбцам новый.
// Если ранее данный столбец уже добавлялся и имеет статическое значение null,
// то значение перезаписывается.
function helpColPush(&$array, ...$cols)
{
    foreach ($cols as $col) {
        $oldCol = array_search($col[1], array_column($array, 1));
        if ($oldCol !== false && $array[$oldCol][0] === 'null')
            $array[$oldCol] = $col;
        else if ($oldCol === false)
            $array[] = $col;
    }
}

function checkTypeFolder($name)
{
    global $filesFolder, $fileName, $elements;

    $folder = array_search($name, $elements);
    if ($folder)
    {
        unset($elements[$folder]);
        $folder = scandir("$filesFolder/$name");
        foreach ($folder as $chunk) {
            if (file_exists("$filesFolder/$name/$chunk/$fileName"))
            {
                $fileName = "$name/$chunk/$fileName";
                $elements = [];
                break;
            }
        }
    }
}



if (isset($_GET['kind']))
{
    if (is_string($_GET['kind']))
    {
        $getKinds = array_unique(array_map(fn($p) => mb_strtolower($p), explode(',', $_GET['kind'])));
        foreach ($getKinds as $kind)
        {
            if (array_search($kind, $materialKindList) === false)
            {
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
            }
        }
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
                $getQuaries = array_unique(array_map(fn($p) => mb_strtolower($p), explode(',', $get)));
                $queries = [];
                $imageQueries = $videoQueries = $musicQueries = $soundQueries = [];

                foreach ($getQuaries as $query)
                {
                    $condition = [ 'parameter' => "%$query%", ];
                    if (array_search('materials#image', $getKinds) !== false)
                    {
                        $imageQueries[] = 'i.`title` LIKE ?';
                        $qImageConditions[] = $condition;
                    }
                    if (array_search('materials#video', $getKinds) !== false)
                    {
                        $videoQueries[] = 'v.`title` LIKE ?';
                        $qVideoConditions[] = $condition;
                    }
                    if (array_search('materials#music', $getKinds) !== false)
                    {
                        $musicQueries[] = 'm.`title` LIKE ?';
                        $qMusicConditions[] = $condition;
                    }
                    if (array_search('materials#sound', $getKinds) !== false)
                    {
                        $soundQueries[] = 's.`title` LIKE ?';
                        $qSoundConditions[] = $condition;
                    }
                }

                if (array_search('materials#image', $getKinds) !== false)
                    $qImageConditions[] = [ 'condition' => implode(' OR ', $imageQueries), ];
                if (array_search('materials#video', $getKinds) !== false)
                    $qVideoConditions[] = [ 'condition' => implode(' OR ', $videoQueries), ];
                if (array_search('materials#music', $getKinds) !== false)
                    $qMusicConditions[] = [ 'condition' => implode(' OR ', $musicQueries), ];
                if (array_search('materials#sound', $getKinds) !== false)
                    $qSoundConditions[] = [ 'condition' => implode(' OR ', $soundQueries), ];
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
        case 'playlistId':
            if (is_numeric($get))
                $getPlaylistId = $get;
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
                        $types[] = 'i.`type` = ?';
                        $qImageConditions[] = [ 'parameter' => $type, ];
                    }
                    
                    $qImageConditions[] = [ 'condition' => implode(' OR ', $types), ];
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
                            $types[] = 'f.`background` = ?';
                            $qImageConditions[] = [ 'parameter' => $type, ];
                        }
                    }
                    
                    $qImageConditions[] = [ 'condition' => implode(' OR ', $types), ];
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
                        $types[] = 'v.`type` = ?';
                        $qVideoConditions[] = [ 'parameter' => $type, ];
                    }
                    
                    $qVideoConditions[] = [ 'condition' => implode(' OR ', $types), ];
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
                $getFrameMinWidth = $get;
                if (array_search('materials#image', $getKinds) !== false)
                    $qImageConditions[] = [ 'condition' => 'f.`width` >= ?', 'parameter' => $get, ];
                if (array_search('materials#video', $getKinds) !== false)
                    $qVideoConditions[] = [ 'condition' => 'f.`width` >= ?', 'parameter' => $get, ];
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
                $getFrameMinHeight = $get;
                if (array_search('materials#image', $getKinds) !== false)
                    $qImageConditions[] = [ 'condition' => 'f.`height` >= ?', 'parameter' => $get, ];
                if (array_search('materials#video', $getKinds) !== false)
                    $qVideoConditions[] = [ 'condition' => 'f.`height` >= ?', 'parameter' => $get, ];
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
                $getFrameMaxWidth = $get;
                if (array_search('materials#image', $getKinds) !== false)
                    $qImageConditions[] = [ 'condition' => 'f.`width` <= ?', 'parameter' => $get, ];
                if (array_search('materials#video', $getKinds) !== false)
                    $qVideoConditions[] = [ 'condition' => 'f.`width` <= ?', 'parameter' => $get, ];
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
                $getFrameMaxHeight = $get;
                if (array_search('materials#image', $getKinds) !== false)
                    $qImageConditions[] = [ 'condition' => 'f.`height` <= ?', 'parameter' => $get, ];
                if (array_search('materials#video', $getKinds) !== false)
                    $qVideoConditions[] = [ 'condition' => 'f.`height` <= ?', 'parameter' => $get, ];
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

                    $getSort = 'ORDER BY `r_create_date`';

                    break;
                case 'views':
                    
                    $getSort = 'ORDER BY `r_views`';

                    break;
                case 'downloads':
                    
                    $getSort = 'ORDER BY `f_downloads`';

                    if (array_search('materials#image', $getKinds) !== false)
                        helpColPush($qImageColumns, ['f.`downloads`', '`f_downloads`']);
                    if (array_search('materials#video', $getKinds) !== false)
                        helpColPush($qVideoColumns, ['f.`downloads`', '`f_downloads`']);
                    if (array_search('materials#music', $getKinds) !== false)
                        helpColPush($qMusicColumns, ['f.`downloads`', '`f_downloads`']);
                    if (array_search('materials#sound', $getKinds) !== false)
                        helpColPush($qSoundColumns, ['f.`downloads`', '`f_downloads`']);

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
            $rows = libSqlQuery($link, 'SELECT '.implode(',', $query), ...array_fill(0, count($query), $_SESSION['user']['id']));
            
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
                    if (array_search('materials#image', $getKinds) !== false)
                    {
                        $qImageConditions[] = [ 'condition' => 'mp.`playlist_id` = ?', 'parameter' => $getPlaylistId, ];
                        $qImageTables[] = 'LEFT JOIN `materials_of_playlists` mp ON mp.`material_id`=i.`material_id`';
                    }
                    if (array_search('materials#video', $getKinds) !== false)
                    {
                        $qVideoConditions[] = [ 'condition' => 'mp.`playlist_id` = ?', 'parameter' => $getPlaylistId, ];
                        $qVideoTables[] = 'LEFT JOIN `materials_of_playlists` mp ON mp.`material_id`=v.`material_id`';
                    }
                    if (array_search('materials#music', $getKinds) !== false)
                    {
                        $qMusicConditions[] = [ 'condition' => 'mp.`playlist_id` = ?', 'parameter' => $getPlaylistId, ];
                        $qMusicTables[] = 'LEFT JOIN `materials_of_playlists` mp ON mp.`material_id`=m.`material_id`';
                    }
                    if (array_search('materials#sound', $getKinds) !== false)
                    {
                        $qSoundConditions[] = [ 'condition' => 'mp.`playlist_id` = ?', 'parameter' => $getPlaylistId, ];
                        $qSoundTables[] = 'LEFT JOIN `materials_of_playlists` mp ON mp.`material_id`=s.`material_id`';
                    }
                }
                else
                {
                    $output['error'] = array(
                        'code' => '403',
                        'message' => "The requester is not allowed to access the playlist.",
                        'errors' => [[
                            'message' => "The requester is not allowed to access the playlist.",
                            'domain' => 'creatorassethub.materials',
                            'reason' => 'materialsForbidden',
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
                            'domain' => 'creatorassethub.materials',
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
    
    if ($getDirection === null)
        $getDirection = 'DESC';

    if ($getSort === null)
        $getSort = "ORDER BY `r_create_date` $getDirection";
    else
        $getSort .= " $getDirection";
    
    $resultsShift = $getPage * $getResultsPerPage - $getResultsPerPage;
    
    if (array_search('materials#image', $getKinds) !== false)
    {
        helpColPush($qImageColumns,
            ['r.`id`', '`r_id`'],
            ['\'image\'', '`r_type`'],
        );
        $qImageConditions[] = [ 'condition' => 'r.`access` IS true', ];
        $qImageTables[] = 'LEFT JOIN `materials` r ON r.`id`=i.`material_id`';
    }
    if (array_search('materials#video', $getKinds) !== false)
    {
        helpColPush($qVideoColumns,
            ['r.`id`', '`r_id`'],
            ['\'video\'', '`r_type`'],
        );
        $qVideoConditions[] = [ 'condition' => 'r.`access` IS true', ];
        $qVideoTables[] = 'LEFT JOIN `materials` r ON r.`id`=v.`material_id`';
    }
    if (array_search('materials#music', $getKinds) !== false)
    {
        helpColPush($qMusicColumns,
            ['r.`id`', '`r_id`'],
            ['\'music\'', '`r_type`'],
        );
        $qMusicConditions[] = [ 'condition' => 'r.`access` IS true', ];
        $qMusicTables[] = 'LEFT JOIN `materials` r ON r.`id`=m.`material_id`';
    }
    if (array_search('materials#sound', $getKinds) !== false)
    {
        helpColPush($qSoundColumns,
            ['r.`id`', '`r_id`'],
            ['\'sound\'', '`r_type`'],
        );
        $qSoundConditions[] = [ 'condition' => 'r.`access` IS true', ];
        $qSoundTables[] = 'LEFT JOIN `materials` r ON r.`id`=s.`material_id`';
    }

    // НАЧАЛЬНАЯ НАСТРОЙКА

    foreach ($request_parts as $part)
    {
        switch ($part)
        {
            case 'snippet':

                $getSort .= ', `fm_queue` ASC';

                if (array_search('materials#image', $getKinds) !== false)
                {
                    helpColPush($qImageColumns,
                        ['r.`date`', '`r_create_date`'],
                        ['i.`title`', '`r_title`'],
                        ['f.`file_name`', '`f_name`'],
                        ['fm.`queue`', '`fm_queue`'],
                        ['(SELECT COUNT(*) FROM `files_of_materials` fm WHERE fm.`material_id` = r.`id`)', '`r_files_count`'],
                        ['f.`width`', '`f_width`'],
                        ['f.`height`', '`f_height`'],
                        ['f.`background`', '`f_background`'],
                    );
                    
                    array_push($qImageTables,
                        'LEFT JOIN `files_of_materials` fm ON fm.`material_id`=r.`id`',
                        'LEFT JOIN `files` f ON f.`id`=fm.`file_id`',
                    );

                    if (array_search('materials#video', $getKinds) !== false)
                    {
                        helpColPush($qVideoColumns,
                            ['null', '`r_create_date`'],
                            ['null', '`r_title`'],
                            ['null', '`f_name`'],
                            ['null', '`fm_queue`'],
                            ['null', '`r_files_count`'],
                            ['null', '`f_width`'],
                            ['null', '`f_height`'],
                            ['null', '`f_background`'],
                        );
                    }
                    if (array_search('materials#music', $getKinds) !== false)
                    {
                        helpColPush($qMusicColumns,
                            ['null', '`r_create_date`'],
                            ['null', '`r_title`'],
                            ['null', '`f_name`'],
                            ['null', '`fm_queue`'],
                            ['null', '`r_files_count`'],
                            ['null', '`f_width`'],
                            ['null', '`f_height`'],
                            ['null', '`f_background`'],
                        );
                    }
                    if (array_search('materials#sound', $getKinds) !== false)
                    {
                        helpColPush($qSoundColumns,
                            ['null', '`r_create_date`'],
                            ['null', '`r_title`'],
                            ['null', '`f_name`'],
                            ['null', '`fm_queue`'],
                            ['null', '`r_files_count`'],
                            ['null', '`f_width`'],
                            ['null', '`f_height`'],
                            ['null', '`f_background`'],
                        );
                    }
                }
                if (array_search('materials#video', $getKinds) !== false)
                {
                    helpColPush($qVideoColumns,
                        ['r.`date`', '`r_create_date`'],
                        ['v.`title`', '`r_title`'],
                        ['f.`file_name`', '`f_name`'],
                        ['fm.`queue`', '`fm_queue`'],
                        ['(SELECT COUNT(*) FROM `files_of_materials` fm WHERE fm.`material_id` = r.`id`)', '`r_files_count`'],
                        ['f.`width`', '`f_width`'],
                        ['f.`height`', '`f_height`'],
                        ['f.`duration`', '`f_duration`'],
                        ['f.`fps`', '`f_fps`'],
                    );
                    
                    array_push($qVideoTables,
                        'LEFT JOIN `files_of_materials` fm ON fm.`material_id`=r.`id`',
                        'LEFT JOIN `files` f ON f.`id`=fm.`file_id`',
                    );

                    if (array_search('materials#image', $getKinds) !== false)
                    {
                        helpColPush($qImageColumns,
                            ['null', '`r_create_date`'],
                            ['null', '`r_title`'],
                            ['null', '`f_name`'],
                            ['null', '`fm_queue`'],
                            ['null', '`r_files_count`'],
                            ['null', '`f_width`'],
                            ['null', '`f_height`'],
                            ['null', '`f_duration`'],
                            ['null', '`f_fps`'],
                        );
                    }
                    if (array_search('materials#music', $getKinds) !== false)
                    {
                        helpColPush($qMusicColumns,
                            ['null', '`r_create_date`'],
                            ['null', '`r_title`'],
                            ['null', '`f_name`'],
                            ['null', '`fm_queue`'],
                            ['null', '`r_files_count`'],
                            ['null', '`f_width`'],
                            ['null', '`f_height`'],
                            ['null', '`f_duration`'],
                            ['null', '`f_fps`'],
                        );
                    }
                    if (array_search('materials#sound', $getKinds) !== false)
                    {
                        helpColPush($qSoundColumns,
                            ['null', '`r_create_date`'],
                            ['null', '`r_title`'],
                            ['null', '`f_name`'],
                            ['null', '`fm_queue`'],
                            ['null', '`r_files_count`'],
                            ['null', '`f_width`'],
                            ['null', '`f_height`'],
                            ['null', '`f_duration`'],
                            ['null', '`f_fps`'],
                        );
                    }
                }
                if (array_search('materials#music', $getKinds) !== false)
                {
                    helpColPush($qMusicColumns,
                        ['r.`date`', '`r_create_date`'],
                        ['m.`title`', '`r_title`'],
                        ['f.`file_name`', '`f_name`'],
                        ['fm.`queue`', '`fm_queue`'],
                        ['(SELECT COUNT(*) FROM `files_of_materials` fm WHERE fm.`material_id` = r.`id`)', '`r_files_count`'],
                        ['f.`vocals`', '`f_vocals`'],
                        ['m.`explicit`', '`m_explicit`'],
                        ['m.`yt_free_use`', '`m_yt_free_use`'],
                        ['mr.`title`', '`mr_title`'],
                        ['mr.`date`', '`mr_date`'],
                        ['mr.`picture_name`', '`mr_picture_name`'],
                    );
                    
                    array_push($qMusicTables,
                        'LEFT JOIN `files_of_materials` fm ON fm.`material_id`=r.`id`',
                        'LEFT JOIN `files` f ON f.`id`=fm.`file_id`',
                        'LEFT JOIN `m_releases` mr ON mr.`id`=m.`release_id`',
                    );

                    if (array_search('materials#image', $getKinds) !== false)
                    {
                        helpColPush($qImageColumns,
                            ['null', '`r_create_date`'],
                            ['null', '`r_title`'],
                            ['null', '`f_name`'],
                            ['null', '`fm_queue`'],
                            ['null', '`r_files_count`'],
                            ['null', '`f_vocals`'],
                            ['null', '`m_explicit`'],
                            ['null', '`m_yt_free_use`'],
                            ['null', '`mr_title`'],
                            ['null', '`mr_date`'],
                            ['null', '`mr_picture_name`'],
                        );
                    }
                    if (array_search('materials#video', $getKinds) !== false)
                    {
                        helpColPush($qVideoColumns,
                            ['null', '`r_create_date`'],
                            ['null', '`r_title`'],
                            ['null', '`f_name`'],
                            ['null', '`fm_queue`'],
                            ['null', '`r_files_count`'],
                            ['null', '`f_vocals`'],
                            ['null', '`m_explicit`'],
                            ['null', '`m_yt_free_use`'],
                            ['null', '`mr_title`'],
                            ['null', '`mr_date`'],
                            ['null', '`mr_picture_name`'],
                        );
                    }
                    if (array_search('materials#sound', $getKinds) !== false)
                    {
                        helpColPush($qSoundColumns,
                            ['null', '`r_create_date`'],
                            ['null', '`r_title`'],
                            ['null', '`f_name`'],
                            ['null', '`fm_queue`'],
                            ['null', '`r_files_count`'],
                            ['null', '`f_vocals`'],
                            ['null', '`m_explicit`'],
                            ['null', '`m_yt_free_use`'],
                            ['null', '`mr_title`'],
                            ['null', '`mr_date`'],
                            ['null', '`mr_picture_name`'],
                        );
                    }
                }
                if (array_search('materials#sound', $getKinds) !== false)
                {
                    helpColPush($qSoundColumns,
                        ['r.`date`', '`r_create_date`'],
                        ['s.`title`', '`r_title`'],
                        ['f.`file_name`', '`f_name`'],
                        ['fm.`queue`', '`fm_queue`'],
                        ['(SELECT COUNT(*) FROM `files_of_materials` fm WHERE fm.`material_id` = r.`id`)', '`r_files_count`'],
                        ['p.`id`', '`p_id`'],
                        ['p.`name`', '`p_name`'],
                    );

                    array_push($qSoundTables,
                        'LEFT JOIN `files_of_materials` fm ON fm.`material_id`=r.`id`',
                        'LEFT JOIN `files` f ON f.`id`=fm.`file_id`',
                        'LEFT JOIN `persons` p ON p.`id`=s.`supplier_id`',
                    );

                    if (array_search('materials#image', $getKinds) !== false)
                    {
                        helpColPush($qImageColumns,
                            ['null', '`r_create_date`'],
                            ['null', '`r_title`'],
                            ['null', '`f_name`'],
                            ['null', '`fm_queue`'],
                            ['null', '`r_files_count`'],
                            ['null', '`p_id`'],
                            ['null', '`p_name`'],
                        );
                    }
                    if (array_search('materials#video', $getKinds) !== false)
                    {
                        helpColPush($qVideoColumns,
                            ['null', '`r_create_date`'],
                            ['null', '`r_title`'],
                            ['null', '`f_name`'],
                            ['null', '`fm_queue`'],
                            ['null', '`r_files_count`'],
                            ['null', '`p_id`'],
                            ['null', '`p_name`'],
                        );
                    }
                    if (array_search('materials#music', $getKinds) !== false)
                    {
                        helpColPush($qMusicColumns,
                            ['null', '`r_create_date`'],
                            ['null', '`r_title`'],
                            ['null', '`f_name`'],
                            ['null', '`fm_queue`'],
                            ['null', '`r_files_count`'],
                            ['null', '`p_id`'],
                            ['null', '`p_name`'],
                        );
                    }
                }

                break;
            case 'contentDetails':
                
                if ($PermissionImagesVideosEditDelete)
                {
                    if (array_search('materials#image', $getKinds) !== false)
                        helpColPush($qImageColumns, ['r.`notes`', '`r_notes`']);
                    if (array_search('materials#video', $getKinds) !== false)
                        helpColPush($qVideoColumns, ['r.`notes`', '`r_notes`']);
                    if (array_search('materials#music', $getKinds) !== false)
                        helpColPush($qMusicColumns, ['null', '`r_notes`']);
                    if (array_search('materials#sound', $getKinds) !== false)
                        helpColPush($qSoundColumns, ['null', '`r_notes`']);
                }
                if ($PermissionMusicEditDelete)
                {
                    if (array_search('materials#image', $getKinds) !== false)
                        helpColPush($qImageColumns, ['null', '`r_notes`']);
                    if (array_search('materials#video', $getKinds) !== false)
                        helpColPush($qVideoColumns, ['null', '`r_notes`']);
                    if (array_search('materials#music', $getKinds) !== false)
                        helpColPush($qMusicColumns, ['r.`notes`', '`r_notes`']);
                    if (array_search('materials#sound', $getKinds) !== false)
                        helpColPush($qSoundColumns, ['null', '`r_notes`']);
                }
                if ($PermissionSoundsEditDelete)
                {
                    if (array_search('materials#image', $getKinds) !== false)
                        helpColPush($qImageColumns, ['null', '`r_notes`']);
                    if (array_search('materials#video', $getKinds) !== false)
                        helpColPush($qVideoColumns, ['null', '`r_notes`']);
                    if (array_search('materials#music', $getKinds) !== false)
                        helpColPush($qMusicColumns, ['null', '`r_notes`']);
                    if (array_search('materials#sound', $getKinds) !== false)
                        helpColPush($qSoundColumns, ['r.`notes`', '`r_notes`']);
                }

                if (isset($_SESSION['user']['id']))
                {
                    if (array_search('materials#image', $getKinds) !== false)
                    {
                        helpColPush($qImageColumns, ['(SELECT CONCAT(mp.`id`, \',\', IF(mp.`notes` IS NULL, \'\', mp.`notes`)) FROM `materials_of_playlists` mp LEFT JOIN `user_playlists` up ON up.`id`=mp.`playlist_id` WHERE mp.`material_id` = r.`id` AND up.`user_id` = ? AND up.`title` = \'Понравившиеся изображения\')', '`r_saved_id_note`']);
                       $qImageColumnsParameters[] = $_SESSION['user']['id'];
                    }
                    if (array_search('materials#video', $getKinds) !== false)
                    {
                        helpColPush($qVideoColumns, ['(SELECT CONCAT(mp.`id`, \',\', IF(mp.`notes` IS NULL, \'\', mp.`notes`)) FROM `materials_of_playlists` mp LEFT JOIN `user_playlists` up ON up.`id`=mp.`playlist_id` WHERE mp.`material_id` = r.`id` AND up.`user_id` = ? AND up.`title` = \'Понравившиеся видео\')', '`r_saved_id_note`']);
                        $qVideoColumnsParameters[] = $_SESSION['user']['id'];
                    }
                    if (array_search('materials#music', $getKinds) !== false)
                    {
                        helpColPush($qMusicColumns, ['(SELECT CONCAT(mp.`id`, \',\', IF(mp.`notes` IS NULL, \'\', mp.`notes`)) FROM `materials_of_playlists` mp LEFT JOIN `user_playlists` up ON up.`id`=mp.`playlist_id` WHERE mp.`material_id` = r.`id` AND up.`user_id` = ? AND up.`title` = \'Понравившаяся музыка\')', '`r_saved_id_note`']);
                        $qMusicColumnsParameters[] = $_SESSION['user']['id'];
                    }
                    if (array_search('materials#sound', $getKinds) !== false)
                    {
                        helpColPush($qSoundColumns, ['(SELECT CONCAT(mp.`id`, \',\', IF(mp.`notes` IS NULL, \'\', mp.`notes`)) FROM `materials_of_playlists` mp LEFT JOIN `user_playlists` up ON up.`id`=mp.`playlist_id` WHERE mp.`material_id` = r.`id` AND up.`user_id` = ? AND up.`title` = \'Понравившиеся звуки\')', '`r_saved_id_note`']);
                        $qSoundColumnsParameters[] = $_SESSION['user']['id'];
                    }
                }
                
                break;
            case 'statistics':

                if (array_search('materials#image', $getKinds) !== false)
                {
                    helpColPush($qImageColumns,
                        ['r.`views`', '`r_views`'],
                        ['(SELECT SUM(f.`downloads`) FROM `files` f LEFT JOIN `files_of_materials` fm ON fm.`file_id`=f.`id` WHERE fm.`material_id` = r.`id`)', '`r_downloads_sum`'],
                        ['(SELECT COUNT(*) FROM `materials_of_playlists` mp LEFT JOIN `user_playlists` up ON up.`id`=mp.`playlist_id` WHERE mp.`material_id` = r.`id` AND up.`title` = \'Понравившиеся изображения\')', '`r_favorite_sum`'],
                    );
                }
                if (array_search('materials#video', $getKinds) !== false)
                {
                    helpColPush($qVideoColumns,
                        ['r.`views`', '`r_views`'],
                        ['(SELECT SUM(f.`downloads`) FROM `files` f LEFT JOIN `files_of_materials` fm ON fm.`file_id`=f.`id` WHERE fm.`material_id` = r.`id`)', '`r_downloads_sum`'],
                        ['(SELECT COUNT(*) FROM `materials_of_playlists` mp LEFT JOIN `user_playlists` up ON up.`id`=mp.`playlist_id` WHERE mp.`material_id` = r.`id` AND up.`title` = \'Понравившиеся видео\')', '`r_favorite_sum`'],
                    );
                }
                if (array_search('materials#music', $getKinds) !== false)
                {
                    helpColPush($qMusicColumns,
                        ['r.`views`', '`r_views`'],
                        ['(SELECT SUM(f.`downloads`) FROM `files` f LEFT JOIN `files_of_materials` fm ON fm.`file_id`=f.`id` WHERE fm.`material_id` = r.`id`)', '`r_downloads_sum`'],
                        ['(SELECT COUNT(*) FROM `materials_of_playlists` mp LEFT JOIN `user_playlists` up ON up.`id`=mp.`playlist_id` WHERE mp.`material_id` = r.`id` AND up.`title` = \'Понравившаяся музыка\')', '`r_favorite_sum`'],
                    );
                }
                if (array_search('materials#sound', $getKinds) !== false)
                {
                    helpColPush($qSoundColumns,
                        ['r.`views`', '`r_views`'],
                        ['(SELECT SUM(f.`downloads`) FROM `files` f LEFT JOIN `files_of_materials` fm ON fm.`file_id`=f.`id` WHERE fm.`material_id` = r.`id`)', '`r_downloads_sum`'],
                        ['(SELECT COUNT(*) FROM `materials_of_playlists` mp LEFT JOIN `user_playlists` up ON up.`id`=mp.`playlist_id` WHERE mp.`material_id` = r.`id` AND up.`title` = \'Понравившиеся звуки\')', '`r_favorite_sum`'],
                    );
                }
                
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


        
        $query = [];

        if (array_search('materials#image', $getKinds) !== false)
        {
            $imageTables = implode(' ', array_unique($qImageTables));
            $imageConditions = count(array_column($qImageConditions, 'condition')) ? 'WHERE '.implode(' AND ', array_map(fn($c) => "($c)", array_column($qImageConditions, 'condition'))) : '';
            $query[] = "SELECT COUNT(DISTINCT r.`id`) AS 'total' FROM `images` i $imageTables $imageConditions";
        }
        if (array_search('materials#video', $getKinds) !== false)
        {
            $videoTables = implode(' ', array_unique($qVideoTables));
            $videoConditions = count(array_column($qVideoConditions, 'condition')) ? 'WHERE '.implode(' AND ', array_map(fn($c) => "($c)", array_column($qVideoConditions, 'condition'))) : '';
            $query[] = "SELECT COUNT(DISTINCT r.`id`) AS 'total' FROM `videos` v $videoTables $videoConditions";
        }
        if (array_search('materials#music', $getKinds) !== false)
        {
            $musicTables = implode(' ', array_unique($qMusicTables));
            $musicConditions = count(array_column($qMusicConditions, 'condition')) ? 'WHERE '.implode(' AND ', array_map(fn($c) => "($c)", array_column($qMusicConditions, 'condition'))) : '';
            $query[] = "SELECT COUNT(DISTINCT r.`id`) AS 'total' FROM `music` m $musicTables $musicConditions";
        }
        if (array_search('materials#sound', $getKinds) !== false)
        {
            $soundTables = implode(' ', array_unique($qSoundTables));
            $soundConditions = count(array_column($qSoundConditions, 'condition')) ? 'WHERE '.implode(' AND ', array_map(fn($c) => "($c)", array_column($qSoundConditions, 'condition'))) : '';
            $query[] = "SELECT COUNT(DISTINCT r.`id`) AS 'total' FROM `sounds` s $soundTables $soundConditions";
        }

        // echo implode(' UNION ', $query)."\n<br>";
        // var_dump(array_merge(array_column($qImageConditions, 'parameter'), array_column($qVideoConditions, 'parameter'), array_column($qMusicConditions, 'parameter'), array_column($qSoundConditions, 'parameter')));
        // exit();
        
        $rows = libSqlQuery($link, implode(' UNION ', $query), ...array_merge(array_column($qImageConditions, 'parameter'), array_column($qVideoConditions, 'parameter'), array_column($qMusicConditions, 'parameter'), array_column($qSoundConditions, 'parameter')));
        
        $totalResults = is_array($rows) ? array_sum(array_column($rows, 'total')) : 0;



        $query = [];
        $cols = '';

        if (array_search('materials#image', $getKinds) !== false)
        {
            $imageCols = implode(', ', array_map(fn($col) => "$col[0] AS $col[1]", $qImageColumns));
            $query[] = "SELECT $imageCols FROM `images` i $imageTables $imageConditions";
            $cols = implode(', ', array_column($qImageColumns, 1));
        }
        if (array_search('materials#video', $getKinds) !== false)
        {
            $videoCols = implode(', ', array_map(fn($col) => "$col[0] AS $col[1]", $qVideoColumns));
            $query[] = "SELECT $videoCols FROM `videos` v $videoTables $videoConditions";
            if (!$cols) $cols = implode(', ', array_column($qVideoColumns, 1));
        }
        if (array_search('materials#music', $getKinds) !== false)
        {
            $musicCols = implode(', ', array_map(fn($col) => "$col[0] AS $col[1]", $qMusicColumns));
            $query[] = "SELECT $musicCols FROM `music` m $musicTables $musicConditions";
            if (!$cols) $cols = implode(', ', array_column($qMusicColumns, 1));
        }
        if (array_search('materials#sound', $getKinds) !== false)
        {
            $soundCols = implode(', ', array_map(fn($col) => "$col[0] AS $col[1]", $qSoundColumns));
            $query[] = "SELECT $soundCols FROM `sounds` s $soundTables $soundConditions";
            if (!$cols) $cols = implode(', ', array_column($qSoundColumns, 1));
        }

        //  ВНИМАНИЕ! ЗАПРОС ОБЁРНУТ И НАЗВАН t ЧТОБЫ УБРАТЬ ПОВТОРЯЮЩИЕСЯ МАТЕРИАЛЫ (r.id)
        //  ОНИ ПОЯВЛЯЮТСЯ, КОГДА 1 МАТЕРИАЛ ИМЕЕТ НЕСКОЛЬКО ФАЙЛОВ, УДОВЛЕТВОРЯЮЩИЕ УСЛОВИЯМ ЗАПРОСА
        //  БЕЗ ИСПОЛЬЗОВАНИя UNION К СТОЛБЦАМ ПОДЗАПРОСОВ ( (SELECT ...) AS `column` ) НУЖНО ОБРАЩАТЬСЯ ЧЕРЕЗ ПРЕФИКС t_ ($row['t_column'])
        //  НО С UNION ОБРАЩАТЬСЯ БЕЗ ПРЕФИКСА. ДЛЯ РЕШЕНИЯ ПРОБЛЕМЫ СОЗДАНА ПЕРЕМЕННАЯ $prefix

        $subQuaries = implode(' UNION ', $query);
        $query = "SELECT $cols FROM ($subQuaries) t GROUP BY `r_id` $getSort LIMIT $resultsShift, $getResultsPerPage;";
        
        // echo $query."\n<br>";
        // var_dump(array_merge(array_column($qImageConditions, 'parameter'), array_column($qVideoConditions, 'parameter'), array_column($qMusicConditions, 'parameter'), array_column($qSoundConditions, 'parameter')));
        // exit();

        $rows = libSqlQuery($link, $query, ...array_merge(
            array_merge($qImageColumnsParameters, array_column($qImageConditions, 'parameter')),
            array_merge($qVideoColumnsParameters, array_column($qVideoConditions, 'parameter')),
            array_merge($qMusicColumnsParameters, array_column($qMusicConditions, 'parameter')),
            array_merge($qSoundColumnsParameters, array_column($qSoundConditions, 'parameter'))
        ));
        
        // var_dump($rows[0]);
        // exit();

        if (is_array($rows))
        {
            foreach ($rows as $row)
            {
                if ($row['t_r_type'] === 'image')
                    $materialKind = 'materials#image';
                else if ($row['t_r_type'] === 'video')
                    $materialKind = 'materials#video';
                else if ($row['t_r_type'] === 'music')
                    $materialKind = 'materials#music';
                else if ($row['t_r_type'] === 'sound')
                    $materialKind = 'materials#sound';
                else
                    $materialKind = '';

                $item = array(
                    'kind' => $materialKind,
                    'id' => $row['t_r_id'],
                );
    
                foreach ($request_parts as $part)
                {
                    switch ($part)
                    {
                        case 'snippet':

                            $fileName = $row['t_f_name'];
                            $elements = scandir($filesFolder);
                            
                            if ($materialKind === 'materials#image')
                                checkTypeFolder('images');
                            else if ($materialKind === 'materials#video')
                                checkTypeFolder('videos');
                            else if ($materialKind === 'materials#music')
                                checkTypeFolder('music');
                            else if ($materialKind === 'materials#sound')
                                checkTypeFolder('sounds');

                            foreach ($elements as $element) {
                                if ($element[0] === '.')
                                    continue;
                                    
                                $folder = scandir("$filesFolder/$element");
                                foreach ($folder as $chunk) {
                                    if (file_exists("$filesFolder/$element/$chunk/$fileName"))
                                    {
                                        $fileName = "$element/$chunk/$fileName";
                                        $elements = [];
                                        break;
                                    }
                                }
                                if (!$elements)
                                    break;
                            }

                            if ($elements)
                                $previewFile = [];
                            else
                            {
                                $previewFile = [
                                    'fileName' => $fileName,
                                ];
                            }

                            $item['snippet'] = [
                                'publishedAt' => $row['t_r_create_date'],
                                'title' => $row['t_r_title'],
                                'filesCount' => $row['t_r_files_count'],
                                'previewFile' => $previewFile,
                            ];

                            if ($materialKind === 'materials#image')
                            {
                                $item['snippet']['previewFile']['width'] = $row['t_f_width'];
                                $item['snippet']['previewFile']['height'] = $row['t_f_height'];
                                $item['snippet']['previewFile']['background'] = $row['t_f_background'];
                            }
                            else if ($materialKind === 'materials#video')
                            {
                                $item['snippet']['previewFile']['width'] = $row['t_f_width'];
                                $item['snippet']['previewFile']['height'] = $row['t_f_height'];
                                $item['snippet']['previewFile']['duration'] = $row['t_f_duration'];
                                $item['snippet']['previewFile']['fps'] = $row['t_f_fps'];
                            }
                            else if ($materialKind === 'materials#music')
                            {
                                $item['snippet']['hasVocals'] = $row['t_f_vocals'];
                                $item['snippet']['hasExplicit'] = $row['t_m_explicit'];
                                $item['snippet']['isYoutubeFreeUsable'] = $row['t_m_yt_free_use'];
                                $item['snippet']['release'] = [
                                    'releaseDate' => $row['t_mr_date'],
                                    'releaseTitle' => $row['t_mr_title'],
                                    'releasePicture' => $row['t_mr_picture_name'],
                                ];
                                $item['snippet']['previewFile']['duration'] = $row['t_f_duration'];
                            }
                            else if ($materialKind === 'materials#sound')
                            {
                                $item['snippet']['supplier'] = [
                                    'supplierId' => $row['t_p_id'],
                                    'supplierTitle' => $row['t_p_name'],
                                ];
                                $item['snippet']['previewFile']['duration'] = $row['t_f_duration'];
                            }
                            
                            break;
                        case 'contentDetails':
    
                            if ($PermissionImagesVideosEditDelete || $PermissionMusicEditDelete || $PermissionSoundsEditDelete)
                            {
                                $item['contentDetails']['notes'] = $row['t_r_notes'];
                            }

                            if (isset($_SESSION['user']['id']))
                            {
                                if ($row['t_r_saved_id_note'])
                                {
                                    $savedIdNote = explode(',', $row['t_r_saved_id_note']);
                                    
                                    $savedInFavoritesId = [
                                        // 'kind' => 'playlists#playlistItem',
                                        'id' => (int)$savedIdNote[0],
                                        'note' => $savedIdNote[1],
                                        // 'snippet' => [
                                        //     'publishedAt' => date('Y-m-d\TH:i:s\Z', time()),
                                        //     // 'title' => $playlistTitle,
                                        //     'playlistId' => $dataPlaylistId,
                                        //     'resourceId' => $resourceId,
                                        // ],
                                    ];
                                }
                                else
                                    $savedInFavoritesId = [];
                                
                                $item['contentDetails']['savedInFavoritesId'] = $savedInFavoritesId;
                            }
                            
                            break;
                        case 'statistics':
    
                            $item['statistics'] = [
                                'viewCount' => $row['t_r_views'],
                                'downloadCount' => $row['t_r_downloads_sum'],
                                'favoriteCount' => $row['t_r_favorite_sum'],
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