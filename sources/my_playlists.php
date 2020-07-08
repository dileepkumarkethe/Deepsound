<?php 
if (IS_LOGGED == false) {
	header("Location: $site_url");
	exit();
}

$html = "";

$getPlayLists = $db->where('user_id', $user->id)->orderBy('id', 'DESC')->get(T_PLAYLISTS);

if (!empty($getPlayLists)) {
	$html = '';
	foreach ($getPlayLists as $key => $playlist) {
		$playlist = getPlayList($playlist, false);
		$html .= loadPage('playlists/list', [
			't_thumbnail' => $playlist->thumbnail_ready,
			't_id' => $playlist->id,
			't_uid' => $playlist->uid,
			't_title' => $playlist->name,
			't_privacy' => $playlist->privacy_text,
			't_url' => $playlist->url,
			't_songs' => $playlist->songs,
			't_key' => ($key + 1)
		]);
	}
}

$counts = $db->where('user_id', $user->id)->orderBy('id', 'DESC')->getValue(T_PLAYLISTS, 'count(*)');

$count_text = str_replace('|c|', $counts, lang("You currently have |c| playlists."));
$music->site_title = lang("Playlists");
$music->site_description = $music->config->description;
$music->site_pagename = "my_playlists";
$music->site_content = loadPage("playlists/content", ['html' => $html, 'counts' => $count_text]);
