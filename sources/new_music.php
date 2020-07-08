<?php 

$time_week = time() - 259200;

$query = "SELECT " . T_SONGS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . "
FROM " . T_SONGS . " LEFT JOIN " . T_VIEWS . " ON " . T_SONGS . ".id = " . T_VIEWS . ".track_id
WHERE " . T_SONGS . ".time > $time_week AND " . T_SONGS . ".availability = '0'";

if (IS_LOGGED) {
	$query .= " AND " . T_SONGS . ".user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)";
}

$query .= " GROUP BY " . T_SONGS . ".id
ORDER BY " . T_VIEWS . " DESC LIMIT 20";

$getNewRelease = $db->rawQuery($query);

$newReleases = '';

if (!empty($getNewRelease)) {
	foreach ($getNewRelease as $key => $song) {
		$songData = songData($song, false, false);
		$newReleases .= loadPage("discover/recently-list", [
			'url' => $songData->url,
			'title' => $songData->title,
			'thumbnail' => $songData->thumbnail,
			'id' => $songData->id,
			'audio_id' => $songData->audio_id,
			'USER_DATA' => $songData->publisher
		]);
	}
}

if (IS_LOGGED) {
	$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
}
$getLatestReleases = $db->where('availability', 0)->orderBy('id', 'DESC')->get(T_SONGS, 21);

$latestReleases = '';

if (!empty($getLatestReleases)) {
	foreach ($getLatestReleases as $key => $song) {
		$songData = songData($song, false, false);
		$latestReleases .= loadPage("discover/recently-list", [
			'url' => $songData->url,
			'title' => $songData->title,
			'thumbnail' => $songData->thumbnail,
			'id' => $songData->id,
			'audio_id' => $songData->audio_id,
			'USER_DATA' => $songData->publisher
		]);
	}
}

$music->getNewRelease = count($getLatestReleases);
$music->latestReleases = count($getLatestReleases);

$music->site_title = lang('New Music');
$music->site_description = $music->config->description;
$music->site_pagename = "new_music";
$music->site_content = loadPage("new_music/content", [
	'BEST_NEW_RELEASES' => $newReleases,
	'NEW_RELEASES' => $latestReleases
]);