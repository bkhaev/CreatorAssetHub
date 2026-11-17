<?php

$output = [];
$request_parts = [];

$qMaterialsColumns = $qPhrasesColumns = $qSourcesColumns = $qSynonymsParameters = [];

$sourceAllowedHosts = ['www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.twitch.tv', 'trovo.live', 't.me', 'vk.com', 'www.facebook.com', 'www.instagram.com', 'twitter.com'];

foreach ($_GET as $key => $get)
{
    switch ($key)
    {
        case 'part':
            $request_parts = array_unique(explode(',', $get));
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


$dataPhrase = $dataEmotion = $dataTone = $dataIsCamera = $dataIsAnonym = $dataAccessMode = null;
$dataAuthors = $dataSynonyms = $dataTags = $dataSources = $dataFiles = [];


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
                    case 'phrase':
                        if (is_string($parameter) && mb_strlen($parameter) >= 3 && mb_strlen($parameter) <= 200)
                            $dataPhrase = $parameter;
                        else
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.phrases',
                                'reason' => 'wrongValue',
                                'location' => 'data',
                                'locationPart' => $key,
                            ];
                        }
                        break;
                    case 'authors':
                        if (is_array($parameter) && $parameter && count(array_unique($parameter)) <= 5)
                        {
                            foreach (array_unique($parameter) as $author)
                            {
                                if (is_string($author) && mb_strlen($author) >= 3 && mb_strlen($author) <= 25)
                                    $dataAuthors[] = str_replace($libPersonNameBadSymbols, '', $author);
                                else
                                {
                                    $dataAuthors = [];
                                    break;
                                }
                            }
                        }

                        if (!$dataAuthors)
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.phrases',
                                'reason' => 'wrongValue',
                                'location' => 'data',
                                'locationPart' => $key,
                            ];
                        }
                        break;
                    case 'sources':
                        if (is_array($parameter))
                        {
                            // array_unique на обхектах не использовать!!! Только для массивов!!!
                            foreach ($parameter as $source)
                            {
                                if (!is_object($source))
                                {
                                    $output['error']['errors'][] = [
                                        'message' => 'source',
                                        'domain' => 'creatorassethub.phrases',
                                        'reason' => 'wrongValue',
                                        'location' => 'data',
                                    ];
                                    break;
                                }

                                // ПЕРЕДЕЛАТЬ НА FOREACH $SOURCE !!! ЧТОБЫ НЕИЗВЕСТНЫЕ ПАРАМЕТРЫ В SOURCE ДАВАЛИ ОШИБКУ
                                
                                $sourceUrl = $sourceTitle = $sourceTime = null;
                    
                                if (isset($source->url) && filter_var(urldecode($source->url), FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) && parse_url(urldecode($source->url), PHP_URL_PATH) !== '/')
                                {
                                    $url = parse_url(urldecode($source->url));
                                    if (array_search(ltrim($url['host'], 'www.'), array_map(fn($u) => ltrim($u, 'www.'), $sourceAllowedHosts)) !== false)
                                    {
                                        // Разные варианты ссылок на YouTube-видео:
                                        // https://www.youtube.com/watch?v=obKMVdrI7M8
                                        // https://www.youtube.com/embed/obKMVdrI7M8
                                        // https://youtu.be/watch?v=obKMVdrI7M8
                                        // https://youtu.be/obKMVdrI7M8
                    
                                        // Пример длинной ссылки на YouTube-видео:
                                        // https://www.youtube.com/watch?v=lxuPV2gCAKQ&list=PLg63EY3CRbu2HXyLEKzxBeWAOtZGho6lU&index=2&t=1s
                                    
                                        if (ltrim($url['host'], 'www.') === 'youtube.com' || ltrim($url['host'], 'www.') === 'm.youtube.com' || ltrim($url['host'], 'www.') === 'youtu.be')
                                        {
                                            if (isset($url['query']) && $url['query'])
                                            {
                                                $ytUrlVideoIdParameter = strpos($url['query'], 'v=');
                                                if ($ytUrlVideoIdParameter !== false)
                                                {
                                                    $sourceUrl = 'https://www.youtube.com/watch?'.explode('&', substr($url['query'], $ytUrlVideoIdParameter), 2)[0];
                                                }
                                                else if (ltrim($url['host'], 'www.') === 'youtu.be')
                                                {
                                                    $sourceUrl = 'https://www.youtube.com/watch?'.explode('?', trim($url['path'], '/'))[0];
                                                }
                                            }
                                            else
                                            {
                                                $sourceUrl = 'https://www.youtube.com/watch?v='.trim($url['path'], '/');
                                            }
                    
                                            if (!$sourceUrl)
                                            {
                                                $output['error']['errors'][] = [
                                                    'message' => 'The YouTube video ID was not found',
                                                    'domain' => 'creatorassethub.phrases',
                                                    'reason' => 'wrongValue',
                                                    'location' => 'data',
                                                    'locationPart' => $key,
                                                ];
                                                break;
                                            }
                                        }
                                        else
                                        {
                                            $sourceUrl = urldecode($source->url);
                                        }
                                    }
                                    else
                                    {
                                        $output['error']['errors'][] = [
                                            'message' => 'You cannot use this phrase source.',
                                            'domain' => 'creatorassethub.phrases',
                                            'reason' => 'wrongValue',
                                            'location' => 'data',
                                            'locationPart' => $key,
                                        ];
                                        break;
                                    }
                                }
                                else if (isset($source->url))
                                {
                                    $output['error']['errors'][] = [
                                        'message' => 'Source Url',
                                        'domain' => 'creatorassethub.phrases',
                                        'reason' => 'wrongValue',
                                        'location' => 'data',
                                        'locationPart' => $key,
                                    ];
                                    break;
                                }
                                else
                                {
                                    $output['error']['errors'][] = [
                                        'message' => 'The required data is missing. Source Url.',
                                        'domain' => 'creatorassethub.phrases',
                                        'reason' => 'sourceUrlNotFound',
                                        'location' => 'data',
                                        'locationPart' => $key,
                                    ];
                                    break;
                                }
                    
                                if (isset($source->title))
                                {
                                    if (is_string($source->title) && mb_strlen($source->title) >= 3 && mb_strlen($source->title) <= 100)
                                    {
                                        $sourceTitle = $source->title;
                                    }
                                    else if ($source->title !== null && $source->title !== '')
                                    {
                                        $output['error']['errors'][] = [
                                            'message' => 'Source Title',
                                            'domain' => 'creatorassethub.phrases',
                                            'reason' => 'wrongValue',
                                            'location' => 'data',
                                            'locationPart' => $key,
                                        ];
                                        break;
                                    }
                                }
                    
                                if (isset($source->time))
                                {
                                    if (is_int($source->time))
                                    {
                                        $sourceTime = $source->time;
                                    }
                                    else if ($source->time !== null)
                                    {
                                        $output['error']['errors'][] = [
                                            'message' => 'Source Time Start',
                                            'domain' => 'creatorassethub.phrases',
                                            'reason' => 'wrongValue',
                                            'location' => 'data',
                                            'locationPart' => $key,
                                        ];
                                        break;
                                    }
                                }
                    
                                $dataSources[] = [
                                    'sourceUrl' => $sourceUrl,
                                    'sourceTitle' => $sourceTitle,
                                    'sourceTime' => $sourceTime,
                                ];
                            }

                            // $dataSources = array_unique(array_map(fn($s) => $s['sourceUrl'].$s['sourceTime'], $dataSources));
                        }
                        else
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.phrases',
                                'reason' => 'wrongValue',
                                'location' => 'data',
                                'locationPart' => $key,
                            ];
                        }
                        break;
                    case 'files':
                        if (is_array($parameter))
                        {

                        }
                        else
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.phrases',
                                'reason' => 'wrongValue',
                                'location' => 'data',
                                'locationPart' => $key,
                            ];
                        }
                        break;
                    case 'synonyms':
                        if (is_array($parameter) && strlen(implode($parameter)) <= 300)
                        {
                            foreach (array_unique($parameter) as $synonym)
                            {
                                if (is_string($synonym) && mb_strlen($synonym) >= 3 && mb_strlen($synonym) <= 80)
                                {
                                    if ($synonym[0] === '#')
                                    {
                                        $dataTags[] = mb_strtolower(str_replace($libHashTagBadSymbols, '', $synonym));
                                    }
                                    else
                                        $dataSynonyms[] = mb_strtolower($synonym);
                                }
                                else
                                {
                                    $dataTags = $dataSynonyms = [];
                                    break;
                                }
                            }
                        }

                        if ($parameter && !$dataTags && !$dataSynonyms)
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.phrases',
                                'reason' => 'wrongValue',
                                'location' => 'data',
                                'locationPart' => $key,
                            ];
                        }
                        break;
                    case 'emotion':
                        if (is_string($parameter))
                            $dataEmotion = $parameter;
                        else if ($parameter !== null)
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.phrases',
                                'reason' => 'wrongValue',
                                'location' => 'data',
                                'locationPart' => $key,
                            ];
                        }
                        break;
                    case 'tone':
                        if (is_array($parameter) && count($parameter) <= 2)
                        {
                            $toneLow = $toneMedium = $toneHigh = '';
                            foreach (array_unique($parameter) as $tone)
                            {
                                if (is_string($tone))
                                {
                                    if ($tone === 'low')
                                        $toneLow = 'L';
                                    else if ($tone === 'medium')
                                        $toneMedium = 'M';
                                    else if ($tone === 'high')
                                        $toneHigh = 'H';
                                    else
                                        $toneLow = $toneMedium = $toneHigh = '';
                                }
                            }

                            $dataTone = $toneLow.$toneMedium.$toneHigh;
                        }

                        if ($parameter !== null && !$dataTone)
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.phrases',
                                'reason' => 'wrongValue',
                                'location' => 'data',
                                'locationPart' => $key,
                            ];
                        }
                        break;
                    case 'isCameraUsed':
                        if (is_bool($parameter))
                            $dataIsCamera = $parameter;
                        else
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.phrases',
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
        case 'contentDetails':
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
                    case 'privacyStatus':
                        if (is_string($parameter))
                        {
                            if ($parameter === 'public')
                                $dataAccessMode = 2;
                            else if ($parameter === 'shared')
                                $dataAccessMode = 1;
                            else if ($parameter === 'private')
                                $dataAccessMode = 0;
                        }

                        if ($dataAccessMode === null)
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.phrases',
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
        case 'userDetails':
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
                    case 'isUserAnonym':
                        if (is_bool($parameter))
                            $dataIsAnonym = $parameter;
                        else
                        {
                            $output['error']['errors'][] = [
                                'message' => $part_key,
                                'domain' => 'creatorassethub.phrases',
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
else if (!$dataPhrase || !$dataAuthors)
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
else if (!$dataSources && !$dataFiles)
{
    $output['error'] = [
        'code' => '400',
        'message' => 'No one source or file was found.',
        'errors' => [[
            'message' => 'No one source or file was found.',
            'domain' => 'creatorassethub.phrases',
            'reason' => 'dataNotFound',
            'location' => 'data',
            'locationPart' => 'snippet',
        ]],
    ];

    header("HTTP/1.1 400 Bad Request");
}
else
{
    $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');



    // НАЧАЛЬНАЯ НАСТРОЙКА

    array_push($qPhrasesColumns,
        [ 'column' => '`phrase`', 'parameter' => $dataPhrase, ],
    );
    

    if (isset($_SESSION['user']['id']))
    {
        array_push($qMaterialsColumns,
            [ 'column' => '`user_id`', 'parameter' => $_SESSION['user']['id'], ],
        );

        $query = "SELECT true FROM `users_data` ud LEFT JOIN `user_permissions` up ON up.`bit` & ud.`role` WHERE ud.`user_id` = ? AND (up.`name` = 'Phrases-Files' Or up.`name` = 'Phrases-Edit-Delete');";

        $rows = libSqlQuery($link, $query, $_SESSION['user']['id']);
        
        if ($rows && $rows[0]['TRUE'])
        {
            array_push($qPhrasesColumns,
                [ 'column' => '`approved`', 'parameter' => true, ],
            );
        }
        else if ($dataFiles)
        {
            $output['error'] = array(
                'code' => '403',
                'message' => "You cannot add any files for now.",
                'errors' => [[
                    'message' => "You cannot add any files for now.",
                    'domain' => 'creatorassethub.phrases',
                    'reason' => 'actionForbidden',
                ]],
            );

            header("HTTP/1.1 403 Forbidden");
            return;
        }
    }
    else if ($dataFiles)
    {
        $output['error'] = array(
            'code' => '401',
            'message' => "User need to authorize for continue this operation.",
            'errors' => [
                [
                    'message' => 'User need to authorize for continue this operation.',
                    'domain' => 'creatorassethub.phrases',
                    'reason' => 'userUnauthorized',
                ],
            ],
        );

        header("HTTP/1.1 401 Unauthorized");
        return;
    }
    
    if ($dataAccessMode !== null)
    {
        if (isset($_SESSION['user']['id']) || $dataAccessMode === 2)
        {
            array_push($qPhrasesColumns,
                [ 'column' => '`access`', 'parameter' => $dataAccessMode, ],
            );
        }
        else
        {
            $output['error'] = array(
                'code' => '401',
                'message' => "User need to authorize for continue this operation.",
                'errors' => [
                    [
                        'message' => 'User need to authorize for continue this operation.',
                        'domain' => 'creatorassethub.phrases',
                        'reason' => 'userUnauthorized',
                    ],
                ],
            );

            header("HTTP/1.1 401 Unauthorized");
            return;
        }
    }
    
    if ($dataIsAnonym !== null)
    {
        if (isset($_SESSION['user']['id']) || $dataIsAnonym === false)
            $qPhrasesColumns[] = [ 'column' => '`anonym`', 'parameter' => $dataIsAnonym, ];
        else
        {
            $output['error'] = array(
                'code' => '401',
                'message' => "User need to authorize for continue this operation.",
                'errors' => [
                    [
                        'message' => 'User need to authorize for continue this operation.',
                        'domain' => 'creatorassethub.phrases',
                        'reason' => 'userUnauthorized',
                    ],
                ],
            );

            header("HTTP/1.1 401 Unauthorized");
            return;
        }
    }

    if ($dataTone !== null)
        $qPhrasesColumns[] = [ 'column' => '`tone`', 'parameter' => $dataTone, ];
    
    if ($dataEmotion !== null)
        $qPhrasesColumns[] = [ 'column' => '`emotion`', 'parameter' => $dataEmotion, ];
    else
        $qPhrasesColumns[] = [ 'column' => '`emotion`', 'parameter' => '', ];

    if ($dataIsCamera !== null)
        $qPhrasesColumns[] = [ 'column' => '`camera`', 'parameter' => $dataIsCamera, ];

    // НАЧАЛЬНАЯ НАСТРОЙКА

    

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    mysqli_begin_transaction($link);

    try
    {
        $cols = implode(', ', array_column($qMaterialsColumns, 'column'));
        $parameters = implode(',', array_fill(0, count($qMaterialsColumns), '?'));

        $query = "INSERT INTO `materials`($cols) VALUES ($parameters);";
        
        $phraseId = libSqlQuery($link, $query, ...array_column($qMaterialsColumns, 'parameter'));
        
        if ($phraseId)
        {
            $displayCode = '';
            $editKey = libGeneratePhraseEditKey($displayCode);

            array_push($qPhrasesColumns,
                [ 'column' => '`material_id`', 'parameter' => $phraseId, ],
                [ 'column' => '`edit_key`', 'parameter' => $editKey, ],
            );

            $cols = implode(', ', array_column($qPhrasesColumns, 'column'));
            $parameters = implode(',', array_fill(0, count($qPhrasesColumns), '?'));

            $query = "INSERT INTO `phrases`($cols) VALUES ($parameters);";

            libSqlQuery($link, $query, ...array_column($qPhrasesColumns, 'parameter'));



            foreach ($dataAuthors as $author)
            {
                $query = "SELECT DISTINCT p.`id` FROM `persons` p LEFT JOIN `authors_of_phrases` ap ON ap.`person_id`=p.`id` WHERE p.`name` = ? ORDER BY ap.`person_id` DESC LIMIT 1;";
                $rows = libSqlQuery($link, $query, $author);

                if ($rows)
                {
                    $authorId = $rows[0]['p_id'];
                    $query = "INSERT INTO `authors_of_phrases` VALUES (?,?);";
                    libSqlQuery($link, $query, $phraseId, $authorId);
                }
                else
                {
                    if (isset($_SESSION['user']['id']))
                    {
                        $cols = '`user_id`, `name`';
                        $data = [$_SESSION['user']['id'], $author];
                        $parameters = '?,?';
                    }
                    else
                    {
                        $cols = '`name`';
                        $data = [$author];
                        $parameters = '?';
                    }

                    $query = "INSERT INTO `persons`($cols) VALUES ($parameters);";
                    $authorId = libSqlQuery($link, $query, ...$data);

                    if ($authorId)
                    {
                        $query = "INSERT INTO `authors_of_phrases` VALUES (?,?);";
                        
                        libSqlQuery($link, $query, $phraseId, $authorId);
                    }
                    else
                    {
                        $output['error'] = array(
                            'code' => '520',
                            'message' => "Unknown error was been occured when new author was adding.",
                            'errors' => [[
                                    'message' => 'New author was not been added.',
                                    'domain' => 'creatorassethub.phrases',
                                    'reason' => 'unknownError',
                            ]],
                        );
            
                        header("HTTP/1.1 520 Unknown Error");
                        return;
                    }
                }
            }

            
            
            if ($dataSources)
            {
                array_push($qSourcesColumns,
                    [ 'column' => '`material_id`', ],
                    [ 'column' => '`url`', ],
                    [ 'column' => '`title`', ],
                    [ 'column' => '`time`', ],
                );
                
                foreach ($dataSources as $source)
                {
                    $s = [
                        $phraseId,
                        $source['sourceUrl'],
                        $source['sourceTitle'],
                        $source['sourceTime'],
                    ];

                    $cols = implode(', ', array_column($qSourcesColumns, 'column'));
                    $parameters = implode(',', array_fill(0, count(array_column($qSourcesColumns, 'column')), '?'));

                    $query = "INSERT INTO `sources`($cols) VALUES ($parameters);";

                    $result = libSqlQuery($link, $query, ...$s);

                    if (!$result)
                    {
                        $output['error'] = array(
                            'code' => '520',
                            'message' => "Unknown error was been occured when source was adding.",
                            'errors' => [
                                [
                                    'message' => 'New source was not been added.',
                                    'domain' => 'creatorassethub.phrases',
                                    'reason' => 'unknownError',
                                ],
                            ],
                        );
            
                        header("HTTP/1.1 520 Unknown Error");
                        return;
                    }
                }
            }

            if ($dataSynonyms)
            {
                foreach ($dataSynonyms as &$synonym)
                {
                    $qSynonymsParameters[] = [$phraseId, &$synonym];
                }

                $query = "INSERT INTO `phrases_synonyms`(`phrase_id`, `synonym`) VALUES (?,?);";

                $result = libSqlInsert($link, $query, $qSynonymsParameters);

                if (!$result)
                {
                    $output['error'] = array(
                        'code' => '520',
                        'message' => "Unknown error was been occured when synonym was adding.",
                        'errors' => [
                            [
                                'message' => 'New synonym was not been added.',
                                'domain' => 'creatorassethub.phrases',
                                'reason' => 'unknownError',
                            ],
                        ],
                    );
        
                    header("HTTP/1.1 520 Unknown Error");
                    return;
                }
            }

            if ($dataTags)
            {
                foreach ($dataTags as $tag)
                {
                    $query = "SELECT `id` FROM `materials_tags` WHERE `tag` = ?;";
                    $rows = libSqlQuery($link, $query, $tag);

                    if ($rows)
                    {
                        $tagId = $rows[0]['materials_tags_id'];
                        
                        $query = "INSERT INTO `tags_of_materials` VALUES ($phraseId,$tagId);";
                        $res = libSqlQuery($link, $query);
                    }
                    else
                    {
                        if (isset($_SESSION['user']['id']))
                        {
                            $cols = '`tag`, `user_id`';
                            $data = [$tag, $_SESSION['user']['id']];
                            $parameters = '?,?';
                        }
                        else
                        {
                            $cols = '`tag`';
                            $data = [$tag];
                            $parameters = '?';
                        }

                        $query = "INSERT INTO `materials_tags`($cols) VALUES ($parameters);";
                        $tagId = libSqlQuery($link, $query, ...$data);

                        if ($tagId)
                        {
                            $query = "INSERT INTO `tags_of_materials` VALUES ($phraseId,$tagId);";
                            $res = libSqlQuery($link, $query);
                        }
                        else
                        {
                            $output['error'] = array(
                                'code' => '520',
                                'message' => "Unknown error was been occured when new tag was adding.",
                                'errors' => [
                                    [
                                        'message' => 'New tag was not been added.',
                                        'domain' => 'creatorassethub.phrases',
                                        'reason' => 'unknownError',
                                    ],
                                ],
                            );
                
                            header("HTTP/1.1 520 Unknown Error");
                            return;
                        }
                    }
                }
            }
            
            // ...INSERT INTO `related_materials` (1,2);

            mysqli_commit($link);
        }
        else
        {
            $output['error'] = array(
                'code' => '520',
                'message' => "Unknown error was been occured when new phrase was adding.",
                'errors' => [
                    [
                        'message' => 'New phrase was not been added.',
                        'domain' => 'creatorassethub.phrases',
                        'reason' => 'unknownError',
                    ],
                ],
            );

            header("HTTP/1.1 520 Unknown Error");
            return;
        }


        $output = array(
            'kind' => 'phrases#phrase',
            'id' => $phraseId,
            'keyCode' => $displayCode,
        );



        foreach ($request_parts as $part)
        {
            switch ($part)
            {
                case 'snippet':

                    // ... $output['snippet'] ...

                    break;
                default:
                    $output['error']['errors'][] = [
                        'message' => $part,
                        'domain' => 'creatorassethub.part',
                        'reason' => 'unknownPart',
                        'location' => 'part',
                        'locationPart' => 'parameter',
                    ];
                    break;
            }
        }

        if (isset($output['error']))
        {
            // $output = array('error' => $output['error']); // - УБИРАЕМ ПРИ ПОДОБНОМ ЗАПРОСЕ, ТАК КАК ДАННЫЕ В БД УЖЕ ЗАНЕСЕНЫ

            $output['error'] = array(
                'code' => '400',
                'message' => 'Unknown parts of return\'s Request.',
            ) + $output['error'];
        }

        header("HTTP/1.1 200 OK");
    }
    catch (mysqli_sql_exception $exception)
    {
        // mysqli_rollback($link);

        $output['error'] = array(
            'code' => '500',
            'message' => "Server error was occured when it tried to add new phrase.",
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

    mysqli_close($link);
}