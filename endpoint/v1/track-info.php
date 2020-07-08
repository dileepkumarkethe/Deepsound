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

$getSong = $db->where('id', $track_id)->getOne(T_SONGS);
if (empty($getSong)) {
    $data = array('status' => 400, 'error' => 'Invalid Track ID.');
    echo json_encode($data);
    exit();
}


$data['status'] = 400;
$data['data'] = songData($track_id);
$getSongComments = $db->where('track_id', $track_id)->orderBy('id', 'DESC')->get(T_COMMENTS, 10);
$data['data']->comments = [];
if (!empty($getSongComments)) {
    foreach ($getSongComments as $key => $comment) {
        $data['data']->comments[$key] = getComment($comment, false);
        $data['data']->comments[$key]->userData = userData($comment->user_id);
    }
}
$fingerPrint  = '';
if (!empty($_SESSION['fingerPrint'])) {
    $fingerPrint = secure($_SESSION['fingerPrint']);
} else {
    $fingerPrint = sha1(json_encode(sha1(time())));
    $_SESSION['fingerPrint'] = $fingerPrint;
}

$db->where('fingerprint', $fingerPrint)->where('track_id', $track_id);
$checkIfViewExits = $db->getValue(T_VIEWS, 'count(*)');
if (empty($checkIfViewExits)) {
    $insertArray = [
        'fingerprint' => secure($fingerPrint),
        'track_id' => $track_id,
        'time' => time()
    ];
    if (IS_LOGGED) {
        $insertArray['user_id'] = $user->id;
    }
    if (!empty($getSong->album_id)) {
        $insertArray['album_id'] = $getSong->album_id;
    }
    $db->insert(T_VIEWS, $insertArray);
}