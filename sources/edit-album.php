<?php 
if (IS_LOGGED == false) {
	header("Location: $site_url");
	exit();
}
if (empty($path['options'][1])) {
	header("Location: $site_url/404");
	exit();
}
$album_id = secure($path['options'][1]);

$getAlbum = $db->where('album_id', $album_id)->getOne(T_ALBUMS);

if (empty($getAlbum)) {
	header("Location: $site_url/404");
	exit();
}

$getAlbum->publisher = userData($getAlbum->user_id);

if (empty($getAlbum->publisher)) {
	header("Location: $site_url/404");
	exit();
}

$getAlbum->owner  = ($user->id == $getAlbum->publisher->id) ? true : false;

if (!$getAlbum->owner && isAdmin() == false) {
	header("Location: $site_url/404");
	exit();
}

$html_list = '';

$getAlbumSongs = $db->where('album_id', $getAlbum->id)->get(T_SONGS);

if (!empty($getAlbumSongs)) {
	$html_list = '';
	foreach ($getAlbumSongs as $key => $song) {
		$songData = songData($song, false, false);
		if (!empty($songData)) {
			$music->songData = $songData;
			$html_list .= loadPage('edit-album/list', [
				't_thumbnail' => $songData->thumbnail,
				't_id' => $songData->id,
				't_title' => $songData->title,
				't_artist' => $songData->publisher->name,
				't_url' => $songData->url,
				't_artist_url' => $songData->publisher->url,
				't_audio_id' => $songData->audio_id,
				't_duration' => $songData->duration,
				't_key' => ($key + 1),
				'form_id' => substr(md5(microtime()), 0, 10)
			]);
		}
	}
}

$music->getAlbum = $getAlbum;
$_SESSION['album_songs'] = [];
$music->site_title = lang("Edit Info") . " | " . htmlspecialchars_decode($getAlbum->title);
$music->site_description = $getAlbum->description;
$music->site_pagename = "edit-album";
$music->site_content = loadPage("edit-album/content", [
	'USER_DATA' => $getAlbum->publisher,
	'thumbnail' => getMedia($getAlbum->thumbnail),
	'title' => $getAlbum->title,
	'edit_description' => br2nl(EditmarkUp($getAlbum->description)),
	'time' => time_Elapsed_String($getAlbum->time),
	'album_id' => $getAlbum->album_id,
	'getSongs' => $html_list
]);
