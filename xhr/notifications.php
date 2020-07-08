<?php  
if (IS_LOGGED == false) {
    exit("You ain't logged in!");
}

$data['status'] = 400;

if ($option == 'get') {
	$countNotSeen = getNotifications('count', false);
	if ($countNotSeen > 0) {
		$getNotifications = getNotifications('fetch', false);
	} else {
		$getNotifications = getNotifications();
	}
	if (!empty($getNotifications)) {
		$html = '';
		foreach ($getNotifications as $key => $notification) {
			$notifierData = userData($notification->notifier_id);
            $notificationtext = ($notification->type == 'admin_notification') ? $notification->text : getNotificationTextFromType($notification->type);

			$html .= loadPage('header/notification-list', [
				'USER_DATA' => $notifierData,
				'n_time' => time_Elapsed_String($notification->time),
                'ns_time' => date('c',$notification->time),
                'n_type' => $notification->type,
				'uri' => $notification->url,
				'n_text' => str_replace('%d',$notification->text, $notificationtext),
				'n_url' => ($notification->type == 'follow_user') ? $notifierData->url : getLink($notification->url),
				'n_a_url' => ($notification->type == 'follow_user') ? 'user/' . $notifierData->username : $notification->url,
			]); 
		}
		if (!empty($html)) {
			$db->where('recipient_id', $user->id)->update(T_NOTIFICATION, ['seen' => time()]);
			$data = [
				'status' => 200,
				'html' => $html
			];
		}
	}
}

if ($option == 'count_unseen') {
	$data = [
		'status' => 200,
		'count' => getNotifications('count', false),
        'msgs' => $db->where('to_id', $user->id)->where('seen', 0)->getValue(T_MESSAGES, "COUNT(*)")
	];
}
?>