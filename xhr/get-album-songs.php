<?php 
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
		 				$final_ids[] = $songData->audio_id;
		 			}
		 		}
		 	    $data['status'] = 200;
		 	    $data['songs'] = array_reverse($final_ids);
		 	}
		}
	}
}
?>