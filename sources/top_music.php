<?php 

$query = "SELECT " . T_SONGS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . "
FROM " . T_SONGS . " LEFT JOIN " . T_VIEWS . " ON " . T_SONGS . ".id = " . T_VIEWS . ".track_id
WHERE " . T_SONGS . ".availability = '0'";

if (IS_LOGGED) {
	$query .= " AND " . T_SONGS . ".user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)";
}

$query .= " GROUP BY " . T_SONGS . ".id
ORDER BY " . T_VIEWS . " DESC LIMIT 20";

$top_first = $db->rawQuery($query);

$top_first_html = '';
$top_second_html = '';
$addTo = '';
$half = 2;
if (!empty($top_first)) {
	if (count($top_first) > 1) {
		$half = (count($top_first) / 2);
	}
	foreach ($top_first as $key => $song) {
		$key = ($key + 1);
		$addTo = "top_first_html";
		if ($key > $half) {
			$addTo = "top_second_html";
		}
		$songData = songData($song, false, false);
		$$addTo .= loadPage("discover/recommended-list", [
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

$top_albums = '';


$query = "SELECT " . T_ALBUMS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . "
FROM " . T_ALBUMS . " LEFT JOIN " . T_VIEWS . " ON " . T_ALBUMS . ".id = " . T_VIEWS . ".album_id";

if (IS_LOGGED) {
	$query .= " AND " . T_ALBUMS . ".user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)";
}

$query .= " GROUP BY " . T_ALBUMS . ".id
ORDER BY " . T_VIEWS . " DESC LIMIT 13";

$top_first_albums = $db->rawQuery($query);

if (!empty($top_first_albums)) {
	foreach ($top_first_albums as $key => $album) {
		$key = ($key + 1);
		$top_albums .= loadPage("top-music-album/list", [
			'url' => getLink("album/$album->album_id"),
			'title' => $album->title,
			'thumbnail' => getMedia($album->thumbnail),
			'id' => $album->id,
			'album_id' => $album->album_id,
			'USER_DATA' => userData($album->user_id),
			'key' => $key,
		]);
	}
}


$music->site_title = lang('Top Music');
$music->site_description = $music->config->description;
$music->site_pagename = "top_music";
$music->site_content = loadPage("top_music/content", [
	'TOP_FIRST_6' => $top_first_html,
	'TOP_SECOND_6' => $top_second_html,
	'TOP_ALBUMS' => $top_albums
]);