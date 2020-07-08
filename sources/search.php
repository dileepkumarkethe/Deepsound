<?php
// if (IS_LOGGED == false) {
//     header("Location: $site_url/feed");
//     exit();
// }

if (!isset($path['options'][1])) {
    header("Location: $site_url/feed");
    exit();
}
$music->site_title = 'Search';
$music->site_description = $music->config->description;
$music->site_pagename = "search";

$search_keyword = '';
if( isset($path['options'][2]) && !empty($path['options'][2]) ){
    $search_keyword = Secure($path['options'][2]);

    $db->where('keyword',$search_keyword)->update(T_SEARCHES,array('hits'=>$db->inc(1)));
    $cnt = $db->where('keyword',$search_keyword)->getValue(T_SEARCHES, "count(id)");
    if($cnt==0){
        // $top_ids = $db->rawQuery('SELECT GROUP_CONCAT(`id`) AS x FROM (SELECT `id` FROM `'.T_SEARCHES.'` ORDER BY `created_at` DESC LIMIT 10) AS rows');
        // if( $top_ids[0]->x !== NULL ){
        //     $db->rawQuery('DELETE FROM '.T_SEARCHES.' WHERE `id` NOT IN ('.$top_ids[0]->x.')');
        // }
        $db->insert(T_SEARCHES,array('keyword'=>$search_keyword,'hits'=>1,'created_at'=>time()));
    }
}

$page = 'songs';
if (empty($path['options'][1])) {
    $page = 'songs';
} else {
    if (in_array($path['options'][1], ['songs', 'artists', 'albums', 'playlist'])) {
        $page = secure($path['options'][1]);
    }
}

$data = [];
$results = [];
if( !empty($search_keyword) ) {
    if ($page == 'songs') {

        $results = $db->where('title','%'.$search_keyword.'%','like')
                      ->OrWhere('description','%'.$search_keyword.'%','like')
                      ->OrWhere('tags','%'.$search_keyword.'%','like')
                      ->orderBy('id', 'DESC')
                      ->get(T_SONGS,10);
        $data = [
            'SEARCH_KEYWORD' => $search_keyword,
            'SONGS_COUNT' => count($results),
            'SONG_DATA' => $results
        ];

    } else if ($page == 'artists') {

        $results = $db->where('artist','1')
            ->Where('name','%'.$search_keyword.'%','like')
            ->OrWhere('username','%'.$search_keyword.'%','like')
            ->orderBy('id', 'DESC')
            ->get(T_USERS,10);
        $data = [
            'SEARCH_KEYWORD' => $search_keyword,
            'ARTISTS_COUNT' => count($results),
            'ARTISTS_DATA' => $results
        ];

    } else if ($page == 'albums') {

        $results = $db->Where('title','%'.$search_keyword.'%','like')
            ->OrWhere('description','%'.$search_keyword.'%','like')
            ->orderBy('id', 'DESC')
            ->get(T_ALBUMS,10);
        $data = [
            'SEARCH_KEYWORD' => $search_keyword,
            'ALBUMS_COUNT' => count($results),
            'ALBUMS_DATA' => $results
        ];

    } else if ($page == 'playlist') {

        $results = $db->Where('name','%'.$search_keyword.'%','like')
            ->where('privacy','0')
            ->orderBy('id', 'DESC')
            ->get(T_PLAYLISTS,10);
        $data = [
            'SEARCH_KEYWORD' => $search_keyword,
            'PLAYLISTS_COUNT' => count($results),
            'PLAYLISTS_DATA' => $results
        ];

    }
}
$music->search_keyword = $search_keyword;
$music->search_page = $page;
$music->site_content = loadPage("search/content",[
    'SEARCH_KEYWORD' => $search_keyword,
    'FILTERS' => loadPage("search/filters", []),
    'RESULT' => loadPage("search/$page", $data),
]);