<?php  
if (IS_LOGGED == false) {
    exit("You ain't logged in!");
}

$track_id = 0;
if (!empty($_REQUEST['id'])) {
	$track_id = secure($_REQUEST['id']);
}		
if (empty($track_id)) {
	exit("Invalid Track ID");
}	

$id = secure($_REQUEST['id']);
$getSong = $db->where('id', $id)->getOne(T_SONGS);
if (empty($getSong)) {
	exit("Invalid Track ID");
}


$data['status'] = 400;

if (empty($_POST['description'])) {
	$errors[] = lang("Please describe your request.");
} else if (empty($_POST['confirm_2']) || empty($_POST['confirm_1'])) {
	$errors[] = lang("Please select the checkboxs below if you own the copyright.");
}
if (empty($errors)) {
	$description = secure($_POST['description']);
	$track_id = $track_id;
	$insert_copyright = $db->insert(T_COPYRIGHTS, ['track_id' => $track_id, 'description' => $description, 'time' => time(), 'user_id' => $user->id]);
	if ($insert_copyright) {
		$data['status'] = 200;
	}
} else {
	$data['status'] = 400;
	$data['errors'] = $errors;
}
?>