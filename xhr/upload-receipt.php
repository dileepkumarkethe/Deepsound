<?php
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'Not logged in');
    echo json_encode($data);
    exit();
}
if (!isset($_FILES) && empty($_FILES)) {
    $data = array('status' => 400, 'error' => 'Forbidden' );
    echo json_encode($data);
    exit();
}


if (!empty($_FILES['receipt_img']['tmp_name'])) {
    $file_info   = array(
        'file' => $_FILES['receipt_img']['tmp_name'],
        'size' => $_FILES['receipt_img']['size'],
        'name' => $_FILES['receipt_img']['name'],
        'type' => $_FILES['receipt_img']['type'],
        'crop' => array(
            'width' => 600,
            'height' => 600
        )
    );
    //$music->config->s3_upload = 'off';
    //$music->config->ftp_upload = 'off';
    $file_upload = shareFile($file_info);
    if (!empty($file_upload['filename'])) {
        $thumbnail = secure($file_upload['filename'], 0);
        $data = array('status' => 200, 'thumbnail' => $thumbnail, 'full_thumbnail' => getMedia($thumbnail));
        $info                  = array();
        $info[ 'user_id' ]     = $user->id;
        $info[ 'receipt_file' ]= $thumbnail;
        $info[ 'created_at' ]  = date('Y-m-d H:i:s');
        $info[ 'description' ] = (isset($_POST['description'])) ? Secure($_POST['description']) : '';
        $info[ 'price' ]       = (isset($_POST['price'])) ? Secure($_POST['price']) : '0';
        $info[ 'mode' ]        = (isset($_POST['mode'])) ? Secure($_POST['mode']) : '';
        $info[ 'track_id' ]    = (isset($_POST['track_id'])) ? Secure($_POST['track_id']) : '';
        $info[ 'approved' ]    = 0;
        $saved                 = $db->insert('bank_receipts', $info);

    }
}
