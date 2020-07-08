<?php 
if (IS_LOGGED == true) {
	header("Location: $site_url/404");
	exit();
}
$music->site_title = 'Forgot Password';
$music->site_description = $music->config->description;
$music->site_pagename = "forgot";
$music->site_content = loadPage("auth/forgot-password");