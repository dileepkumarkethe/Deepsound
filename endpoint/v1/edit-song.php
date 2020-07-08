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
        'error' => 'Undefined song'
    );
    echo json_encode($data);
    exit();
}  else if (empty($db->where('audio_id', secure($_POST['song-id']))->getValue(T_SONGS, 'count(*)'))) {
	$data = array(
        'status' => 400,
        'error' => 'Undefined song'
    );
    echo json_encode($data);
    exit();
} else {
	$songData = $db->where('audio_id', secure($_POST['song-id']))->getOne(T_SONGS);
    $request   = array();
    $request[] = (empty($_POST['title']) || empty($_POST['description']));
    $request[] = (empty($_POST['tags']));
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
    if (empty($error)) {
    	$thumbnail = $songData->thumbnail;
        if (!empty($_POST['song-thumbnail'])) {
        	$thumbnail = secure($_POST['song-thumbnail'], 0);
        	if (file_exists($thumbnail)) {
        		unlink($songData->thumbnail);
	            //$upload = PT_UploadToS3($thumbnail);
	        }
        }
        $category_id =  $songData->category_id;
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
        $audio_privacy = $songData->availability;
        if (isset($_POST['privacy'])) {
            if (in_array($_POST['privacy'], array(0, 1))) {
                $audio_privacy = secure($_POST['privacy']);
            }
        }
        $age_restriction = $songData->age_restriction;
        if (isset($_POST['age_restriction'])) {
            if (in_array($_POST['age_restriction'], array(0, 1))) {
                $age_restriction = secure($_POST['age_restriction']);
            }
        }
        $song_price = $songData->price;
        if (isset($_POST['song-price'])) {
        	if (in_array($_POST['song-price'], ['0', '0.99', '1.99', '2.99'])) {
        		$song_price = secure($_POST['song-price']);
        	}
        }

        $spotlight = $songData->spotlight;
        if (!empty($_POST['spotlight']) && IsAdmin()) {
            if ($_POST['spotlight'] == 'yes') {
                $spotlight = 1;
            } else if ($_POST['spotlight'] == 'no') {
                $spotlight = 0;
            }
            if ($spotlight == $songData->spotlight) {
                $spotlight = $songData->spotlight;
            }
        }

        $data_insert = array(
            'title' => secure($_POST['title']),
            'description' => secure($_POST['description']),
            'tags' => secure(str_replace('#', '', $_POST['tags'])),
            'category_id' => $category_id,
            'thumbnail' => $thumbnail,
            'availability' => $audio_privacy,
            'age_restriction' => $age_restriction,
            'price' => $song_price,
            'spotlight' => $spotlight
        );
        if ($songData->user_id == $user->id || isAdmin()) {
        	$update      = $db->where('id', $songData->id)->update(T_SONGS, $data_insert);
        	if ($update) {
        		$data = array(
	                'status' => 200,
	                'link' => getLink("track/$songData->audio_id")
	            );
        	}
        }
    } else {
        $data = array(
            'status' => 400,
            'error' => $error
        );
    }
}