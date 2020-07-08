<?php
error_reporting(0);
@ini_set('max_execution_time', 0);

require 'config.php';
require 'assets/import/DB/vendor/autoload.php';
require 'assets/import/s3/aws-autoloader.php';
require 'assets/import/ftp/vendor/autoload.php';

$music     = ToObject(array());

// Connect to MySQL Server
$mysqli     = new mysqli($sql_db_host, $sql_db_user, $sql_db_pass, $sql_db_name);

$ServerErrors = array();
if (mysqli_connect_errno()) {
    $ServerErrors[] = "Failed to connect to MySQL: " . mysqli_connect_error();
}
if (isset($ServerErrors) && !empty($ServerErrors)) {
    foreach ($ServerErrors as $Error) {
        echo "<h3>" . $Error . "</h3>";
    }
    die();
}
$sqlConnect = $mysqli;




$query = $mysqli->query("SET NAMES utf8");

//for emoji icons encoding
$mysqli->set_charset('utf8mb4');
$mysqli->query("SET collation_connection = utf8mb4_unicode_ci");

// Connecting to DB after verfication
$db = new MysqliDb($mysqli);

$baned_ips = GetBanned('user');

if (in_array($_SERVER["REMOTE_ADDR"], $baned_ips)) {
    exit();
}

$http_header = 'http://';
if (!empty($_SERVER['HTTPS'])) {
    $http_header = 'https://';
}

$music->disallowed_usernames = array(
    'feed',
    'discover',
    'new_music',
    'top_music',
    'spotlight',
    'genres',
    'explore-genres',
    'playlists',
    'store',
    'purchased',
    'recently_played',
    'my_playlists',
    'favourites',
    'terms',
    'contact',
    'upload-song',
    'upload-single',
    'upload-album',
    'messages',
    'search',
    'dashboard',
    'settings'
);
$music->site_pages           = array('home');
$music->actual_link          = $http_header . $_SERVER['HTTP_HOST'] . urlencode($_SERVER['REQUEST_URI']);

$config                   = getConfig();
$music->loggedin          = false;
$config['theme_url']      = $site_url . '/themes/' . $config['theme'];
$config['site_url']       = $site_url;
$config['ajax_url']       = $site_url . '/endpoints';
$config['script_version'] = $music->script_version;
$site = parse_url($site_url);
if (empty($site['host'])) {
    $config['hostname'] = $site['scheme'] . '://' .  $site['host'];
}

$music->config               = ToObject($config);
$langs                       = db_langs();
$music->langs                = $langs;

$music->script_version = $music->config->version;

if (isLogged() == true) {
    $music->loggedin   = true;
    if (isset($_POST['access_token']) && !empty($_POST['access_token'])) {
        $music->user_session  = getUserFromSessionID($_POST['access_token'], 'mobile');
    }else{
        $session_id        = (!empty($_SESSION['user_id'])) ? $_SESSION['user_id'] : $_COOKIE['user_id'];
        $music->user_session  = getUserFromSessionID($session_id);
    }

    if (empty($music->user_session) && !empty($_POST['access_token'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 400,"error" => 'Invalid access token']);
        exit();
    }

    $user = $music->user  = userData($music->user_session);
    $user->wallet      = number_format($user->wallet, 2);
    
    if (!empty($user->language) && in_array($user->language, $langs)) {
        $_SESSION['lang'] = $user->language;
    }

    if ($user->id < 0 || empty($user->id) || !is_numeric($user->id) || isUserActive($user->id) === false) {
        header("Location: " . getLink('logout'));
    }
}

if (isset($_GET['lang']) AND !empty($_GET['lang'])) {
    $lang_name = secure(strtolower($_GET['lang']));

    if (in_array($lang_name, $langs)) {
        $_SESSION['lang'] = $lang_name;
        if ($music->loggedin == true) {
            $db->where('id', $user->id)->update(T_USERS, array('language' => $lang_name));
        }
    }
}

define('IS_LOGGED', $music->loggedin);


if (empty($_SESSION['lang'])) {
  $_SESSION['lang'] = $music->config->language;
}

if (isset($_SESSION['user_id'])) {
    if (empty($_COOKIE['user_id'])) {
        setcookie("user_id", $_SESSION['user_id'], time() + (10 * 365 * 24 * 60 * 60), "/");
    }
}

$music->min_song_price = 0;//(float)$db->rawQuery('SELECT MIN('.T_SONG_PRICE.'.price) AS MinPrice FROM '.T_SONG_PRICE.' WHERE '.T_SONG_PRICE.'.price > 0')[0]->MinPrice;
$music->max_song_price = (float)$db->rawQuery('SELECT MAX('.T_SONG_PRICE.'.price) AS MaxPrice FROM '.T_SONG_PRICE)[0]->MaxPrice;
$music->song_prices = [];
$prices = $db->rawQuery('SELECT '.T_SONG_PRICE.'.price FROM '.T_SONG_PRICE);
$music->song_prices[] = '0.00';
foreach ($prices as $key => $value){
    $music->song_prices[] = $value->price;
}

$music->language      = $_SESSION['lang'];
$music->language_type = 'ltr';

// Add rtl languages here.
$rtl_langs           = array(
    'arabic'
);

// checking if corrent language is rtl.
foreach ($rtl_langs as $lang) {
    if ($music->language == strtolower($lang)) {
        $music->language_type = 'rtl';
    }
}


// Include Language File
$lang_file = 'assets/langs/' . $music->language . '.php';
if (file_exists($lang_file)) {
    require($lang_file);
}



$lang_array = get_langs($music->language);
if (empty($lang_array)) {
    $lang_array = get_langs();
}

$lang       = ToObject($lang_array);

$music->user_default_avatar = 'upload/photos/d-avatar.jpg';
$music->categories  = ToObject(getCategories());

$music->update_cache                 = '';
if (!empty($music->config->last_update)) {
    $update_cache = time() - 21600;
    if ($update_cache < $music->config->last_update) {
        $music->update_cache = '?' . sha1(time());
    }
}

$music->mode_link = 'night';
$music->mode_text = lang('Night mode');

// night mode
if (empty($_COOKIE['mode'])) {
    setcookie("mode", $music->config->night_mode, time() + (10 * 365 * 24 * 60 * 60), '/');
    $_COOKIE['mode'] = $music->config->night_mode;
    $music->mode_link = 'day';
    $music->mode_text = lang('Night mode');
} else {
    if ($_COOKIE['mode'] == 'day') {
        $music->mode_link = 'night';
        $music->mode_text = lang('Night mode');
    }
    if ($_COOKIE['mode'] == 'night') {
        $music->mode_link = 'day';
        $music->mode_text = lang('Day mode');
    }
}

if (!empty($_GET['mode'])) {
    if ($_GET['mode'] == 'day') {
        setcookie("mode", 'day', time() + (10 * 365 * 24 * 60 * 60), '/');
        $_COOKIE['mode'] = 'day';
        $music->mode_link = 'night';
        $music->mode_text = lang('Night mode');
    } else if ($_GET['mode'] == 'night') {
        setcookie("mode", 'night', time() + (10 * 365 * 24 * 60 * 60), '/');
        $_COOKIE['mode'] = 'night';
        $music->mode_link = 'day';
        $music->mode_text = lang('Day mode');
    }
}

if (empty($_SESSION['uploads'])) {

    $_SESSION['uploads'] = array();

    if (empty($_SESSION['uploads']['music'])) {
        $_SESSION['uploads']['music'] = array();
    }

    if (empty($_SESSION['uploads']['images'])) {
        $_SESSION['uploads']['images'] = array();
    }
}

include_once('assets/includes/onesignal.php');