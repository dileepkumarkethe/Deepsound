<?php  
if (IS_LOGGED == false) {
    exit("You ain't logged in!");
}

$track_id = 0;
if (!empty($_GET['id'])) {
	$track_id = secure($_GET['id']);
}		
if (empty($track_id)) {
	exit("Invalid Track ID");
}	

if (empty($_GET['type'])) {
	exit("Invalid type");
}	

$id = secure($_GET['id']);
$getSong = $db->where('audio_id', $id)->getOne(T_SONGS);
if (empty($getSong)) {
	exit("Invalid Track ID");
}


$data['status'] = 400;

if (!isLiked($getSong->id)) {

    $delete_like = $db->where('user_id', $user->id)->where('track_id', $getSong->id)->delete(T_DISLIKES);
    if ($delete_like) {
        $delete_notification = $db->where('notifier_id', $user->id)->where('recipient_id', $getSong->user_id)->where('type', 'disliked_track')->where('track_id', $getSong->id)->delete(T_NOTIFICATION);
        deleteActivity([
            'user_id' => $user->id,
            'type' => 'disliked_track',
            'track_id' => $getSong->id,
        ]);
    }


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
	}
}
?>