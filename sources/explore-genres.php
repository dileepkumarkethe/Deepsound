<?php 
$db->where('time', '0', '<>');
$db->where('availability', 0);
$music->category_id = 0;
if (!empty($path['options'][1])) {
	if (is_numeric($path['options'][1]) && $path['options'][1] > 0) {
		$music->category_id = $category_id = secure($path['options'][1]);
		$db->where('category_id', $music->category_id);
	}
} else {
	header("Location: $site_url");
	exit();
}

if (IS_LOGGED) {
	$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
}

$top_first = $db->orderBy('id', 'DESC')->get(T_SONGS, 30);

$top_first_html = '';
if (!empty($top_first)) {
	foreach ($top_first as $key => $song) {
		$key = "";
		$songData = songData($song, false, false);
		$top_first_html .= loadPage("discover/recommended-list", [
			'url' => $songData->url,
			'title' => $songData->title,
			'thumbnail' => $songData->thumbnail,
			'id' => $songData->id,
			'audio_id' => $songData->audio_id,
			'USER_DATA' => $songData->publisher,
			'key' => $key,
			'fav_button' => getFavButton($songData->id, 'fav-icon'),
			'duration' => $songData->duration
		]);
	}
}
$music->songs = count($top_first);
if(isset($music->categories->{$category_id})){
    $music->site_title = $music->categories->{$category_id};
}else{
    $music->site_title = 'Category';
}
$music->site_description = $music->config->description;
$music->site_pagename = "explore_genres";
$music->site_content = loadPage("genres/explore", [
	'TOP_SONGS' => $top_first_html,
	'CATEGORY_NAME' => $music->site_title,
	'category_id' => $category_id
]);