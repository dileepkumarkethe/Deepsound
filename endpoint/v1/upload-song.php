<?php 
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'Not logged in');
    echo json_encode($data);
    exit();
}

if ($music->config->go_pro == 'on') {
    if ($user->uploads >= ($music->config->pro_upload_limit * 1000 * 1000) && $user->is_pro == 0) {
        $data = array('status' => 400, 'error' => 'You exceed your upload limit.');
        echo json_encode($data);
        exit();
    }
}

if (!empty($_FILES['audio']['tmp_name'])) {
    if ($_FILES['audio']['size'] > $music->config->max_upload) {
        $max  = size_format($music->config->max_upload);
        $data = array('status' => 402,'error' => ("File is too big, Max upload size is" .": $max"));
        echo json_encode($data);
        exit();
    }

	$file_info = array(
        'file' => $_FILES['audio']['tmp_name'],
        'size' => $_FILES['audio']['size'],
        'name' => $_FILES['audio']['name'],
        'type' => $_FILES['audio']['type'],
        'allowed' => 'mp3,ogg,wav,mpeg'
    );
    if ($music->config->ffmpeg_system == "off") {
        $file_info['allowed'] = 'mp3';
    }
    $file_upload = shareFile($file_info);
    if (!empty($file_upload['filename'])) {
        $_SESSION['uploads'][] = $file_upload['filename'];
    	$data   = array('status' => 200, 'file_path' => $file_upload['filename'], 'file_name' => $file_upload['name']);
    } else if (!empty($file_upload['error'])) {
        $data = array('status' => 400, 'error' => $file_upload['error']);
    }
}else{
    $data = array('status' => 400, 'error' => "Please check your details");
    echo json_encode($data);
    exit();
}
?>