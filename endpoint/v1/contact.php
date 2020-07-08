<?php 

	if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['message'])) {
		$errors[] = "Please check your details";
	} else {
		if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		    $errors[] = "This e-mail is invalid";
		}
		if (empty($errors)) {
		    $name              = secure($_POST['name']);
		    $email             = secure($_POST['email']);
		    $message           = secure($_POST['message']);

		    $send_message_data = array(
		        'from_email' => $music->config->email,
		        'from_name' => $name,
		        'reply-to' => $email,
		        'to_email' => $music->config->email,
		        'to_name' => $music->config->name,
		        'subject' => 'Contact us new message',
		        'charSet' => 'utf-8',
		        'message_body' => $message,
		        'is_html' => false
		    );

		    $send = sendMessage($send_message_data);
		    if ($send) {
		        $data = array(
		            'status' => 200,
		            'message' => "E-mail sent successfully"
		        );
		    } 
		}
	}

?>