<?php 
if (empty($_POST['file']) || empty($_POST['id']) || IS_LOGGED == false) {
	exit();
}

$request[] = (!in_array($_POST['file'], $_SESSION['uploads']));
$request[] = (!file_exists($_POST['file']));
if (in_array(true, $request)) {
   $errors[] = lang("Something went wrong Please try again later!");
} else {
	$ffmpeg_b                   = $music->config->ffmpeg_binary_file;
	$filepath                   = explode('.', $_POST['file'])[0];
	$time                       = time();
	$full_dir                   = str_replace('xhr', '/', __DIR__);
	if (!file_exists('upload/waves/' . date('Y'))) {
        @mkdir('upload/waves/' . date('Y'), 0777, true);
    }
    if (!file_exists('upload/waves/' . date('Y') . '/' . date('m'))) {
        @mkdir('upload/waves/' . date('Y') . '/' . date('m'), 0777, true);
    }
    $demoURL = $filepath . "_" . rand(11111, 99999) . "_converted.mp3";
    $originalURL = $filepath . "_" . rand(11111, 99999) . "_converted.mp3";

	$audio_output_mp3 = $full_dir . $originalURL;
    $audio_demo_output_mp3 = $full_dir . $demoURL;

    $key = generateKey(40, 40);
	$generateWaveLight = 'upload/waves/' . date('Y') . '/' . date('m') . '/' . $key . "_light.png";
	$generateWaveDark = 'upload/waves/' . date('Y') . '/' . date('m') . '/' . $key . "_dark.png";
    $generateWaveDay = 'upload/waves/' . date('Y') . '/' . date('m') . '/' . $key . "_day.png";

    $generateBuyerWaveLight = 'upload/waves/' . date('Y') . '/' . date('m') . '/' . $key . "_demo_light.png";
	$generateBuyerWaveDark = 'upload/waves/' . date('Y') . '/' . date('m') . '/' . $key . "_demo_dark.png";
    $generateBuyerWaveDay = 'upload/waves/' . date('Y') . '/' . date('m') . '/' . $key . "_demo_day.png";

	$audio_output_light_wave = $full_dir . $generateWaveLight;
	$audio_output_black_wave = $full_dir . $generateWaveDark;
    $audio_output_day_wave = $full_dir . $generateWaveDay;

    $audio_output_demo_light_wave = $full_dir . $generateBuyerWaveLight;
	$audio_output_demo_black_wave = $full_dir . $generateBuyerWaveDark;
    $audio_output_demo_day_wave = $full_dir . $generateBuyerWaveDay;

	$audio_file_full_path = $full_dir . $_POST['file'];

    $wavecolor                   = $music->config->waves_color;

    $there_is_demo = false;

	$shell     = shell_exec("$ffmpeg_b -i $audio_file_full_path -map 0:a:0 -b:a 192k $audio_output_mp3 2>&1");
	$shell_get_time     = shell_exec("$ffmpeg_b -v quiet -stats -i $audio_output_mp3 -f null - 2>&1");
	if (!empty($shell_get_time)) {
        $whatIWant = substr($shell_get_time, strpos($shell_get_time, "time=") + 5);
        $timeOfAudioFile = substr($whatIWant, 0, strpos($whatIWant, " "));
        $seconds = strtotime("1970-01-01 $timeOfAudioFile UTC");
		$percentage = 25;
		$new_duration = ($percentage / 100) * $seconds;
		if (!empty($new_duration)) {
			$shell_make_demo     = shell_exec("$ffmpeg_b -t $new_duration -i $audio_output_mp3 -acodec copy $audio_demo_output_mp3 2>&1");
			$there_is_demo = true;
		}
	}

	$shell     = shell_exec("$ffmpeg_b -y -i $audio_output_mp3 -filter_complex \"aformat=channel_layouts=mono,showwavespic=s=1100x150:colors=#474747\" -frames:v 1 $audio_output_black_wave 2>&1");

	$shell     = shell_exec("$ffmpeg_b -y -i $audio_output_mp3 -filter_complex \"aformat=channel_layouts=mono,showwavespic=s=1100x150:colors=".$wavecolor."\" -frames:v 1 $audio_output_light_wave 2>&1");
    $shell     = shell_exec("$ffmpeg_b -y -i $audio_output_mp3 -filter_complex \"aformat=channel_layouts=mono,showwavespic=s=1100x150:colors=#bfbfbf\" -frames:v 1 $audio_output_day_wave 2>&1");

    $shell     = shell_exec("$ffmpeg_b -y -i $audio_demo_output_mp3 -filter_complex \"aformat=channel_layouts=mono,showwavespic=s=1100x150:colors=#474747\" -frames:v 1 $audio_output_demo_black_wave 2>&1");

	$shell     = shell_exec("$ffmpeg_b -y -i $audio_demo_output_mp3 -filter_complex \"aformat=channel_layouts=mono,showwavespic=s=1100x150:colors=".$wavecolor."\" -frames:v 1 $audio_output_demo_light_wave 2>&1");
    $shell     = shell_exec("$ffmpeg_b -y -i $audio_demo_output_mp3 -filter_complex \"aformat=channel_layouts=mono,showwavespic=s=1100x150:colors=#bfbfbf\" -frames:v 1 $audio_output_demo_day_wave 2>&1");

    //$upload_s3 = PT_UploadToS3($filepath . "_240p_converted.mp4");
	$db->where('audio_id', $_POST['id']);
	$update_data = array(
	    'audio_location' => $originalURL,
	);

	if (file_exists($generateWaveLight) && file_exists($generateWaveDark)) {
		$update_data['dark_wave'] = $generateWaveDark;
	    $update_data['light_wave'] = $generateWaveLight;
	}

	if ($there_is_demo == true) {
		$update_data['demo_track'] = $demoURL;
		$hours = floor($new_duration / 3600);
		$mins = floor($new_duration / 60 % 60);
		$secs = floor($new_duration % 60);
		$timeFormat = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
		$update_data['demo_duration'] = $timeFormat;
		PT_UploadToS3($demoURL);
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
	PT_UploadToS3($generateWaveLight);
    PT_UploadToS3($generateWaveDark);
    PT_UploadToS3($generateWaveDay);
    PT_UploadToS3($generateBuyerWaveLight);
    PT_UploadToS3($generateBuyerWaveDark);
    PT_UploadToS3($generateBuyerWaveDay);
    PT_UploadToS3($originalURL);
	if (!isset($_GET['reset'])) {
		$_SESSION['uploads'] = array();
	}

	$data = array('status' => 200, 'audio_file' => $audio_output_mp3);
}
?>