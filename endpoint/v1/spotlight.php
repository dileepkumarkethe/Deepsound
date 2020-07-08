<?php
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
    echo json_encode($data);
    exit();
}

$time_week = time() - 604800;

$query = "SELECT " . T_SONGS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . "
FROM " . T_SONGS . " LEFT JOIN " . T_VIEWS . " ON " . T_SONGS . ".id = " . T_VIEWS . ".track_id
WHERE " . T_SONGS . ".time > $time_week AND " . T_SONGS . ".availability = '0'";
$query .= " GROUP BY " . T_SONGS . ".id
ORDER BY " . T_VIEWS . " DESC LIMIT 20";

$weekly_sidebar = $db->rawQuery($query);

if (!empty($weekly_sidebar)) {
    foreach ($weekly_sidebar as $key => $song) {
        $songData = songData($song->id);
        if (!empty($songData)) {
            $final_ids[] = $songData;
        }
    }
    $data['status'] = 200;
    $data['songs'] = array_reverse($final_ids);
}
