<?php
if (IS_LOGGED == false) {
    exit("You ain't logged in!");
}

$track_id = 0;
if (!empty($_REQUEST['id'])) {
    $track_id = secure($_REQUEST['id']);
}
if (empty($track_id)) {
    exit("Invalid Track ID");
}

$getTrack = $db->where('id', $track_id)->getOne(T_SONGS);
if (empty($getTrack)) {
    exit("Invalid Track ID");
}

$data['status'] = 400;

if (empty($_POST['track_description'])) {
    $errors[] = lang("Please describe your request.");
}

if (empty($errors)) {
    $description = secure($_POST['track_description']);
    $insert_report = $db->insert(T_REPORTS, ['track_id' => $track_id, 'description' => $description, 'time' => time(), 'user_id' => $user->id]);
    if ($insert_report) {
        $data['status'] = 200;
    }
} else {
    $data['status'] = 400;
    $data['errors'] = $errors;
}