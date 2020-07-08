<?php
if (IS_LOGGED == false) {
    exit("You ain't logged in!");
}

$report_track_id = 0;
if (!empty($_REQUEST['id'])) {
    $report_track_id = secure($_REQUEST['id']);
}
if (empty($report_track_id)) {
    exit("Invalid Report Track ID");
}
if (empty(secure($_REQUEST['uid']))) {
    exit("Invalid User ID");
}

$getReportTrack = $db->where('track_id', $report_track_id)->where('user_id', secure($_REQUEST['uid']))->getOne(T_REPORTS);
if (empty($getReportTrack)) {
    exit("Invalid Report Track ID");
}

$data['status'] = 400;

$deleted = $db->where('track_id', $report_track_id)->where('user_id', secure($_REQUEST['uid']))->delete(T_REPORTS);
if($deleted){
    $data['status'] = 200;
}