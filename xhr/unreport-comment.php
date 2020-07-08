<?php
if (IS_LOGGED == false) {
    exit("You ain't logged in!");
}

$report_comment_id = 0;
if (!empty($_REQUEST['id'])) {
    $report_comment_id = secure($_REQUEST['id']);
}
if (empty($report_comment_id)) {
    exit("Invalid Report Comment ID");
}
if (empty(secure($_REQUEST['uid']))) {
    exit("Invalid User ID");
}

$getReportComment = $db->where('comment_id', $report_comment_id)->where('user_id', secure($_REQUEST['uid']))->getOne(T_REPORTS);
if (empty($getReportComment)) {
    exit("Invalid Report Comment ID");
}

$data['status'] = 400;

$deleted = $db->where('comment_id', $report_comment_id)->where('user_id', secure($_REQUEST['uid']))->delete(T_REPORTS);
if($deleted){
    $data['status'] = 200;
}