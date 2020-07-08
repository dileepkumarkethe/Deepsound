<?php
if (IS_LOGGED == false) {
    $errors[] = "You ain't logged in!";
}
if (empty($errors)) {

    $time_week = time() - 259200;

    if ($option == 'list') {
        $_data = [];
        $_data['new_releases'] = [];
        $_data['latest_music'] = [];

        $NewRelease_query = "SELECT " . T_SONGS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . " FROM " . T_SONGS . " LEFT JOIN " . T_VIEWS . " ON " . T_SONGS . ".id = " . T_VIEWS . ".track_id WHERE " . T_SONGS . ".time > $time_week AND " . T_SONGS . ".availability = '0' AND " . T_SONGS . ".user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = " . $music->user->id . ") GROUP BY " . T_SONGS . ".id ORDER BY " . T_VIEWS . " DESC LIMIT 20";
        $getNewRelease = $db->rawQuery($NewRelease_query);
        if (!empty($getNewRelease)) {
            foreach ($getNewRelease as $key => $song) {
                $songData = songData($song, false, false);
                $_data['new_releases'][] = $songData;
            }
        }

        $getLatestReleases = $db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = ".$music->user->id.")")->where('availability', 0)->orderBy('id', 'DESC')->get(T_SONGS, 20);
        if (!empty($getLatestReleases)) {
            foreach ($getLatestReleases as $key => $song) {
                $_songData = songData($song, false, false);
                $_data['latest_music'][] = $_songData;
            }
        }

        $data = [
            'status' => 200,
            'data' => $_data
        ];
    }

    if ($option == 'latest_music') {
        $_data = [];
        $limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
        $offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;
        $getLatestReleases = $db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = ".$music->user->id.")")->where('availability', 0)->orderBy('id', 'DESC')->get(T_SONGS, array($offset, $limit ));
        if (!empty($getLatestReleases)) {
            foreach ($getLatestReleases as $key => $song) {
                $songData = songData($song, false, false);
                $_data[] = $songData;
            }
        }
        $data = [
            'status' => 200,
            'data' => $_data
        ];
    }

}