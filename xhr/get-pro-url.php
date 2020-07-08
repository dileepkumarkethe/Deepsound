<?php  
if (IS_LOGGED == false) {
    $data['status'] = 300;
} else {
	$getLink = createProPayPalLink();
	if (!empty($getLink['url'])) {
		$data['status'] = 200;
		$data['url'] = $getLink['url'];
	} else {
		$data['status'] = 400;
		$data['error'] = $getLink['details'];
	}
}
?>