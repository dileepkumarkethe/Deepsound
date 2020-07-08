<?php
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
    echo json_encode($data);
    exit();
}

$feeds              = [];
$limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
$offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;

$getActivties = getActivties($limit, $offset);
if (!empty($getActivties)) {
    foreach ($getActivties as $key => $activity) {
        $feeds[] = getActivity($activity, false);
    }
}
$data = array('status' => 200, 'data' => $feeds);
echo json_encode($data);
exit();