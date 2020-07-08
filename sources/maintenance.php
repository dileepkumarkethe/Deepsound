<?php
$music->site_title = lang("Maintenance");
$music->site_description = $music->config->description;
$music->site_pagename = "maintenance";
$music->site_content = loadPage("maintenance/content");

$content_data = [
    'site_title' => $music->site_title,
    'site_content' => $music->site_content,
    'site_header' => '',
    'site_sidebar' => '',
    'site_player' => '',
    'site_loginForm' => loadPage('auth/login'),
    'site_signupForm' => loadPage('auth/signup'),
    'theme_url' => $config['theme_url'],
    'classes' => '',
    'SIDE_AD' => GetAd('side_bar'),
    'FOOTER_AD' => ($music->site_pagename != 'login') ? GetAd('footer') : '',
    'HEADER_AD' => GetAd('header')
];

echo loadPage('container', $content_data);
exit();