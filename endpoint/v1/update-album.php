<?php
if (IS_LOGGED == false) {
    $data = array(
        'status' => 400,
        'error' => 'Not logged in'
    );
    echo json_encode($data);
    exit();
}  else if (empty($_POST['song-id'])) { 
	$data = array(
        'status' => 400,
        'error' => 'Undefined album'
    );
    echo json_encode($data);
    exit();
}  else if (empty($db->where('album_id', secure($_POST['song-id']))->getValue(T_ALBUMS, 'count(*)'))) {
	$data = array(
        'status' => 400,
        'error' => 'Undefined album'
    );
    echo json_encode($data);
    exit();
} else {
	$getAlbum = $db->where('album_id', secure($_POST['song-id']))->getOne(T_ALBUMS);
    $request   = array();
    $request[] = (empty($_POST['title']) || empty($_POST['description']));
    if (in_array(true, $request)) {
        $error = "Please check your details";
    } else {
        $request   = array();
        if (!empty($_POST['song-thumbnail'])) {
        	$request[] = (!in_array($_POST['song-thumbnail'], $_SESSION['uploads']));
        }
        if (in_array(true, $request)) {
           $error = "Something went wrong Please try again later!";
        }
    }
    $songs = [];
    if( isset( $_SESSION['album_songs'] ) ) {
        if (is_array($_SESSION['album_songs'])) {
            foreach ($_SESSION['album_songs'] as $key => $audio_id) {
                $getSong = $db->where('audio_id', secure($audio_id))->getOne(T_SONGS);
                if (!empty($getSong)) {
                    $songs[] = $getSong->id;
                }
            }
        }
    }
    if (empty($error)) {
    	$thumbnail = $getAlbum->thumbnail;
        if (!empty($_POST['song-thumbnail'])) {
        	$thumbnail = secure($_POST['song-thumbnail'], 0);
        	if (file_exists($thumbnail)) {
        		unlink($getAlbum->thumbnail);
	            //$upload = PT_UploadToS3($thumbnail);
	        }
        }
        $category_id =  $getAlbum->category_id;
        $convert     = true;
        if (!empty($_POST['category_id'])) {
            if (in_array($_POST['category_id'], array_keys($categories))) {
                $category_id = secure($_POST['category_id']);
            }
        }
        $link_regex = '/(http\:\/\/|https\:\/\/|www\.)([^\ ]+)/i';
        $i          = 0;
        preg_match_all($link_regex, secure($_POST['description']), $matches);
        foreach ($matches[0] as $match) {
            $match_url            = strip_tags($match);
            $syntax               = '[a]' . urlencode($match_url) . '[/a]';
            $_POST['description'] = str_replace($match, $syntax, $_POST['description']);
        }
        $album_price = $getAlbum->price;
        if (isset($_POST['song-price'])) {
        	if (in_array($_POST['song-price'], $music->song_prices)) {
        		$album_price = secure($_POST['song-price']);
        	}
        }
        $data_insert = array(
            'title' => secure($_POST['title']),
            'description' => secure($_POST['description']),
            'category_id' => $category_id,
            'thumbnail' => $thumbnail,
            'price' => $album_price
        );
        if ($getAlbum->user_id == $user->id || isAdmin()) {
        	$update      = $db->where('id', $getAlbum->id)->update(T_ALBUMS, $data_insert);
        	if ($update) {
        		$data = array(
	                'status' => 200,
	                'url' => "album/$getAlbum->album_id"
	            );

	            foreach ($songs as $key => $song_id) {
	            	if (is_numeric($song_id)) {
	            		$db->where('id', $song_id)->update(T_SONGS, ['album_id' => $getAlbum->id]);
	            	}
	            }

	            $countSongs = $db->where('album_id', $getAlbum->id)->getValue(T_SONGS, 'count(*)');
	            if ($album_price > 0) {
	            	$album_price = number_format($album_price / $countSongs, 2);
	            }
	            $db->where('album_id', $getAlbum->id)->update(T_SONGS, ['price' => $album_price]);
        	}
        }
    } else {
        $data = array(
            'status' => 400,
            'error' => $error
        );
    }
}