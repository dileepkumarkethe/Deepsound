<?php
if (IS_LOGGED == true) {
	header("Location: $site_url/404");
	exit();
}
if (empty($path['options'][1]) || empty($path['options'][2])) {
	header("Location: $site_url/404");
	exit();
}

$email = secure($path['options'][2]);
$code = secure($path['options'][1]);


$db->where('email', $email);
$db->where('email_code', $code);
$user = $db->getOne(T_USERS);
if (empty($user)) {
	exit(lang('Invalid Request'));
}
if ($user->active == 1) {
	exit(lang('Invalid Request'));
}

$email_code = sha1(time() + rand(111,999));

$db->where('id', $user->id);

$update_data = array('active' => 1, 'email_code' => $email_code);
$update = $db->update(T_USERS, $update_data);
if ($update) {
	$session_id          = sha1(rand(11111, 99999)) . time() . md5(microtime());
    $insert_data         = array(
        'user_id' => $user->id,
        'session_id' => $session_id,
        'time' => time()
    );
    $insert              = $db->insert(T_SESSIONS, $insert_data);
    $_SESSION['user_id'] = $session_id;
    setcookie("user_id", $session_id, time() + (10 * 365 * 24 * 60 * 60), "/");
    $music->loggedin = true;
    header("Location: $site_url");
    exit();
}
exit();