<?php
if (IS_LOGGED == false) {
    $data = array(
        'status' => 400,
        'error' => 'Not logged in'
    );
    echo json_encode($data);
    exit();
}  else {
    $featured  = ($user->is_pro == 1) ? 1 : 0;
    $error     = false;
    $request   = array();
    $request[] = (empty($_POST['title']) || empty($_POST['description']));
    $request[] = (empty($_POST['song-thumbnail']));
    //$request[] = (empty($_SESSION['album_songs']));
    if (in_array(true, $request)) {
        $error = "Please check your details";
    } else {
        $request   = array();
        if (!in_array($_POST['song-thumbnail'], array_values($_SESSION['uploads']))) {
           $error = "Something went wrong Please try again later!";
        }
    }
    $songs = [];
//    if (is_array($_SESSION['album_songs'])) {
//    	foreach ($_SESSION['album_songs'] as $key => $audio_id) {
//    		$getSong = $db->where('audio_id', secure($audio_id))->getOne(T_SONGS);
//    		if (!empty($getSong)) {
//    			$songs[] = $getSong->id;
//    		}
//    	}
//    } else {
//    	$error = "No songs found, add new songs.";
//    }
    if (empty($error)) {
        $album_id        = generateKey(15, 15);
        $check_for_album = $db->where('album_id', $album_id)->getValue(T_ALBUMS, 'count(*)');
        if ($check_for_album > 0) {
            $album_id = generateKey(15, 15);
        }
        $thumbnail = secure($_POST['song-thumbnail'], 0);
        if (file_exists($thumbnail)) {
            //$upload = PT_UploadToS3($thumbnail);
        }
        $category_id = 0;
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
        $album_price = 0;
        if (isset($_POST['song-price'])) {
        	if (in_array($_POST['song-price'], $music->song_prices)) {
        		$album_price = secure($_POST['song-price']);
        	}
        }
        $data_insert = array(
            'album_id' => $album_id,
            'user_id' => $user->id,
            'title' => secure($_POST['title']),
            'description' => secure($_POST['description']),
            'category_id' => $category_id,
            'thumbnail' => $thumbnail,
            'time' => time(),
            'registered' => date('Y') . '/' . intval(date('m')),
            'price' => $album_price
        );
        $insert      = $db->insert(T_ALBUMS, $data_insert);
        if ($insert) {
            $countSongs = count($songs);
            if ($album_price > 0) {
            	$album_price = number_format($album_price / $countSongs, 2);
            }
//            foreach ($songs as $key => $song_id) {
//            	if (is_numeric($song_id)) {
//            		$db->where('id', $song_id)->update(T_SONGS, ['album_id' => $insert, 'price' => $album_price]);
//            	}
//            }
            $data = array(
                'status' => 200,
                'album_id' => $album_id,
                'album_data' => albumData($insert),
                'url' => "album/$album_id"
            );
            $_SESSION['album_songs'] = array();
            $_SESSION['uploads'] = array();
        }
    } else {
        $data = array(
            'status' => 400,
            'error' => $error
        );
    }
}