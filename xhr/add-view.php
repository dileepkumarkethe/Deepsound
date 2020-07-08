<?php 	

$track_id = 0;
if (!empty($_POST['id'])) {
	$track_id = secure($_POST['id']);
}		
if (empty($track_id)) {
	exit("Invalid Track ID");
}	


$id = secure($_POST['id']);
$getSong = $db->where('id', $_POST['id'])->getOne(T_SONGS);
if (empty($getSong)) {
	exit("Invalid Track ID");
}

if (empty($_POST['components'])) {
	$_POST['components'] = sha1(time());
}

$fingerPrint = sha1(json_encode($_POST['components']));

if (IS_LOGGED) {
	$db->where('track_id', $id)->where('user_id', $user->id)->delete(T_VIEWS);
}

$db->where('fingerprint', $fingerPrint)->where('track_id', $id);

if (IS_LOGGED) {
	$db->where('user_id', $user->id, '<>');
}
$checkIfViewExits = $db->getValue(T_VIEWS, 'count(*)');
if (empty($checkIfViewExits)) {
	$insertArray = [
		'fingerprint' => secure($fingerPrint),
		'track_id' => $id,
		'time' => time()
	];
	if (IS_LOGGED) {
		$insertArray['user_id'] = $user->id;
	}
	if (!empty($getSong->album_id)) {
		$insertArray['album_id'] = $getSong->album_id;
	}
	$addFingerPrint = $db->insert(T_VIEWS, $insertArray);
	if ($addFingerPrint) {
		$data['status'] = 200;
	}
} else {
	if (IS_LOGGED) {
		$updateArray = [
			'user_id' => $user->id,
			'time' => time()
		];
		if (!empty($getSong->album_id)) {
			$updateArray['album_id'] = $getSong->album_id;
		}
		$db->where('fingerprint', $fingerPrint)->where('track_id', $id)->update(T_VIEWS, $updateArray);
	}
	$data['status'] = 202;
}
?>