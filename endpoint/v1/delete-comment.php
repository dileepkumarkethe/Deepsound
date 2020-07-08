<?php
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'Not logged in');
    echo json_encode($data);
    exit();
}

if (!empty($_POST['id'])) {
    if (!is_numeric($_POST['id'])) {
        $data = array('status' => 400, 'error' => 'Invalid Comment ID');
        echo json_encode($data);
        exit();
    }
}else{
    $data = array('status' => 400, 'error' => 'Invalid Comment ID');
    echo json_encode($data);
    exit();
}

$comment_id = secure($_POST['id']);
$getComment = getComment($comment_id);
if (empty($getComment)) {
    $data = array('status' => 400, 'error' => 'Invalid Comment ID');
    echo json_encode($data);
    exit();
}	

if ($getComment->user_id != $user->id && isAdmin() == false) {
    $data = array('status' => 400, 'error' => 'You can\'t delete this Comment');
    echo json_encode($data);
    exit();
}

$data['status'] = 400;


if ($db->where('id', $comment_id)->delete(T_COMMENTS)) {
	deleteActivity([
	    'user_id' => $user->id,
		'type' => 'commented_track',
		'track_id' => $getComment->track_id,
	]);
	$data['status'] = 200;
}

?>