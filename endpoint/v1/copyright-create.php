<?php
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'Not logged in');
    echo json_encode($data);
    exit();
}

if (empty($_REQUEST['id'])) {
    $data = array('status' => 400, 'error' => 'Invalid Track ID');
    echo json_encode($data);
    exit();
}

$id = secure($_REQUEST['id']);
$getSong = $db->where('id', $id)->getOne(T_SONGS);
if (empty($getSong)) {
    $data = array('status' => 400, 'error' => 'Invalid Track ID');
    echo json_encode($data);
    exit();
}

$data['status'] = 400;

if (empty($_POST['description'])) {
	$errors[] = "Please describe your request.";
}
if (empty($errors)) {
	$description = secure($_POST['description']);
	$insert_copyright = $db->insert(T_COPYRIGHTS, ['track_id' => $id, 'description' => $description, 'time' => time(), 'user_id' => $user->id]);
	if ($insert_copyright) {
		$data['status'] = 200;
	}
} else {
	$data['status'] = 400;
	$data['error'] = $errors;
}
?>