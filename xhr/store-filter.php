<?php
$filter_type = (isset($_GET['filter_type'])) ? Secure($_GET['filter_type']) : '';
$geners = (isset($_GET['geners'])) ? $_GET['geners'] : '';
$price_from = (isset($_GET['price_from'])) ? $_GET['price_from'] : 1;
$price_to = (isset($_GET['price_to'])) ? $_GET['price_to'] : 40;

if( empty($filter_type) || empty($price_from) || empty($price_to) ){
    exit('Empty parameters, hmm?');
}

$results = [];
switch ($filter_type){
    case 'songs':

        $and = [];
        $sql = 'SELECT * FROM `'. T_SONGS .'` WHERE ';
        $and[] = " `price` BETWEEN ". $price_from ." AND ". $price_to ;
        if(is_array($geners) && !empty($geners)) {
            $and[] = " `category_id` IN ('" . implode("','",$geners) . "') ";
        }
        $and[] = " `album_id` = 0 ";

        $sql .= implode(' AND ', $and ) .' ORDER BY `id` DESC LIMIT 10';
        $html_list = '';


        $getSongs = $db->rawQuery($sql);
        if (!empty($getSongs)) {
            $records = count($getSongs);
            $html_list = '';
            foreach ($getSongs as $key => $song) {
                $songData = songData($song, false, false);
                if (!empty($songData)) {
                    $music->songData = $songData;
                    $html_list .= loadPage('store/song-list', [
                        't_thumbnail' => $songData->thumbnail,
                        't_id' => $songData->id,
                        't_title' => $songData->title,
                        't_artist' => $songData->publisher->name,
                        't_uartist' => $songData->publisher->username,
                        't_url' => $songData->url,
                        't_artist_url' => $songData->publisher->url,
                        't_price' => $songData->price,
                        't_audio_id' => $songData->audio_id,
                        't_duration' => $songData->duration,
                        't_posted' => $songData->time_formatted,
                        't_key' => ($key + 1)
                    ]);
                }
            }
        }
//        var_dump($getSongs);
//        var_dump($html_list);
//        exit();
        $data['sql'] = $sql;
        $data['html'] = $html_list;
        $data['status'] = 200;

        break;
    case 'albums':
        $and = [];
        $sql = 'SELECT * FROM `'. T_ALBUMS .'` WHERE ';
        $and[] = " `price` BETWEEN ". $price_from ." AND ". $price_to ;
        if(is_array($geners) && !empty($geners)) {
            $and[] = " `category_id` IN ('" . implode("','",$geners) . "') ";
        }
        $sql .= implode(' AND ', $and ) .' ORDER BY `id` DESC  LIMIT 10';
        $html_list = '';
        $getAlbums = $db->rawQuery($sql);
        if (!empty($getAlbums)) {
            $records = count($getAlbums);

            foreach ($getAlbums as $key => $album) {
                if (!empty($album)) {
                    $publisher = userData($album->user_id);
                    $html_list .= loadPage('store/albums', [
                        'id' => $album->id,
                        'album_id' => $album->album_id,
                        'user_id' => $album->user_id,
                        'artist' => $publisher->name,
                        'title' => $album->title,
                        'description' => $album->description,
                        'category_id' => $album->category_id,
                        'thumbnail' => getMedia($album->thumbnail),
                        'time' => $album->time,
                        'registered' => $album->registered,
                        'price' => $album->price
                    ]);
                }
            }
        }
        $data['html'] = $html_list;
        $data['status'] = 200;

        break;
    default:
        $data['status'] = 400;
        break;
}