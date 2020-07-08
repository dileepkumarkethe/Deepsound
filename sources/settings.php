<?php 
if (IS_LOGGED == false) {
	header("Location: $site_url/discover");
	exit();
}
if (empty($path['options'][1])) {
	header("Location: $site_url/discover");
	exit();
}
$settings_page = 'general';
if (empty($path['options'][2])) {
	$settings_page = 'general';
} else {
	if (in_array($path['options'][2], ['general', 'profile', 'delete', 'password', 'blocked','interest','balance','withdrawals'])) {
		$settings_page = secure($path['options'][2]);
	}
}

if( $settings_page == 'delete' ){
    if( $music->config->delete_account == 'off' ){
        header("Location: $site_url/feed");
        exit();
    }
}

$username = secure($path['options'][1]);

$getIDfromUser = $db->where('username', $username)->getValue(T_USERS, 'id');
if (empty($getIDfromUser) || !isAdmin()) {
	$getIDfromUser = $user->id;
}

$userData = userData($getIDfromUser);

$userData->owner  = false;

if ($music->loggedin == true) {
    $userData->owner  = ($user->id == $userData->id) ? true : false;
}

$countries = '';
foreach ($countries_name as $key => $value) {
    $selected = ($key == $userData->country_id) ? 'selected' : '';
    $countries .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
}

$blocked_list = '<div class="no-track-found"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12,4A4,4 0 0,1 16,8C16,9.95 14.6,11.58 12.75,11.93L8.07,7.25C8.42,5.4 10.05,4 12,4M12.28,14L18.28,20L20,21.72L18.73,23L15.73,20H4V18C4,16.16 6.5,14.61 9.87,14.14L2.78,7.05L4.05,5.78L12.28,14M20,18V19.18L15.14,14.32C18,14.93 20,16.35 20,18Z" /></svg>' . lang("No blocked users found") . '</div>';

$getBlocked = $db->where('user_id', $userData->id)->get(T_BLOCKS);
if (!empty($getBlocked)) {
	$blocked_list = '';
	foreach ($getBlocked as $key => $buser) {
		$blocked_list .= loadPage('settings/blocked_list', [
			'USER_DATA' => userData($buser->blocked_id)
		]);
	}
}

$withdrawal_history = "";
if( $settings_page == 'withdrawals' ){
    $user_withdrawals  = $db->where('user_id',$music->user->id)->get(T_WITHDRAWAL_REQUESTS);
    foreach ($user_withdrawals as $withdrawal) {
        $music->withdrawal_stat = $withdrawal->status;
        $withdrawal_history .= LoadPage("settings/withdrawals-list",array(
            'W_ID' => $withdrawal->id,
            'W_REQUESTED' => date('Y-F-d', $withdrawal->requested),
            'W_AMOUNT' => number_format($withdrawal->amount, 2),
            'W_CURRENCY' => $withdrawal->currency,
        ));
    }
}

$music->settings_page = $settings_page;
$music->userData = $userData;
$music->site_title = lang("Settings");
$music->site_description = $music->config->description;
$music->site_pagename = "settings";
$music->site_content = loadPage("settings/$settings_page", [
	'USER_DATA' => $userData,
	'COUNTRIES_LAYOUT' => $countries,
	'blocked_list' => $blocked_list,
	'WITHDRAWAL_HISTORY_LIST' => $withdrawal_history
]);