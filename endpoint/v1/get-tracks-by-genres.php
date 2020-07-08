<?php
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'Not logged in');
    echo json_encode($data);
    exit();
}

if (!empty($_POST['id'])) {
    if (!is_numeric($_POST['id'])) {
        $data = array('status' => 400, 'error' => 'Invalid genre ID');
        echo json_encode($data);
        exit();
    }
}else{
    $data = array('status' => 400, 'error' => 'Invalid genre ID');
    echo json_encode($data);
    exit();
}

$genre_id = secure($_POST['id']);
$limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
$offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;
$gettracks = GetTracksByGenres($genre_id,$limit,$offset);
if (empty($gettracks)) {
    $data = array('status' => 400, 'error' => 'Invalid genre ID');
    echo json_encode($data);
    exit();
}


    $data['status'] = 200;
    $data['tracks'] = $gettracks

?>