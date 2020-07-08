<?php 
if (IS_LOGGED == false) {
	header("Location: $site_url");
	exit();
}

$records = 0;
$html = "<div class='no-songs-found text-center'>" . lang("No tracks found, try to listen more? ;)") . "</div>";

// if (IS_LOGGED) {
// 	$db->where("user_id NOT IN (SELECT user_id FROM songs WHERE user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id))");
// }

if (!empty($_SESSION['fingerPrint'])) {
	$db->where('fingerprint', secure($_SESSION['fingerPrint']));
} else if (IS_LOGGED) {
	$db->where('user_id', secure($user->id));
}

$getRecentPlayes = $db->groupBy('track_id')->orderBy('time', 'DESC')->get(T_VIEWS, 20);
if (!empty($getRecentPlayes)) {
    $records = $getRecentPlayes;
	$html = '';
	foreach ($getRecentPlayes as $key => $song) {
		$songData = songData($song->track_id);
		if (!empty($songData)) {
			$html .= loadPage('recently_played/list', [
				't_thumbnail' => $songData->thumbnail,
				't_id' => $songData->id,
				't_title' => $songData->title,
				't_artist' => $songData->publisher->name,
				't_url' => $songData->url,
				't_artist_url' => $songData->publisher->url,
				't_audio_id' => $songData->audio_id,
				't_time' => $song->time
			]);
		}
	}
}
$music->records = $records;
$music->site_title = lang("Recently Played");
$music->site_description = $music->config->description;
$music->site_pagename = "recently_played";
$music->site_content = loadPage("recently_played/content", ['html' => $html]);
