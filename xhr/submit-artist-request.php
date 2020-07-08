<?php 
if (IS_LOGGED == false) {
    exit("You ain't logged in!");
}
if ($db->where('user_id', $user->id)->getValue(T_ARTIST_R, "count(*)") > 0) {
	$data['status'] = 400;
	$data['message'] = 'Your request has been already sent, we will get back to you shourtly.';
} else if ($user->artist == 1) {
	$data['status'] = 400;
	$data['message'] = 'You are already an artist!';
} else {
	if (empty($_POST['name']) || empty($_POST['category_id']) || empty($_FILES['passport']) || empty($_FILES['photo'])) {
		$data['status'] = 400;
	    $data['message'] = lang("Please check your details");
    } else {
        $name          = secure($_POST['name']);
        $details       = ($_POST['details']) ? secure($_POST['details']) : '';
        $category_id =  0;
        if (!empty($_POST['category_id'])) {
            if (in_array($_POST['category_id'], array_keys($categories))) {
                $category_id = secure($_POST['category_id']);
            }
        }
        $website = ($_POST['website']) ? secure($_POST['website']) : '';
        $create_data = [
            'name' => $name,
            'website' => $website,
            'details' => $details,
            'category_id' => $category_id,
            'user_id' => $user->id,
            'time' => time()
        ];
        if (!empty($_FILES['passport']['tmp_name'])) {
            $file_info = array(
                'file' => $_FILES['passport']['tmp_name'],
                'size' => $_FILES['passport']['size'],
                'name' => $_FILES['passport']['name'],
                'type' => $_FILES['passport']['type'],
                'allowed' => 'jpg,png,jpeg,gif'
            );
            $file_upload = shareFile($file_info);
            if (!empty($file_upload['filename'])) {
                $create_data['passport'] = $file_upload['filename'];
            }
        }
        if (!empty($_FILES['photo']['tmp_name'])) {
            $file_info = array(
                'file' => $_FILES['photo']['tmp_name'],
                'size' => $_FILES['photo']['size'],
                'name' => $_FILES['photo']['name'],
                'type' => $_FILES['photo']['type'],
                'allowed' => 'jpg,png,jpeg,gif'
            );
            $file_upload = shareFile($file_info);
            if (!empty($file_upload['filename'])) {
                $create_data['photo'] = $file_upload['filename'];
            }
        }
        if (empty($errors) && !empty($create_data['photo']) && !empty($create_data['passport'])) {
            if (isAdmin() || $user->id) {
                $insert = $db->insert(T_ARTIST_R, $create_data);
                if ($insert) {
                    $data = [
                        'status' => 200,
                    ];
                }
            }
        } else {
        	$data['status'] = 400;
        	$data['message'] = lang("Error found while processing your request, please try again later.");
        }
    }
}
?>