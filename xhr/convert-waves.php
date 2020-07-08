<?php
if (empty($_POST['darkImage']) || empty($_POST['LightImage']) || empty($_POST['id'])) {
	exit("Audio not found");
}

$audio_id = secure($_POST['id']);
$getIDAudio = $db->where('audio_id', $audio_id)->getValue(T_SONGS, 'id');

if (empty($getIDAudio)) {
	exit("Audio not found");
}

$darkImage = base64_decode(str_replace('data:image/png;base64,', '', $_POST['darkImage']));
$LightImage = base64_decode(str_replace('data:image/png;base64,', '', $_POST['LightImage']));

if (!file_exists('upload/waves/' . date('Y'))) {
    @mkdir('upload/waves/' . date('Y'), 0777, true);
}
if (!file_exists('upload/waves/' . date('Y') . '/' . date('m'))) {
    @mkdir('upload/waves/' . date('Y') . '/' . date('m'), 0777, true);
}

$darkImageFinal = 'upload/waves/' . date('Y') . '/' . date('m') . '/' . generateKey(40, 40) . '_dark.png';
$lightImageFinal = 'upload/waves/' . date('Y') . '/' . date('m') . '/' . generateKey(40, 40) . '_light.png';

$put1 = file_put_contents($darkImageFinal, $darkImage);
$put2 = file_put_contents($lightImageFinal, $LightImage);



if ($put1 && $put2 && file_exists($darkImageFinal) && file_exists($lightImageFinal)) {
	PT_UploadToS3($darkImageFinal);
    PT_UploadToS3($lightImageFinal);
	$update = $db->where('audio_id', $audio_id)->update(T_SONGS, ['dark_wave'=> $darkImageFinal, 'light_wave'=> $lightImageFinal]);
	if ($update) {
		$data['status'] = 200;
	}
}

?>
