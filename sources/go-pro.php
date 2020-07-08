<?php 
if (IS_LOGGED == false || $music->config->go_pro != 'on') {
	header("Location: $site_url");
	exit();
}

if ($user->is_pro == 1) {
	header("Location: $site_url");
	exit();
}

$music->site_title = lang("Go Pro!");
$music->site_description = $music->config->description;
$music->site_pagename = "go-pro";
$music->site_content = loadPage("go-pro/content");