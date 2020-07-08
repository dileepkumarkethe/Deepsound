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

$html = loadPage('modals/share-song', [
	't_title' => $getSong->title,
	's_artist' => $getSong->publisher->name,
	't_url' => urlencode($getSong->url),
	't_url_original' => $getSong->url,
	't_thumbnail' => $getSong->thumbnail,
	't_audio_id' => $getSong->audio_id,
    'songData' => $getSong
]);
$db->where('id', $getSong->id)->update(T_SONGS, ['shares' => ($getSong->shares + 1)]);
if ($html) {
	$data['status'] = 200;
	$data['html'] = $html;
}

?>