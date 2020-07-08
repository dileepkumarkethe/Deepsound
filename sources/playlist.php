<?php 
if (empty($path['options'][1])) {
	header("Location: $site_url/404");
	exit();
}

$id = secure($path['options'][1]);

$playlist = $music->playlist = getPlayList($db->where('uid', $id)->getValue(T_PLAYLISTS, 'id'));

if (empty($playlist)) {
	header("Location: $site_url/404");
	exit();
}

$html_list = '<div class="no-track-found bg_light"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M15,6H3V8H15V6M15,10H3V12H15V10M3,16H11V14H3V16M17,6V14.18C16.69,14.07 16.35,14 16,14A3,3 0 0,0 13,17A3,3 0 0,0 16,20A3,3 0 0,0 19,17V8H22V6H17Z" /></svg>' . lang("No songs on this playlist yet.") . '</div>';

$getPlaylistSongs = $db->where('playlist_id', $playlist->id)->get(T_PLAYLIST_SONGS);

if (!empty($getPlaylistSongs)) {
	$html_list = '';
	foreach ($getPlaylistSongs as $key => $song) {
		$songData = songData($song->track_id);
		if (!empty($songData)) {
			$music->songData = $songData;
			$html_list .= loadPage('playlist-play/list', [
				't_thumbnail' => $songData->thumbnail,
				't_id' => $songData->id,
				't_title' => $songData->title,
				't_artist' => $songData->publisher->name,
				't_url' => $songData->url,
				't_artist_url' => $songData->publisher->url,
				't_audio_id' => $songData->audio_id,
				't_duration' => $songData->duration,
				't_key' => ($key + 1)
			]);
		}
	}
}

$music->site_title = $playlist->name;
$music->site_description = $music->config->description;
$music->site_pagename = "playlist";
$music->site_content = loadPage("playlist-play/content", [
	't_thumbnail' => $playlist->thumbnail_ready,
	't_id' => $playlist->id,
	'USER_DATA' => $playlist->publisher,
	't_uid' => $playlist->uid,
	't_title' => $playlist->name,
	't_privacy' => $playlist->privacy_text,
	't_url' => urlencode($playlist->url),
	't_url_original' => $playlist->url,
	't_songs' => $playlist->songs,
	't_date' => time_Elapsed_String($playlist->time),
	'html_list' => $html_list
]);