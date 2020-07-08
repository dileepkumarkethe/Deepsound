<?php
if (empty($_REQUEST['id'])) {
	exit('No ID sent, what to load?');
}

if (!is_numeric($_REQUEST['id'])) {
	exit('ID is not numaric, hmm?');
}

$id = secure($_REQUEST['id']);
$user_id = 0;
if (!empty($_REQUEST['userID'])) {
	if (is_numeric($_REQUEST['userID'])) {
		$user_id = secure($_REQUEST['userID']);
	}
}

if ($option == 'songs') {
	$get_data = (!empty($_REQUEST['get_data'])) ? secure($_REQUEST['get_data']) : "songs";
	if ($get_data == 'songs') {
		$db->where('id', $id, '<');
		if (!empty($user_id)) {
			$db->where('user_id', secure($_REQUEST['userID']));
		}
		if (!IS_LOGGED) {
			$db->where('availability', '0');
		} else {
			$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
			if ($user->id != $user_id) {
				$db->where('availability', '0');
			}
		}
		$getUserSongs = $db->orderby('id', 'DESC')->get(T_SONGS, 10, 'id');
	} else if ($get_data == 'top-songs') {
		$getLastSongView = $db->where('track_id', $id)->getValue(T_VIEWS, 'count(*)');
		$query = "SELECT " . T_SONGS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . " FROM " . T_SONGS . " LEFT JOIN " . T_VIEWS . " ON " . T_SONGS . ".id = " . T_VIEWS . ".track_id WHERE " . T_SONGS . ".id <> $id";

		if (!empty($user_id)) {
			$query .=  " AND " . T_SONGS . ".user_id = " . $user_id;
		}
		if (!IS_LOGGED) {
			$query .= " AND availability = 0";
		} else {
			if ($user->id != $user_id) {
				$query .= " AND availability = 0";
			}
		}
		$query .= " GROUP BY " . T_SONGS . ".id HAVING $getLastSongView >= views ORDER BY " . T_VIEWS . " DESC LIMIT 20";

		$getUserSongs = $db->rawQuery($query);
	} else if ($get_data == 'store') {
		$db->where('id', $id, '<');
		$db->where('price', '0', '<>');
		if (!empty($user_id)) {
			$db->where('user_id', secure($_REQUEST['userID']));
		}
		if (IS_LOGGED) {
			$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
		}
		$getUserSongs = $db->orderby('id', 'DESC')->get(T_SONGS, 10, 'id');
	} 
	
	$html = '';
	if (!empty($getUserSongs)) {
		foreach ($getUserSongs as $key => $userSong) {
			$userSong = songData($userSong->id);
			$music->isSongOwner = false;
			if (IS_LOGGED == true) {
				$music->isSongOwner = ($user->id == $userSong->publisher->id) ? true : false;
			}
			$music->songData = $userSong;
			$html .= loadPage("user/posts", $userSong->songArray);
		}
	}
	$data['status'] = 200;
	$data['html'] = $html;
}
if ($option == 'activities') {
	$filterBy = [];
	if (!empty($_REQUEST['get_data'])) {
		if ($_REQUEST['get_data'] == 'liked') {
			$filterBy['likes'] = true;
		}
		if ($_REQUEST['get_data'] == 'spotlight') {
			$filterBy['spotlight'] = true;
		}
	}
	$getActivities = getActivties(10, $id, $user_id, $filterBy);
	$html = '';
	if (!empty($getActivities)) {
		$html = '';
		foreach ($getActivities as $key => $activity) {
			$getActivity = getActivity($activity, false);
			$html .= loadPage("user/activity", $getActivity);
		}
	}
	$data['status'] = 200;
	$data['html'] = $html;
}
if ($option == 'latest_music') {
	$db->where('id', $id, '<')->where('availability', 0);
	if (IS_LOGGED) {
		$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
	}
	$getNewRelease = $db->orderby('id', 'DESC')->get(T_SONGS, 21);
	$html = "";
	if (!empty($getNewRelease)) {
		foreach ($getNewRelease as $key => $song) {
			$songData = songData($song, false, false);
			$html .= loadPage("discover/recently-list", [
				'url' => $songData->url,
				'title' => $songData->title,
				'thumbnail' => $songData->thumbnail,
				'id' => $songData->id,
				'audio_id' => $songData->audio_id,
				'USER_DATA' => $songData->publisher
			]);
		}
	}
	$data['status'] = 200;
	$data['html'] = $html;
}
if ($option == 'albums') {
	$db->where('id', $id, '<');
	if (!empty($user_id)) {
		$db->where('user_id', secure($_REQUEST['userID']));
	}
	if (IS_LOGGED) {
		$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
	}
	$html = "";
	$getAlbums = $db->orderBy('id', 'DESC')->get(T_ALBUMS, 10);
	if (!empty($getAlbums)) {
		$html = "";
		foreach ($getAlbums as $key => $album) {
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
	$data['status'] = 200;
	$data['html'] = $html;
}
if ($option == 'playlists') {
	$db->where('id', $id, '<');
	if (!empty($user_id)) {
		$db->where('user_id', secure($_REQUEST['userID']));
	}
	if (IS_LOGGED) {
		$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
	}
	$html = "";
	$getPlayLists = $db->where('privacy', 0)->orderBy('id', 'DESC')->get(T_PLAYLISTS, 20);
	if (!empty($getPlayLists)) {
		foreach ($getPlayLists as $key => $playlist) {
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
	$data['status'] = 200;
	$data['html'] = $html;
}
if ($option == 'recently_played') {
	if (IS_LOGGED == false) {
	    exit("You ain't logged in!");
	}
	$db->where('time', $id, '<');
	if (!empty($_SESSION['fingerPrint'])) {
		$db->where('fingerprint', secure($_SESSION['fingerPrint']));
	} else {
		$db->where('user_id', secure($user->id));
	}
	$getRecentPlayes = $db->groupBy('track_id')->orderby('time', 'DESC')->get(T_VIEWS, 20);
	$html = '';
	if (!empty($getRecentPlayes)) {
		$html = '';
		foreach ($getRecentPlayes as $key => $song) {
			$songData = songData($song->track_id);
			if (!empty($songData)) {
				$html .= loadPage('recently_played/list', [
					't_thumbnail' => $songData->thumbnail,
					't_id' => $songData->id,
					't_title' => $songData->title,
					't_artist' => $songData->publisher->name,
					't_url' => $songData->url,
					't_artist_url' => $songData->publisher->url,
					't_audio_id' => $songData->audio_id,
					't_time' => $song->time
				]);
			}
		}
	}
	$data['status'] = 200;
	$data['html'] = $html;
}
if ($option == 'categories') {
	if (IS_LOGGED == false) {
	    exit("You ain't logged in!");
	}
	if (IS_LOGGED) {
		$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
	}
	$db->where('id', $id, '<');
	$category_id = (!empty($_REQUEST['get_data'])) ? secure($_REQUEST['get_data']) : "0";
	$db->where('category_id', $category_id);
	$getSongs = $db->orderby('id', 'DESC')->get(T_SONGS, 30);
	$html = '';
	if (!empty($getSongs)) {
		$html = '';
		foreach ($getSongs as $key => $song) {
			$key = "";
			$songData = songData($song, false, false);
			$html .= loadPage("discover/recommended-list", [
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
	$data['status'] = 200;
	$data['html'] = $html;
}
if ($option == 'comments') {
	if (IS_LOGGED == false) {
	    exit("You ain't logged in!");
	}
	$getSong = $db->where('audio_id', secure($_REQUEST['track_id']))->getOne(T_SONGS);
	if (IS_LOGGED) {
		$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
	}
	$getSongComments = $db->where('id', $id, '<')->where('track_id', $getSong->id)->orderBy('id', 'DESC')->get(T_COMMENTS, 20);
	
	$comment_html = '';
	if (!empty($getSongComments)) {
		foreach ($getSongComments as $key => $comment) {
			$comment = getComment($comment, false);
			$commentUser = userData($comment->user_id);
			$music->comment = $comment;
			$comment_html .= loadPage('track/comment-list', [
				'comment_id' => $comment->id,
				'comment_seconds' => $comment->songseconds,
				'comment_percentage' => $comment->songpercentage,
				'USER_DATA' => $commentUser,
				'comment_text' => $comment->value,
				'comment_posted_time' => $comment->posted,
                'tcomment_posted_time' => date('c',strtotime($comment->posted)),
				'comment_seconds_formatted' => $comment->secondsFormated,
				'comment_song_id' => $getSong->audio_id,
                'comment_song_track_id' => $comment->track_id,
			]);
		}
	} 
	$data['status'] = 200;
	$data['html'] = $comment_html;
}
if ($option == 'followers') {
    $html = "";
    $db->where('id', $id, '<');
    if (IS_LOGGED) {
        $db->where("follower_id NOT IN (SELECT blocked_id FROM blocks WHERE user_id = $user->id)");
    }
    $getFollowers = $db->where('following_id', $user_id)->orderBy('id', 'DESC')->get(T_FOLLOWERS, 9);
    if (!empty($getFollowers)) {
        foreach ($getFollowers as $key => $follower) {
            $key = ($key + 1);
            $html .= loadPage("user/follower-list", [
                'f_id' => $follower->id,
                'USER_DATA' => userData($follower->follower_id),
            ]);
        }
    }
    $data['status'] = 200;
    $data['html'] = $html;
}
if ($option == 'followings') {
    $html = "";
    $db->where('id', $id, '<');
    if (IS_LOGGED) {
        $db->where("following_id NOT IN (SELECT blocked_id FROM blocks WHERE user_id = $user->id)");
    }
    $getFollowings = $db->where('follower_id', $user_id)->orderBy('id', 'DESC')->get(T_FOLLOWERS, 9);
    if (!empty($getFollowings)) {
        $html = "";
        foreach ($getFollowings as $key => $following) {
            $key = ($key + 1);
            $html .= loadPage("user/following-list", [
                'f_id' => $following->id,
                'USER_DATA' => userData($following->following_id),
            ]);
        }
    }
    $data['status'] = 200;
    $data['html'] = $html;
}
if ($option == 'songs_search') {

    $search_keyword = (!empty($_REQUEST['get_data'])) ? secure($_REQUEST['get_data']) : "";
    $results = $db->rawQuery("SELECT * FROM `".T_SONGS."` WHERE `id` < ". $id ." AND (`title` LIKE '%".$search_keyword."%' OR `description` LIKE '%".$search_keyword."%') ORDER BY `id` DESC LIMIT 10;");
    $html = '';
    foreach ($results as $song) {
        $pagedata = [
            'SONG_DATA' => songData( $song->id )
        ];
        $html = loadPage("search/song-list", $pagedata);
    }
    $data['status'] = 200;
    $data['html'] = $html;

}
if ($option == 'artists_search') {

    $search_keyword = (!empty($_REQUEST['get_data'])) ? secure($_REQUEST['get_data']) : "";
    $results = $db->rawQuery("SELECT * FROM `".T_USERS."` WHERE `id` < ". $id ." AND (`name` LIKE '%".$search_keyword."%' OR `username` LIKE '%".$search_keyword."%') AND `artist` = 1 ORDER BY `id` DESC LIMIT 10;");
    $html = '';
    foreach ($results as $artists) {
        $pagedata = [
            'ARTIST_DATA' => userData( $artists->id )
        ];
        $html = loadPage("search/artists-list", $pagedata);
    }
    $data['status'] = 200;
    $data['html'] = $html;

}
if ($option == 'albums_search') {

    $search_keyword = (!empty($_REQUEST['get_data'])) ? secure($_REQUEST['get_data']) : "";
    $results = $db->rawQuery("SELECT * FROM `".T_ALBUMS."` WHERE `id` < ". $id ." AND (`title` LIKE '%".$search_keyword."%' OR `description` LIKE '%".$search_keyword."%') ORDER BY `id` DESC LIMIT 10;");
    $html = '';
    foreach ($results as $album) {
        $pagedata = [
            'ALBUM_DATA' => $album,
            'PUBLISHER_DATA' => userData($album->user_id),
            'thumbnail' => GetMedia($album->thumbnail)
        ];
        $html = loadPage("search/albums-list", $pagedata);
    }
    $data['status'] = 200;
    $data['html'] = $html;

}
if ($option == 'playlists_search') {

    $search_keyword = (!empty($_REQUEST['get_data'])) ? secure($_REQUEST['get_data']) : "";
    $results = $db->rawQuery("SELECT * FROM `".T_PLAYLISTS."` WHERE `id` < ". $id ." AND (`name` LIKE '%".$search_keyword."%' AND `privacy` = 0 ) ORDER BY `id` DESC LIMIT 10;");
    $html = '';
    foreach($results as $key => $playlists){
        $playlist = getPlayList($playlists->id);
        $html = loadPage("user/playlist-list", ['t_id' => $playlist->id,'t_uid' => $playlist->uid,'t_title' => $playlist->name,'t_songs' => $playlist->songs, 't_url' => $playlist->url, 'USER_DATA' => userData($playlist->user_id), 't_thumbnail' => GetMedia($playlist->thumbnail) ]);
    }
    $data['status'] = 200;
    $data['html'] = $html;

}
if ($option == 'store_albums') {
    $db->where('id', $id, '<');
    if (!empty($user_id)) {
        $db->where('user_id', secure($_REQUEST['userID']));
    }
    if (IS_LOGGED) {
        $db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
    }
    $html = "";


    $db->where('price', 0, '>');

    $price_from = 0.99;
    $price_to = 40;
    $categories = '';
    if( isset($_REQUEST['params']) ){
        if( isset($_REQUEST['params']['price']) ){
            if( isset($_REQUEST['params']['price'][0]) ){
                $price_from = (int)$_REQUEST['params']['price'][0];
            }
            if( isset($_REQUEST['params']['price'][1]) ){
                $price_to = (int)$_REQUEST['params']['price'][1];
            }
            $db->where('price', Array ($price_from, $price_to), 'BETWEEN');
        }
        if( isset($_REQUEST['params']['geners']) && !empty($_REQUEST['params']['geners']) ){
            $categories = implode(',', $_REQUEST['params']['geners']);
            $db->where('category_id', $_REQUEST['params']['geners'], 'IN');
        }
        $data['price_from'] = $price_from;
        $data['price_to'] = $price_to;
        $data['categories'] = $categories;
    }

    $db->orderBy('id', 'DESC');
    $getAlbums = $db->get(T_ALBUMS, 10);
    if (!empty($getAlbums)) {
        $records = count($getAlbums);
        foreach ($getAlbums as $key => $album) {
            if (!empty($album)) {
                $publisher = userData($album->user_id);
                $html .= loadPage('store/albums', [
                    'id' => $album->id,
                    'album_id' => $album->album_id,
                    'user_id' => $album->user_id,
                    'artist' => $publisher->name,
                    'title' => $album->title,
                    'description' => $album->description,
                    'category_id' => $album->category_id,
                    'thumbnail' => getMedia($album->thumbnail),
                    'time' => $album->time,
                    'registered' => $album->registered,
                    'price' => $album->price
                ]);
            }
        }
    }
    $data['status'] = 200;
    $data['html'] = $html;
}
if ($option == 'store_songs') {
    $db->where('id', $id, '<');
    if (!empty($user_id)) {
        $db->where('user_id', secure($_REQUEST['userID']));
    }
    if (IS_LOGGED) {
        $db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
    }
    $html = "";
    $db->where('price', 0, '>');
    $db->where('album_id', 0);
    $db->where('id',$id,'<');

    $price_from = 0.99;
    $price_to = 40;
    $categories = '';
    if( isset($_REQUEST['params']) ){
        if( isset($_REQUEST['params']['price']) ){
            if( isset($_REQUEST['params']['price'][0]) ){
                $price_from = (int)$_REQUEST['params']['price'][0];
            }
            if( isset($_REQUEST['params']['price'][1]) ){
                $price_to = (int)$_REQUEST['params']['price'][1];
            }
            $db->where('price', Array ($price_from, $price_to), 'BETWEEN');
        }
        if( isset($_REQUEST['params']['geners']) && !empty($_REQUEST['params']['geners']) ){
            $categories = implode(',', $_REQUEST['params']['geners']);
            $db->where('category_id', $_REQUEST['params']['geners'], 'IN');
        }
        $data['price_from'] = $price_from;
        $data['price_to'] = $price_to;
        $data['categories'] = $categories;
    }

    $db->orderBy('id', 'DESC');
    $getSongs = $db->get(T_SONGS, 10);
    if (!empty($getSongs)) {
        $records = count($getSongs);
        foreach ($getSongs as $key => $song) {
            $songData = songData($song, false, false);
            if (!empty($songData)) {
                $music->songData = $songData;
                $html .= loadPage('store/song-list', [
                    't_thumbnail' => $songData->thumbnail,
                    't_id' => $songData->id,
                    't_title' => $songData->title,
                    't_artist' => $songData->publisher->name,
                    't_uartist' => $songData->publisher->username,
                    't_url' => $songData->url,
                    't_artist_url' => $songData->publisher->url,
                    't_price' => $songData->price,
                    't_audio_id' => $songData->audio_id,
                    't_duration' => $songData->duration,
                    't_posted' => $songData->time_formatted,
                    't_key' => ($key + 1)
                ]);
            }
        }
    }
    $data['status'] = 200;
    $data['html'] = $html;
}