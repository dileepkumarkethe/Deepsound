<?php
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'Not logged in');
    echo json_encode($data);
    exit();
}
if (empty($_POST['id'])) {
    $data = array('status' => 400, 'error' => 'Invalid Track ID');
    echo json_encode($data);
    exit();
}
$id = secure($_POST['id']);
$getSong = $db->where('id', $id)->getOne(T_SONGS);
if (empty($getSong)) {
    $data = array('status' => 400, 'error' => 'Invalid Track ID');
    echo json_encode($data);
    exit();
}

$data['status'] = 200;
$limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
$offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;

$data['data'] = GetCommentByTrackid($id,$limit,$offset);