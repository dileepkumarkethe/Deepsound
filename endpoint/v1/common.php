<?php
if (IS_LOGGED == false) {
    $errors[] = "You ain't logged in!";
}

if ($option == 'get-trending') {
    $data['status'] = 200;
    $data['top_albums'] = GetTotalTopAlbum()['data'];
    $data['top_songs'] = GetTotalTopSong()['data'];

    $data['top_seller_albums'] = [];
    $data['top_seller_songs'] = [];
    $getTopAlbums = $db->rawQuery('SELECT DISTINCT 
                                      `'.T_ALBUMS.'`.`id`
                                    FROM
                                      `'.T_PURCHAES.'`
                                      INNER JOIN `'.T_SONGS.'` ON (`'.T_PURCHAES.'`.`track_id` = `'.T_SONGS.'`.`id`)
                                      INNER JOIN `'.T_ALBUMS.'` ON (`'.T_SONGS.'`.`album_id` = `'.T_ALBUMS.'`.`id`)
                                    ORDER BY
                                      `'.T_PURCHAES.'`.`time` DESC LIMIT 14');

    foreach ($getTopAlbums as $key => $value){
        $data['top_seller_albums'][$key] = albumData($value->id);
    }

    $getTopSongs = $db->rawQuery('SELECT track_id, COUNT(track_id) AS count FROM `'.T_PURCHAES.'` GROUP BY track_id ORDER BY count,`time` DESC LIMIT 10');
    foreach ($getTopSongs as $key => $value){
        $song_data = songData($value->track_id);
        if($song_data !== false) {
            $data['top_seller_songs'][$key] = $song_data;
        }else{
            unset($data['top_seller_songs'][$key]);
        }
    }

    $data['most_popular_week'] = GetMostPopularWeek()['data'];
}

if ($option == 'top-seller') {
    $data['status'] = 200;
    $data['albums'] = [];
    $data['songs'] = [];
    $getTopAlbums = $db->rawQuery('SELECT DISTINCT 
                                      `'.T_ALBUMS.'`.`id`
                                    FROM
                                      `'.T_PURCHAES.'`
                                      INNER JOIN `'.T_SONGS.'` ON (`'.T_PURCHAES.'`.`track_id` = `'.T_SONGS.'`.`id`)
                                      INNER JOIN `'.T_ALBUMS.'` ON (`'.T_SONGS.'`.`album_id` = `'.T_ALBUMS.'`.`id`)
                                    ORDER BY
                                      `'.T_PURCHAES.'`.`time` DESC LIMIT 14');

    foreach ($getTopAlbums as $key => $value){
        $data['albums'][$key] = albumData($value->id);
    }

    $getTopSongs = $db->rawQuery('SELECT track_id, COUNT(track_id) AS count FROM `'.T_PURCHAES.'` GROUP BY track_id ORDER BY count,`time` DESC LIMIT 10');
    foreach ($getTopSongs as $key => $value){
        $song_data = songData($value->track_id);
        if($song_data !== false) {
            $data['songs'][$key] = $song_data;
        }else{
            unset($data['songs'][$key]);
        }
    }
}

if ($option == 'get-prices') {
    $data['status'] = 200;
    $data['data'] = $db->get(T_SONG_PRICE,null,array('*'));
}

if ($option == 'discover') {
    $data['status'] = 200;
    $data['most_popular_week'] = GetMostPopularWeek();
    $data['new_releases'] = GetNewReleases();
    $data['recently_played'] = GetRecentlyPlayed($music->user->id);
    $data['randoms'] = array(
        'playlist' => GetRandomPlaylist(),
        'song' => GetRandomSong(),
        'album' => GetRandomAlbum(),
        'recommended' => GetRecommendedSongs()
    );
}

if ($option == 'get-most-popular-week') {
    $data['status'] = 200;
    $data['data'] = GetMostPopularWeek()['data'];
}

if ($option == 'get-top-songs') {
    $data['status'] = 200;
    $data['data'] = GetTotalTopSong()['data'];
}

if ($option == 'get-new-releases') {
    $data['status'] = 200;
    $data['data'] = GetNewReleases()['data'];
}

if ($option == 'update-interest') {
    if ( ( !isset($_POST['genres']) || empty($_POST['genres']) ) || ( !isset($_POST['id']) || empty($_POST['id']) ) ) {
        $errors[] = "Please check your details";
    } else {
        $genres = secure($_POST['genres']);
        $id = (int)secure($_POST['id']);
        $arr = explode(',',$genres);
        $insert = false;
        $db->where('user_id', $id)->delete(T_USER_INTEREST);
        if(!empty($arr)){
            foreach ($arr as $key){
                $insert = $db->insert(T_USER_INTEREST, array('user_id' => $id, 'category_id' => $key));
            }
            if($insert){
                $data = [
                    'status' => 200,
                    'message' => "Profile successfully updated!"
                ];
            }else{
                $errors[] = "Please check your details";
            }
        }else{
            $errors[] = "Please check your details";
        }
    }
}

if ($option == 'search') {
    $results = [];
    $results['songs'] = [];
    $results['albums'] = [];
    $limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
    $offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;

    $album_limit              = (isset($_POST['album_limit'])) ? secure($_POST['album_limit']) : 20;
    $album_offset             = (isset($_POST['album_offset'])) ? secure($_POST['album_offset']) : 0;

    $artist_limit             = (isset($_POST['artist_limit'])) ? secure($_POST['artist_limit']) : 12;
    $artist_offset            = (isset($_POST['artist_offset'])) ? secure($_POST['artist_offset']) : 0;

    $playlist_limit           = (isset($_POST['playlist_limit'])) ? secure($_POST['playlist_limit']) : 12;
    $playlist_offset          = (isset($_POST['playlist_offset'])) ? secure($_POST['playlist_offset']) : 0;

    $error     = false;
    $request   = array();
    $request[] = (empty($_POST['keyword']) || empty($_POST['genres']) || empty($_POST['price']));
    if (in_array(true, $request)) {
        $error = "Please check your details";
    }
    if (empty($error)) {
        $filter_search_keyword = Secure($_POST['keyword']);
        $prices = explode(',', Secure($_POST['price']));
        $genres = explode(',', Secure($_POST['genres']));
        if(is_array($prices) ) {
            $db->where('price', $prices, 'IN');
        }
        if(is_array($genres) && !empty($genres)) {
            $db->where('category_id', $genres, 'IN');
        }
        if(!empty($filter_search_keyword)) {
            $db->orWhere('title','%'.$filter_search_keyword.'%','like');
            $db->orWhere('description','%'.$filter_search_keyword.'%','like');
            $db->orWhere('tags','%'.$filter_search_keyword.'%','like');
        }
        $songs = $db->get(T_SONGS, array($offset, $limit));
        foreach ($songs as $key => $value){
            $song_data = songData($value->id);
            if($song_data !== false) {
                $results['songs'][$key] = $song_data;
            }else{
                unset($results['songs'][$key]);
            }
        }

        $and = [];
        $or = [];
        $sql = 'SELECT * FROM `'. T_ALBUMS .'`';
        if(is_array($prices) ) {
            $and[] = " `price` IN ('" . implode("','",$prices) . "') ";
        }
        if(is_array($genres) && !empty($genres)) {
            $and[] = " `category_id` IN ('" . implode("','",$genres) . "') ";
        }
        if(!empty($filter_search_keyword)){
            $or[] = " `title` LIKE '%".$filter_search_keyword."%' ";
            $or[] = " `description` LIKE '%".$filter_search_keyword."%' ";
        }

        $sql .= ' WHERE ( ' . implode(' OR ', $and ) . ' ) OR ( ' . implode(' OR ', $or ) . ' ) LIMIT ' . $album_limit . ' OFFSET '. $album_offset;
        $albums = $db->rawQuery($sql);
        foreach ($albums as $key => $value){
            $results['albums'][$key] = albumData($value->id);
            unset($results['albums'][$key]->songs);
        }


        $artists = $db->where('artist','1')
            ->Where('name','%'.$filter_search_keyword.'%','like')
            ->OrWhere('username','%'.$filter_search_keyword.'%','like')
            ->orderBy('id', 'DESC')
            ->get(T_USERS,array($artist_offset, $artist_limit));
        foreach ($artists as $key => $value){
            $results['artist'][$key] = userData($value->id);
        }

        $playlists = $db->Where('name','%'.$filter_search_keyword.'%','like')
            ->where('privacy','0')
            ->orderBy('id', 'DESC')
            ->get(T_PLAYLISTS,array($playlist_offset, $playlist_limit));
        foreach ($playlists as $key => $value){
            $results['playlist'][$key] = getPlayList($value->id);
        }



        $data['status'] = 200;
        $data['data'] = $results;
    } else {
        $data = array(
            'status' => 400,
            'message' => $error
        );
    }
}