<?php
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'Not logged in');
    echo json_encode($data);
    exit();
}
if (!empty($_POST['id'])) {
    if (!is_numeric($_POST['id'])) {
        $data = array('status' => 400, 'error' => 'Invalid Report Track ID');
        echo json_encode($data);
        exit();
    }
}else{
    $data = array('status' => 400, 'error' => 'Invalid Report Track ID');
    echo json_encode($data);
    exit();
}

$report_track_id = secure($_POST['id']);
$getReportTrack = $db->where('track_id', $report_track_id)->where('user_id', $user->id)->getOne(T_REPORTS);
if (empty($getReportTrack)) {
    $data = array('status' => 400, 'error' => 'Invalid Report Track ID');
    echo json_encode($data);
    exit();
}

$data['status'] = 400;

$deleted = $db->where('track_id', $report_track_id)->where('user_id', $user->id)->delete(T_REPORTS);
if($deleted){
    $data['status'] = 200;
}