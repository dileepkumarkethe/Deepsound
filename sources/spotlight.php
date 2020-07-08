<?php 
$html = '<div class="no-track-found bg_light"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M17,3A2,2 0 0,1 19,5V21L12,18L5,21V5C5,3.89 5.9,3 7,3H17M11,11A2,2 0 0,0 9,13A2,2 0 0,0 11,15A2,2 0 0,0 13,13V8H16V6H12V11.27C11.71,11.1 11.36,11 11,11Z" /></svg>' . lang("No spotlight tracks found") . '</div>';

$music->thereIsData = false;

$getActivties = getActivties(20, 0, 0, ['spotlight' => true]);
if (!empty($getActivties)) {
	$html = '';
	$music->thereIsData = true;
	foreach ($getActivties as $key => $activity) {
		$getActivity = getActivity($activity, false);
		$html .= loadPage("user/activity", $getActivity);
	}
}
$artist_sidebar_html = '';
if (IS_LOGGED) {
	$db->where("id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
	$artist_sidebar = $db->where('artist', '1')->
	where("id NOT IN (SELECT following_id FROM " . T_FOLLOWERS . " WHERE follower_id = '{$music->user->id}') AND id <> '{$music->user->id}'")->get(T_USERS, 8);
	
	$artist_sidebar_html_list = '';
	if (!empty($artist_sidebar)) {
		foreach ($artist_sidebar as $key => $value) {
			$artist_sidebar_html_list .= loadPage('feed/sidebar_artists_list', ['USER_DATA' => userData($value->id)]);
		}
		$artist_sidebar_html = loadPage('feed/sidebar_artists', ['html' => $artist_sidebar_html_list]);
	}
}


$time_week = time() - 604800;

$query = "SELECT " . T_SONGS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . "
FROM " . T_SONGS . " LEFT JOIN " . T_VIEWS . " ON " . T_SONGS . ".id = " . T_VIEWS . ".track_id
WHERE " . T_SONGS . ".time > $time_week AND " . T_SONGS . ".availability = '0'";

if (IS_LOGGED) {
	$query .= " AND " . T_SONGS . ".user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)";
}

$query .= " GROUP BY " . T_SONGS . ".id
ORDER BY " . T_VIEWS . " DESC LIMIT 20";

$weekly_sidebar = $db->rawQuery($query);

$weekly_sidebar_html = '';
$weekly_sidebar_html_list = '';

if (!empty($weekly_sidebar)) {
	foreach ($weekly_sidebar as $key => $song) {
		$songData = songData($song, false, false);
		$weekly_sidebar_html_list .= loadPage('feed/sidebar_weekly_list', $songData->songArray);
	}
	$weekly_sidebar_html = loadPage('feed/sidebar_weekly', ['html' => $weekly_sidebar_html_list]);
}


$music->site_title = lang('Spotlight');
$music->site_description = $music->config->description;
$music->site_pagename = "spotlight";
$music->site_content = loadPage("spotlight/content", ['html' => $html, 'artist_sidebar' => $artist_sidebar_html, 'weekly_sidebar' => $weekly_sidebar_html]);