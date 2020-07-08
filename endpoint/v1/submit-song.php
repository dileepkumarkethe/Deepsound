<?php
require 'assets/import/getID3-1.9.14/getid3/getid3.php';

if (IS_LOGGED == false) {
    $data = array(
        'status' => 400,
        'error' => 'Not logged in'
    );
    echo json_encode($data);
    exit();
}  else {
    $getID3    = new getID3;
    $featured  = ($user->is_pro == 1) ? 1 : 0;
    $filesize  = 0;
    $error     = false;
    $request   = array();
    $request[] = (empty($_POST['title']) || empty($_POST['description']));
    $request[] = (empty($_POST['tags']) || empty($_POST['song-thumbnail']));
    if (in_array(true, $request)) {
        $error = "Please check your details";
    } else if (empty($_POST['song-location'])) {
        $error = "Audio file not found, please refresh the page and try again.";
    } else {
        $request   = array();
        $request[] = (!in_array($_POST['song-location'], $_SESSION['uploads']));
        $request[] = (!in_array($_POST['song-thumbnail'], $_SESSION['uploads']));
        $request[] = (!file_exists($_POST['song-location']));
        if (in_array(true, $request)) {
           $error = "Something went wrong Please try again later!";
        }
    }
    if (empty($error)) {
        $file     = $getID3->analyze($_POST['song-location']);
        $duration = '00:00';
        if (!empty($file['playtime_string'])) {
            $duration = secure($file['playtime_string']);
        }
        if (!empty($file['filesize'])) {
            $filesize = $file['filesize'];
        }
        $audio_id        = generateKey(15, 15);
        $check_for_audio = $db->where('audio_id', $audio_id)->getValue(T_SONGS, 'count(*)');
        if ($check_for_audio > 0) {
            $audio_id = generateKey(15, 15);
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
        $audio_privacy = 0;
        if (!empty($_POST['privacy'])) {
            if (in_array($_POST['privacy'], array(0, 1))) {
                $audio_privacy = secure($_POST['privacy']);
            }
        }
        $age_restriction = 0;
        if (!empty($_POST['age_restriction'])) {
            if (in_array($_POST['age_restriction'], array(0, 1))) {
                $age_restriction = secure($_POST['age_restriction']);
            }
        }
        $song_price = 0;
        if (isset($_POST['song-price'])) {
            $song_price = secure($_POST['song-price']);
        }
        $album_id = 0;
        if (isset($_POST['album_id']) && !empty($_POST['album_id'])) {
            $album_id = (int)secure($_POST['album_id']);
        }
        $data_insert = array(
            'audio_id' => $audio_id,
            'user_id' => $user->id,
            'title' => secure($_POST['title']),
            'description' => secure($_POST['description']),
            'tags' => secure(str_replace('#', '', $_POST['tags'])),
            'duration' => $duration,
            'audio_location' => '',
            'category_id' => $category_id,
            'thumbnail' => $thumbnail,
            'time' => time(),
            'registered' => date('Y') . '/' . intval(date('m')),
            'size' => $filesize,
            'availability' => $audio_privacy,
            'age_restriction' => $age_restriction,
            'price' => $song_price,
            'spotlight' => $featured,
            'album_id' => $album_id
        );
        if ($music->config->ffmpeg_system == "off" && in_array($_POST['song-location'], $_SESSION['uploads'])) {
        	$data_insert['audio_location'] = secure($_POST['song-location']);
        }
        $insert      = $db->insert(T_SONGS, $data_insert);
        if ($insert) {
            $create_activity = createActivity([
                'user_id' => $user->id,
                'type' => 'uploaded_track',
                'track_id' => $insert,
            ]);
            $delete_files = array();
            $data = array(
                'status' => 200,
                'audio_id' => $audio_id,
                'track_data' => songData($insert),
                'song_location' => $_POST['song-location'],
                'link' => getLink("track/$audio_id")
            );
            $_SESSION['album_songs'][] = $audio_id;
        }
    } else {
        $data = array(
            'status' => 400,
            'error' => $error
        );
    }
}