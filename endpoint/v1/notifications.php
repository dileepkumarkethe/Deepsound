<?php
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
    echo json_encode($data);
    exit();
}

$data['status'] = 400;

if ($option == 'get') {
	$countNotSeen = getNotifications('count', false);
	if ($countNotSeen > 0) {
		$getNotifications = getNotifications('fetch', false);
	} else {
		$getNotifications = getNotifications();
	}
    $notifiations = [];
	if (!empty($getNotifications)) {
		foreach ($getNotifications as $key => $notification) {
			$notifierData = userData($notification->notifier_id);
            $notifiations[] = [
				'USER_DATA' => $notifierData,
                'n_id' => $notification->id,
				'n_time' => time_Elapsed_String($notification->time),
				'n_text' => ($notification->type == 'admin_notification') ? $notification->text : getNotificationTextFromType($notification->type),
				'n_url' => ($notification->type == 'follow_user') ? $notifierData->url : getLink($notification->url),
				'n_a_url' => ($notification->type == 'follow_user') ? 'user/' . $notifierData->username : $notification->url,
                'n_type' => $notification->type
			];
		}
		if (!empty($notifiations)) {
			$db->where('recipient_id', $user->id)->update(T_NOTIFICATION, ['seen' => time()]);
		}
	}
    $data = [
        'status' => 200,
        'notifiations' => $notifiations
    ];
}

if ($option == 'count_unseen') {
	$data = [
		'status' => 200,
		'count' => getNotifications('count', false),
        'msgs' => $db->where('to_id', $user->id)->where('seen', 0)->getValue(T_MESSAGES, "COUNT(*)")
	];
}
?>