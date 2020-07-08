<?php  
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
    echo json_encode($data);
    exit();
}

$user_id = 0;
if (!empty($_POST['id'])) {
	if (is_numeric($_POST['id'])) {
		if ($_POST['id'] != $user->id && $_POST['id'] > 0) {
			$user_id = secure($_POST['id']);
        }
    }
}		
if (empty($user_id)) {
    $data = array('status' => 400, 'error' => 'Invalid User ID');
    echo json_encode($data);
    exit();
}	

$isBlocked = ($db->where('blocked_id', $music->user->id)->where('user_id', $user_id)->getValue(T_BLOCKS, 'count(*)') > 0);

if ($isBlocked) {
    $data = array('status' => 400, 'error' => 'You are blocked!');
    echo json_encode($data);
    exit();
}

$data['status'] = 400;

if ($option == 'add') {
	if (!isFollowing($user_id)) {
		$create_follow = $db->insert(T_FOLLOWERS, ['follower_id' => $user->id, 'following_id' => $user_id, 'time' => time()]);
		if ($create_follow) {
			$create_notification = createNotification([
				'notifier_id' => $user->id,
				'recipient_id' => $user_id,
				'type' => 'follow_user',
			]);
			$data['status'] = 200;
		}
	}else{
        $data = array('status' => 400, 'error' => 'You already follow this user!');
        echo json_encode($data);
        exit();
    }
}

if ($option == 'remove') {
	if (isFollowing($user_id)) {
		$delete_follow = $db->where('follower_id', $user->id)->where('following_id', $user_id)->delete(T_FOLLOWERS);
		if ($delete_follow) {
			$delete_notification = $db->where('notifier_id', $user->id)->where('recipient_id', $user_id)->where('type', 'follow_user')->delete(T_NOTIFICATION);
			$data['status'] = 200;
		}
	}
}
?>