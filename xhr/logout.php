<?php
if (!empty($_SESSION['user_id'])) {
	$db->where('session_id', secure($_SESSION['user_id']));
	$db->delete(T_SESSIONS);

    $db->where('session_id', secure($_SESSION['user_id']));
    $db->delete(T_APP_SESSIONS);

}

if (isset($_COOKIE['user_id'])) {
	$db->where('session_id', secure($_COOKIE['user_id']));
	$db->delete(T_SESSIONS);

    $db->where('session_id', secure($_COOKIE['user_id']));
    $db->delete(T_APP_SESSIONS);

    unset($_COOKIE['user_id']);
    setcookie('user_id', null, -1);
} 

unset($_SESSION['user_id']);

$data = ['status' => 200, 'header' => loadPage('header/content', ['site_search_bar' => loadPage('header/search-bar')])];