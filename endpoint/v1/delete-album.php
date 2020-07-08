<?php 
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'Not logged in');
    echo json_encode($data);
    exit();
}
if (empty($_REQUEST['type'])) {
    $data = array('status' => 400, 'error' => 'No type was sent.');
    echo json_encode($data);
    exit();
}

if (!empty($_REQUEST['id'])) {
	if (is_numeric($_REQUEST['id'])) {
		$_REQUEST['id'] = secure($_REQUEST['id']);
		$album = $db->where('id', $_REQUEST['id'])->getOne(T_ALBUMS);
		if (!empty($album)) {
		 	if (isAdmin() || $user->id == $album->user_id) {
           		$dalbum = $db->where('id', $album->id)->delete(T_ALBUMS);
           		$dalbum = $db->where('album_id', $album->id)->delete(T_VIEWS);
           		if ($_REQUEST['type'] == 'all') {
           			$getSongs = $db->where('album_id', $album->id)->get(T_SONGS);
           			foreach ($getSongs as $key => $song) {
           				deleteSong($song->id);
           			}
           		} else {
           			$update = $db->where('album_id', $album->id)->update(T_SONGS, ['album_id' => 0, 'price' => $album->price]);
           		}
	           	if ($dalbum) {
	           		$data['status'] = 200;
	           	}
           	}
		}
	}
}else{
    $data = array('status' => 400, 'error' => "Please check your details");
    echo json_encode($data);
    exit();
}

?>