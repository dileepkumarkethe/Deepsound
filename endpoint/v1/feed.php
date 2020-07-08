<?php
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
    echo json_encode($data);
    exit();
}

if (!isset($_POST['id'])) {
    $data = array('status' => 400, 'error' => 'Invalid User ID');
    echo json_encode($data);
    exit();
}
if (empty($_POST['id'])) {
    $data = array('status' => 400, 'error' => 'Invalid User ID');
    echo json_encode($data);
    exit();
}
$user_id = (int)Secure($_POST['id']);
$getUser = $db->where('id', $user_id)->getOne(T_USERS);
if (empty($getUser)) {
    $data = array('status' => 400, 'error' => 'Invalid User ID.');
    echo json_encode($data);
    exit();
}

$feeds              = [];
$limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
$offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;

$getActivties = getActivties($limit, $offset, $user_id);
if (!empty($getActivties)) {
    foreach ($getActivties as $key => $activity) {
        $feeds[] = getActivity($activity, false);
    }
}

$data['status'] = 200;
$data['data'] = $feeds;