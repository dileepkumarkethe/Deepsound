<?php 
$top_albums = '';

$query = "SELECT " . T_ALBUMS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . "
FROM " . T_ALBUMS . " LEFT JOIN " . T_VIEWS . " ON " . T_ALBUMS . ".id = " . T_VIEWS . ".album_id";

if (IS_LOGGED) {
	$query .= " AND " . T_ALBUMS . ".user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)";
}

$query .= " GROUP BY " . T_ALBUMS . ".id
ORDER BY " . T_VIEWS . " DESC LIMIT 50";

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


$music->site_title = lang('Top Albums');
$music->site_description = $music->config->description;
$music->site_pagename = "top-music-album";
$music->site_content = loadPage("top-music-album/content", [
	'TOP_ALBUMS' => $top_albums
]);