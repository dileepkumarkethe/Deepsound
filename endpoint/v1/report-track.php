<?php
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'Not logged in');
    echo json_encode($data);
    exit();
}
if (!empty($_POST['id'])) {
    if (!is_numeric($_POST['id'])) {
        $data = array('status' => 400, 'error' => 'Invalid Track ID');
        echo json_encode($data);
        exit();
    }
}else{
    $data = array('status' => 400, 'error' => 'Invalid Track ID');
    echo json_encode($data);
    exit();
}

$track_id = secure($_REQUEST['id']);

$getTrack = $db->where('id', $track_id)->getOne(T_SONGS);
if (empty($getTrack)) {
    exit("Invalid Track ID");
}

$data['status'] = 400;

if (empty($_POST['track_description'])) {
    $errors[] = "Please describe your request.";
}

if (empty($errors)) {
    $description = secure($_POST['track_description']);
    $insert_report = $db->insert(T_REPORTS, ['track_id' => $track_id, 'description' => $description, 'time' => time(), 'user_id' => $user->id]);
    if ($insert_report) {
        $data['status'] = 200;
    }
} else {
    $data['status'] = 400;
    $data['error'] = $errors;
}