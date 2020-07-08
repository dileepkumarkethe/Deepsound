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


$comment_id = secure($_REQUEST['id']);
$getComment = $db->where('id', $comment_id)->getOne(T_COMMENTS);
if (empty($getComment)) {
    $data = array('status' => 400, 'error' => 'Invalid Comment ID');
    echo json_encode($data);
    exit();
}

$data['status'] = 400;

if (empty($_POST['comment_description'])) {
    $errors[] = "Please describe your request.";
}

if (empty($errors)) {
    $description = secure($_POST['comment_description']);
    $insert_report = $db->insert(T_REPORTS, ['comment_id' => $comment_id, 'description' => $description, 'time' => time(), 'user_id' => $user->id]);
    if ($insert_report) {
        $data['status'] = 200;
    }
} else {
    $data['status'] = 400;
    $data['error'] = $errors;
}