<?php

$output = [];

// $qPlaylistMaterialColumns = [];
$qMaterialsColumns = $qMaterialsTables = [];

// $resourceKindList = ['materials#image', 'materials#video', 'materials#music', 'materials#sound', 'phrases#phrase'];

foreach ($_GET as $key => $get)
{
    switch ($key)
    {
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
    return;
}
else if (!is_object($data))
{
    $output['error'] = [
        'code' => '400',
        'message' => 'The invalid Request Body.',
        'errors' => [[
            'message' => 'The invalid Request Body.',
            'domain' => 'creatorassethub.playlists',
            'reason' => 'wrongValue',
            'location' => 'data',
        ]],
    ];

    header("HTTP/1.1 400 Bad Request");
    return;
}


$dataResourceId = $dataPlaylistId = $dataPosition = $dataNote = null;


foreach ($data as $key => $part)
{
    switch ($key)
    {
        case 'snippet':
            if (!is_object($part))
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.playlists',
                    'reason' => 'wrongValue',
                    'location' => 'data',
                ];
                break;
            }

            foreach ($part as $part_key => $parameter)
            {
                switch ($part_key)
                {
                    case 'playlistId':
                        if (is_int($parameter))
                            $dataPlaylistId = $parameter;
                        else
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.playlists',
                                'reason' => 'wrongValue',
                                'location' => 'data',
                                'locationPart' => $key,
                            ];
                        }
                        break;
                    case 'position':
                        if (is_int($parameter))
                            $dataPosition = $parameter;
                        else
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.playlists',
                                'reason' => 'wrongValue',
                                'location' => 'data',
                                'locationPart' => $key,
                            ];
                        }
                        break;
                    case 'resourceId':
                        if (!is_object($parameter))
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.playlists',
                                'reason' => 'wrongValue',
                                'location' => 'data',
                                'locationPart' => $key,
                            ];
                            break;
                        }

                        if (isset($parameter->kind))
                        {
                            if (is_string($parameter->kind))
                            {
                                $dataResourceKind = $parameter->kind;

                                if ($dataResourceKind === 'phrases#phrase')
                                    $dataResourceId = $parameter->phraseId ?? null;
                                else if ($dataResourceKind === 'materials#image')
                                    $dataResourceId = $parameter->imageId ?? null;
                                else if ($dataResourceKind === 'materials#video')
                                    $dataResourceId = $parameter->videoId ?? null;
                                else if ($dataResourceKind === 'materials#music')
                                    $dataResourceId = $parameter->musicId ?? null;
                                else if ($dataResourceKind === 'materials#sound')
                                    $dataResourceId = $parameter->soundId ?? null;
                                else
                                {
                                    $output['error']['errors'][] = [
                                        'message' => 'kind',
                                        'domain' => 'creatorassethub.playlists',
                                        'reason' => 'wrongValue',
                                        'location' => 'data',
                                        'locationPart' => "$key $part_key",
                                    ];
                                }
                            }
                            else
                            {
                                $output['error']['errors'][] = [
                                    'message' => 'kind',
                                    'domain' => 'creatorassethub.playlists',
                                    'reason' => 'wrongValue',
                                    'location' => 'data',
                                    'locationPart' => "$key $part_key",
                                ];
                            }
                        }
                        break;
                    case 'note':
                        if (is_string($parameter))
                        {
                            if ($parameter)
                                $dataNote = $parameter;
                        }
                        else if ($parameter !== null)
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.playlists',
                                'reason' => 'wrongValue',
                                'location' => 'data',
                                'locationPart' => $key,
                            ];
                        }
                        break;
                    default:
                        $output['error']['errors'][] = [
                            'message' => $part_key,
                            'domain' => 'creatorassethub.playlists',
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
                'domain' => 'creatorassethub.playlists',
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
else if (!$dataPlaylistId || !$dataResourceKind || !$dataResourceId)
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
else if (isset($_SESSION['user']['id']))
{
    $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');



    // НАЧАЛЬНАЯ НАСТРОЙКА

    if ($dataResourceKind === 'phrases#phrase')
    {
        $qMaterialsColumns[] = 'x.`material_id` AS `is_right_type`';
        $qMaterialsTables[] = 'LEFT JOIN `phrases` x ON x.`material_id`=r.`id`';
    }
    else if ($dataResourceKind === 'materials#image')
    {
        $qMaterialsColumns[] = 'x.`material_id` AS `is_right_type`';
        $qMaterialsTables[] = 'LEFT JOIN `images` x ON x.`material_id`=r.`id`';
    }
    else if ($dataResourceKind === 'materials#video')
    {
        $qMaterialsColumns[] = 'x.`material_id` AS `is_right_type`';
        $qMaterialsTables[] = 'LEFT JOIN `videos` x ON x.`material_id`=r.`id`';
    }
    else if ($dataResourceKind === 'materials#music')
    {
        $qMaterialsColumns[] = 'x.`material_id` AS `is_right_type`';
        $qMaterialsTables[] = 'LEFT JOIN `music` x ON x.`material_id`=r.`id`';
    }
    else if ($dataResourceKind === 'materials#sound')
    {
        $qMaterialsColumns[] = 'x.`material_id` AS `is_right_type`';
        $qMaterialsTables[] = 'LEFT JOIN `sounds` x ON x.`material_id`=r.`id`';
    }

    // НАЧАЛЬНАЯ НАСТРОЙКА


    
    $query = "SELECT `user_id`, `resource_type` FROM `user_playlists` WHERE `id` = ?";

    $rows = libSqlQuery($link, $query, $dataPlaylistId);
        
    if (is_array($rows))
    {
        if ($rows)
        {
            if ($_SESSION['user']['id'] !== $rows[0]['user_playlists_user_id'])
            {
                $output['error'] = array(
                    'code' => '403',
                    'message' => "The playlist owner not allowed you to control it.",
                    'errors' => [[
                        'message' => "The playlist owner not allowed you to control it.",
                        'domain' => 'creatorassethub.playlists',
                        'reason' => 'actionForbidden',
                    ]],
                );
    
                header("HTTP/1.1 403 Forbidden");
                return;
            }

            if (explode('#', $dataResourceKind)[1] !== $rows[0]['user_playlists_resource_type'])
            {
                $output['error'] = array(
                    'code' => '412',
                    'message' => "The playlist can has only phrase resources.",
                    'errors' => [[
                        'message' => "The playlist can has only phrase resources.",
                        'domain' => 'creatorassethub.playlists',
                        'reason' => 'playlistPreconditionFailed',
                    ]],
                );
    
                header("HTTP/1.1 412 Precondition Failed"); // условие ложно
                return;
            }

            $cols = implode(', ', array_unique($qMaterialsColumns));
            $tables = implode(' ', array_unique($qMaterialsTables));

            $query = "SELECT $cols FROM `materials` r $tables WHERE r.`id` = ?";

            $rows = libSqlQuery($link, $query, $dataResourceId);
                
            if (is_array($rows))
            {
                if ($rows)
                {
                    if ($rows[0]['x_is_right_type'] === null)
                    {
                        $output['error'] = array(
                            'code' => '412',
                            'message' => "The item is not type of $dataResourceKind.",
                            'errors' => [[
                                'message' => "The item is not type of $dataResourceKind.",
                                'domain' => 'creatorassethub.playlists',
                                'reason' => 'itemPreconditionFailed',
                            ]],
                        );
            
                        header("HTTP/1.1 412 Precondition Failed");
                        return;
                    }

                    $query = "SELECT `id` FROM `materials_of_playlists` WHERE `playlist_id` = ? AND material_id = ?";

                    $rows = libSqlQuery($link, $query, $dataPlaylistId, $dataResourceId);
                        
                    if (is_array($rows))
                    {
                        if ($rows)
                        {
                            $output = array(
                                'kind' => 'playlists#playlistItem',
                                'id' => $rows[0]['materials_of_playlists_id'],
                                'error' => [
                                    'code' => '208',
                                    'message' => 'The resource is already in playlist.',
                                    'errors' => [[
                                        'message' => 'The resource is already in playlist.',
                                        'domain' => 'creatorassethub.user',
                                        'reason' => 'resourceAlreadyInPlaylist',
                                        'location' => 'data',
                                    ]],
                                ],
                            );

                            header("HTTP/1.1 208 Already Reported");
                        }
                        else
                        {
                            $query = "INSERT INTO `materials_of_playlists`(`playlist_id`, `material_id`, `notes`) VALUES (?,?,?)";

                            $result = libSqlQuery($link, $query, $dataPlaylistId, $dataResourceId, $dataNote);

                            if ($result)
                            {
                                if ($dataResourceKind === 'phrases#phrase')
                                {
                                    $resourceId = [
                                        'kind' => $dataResourceKind,
                                        'phraseId' => $dataResourceId,
                                    ];
                                }

                                $output = [
                                    'kind' => 'playlists#playlistItem',
                                    'id' => $result,
                                ];
                            }
                            else
                            {
                                $output['error'] = array(
                                    'code' => '520',
                                    'message' => "Unknown error was been occured when new item was adding.",
                                    'errors' => [[
                                        'message' => 'New item was not been added.',
                                        'domain' => 'creatorassethub.playlists',
                                        'reason' => 'unknownError',
                                    ]],
                                );
                    
                                header("HTTP/1.1 520 Unknown Error");
                            }
                        }
                    }
                    else
                    {
                        $output['error'] = [
                            'code' => '520',
                            'message' => "Cannot search playlist item.",
                            'errors' => [[
                                'message' => 'Cannot search playlist item.',
                                'domain' => 'creatorassethub.playlists',
                                'reason' => 'unknownError',
                            ]],
                        ];
                
                        header("HTTP/1.1 520 Unknown Error");
                    }
                }
                else
                {
                    $output['error'] = [
                        'code' => '520',
                        'message' => "The required item was not found.",
                        'errors' => [[
                            'message' => 'The required item was not found.',
                            'domain' => 'creatorassethub.playlists',
                            'reason' => $dataResourceKind.'NotFound',
                        ]],
                    ];
            
                    header("HTTP/1.1 404 Not Found");
                }
            }
            else
            {
                $output['error'] = [
                    'code' => '520',
                    'message' => "Cannot find item.",
                    'errors' => [[
                        'message' => 'Cannot find item.',
                        'domain' => 'creatorassethub.playlists',
                        'reason' => 'unknownError',
                    ]],
                ];
        
                header("HTTP/1.1 520 Unknown Error");
            }
        }
        else
        {
            $output['error'] = [
                'code' => '404',
                'message' => "The required playlist was not found.",
                'errors' => [[
                    'message' => 'The required playlist was not found.',
                    'domain' => 'creatorassethub.playlists',
                    'reason' => 'playlistNotFound',
                ]],
            ];
    
            header("HTTP/1.1 404 Not Found");
        }
    }
    else
    {
        $output['error'] = [
            'code' => '520',
            'message' => "Cannot find playlist.",
            'errors' => [[
                'message' => 'Cannot find playlist.',
                'domain' => 'creatorassethub.playlists',
                'reason' => 'unknownError',
            ]],
        ];

        header("HTTP/1.1 520 Unknown Error");
    }
}
else
{
    header("HTTP/1.1 401 Unauthorized");

    $output['error']['errors'][] = [
        'message' => 'User did not authorize yet.',
        'domain' => 'creatorassethub.user',
        'reason' => 'userUnauthorized',
    ];

    $output['error'] = [
        'code' => '401',
        'message' => 'User did not authorize yet.',
    ] + $output['error'];
}