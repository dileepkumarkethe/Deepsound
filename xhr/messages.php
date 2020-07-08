<?php
if (IS_LOGGED == false) {
    exit("You ain't logged in!");
}

if ($option == 'new') {
    if (!empty($_POST['id']) && !empty($_POST['new-message'])) {
        $link_regex = '/(http\:\/\/|https\:\/\/|www\.)([^\ ]+)/i';
        $i          = 0;
        preg_match_all($link_regex, Secure($_POST['new-message']), $matches);
        foreach ($matches[0] as $match) {
            $match_url           = strip_tags($match);
            $syntax              = '[a]' . urlencode($match_url) . '[/a]';
            $_POST['new-message'] = str_replace($match, $syntax, $_POST['new-message']);
        }
        $new_message = Secure($_POST['new-message']);
        $id = Secure($_POST['id']);
        if ($id != $music->user->id) {
            $chat_exits = $db->where("user_one", $music->user->id)->where("user_two", $id)->getValue(T_CHATS, 'count(*)');
            if (!empty($chat_exits)) {
                $db->where("user_two", $music->user->id)->where("user_one", $id)->update(T_CHATS, array('time' => time()));
                $db->where("user_one", $music->user->id)->where("user_two", $id)->update(T_CHATS, array('time' => time()));
                if ($db->where("user_two", $music->user->id)->where("user_one", $id)->getValue(T_CHATS, 'count(*)') == 0) {
                    $db->insert(T_CHATS, array('user_two' => $music->user->id, 'user_one' => $id,'time' => time()));
                }
            } else {
                $db->insert(T_CHATS, array('user_one' => $music->user->id, 'user_two' => $id,'time' => time()));
                if (empty($db->where("user_two", $music->user->id)->where("user_one", $id)->getValue(T_CHATS, 'count(*)'))) {
                    $db->insert(T_CHATS, array('user_two' => $music->user->id, 'user_one' => $id,'time' => time()));
                }
            }
            $insert_message = array(
                'from_id' => $music->user->id,
                'to_id' => $id,
                'text' => $new_message,
                'time' => time()
            );
            $insert = $db->insert(T_MESSAGES, $insert_message);
            if ($insert) {
                $music->message = GetMessageData($insert);
                $data = array(
                    'status' => 200,
                    'message_id' => $_POST['message_id'],
                    'message' => LoadPage('messages/ajax/outgoing', array(
                        'ID' => $music->message->id,
                        'TEXT' => $music->message->text
                    ))
                );
            }
        }
    }
}

if ($option == 'fetch') {
    if (empty($_POST['last_id'])) {
        $_POST['last_id'] = 0;
    }
    if (empty($_POST['id'])) {
        $_POST['id'] = 0;
    }
    if (empty($_POST['first_id'])) {
        $_POST['first_id'] = 0;
    }
    $messages_html = GetMessages($_POST['id'], array('last_id' => $_POST['last_id'], 'first_id' => $_POST['first_id'], 'return_method' => 'html'));
    if (!empty($messages_html)) {
        $html = LoadPage("messages/ajax/messages", array('MESSAGES' => $messages_html));
    } else {
        $html = LoadPage("messages/ajax/no-messages");
    }

    $users_html = GetMessagesUserList(array('return_method' => 'html'));

    if (!empty($messages_html) || !empty($users_html)) {
        $data = array('status' => 200, 'message' => $messages_html, 'users' => $users_html);
    }
}

if ($option == 'search') {
    $keyword = '';
    $users_html = '<p class="text-center">' . $lang->no_match_found . '</p>';
    if (isset($_POST['keyword'])) {
        $users_html = GetMessagesUserList(array('return_method' => 'html', 'keyword' => $_POST['keyword']));
    }
    $data = array('status' => 200, 'users' => $users_html);
}

if ($option == 'delete_chat') {
    if (!empty($_POST['id'])) {
        $id = Secure($_POST['id']);
        $messages = $db->where("(from_id = {$music->user->id} AND to_id = {$id}) OR (from_id = {$id} AND to_id = {$music->user->id})")->get(T_MESSAGES);
        $update1 = array();
        $update2 = array();
        $erase = array();
        foreach ($messages as $key => $message) {
            if ($message->from_deleted == 1 || $message->to_deleted == 1) {
                $erase[] = $message->id;
            } else {
                if ($message->to_id == $music->user->id) {
                    $update2[] = $message->id;
                } else {
                    $update1[] = $message->id;
                }
            }
        }
        if (!empty($erase)) {
            $erase = implode(',', $erase);
            $final_query = "DELETE FROM " . T_MESSAGES . " WHERE id IN ($erase)";
            $db->rawQuery($final_query);
        }
        if (!empty($update1)) {
            $update1 = implode(',', $update1);
            $final_query = "UPDATE " . T_MESSAGES . " set `from_deleted` = '1' WHERE `id` IN({$update1}) ";
            $db->rawQuery($final_query);
        }
        if (!empty($update2)) {
            $update2 = implode(',', $update2);
            $final_query = "UPDATE " . T_MESSAGES . " set `to_deleted` = '1' WHERE `id` IN({$update2}) ";
            $db->rawQuery($final_query);
        }
        $delete_chats = $db->rawQuery("DELETE FROM " . T_CHATS . " WHERE user_one = {$music->user->id} AND user_two = $id");
    }
}
?>