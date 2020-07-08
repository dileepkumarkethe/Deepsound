<?php 
if (IS_LOGGED == false) {
	header("Location: $site_url");
	exit();
}
$music->site_title = lang('You are a pro!');
$music->site_description = $music->config->description;
$music->site_pagename = "upgraded";
$music->site_content = loadPage("go-pro/upgraded");