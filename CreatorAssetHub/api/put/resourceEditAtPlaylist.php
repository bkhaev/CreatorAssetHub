<?php

$output = [];

$qPlaylistItemColumns = $qPlaylistItemConditions = $qPlaylistItemTables = $qPlaylistItemColumnsEdit = $qPlaylistItemConditionsEdit = [];

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


$dataPlaylistItemId = $dataResourceId = $dataPlaylistId = $dataPosition = $dataNote = null;


foreach ($data as $key => $part)
{
    switch ($key)
    {
        case 'id':
            if (is_int($part))
                $dataPlaylistItemId = $part;
            else
            {
                $output['error']['errors'][] = [
                    'message' => $key,
                    'domain' => 'creatorassethub.playlists',
                    'reason' => 'wrongValue',
                    'location' => 'data',
                ];
            }
            break;
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
else if ($dataPlaylistItemId === null)
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

    $qPlaylistItemColumns[] = 'up.`user_id`';
    
    $qPlaylistItemConditions[] = [ 'condition' => 'mp.`id` = ?', 'parameter' => $dataPlaylistItemId, ];
    
    $qPlaylistItemTables[] = 'LEFT JOIN `user_playlists` up ON up.`id`=mp.`playlist_id`';

    $qPlaylistItemColumnsEdit[] = [ 'column' => '`notes` = ?', 'parameter' => $dataNote, ];

    $qPlaylistItemConditionsEdit[] = [ 'condition' => '`id` = ?', 'parameter' => $dataPlaylistItemId, ];

    // НАЧАЛЬНАЯ НАСТРОЙКА


    $cols = implode(', ', array_unique($qPlaylistItemColumns));
    $tables = implode(' ', array_unique($qPlaylistItemTables));
    $conditions = count($qPlaylistItemConditions) ? 'WHERE '.implode(' AND ', array_map(fn($c) => "($c)", array_column($qPlaylistItemConditions, 'condition'))) : '';

    $query = "SELECT $cols FROM `materials_of_playlists` mp $tables $conditions;"; // "UPDATE `materials_of_playlists` SET  WHERE "
    
    $rows = libSqlQuery($link, $query, ...array_column($qPlaylistItemConditions, 'parameter'));
    
    if (is_array($rows))
    {
        if ($rows)
        {
            
            if ($rows[0]['up_user_id'] === $_SESSION['user']['id'])
            {
                $cols = implode(', ', array_unique(array_column($qPlaylistItemColumnsEdit, 'column')));
                $conditions = count($qPlaylistItemConditionsEdit) ? 'WHERE '.implode(' AND ', array_map(fn($c) => "($c)", array_column($qPlaylistItemConditionsEdit, 'condition'))) : '';
                
                $query = "UPDATE `materials_of_playlists` SET $cols $conditions;";
                $result = libSqlQuery($link, $query, ...array_merge(array_column($qPlaylistItemColumnsEdit, 'parameter'), array_column($qPlaylistItemConditionsEdit, 'parameter')));
                
                if (is_int($result))
                {
                    if ($result)
                    {
                        header("HTTP/1.1 200 OK");
                        $output = [];
                    }
                    else
                    {
                        $output['error'] = [
                            'code' => '200',
                            'message' => "Playlist Item for deleting is already not exist more.",
                            'errors' => [[
                                'message' => 'Playlist Item for deleting is already not exist more.',
                                'domain' => 'creatorassethub.playlists',
                                'reason' => 'playlistItemNotFound',
                            ]],
                        ];

                        header("HTTP/1.1 200 OK");
                    }
                }
                else
                {
                    $output['error'] = [
                        'code' => '520',
                        'message' => "Cannot update playlist item.",
                        'errors' => [[
                            'message' => 'Cannot update playlist item.',
                            'domain' => 'creatorassethub.playlists',
                            'reason' => 'unknownError',
                        ]],
                    ];

                    header("HTTP/1.1 520 Unknown Error");
                }
            }
            else
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
            }
        }
        else
        {
            $output['error'] = [
                'code' => '404',
                'message' => "The required playlist item was not found.",
                'errors' => [[
                    'message' => 'The required playlist item was not found.',
                    'domain' => 'creatorassethub.playlists',
                    'reason' => 'playlistItemNotFound',
                ]],
            ];
    
            header("HTTP/1.1 404 Not Found");
        }
    }
    else
    {
        $output['error'] = [
            'code' => '520',
            'message' => "Cannot find playlist item.",
            'errors' => [[
                'message' => 'Cannot find playlist item.',
                'domain' => 'creatorassethub.playlists',
                'reason' => 'unknownError',
            ]],
        ];

        header("HTTP/1.1 520 Unknown Error");
    }


    mysqli_close($link);
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