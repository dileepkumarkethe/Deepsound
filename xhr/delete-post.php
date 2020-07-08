<?php  
if (IS_LOGGED == false) {
    exit("You ain't logged in!");
}

$activity_id = 0;
if (!empty($_GET['id'])) {
	if (is_numeric($_GET['id'])) {
		$activity_id = secure($_GET['id']);
    }
}		
if (empty($activity_id)) {
	exit("Invalid SONG ID");
}	

$getActivity = $db->where('id', $activity_id)->getOne(T_ACTIVITIES);
if (empty($getActivity)) {
	exit("Invalid Activity ID");
}	

if ($getActivity->user_id != $user->id && isAdmin() == false) {
	exit("You can't delete this song");
}

if ($getActivity->type == 'uploaded_track') {
	$deleteSong = deleteSong($getActivity->track_id);
}
$data['status'] = 400;
$deleteSong = $db->where('id', $activity_id)->delete(T_ACTIVITIES);
if ($deleteSong) {
	$data['status'] = 200;
}

?>