<?php
if (IS_LOGGED == false) {
    exit("You ain't logged in!");
}

$comment_id = 0;
if (!empty($_GET['id'])) {
    if (is_numeric($_GET['id'])) {
        $comment_id = secure($_GET['id']);
    }
}
if (empty($comment_id)) {
    exit("Invalid Comment ID");
}

$getComment = getComment($comment_id);
if (empty($getComment)) {
    exit("Invalid Comment ID");
}

$data['status'] = 400;


$unlike = UnLikeComment([
    'comment_user_id' => $getComment->user_id,
    'track_id' => $getComment->track_id,
    'user_id' => $user->id,
    'comment_id' => $comment_id
]);
if($unlike) {
    $data['status'] = 200;
}

?>