<?php
if (IS_LOGGED == false) {
    $data = array(
        'status' => 400,
        'error' => 'Not logged in'
    );
    echo json_encode($data);
    exit();
}  else {
    $error     = '';

    if ( empty($_POST['trackid']) || empty($_POST['users']) || empty($_POST['msg']) ) {
        $error = lang("Please check your details");
    }

    if (empty($error)) {

        $users = [];
        $users = explode(',',Secure($_POST['users']));
        $new_message = "<a href='" . $site_url . '/track/'. Secure($_POST['trackid']) . "' target='_blank'>".Secure($_POST['trackname'])."</a>&nbsp;<br>".nl2br(Secure($_POST['msg']));
        foreach ($users as $key) {
            if ($key != $music->user->id) {
                $chat_exits = $db->where("user_one", $music->user->id)->where("user_two", $key)->getValue(T_CHATS, 'count(*)');
                if (!empty($chat_exits)) {
                    $db->where("user_two", $music->user->id)->where("user_one", $key)->update(T_CHATS, array('time' => time()));
                    $db->where("user_one", $music->user->id)->where("user_two", $key)->update(T_CHATS, array('time' => time()));
                    if ($db->where("user_two", $music->user->id)->where("user_one", $key)->getValue(T_CHATS, 'count(*)') == 0) {
                        $db->insert(T_CHATS, array('user_two' => $music->user->id, 'user_one' => $key, 'time' => time()));
                    }
                } else {
                    $db->insert(T_CHATS, array('user_one' => $music->user->id, 'user_two' => $key, 'time' => time()));
                    if (empty($db->where("user_two", $music->user->id)->where("user_one", $key)->getValue(T_CHATS, 'count(*)'))) {
                        $db->insert(T_CHATS, array('user_two' => $music->user->id, 'user_one' => $key, 'time' => time()));
                    }
                }
                $insert_message = array(
                    'from_id' => $music->user->id,
                    'to_id' => $key,
                    'text' => $new_message,
                    'time' => time()
                );
                $insert = $db->insert(T_MESSAGES, $insert_message);
            }
        }
        $data = array(
            'status' => 200,
            'message' => lang("Message sent successfully")
        );


    } else {
        $data = array(
            'status' => 400,
            'message' => $error
        );
    }
}