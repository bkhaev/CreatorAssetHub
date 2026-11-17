<?php

require_once 'config.php';
require_once '../service/bflib.php';

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Access-Control-Allow-Headers: *");

// session_name('bf-account');
session_id('bf-account');
session_start();

mb_internal_encoding('utf-8');

$output = null;
$filesFolder = '../files';

$request_pars = strpos($_SERVER['REQUEST_URI'], '?');
$request_path = explode('/', substr($_SERVER['REQUEST_URI'], 0, ($request_pars === false) ? null : $request_pars));

$request_method = $_SERVER['REQUEST_METHOD'];
switch ($request_method)
{
    case 'GET':
        if ($request_path[2] === 'user')
        {
            if ($request_path[3] === 'list')
            {
                require_once('get/usersList.php');
            }
            else if ($request_path[3] === 'logout')
            {
                if (count($_GET))
                {
                    $output['error'] = array(
                        'code' => '400',
                        'message' => 'The invalid Request URI.',
                        'errors' => [[
                            'message' => 'The unknown parameter has been provided.',
                            'domain' => 'creatorassethub.parameter',
                            'reason' => 'unknownParameter',
                            'location' => 'parameters',
                            'locationPart' => 'other',
                        ]],
                    );

                    header("HTTP/1.1 400 Bad Request");
                }
                else if (isset($_SESSION['user']['id']))
                {
                    // session_start();
                    // session_unset();
                    // session_destroy();
                    // session_write_close();
                    // setcookie(session_name(),'',0,'/');
                    // session_regenerate_id(true);
                    unset($_SESSION['user']['id']);

                    header("HTTP/1.1 200 OK");
                    $output = [];
                }
                else
                {
                    header("HTTP/1.1 208 Already Reported");
                    $output = [];
                }
            }
        }
        else if ($request_path[2] === 'phrases')
        {
            if ($request_path[3] === 'search')
            {
                require_once('get/phrasesList.php');
            }
        }
        else if ($request_path[2] === 'materials')
        {
            if ($request_path[3] === 'search')
            {
                require_once('get/materialsList.php');
            }
        }
        if ($request_path[2] === 'playlists')
        {
            require_once('get/playlistsList.php');
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'));

        if ($request_path[2] === 'user')
        {
            if ($request_path[3] === 'login')
            {
                require_once('post/login.php');
            }
            else if ($request_path[3] === 'signup')
            {
                require_once('post/signup.php');
            }
        }
        else if ($request_path[2] === 'phrases')
        {
            if ($request_path[3] === 'add')
            {
                require_once('post/phrasesAdd.php');
            }
        }
        else if ($request_path[2] === 'playlistItems')
        {
            if (isset($data->snippet->playlistTitle))
            {
                $reservedPlaylistsTitleList = ['Понравившиеся фразы', 'Понравившиеся изображения', 'Понравившиеся видео', 'Понравившаяся музыка', 'Понравившиеся звуки'];

                if (isset($data->snippet->playlistId))
                {
                    $output['error'] = array(
                        'code' => '400',
                        'message' => 'The invalid Request Body.',
                        'errors' => [[
                            'message' => 'playlistId',
                            'domain' => 'creatorassethub.part',
                            'reason' => 'unknownPart',
                            'location' => 'data',
                            'locationPart' => 'snippet',
                        ]],
                    );
    
                    header("HTTP/1.1 400 Bad Request");
                }
                else if (array_search($data->snippet->playlistTitle, $reservedPlaylistsTitleList) === false)
                {
                    $output['error'] = [
                        'code' => '400',
                        'message' => 'playlistTitle',
                        'errors' => [[
                            'message' => 'playlistTitle',
                            'domain' => 'creatorassethub.playlists',
                            'reason' => 'wrongValue',
                            'location' => 'data',
                        ]],
                    ];
                
                    header("HTTP/1.1 400 Bad Request");
                }
                else if (isset($_SESSION['user']['id']))
                {
                    $link = mysqli_connect($host, $user, $password, $database) or die('Потеряно соединение с сервером.');

                    $query = "SELECT `id` FROM `user_playlists` WHERE `user_id` = ? AND `title` = ?;";

                    $rows = libSqlQuery($link, $query, $_SESSION['user']['id'], $data->snippet->playlistTitle);
                    
                    if (is_array($rows))
                    {
                        if ($rows && $rows[0]['user_playlists_id'])
                            $data->snippet->playlistId = $rows[0]['user_playlists_id'];
                        else
                        {
                            $query = '';
                            if ($data->snippet->playlistTitle === 'Понравившиеся фразы')
                                $query = "CALL `add-edit-playlist`(null,?,'Понравившиеся фразы','В этом плейлисте собраны все ваши понравившиеся фразы',0,'phrase');";
                            else if ($data->snippet->playlistTitle === 'Понравившиеся изображения')
                                $query = "CALL `add-edit-playlist`(null,?,'Понравившиеся изображения','В этом плейлисте собраны все ваши понравившиеся изображения',0,'image');";
                            else if ($data->snippet->playlistTitle === 'Понравившиеся видео')
                                $query = "CALL `add-edit-playlist`(null,?,'Понравившиеся видео','В этом плейлисте собраны все ваши понравившиеся видео',0,'video');";
                            else if ($data->snippet->playlistTitle === 'Понравившаяся музыка')
                                $query = "CALL `add-edit-playlist`(null,?,'Понравившаяся музыка','В этом плейлисте собраны все ваши понравившаяся музыка',0,'music');";
                            else if ($data->snippet->playlistTitle === 'Понравившиеся звуки')
                                $query = "CALL `add-edit-playlist`(null,?,'Понравившиеся звуки','В этом плейлисте собраны все ваши понравившиеся звуки',0,'sound');";

                            $rows = libSqlQuery($link, $query, $_SESSION['user']['id']);
                            
                            if (is_array($rows) && $rows)
                            {
                                if ($rows[0][0] === 2)
                                    $data->snippet->playlistId = $rows[0][1];
                                else
                                {
                                    $output['error'] = [
                                        'code' => '500',
                                        'message' => "Cannot create new playlist.",
                                        'errors' => [[
                                            'message' => 'Cannot create new playlist.',
                                            'domain' => 'creatorassethub.playlists',
                                            'reason' => 'serverError',
                                        ]],
                                    ];
                        
                                    header("HTTP/1.1 500 Internal Server Error");
                                }
                            }
                            else
                            {
                                $output['error'] = [
                                    'code' => '520',
                                    'message' => "Cannot create new playlist.",
                                    'errors' => [[
                                        'message' => 'Cannot create new playlist.',
                                        'domain' => 'creatorassethub.playlists',
                                        'reason' => 'unknownError',
                                    ]],
                                ];
                    
                                header("HTTP/1.1 520 Unknown Error");
                            }
                        }
                    }
                    else
                    {
                        $output['error'] = [
                            'code' => '520',
                            'message' => "Cannot find requarid playlist.",
                            'errors' => [[
                                'message' => 'Cannot find requarid playlist.',
                                'domain' => 'creatorassethub.playlists',
                                'reason' => 'unknownError',
                            ]],
                        ];
            
                        header("HTTP/1.1 520 Unknown Error");
                    }

                    mysqli_close($link);
                }
                
                unset($data->snippet->playlistTitle);
            }

            if (!isset($output['error']))
                require_once('post/resourceSaveInPlaylist.php');
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'));
        
        if ($request_path[2] === 'playlistItems')
        {
            require_once('put/resourceEditAtPlaylist.php');
        }
        else if ($request_path[2] === 'phrases')
        {
            if ($request_path[3] === 'edit')
            {
                require_once('put/phrasesEdit.php');
            }
        }
        break;
    case 'DELETE':
        if ($request_path[2] === 'playlistItems')
        {
            require_once('delete/resourceDeleteFromPlaylist.php');
        }
        break;
    default:
        $output['error'] = array(
            'code' => '405',
            'message' => "The method is not allowed.",
            'errors' => [[
                'message' => "The method is not allowed.",
                'domain' => 'creatorassethub.method',
                'reason' => 'methodNotAllowed',
            ]],
        );

        header("HTTP/1.1 405 Method Not Allowed");
}



if ($output)
{
    header('Content-Type: application/json; charset=utf-8');
    die (json_encode($output));
}
else if (!is_array($output))
{
    header("HTTP/1.1 404 Not Found");
    header('Content-Type: application/json; charset=utf-8');
    die (json_encode([
        'error' => [
            'code' => '404',
            'message' => "The required resource not found.",
            'errors' => [[
                'message' => "The required resource not found.",
                'domain' => 'creatorassethub.resource',
                'reason' => 'resourceNotFound',
            ]],
        ],
    ]));
}