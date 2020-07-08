<?php 
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'Not logged in');
    echo json_encode($data);
    exit();
}

if ($music->config->go_pro == 'on') {
    if ($user->uploads >= ($music->config->pro_upload_limit * 1000 * 1000) && $user->is_pro == 0) {
        exit();
    }
}

$data = array();

if( !empty($_FILES['audio']) ){
    for ($i = 0 ; $i < count($_FILES['audio']) ; $i++){
        if (!empty($_FILES['audio']['tmp_name'][$i])) {
            if ($_FILES['audio']['size'][$i] > $music->config->max_upload) {
                $max  = size_format($music->config->max_upload);
                $data = array('status' => 402,'message' => (lang("File is too big, Max upload size is") .": $max"));
                echo json_encode($data);
                exit();
            }
            $file_info = array(
                'file' => $_FILES['audio']['tmp_name'][$i],
                'size' => $_FILES['audio']['size'][$i],
                'name' => $_FILES['audio']['name'][$i],
                'type' => $_FILES['audio']['type'][$i],
                'allowed' => 'mp3,ogg,wav,mpeg'
            );
            if ($music->config->ffmpeg_system == "off") {
                $file_info['allowed'] = 'mp3';
            }
            if ($music->config->ffmpeg_system == "on") {
                $music->config->s3_upload = 'off';
                $music->config->ftp_upload = 'off';
            }
            $file_upload = shareFile($file_info);
            if (!empty($file_upload['filename'])) {
                $_SESSION['uploads'][] = $file_upload['filename'];
                $data['status'] = 200;
                $data['file_path'][] = $file_upload['filename'];
                $data['file_name'][] = $file_upload['name'];
            } else if (!empty($file_upload['error'])) {
                $data = array('status' => 400, 'error' => $file_upload['error']);
            }
        }
    }
}


?>