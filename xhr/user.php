<?php 
if (IS_LOGGED == false) {
    exit("You ain't logged in!");
}
if ($option == 'general' || $option == 'profile' || $option == 'password' || $option == 'delete') {
    if (empty($_POST['user_id']) || !is_numeric($_POST['user_id']) || $_POST['user_id'] == 0) {
        exit("Invalid user ID");
    } else {
        $userData = userData($_POST['user_id']);
    }
}
if ($option == 'request-withdrawal') {

    $error    = null;
    $balance  = $music->user->balance;
    $user_id  = $music->user->id;
    $currency = $music->config->currency;

    // Check is unprocessed requests exits
    $db->where('user_id',$user_id);
    $db->where('status',0);
    $requests = $db->getValue(T_WITHDRAWAL_REQUESTS, 'count(*)');

    if (!empty($requests)) {
        $error = lang('You can not submit withdrawal request until the previous requests has been approved / rejected');
    }

    else if ($music->user->balance < $_POST['amount']) {
        $error = lang("The amount exceeded your current balance.");
    } else if (50 > $_POST['amount']) {
        $error = lang("Minimum amount required is 50.");
    }

    else{

        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $error = lang("Please check your details");
        }

        else if(empty($_POST['amount']) || !is_numeric($_POST['amount'])){
            $error = lang("Please check your details");
        }

        else if($_POST['amount'] < 5){
            $error = lang('The minimum withdrawal request is 50:') . " $currency";
        }
    }

    if (empty($error)) {
        $insert_data    = array(
            'user_id'   => $user_id,
            'amount'    => Secure($_POST['amount']),
            'email'     => Secure($_POST['email']),
            'requested' => time(),
            'currency' => $currency,
        );

        $insert  = $db->insert(T_WITHDRAWAL_REQUESTS,$insert_data);
        if (!empty($insert)) {
            $data['status']  = 200;
            $data['message'] = lang('Your withdrawal request has been successfully sent!');
        }
    }

    else{
        $data['status']  = 400;
        $data['message'] = $error;
    }
}
if ($option == 'delete') {
    if( $music->config->delete_account == 'off' ){
        exit("You can not delete this account");
    }
    if (empty($_POST['c_pass'])) {
        $errors[] = lang("Please check your details");
    } else {
        $c_pass      = secure($_POST['c_pass']);

        if (!password_verify($c_pass, $db->where('id', $userData->id)->getValue(T_USERS, 'password'))) {
            $errors[] = lang("Your current password is invalid");
        } 
        if (empty($errors)) {
            if (isAdmin() || $userData->id == $user->id) {
                $delete = deleteUser($userData->id);
                if ($delete) {
                    $data = [
                        'status' => 200,
                        'message' => lang("Your account was successfully deleted, please wait..")
                    ];
                }
            }
        }
    }
}
if ($option == 'password') {
    if (empty($_POST['c_pass']) || empty($_POST['n_pass']) || empty($_POST['rn_pass'])) {
        $errors[] = lang("Please check your details");
    } else {
        $c_pass      = secure($_POST['c_pass']);
        $n_pass      = secure($_POST['n_pass']);
        $rn_pass     = secure($_POST['rn_pass']);
        if (!password_verify($c_pass, $db->where('id', $userData->id)->getValue(T_USERS, 'password'))) {
            $errors[] = lang("Your current password is invalid");
        } else if ($n_pass != $rn_pass) {
            $errors[] = lang("Passwords don't match");
        } else if (strlen($n_pass) < 4 || strlen($n_pass) > 32) {
            $errors[] = lang("New password is too short");
        }
        if (empty($errors)) {
            $update_data = [
                'password' => password_hash($n_pass, PASSWORD_DEFAULT),
            ];

            if (isAdmin() || $userData->id == $user->id) {
                $update = $db->where('id', $userData->id)->update(T_USERS, $update_data);
                if ($update) {
                    $delete = $db->where('user_id', $user->id)->where('session_id', $session_id, '<>')->delete(T_SESSIONS);
                    $data = [
                        'status' => 200,
                        'message' => lang("Your password was successfully updated!")
                    ];
                }
            }
        }
    }
}
if ($option == 'profile') {
    $name                 = secure($_POST['name']);
    $about_me             = secure($_POST['about_me']);
    $facebook             = secure($_POST['facebook']);
    $website              = secure($_POST['website']);
    if (!empty($website)) {
        if (!filter_var($_POST['website'], FILTER_VALIDATE_URL)) {
            $errors[] = lang("Invalid website url, format allowed: http(s)://*.*/*");
        }
    }
    if (!empty($facebook)) {
        if (filter_var($_POST['facebook'], FILTER_VALIDATE_URL)) {
            $errors[] = lang("Invalid facebook username, urls are not allowed");
        }
    }
    if (empty($errors)) {
        $update_data = [
            'name' => $name,
            'about' => $about_me,
            'facebook' => $facebook,
            'website' => $website,
        ];

        if (isAdmin() || $userData->id == $user->id) {
            $update = $db->where('id', $userData->id)->update(T_USERS, $update_data);
            if ($update) {
                $data = [
                    'status' => 200,
                    'message' => lang("Profile successfully updated!")
                ];
            }
        }
    }
}
if ($option == 'hide-announcement') {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        $errors[] = lang("Please check your details");
    } else {
        $request        = (!empty($_POST['id']) && is_numeric($_POST['id']));
        $data['status'] = 400;
        if ($request === true) {
            $announcement_id = secure($_POST['id']);
            $user_id         = $music->user->id;
            $insert_data     = array(
                'announcement_id' => $announcement_id,
                'user_id'         => $user_id
            );

            $db->insert(T_ANNOUNCEMENT_VIEWS,$insert_data);
            $data['status'] = 200;
        }
    }
}
if ($option == 'interest') {
    if (!isset($_POST['genres']) || empty($_POST['genres'])) {
        $errors[] = lang("Please check your details");
    } else {
        $genres = secure($_POST['genres']);
        $arr = explode(',',$genres);
        $insert = false;
        if(!empty($arr)){
            foreach ($arr as $key){
                $is_exist = $db->where('user_id', $music->user->id)->where('category_id', $key)->getOne(T_USER_INTEREST,'count(id) as cnt')->cnt;
                if($is_exist == 0) {
                    $insert = $db->insert(T_USER_INTEREST, array('user_id' => $music->user->id, 'category_id' => $key));
                }
            }
            if($insert){
                $data = [
                    'status' => 200,
                    'message' => lang("Profile successfully updated!")
                ];
            }else{
                $errors[] = lang("Please check your details");
            }
        }else{
            $errors[] = lang("Please check your details");
        }
    }
}
if ($option == 'update-interest') {
    if (!isset($_POST['genres']) || empty($_POST['genres'])) {
        $errors[] = lang("Please check your details");
    } else {
        $genres = secure($_POST['genres']);
        $arr = explode(',',$genres);
        $insert = false;
        $db->where('user_id', $music->user->id)->delete(T_USER_INTEREST);
        if(!empty($arr)){
            foreach ($arr as $key){
                $insert = $db->insert(T_USER_INTEREST, array('user_id' => $music->user->id, 'category_id' => $key));
            }
            if($insert){
                $data = [
                    'status' => 200,
                    'message' => lang("Profile successfully updated!")
                ];
            }else{
                $errors[] = lang("Please check your details");
            }
        }else{
            $errors[] = lang("Please check your details");
        }
    }
}
if ($option == 'general') {
    if (empty($_POST['username']) || empty($_POST['email'])) {
        $errors[] = lang("Please check your details");
    } else {
        $username          = secure($_POST['username']);
        $email             = secure($_POST['email']);
        if (UsernameExits($_POST['username']) && $_POST['username'] != $userData->username) {
            $errors[] = lang("This username is already taken");
        }
        if (strlen($_POST['username']) < 4 || strlen($_POST['username']) > 32) {
            $errors[] = lang("Username length must be between 5 / 32");
        }
        if (!preg_match('/^[\w]+$/', $_POST['username'])) {
            $errors[] = lang("Invalid username characters");
        }
        if (in_array($_POST['username'],$music->disallowed_usernames)){
            $errors[] = lang("This username is disallowed");
        }
        if (EmailExists($_POST['email']) && $_POST['email'] != $userData->email) {
            $errors[] = lang("This e-mail is already taken");
        }
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = lang("This e-mail is invalid");
        }
        $country = $userData->country_id;
        if (in_array($_POST['country'], array_keys($countries_name))) {
            $country = secure($_POST['country']);
        }

        $gender = $userData->gender;
        if (in_array($_POST['gender'], ['male', 'female'])) {
            $gender = secure($_POST['gender']);
        }

        $age = $userData->age;
        if (is_numeric($_POST['age']) && ($_POST['age'] <= 100 || $_POST['age'] >= 0)) {
            $age = secure($_POST['age']);
        }

        $ispro = $userData->is_pro;
        if (!empty($_POST['ispro']) && IsAdmin()) {
            if ($_POST['ispro'] == 'yes') {
                $ispro = 1;
            } else if ($_POST['ispro'] == 'no') {
                $ispro = 0;
            }
            if ($ispro == $userData->is_pro) {
                $ispro = $userData->is_pro;
            }
        }

        $verified = $userData->verified;
        if (!empty($_POST['verified']) && IsAdmin()) {
            if ($_POST['verified'] == 'yes') {
                $verified = 1;
            } else if ($_POST['verified'] == 'no') {
                $verified = 0;
            }
            if ($verified == $userData->verified) {
                $verified = $userData->verified;
            }
        }

        $isartist = $userData->artist;
        if (!empty($_POST['user_type']) && IsAdmin()) {
            if ($_POST['user_type'] == 'yes') {
                $isartist = 1;
            } else if ($_POST['user_type'] == 'no') {
                $isartist = 0;
            }
            if ($isartist == $userData->artist) {
                $isartist = $userData->artist;
            }
        }

        if (empty($errors)) {
            $update_data = [
                'username' => $username,
                'email' => $email,
                'gender' => $gender,
                'age' => $age,
                'country_id' => $country,
                'is_pro' => $ispro,
                'verified' => $verified,
                'artist' => $isartist
            ];

            if (isAdmin() || $userData->id == $user->id) {
                $update = $db->where('id', $userData->id)->update(T_USERS, $update_data);
                if ($update) {
                    $data = [
                        'status' => 200,
                        'message' => lang("Settings successfully updated!")
                    ];
                }
            }
        }
    }
}
if ($option == 'update-profile-cover') {
	if (!empty($_FILES)) {
		if (!empty($_FILES['cover']['tmp_name'])) {
            $type = (!empty($_REQUEST['type'])) ? secure($_REQUEST['type']) : "";
            $file_info = array(
                'file' => $_FILES['cover']['tmp_name'],
                'size' => $_FILES['cover']['size'],
                'name' => $_FILES['cover']['name'],
                'type' => $_FILES['cover']['type'],
                'crop' => array('width' => 1600, 'height' => 400),
                'allowed' => 'jpg,png,jpeg,gif'
            );
            if ($type == 'artist') {
                $file_info['crop'] = array('width' => 1400, 'height' => 800);
            }
            $file_upload = shareFile($file_info);
            if (!empty($file_upload['filename'])) {
                $update_data['cover'] = $file_upload['filename'];
                $db->where('id', $user->id)->update(T_USERS, $update_data);
                $data['status'] = 200;
                $data['img'] = getMedia($file_upload['filename']);
            }
        }
	}
}
if ($option == 'update-profile-picture') {
	if (!empty($_FILES)) {
		if (!empty($_FILES['avatar']['tmp_name'])) {
            $file_info = array(
                'file' => $_FILES['avatar']['tmp_name'],
                'size' => $_FILES['avatar']['size'],
                'name' => $_FILES['avatar']['name'],
                'type' => $_FILES['avatar']['type'],
                'crop' => array('width' => 400, 'height' => 400),
                'allowed' => 'jpg,png,jpeg,gif'
            );
            $file_upload = shareFile($file_info);
            if (!empty($file_upload['filename'])) {
                $update_data['avatar'] = $file_upload['filename'];
                $db->where('id', $user->id)->update(T_USERS, $update_data);
                $data['status'] = 200;
                $data['img'] = getMedia($file_upload['filename']);
            }
        }
	}
}
if ($option == 'update_user_device_id') {
    if (!empty($_POST['id'])) {
        $id = Secure($_POST['id']);
        if ($id != $music->user->web_device_id) {
            $update = $db->where('id', $music->user->id)->update(T_USERS, array(
                'web_device_id' => $id
            ));
            if ($update) {
                $data = array(
                    'status' => 200
                );
            }
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
if ($option == 'remove_user_device_id'){
    if (!empty($music->user->web_device_id)) {
        $update = $db->where('id', $music->user->id)->update(T_USERS, array(
            'web_device_id' => ''
        ));
        if ($update) {
            $data = array(
                'status' => 200
            );
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
?>