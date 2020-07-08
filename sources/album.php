<?php 
if (empty($path['options'][1])) {
	header("Location: $site_url/404");
	exit();
}
$album_id = secure($path['options'][1]);

if (IS_LOGGED) {
	$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
}

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

$getAlbum->owner  = false;

if ($music->loggedin == true) {
    $getAlbum->owner  = ($user->id == $getAlbum->publisher->id) ? true : false;
}
$getAlbum->category_name =  (!empty($music->categories->{$getAlbum->category_id})) ? $music->categories->{$getAlbum->category_id} : lang('Other');	    
$music->getAlbum = $getAlbum;

$related_albums = $db->where('category_id', $getAlbum->category_id)->where('id', $getAlbum->id, '<>')->orderBy('RAND()')->get(T_ALBUMS, 10);
if (empty($related_albums)) {
	$related_albums = $db->orderBy('RAND()')->where('id', $getAlbum->id, '<>')->get(T_ALBUMS, 10);
}

$related_albums_html = '';
if (!empty($related_albums)) {
	foreach ($related_albums as $key => $album) {
		$key = ($key + 1);
		$related_albums_html .= loadPage("top-music-album/list", [
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


$html_list = "<div class='no-songs-found text-center'>" . lang("No songs on this album yet.") . "</div>";;

$getAlbumSongs = $db->where('album_id', $getAlbum->id)->get(T_SONGS);

if (!empty($getAlbumSongs)) {
	$html_list = '';
	foreach ($getAlbumSongs as $key => $song) {
		$songData = songData($song, false, false);
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


$music->site_title = htmlspecialchars_decode($getAlbum->title);
$music->site_description = $getAlbum->description;
$music->site_pagename = "album";
$music->site_content = loadPage("album/content", [
	'USER_DATA' => $getAlbum->publisher,
	'thumbnail' => getMedia($getAlbum->thumbnail),
	'title' => $getAlbum->title,
	'description' => $getAlbum->description,
	'time' => time_Elapsed_String($getAlbum->time),
	'url' => getLink("album/$getAlbum->album_id"),
	'album_id' => $getAlbum->album_id,
	'id' => $getAlbum->id,
	'price' => $getAlbum->price,
	'category_name' => $getAlbum->category_name,
	'RELATED' => $related_albums_html,
	'SONG_LIST' => $html_list,
	'count' => count($getAlbumSongs),
    'purchases_count' => $getAlbum->purchases
]);
