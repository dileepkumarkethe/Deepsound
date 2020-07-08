<?php
$filter_type = (isset($_GET['filter_type'])) ? Secure($_GET['filter_type']) : '';
$filter_search_keyword = (isset($_GET['filter_search_keyword'])) ? Secure($_GET['filter_search_keyword']) : '';
$geners = (isset($_GET['geners'])) ? $_GET['geners'] : '';
$prices = (isset($_GET['prices'])) ? $_GET['prices'] : '';

if( empty($filter_type) || empty($filter_search_keyword) ){
    exit('Empty parameters, hmm?');
}

$results = [];
switch ($filter_type){
    case 'songs':

        if(is_array($prices) ) {
            $db->where('price', $prices, 'IN');
        }
        if(is_array($geners) && !empty($geners)) {
            $db->where('category_id', $geners, 'IN');
        }
        if(!empty($filter_search_keyword)) {
            $db->where("(title LIKE '%$filter_search_keyword%' OR description LIKE '%$filter_search_keyword%' OR tags LIKE '%$filter_search_keyword%')");
        }
        $results = $db->get(T_SONGS,10);
        $pagedata = [
            'SEARCH_KEYWORD' => $filter_search_keyword,
            'SONGS_COUNT' => count($results),
            'SONG_DATA' => $results
        ];
        $data['html'] = loadPage("search/songs", $pagedata);
        $data['status'] = 200;

        break;
    case 'albums':
        $querya = array();
        $sql = 'SELECT * FROM `'. T_ALBUMS .'` WHERE ';
        if(is_array($prices) ) {
            foreach ($prices as $key => $value) {
                $value = Secure($value);
                $querya[] =  "price LIKE '$value'";
            }
            $db->where('(' . implode($querya, ' OR ') . ')');
        }
        if(is_array($geners) && !empty($geners)) {
            $db->where('category_id', $geners, 'IN');
        }
        if(!empty($filter_search_keyword)) {
            $db->where("(title LIKE '%$filter_search_keyword%' OR description LIKE '%$filter_search_keyword%')");
        }
        $results = $db->get(T_ALBUMS,10);
        $pagedata = [
            'SEARCH_KEYWORD' => $filter_search_keyword,
            'ALBUMS_COUNT' => count($results),
            'ALBUMS_DATA' => $results
        ];
        $data['html'] = loadPage("search/albums", $pagedata);
        $data['status'] = 200;

        break;
    default:
        $data['status'] = 400;
        break;
}