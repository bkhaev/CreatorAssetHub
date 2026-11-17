<?php

$output = [];

// $getResourceId = $getPlaylistId = null;
$playlistItemId = null;

$qPlaylistItemColumns = $qPlaylistItemConditions = $qPlaylistItemTables = $qPlaylistItemConditionsDelete = [];

foreach ($_GET as $key => $get)
{
    switch ($key)
    {
        case 'id':
            if (is_numeric($get))
            {
                $playlistItemId = $get;
            }
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
    return;
}
else if ($playlistItemId === null)
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
    return;
}
else if (isset($_SESSION['user']['id']))
{
    $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');



    // НАЧАЛЬНАЯ НАСТРОЙКА

    $qPlaylistItemColumns[] = 'up.`user_id`';
    
    $qPlaylistItemConditions[] = [ 'condition' => 'mp.`id` = ?', 'parameter' => $playlistItemId, ];
    
    $qPlaylistItemTables[] = 'LEFT JOIN `user_playlists` up ON up.`id`=mp.`playlist_id`';

    $qPlaylistItemConditionsDelete[] = [ 'condition' => '`id` = ?', 'parameter' => $playlistItemId, ];

    // НАЧАЛЬНАЯ НАСТРОЙКА


    $cols = implode(', ', array_unique($qPlaylistItemColumns));
    $tables = implode(' ', array_unique($qPlaylistItemTables));
    $conditions = count($qPlaylistItemConditions) ? 'WHERE '.implode(' AND ', array_map(fn($c) => "($c)", array_column($qPlaylistItemConditions, 'condition'))) : '';

    $query = "SELECT $cols FROM `materials_of_playlists` mp $tables $conditions;";
    
    $rows = libSqlQuery($link, $query, ...array_column($qPlaylistItemConditions, 'parameter'));
    
    if (is_array($rows))
    {
        if ($rows)
        {
            if ($rows[0]['up_user_id'] === $_SESSION['user']['id'])
            {
                $conditions = count($qPlaylistItemConditionsDelete) ? 'WHERE '.implode(' AND ', array_map(fn($c) => "($c)", array_column($qPlaylistItemConditionsDelete, 'condition'))) : '';

                $query = "DELETE FROM `materials_of_playlists` $conditions;";
                
                $result = libSqlQuery($link, $query, ...array_column($qPlaylistItemConditionsDelete, 'parameter'));
                
                if (is_int($result))
                {
                    header("HTTP/1.1 200 OK");
                    $output = [];
                }
                else
                {
                    $output['error'] = [
                        'code' => '520',
                        'message' => "Unknown error when trying deleting item from playlist.",
                        'errors' => [[
                            'message' => 'Unknown error when trying deleting item from playlist.',
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
    $output['error'] = array(
        'code' => '401',
        'message' => "User need to authorize for continue this operation.",
        'errors' => [
            [
                'message' => 'User need to authorize for continue this operation.',
                'domain' => 'creatorassethub.playlists',
                'reason' => 'userUnauthorized',
            ],
        ],
    );

    header("HTTP/1.1 401 Unauthorized");
}