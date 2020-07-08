<?php 
if (IS_LOGGED == true) {
	header("Location: $site_url/feed");
	exit();
}

$result_artists = $db->rawQuery("SELECT * FROM `".T_USERS."` WHERE `artist` = 1 ORDER BY rand() DESC LIMIT 14;");
$artists_html = '';
foreach ($result_artists as $artists) {
    $pagedata = [
        'ARTIST_DATA' => userData( $artists->id )
    ];
    $artists_html .= loadPage("user/artist-item", $pagedata);
}
$music->artists_html = $artists_html;

$music->site_title = 'Home';
$music->site_description = $music->config->description;
$music->site_pagename = "home";
$music->site_content = loadPage("home/content");