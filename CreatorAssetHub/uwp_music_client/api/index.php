<?php

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: *");

// require_once 'service/bflib.php';
session_id('bf-sg-session');
session_start();

mb_internal_encoding("utf-8");

$pars = strpos($_SERVER['REQUEST_URI'], '?');
$path = explode('/', substr($_SERVER['REQUEST_URI'], 0, ($pars === false) ? null : $pars));

// архивная папка
if ($path[1] === '1') {
    unset($path[1]);
    $path = array_values($path);
}

$method = $_SERVER['REQUEST_METHOD'];
switch ($method)
{
    case "POST":
        if ($path[3] === 'user')
        {
            if ($path[4] === 'login')
            {
                if (isset($_SESSION['user']['id']))
                {
                    $data = array(
                        'code' => '201',
                        'message' => 'User already is authorized.',
                        'snippet' => [
                            'userid' => (int)$_SESSION['user']['id'],
                            'useremail' => $_SESSION['user']['email'],
                            'userrole' => $_SESSION['user']['role'],
                        ],
                    );
                }
                else
                {
                    $data = json_decode(file_get_contents('php://input'), true);

                    if (isset($data["snippet"]))
                    {
                        $email = $data["snippet"]["email"];
                        $pass = $data["snippet"]["pass"];
        
                        require_once '../../service/config.php';
                        $database = 'sgdb';
                        $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером');
                        
                        $email = htmlentities(mysqli_real_escape_string($link, $email));
        
                        $query = "SELECT `Id`, `Password`, `Role` FROM `users` WHERE `Login` = '$email'";
                        
                        $output = mysqli_query($link, $query) or die('Не удалось загрузить данные.');
                        mysqli_close($link);
        
                        if ($output)
                        {
                            $row = mysqli_fetch_assoc($output);
        
                            $data = null;
                            
                            if ($row && password_verify($pass, $row['Password']) === true)
                            {
                                $_SESSION['user']['id'] = $row['Id'];
                                $_SESSION['user']['email'] = $email;
                                $_SESSION['user']['password'] = $row['Password'];
                                $_SESSION['user']['role'] = $row['Role'];
                                
                                $data = array(
                                    'code' => '201',
                                    'message' => 'User is authorized.',
                                    'snippet' => [
                                        'userid' => (int)$row['Id'],
                                        'useremail' => $email,
                                        'userrole' => $row['Role'],
                                    ],
                                );
                            }
                            else
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'The reqired user is not exist.',
                                    'errors' => [
                                        [
                                            'message' => 'Bad password were post.',
                                            'domain' => 'creatorassethub.account',
                                            'reason' => 'userForbidden',
                                        ],
                                    ],
                                ];
                            }
                        }
                        else
                        {
                            $data['error'] = [
                                'code' => '404',
                                'message' => 'The reqired user is not exist.',
                                'errors' => [
                                    [
                                        'message' => 'The reqired user is not exist.',
                                        'domain' => 'creatorassethub.account',
                                        'reason' => 'userNotFound',
                                    ],
                                ],
                            ];
                        }
                    }
                    else
                    {
                        $data['error'] = [
                            'code' => '400',
                            'message' => 'The snippet of data were not found.',
                            'errors' => [
                                [
                                    'message' => 'The snippet of data were not found.',
                                    'domain' => 'creatorassethub.account',
                                    'reason' => 'dataNotFound',
                                ],
                            ],
                        ];
                    }
                }
            }
            else if ($path[4] === 'playlistitems')
            {
                if (isset($_SESSION['user']['id']))
                {
                    $userId = $_SESSION['user']['id'];

                    if (!isset($path[5]))
                    {
                        $data = json_decode(file_get_contents('php://input'), true);
                        
                        if (isset($data["snippet"]))
                        {
                            $PostPlaylistId = $data["snippet"]["Id"];
                            $postKind = $data["snippet"]["ResourceId"]["Kind"];
                            $postId = $data["snippet"]["ResourceId"]["Id"];

                            if ($postKind === "music#composition")
                            {
                                $query = "CALL `addtoplaylist`($userId, $postId, $PostPlaylistId);";
                            }
                            else
                            {
                                $postKind = null;
                                $data['error'] = [
                                    'code' => '400',
                                    'message' => 'Unsupported kind of sent resource.',
                                    'errors' => [
                                        [
                                            'message' => 'Unsupported kind of sent resource.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'postBadRequest',
                                        ],
                                    ],
                                ];
                            }

                            if ($postKind)
                            {
                                try
                                {
                                    require_once '../../service/config.php';
                                    $database = 'sgdb';

                                    $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');
                                
                                    $result = mysqli_query($link, $query) or die("Ошибка входа в систему.");
                                    mysqli_close($link);

                                    if ($result)
                                    {
                                        $row = mysqli_fetch_assoc($result);

                                        if ($row['result'] === '1')
                                        {
                                            $data['error'] = [
                                                'code' => '400',
                                                'message' => 'The required user is not exist.',
                                                'errors' => [
                                                    [
                                                        'message' => 'The required user is not exist.',
                                                        'domain' => 'creatorassethub.music',
                                                        'reason' => 'userNotFound',
                                                    ],
                                                ],
                                            ];
                                        }
                                        else if ($row['result'] === '2')
                                        {
                                            $data['error'] = [
                                                'code' => '400',
                                                'message' => 'The required composition is not exist.',
                                                'errors' => [
                                                    [
                                                        'message' => 'The required composition is not exist.',
                                                        'domain' => 'creatorassethub.music',
                                                        'reason' => 'compositionNotFound',
                                                    ],
                                                ],
                                            ];
                                        }
                                        else if ($row['result'] === '3')
                                        {
                                            $data['error'] = [
                                                'code' => '400',
                                                'message' => 'The required playlist is not exist.',
                                                'errors' => [
                                                    [
                                                        'message' => 'The required playlist is not exist.',
                                                        'domain' => 'creatorassethub.music',
                                                        'reason' => 'playlistNotFound',
                                                    ],
                                                ],
                                            ];
                                        }
                                        else if ($row['result'] === '4')
                                        {
                                            $data['error'] = [
                                                'code' => '403',
                                                'message' => 'The required playlist is not allowed for public actions.',
                                                'errors' => [
                                                    [
                                                        'message' => 'The required playlist is not allowed for public actions.',
                                                        'domain' => 'creatorassethub.music',
                                                        'reason' => 'playlistForbidden',
                                                    ],
                                                ],
                                            ];
                                        }
                                        else if ($row['result'] === '5')
                                        {
                                            $data = [
                                                'kind' => 'music#playlistItem',
                                                'id' => 0,
                                            ];
                                        }
                                        else if ($row['result'] === '6')
                                        {
                                            $data = [
                                                'kind' => 'music#playlistItem',
                                                'id' => (int)$row['id'],
                                            ];
                                        }
                                        else
                                        {
                                            $data['error'] = [
                                                'code' => '520',
                                                'message' => 'Sorry, we don\'t know about the result of operation.',
                                                'errors' => [
                                                    [
                                                        'message' => 'Sorry, we don\'t know about the result of operation.',
                                                        'domain' => 'creatorassethub.music',
                                                        'reason' => 'serverUnknownError',
                                                    ],
                                                ],
                                            ];
                                        }
                                    }
                                }
                                catch (Exception $e)
                                {
                                    $data['error'] = [
                                        'code' => '500',
                                        'message' => $e->getMessage(),
                                        'errors' => [
                                            [
                                                'message' => $e->getMessage(),
                                                'domain' => 'creatorassethub.music',
                                                'reason' => 'serverError',
                                            ],
                                        ],
                                    ];
                                }
                            }
                        }
                        else
                        {
                            $data['error'] = [
                                'code' => '400',
                                'message' => 'The snippet of data were not found.',
                                'errors' => [
                                    [
                                        'message' => 'The snippet of data were not found.',
                                        'domain' => 'creatorassethub.music',
                                        'reason' => 'dataNotFound',
                                    ],
                                ],
                            ];
                        }
                    }
                    else if ($path[5] === 'favorites')
                    {
                        $data = json_decode(file_get_contents('php://input'), true);
                        
                        if (isset($data["snippet"]))
                        {
                            $postKind = $data["snippet"]["ResourceId"]["Kind"];
                            $postId = $data["snippet"]["ResourceId"]["Id"];

                            if ($postKind === "music#composition")
                            {
                                $query = "CALL `favorite`($userId, $postId);";
                            }
                            else
                            {
                                $postKind = null;
                                $data['error'] = [
                                    'code' => '400',
                                    'message' => 'Unsupported kind of sent resource.',
                                    'errors' => [
                                        [
                                            'message' => 'Unsupported kind of sent resource.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'postBadRequest',
                                        ],
                                    ],
                                ];
                            }

                            if ($postKind)
                            {
                                try
                                {
                                    require_once '../../service/config.php';
                                    $database = 'sgdb';

                                    $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');
                                
                                    $result = mysqli_query($link, $query) or die("Ошибка входа в систему.");
                                    mysqli_close($link);

                                    if ($result)
                                    {
                                        $row = mysqli_fetch_assoc($result);

                                        if ($row['result'] === '1')
                                        {
                                            $data['error'] = [
                                                'code' => '400',
                                                'message' => 'The required user is not exist.',
                                                'errors' => [
                                                    [
                                                        'message' => 'The required user is not exist.',
                                                        'domain' => 'creatorassethub.music',
                                                        'reason' => 'userNotFound',
                                                    ],
                                                ],
                                            ];
                                        }
                                        else if ($row['result'] === '2')
                                        {
                                            $data['error'] = [
                                                'code' => '400',
                                                'message' => 'The required composition is not exist.',
                                                'errors' => [
                                                    [
                                                        'message' => 'The required composition is not exist.',
                                                        'domain' => 'creatorassethub.music',
                                                        'reason' => 'compositionNotFound',
                                                    ],
                                                ],
                                            ];
                                        }
                                        else if ($row['result'] === '3')
                                        {
                                            $data = [
                                                'kind' => 'music#playlistItem',
                                                'id' => (int)$row['id'],
                                            ];
                                        }
                                        else
                                        {
                                            $data['error'] = [
                                                'code' => '520',
                                                'message' => 'Sorry, we don\'t know about the result of operation.',
                                                'errors' => [
                                                    [
                                                        'message' => 'Sorry, we don\'t know about the result of operation.',
                                                        'domain' => 'creatorassethub.music',
                                                        'reason' => 'serverUnknownError',
                                                    ],
                                                ],
                                            ];
                                        }
                                    }
                                }
                                catch (Exception $e)
                                {
                                    $data['error'] = [
                                        'code' => '500',
                                        'message' => $e->getMessage(),
                                        'errors' => [
                                            [
                                                'message' => $e->getMessage(),
                                                'domain' => 'creatorassethub.music',
                                                'reason' => 'serverError',
                                            ],
                                        ],
                                    ];
                                }
                            }
                        }
                        else
                        {
                            $data['error'] = [
                                'code' => '400',
                                'message' => 'The snippet of data were not found.',
                                'errors' => [
                                    [
                                        'message' => 'The snippet of data were not found.',
                                        'domain' => 'creatorassethub.music',
                                        'reason' => 'dataNotFound',
                                    ],
                                ],
                            ];
                        }
                    }
                }
                else
                {
                    $data['error'] = [
                        'code' => '401',
                        'message' => 'The user is unauthorized.',
                        'errors' => [
                            [
                                'message' => 'The user is unauthorized.',
                                'domain' => 'creatorassethub.music',
                                'reason' => 'userUnauthorized',
                            ],
                        ],
                    ];
                }
            }
        }

        die (json_encode($data));

        break;
    case 'GET':
        if ($path[3] === 'user')
        {
            if ($path[4] === 'login')
            {
                if (isset($_SESSION['user']['id']))
                {
                    $data = array(
                        'code' => '201',
                        'message' => 'User already is authorized.',
                        'snippet' => [
                            'userid' => (int)$_SESSION['user']['id'],
                            'useremail' => $_SESSION['user']['email'],
                            'userrole' => $_SESSION['user']['role'],
                        ],
                    );
                }
                else
                {
                    $data['error'] = [
                        'code' => '401',
                        'message' => 'The user is unauthorized.',
                        'errors' => [
                            [
                                'message' => 'The user is unauthorized.',
                                'domain' => 'creatorassethub.music',
                                'reason' => 'userUnauthorized',
                            ],
                        ],
                    ];
                }
            }
            else if ($path[4] === 'logout')
            {
                if (isset($_SESSION['user']['id']))
                {
                    unset($_SESSION['user']['id']);
                }

                header("HTTP/1.1 204 No Content");
                return;
            }
            else if ($path[4] === 'playlists')
            {
                if (isset($_SESSION['user']['id']))
                {
                    $userId = $_SESSION['user']['id'];

                    $getLimit = 20; // число плейлистов по умолчанию
                    if (isset($_GET['maxResults']))
                    {
                        if ($_GET['maxResults'] > 10)
                            $getLimit = $_GET['maxResults'] > 30 ? 30 : (int)$_GET['maxResults'];
                        else
                            $getLimit = 10; // минимальное число плейлистов
                    }
                    
                    $getShift = $_GET['page'] - 1 ?? 0;

                    try
                    {
                        require_once '../../service/config.php';
                        $database = 'sgdb';

                        $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');
                    
                        $query = "SELECT p.`Id`, p.`Title`, p.`Description`, (SELECT COUNT(*) FROM `compositionsofplaylists` cp WHERE cp.`PlaylistId`=p.`Id`) AS `Summary`, (SELECT a.`PictureName` FROM `albums` a LEFT JOIN `compositions` c ON c.`AlbumId`=a.`Id` LEFT JOIN `compositionsofplaylists` cp ON cp.`CompositionId`=c.`Id` WHERE cp.`PlaylistId`=p.`Id` AND a.`PictureName` IS NOT NULL LIMIT 1) AS `PictureName` FROM `playlists` p WHERE p.`UserId`=$userId LIMIT $getShift, $getLimit;";
                        
                        $result = mysqli_query($link, $query) or die("Ошибка входа в систему.");
                        mysqli_close($link);

                        if ($result)
                        {
                            while($row = mysqli_fetch_assoc($result))
                            {
                                if ($row['PictureName'])
                                {
                                    $picture = file_exists('../images/albums/'.$row['PictureName']);
                                    if ($picture)
                                        $picture = base64_encode($picture);
                                    else
                                        $picture = null; // файл не найден
                                }
                                else
                                    $picture = null; // нет альбома/картинки

                                $playlists[] = array(
                                    'kind' => 'music#playlist',
                                    'id' => (int)$row['Id'],
                                    'snippet' => [
                                        'title' => $row['Title'],
                                        'description' => $row['Description'],
                                        'summary' => (int)$row['Summary'],
                                        'picture' => $picture,
                                        // 'pictureName' => $row['PictureName'],
                                    ],
                                );
                            }

                            $data = array(
                                'kind' => 'music#playlistListResponse',
                                'pageInfo' => [
                                    'resultsPerPage' => $getLimit,
                                ],
                                'items' => $playlists,
                            );
                        }
                        else
                        {
                            $data['error'] = [
                                'code' => '404',
                                'message' => 'The reqired playlists are not exist.',
                                'errors' => [
                                    [
                                        'message' => 'The reqired playlists are not exist.',
                                        'domain' => 'creatorassethub.music',
                                        'reason' => 'userPlaylistsNotFound',
                                    ],
                                ],
                            ];
                        }
                    }
                    catch (Exception $e)
                    {
                        $data['error'] = [
                            'code' => '500',
                            'message' => $e->getMessage(),
                            'errors' => [
                                [
                                    'message' => $e->getMessage(),
                                    'domain' => 'creatorassethub.music',
                                    'reason' => 'serverError',
                                ],
                            ],
                        ];
                    }
                }
                else
                {
                    $data['error'] = [
                        'code' => '401',
                        'message' => 'The user is unauthorized.',
                        'errors' => [
                            [
                                'message' => 'The user is unauthorized.',
                                'domain' => 'creatorassethub.music',
                                'reason' => 'userUnauthorized',
                            ],
                        ],
                    ];
                }
            }
            else if ($path[4] === 'compositions')
            {
                if (isset($_SESSION['user']['id']))
                {
                    $parts = explode(',', $_GET['part']);

                    $snippet = array_search('snippet', $parts);
                    $userAssociations = array_search('userAssociations', $parts);
                    $userPlaylists = array_search('userPlaylists', $parts);

                    $getType = $_GET['type'];
                    $userId = $_SESSION['user']['id'];

                    $getLimit = 20; // число плейлистов по умолчанию
                    if (isset($_GET['maxResults']))
                    {
                        if ($_GET['maxResults'] > 10)
                            $getLimit = $_GET['maxResults'] > 30 ? 30 : (int)$_GET['maxResults'];
                        else
                            $getLimit = 10; // минимальное число плейлистов
                    }
                    
                    $getShift = $_GET['page'] - 1 ?? 0;

                    $getLimitUserPlaylists = 5; // число пользовательских плейлистов
                    $getShiftUserPlaylists = 0;

                    if ($getType === 'mix')
                    {
                        $query = "SELECT COUNT(*) AS `User Playlists` FROM `playlists` WHERE `UserId`=$userId;";
                        $query .= "SELECT c.`Id` AS `Composition Id`, a.`Id` AS `Artist Id`, a.`Name` AS `Artist Name`, c.`Name`, c.`Date`, c.`Text`, c.`Auditions`, c.`Explicit`, c.`Duration`, c.`FileName`, al.`Id` AS `Album Id`, al.`Name` AS `Album Name`, al.`PictureName`, IF(c.`Id` IN (SELECT cp.`CompositionId` FROM `compositionsofplaylists` cp LEFT JOIN `playlists` p ON p.`Id`=cp.`PlaylistId` WHERE p.`UserId` = $userId AND p.`Title` = 'Понравившееся'), true, false) AS `IsLiked` FROM `compositions` c INNER JOIN `compsofartists` ca ON ca.CompositionId = c.`Id` LEFT JOIN `artists` a ON a.Id = ca.ArtistId LEFT JOIN `albums` al ON al.`Id` = c.`AlbumId` ORDER BY RAND() LIMIT $getShift, $getLimit;";
                        $query .= "SELECT `Id`, `Title` FROM `playlists` WHERE `UserId`=$userId ORDER BY `lastModified` DESC LIMIT $getShiftUserPlaylists, $getLimitUserPlaylists;";
                    }
                    else
                    {
                        $getType = null;
                        $data['error'] = [
                            'code' => '400',
                            'message' => 'The sent type of set is not right.',
                            'errors' => [
                                [
                                    'message' => 'The sent type of is are not right.',
                                    'domain' => 'creatorassethub.music',
                                    'reason' => 'uriBadRequest',
                                ],
                            ],
                        ];
                    }

                    if ($getType)
                    {
                        try
                        {
                            require_once '../../service/config.php';
                            $database = 'sgdb';
    
                            $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');

                            mysqli_multi_query($link, $query);

                            $result1 = mysqli_store_result($link);

                            mysqli_next_result($link);

                            $result2 = mysqli_store_result($link); // информация о композициях

                            mysqli_next_result($link);

                            $result3 = mysqli_store_result($link); // информация о последних изменённых плейлистах пользователя

                            mysqli_close($link);

                            if ($result1 && $result2 && $result3)
                            {
                                $set = mysqli_fetch_assoc($result1);

                                $comps = array();

                                $i = 0; $next = true;
                                while($composition = mysqli_fetch_assoc($result2))
                                {
                                    if ($snippet !== false)
                                    {
                                        if ($composition['PictureName'])
                                        {
                                            $picture = file_exists('../images/albums/'.$composition['PictureName']);
                                            if ($picture)
                                                $picture = base64_encode($picture);
                                            else
                                                $picture = null; // файл не найден
                                        }
                                        else
                                            $picture = null; // нет альбома/картинки


                                        $snippet = [
                                            'name' => $composition['Name'],
                                            'artists' => [
                                                [
                                                    'id' => (int)$composition['Artist Id'],
                                                    'name' => $composition['Artist Name'],
                                                ],
                                            ],
                                            'album' => [
                                                'id' => (int)$composition['Album Id'],
                                                'name' => $composition['Album Name'],
                                                'picture' => $picture,
                                            ],
                                            'date' => $composition['Date'],
                                            'text' => $composition['Text'],
                                            'auditions' => (int)$composition['Auditions'],
                                            'explicit' => (boolean)$composition['Explicit'],
                                            'duration' => (int)$composition['Duration'],
                                            'namepath' => $composition['FileName'],
                                        ];
                                    }

                                    if ($userAssociations)
                                    {
                                        $userAssociations = [
                                            'liked' => (boolean)$composition['IsLiked'],
                                        ];
                                    }

                                    $comps[] = array(
                                        'kind' => 'music#composition',
                                        'id' => (int)$composition['Composition Id'],
                                        'snippet' => $snippet ? $snippet : null,
                                        'user associations' => $userAssociations ? $userAssociations : null,
                                    );
                                }

                                $account = array();

                                if ($userPlaylists)
                                {
                                    $playlists = array();

                                    while($row = mysqli_fetch_assoc($result3))
                                    {
                                        if ($snippet !== false)
                                        {
                                            $snippet = [
                                                'title' => $row['Title'],
                                            ];
                                        }
                                        $playlists[] = [
                                            'kind' => 'music#playlist',
                                            'id' => (int)$row['Id'],
                                            'snippet' => $snippet ? $snippet : null,
                                        ];
                                    }

                                    $userPlaylists = [
                                        'kind' => 'music#playlistListResponse',
                                        'pageInfo' => [
                                            'totalResults' => (int)$set['User Playlists'],
                                            'resultsPerPage' => $getLimitUserPlaylists,
                                        ],
                                        'items' => $playlists,
                                    ];

                                    $account['playlists'] = $userPlaylists;
                                }

                                $data = array(
                                    'kind' => 'music#compositionListResponse',
                                    'items' => $comps,
                                    'account' => $account ? $account : null,
                                );
                            }
                            else if (!$result2)
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'Compositions are not found.',
                                    'errors' => [
                                        [
                                            'message' => 'Compositions are not found.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'compositionNotFound',
                                        ],
                                    ],
                                ];
                            }
                            else if (!$result3)
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'Artists of associated compositions are not found.',
                                    'errors' => [
                                        [
                                            'message' => 'Artists of associated compositions are not found.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'artistNotFound',
                                        ],
                                    ],
                                ];
                            }
                        }
                        catch (Exception $e)
                        {
                            $data['error'] = [
                                'code' => '500',
                                'message' => $e->getMessage(),
                                'errors' => [
                                    [
                                        'message' => $e->getMessage(),
                                        'domain' => 'creatorassethub.music',
                                        'reason' => 'serverError',
                                    ],
                                ],
                            ];
                        }
                    }
                }
                else
                {
                    $data['error'] = [
                        'code' => '401',
                        'message' => 'The user is unauthorized.',
                        'errors' => [
                            [
                                'message' => 'The user is unauthorized.',
                                'domain' => 'creatorassethub.music',
                                'reason' => 'userUnauthorized',
                            ],
                        ],
                    ];
                }
            }
        }
        else if ($path[3] === 'compositions')
        {
            if ($path[4] === 'top')
            {
                $getId = (int)$_GET['id'];
            }
        }
        else if ($path[3] === 'playlists')
        {
            if ($path[4] === 'open')
            {
                if (isset($_SESSION['user']['id']))
                {
                    if (isset($_GET['part']))
                    {
                        $parts = explode(',', $_GET['part']);

                        $snippet = array_search('snippet', $parts);
                        $userAssociations = array_search('userAssociations', $parts);
                        $userPlaylists = array_search('userPlaylists', $parts);

                        $getId = (int)$_GET['id'];
                        $userId = $_SESSION['user']['id'];

                        $getLimit = 25; // число композиций по умолчанию
                        if (isset($_GET['maxResults']))
                        {
                            if ($_GET['maxResults'] > 20)
                                $getLimit = $_GET['maxResults'] > 50 ? 50 : (int)$_GET['maxResults'];
                            else
                                $getLimit = 20; // минимальное число композиций
                        }
                            
                        $getShift = $_GET['page'] - 1 ?? 0;

                        $getLimitUserPlaylists = 5; // число пользовательских плейлистов
                        $getShiftUserPlaylists = 0;

                        try
                        {
                            require_once '../../service/config.php';
                            $database = 'sgdb';

                            $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');
                        
                            // (SELECT SUM(`Duration`) FROM `compositions` c LEFT JOIN `compositionsofplaylists` cp ON cp.`CompositionId`=c.`Id` WHERE cp.`PlaylistId`=p.`Id`) AS `Duration`
                            $query = "SELECT `Title`, `Description`, (SELECT COUNT(*) FROM `compositionsofplaylists` cp WHERE cp.`PlaylistId`=p.`Id`) AS `Total`, (SELECT COUNT(*) FROM `playlists` WHERE `UserId`=$userId) AS `User Playlists` FROM `playlists` p WHERE Id=$getId;";
                            $query .= "SELECT c.`Id`, c.`Name`, c.`Date`, c.`Text`, c.`Auditions`, c.`Explicit`, c.`Duration`, c.`FileName`, a.`Id` AS `Album Id`, a.`Name` AS `Album Name`, a.`PictureName`, IF(c.`Id` IN (SELECT cp.`CompositionId` FROM `compositionsofplaylists` cp LEFT JOIN `playlists` p ON p.`Id`=cp.`PlaylistId` WHERE p.`UserId` = $userId AND p.`Title` = 'Понравившееся'), true, false) AS `IsLiked`, cp.`Id` AS `CompOfPlaylist Id` FROM `compositions` c LEFT JOIN `compositionsofplaylists` cp ON cp.`CompositionId` = c.`Id` LEFT JOIN `albums` a ON a.`Id` = c.`AlbumId` WHERE cp.`PlaylistId`=$getId LIMIT $getShift, $getLimit;";
                            $query .= "SELECT c.`Id` AS `Composition Id`, a.`Id` AS `Artist Id`, a.`Name` FROM `artists` a LEFT JOIN `compsofartists` ca ON ca.`ArtistId` = a.`Id` LEFT JOIN `compositions` c ON c.Id = ca.`CompositionId` LEFT JOIN `compositionsofplaylists` cp ON cp.`CompositionId` = c.`Id` WHERE cp.`PlaylistId`=$getId LIMIT $getShift, $getLimit;";
                            $query .= "SELECT `Id`, `Title` FROM `playlists` WHERE `UserId`=$userId ORDER BY `lastModified` DESC LIMIT $getShiftUserPlaylists, $getLimitUserPlaylists;";

                            mysqli_multi_query($link, $query);

                            $result1 = mysqli_store_result($link); // информация о плейлисте

                            mysqli_next_result($link);

                            $result2 = mysqli_store_result($link); // информация о композициях

                            mysqli_next_result($link);

                            $result3 = mysqli_store_result($link); // информация об исполнителях

                            mysqli_next_result($link);

                            $result4 = mysqli_store_result($link); // информация о последних изменённых плейлистах пользователя

                            mysqli_close($link);

                            if ($result1 && $result2 && $result3 && $result4)
                            {
                                $playlist = mysqli_fetch_assoc($result1);

                                $comps = array();

                                $i = 0; $next = true;
                                while($composition = mysqli_fetch_assoc($result2))
                                {
                                    if ($snippet !== false)
                                    {
                                        $artists = array();

                                        for (; $i < mysqli_num_rows($result3);)
                                        {
                                            if ($next)
                                                $artist = mysqli_fetch_assoc($result3);
                                            
                                            if ($artist['Composition Id'] === $composition['Id'])
                                            {
                                                $artists[] = [
                                                    'id' => (int)$artist['Artist Id'],
                                                    'name' => $artist['Name'],
                                                ];
                                                $i++;
                                                $next = true;
                                            }
                                            else
                                            {
                                                $next = false;
                                                break;
                                            }
                                        }

                                        
                                        if ($composition['PictureName'])
                                        {
                                            $picture = file_exists('../images/albums/'.$composition['PictureName']);
                                            if ($picture)
                                                $picture = base64_encode($picture);
                                            else
                                                $picture = null; // файл не найден
                                        }
                                        else
                                            $picture = null; // нет альбома/картинки


                                        $snippet = [
                                            'name' => $composition['Name'],
                                            'artists' => $artists,
                                            'album' => [
                                                'id' => (int)$composition['Album Id'],
                                                'name' => $composition['Album Name'],
                                                'picture' => $picture,
                                            ],
                                            'date' => $composition['Date'],
                                            'text' => $composition['Text'],
                                            'auditions' => (int)$composition['Auditions'],
                                            'explicit' => (boolean)$composition['Explicit'],
                                            'duration' => (int)$composition['Duration'],
                                            'namepath' => $composition['FileName'],
                                        ];
                                    }

                                    if ($userAssociations)
                                    {
                                        $userAssociations = [
                                            'liked' => (boolean)$composition['IsLiked'],
                                        ];
                                    }
                                    $comps[] = array(
                                        'kind' => 'music#composition',
                                        'id' => (int)$composition['Id'],
                                        'snippet' => $snippet ? $snippet : null,
                                        'user associations' => $userAssociations ? $userAssociations : null,
                                        'playlist' => [
                                            'id' => (int)$composition['CompOfPlaylist Id'],
                                        ],
                                    );
                                }

                                $account = array();

                                if ($userPlaylists)
                                {
                                    $playlists = array();

                                    while($row = mysqli_fetch_assoc($result4))
                                    {
                                        if ($snippet !== false)
                                        {
                                            $snippet = [
                                                'title' => $row['Title'],
                                            ];
                                        }
                                        $playlists[] = [
                                            'kind' => 'music#playlist',
                                            'id' => (int)$row['Id'],
                                            'snippet' => $snippet ? $snippet : null,
                                        ];
                                    }

                                    $userPlaylists = [
                                        'kind' => 'music#playlistListResponse',
                                        'pageInfo' => [
                                            'totalResults' => (int)$playlist['User Playlists'],
                                            'resultsPerPage' => $getLimitUserPlaylists,
                                        ],
                                        'items' => $playlists,
                                    ];

                                    $account['playlists'] = $userPlaylists;
                                }
                                if ($snippet !== false)
                                {
                                    $snippet = [
                                        'title' => $playlist['Title'],
                                        'description' => $playlist['Description'],
                                    ];
                                }
                                $data = array(
                                    'kind' => 'music#playlist',
                                    'id' => $getId,
                                    'snippet' => $snippet ? $snippet : null,
                                    'pageInfo' => [
                                        'totalResults' => (int)$playlist['Total'],
                                        'resultsPerPage' => $getLimit,
                                    ],
                                    'items' => $comps,
                                    'account' => $account ? $account : null,
                                );
                            }
                            else if (!$result1)
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'The reqired playlist is not exist.',
                                    'errors' => [
                                        [
                                            'message' => 'The reqired playlist is not exist.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'playlistNotFound',
                                        ],
                                    ],
                                ];
                            }
                            else if (!$result2)
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'Compositions of reqired playlist are not found.',
                                    'errors' => [
                                        [
                                            'message' => 'Compositions of reqired playlist are not found.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'compositionNotFound',
                                        ],
                                    ],
                                ];
                            }
                            else if (!$result3)
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'Artists of associated compositions are not found.',
                                    'errors' => [
                                        [
                                            'message' => 'Artists of associated compositions are not found.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'artistNotFound',
                                        ],
                                    ],
                                ];
                            }
                            else
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'User playlists are not found.',
                                    'errors' => [
                                        [
                                            'message' => 'User playlists are not found.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'playlistNotFound',
                                        ],
                                    ],
                                ];
                            }
                        }
                        catch (Exception $e)
                        {
                            $data['error'] = [
                                'code' => '500',
                                'message' => $e->getMessage(),
                                'errors' => [
                                    [
                                        'message' => $e->getMessage(),
                                        'domain' => 'creatorassethub.music',
                                        'reason' => 'serverError',
                                    ],
                                ],
                            ];
                        }
                    }
                    else
                    {
                        $data['error'] = [
                            'code' => '400',
                            'message' => 'The request has no part parameter.',
                            'errors' => [
                                [
                                    'message' => 'The request has no part parameter.',
                                    'domain' => 'creatorassethub.music',
                                    'reason' => 'uriBadRequest',
                                ],
                            ],
                        ];
                    }
                }
                else
                {
                    $data['error'] = [
                        'code' => '401',
                        'message' => 'The user is unauthorized.',
                        'errors' => [
                            [
                                'message' => 'The user is unauthorized.',
                                'domain' => 'creatorassethub.music',
                                'reason' => 'userUnauthorized',
                            ],
                        ],
                    ];
                }
            }
        }
        else if ($path[3] === 'sets')
        {
            if (!isset($path[4]))
            {
                $getLimit = 20; // число наборов по умолчанию
                if (isset($_GET['maxResults']))
                {
                    if ($_GET['maxResults'] > 10)
                        $getLimit = $_GET['maxResults'] > 30 ? 30 : (int)$_GET['maxResults'];
                    else
                        $getLimit = 10; // минимальное число наборов
                }
                
                $getShift = $_GET['page'] - 1 ?? 0;

                try
                {
                    require_once '../../service/config.php';
                    $database = 'sgdb';

                    $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');
                
                    $query = "SELECT s.`Id`, s.`Title`, s.`Description`, s.`PictureName`, (SELECT COUNT(*) FROM `compsofeditorschoice` cs WHERE cs.`SetId`=s.`Id`) AS `Summary` FROM `editorschoice` s ORDER BY s.`lastModified` DESC LIMIT $getShift, $getLimit;";
                    
                    $result = mysqli_query($link, $query) or die("Ошибка входа в систему.");
                    mysqli_close($link);

                    if ($result)
                    {
                        while($row = mysqli_fetch_assoc($result))
                        {
                            $picture = file_exists('../images/sets/'.$row['PictureName']);
                            if ($picture)
                                $picture = base64_encode($picture);
                            else
                                $picture = null; // файл не найден

                            $sets[] = array(
                                'kind' => 'music#set',
                                'id' => (int)$row['Id'],
                                'snippet' => [
                                    'title' => $row['Title'],
                                    'description' => $row['Description'],
                                    'summary' => (int)$row['Summary'],
                                    'picture' => $picture,
                                ],
                            );
                        }

                        $data = array(
                            'kind' => 'music#setListResponse',
                            'pageInfo' => [
                                'resultsPerPage' => $getLimit,
                            ],
                            'items' => $sets,
                        );
                    }
                    else
                    {
                        $data['error'] = [
                            'code' => '404',
                            'message' => 'The reqired sets are not exist.',
                            'errors' => [
                                [
                                    'message' => 'The reqired sets are not exist.',
                                    'domain' => 'creatorassethub.music',
                                    'reason' => 'setsNotFound',
                                ],
                            ],
                        ];
                    }
                }
                catch (Exception $e)
                {
                    $data['error'] = [
                        'code' => '500',
                        'message' => $e->getMessage(),
                        'errors' => [
                            [
                                'message' => $e->getMessage(),
                                'domain' => 'creatorassethub.music',
                                'reason' => 'serverError',
                            ],
                        ],
                    ];
                }
            }
            else if ($path[4] === 'open')
            {
                if (isset($_SESSION['user']['id']))
                {
                    if (isset($_GET['part']))
                    {
                        $parts = explode(',', $_GET['part']);

                        $snippet = array_search('snippet', $parts);
                        $userAssociations = array_search('userAssociations', $parts);
                        $userPlaylists = array_search('userPlaylists', $parts);

                        $getId = (int)$_GET['id'];
                        $userId = $_SESSION['user']['id'];

                        $getLimit = 25; // число композиций по умолчанию
                        if (isset($_GET['maxResults']))
                        {
                            if ($_GET['maxResults'] > 20)
                                $getLimit = $_GET['maxResults'] > 50 ? 50 : (int)$_GET['maxResults'];
                            else
                                $getLimit = 20; // минимальное число композиций
                        }
                            
                        $getShift = $_GET['page'] - 1 ?? 0;

                        $getLimitUserPlaylists = 5; // число пользовательских плейлистов
                        $getShiftUserPlaylists = 0;

                        try
                        {
                            require_once '../../service/config.php';
                            $database = 'sgdb';

                            $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');
                        
                            // (SELECT SUM(`Duration`) FROM `compositions` c LEFT JOIN `compsofeditorschoice` cs ON cs.`CompositionId`=c.`Id` WHERE cs.`SetId`=s.`Id`) AS `Duration`
                            $query = "SELECT `Title`, `Description`, `PictureName`, (SELECT COUNT(*) FROM `compsofeditorschoice` cs WHERE cs.`SetId`=s.`Id`) AS `Total`, (SELECT COUNT(*) FROM `playlists` WHERE `UserId`=$userId) AS `User Playlists` FROM `editorschoice` s WHERE Id=$getId;";
                            $query .= "SELECT c.`Id`, c.`Name`, c.`Date`, c.`Text`, c.`Auditions`, c.`Explicit`, c.`Duration`, c.`FileName`, a.`Id` AS `Album Id`, a.`Name` AS `Album Name`, a.`PictureName`, IF(c.`Id` IN (SELECT cp.`CompositionId` FROM `compositionsofplaylists` cp LEFT JOIN `playlists` p ON p.`Id`=cp.`PlaylistId` WHERE p.`UserId` = $userId AND p.`Title` = 'Понравившееся'), true, false) AS `IsLiked` FROM `compositions` c LEFT JOIN `compsofeditorschoice` cs ON cs.`CompositionId` = c.`Id` LEFT JOIN `albums` a ON a.`Id` = c.`AlbumId` WHERE cs.`SetId`=$getId LIMIT $getShift, $getLimit;";
                            $query .= "SELECT c.`Id` AS `Composition Id`, a.`Id` AS `Artist Id`, a.`Name` FROM `artists` a LEFT JOIN `compsofartists` ca ON ca.`ArtistId` = a.`Id` LEFT JOIN `compositions` c ON c.Id = ca.`CompositionId` LEFT JOIN `compsofeditorschoice` cs ON cs.`CompositionId` = c.`Id` WHERE cs.`SetId`=$getId LIMIT $getShift, $getLimit;";
                            $query .= "SELECT `Id`, `Title` FROM `playlists` WHERE `UserId`=$userId ORDER BY `lastModified` DESC LIMIT $getShiftUserPlaylists, $getLimitUserPlaylists;";

                            mysqli_multi_query($link, $query);

                            $result1 = mysqli_store_result($link); // информация о наборе

                            mysqli_next_result($link);

                            $result2 = mysqli_store_result($link); // информация о композициях

                            mysqli_next_result($link);

                            $result3 = mysqli_store_result($link); // информация об исполнителях

                            mysqli_next_result($link);

                            $result4 = mysqli_store_result($link); // информация о последних изменённых плейлистах пользователя

                            mysqli_close($link);

                            if ($result1 && $result2 && $result3 && $result4)
                            {
                                $set = mysqli_fetch_assoc($result1);

                                $comps = array();

                                $i = 0; $next = true;
                                while($composition = mysqli_fetch_assoc($result2))
                                {
                                    if ($snippet !== false)
                                    {
                                        $artists = array();

                                        for (; $i < mysqli_num_rows($result3);)
                                        {
                                            if ($next)
                                                $artist = mysqli_fetch_assoc($result3);
                                            
                                            if ($artist['Composition Id'] === $composition['Id'])
                                            {
                                                $artists[] = [
                                                    'id' => (int)$artist['Artist Id'],
                                                    'name' => $artist['Name'],
                                                ];
                                                $i++;
                                                $next = true;
                                            }
                                            else
                                            {
                                                $next = false;
                                                break;
                                            }
                                        }

                                        
                                        if ($composition['PictureName'])
                                        {
                                            $picture = file_exists('../images/albums/'.$composition['PictureName']);
                                            if ($picture)
                                                $picture = base64_encode($picture);
                                            else
                                                $picture = null; // файл не найден
                                        }
                                        else
                                            $picture = null; // нет альбома/картинки

                                        $snippet = [
                                            'name' => $composition['Name'],
                                            'artists' => $artists,
                                            'album' => [
                                                'id' => (int)$composition['Album Id'],
                                                'name' => $composition['Album Name'],
                                                'picture' => $picture,
                                            ],
                                            'date' => $composition['Date'],
                                            'text' => $composition['Text'],
                                            'auditions' => (int)$composition['Auditions'],
                                            'explicit' => (boolean)$composition['Explicit'],
                                            'duration' => (int)$composition['Duration'],
                                            'namepath' => $composition['FileName'],
                                        ];
                                    }

                                    if ($userAssociations)
                                    {
                                        $userAssociations = [
                                            'liked' => (boolean)$composition['IsLiked'],
                                        ];
                                    }

                                    $comps[] = array(
                                        'kind' => 'music#composition',
                                        'id' => (int)$composition['Id'],
                                        'snippet' => $snippet ? $snippet : null,
                                        'user associations' => $userAssociations ? $userAssociations : null,
                                    );
                                }

                                $account = array();

                                if ($userPlaylists)
                                {
                                    $playlists = array();

                                    while($row = mysqli_fetch_assoc($result4))
                                    {
                                        if ($snippet !== false)
                                        {
                                            $snippet = [
                                                'title' => $row['Title'],
                                            ];
                                        }
                                        
                                        $playlists[] = [
                                            'kind' => 'music#playlist',
                                            'id' => (int)$row['Id'],
                                            'snippet' => $snippet ? $snippet : null,
                                        ];
                                    }

                                    $userPlaylists = [
                                        'kind' => 'music#playlistListResponse',
                                        'pageInfo' => [
                                            'totalResults' => (int)$set['User Playlists'],
                                            'resultsPerPage' => $getLimitUserPlaylists,
                                        ],
                                        'items' => $playlists,
                                    ];

                                    $account['playlists'] = $userPlaylists;
                                }

                                if ($snippet !== false)
                                {
                                    $picture = file_exists('../images/sets/'.$set['PictureName']);
                                    if ($picture)
                                        $picture = base64_encode($picture);
                                    else
                                        $picture = null; // файл не найден
    
                                    $snippet = [
                                        'title' => $set['Title'],
                                        'description' => $set['Description'],
                                        'picture' => $picture,
                                    ];
                                }

                                $data = array(
                                    'kind' => 'music#set',
                                    'id' => $getId,
                                    'snippet' => $snippet ? $snippet : null,
                                    'pageInfo' => [
                                        'totalResults' => (int)$set['Total'],
                                        'resultsPerPage' => $getLimit,
                                    ],
                                    'items' => $comps,
                                    'account' => $account ? $account : null,
                                );
                            }
                            else if (!$result1)
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'The reqired set is not exist.',
                                    'errors' => [
                                        [
                                            'message' => 'The reqired set is not exist.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'setNotFound',
                                        ],
                                    ],
                                ];
                            }
                            else if (!$result2)
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'Compositions of reqired set are not found.',
                                    'errors' => [
                                        [
                                            'message' => 'Compositions of reqired set are not found.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'compositionNotFound',
                                        ],
                                    ],
                                ];
                            }
                            else if (!$result3)
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'Artists of associated compositions are not found.',
                                    'errors' => [
                                        [
                                            'message' => 'Artists of associated compositions are not found.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'artistNotFound',
                                        ],
                                    ],
                                ];
                            }
                            else
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'User playlists are not found.',
                                    'errors' => [
                                        [
                                            'message' => 'User playlists are not found.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'playlistNotFound',
                                        ],
                                    ],
                                ];
                            }
                        }
                        catch (Exception $e)
                        {
                            $data['error'] = [
                                'code' => '500',
                                'message' => $e->getMessage(),
                                'errors' => [
                                    [
                                        'message' => $e->getMessage(),
                                        'domain' => 'creatorassethub.music',
                                        'reason' => 'serverError',
                                    ],
                                ],
                            ];
                        }
                    }
                    else
                    {
                        $data['error'] = [
                            'code' => '400',
                            'message' => 'The request has no part parameter.',
                            'errors' => [
                                [
                                    'message' => 'The request has no part parameter.',
                                    'domain' => 'creatorassethub.music',
                                    'reason' => 'uriBadRequest',
                                ],
                            ],
                        ];
                    }
                }
                else
                {
                    $data['error'] = [
                        'code' => '401',
                        'message' => 'The user is unauthorized.',
                        'errors' => [
                            [
                                'message' => 'The user is unauthorized.',
                                'domain' => 'creatorassethub.music',
                                'reason' => 'userUnauthorized',
                            ],
                        ],
                    ];
                }
            }
        }
        else if ($path[3] === 'albums')
        {
            if ($path[4] === 'open')
            {
                if (isset($_SESSION['user']['id']))
                {
                    if (isset($_GET['part']))
                    {
                        $parts = explode(',', $_GET['part']);

                        $snippet = array_search('snippet', $parts);
                        $userAssociations = array_search('userAssociations', $parts);
                        $userPlaylists = array_search('userPlaylists', $parts);

                        $getId = (int)$_GET['id'];
                        $userId = $_SESSION['user']['id'];

                        $getLimit = 25; // число композиций по умолчанию
                        if (isset($_GET['maxResults']))
                        {
                            if ($_GET['maxResults'] > 20)
                                $getLimit = $_GET['maxResults'] > 50 ? 50 : (int)$_GET['maxResults'];
                            else
                                $getLimit = 20; // минимальное число композиций
                        }
                            
                        $getShift = $_GET['page'] - 1 ?? 0;

                        $getLimitUserPlaylists = 5; // число пользовательских плейлистов
                        $getShiftUserPlaylists = 0;

                        try
                        {
                            require_once '../../service/config.php';
                            $database = 'sgdb';

                            $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');
                        
                            // (SELECT SUM(`Duration`) FROM `compositions` c LEFT JOIN `compositionsofplaylists` cp ON cp.`CompositionId`=c.`Id` WHERE cp.`PlaylistId`=p.`Id`) AS `Duration`
                            $query = "SELECT `Name`, `Type`, `Date`, `PictureName`, (SELECT COUNT(*) FROM `compositions` WHERE `AlbumId`=$getId) AS `Total`, (SELECT COUNT(*) FROM `playlists` WHERE `UserId`=$userId) AS `User Playlists` FROM `albums` p WHERE Id=$getId;";
                            $query .= "SELECT c.`Id`, c.`Name`, c.`Date`, c.`Text`, c.`Auditions`, c.`Explicit`, c.`Duration`, c.`FileName`, IF(c.`Id` IN (SELECT cp.`CompositionId` FROM `compositionsofplaylists` cp LEFT JOIN `playlists` p ON p.`Id`=cp.`PlaylistId` WHERE p.`UserId` = $userId AND p.`Title` = 'Понравившееся'), true, false) AS `IsLiked` FROM `compositions` c WHERE c.`AlbumId`=$getId LIMIT $getShift, $getLimit;";
                            $query .= "SELECT c.`Id` AS `Composition Id`, a.`Id` AS `Artist Id`, a.`Name` FROM `artists` a LEFT JOIN `compsofartists` ca ON ca.`ArtistId` = a.`Id` RIGHT JOIN `compositions` c ON c.Id = ca.`CompositionId` WHERE c.`AlbumId`=$getId LIMIT $getShift, $getLimit;";
                            $query .= "SELECT `Id`, `Title` FROM `playlists` WHERE `UserId`=$userId ORDER BY `lastModified` DESC LIMIT $getShiftUserPlaylists, $getLimitUserPlaylists;";

                            mysqli_multi_query($link, $query);

                            $result1 = mysqli_store_result($link); // информация об альбоме

                            mysqli_next_result($link);

                            $result2 = mysqli_store_result($link); // информация о композициях

                            mysqli_next_result($link);

                            $result3 = mysqli_store_result($link); // информация об исполнителях

                            mysqli_next_result($link);

                            $result4 = mysqli_store_result($link); // информация о последних изменённых плейлистах пользователя

                            mysqli_close($link);

                            if ($result1 && $result2 && $result3 && $result4)
                            {
                                $album = mysqli_fetch_assoc($result1);

                                $comps = array();

                                $i = 0; $next = true;
                                while($composition = mysqli_fetch_assoc($result2))
                                {
                                    if ($snippet !== false)
                                    {
                                        $artists = array();

                                        for (; $i < mysqli_num_rows($result3);)
                                        {
                                            if ($next)
                                                $artist = mysqli_fetch_assoc($result3);
                                            
                                            if ($artist['Composition Id'] === $composition['Id'])
                                            {
                                                $artistId = (int)$artist['Artist Id'];
                                                $artistName = $artist['Name'];
                                                $artists[] = [
                                                    'id' => ($artistId === 0) ? $snippet['artists'][0]['id'] : $artistId,
                                                    'name' => ($artistName === null) ? $snippet['artists'][0]['name'] : $artistName,
                                                ];
                                                $i++;
                                                $next = true;
                                            }
                                            else
                                            {
                                                $next = false;
                                                break;
                                            }
                                        }

                                        $snippet = [
                                            'name' => $composition['Name'],
                                            'artists' => $artists,
                                            'text' => $composition['Text'],
                                            'auditions' => (int)$composition['Auditions'],
                                            'explicit' => (boolean)$composition['Explicit'],
                                            'duration' => (int)$composition['Duration'],
                                            'namepath' => $composition['FileName'],
                                        ];
                                    }

                                    if ($userAssociations)
                                    {
                                        $userAssociations = [
                                            'liked' => (boolean)$composition['IsLiked'],
                                        ];
                                    }
                                    $comps[] = array(
                                        'kind' => 'music#composition',
                                        'id' => (int)$composition['Id'],
                                        'snippet' => $snippet ? $snippet : null,
                                        'user associations' => $userAssociations ? $userAssociations : null,
                                    );
                                }

                                $account = array();

                                if ($userPlaylists)
                                {
                                    $playlists = array();

                                    while($row = mysqli_fetch_assoc($result4))
                                    {
                                        if ($snippet !== false)
                                        {
                                            $snippet = [
                                                'title' => $row['Title'],
                                            ];
                                        }
                                        $playlists[] = [
                                            'kind' => 'music#playlist',
                                            'id' => (int)$row['Id'],
                                            'snippet' => $snippet ? $snippet : null,
                                        ];
                                    }

                                    $userPlaylists = [
                                        'kind' => 'music#playlistListResponse',
                                        'pageInfo' => [
                                            'totalResults' => (int)$album['User Playlists'],
                                            'resultsPerPage' => $getLimitUserPlaylists,
                                        ],
                                        'items' => $playlists,
                                    ];

                                    $account['playlists'] = $userPlaylists;
                                }
                                if ($snippet !== false)
                                {
                                    $picture = file_exists('../images/albums/'.$album['PictureName']);
                                    if ($picture)
                                        $picture = base64_encode($picture);
                                    else
                                        $picture = null; // файл не найден
    
                                    if ($album['Date'])
                                        $description = $album['Type'].', '.$album['Date'];
                                    else
                                        $description = $album['Type'];

                                    $snippet = [
                                        'title' => $album['Name'],
                                        'description' => $description,
                                        'picture' => $picture,
                                    ];
                                }
                                $data = array(
                                    'kind' => 'music#album',
                                    'id' => $getId,
                                    'snippet' => $snippet ? $snippet : null,
                                    'pageInfo' => [
                                        'totalResults' => (int)$album['Total'],
                                        'resultsPerPage' => $getLimit,
                                    ],
                                    'items' => $comps,
                                    'account' => $account ? $account : null,
                                );
                            }
                            else if (!$result1)
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'The reqired playlist is not exist.',
                                    'errors' => [
                                        [
                                            'message' => 'The reqired playlist is not exist.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'playlistNotFound',
                                        ],
                                    ],
                                ];
                            }
                            else if (!$result2)
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'Compositions of reqired playlist are not found.',
                                    'errors' => [
                                        [
                                            'message' => 'Compositions of reqired playlist are not found.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'compositionNotFound',
                                        ],
                                    ],
                                ];
                            }
                            else if (!$result3)
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'Artists of associated compositions are not found.',
                                    'errors' => [
                                        [
                                            'message' => 'Artists of associated compositions are not found.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'artistNotFound',
                                        ],
                                    ],
                                ];
                            }
                            else
                            {
                                $data['error'] = [
                                    'code' => '404',
                                    'message' => 'User playlists are not found.',
                                    'errors' => [
                                        [
                                            'message' => 'User playlists are not found.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'playlistNotFound',
                                        ],
                                    ],
                                ];
                            }
                        }
                        catch (Exception $e)
                        {
                            $data['error'] = [
                                'code' => '500',
                                'message' => $e->getMessage(),
                                'errors' => [
                                    [
                                        'message' => $e->getMessage(),
                                        'domain' => 'creatorassethub.music',
                                        'reason' => 'serverError',
                                    ],
                                ],
                            ];
                        }
                    }
                    else
                    {
                        $data['error'] = [
                            'code' => '400',
                            'message' => 'The request has no part parameter.',
                            'errors' => [
                                [
                                    'message' => 'The request has no part parameter.',
                                    'domain' => 'creatorassethub.music',
                                    'reason' => 'uriBadRequest',
                                ],
                            ],
                        ];
                    }
                }
                else
                {
                    $data['error'] = [
                        'code' => '401',
                        'message' => 'The user is unauthorized.',
                        'errors' => [
                            [
                                'message' => 'The user is unauthorized.',
                                'domain' => 'creatorassethub.music',
                                'reason' => 'userUnauthorized',
                            ],
                        ],
                    ];
                }
            }
        }
        else if ($path[3] === 'composition')
        {
            if ($path[4] === 'play')
            {
                $id = $_GET['id'];

                try
                {
                    require_once '../../service/config.php';
                    $database = 'sgdb';

                    $link = mysqli_connect($host, $user, $password, $database) or die('Не удалось загрузить данные.');

                    $query = "SELECT `FileName` FROM `compositions` WHERE `Id` = $id";
                    $result = mysqli_query($link, $query) or die("Ошибка ".mysqli_error($link));
                    mysqli_close($link);

                    if ($result)
                    {
                        $row = mysqli_fetch_assoc($result);
                        
                        $filepath = '../materials/music/'.$row['FileName'];
                        // $filepath = '../materials/Аутро Ильи Exile.mp4';

                        if (!file_exists($filepath))
                        {
                            header("HTTP/1.1 404 Not Found");
                            return;
                        }

                        //на всякий случай очищаю буфер вывода
                        if (ob_get_level())
                        {
                            ob_end_clean();
                        }
                        //header из которого получу имя файла
                        header('Content-Disposition: attachment; filename=' . basename($filepath));

                        //а эти headers нужны в первую очередь для браузера, оставил на всякий случай
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Transfer-Encoding: binary');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($filepath));
                        //отдаю файл
                        readfile($filepath);
                        exit;
                    }
                    else
                    {
                        $data['error'] = [
                            'code' => '404',
                            'message' => 'The composition has not found.',
                            'errors' => [
                                [
                                    'message' => 'The composition has not found.',
                                    'domain' => 'creatorassethub.music',
                                    'reason' => 'compositionNotFound',
                                ],
                            ],
                        ];
                    }
                }
                catch (Exception $e)
                {
                    $data['error'] = [
                        'code' => '500',
                        'message' => $e->getMessage(),
                        'errors' => [
                            [
                                'message' => $e->getMessage(),
                                'domain' => 'creatorassethub.music',
                                'reason' => 'serverError',
                            ],
                        ],
                    ];
                }
            }
        }
        else if ($path[3] === 'genres')
        {
            if (!isset($path[4]))
            {
                try
                {
                    require_once '../../service/config.php';
                    $database = 'sgdb';

                    $link = mysqli_connect($host, $user, $password, $database) or die('Не удалось загрузить данные.');

                    $query = "SELECT * FROM `genres`;";
                    $result = mysqli_query($link, $query) or die("Ошибка ".mysqli_error($link));
                    mysqli_close($link);

                    if ($result)
                    {
                        while($genre = mysqli_fetch_assoc($result))
                        {
                            $genres[] = [
                                'id' => (int)$genre['Id'],
                                'name' => $genre['Name'],
                            ];
                        }

                        $data = array(
                            'kind' => 'music#genreListResponse',
                            'items' => $genres,
                        );
                    }
                    else
                    {
                        $data['error'] = [
                            'code' => '404',
                            'message' => 'Genres were not found.',
                            'errors' => [
                                [
                                    'message' => 'Genres were not found.',
                                    'domain' => 'creatorassethub.music',
                                    'reason' => 'genreNotFound',
                                ],
                            ],
                        ];
                    }
                }
                catch (Exception $e)
                {
                    $data['error'] = [
                        'code' => '500',
                        'message' => $e->getMessage(),
                        'errors' => [
                            [
                                'message' => $e->getMessage(),
                                'domain' => 'creatorassethub.music',
                                'reason' => 'serverError',
                            ],
                        ],
                    ];
                }
            }
            else if ($path[4] === 'open')
            {
                if (isset($_SESSION['user']['id']))
                {
                    $parts = explode(',', $_GET['part']);

                    $snippet = array_search('snippet', $parts);
                    $userAssociations = array_search('userAssociations', $parts);
                    $userPlaylists = array_search('userPlaylists', $parts);

                    $getId = (int)$_GET['id'];
                    $userId = $_SESSION['user']['id'];

                    $getLimit = 20; // число плейлистов по умолчанию
                    if (isset($_GET['maxResults']))
                    {
                        if ($_GET['maxResults'] > 10)
                            $getLimit = $_GET['maxResults'] > 30 ? 30 : (int)$_GET['maxResults'];
                        else
                            $getLimit = 10; // минимальное число плейлистов
                    }
                    
                    $getShift = $_GET['page'] - 1 ?? 0;

                    $getLimitUserPlaylists = 5; // число пользовательских плейлистов
                    $getShiftUserPlaylists = 0;

                    try
                    {
                        require_once '../../service/config.php';
                        $database = 'sgdb';

                        $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');

                        $query = "SELECT `Id`, `Name`, (SELECT COUNT(*) FROM `compositions` c LEFT JOIN `compsofgenres` cg ON cg.`CompositionId` = c.`Id` WHERE cg.`GenreId` = $getId) AS `Total`, (SELECT COUNT(*) FROM `playlists` WHERE `UserId`=$userId) AS `User Playlists` FROM `genres` p WHERE Id = $getId;";
                        $query .= "SELECT c.`Id`, c.`Name`, c.`Date`, c.`Text`, c.`Auditions`, c.`Explicit`, c.`Duration`, c.`FileName`, a.`Id` AS `Album Id`, a.`Name` AS `Album Name`, a.`PictureName`, IF(c.`Id` IN (SELECT cp.`CompositionId` FROM `compositionsofplaylists` cp LEFT JOIN `playlists` p ON p.`Id`=cp.`PlaylistId` WHERE p.`UserId` = $userId AND p.`Title` = 'Понравившееся'), true, false) AS `IsLiked` FROM `compositions` c LEFT JOIN `compsofgenres` cg ON cg.`CompositionId` = c.`Id` LEFT JOIN `albums` a ON a.`Id` = c.`AlbumId` WHERE cg.`GenreId` = $getId LIMIT $getShift, $getLimit;";
                        $query .= "SELECT c.`Id` AS `Composition Id`, a.`Id` AS `Artist Id`, a.`Name` FROM `artists` a LEFT JOIN `compsofartists` ca ON ca.`ArtistId` = a.`Id` RIGHT JOIN `compositions` c ON c.Id = ca.`CompositionId` LEFT JOIN `compsofgenres` cg ON cg.`CompositionId` = c.`Id` WHERE cg.`GenreId` = $getId LIMIT $getShift, $getLimit;";
                        $query .= "SELECT `Id`, `Title` FROM `playlists` WHERE `UserId`=$userId ORDER BY `lastModified` DESC LIMIT $getShiftUserPlaylists, $getLimitUserPlaylists;";
                        
                        mysqli_multi_query($link, $query);

                        $result1 = mysqli_store_result($link); // информация о жанре

                        mysqli_next_result($link);

                        $result2 = mysqli_store_result($link); // информация о композициях

                        mysqli_next_result($link);

                        $result3 = mysqli_store_result($link); // информация об исполнителях

                        mysqli_next_result($link);

                        $result4 = mysqli_store_result($link); // информация о последних изменённых плейлистах пользователя

                        mysqli_close($link);

                        if ($result1 && $result2 && $result3 && $result4)
                        {
                            $playlist = mysqli_fetch_assoc($result1);

                            $comps = array();

                            $i = 0; $next = true;
                            while($composition = mysqli_fetch_assoc($result2))
                            {
                                if ($snippet !== false)
                                {
                                    $artists = array();

                                    for (; $i < mysqli_num_rows($result3);)
                                    {
                                        if ($next)
                                            $artist = mysqli_fetch_assoc($result3);
                                        
                                        if ($artist['Composition Id'] === $composition['Id'])
                                        {
                                            $artists[] = [
                                                'id' => (int)$artist['Artist Id'],
                                                'name' => $artist['Name'],
                                            ];
                                            $i++;
                                            $next = true;
                                        }
                                        else
                                        {
                                            $next = false;
                                            break;
                                        }
                                    }

                                    
                                    if ($composition['PictureName'])
                                    {
                                        $picture = file_exists('../images/albums/'.$composition['PictureName']);
                                        if ($picture)
                                            $picture = base64_encode($picture);
                                        else
                                            $picture = null; // файл не найден
                                    }
                                    else
                                        $picture = null; // нет альбома/картинки


                                    $snippet = [
                                        'name' => $composition['Name'],
                                        'artists' => $artists,
                                        'album' => [
                                            'id' => (int)$composition['Album Id'],
                                            'name' => $composition['Album Name'],
                                            'picture' => $picture,
                                        ],
                                        'date' => $composition['Date'],
                                        'text' => $composition['Text'],
                                        'auditions' => (int)$composition['Auditions'],
                                        'explicit' => (boolean)$composition['Explicit'],
                                        'duration' => (int)$composition['Duration'],
                                        'namepath' => $composition['FileName'],
                                    ];
                                }

                                if ($userAssociations)
                                {
                                    $userAssociations = [
                                        'liked' => (boolean)$composition['IsLiked'],
                                    ];
                                }
                                $comps[] = array(
                                    'kind' => 'music#composition',
                                    'id' => (int)$composition['Id'],
                                    'snippet' => $snippet ? $snippet : null,
                                    'user associations' => $userAssociations ? $userAssociations : null,
                                );
                            }

                            $account = array();

                            if ($userPlaylists)
                            {
                                $playlists = array();

                                while($row = mysqli_fetch_assoc($result4))
                                {
                                    if ($snippet !== false)
                                    {
                                        $snippet = [
                                            'title' => $row['Title'],
                                        ];
                                    }
                                    $playlists[] = [
                                        'kind' => 'music#playlist',
                                        'id' => (int)$row['Id'],
                                        'snippet' => $snippet ? $snippet : null,
                                    ];
                                }

                                $userPlaylists = [
                                    'kind' => 'music#playlistListResponse',
                                    'pageInfo' => [
                                        'totalResults' => (int)$playlist['User Playlists'],
                                        'resultsPerPage' => $getLimitUserPlaylists,
                                    ],
                                    'items' => $playlists,
                                ];

                                $account['playlists'] = $userPlaylists;
                            }
                            if ($snippet !== false)
                            {
                                $snippet = [
                                    'title' => $playlist['Name'],
                                    'description' => 'Треки по жанру',
                                ];
                            }
                            $data = array(
                                'kind' => 'music#genre',
                                'id' => $getId,
                                'snippet' => $snippet ? $snippet : null,
                                'pageInfo' => [
                                    'totalResults' => (int)$playlist['Total'],
                                    'resultsPerPage' => $getLimit,
                                ],
                                'items' => $comps,
                                'account' => $account ? $account : null,
                            );
                        }
                        else if (!$result1)
                        {
                            $data['error'] = [
                                'code' => '404',
                                'message' => 'The reqired playlist is not exist.',
                                'errors' => [
                                    [
                                        'message' => 'The reqired playlist is not exist.',
                                        'domain' => 'creatorassethub.music',
                                        'reason' => 'playlistNotFound',
                                    ],
                                ],
                            ];
                        }
                        else if (!$result2)
                        {
                            $data['error'] = [
                                'code' => '404',
                                'message' => 'Compositions of reqired playlist are not found.',
                                'errors' => [
                                    [
                                        'message' => 'Compositions of reqired playlist are not found.',
                                        'domain' => 'creatorassethub.music',
                                        'reason' => 'compositionNotFound',
                                    ],
                                ],
                            ];
                        }
                        else if (!$result3)
                        {
                            $data['error'] = [
                                'code' => '404',
                                'message' => 'Artists of associated compositions are not found.',
                                'errors' => [
                                    [
                                        'message' => 'Artists of associated compositions are not found.',
                                        'domain' => 'creatorassethub.music',
                                        'reason' => 'artistNotFound',
                                    ],
                                ],
                            ];
                        }
                        else
                        {
                            $data['error'] = [
                                'code' => '404',
                                'message' => 'User playlists are not found.',
                                'errors' => [
                                    [
                                        'message' => 'User playlists are not found.',
                                        'domain' => 'creatorassethub.music',
                                        'reason' => 'playlistNotFound',
                                    ],
                                ],
                            ];
                        }
                    }
                    catch (Exception $e)
                    {
                        $data['error'] = [
                            'code' => '500',
                            'message' => $e->getMessage(),
                            'errors' => [
                                [
                                    'message' => $e->getMessage(),
                                    'domain' => 'creatorassethub.music',
                                    'reason' => 'serverError',
                                ],
                            ],
                        ];
                    }
                }
                else
                {
                    $data['error'] = [
                        'code' => '401',
                        'message' => 'The user is unauthorized.',
                        'errors' => [
                            [
                                'message' => 'The user is unauthorized.',
                                'domain' => 'creatorassethub.music',
                                'reason' => 'userUnauthorized',
                            ],
                        ],
                    ];
                }
            }
        }

        die (json_encode($data));
        
        break;
    case "DELETE":
        if (isset($_SESSION['user']['id']))
        {
            $userId = $_SESSION['user']['id'];

            if ($path[3] === 'user')
            {
                if ($path[4] === 'playlistitems')
                {
                    $getId = (int)$_GET['id'];

                    try
                    {
                        require_once '../../service/config.php';
                        $database = 'sgdb';

                        $link = mysqli_connect($host, $user, $password, $database) or die('Не удалось загрузить данные.');

                        $query = "CALL `deletefromplaylist`($userId, $getId);";

                        $result = mysqli_query($link, $query) or die("Ошибка ".mysqli_error($link));
                        mysqli_close($link);

                        if ($result)
                        {
                            $row = mysqli_fetch_assoc($result);
                            
                            if ($row['result'] === '1')
                            {
                                $data['error'] = [
                                    'code' => '403',
                                    'message' => 'You are not allowed to access for this resource.',
                                    'errors' => [
                                        [
                                            'message' => 'You are not allowed to access for this resource or this resource was not found.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'playlistItemForbidden',
                                        ],
                                    ],
                                ];
                            }
                            else if ($row['result'] === '2' || $row['result'] === '3')
                            {
                                header("HTTP/1.1 204 No Content");
                                exit();
                            }
                            else
                            {
                                $data['error'] = [
                                    'code' => '520',
                                    'message' => 'Sorry, we don\'t know about the result of operation.',
                                    'errors' => [
                                        [
                                            'message' => 'Sorry, we don\'t know about the result of operation.',
                                            'domain' => 'creatorassethub.music',
                                            'reason' => 'serverUnknownError',
                                        ],
                                    ],
                                ];
                            }
                        }
                        else
                        {
                            $data['error'] = [
                                'code' => '404',
                                'message' => 'The resource has not found.',
                                'errors' => [
                                    [
                                        'message' => 'The resource has not found.',
                                        'domain' => 'creatorassethub.music',
                                        'reason' => 'compositionNotFound',
                                    ],
                                ],
                            ];
                        }
                    }
                    catch (Exception $e)
                    {
                        $data['error'] = [
                            'code' => '500',
                            'message' => $e->getMessage(),
                            'errors' => [
                                [
                                    'message' => $e->getMessage(),
                                    'domain' => 'creatorassethub.music',
                                    'reason' => 'serverError',
                                ],
                            ],
                        ];
                    }
                }
            }
        }
        else
        {
            $data['error'] = [
                'code' => '401',
                'message' => 'The user is unauthorized.',
                'errors' => [
                    [
                        'message' => 'The user is unauthorized.',
                        'domain' => 'creatorassethub.music',
                        'reason' => 'userUnauthorized',
                    ],
                ],
            ];
        }
        
        die (json_encode($data));
        
        break;
    default:
        die (json_encode([
            'error' => [
                'code' => '405',
                'message' => "The method is not allowed.",
                'errors' => [
                    [
                        'message' => "The method is not allowed.",
                        'domain' => 'creatorassethub.music',
                        'reason' => 'methodNotAllowed',
                    ],
                ],
            ],
        ]));
        break;
}