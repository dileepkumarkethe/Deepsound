<?php  
if (IS_LOGGED == false) {
    exit("You ain't logged in!");
}

$song_id = 0;
if (!empty($_GET['id'])) {
	if (is_numeric($_GET['id'])) {
		$song_id = secure($_GET['id']);
    }
}		
if (empty($song_id)) {
	exit("Invalid SONG ID");
}	

$getSong = songData($song_id);
if (empty($getSong)) {
	exit("Invalid SONG ID");
}	

if ($getSong->user_id != $user->id && isAdmin() == false) {
	exit("You can't delete this song");
}

$data['status'] = 400;

if (file_exists($getSong->audio_location_original)) {
	$size = filesize($getSong->audio_location_original);
	if ($getSong->publisher->uploads > 0) {
		$update = $db->where('id', $getSong->user_id)->update(T_USERS, ['uploads' => $db->dec($size)]);
	}
}

$deleteSong = deleteSong($song_id);
if ($deleteSong) {
	$data['status'] = 200;
}

?>