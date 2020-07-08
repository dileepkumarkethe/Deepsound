<?php
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'Not logged in');
    echo json_encode($data);
    exit();
}

if (empty($_POST['file']) || empty($_POST['id']) ) {
    $data = array('status' => 400, 'error' => "Please check your details");
    echo json_encode($data);
    exit();
}

$request[] = (!in_array($_POST['file'], $_SESSION['uploads']));
$request[] = (!file_exists($_POST['file']));
if (in_array(true, $request)) {
   $errors[] = "Something went wrong Please try again later!";
} else {
	$ffmpeg_b                   = $music->config->ffmpeg_binary_file;
	$filepath                   = explode('.', $_POST['file'])[0];
	$time                       = time();
	$full_dir                   = str_replace('endpoint'.DIRECTORY_SEPARATOR.'v1', '', __DIR__);
	if (!file_exists('upload/waves/' . date('Y'))) {
        @mkdir('upload/waves/' . date('Y'), 0777, true);
    }
    if (!file_exists('upload/waves/' . date('Y') . '/' . date('m'))) {
        @mkdir('upload/waves/' . date('Y') . '/' . date('m'), 0777, true);
    }
	$audio_output_mp3 = $full_dir . DIRECTORY_SEPARATOR . str_replace('/',DIRECTORY_SEPARATOR,$filepath) . "_converted.mp3" ;
	
	$generateWaveLight = 'upload/waves/' . date('Y') . '/' . date('m') . '/' . generateKey(40, 40) . "_light.png";
	$generateWaveDark = 'upload/waves/' . date('Y') . '/' . date('m') . '/' . generateKey(40, 40) . "_dark.png";

	$audio_output_light_wave = $full_dir . $generateWaveLight;
	$audio_output_black_wave = $full_dir . $generateWaveDark;
	$audio_file_full_path = $full_dir . $_POST['file'];

	$shell     = shell_exec("$ffmpeg_b -i $audio_file_full_path -map 0:a:0 -b:a 96k $audio_output_mp3 2>&1");
	$shell     = shell_exec("$ffmpeg_b -y -i $audio_file_full_path -filter_complex \"aformat=channel_layouts=mono,showwavespic=s=1100x150:colors=#474747\" -frames:v 1 $audio_output_black_wave 2>&1");

	$shell     = shell_exec("$ffmpeg_b -y -i $audio_file_full_path -filter_complex \"aformat=channel_layouts=mono,showwavespic=s=1100x150:colors=#f98f1d\" -frames:v 1 $audio_output_light_wave 2>&1");
	//$upload_s3 = PT_UploadToS3($filepath . "_240p_converted.mp4");
	$db->where('audio_id', $_POST['id']);
	$update_data = array(
	    'audio_location' => $filepath . "_converted.mp3",
	);

	if (file_exists($generateWaveLight) && file_exists($generateWaveDark)) {
		$update_data['dark_wave'] = $generateWaveDark;
	    $update_data['light_wave'] = $generateWaveLight;
	}

	$db->update(T_SONGS, $update_data);

	if (file_exists($_POST['file'])) {
	    unlink($_POST['file']);
	}

	if (isset($_SESSION['uploads']) && !isset($_GET['reset'])) {
	    unset($_SESSION['uploads']);
	}

	if (file_exists($filepath . "_converted.mp3")) {
		$size = filesize($filepath . "_converted.mp3");
		$update = $db->where('id', $user->id)->update(T_USERS, ['uploads' => $db->inc($size)]);
	}
	if (!isset($_GET['reset'])) {
		$_SESSION['uploads'] = array();
	}

	$data = array('status' => 200, 'audio_file' => $audio_output_mp3);
}
?>