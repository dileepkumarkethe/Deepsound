<?php 
if (IS_LOGGED == false) {
	header("Location: $site_url/404");
	exit();
}

$page_to_load = 'upload-album';

if ($music->config->go_pro == 'on') {
    if (($user->uploads >= ($music->config->pro_upload_limit * 1000 * 1000)) && $user->is_pro == 0) {
        $page_to_load = 'limit-reached';
    }
}

$music->site_title = lang("Upload");
$music->site_description = $music->config->description;
$music->site_pagename = "upload-song";
$music->site_content = loadPage("upload-song/$page_to_load");