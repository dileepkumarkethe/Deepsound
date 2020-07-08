<?php  
$track_id = 0;
if (!empty($_GET['id'])) {
	$track_id = secure($_GET['id']);
}		
if (empty($track_id)) {
	exit("Invalid Track ID");
}	

$id = secure($_GET['id']);
$getSong = $db->where('audio_id', $id)->getOne(T_SONGS);
if (empty($getSong)) {
	exit("Invalid Track ID");
}


$data['status'] = 400;

if (IS_LOGGED == false) {
    $data['status'] = 300;
} else {
	$getLink = createPurchasePayPalLink($getSong);
	if (!empty($getLink)) {
		$data['status'] = 200;
		$data['url'] = $getLink['url'];
	}
}
?>