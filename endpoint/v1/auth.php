<?php 
if ($option == 'login') {
	if (!empty($_POST)) {
	    if (empty($_POST['username']) || empty($_POST['password'])) {
	        $errors[] = "Please check your details";
	    } else {
	        $username        = secure($_POST['username']);
	        $password        = secure($_POST['password']);

	        $getUser = $db->where("(username = ? or email = ?)", array(
	            $username,
	            $username
	        ))->getOne(T_USERS, ["password", "id", "active"]);

	        if (empty($getUser)) {
	        	$errors[] = "Incorrect username or password";
	        } else if (!password_verify($password, $getUser->password)) {
	        	$errors[] = "Incorrect username or password";
	        } else if ($getUser->active == 0) {
	        	$errors[] = "Your account is not activated yet, please check your inbox for the activation link";
	        }

	        if (empty($errors)) {
	            createUserSession($getUser->id,'mobile');

                if (!empty($_POST['android_device_id'])) {
                    $device_id  = Secure($_POST['android_device_id']);
                    $update  = mysqli_query($sqlConnect, "UPDATE " . T_USERS . " SET `android_device_id` = '{$device_id}' WHERE `id` = '{$user_id}'");
                }
                if (!empty($_POST['ios_device_id'])) {
                    $device_id  = Secure($_POST['ios_device_id']);
                    $update  = mysqli_query($sqlConnect, "UPDATE " . T_USERS . " SET `ios_device_id` = '{$device_id}' WHERE `id` = '{$user_id}'");
                }

	            $music->loggedin = true;
	            $music->user = userData($getUser->id);
	            unset($music->user->password);
                $data = array(
		            'status' => 200,
		            'access_token' => $_SESSION['user_id'],
		            'data' => $music->user
		        );
	        }
	    }
	}
}

if ($option == 'forgot-password') {
	if (!empty($_POST)) {
	    if (empty($_POST['email'])) {
	        $errors[] = "Please check your details";
	    } else {
	        $email        = secure($_POST['email']);

	        $getUser = $db->where("email = ?", array(
	            $email,
	        ))->getOne(T_USERS, ["password", "id", "active", "email_code"]);

	        if (empty($getUser)) {
	        	$errors[] = "This e-mail is not found";
	        }

	        if (empty($errors)) {
	        	$user_id             = $getUser->id;
	            $email_code          = sha1(rand(11111, 99999) . $getUser->password);
                $rest_user           = userData($user_id);
	            $update              = $db->where('id', $getUser->id)->update(T_USERS, ['email_code' => $email_code]);

	            $update_data['USER_DATA'] = $rest_user;
	            $update_data['email_code'] = $email_code;

	            $send_email_data = array(
	           		'from_email' => $music->config->email,
	           		'from_name' => $music->config->name,
	           		'to_email' => $email,
	           		'to_name' => $rest_user->name,
	           		'subject' => "Reset Password",
	           		'charSet' => 'UTF-8',
	           		'message_body' => loadPage('emails/reset-password', $update_data),
	           		'is_html' => true
	           	);

	            $send_message = sendMessage($send_email_data);
	            if ($send_message) {
	            	$data = array(
			            'status' => 200,
			            'message' => "Please check your inbox / spam folder for the reset email."
			        );
	            } else {
	            	$errors[] = "Error found while sending the reset link, please try again later.";
		        }
            }
	    }
	}
}

if ($option == 'reset-password') {
	if (!empty($_POST)) {
	    if (empty($_POST['password']) || empty($_POST['c_password']) || empty($_POST['email_code'])) {
	        $errors[] = "Please check your details";
	    } else {
	        $password        = secure($_POST['password']);
	        $c_password  = secure($_POST['c_password']);
	        $old_email_code = secure($_POST['email_code']);

	        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
	        if ($password != $c_password) {
	            $errors[] = "Passwords don't match";
	        } else if (strlen($password) < 4 || strlen($password) > 32) {
	            $errors[] = "Password is too short";
	        }

	        if (empty($errors)) {
	        	$user_id = $db->where('email_code', $old_email_code)->getValue(T_USERS, "id");
	        	$email_code = sha1(time() + rand(1111,9999));
	        	$update = $db->where('id', $user_id)->update(T_USERS, ['password' => $password_hashed, 'email_code' => $email_code]);
	        	if ($update) {
	        		createUserSession($user_id);
		            $data = [
		                'status' => 200,
                        'access_token' => $_SESSION['user_id']
                    ];
	        	}
            }
	    }
	}
}

if ($option == 'signup') {
	if (!empty($_POST)) {
	    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email']) || empty($_POST['c_password']) || empty($_POST['name'])) {
	        $errors[] = "Please check your details";
	    } else {
	        $username        = secure($_POST['username']);
	        $name            = secure($_POST['name']);
	        $password        = secure($_POST['password']);
	        $c_password      = secure($_POST['c_password']);
	        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
	        $email           = secure($_POST['email']);
	        if (UsernameExits($_POST['username'])) {
	            $errors[] = "This username is already taken";
	        }
	        if (strlen($_POST['username']) < 4 || strlen($_POST['username']) > 32) {
	            $errors[] = "Username length must be between 5 / 32";
	        }
	        if (!preg_match('/^[\w]+$/', $_POST['username'])) {
	            $errors[] = "Invalid username characters";
	        }
	        if (EmailExists($_POST['email'])) {
	            $errors[] = "This e-mail is already taken";
	        }
	        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	            $errors[] = "This e-mail is invalid";
	        }
	        if ($password != $c_password) {
	            $errors[] = "Passwords don't match";
	        }
	        if (strlen($password) < 4) {
	            $errors[] = "Password is too short";
	        }

	        $active = ($music->config->validation == 'on') ? 0 : 1;
	        if (empty($errors)) {
	            $email_code = sha1(time() + rand(111,999));
	            $insert_data = array(
	                'username' => $username,
	                'password' => $password_hashed,
	                'email' => $email,
	                'name' => $name,
	                'ip_address' => get_ip_address(),
	                'active' => $active,
	                'email_code' => $email_code,
	                'last_active' => time(),
	                'registered' => date('Y') . '/' . intval(date('m'))
	            );

                if (!empty($_POST['android_device_id'])) {
                    $insert_data['android_device_id'] = Secure($_POST['android_device_id']);
                }
                if (!empty($_POST['ios_device_id'])) {
                    $insert_data['ios_device_id'] = Secure($_POST['ios_device_id']);
                }

	            $insert_data['language'] = $music->config->language;
	            if (!empty($_SESSION['lang'])) {
	                if (in_array($_SESSION['lang'], $langs)) {
	                    $insert_data['language'] = $_SESSION['lang'];
	                }
	            }
	            $user_id             = $db->insert(T_USERS, $insert_data);
	            if (!empty($user_id)) {
	                if ($music->config->validation == 'on') {
	                     $link = $email_code . '/' . $email; 
	                     $data['EMAIL_CODE'] = $link;
	                     $data['USERNAME']   = $username;
	                     $send_email_data = array(
	                        'from_email' => $music->config->email,
	                        'from_name' => $music->config->name,
	                        'to_email' => $email,
	                        'to_name' => $username,
	                        'subject' => "Confirm your account",
	                        'charSet' => 'UTF-8',
	                        'message_body' => loadPage('emails/confirm-account', $data),
	                        'is_html' => true
	                    );
	                    $send_message = sendMessage($send_email_data);
	                    $data = array(
				            'status' => 200,
                            'wait_validation' => 1,
                            'access_token' => $_SESSION['user_id'],
		                    'data' => $music->user
				        );
	                } else {
	                	createUserSession($user_id,'mobile');
	                	$music->loggedin = true;
	                    $music->user = userData($user_id);
                        unset($music->user->password);
	                    $data = array(
				            'status' => 200,
                            'wait_validation' => 0,
                            'access_token' => $_SESSION['user_id'],
                            'data' => $music->user
				        );
	                }
	            }
	        }
	    }
	}
}
?>