<?php 
if (IS_LOGGED == false) {
	header("Location: $site_url");
	exit();
}
if (empty($path['options'][1])) {
	header("Location: $site_url/404");
	exit();
}
$audio_id = secure($path['options'][1]);
$getIDAudio = $db->where('audio_id', $audio_id)->getValue(T_SONGS, 'id');

if (empty($getIDAudio)) {
	header("Location: $site_url/404");
	exit();
}

$songData = songData($getIDAudio);

$songData->owner  = ($user->id == $songData->publisher->id) ? true : false;

if (!$songData->owner && isAdmin() == false) {
	header("Location: $site_url/404");
	exit();
}
$music->songData = $songData;


$music->site_title = lang("Edit Info") . " | " . htmlspecialchars_decode($songData->title);
$music->site_description = $songData->description;
$music->site_pagename = "track";
$music->site_content = loadPage("edit-track/content", [
	'USER_DATA' => $songData->publisher,
	't_thumbnail' => $songData->thumbnail,
	't_song' => $songData->audio_location,
	't_title' => $songData->title,
	't_edit_lyrics' => br2nl($songData->lyrics),
    't_description' => $songData->description,
	't_edit_description' => br2nl($songData->org_description),
	't_time' => time_Elapsed_String($songData->time),
	't_audio_id' => $songData->audio_id,
	't_tags' => $songData->tags_default,
	'session' => $_SESSION['session_hash']
]);
