<?php 
if (IS_LOGGED == true) {
	header("Location: $site_url/404");
	exit();
}

if (empty($path['options'][1])) {
	header("Location: $site_url/404");
	exit();
}

$email_code = secure($path['options'][1]);

$getUser = $db->where('email_code', $email_code)->getOne(T_USERS);

if (empty($getUser)) {
	header("Location: $site_url/404");
	exit();
}

$music->site_title = 'Reset Password';
$music->site_description = $music->config->description;
$music->site_pagename = "forgot";
$music->site_content = loadPage("auth/reset-password", ['email_code' => $email_code]);