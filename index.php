<?php
require "assets/init.php";

$path = (!empty($_GET['path'])) ? getPageFromPath($_GET['path']) : null;

$page = "";
$music->path = $path;

if (!empty($path['page'])) {
	$page = $path['page'];

    if( $page == 'admin-cp' ){
        if (IS_LOGGED == false) {
            header("Location: $site_url");
            exit();
        }
        if (IsAdmin() == false) {
            header("Location: $site_url");
            exit();
        }
        require 'admin-panel/autoload.php';
        exit();
    }

    if ($page == 'endpoint' && !empty($path['options'])) {
        if( !isset($_REQUEST['server_key']) ){
            header('Content-Type: application/json');
            echo json_encode(['status' => 400,"error" => 'Missing server key']);
            exit();
        }else{
            if( $_REQUEST['server_key'] !== $music->config->apps_api_key ) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 400, "error" => 'Invalid server key']);
                exit();
            }
        }

        require_once "./endpoint/functions.php";
        $data = [];
        $file_location = "./endpoint/v1/{$path['options'][1]}.php";
        $api = (!empty($path['options'][1])) ? $path['options'][1] : '';
        $option = (!empty($path['options'][2])) ? $path['options'][2] : '';
        $whitelist = ['login','forgot-password','reset-password','signup','contact','options','social-login'];

        $is_whitelist = false;
        if( in_array($api, $whitelist) ) $is_whitelist = true;
        if( in_array($option, $whitelist) ) $is_whitelist = true;

        if( $is_whitelist === false ) {
            if( !isset($_REQUEST['access_token']) ){
                header('Content-Type: application/json');
                echo json_encode(['status' => 400,"error" => 'Invalid access token']);
                exit();
            }
            if (empty($_REQUEST['access_token'])) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 400,"error" => 'Invalid access token']);
                exit();
            }
            if (isLogged() === false) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 400,"error" => 'Invalid access token']);
                exit();
            }
        }

        if (file_exists($file_location)) {
            require_once $file_location;
            if (!empty($errors)) {
                $data = array(
                    'status' => 400,
                    'error' => end($errors)
                );
            }
        } else {
            $data = array(
                'status' => 400,
                'error' => "Endpoint not found"
            );
        }

        if(empty($data)){
            $data = array(
                'status' => 400,
                'error' => "Error while processing your request"
            );
        }
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

	if ($page == 'endpoints' && !empty($path['options'])) {
		$data = [];
		$file_location = "./xhr/{$path['options'][1]}.php";
		$option = (!empty($path['options'][2])) ? $path['options'][2] : '';
		if (empty($_REQUEST['hash_id'])) {
			header('Content-Type: application/json');
			echo json_encode(["error" => 'Invalid hash key']);
			exit();
		} else if ($_SESSION['hash'] != $_REQUEST['hash_id']) {
			header('Content-Type: application/json');
			echo json_encode(["error" => 'Invalid hash key']);
			exit();
		}
		if (file_exists($file_location)) {
			require_once $file_location;
			if (!empty($errors)) {
				$data = array(
			        'status' => 400,
			        'errors' => $errors
			    );
			}
		} else {
			$data = array(
		        'status' => 400,
		        'message' => "Endpoint not found"
		    );
		}
		header('Content-Type: application/json');
		echo json_encode($data);
		exit();
	}
}


$file_location = "./sources/$page.php";
if (file_exists($file_location)) {
	require_once $file_location;
} else if (UsernameExits($page)) {
   require_once "./sources/user.php";
} else if (empty($page)) {
	require_once "./sources/home.php";
} else if (empty($page)) {
	require_once "./sources/not-found.php";
} 

if (empty($music->site_content)) {
	require_once "./sources/not-found.php";
}



$content_data = [
	'site_title' => $music->site_title,
    'site_desc' => $music->site_description,
	'site_content' => $music->site_content,
	'site_header' => '',
	'site_sidebar' => '',
	'site_player' => '',
	'site_loginForm' => loadPage('auth/login'),
	'site_signupForm' => loadPage('auth/signup'),
	'theme_url' => $config['theme_url'],
	'classes' => '',
    'FOOTER_AD' => ($music->site_pagename != 'login') ? GetAd('footer') : '',
];

if ($music->site_pagename == 'forgot' || $music->site_pagename == 'reset') {
	$content_data['classes'] = "full_page";
}

if ($music->site_pagename == 'single_song') {
	$content_data['classes'] = "no-player";
}

if ($music->site_pagename != 'home') {
    $trend_search = $db->orderBy('hits', 'DESC')->get(T_SEARCHES, 10, array('id','keyword'));
	$header_data = ['site_search_bar' => loadPage('header/search-bar',$trend_search)];
	$content_data['site_header'] = (IS_LOGGED) ? loadPage('header/logged_head', $header_data) : loadPage('header/content', $header_data);
}

if ($music->site_pagename != 'forgot' && $music->site_pagename != 'reset' && $music->site_pagename != 'home') {
	$content_data['site_sidebar'] = loadPage('sidebar/content');
	$content_data['site_player'] = loadPage('player/content');
}


$maintenance_mode = false;
if ( $music->config->maintenance_mode == 'on' ) {
    if ( IS_LOGGED === false ) {
        $maintenance_mode = true;
        //http://localhost/quickdatescript.com/?access=admin
        if(isset($_GET['access']) && $_GET['access'] == 'admin'){
            $maintenance_mode = false;
        }
    } else {
        if ($music->user->admin === "0") {
            $maintenance_mode = true;
        }

    }

    if( $maintenance_mode === true ){
        $file_location = "./sources/maintenance.php";
        if (file_exists($file_location)) {
            require_once $file_location;
        }

    }
}

echo loadPage('container', $content_data);
exit();