<?php 
$query = "SELECT " . T_SONGS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . "
FROM " . T_SONGS . " LEFT JOIN " . T_VIEWS . " ON " . T_SONGS . ".id = " . T_VIEWS . ".track_id WHERE " . T_SONGS . ".availability = '0'";

$music->category_id = 0;
if (!empty($path['options'][1])) {
	if (is_numeric($path['options'][1]) && $path['options'][1] > 0) {
		$music->category_id = $category_id = secure($path['options'][1]);
		$query .= " AND " . T_SONGS . ".category_id = '$category_id' ";
	}
}
if (IS_LOGGED) {
	$query .= " AND " . T_SONGS . ".user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)";
}


$query .= " GROUP BY " . T_SONGS . ".id
ORDER BY " . T_VIEWS . " DESC LIMIT 50";

$top_first = $db->rawQuery($query);

$top_first_html = '';
if (!empty($top_first)) {
	foreach ($top_first as $key => $song) {
		$key = ($key + 1);
		$songData = songData($song, false, false);
		$top_first_html .= loadPage("discover/recommended-list", [
			'url' => $songData->url,
			'title' => $songData->title,
			'thumbnail' => $songData->thumbnail,
			'id' => $songData->id,
			'audio_id' => $songData->audio_id,
			'USER_DATA' => $songData->publisher,
			'key' => $key,
			'fav_button' => getFavButton($songData->id, 'fav-icon'),
			'duration' => $songData->duration
		]);
	}
}

$music->site_title = lang('Top Music');
$music->site_description = $music->config->description;
$music->site_pagename = "top_genres";
$music->site_content = loadPage("top-genres/content", [
	'TOP_SONGS' => $top_first_html,
]);