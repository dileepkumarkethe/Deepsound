<?php
error_reporting(E_ALL);
ini_set('display_startup_errors', true);
ini_set('display_errors', true);
if (empty($path['options'][1])) {
    header("Location: $site_url/404");
    exit();
}
$album_id = secure($path['options'][1]);

$period = secure($path['options'][2]);
if(empty($period)){
    $period = 'today';
}


$count_likes = 0;
$count_dislikes = 0;
$count_views = 0;


if (IS_LOGGED) {
    $db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
}

$getIDalbum = $db->where('album_id', $album_id)->getValue(T_ALBUMS, 'id');

if (empty($getIDalbum)) {
    header("Location: $site_url/404");
    exit();
}

$albumData = albumData($getIDalbum);

$month_days = 31;
if ($period == 'today') {
    $array = array('00' => 0 ,'01' => 0 ,'02' => 0 ,'03' => 0 ,'04' => 0 ,'05' => 0 ,'06' => 0 ,'07' => 0 ,'08' => 0 ,'09' => 0 ,'10' => 0 ,'11' => 0 ,'12' => 0 ,'13' => 0 ,'14' => 0 ,'15' => 0 ,'16' => 0 ,'17' => 0 ,'18' => 0 ,'19' => 0 ,'20' => 0 ,'21' => 0 ,'22' => 0 ,'23' => 0);
    $day_likes_array = $array;
    $day_dislikes_array = $array;
    $day_views_array = $array;

    $today_start = strtotime(date('M')." ".date('d').", ".date('Y')." 12:00am");
    $today_end = strtotime(date('M')." ".date('d').", ".date('Y')." 11:59pm");

    //$today_likes = $db->where('time',$today_start,'>=')->where('time',$today_end,'<=')->where('track_id',$albumData->id)->get(T_LIKES);
    //$today_dislikes = $db->where('time',$today_start,'>=')->where('time',$today_end,'<=')->where('track_id',$albumData->id)->get(T_DISLIKES);
    //$today_views = $db->where('time',$today_start,'>=')->where('time',$today_end,'<=')->where('album_id',$albumData->id)->get(T_VIEWS);

    $today_likes = $db->rawQuery( 'SELECT * FROM '.T_SONGS.' INNER JOIN '.T_LIKES.' ON ('.T_SONGS.'.id = '.T_LIKES.'.track_id) WHERE '.T_SONGS.'.album_id = ' . $albumData->id .' AND '.T_LIKES.'.`time` >= ' . $today_start . ' AND '.T_LIKES.'.`time` <= '.$today_end );
    $today_dislikes = $db->rawQuery( 'SELECT * FROM '.T_SONGS.' INNER JOIN '.T_DISLIKES.' ON ('.T_SONGS.'.id = '.T_DISLIKES.'.track_id) WHERE '.T_SONGS.'.album_id = ' . $albumData->id .' AND '.T_DISLIKES.'.`time` >= ' . $today_start . ' AND '.T_DISLIKES.'.`time` <= '.$today_end );
    $today_views = $db->rawQuery( 'SELECT * FROM '.T_SONGS.' INNER JOIN '.T_VIEWS.' ON ('.T_SONGS.'.id = '.T_VIEWS.'.track_id) WHERE '.T_SONGS.'.album_id = ' . $albumData->id .' AND '.T_VIEWS.'.`time` >= ' . $today_start . ' AND '.T_VIEWS.'.`time` <= '.$today_end );


    foreach ($today_likes as $key => $value) {
        $hour = date('H',$value->time);
        if (in_array($hour, array_keys($day_likes_array))) {
            $day_likes_array[$hour] += 1;
        }
    }
    foreach ($today_dislikes as $key => $value) {
        $hour = date('H',$value->time);
        if (in_array($hour, array_keys($day_dislikes_array))) {
            $day_dislikes_array[$hour] += 1;
        }
    }
    foreach ($today_views as $key => $value) {
        $hour = date('H',$value->time);
        if (in_array($hour, array_keys($day_views_array))) {
            $day_views_array[$hour] += 1;
        }
    }

    $music->cat_type = 'today';
    $music->chart_title = $lang->today;
    $music->chart_text = date("l");
    $music->likes_array = implode(', ', $day_likes_array);
    $music->dislikes_array = implode(', ', $day_dislikes_array);
    $music->views_array = implode(', ', $day_views_array);

    $count_likes = count($today_likes);
    $count_dislikes = count($today_dislikes);
    $count_views = count($today_views);


} elseif ($period == 'week') {
    $time = strtotime(date(' l').", ".date('M')." ".date('d').", ".date('Y'));
    if (date('l') == 'Saturday') {
        $week_start = strtotime(date('M')." ".date('d').", ".date('Y')." 12:00am");
    } else {
        $week_start = strtotime('last saturday, 12:00am', $time);
    }
    if (date('l') == 'Friday') {
        $week_end = strtotime(date('M')." ".date('d').", ".date('Y')." 11:59pm");
    } else {
        $week_end = strtotime('next Friday, 11:59pm', $time);
    }
    $array = array('Saturday' => 0 , 'Sunday' => 0 , 'Monday' => 0 , 'Tuesday' => 0 , 'Wednesday' => 0 , 'Thursday' => 0 , 'Friday' => 0);
    $week_likes_array = $array;
    $week_dislikes_array = $array;
    $week_views_array = $array;
//
//    $week_likes = $db->where('time',$week_start,'>=')->where('time',$week_end,'<=')->where('track_id',$albumData->id)->get(T_LIKES);
//    $week_dislikes = $db->where('time',$week_start,'>=')->where('time',$week_end,'<=')->where('track_id',$albumData->id)->get(T_DISLIKES);
//    $week_views = $db->where('time',$week_start,'>=')->where('time',$week_end,'<=')->where('track_id',$albumData->id)->get(T_VIEWS);

    $week_likes = $db->rawQuery( 'SELECT * FROM '.T_SONGS.' INNER JOIN '.T_LIKES.' ON ('.T_SONGS.'.id = '.T_LIKES.'.track_id) WHERE '.T_SONGS.'.album_id = ' . $albumData->id .' AND '.T_LIKES.'.`time` >= ' . $week_start . ' AND '.T_LIKES.'.`time` <= '.$week_end );
    $week_dislikes = $db->rawQuery( 'SELECT * FROM '.T_SONGS.' INNER JOIN '.T_DISLIKES.' ON ('.T_SONGS.'.id = '.T_DISLIKES.'.track_id) WHERE '.T_SONGS.'.album_id = ' . $albumData->id .' AND '.T_DISLIKES.'.`time` >= ' . $week_start . ' AND '.T_DISLIKES.'.`time` <= '.$week_end );
    $week_views = $db->rawQuery( 'SELECT * FROM '.T_SONGS.' INNER JOIN '.T_VIEWS.' ON ('.T_SONGS.'.id = '.T_VIEWS.'.track_id) WHERE '.T_SONGS.'.album_id = ' . $albumData->id .' AND '.T_VIEWS.'.`time` >= ' . $week_start . ' AND '.T_VIEWS.'.`time` <= '.$week_end );


    foreach ($week_likes as $key => $value) {
        $day_week = date('l',$value->time);
        if (in_array($day_week, array_keys($week_likes_array))) {
            $week_likes_array[$day_week] += 1;
        }
    }
    foreach ($week_dislikes as $key => $value) {
        $day_week = date('l',$value->time);
        if (in_array($day_week, array_keys($week_dislikes_array))) {
            $week_dislikes_array[$day_week] += 1;
        }
    }
    foreach ($week_views as $key => $value) {
        $day_week = date('l',$value->time);
        if (in_array($day_week, array_keys($week_views_array))) {
            $week_views_array[$day_week] += 1;
        }
    }

    $music->cat_type = 'week';
    $music->chart_title = $lang->this_week;
    $music->chart_text = date('y/M/d',$week_start)." To ".date('y/M/d',$week_end);
    $music->likes_array = implode(', ', $week_likes_array);
    $music->dislikes_array = implode(', ', $week_dislikes_array);
    $music->views_array = implode(', ', $week_views_array);

    $count_likes = count($week_likes);
    $count_dislikes = count($week_dislikes);
    $count_views = count($week_views);


} elseif ($period == 'month') {
    $this_month_start = strtotime("1 ".date('M')." ".date('Y')." 12:00am");
    $this_month_end = strtotime(cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'))." ".date('M')." ".date('Y')." 11:59pm");
    $array = array_fill(1, cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')),0);
    if (cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')) == 31) {
        $array = array('01' => 0 ,'02' => 0 ,'03' => 0 ,'04' => 0 ,'05' => 0 ,'06' => 0 ,'07' => 0 ,'08' => 0 ,'09' => 0 ,'10' => 0 ,'11' => 0 ,'12' => 0 ,'13' => 0 ,'14' => 0 ,'15' => 0 ,'16' => 0 ,'17' => 0 ,'18' => 0 ,'19' => 0 ,'20' => 0 ,'21' => 0 ,'22' => 0 ,'23' => 0,'24' => 0 ,'25' => 0 ,'26' => 0 ,'27' => 0 ,'28' => 0 ,'29' => 0 ,'30' => 0 ,'31' => 0);
    }elseif (cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')) == 30) {
        $array = array('01' => 0 ,'02' => 0 ,'03' => 0 ,'04' => 0 ,'05' => 0 ,'06' => 0 ,'07' => 0 ,'08' => 0 ,'09' => 0 ,'10' => 0 ,'11' => 0 ,'12' => 0 ,'13' => 0 ,'14' => 0 ,'15' => 0 ,'16' => 0 ,'17' => 0 ,'18' => 0 ,'19' => 0 ,'20' => 0 ,'21' => 0 ,'22' => 0 ,'23' => 0,'24' => 0 ,'25' => 0 ,'26' => 0 ,'27' => 0 ,'28' => 0 ,'29' => 0 ,'30' => 0);
    }elseif (cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')) == 29) {
        $array = array('01' => 0 ,'02' => 0 ,'03' => 0 ,'04' => 0 ,'05' => 0 ,'06' => 0 ,'07' => 0 ,'08' => 0 ,'09' => 0 ,'10' => 0 ,'11' => 0 ,'12' => 0 ,'13' => 0 ,'14' => 0 ,'15' => 0 ,'16' => 0 ,'17' => 0 ,'18' => 0 ,'19' => 0 ,'20' => 0 ,'21' => 0 ,'22' => 0 ,'23' => 0,'24' => 0 ,'25' => 0 ,'26' => 0 ,'27' => 0 ,'28' => 0 ,'29' => 0);
    }elseif (cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')) == 28) {
        $array = array('01' => 0 ,'02' => 0 ,'03' => 0 ,'04' => 0 ,'05' => 0 ,'06' => 0 ,'07' => 0 ,'08' => 0 ,'09' => 0 ,'10' => 0 ,'11' => 0 ,'12' => 0 ,'13' => 0 ,'14' => 0 ,'15' => 0 ,'16' => 0 ,'17' => 0 ,'18' => 0 ,'19' => 0 ,'20' => 0 ,'21' => 0 ,'22' => 0 ,'23' => 0,'24' => 0 ,'25' => 0 ,'26' => 0 ,'27' => 0 ,'28' => 0);
    }
    $month_days = count($array);
    $month_likes_array = $array;
    $month_dislikes_array = $array;
    $month_views_array = $array;
    $month_comments_array = $array;

//    $month_likes = $db->where('time',$this_month_start,'>=')->where('time',$this_month_end,'<=')->where('track_id',$albumData->id)->get(T_LIKES);
//    $month_dislikes = $db->where('time',$this_month_start,'>=')->where('time',$this_month_end,'<=')->where('track_id',$albumData->id)->get(T_DISLIKES);
//    $month_views = $db->where('time',$this_month_start,'>=')->where('time',$this_month_end,'<=')->where('track_id',$albumData->id)->get(T_VIEWS);

    $month_likes = $db->rawQuery( 'SELECT * FROM '.T_SONGS.' INNER JOIN '.T_LIKES.' ON ('.T_SONGS.'.id = '.T_LIKES.'.track_id) WHERE '.T_SONGS.'.album_id = ' . $albumData->id .' AND '.T_LIKES.'.`time` >= ' . $this_month_start . ' AND '.T_LIKES.'.`time` <= '.$this_month_end );
    $month_dislikes = $db->rawQuery( 'SELECT * FROM '.T_SONGS.' INNER JOIN '.T_DISLIKES.' ON ('.T_SONGS.'.id = '.T_DISLIKES.'.track_id) WHERE '.T_SONGS.'.album_id = ' . $albumData->id .' AND '.T_DISLIKES.'.`time` >= ' . $this_month_start . ' AND '.T_DISLIKES.'.`time` <= '.$this_month_end );
    $month_views = $db->rawQuery( 'SELECT * FROM '.T_SONGS.' INNER JOIN '.T_VIEWS.' ON ('.T_SONGS.'.id = '.T_VIEWS.'.track_id) WHERE '.T_SONGS.'.album_id = ' . $albumData->id .' AND '.T_VIEWS.'.`time` >= ' . $this_month_start . ' AND '.T_VIEWS.'.`time` <= '.$this_month_end );

    foreach ($month_likes as $key => $value) {
        $day = date('d',$value->time);
        if (in_array($day, array_keys($month_likes_array))) {
            $month_likes_array[$day] += 1;
        }
    }
    foreach ($month_dislikes as $key => $value) {
        $day = date('d',$value->time);
        if (in_array($day, array_keys($month_dislikes_array))) {
            $month_dislikes_array[$day] += 1;
        }
    }
    foreach ($month_views as $key => $value) {
        $day = date('d',$value->time);
        if (in_array($day, array_keys($month_views_array))) {
            $month_views_array[$day] += 1;
        }
    }

    $music->cat_type = 'month';
    $music->chart_title = $lang->this_month;
    $music->chart_text = date("M");
    $music->likes_array = implode(', ', $month_likes_array);
    $music->dislikes_array = implode(', ', $month_dislikes_array);
    $music->views_array = implode(', ', $month_views_array);

    $count_likes = count($month_likes);
    $count_dislikes = count($month_dislikes);
    $count_views = count($month_views);
}

$music->albumData = $albumData;

$albumData->owner  = false;

if (IS_LOGGED == true) {
    $albumData->owner  = ($user->id == $albumData->publisher->id) ? true : false;
}

$t_desc = $albumData->description;

$music->site_title = html_entity_decode($albumData->title);
$music->site_description = $albumData->description;
$music->site_pagename = "album_statistics";
$music->site_content = loadPage("album/statistics", [
    'USER_DATA' => $albumData->publisher,
    't_thumbnail' => $albumData->thumbnail,
    't_title' => $albumData->title,
    't_description' => $t_desc,
    't_time' => time_Elapsed_String($albumData->time),
    'ts_time' => date('c',$albumData->time),
    't_album_id' => $albumData->album_id,
    't_id' => $albumData->id,
    't_price' => $albumData->price,
    'category_name' => $albumData->category_name,
    'COUNT_LIKES' => number_format_mm($count_likes),
    'COUNT_DISLIKES' => number_format_mm($count_dislikes),
    'COUNT_VIEWS' => number_format_mm($count_views),
    'COUNT_USER_SONGS' => $db->where('user_id', $albumData->publisher->id)->getValue(T_SONGS, 'count(*)'),
    'COUNT_USER_FOLLOWERS' => number_format_mm($db->where('following_id', $albumData->publisher->id)->getValue(T_FOLLOWERS, 'COUNT(*)')),
    'period' => $period,
    'LIKES' => '',
    'DISLIKES' => '',
    'VIEWS' => '',
    'month_days' => $month_days
]);