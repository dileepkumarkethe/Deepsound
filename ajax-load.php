<?php
require "assets/init.php";


$_GET['path'] = ltrim($_GET['path'], '/'); 
$explode = explode('/', $_GET['path']);
if (!empty($explode)) {
	if ($explode[0] == 'user') {
		unset($explode[0]);
		$_GET['path'] = implode($explode, '/'); 
	}
}



$path = (!empty($_GET['path'])) ? getPageFromPath(urldecode($_GET['path'])) : null;

if (empty($path)) {
	$path['page'] = '404';
}
$page = "";

if (!empty($path['page'])) {
	$page = $path['page'];
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


if (in_array($page, ['purchased', 'my_playlists', 'favourites', 'recently_played']) && !IS_LOGGED) {
	header("HTTP/1.1 201 OK");
	exit();
}

$file_location = "./sources/$page.php";
if(IS_LOGGED === true) {
    if (checkUserInterest() === true) {
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
    }
    if (checkUserInterest() === false) {
        $file_location = "./sources/interest.php";
        if (file_exists($file_location)) {
            require_once $file_location;
        }
    }
}else{
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
}

$_GET['path'] = (!empty($_GET['path'])) ? secure($_GET['path']) : '404';
$content_data = [
	'site_title' => $music->site_title,
	'theme_url' => $config['theme_url'],
	'page_name' => $music->site_pagename,
	'description' => $music->site_description,
	'keyword' => '',
	'url' => getLink(urldecode($_GET['path'])),
	'classes' => '',
	'scroll' => false
];

if ($music->site_pagename == 'forgot' || $music->site_pagename == 'reset') {
	$content_data['classes'] = "full_page";
}

if ($music->site_pagename == 'single_song') {
	$content_data['classes'] = "no-player";
}

$content_data['ajax_url'] = urldecode($_GET['path']);

if ($music->site_pagename == 'single_song') {
	$content_data['classes'] = "no-player";
}
if ($music->site_pagename == 'user' && !empty($path['options'][2])) {
	$content_data['scroll'] = true;
}
?>
<input type="hidden" value="<?php echo htmlspecialchars(json_encode($content_data))?>" id="json-data">
<?php
echo $music->site_content;
exit();