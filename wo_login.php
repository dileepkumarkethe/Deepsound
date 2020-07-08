<?php
require_once('./assets/init.php');

$uri = $site_url;

global $db;
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $app_id        = $music->config->wowonder_app_ID;
    $app_secret    = $music->config->wowonder_app_key;
    $wowonder_url  = $music->config->wowonder_domain_uri;
    $code          = Secure($_GET['code']);
    $url           = $wowonder_url . "/authorize?app_id={$app_id}&app_secret={$app_secret}&code={$code}";
    $get           = file_get_contents($url);
    $wo_json_reply = json_decode($get, true);
    $access_token  = '';
    if (is_array($wo_json_reply) && isset($wo_json_reply['access_token'])) {
        $access_token    = $wo_json_reply['access_token'];
        $type            = "get_user_data";
        $url             = $wowonder_url . "/api_request?access_token={$access_token}&type={$type}";
        $user_data_json  = file_get_contents($url);
        $user_data_array = json_decode($user_data_json, true);

        if (is_array($user_data_array) && !empty($user_data_array) && isset($user_data_array['user_data'])) {
            $user_data  = $user_data_array['user_data'];
            $user_email = $user_data['email'];
            if (EmailExists($user_email) === true) {
                $db->where('email', $user_email);
                $login = $db->getOne(T_USERS);
                createUserSession($login->id);
                header("Location: $site_url");
                exit();
            } else {
                $str          = md5(microtime());
                $id           = substr($str, 0, 9);
                $password     = substr(md5(time()), 0, 9);
                $user_uniq_id = (empty($db->where('username', $id)->getValue(T_USERS, 'id'))) ? $id : 'u_' . $id;
                $first_name   = (isset($user_data['first_name'])) ? Secure($user_data['first_name'], 0) : '';
                $last_name    = (isset($user_data['last_name'])) ? Secure($user_data['last_name'], 0) : '';
                $gender       = (isset($user_data['gender'])) ? Secure($user_data['gender'], 0) : 'male';
                $username     = (Secure($user_data['username']));
                $provider     = ($wowonder_url . "/{$username}");
                $re_data      = array(
                    'username' => Secure($user_uniq_id, 0),
                    'email' => Secure($user_email, 0),
                    'password' => Secure(sha1($user_email), 0),
                    'email_code' => Secure(md5($user_uniq_id), 0),
                    'name' => $first_name . ' ' . $last_name,
                    'gender' => Secure($gender),
                    'active' => '1',
                    'src' => $provider
                );
                if (!empty($user_data['avatar'])) {
                    $re_data['avatar'] = Secure(importImageFromLogin($user_data['avatar']));
                }
                $insert_id = $db->insert(T_USERS, $re_data);
                if ($insert_id) {
                    createUserSession($insert_id);
                    header("Location: $site_url");
                    exit();
                }
            }
        }
    } else {
        echo Lang('Error found, please try again later.') . "<a href='" . $uri . "'>".Lang('Return back')."</a>";
    }
} else {
    echo "<a href='" . $uri . "'>".Lang('Return back')."</a>";
}
?>