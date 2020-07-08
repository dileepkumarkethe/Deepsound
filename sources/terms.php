<?php 

$pages = ['about', 'privacy', 'terms'];


if (empty($path['options'][1])) {
	header("Location: $site_url/404");
	exit();
}

if (!in_array($path['options'][1], $pages)) {
	header("Location: $site_url/404");
	exit();
}

$page_to_load = secure($path['options'][1]);

switch ($page_to_load) {
	case 'terms':
		$music->site_title = lang("Terms");
		break;
	case 'about':
		$music->site_title = lang("About Us");
		break;
	case 'privacy':
		$music->site_title = lang("Privacy Policy");
		break;
}
$music->page_to_load = $page_to_load;

$terms_content = htmlspecialchars_decode($db->where('type', $page_to_load)->getValue(T_TERMS, 'content'));

$music->site_description = $music->config->description;
$music->site_pagename = "terms";

$music->site_content = loadPage("terms/content", ['terms_header' => loadPage('terms/header'), 'terms_content' => $terms_content]);