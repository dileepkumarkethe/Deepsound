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

$id = secure($_POST['id']);
$getSong = $db->where('audio_id', $id)->getOne(T_SONGS);
if (empty($getSong)) {
    $data = array('status' => 400, 'error' => 'Invalid Track ID');
    echo json_encode($data);
    exit();
}

$data['status'] = 400;

if (!isLiked($getSong->id)) {
	$create_like = $db->insert(T_LIKES, ['user_id' => $user->id, 'track_id' => $getSong->id, 'time' => time()]);
	if ($create_like) {
		$create_notification = createNotification([
			'notifier_id' => $user->id,
			'recipient_id' => $getSong->user_id,
			'type' => 'liked_track',
			'track_id' => $getSong->id,
			'url' => "track/$getSong->audio_id"
		]);
		$create_activity = createActivity([
		    'user_id' => $user->id,
			'type' => 'liked_track',
			'track_id' => $getSong->id,
		]);
		$data['status'] = 200;
        $data['mode'] = 'liked';
	}
} else {
	$delete_like = $db->where('user_id', $user->id)->where('track_id', $getSong->id)->delete(T_LIKES);
	if ($delete_like) {
		$delete_notification = $db->where('notifier_id', $user->id)->where('recipient_id', $getSong->user_id)->where('type', 'liked_track')->where('track_id', $getSong->id)->delete(T_NOTIFICATION);
		deleteActivity([
		    'user_id' => $user->id,
			'type' => 'liked_track',
			'track_id' => $getSong->id,
		]);
		$data['status'] = 300;
        $data['mode'] = 'disliked';
	}
}
?>