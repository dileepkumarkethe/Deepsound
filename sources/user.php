<?php 

if (empty($path['page'])) {
	header("Location: $site_url/404");
	exit();
}
$record_count = 0;
$username = secure($path['page']);
if (IS_LOGGED) {
	$db->where("id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
}
$getIDfromUser = $db->where('username', $username)->getValue(T_USERS, 'id');
if (empty($getIDfromUser)) {
	header("Location: $site_url/404");
	exit();
}

$userData = userData($getIDfromUser);

$userData->owner  = false;

if ($music->loggedin == true) {
    $userData->owner  = ($user->id == $userData->id) ? true : false;
}

$profile_content = "";
$html = '<div class="no-track-found bg_light"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M19,9H2V11H19V9M19,5H2V7H19V5M2,15H15V13H2V15M17,13V19L22,16L17,13Z" /></svg>' . lang("No tracks found") . '</div>';
$music->third_url = $third_url = (!empty($path['options'][1])) ? $path['options'][1] : '';
$file = 'songs';

if (empty($path['options'][1]) || $path['options'][1] == 'activities') {
	$html = '<div class="no-track-found bg_light"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M3,7H9V13H3V7M3,3H21V5H3V3M21,11V13H11V11H21M3,15H17V17H3V15M3,19H21V21H3V19Z" /></svg>' . lang("No activties found") . '</div>';
	$getActivties = getActivties(10, 0, $userData->id);
	if (!empty($getActivties)) {
		$html = '';
		foreach ($getActivties as $key => $activity) {
            $record_count++;
			$getActivity = getActivity($activity, false);
			$html .= loadPage("user/activity", $getActivity);
		}
	}
} else if ($path['options'][1] == 'liked') {
	$html = '<div class="no-track-found bg_light"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M23,10C23,8.89 22.1,8 21,8H14.68L15.64,3.43C15.66,3.33 15.67,3.22 15.67,3.11C15.67,2.7 15.5,2.32 15.23,2.05L14.17,1L7.59,7.58C7.22,7.95 7,8.45 7,9V19A2,2 0 0,0 9,21H18C18.83,21 19.54,20.5 19.84,19.78L22.86,12.73C22.95,12.5 23,12.26 23,12V10M1,21H5V9H1V21Z" /></svg>' . lang("No activties found") . '</div>';
	$getActivties = getActivties(10, 0, $userData->id, ['likes' => true]);
	if (!empty($getActivties)) {
		$html = '';
		foreach ($getActivties as $key => $activity) {
            $record_count++;
			$getActivity = getActivity($activity, false);
			$html .= loadPage("user/activity", $getActivity);
		}
	}
} else if ($path['options'][1] == 'songs') {
	$db->where('user_id', $userData->id);
	if (!IS_LOGGED) {
		$db->where('availability', '0');
	} else {
		if ($user->id != $userData->id) {
			$db->where('availability', '0');
		}
	}
	$getUserSongs = $db->orderby('id', 'DESC')->get(T_SONGS, 10, 'id');
	if (!empty($getUserSongs)) {
		$html = '';
		foreach ($getUserSongs as $key => $userSong) {
            $record_count++;
			$userSong = songData($userSong->id);
			$music->isSongOwner = false;
			if (IS_LOGGED == true) {
				$music->isSongOwner = ($user->id == $userSong->publisher->id) ? true : false;
			}
			$music->songData = $userSong;
			$music->dark_wave = $userSong->dark_wave;
			$music->light_wave = $userSong->light_wave;
			$html .= loadPage("user/posts", $userSong->songArray);
		}
	}
} else if ($path['options'][1] == 'top-songs') {
	$getUserSongs = $getLatestSongs = $db->rawQuery("
SELECT " . T_SONGS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . "
FROM " . T_SONGS . " LEFT JOIN " . T_VIEWS . " ON " . T_SONGS . ".id = " . T_VIEWS . ".track_id
WHERE " . T_SONGS . ".user_id = " . $userData->id . "
GROUP BY " . T_SONGS . ".id
ORDER BY " . T_VIEWS . " DESC LIMIT 20");
	
	if (!empty($getUserSongs)) {
		$html = "";
		foreach ($getUserSongs as $key => $userSong) {
            $record_count++;
			$userSong = songData($userSong->id);
			$music->isSongOwner = false;
			if (IS_LOGGED == true) {
				$music->isSongOwner = ($user->id == $userSong->publisher->id) ? true : false;
			}
			$music->songData = $userSong;
			$music->dark_wave = $userSong->dark_wave;
			$music->light_wave = $userSong->light_wave;
			$html .= loadPage("user/posts", $userSong->songArray);
		}
	}
}  else if ($path['options'][1] == 'store') {
	$getUserSongs = $db->where('user_id', $userData->id)->where('price', '0', '<>')->orderBy('id', 'DESC')->get(T_SONGS, 10);
	if (!empty($getUserSongs)) {
		$html = "";
		foreach ($getUserSongs as $key => $userSong) {
            $record_count++;
			$userSong = songData($userSong->id);
			$music->isSongOwner = false;
			if (IS_LOGGED == true) {
				$music->isSongOwner = ($user->id == $userSong->publisher->id) ? true : false;
			}
			$music->songData = $userSong;
			$music->dark_wave = $userSong->dark_wave;
			$music->light_wave = $userSong->light_wave;
			$music->store = true;
			$html .= loadPage("user/posts", $userSong->songArray);
		}
	}
} else if ($path['options'][1] == 'playlists') {
	$file = 'playlists';
	$getPlayLists = $db->where('user_id', $userData->id)->where('privacy', 0)->orderBy('id', 'DESC')->get(T_PLAYLISTS, 9);
	if (!empty($getPlayLists)) {
		$html = "";
		foreach ($getPlayLists as $key => $playlist) {
            $record_count++;
			$playlist = getPlayList($playlist, false);
			$html .= loadPage("user/playlist-list", [
				't_thumbnail' => $playlist->thumbnail_ready,
				't_id' => $playlist->id,
				's_artist' => $playlist->publisher->name,
				't_uid' => $playlist->uid,
				't_title' => $playlist->name,
				't_privacy' => $playlist->privacy_text,
				't_url' => $playlist->url,
				't_url_original' => $playlist->url,
				't_songs' => $playlist->songs,
				'USER_DATA' => $playlist->publisher
			]);
		}
	}
} else if ($path['options'][1] == 'albums') {
	$file = 'albums';
	$getAlbums = $db->where('user_id', $userData->id)->orderBy('id', 'DESC')->get(T_ALBUMS, 9);
	if (!empty($getAlbums)) {
		$html = "";
		foreach ($getAlbums as $key => $album) {
            $record_count++;
			$key = ($key + 1);
			$html .= loadPage("user/album-list", [
				'url' => getLink("album/$album->album_id"),
				'title' => $album->title,
				'thumbnail' => getMedia($album->thumbnail),
				'id' => $album->id,
				'album_id' => $album->album_id,
				'USER_DATA' => userData($album->user_id),
				'key' => $key,
				'songs' => $db->where('album_id', $album->id)->getValue(T_SONGS, 'COUNT(*)')
			]);
		}
	}
} else if ($path['options'][1] == 'followers') {
    $file = 'followers';
    $getFollowers = $db->where('following_id', $userData->id)
                        ->where("follower_id NOT IN (SELECT blocked_id FROM blocks WHERE user_id = $userData->id)")
                        ->orderBy('id', 'DESC')->get(T_FOLLOWERS, 9);
	$html = '<div class="no-track-found bg_light"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M16,13C15.71,13 15.38,13 15.03,13.05C16.19,13.89 17,15 17,16.5V19H23V16.5C23,14.17 18.33,13 16,13M8,13C5.67,13 1,14.17 1,16.5V19H15V16.5C15,14.17 10.33,13 8,13M8,11A3,3 0 0,0 11,8A3,3 0 0,0 8,5A3,3 0 0,0 5,8A3,3 0 0,0 8,11M16,11A3,3 0 0,0 19,8A3,3 0 0,0 16,5A3,3 0 0,0 13,8A3,3 0 0,0 16,11Z" /></svg>' . lang("No followers found") . '</div>';
    if (!empty($getFollowers)) {
        $html = "";
        foreach ($getFollowers as $key => $follower) {
            $record_count++;
            $key = ($key + 1);
            $html .= loadPage("user/follower-list", [
                'f_id' => $follower->id,
                'USER_DATA' => userData($follower->follower_id),
            ]);
        }
    }
} else if ($path['options'][1] == 'following') {
    $file = 'following';
    $getFollowings = $db->where('follower_id', $userData->id)
                        ->where("following_id NOT IN (SELECT blocked_id FROM blocks WHERE user_id = $userData->id)")
                        ->orderBy('id', 'DESC')->get(T_FOLLOWERS, 9);
	$html = '<div class="no-track-found bg_light"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M16,13C15.71,13 15.38,13 15.03,13.05C16.19,13.89 17,15 17,16.5V19H23V16.5C23,14.17 18.33,13 16,13M8,13C5.67,13 1,14.17 1,16.5V19H15V16.5C15,14.17 10.33,13 8,13M8,11A3,3 0 0,0 11,8A3,3 0 0,0 8,5A3,3 0 0,0 5,8A3,3 0 0,0 8,11M16,11A3,3 0 0,0 19,8A3,3 0 0,0 16,5A3,3 0 0,0 13,8A3,3 0 0,0 16,11Z" /></svg>' . lang("No following found") . '</div>';
    if (!empty($getFollowings)) {
        $html = "";
        foreach ($getFollowings as $key => $following) {
            $record_count++;
            $key = ($key + 1);
            $html .= loadPage("user/following-list", [
                'f_id' => $following->id,
                'USER_DATA' => userData($following->following_id),
            ]);
        }
    }
}

$where = '';
if (IS_LOGGED) {
    $where = "`id` <> ". $user->id ." AND ";
}

$result_artists = $db->rawQuery("SELECT * FROM `".T_USERS."` WHERE ". $where ."  `artist` = 1 ORDER BY rand() DESC LIMIT 10;");
$artists_html = '';
foreach ($result_artists as $artists) {
    $pagedata = [
        'ARTIST_DATA' => userData( $artists->id )
    ];
    $artists_html = loadPage("user/artist-item", $pagedata);
}
$music->artists_html = $artists_html;


$query4 = "SELECT " . T_SONGS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . "
FROM " . T_SONGS . " LEFT JOIN " . T_VIEWS . " ON " . T_SONGS . ".id = " . T_VIEWS . ".track_id
WHERE " . T_SONGS . ".availability = '0'";

if (IS_LOGGED) {
    $query4 .= " AND " . T_SONGS . ".user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)";
}

$time = strtotime(date('l').", ".date('M')." ".date('d').", ".date('Y'));

if (date('l') == 'Saturday') {
    $week_start = strtotime(date('M')." ".date('d').", ".date('Y')." 12:00am");
}
else{
    $week_start = strtotime('last saturday, 12:00am', $time);
}

if (date('l') == 'Friday') {
    $week_end = strtotime(date('M')." ".date('d').", ".date('Y')." 11:59pm");
}
else{
    $week_end = strtotime('next Friday, 11:59pm', $time);
}

$query4 .= " AND " . T_VIEWS .".time >= " . $week_start . " AND " . T_VIEWS .".time <= " . $week_end;

$query4 .= " GROUP BY " . T_SONGS . ".id
ORDER BY " . T_VIEWS . " DESC LIMIT 10";
$top_weekly = $db->rawQuery($query4);

$music->top_weekly_html = [];
foreach ($top_weekly as $song) {
    $music->top_weekly[] = songData($song, false, false);
//    $pagedata = [
//        'ARTIST_DATA' => userData( $artists->id )
//    ];
//    $top_weekly_html = loadPage("user/artist-item", $pagedata);
}





$music->html = $html;
$music->record_count = $record_count;
$profile_content = loadPage("user/$file", [
	'HTML' => $html
]);
$user_profile = ($userData->artist == 0) ? "content" : "artist";
$music->userData = $userData;
$music->site_title = $userData->name;
$music->site_description = $music->config->description;
$music->site_pagename = "user";
$userFinalData = [
	'USER_DATA' => $userData,
    'MESSAGE_BUTTON'  => GetMessageButton($userData->username),
	'COUNT_FOLLOWERS' => $db->where('following_id', $userData->id)->where("follower_id NOT IN (SELECT blocked_id FROM blocks WHERE user_id = $userData->id)")->getValue(T_FOLLOWERS, 'COUNT(*)'),
	'COUNT_FOLLOWING' => $db->where('follower_id', $userData->id)->where("following_id NOT IN (SELECT blocked_id FROM blocks WHERE user_id = $userData->id)")->getValue(T_FOLLOWERS, 'COUNT(*)'),
	'COUNT_TRACKS' => $db->where('user_id', $userData->id)->getValue(T_SONGS, 'COUNT(*)'),
	'PROFILE_CONTENT' => $profile_content
];

$music->site_content = loadPage("user/$user_profile", $userFinalData);