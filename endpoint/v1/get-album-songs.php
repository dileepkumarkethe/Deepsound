<?php
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
    echo json_encode($data);
    exit();
}
if (!empty($_REQUEST['id'])) {
	if (is_numeric($_REQUEST['id'])) {
		$getAlbum = $db->where('id', secure($_REQUEST['id']))->getOne(T_ALBUMS);
		if (!empty($getAlbum)) {
		 	$getAlbumSongs = $db->where('album_id', $getAlbum->id)->get(T_SONGS, null, ['id']);
		 	$final_ids = [];
		 	if (!empty($getAlbumSongs)) {
		 		foreach ($getAlbumSongs as $key => $song) {
		 			$songData = songData($song->id);
		 			if (!empty($songData)) {
		 				$final_ids[] = $songData;
		 			}
		 		}
		 	    $data['status'] = 200;
		 	    $data['songs'] = array_reverse($final_ids);
		 	}
		}
	}
}else{
    $data = array('status' => 400, 'error' => "Please check your details");
    echo json_encode($data);
    exit();
}
?>