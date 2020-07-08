<?php  
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
    echo json_encode($data);
    exit();
}

$song_id = 0;
if (!empty($_POST['id'])) {
	if (is_numeric($_POST['id'])) {
		$song_id = secure($_POST['id']);
    }
}		
if (empty($song_id)) {
    $data = array('status' => 400, 'error' => 'Invalid SONG ID');
    echo json_encode($data);
    exit();
}else{
    $getSong = songData($song_id);
    if (empty($getSong)) {
        $data = array('status' => 400, 'error' => 'Invalid SONG ID');
        echo json_encode($data);
        exit();
    }
    if ($getSong->user_id != $user->id && isAdmin() == false) {
        $data = array('status' => 400, 'error' => 'You can\'t delete this song');
        echo json_encode($data);
        exit();
    }
}



$data['status'] = 400;

if (file_exists($getSong->audio_location_original)) {
	$size = filesize($getSong->audio_location_original);
	if ($getSong->publisher->uploads > 0) {
		$update = $db->where('id', $getSong->user_id)->update(T_USERS, ['uploads' => $db->dec($size)]);
	}
}

$deleteSong = deleteSong($song_id);
if ($deleteSong) {
	$data['status'] = 200;
}

?>