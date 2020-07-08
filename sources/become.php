<?php 
if (IS_LOGGED == false) {
	header("Location: $site_url");
	exit();
}
if ($user->artist == 1) {
	header("Location: $site_url");
	exit();
}
$music->hasRequest = ($db->where('user_id', $user->id)->getValue(T_ARTIST_R, "count(*)") > 0) ? true : false;
$music->site_title = lang("Become an artist");
$music->site_description = $music->config->description;
$music->site_pagename = "become";
$music->site_content = loadPage("become/content");