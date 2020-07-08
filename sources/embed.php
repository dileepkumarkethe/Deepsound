<?php 
if (empty($path['options'][1])) {
	exit('The track is not found, 404');
}
$audio_id = secure($path['options'][1]);

if (IS_LOGGED) {
	$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
}

$getIDAudio = $db->where('audio_id', $audio_id)->getValue(T_SONGS, 'id');

if (empty($getIDAudio)) {
	exit('The track is not found, 404');
}

$songData = songData($getIDAudio);


 if ($songData->display_embed == 0) {
 	exit("This tracks can't be embeded");
 }

// if ($songData->price > 0) {
// 	exit("Priced tracks can't be embeded");
// }
if ($songData->age_restriction > 0) {
	exit("Age restrcited tracks can't be embeded");
}


$songData->owner  = false;

if ($music->loggedin == true) {
    $songData->owner  = ($user->id == $songData->publisher->id) ? true : false;
}

$music->albumData = [];
if (!empty($songData->album_id)) {
	$music->albumData = $db->where('id', $songData->album_id)->getOne(T_ALBUMS);
}
$isPurchased = isTrackPurchased($songData->id);

$can_download = false;
$notPurchased = false;
if (IS_LOGGED) { 
	if($songData->owner == true || isAdmin()) {
		$can_download = true;
 	}
 	if ($songData->price > 0) {
 		if ($isPurchased) {
	 		$can_download = true;
	 	} else {
	 		$notPurchased = true;
	 	}
 	}
 	if ($music->config->go_pro == 'on') {
 		if ($user->is_pro == 1) {
 			$can_download = true;
 		}
 	} else if ($notPurchased == false) {
 		$can_download = true;
 	}
}

$music->can_download = $can_download;				    
$music->songData = $songData;

$autoPlay = false;
if (!empty($path['options'][2])) {
	if ($path['options'][2] == 'play') {
		$autoPlay = true;
	}
}

$music->isPurchased = $isPurchased;
$music->autoPlay = $autoPlay;
$music->site_title = htmlspecialchars_decode($songData->title);
$music->site_description = $songData->description;
$music->site_pagename = "embed";
echo loadPage("embed/content", [
	'USER_DATA' => $songData->publisher,
	't_thumbnail' => $songData->thumbnail,
	't_song' => $songData->secure_url,
	't_title' => $songData->title,
	't_description' => $songData->description,
	't_time' => time_Elapsed_String($songData->time),
	't_audio_id' => $songData->audio_id,
	't_id' => $songData->id,
	't_price' => $songData->price,
	'category_name' => $songData->category_name,
	't_shares' => number_format_mm($songData->shares),
	'COUNT_LIKES' => number_format_mm(countLikes($songData->id)),
    'COUNT_DISLIKES' => number_format_mm(countDisLikes($songData->id)),
	'COUNT_VIEWS' => number_format_mm($db->where('track_id', $songData->id)->getValue(T_VIEWS, 'count(*)')),
	'COUNT_USER_SONGS' => $db->where('user_id', $songData->publisher->id)->getValue(T_SONGS, 'count(*)'),
	'COUNT_USER_FOLLOWERS' => number_format_mm($db->where('following_id', $songData->publisher->id)->getValue(T_FOLLOWERS, 'COUNT(*)')),
	'comment_count' => number_format_mm($db->where('track_id', $songData->id)->getValue(T_COMMENTS, 'count(*)')),
	'fav_count' => number_format_mm($db->where('track_id', $songData->id)->getValue(T_FOV, 'count(*)')),
]);

exit();
