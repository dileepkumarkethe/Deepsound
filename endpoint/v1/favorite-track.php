<?php  
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
    echo json_encode($data);
    exit();
}

$track_id = 0;
if (!empty($_POST['id'])) {
	$track_id = secure($_POST['id']);
}		
if (empty($track_id)) {
    $data = array('status' => 400, 'error' => 'Invalid Track ID');
    echo json_encode($data);
    exit();
}	

$getSong = $db->where('audio_id', $track_id)->getOne(T_SONGS);
if (empty($getSong)) {
    $data = array('status' => 400, 'error' => 'Invalid Track ID');
    echo json_encode($data);
    exit();
}


$data['status'] = 400;

if (!isFavorated($getSong->id)) {
	$create_fav = $db->insert(T_FOV, ['user_id' => $user->id, 'track_id' => $getSong->id, 'time' => time()]);
	if ($create_fav) {
		$data['status'] = 200;
        $data['mode'] = 'Add to favorite';
	}
} else {
	$delete_fav = $db->where('user_id', $user->id)->where('track_id', $getSong->id)->delete(T_FOV);
	if ($delete_fav) {
		$data['status'] = 200;
        $data['mode'] = 'Remove from favorite';
	}
}
?>