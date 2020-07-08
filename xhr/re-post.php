<?php  
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

$data['status'] = 400;

$create_activity = createActivity([
    'user_id' => $user->id,
	'type' => 'shared_track',
	'track_id' => $getSong->id,
]);

if ($create_activity) {
	$data['status'] = 200;
}
?>