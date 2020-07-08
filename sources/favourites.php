<?php 
if (IS_LOGGED == false) {
	header("Location: $site_url");
	exit();
}

$html = "";
$counts = 0;
//$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
$getFovs = $db->where('user_id', $user->id)->orderBy('id', 'DESC')->get(T_FOV);

if (!empty($getFovs)) {
	$html = '';
	foreach ($getFovs as $key => $song) {
		$songData = songData($song->track_id);
		if (!empty($songData)) {
			$music->songData = $songData;
			$html .= loadPage('favourites/list', [
				't_thumbnail' => $songData->thumbnail,
				't_id' => $songData->id,
				't_title' => $songData->title,
				't_artist' => $songData->publisher->name,
				't_url' => $songData->url,
				't_artist_url' => $songData->publisher->url,
				't_audio_id' => $songData->audio_id,
				't_time' => $songData->time,
				't_price' => $songData->price,
				't_duration' => $songData->duration,
				't_key' => ($key + 1)
			]);
            $counts++;
		}
	}
}


$getlikedtracks = $db->where('user_id', $user->id)->orderBy('time', 'DESC')->get(T_LIKES);
if (!empty($getlikedtracks)) {
    foreach ($getlikedtracks as $key => $song) {
        $songData = songData($song->track_id);
        if (!empty($songData)) {
            $music->songData = $songData;
            $html .= loadPage('favourites/list', [
                't_thumbnail' => $songData->thumbnail,
                't_id' => $songData->id,
                't_title' => $songData->title,
                't_artist' => $songData->publisher->name,
                't_url' => $songData->url,
                't_artist_url' => $songData->publisher->url,
                't_audio_id' => $songData->audio_id,
                't_time' => $songData->time,
                't_price' => $songData->price,
                't_duration' => $songData->duration,
                't_key' => ($key + 1)
            ]);
            $counts++;
        }
    }
}



$count_text = str_replace('|c|', $counts, lang("You currently have |c| favourite songs"));
$music->site_title = lang("Favourites");
$music->site_description = $music->config->description;
$music->site_pagename = "favourites";
$music->site_content = loadPage("favourites/content", ['html' => $html, 'counts' => $count_text]);
