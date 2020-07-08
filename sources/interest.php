<?php
if (IS_LOGGED == false) {
    header("Location: $site_url");
    exit();
}
$music->site_title = lang("Interest");
$music->site_description = lang("Interest") ;
$music->site_pagename = "interest";
$music->site_content = loadPage("interest/content", []);
