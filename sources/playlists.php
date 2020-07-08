<?php 
$html = '<div class="no-track-found bg_light"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M19,9H2V11H19V9M19,5H2V7H19V5M2,15H15V13H2V15M17,13V19L22,16L17,13Z" /></svg>' . lang("No playlists found") . '</div>';

if (IS_LOGGED) {
	$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
}
$music->playlist_count = 0;
$getPlayLists = $db->where('privacy', 0)->orderBy('id', 'DESC')->get(T_PLAYLISTS, 30);

if (!empty($getPlayLists)) {
	$html = '';
	foreach ($getPlayLists as $key => $playlist) {
        $music->playlist_count++;
		$playlist = getPlayList($playlist, false);
		$html .= loadPage('user/playlist-list', [
			't_thumbnail' => $playlist->thumbnail_ready,
			't_id' => $playlist->id,
			't_uid' => $playlist->uid,
			'USER_DATA' => $playlist->publisher,
			't_title' => $playlist->name,
			't_privacy' => $playlist->privacy_text,
			't_url' => $playlist->url,
			't_songs' => $playlist->songs,
			't_key' => ($key + 1)
		]);
	}
}

$music->site_title = lang("Playlists");
$music->site_description = $music->config->description;
$music->site_pagename = "playlists";
$music->site_content = loadPage("playlists/public", ['html' => $html]);
