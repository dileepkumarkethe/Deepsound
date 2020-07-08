<?php
if (empty($path['options'][1])) {
    header("Location: $site_url/404");
    exit();
}
$audio_id = secure($path['options'][1]);

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

$getIDAudio = $db->where('audio_id', $audio_id)->getValue(T_SONGS, 'id');

if (empty($getIDAudio)) {
    header("Location: $site_url/404");
    exit();
}

$songData = songData($getIDAudio);
if ($songData->IsOwner == true || IsAdmin()) {

}else{
    header("Location: $site_url");
    exit();
}

$month_days = 31;
if ($period == 'today') {
    $array = array('00' => 0 ,'01' => 0 ,'02' => 0 ,'03' => 0 ,'04' => 0 ,'05' => 0 ,'06' => 0 ,'07' => 0 ,'08' => 0 ,'09' => 0 ,'10' => 0 ,'11' => 0 ,'12' => 0 ,'13' => 0 ,'14' => 0 ,'15' => 0 ,'16' => 0 ,'17' => 0 ,'18' => 0 ,'19' => 0 ,'20' => 0 ,'21' => 0 ,'22' => 0 ,'23' => 0);
    $day_likes_array = $array;
    $day_dislikes_array = $array;
    $day_views_array = $array;
    $day_sales_array = $array;

    $total_earning = 0;
    $today_start = strtotime(date('M')." ".date('d').", ".date('Y')." 12:00am");
    $today_end = strtotime(date('M')." ".date('d').", ".date('Y')." 11:59pm");

    $today_likes = $db->where('time',$today_start,'>=')->where('time',$today_end,'<=')->where('track_id',$songData->id)->get(T_LIKES);
    $today_dislikes = $db->where('time',$today_start,'>=')->where('time',$today_end,'<=')->where('track_id',$songData->id)->get(T_DISLIKES);
    $today_views = $db->where('time',$today_start,'>=')->where('time',$today_end,'<=')->where('track_id',$songData->id)->get(T_VIEWS);

    $today_sales = $db->where('time',$today_start,'>=')->where('time',$today_end,'<=')->where('track_id',$songData->id)->get(T_PURCHAES);


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
    foreach ($today_sales as $key => $value) {
        $total_earning += $value->final_price;
        $hour = date('H',$value->time);
        if (in_array($hour, array_keys($day_sales_array))) {
            $day_sales_array[$hour] += 1;
        }
    }

    $music->cat_type = 'today';
    $music->chart_title = $lang->today;
    $music->chart_text = date("l");
    $music->likes_array = implode(', ', $day_likes_array);
    $music->dislikes_array = implode(', ', $day_dislikes_array);
    $music->views_array = implode(', ', $day_views_array);
    $music->sales_array = implode(', ', $day_sales_array);

    $count_likes = count($today_likes);
    $count_dislikes = count($today_dislikes);
    $count_views = count($today_views);
    $count_sales = count($today_sales);

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
    $week_sales_array = $array;
    $total_earning = 0;

    $week_likes = $db->where('time',$week_start,'>=')->where('time',$week_end,'<=')->where('track_id',$songData->id)->get(T_LIKES);
    $week_dislikes = $db->where('time',$week_start,'>=')->where('time',$week_end,'<=')->where('track_id',$songData->id)->get(T_DISLIKES);
    $week_views = $db->where('time',$week_start,'>=')->where('time',$week_end,'<=')->where('track_id',$songData->id)->get(T_VIEWS);

    $week_sales = $db->where('time',$week_start,'>=')->where('time',$week_end,'<=')->where('track_id',$songData->id)->get(T_PURCHAES);


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
    foreach ($week_sales as $key => $value) {
        $total_earning += $value->final_price;
        $day_week = date('l',$value->time);
        if (in_array($day_week, array_keys($week_sales_array))) {
            $week_sales_array[$day_week] += 1;
        }
    }

    $music->cat_type = 'week';
    $music->chart_title = $lang->this_week;
    $music->chart_text = date('y/M/d',$week_start)." To ".date('y/M/d',$week_end);
    $music->likes_array = implode(', ', $week_likes_array);
    $music->dislikes_array = implode(', ', $week_dislikes_array);
    $music->views_array = implode(', ', $week_views_array);
    $music->sales_array = implode(', ', $week_sales_array);

    $count_likes = count($week_likes);
    $count_dislikes = count($week_dislikes);
    $count_views = count($week_views);
    $count_sales = count($week_sales);


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
    $total_earning = 0;
    $month_sales_array = $array;

    $month_likes = $db->where('time',$this_month_start,'>=')->where('time',$this_month_end,'<=')->where('track_id',$songData->id)->get(T_LIKES);
    $month_dislikes = $db->where('time',$this_month_start,'>=')->where('time',$this_month_end,'<=')->where('track_id',$songData->id)->get(T_DISLIKES);
    $month_views = $db->where('time',$this_month_start,'>=')->where('time',$this_month_end,'<=')->where('track_id',$songData->id)->get(T_VIEWS);

    $month_sales = $db->where('time',$this_month_start,'>=')->where('time',$this_month_end,'<=')->where('track_id',$songData->id)->get(T_PURCHAES);


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
    foreach ($month_sales as $key => $value) {
        $total_earning += $value->final_price;
        $day = date('d',$value->time);
        if (in_array($day, array_keys($month_sales_array))) {
            $month_sales_array[$day] += 1;
        }
    }

    $music->cat_type = 'month';
    $music->chart_title = $lang->this_month;
    $music->chart_text = date("M");
    $music->likes_array = implode(', ', $month_likes_array);
    $music->dislikes_array = implode(', ', $month_dislikes_array);
    $music->views_array = implode(', ', $month_views_array);
    $music->sales_array = implode(', ', $month_sales_array);

    $count_likes = count($month_likes);
    $count_dislikes = count($month_dislikes);
    $count_views = count($month_views);
    $count_sales = count($month_sales);
}

$songData->owner  = false;

if (IS_LOGGED == true) {
    $songData->owner  = ($user->id == $songData->publisher->id) ? true : false;
}

$music->albumData = [];
if (!empty($songData->album_id)) {
    $music->albumData = $db->where('id', $songData->album_id)->getOne(T_ALBUMS);
}
$isPurchased = isTrackPurchased($songData->id);
$music->songData = $songData;

$t_desc = $songData->description;

$music->site_title = html_entity_decode($songData->title);
$music->site_description = $songData->description;
$music->site_pagename = "track_statistics";
$music->site_content = loadPage("track/statistics", [
    'USER_DATA' => $songData->publisher,
    't_thumbnail' => $songData->thumbnail,
    't_song' => $songData->secure_url,
    't_title' => $songData->title,
    't_description' => $t_desc,
    't_time' => time_Elapsed_String($songData->time),
    'ts_time' => date('c',$songData->time),
    't_audio_id' => $songData->audio_id,
    't_id' => $songData->id,
    't_price' => $songData->price,
    'category_name' => $songData->category_name,
    't_shares' => number_format_mm($songData->shares),
    'COUNT_LIKES' => number_format_mm($count_likes),
    'COUNT_DISLIKES' => number_format_mm($count_dislikes),
    'COUNT_VIEWS' => number_format_mm($count_views),
    'COUNT_SALES' => number_format_mm($count_sales),
    'COUNT_USER_SONGS' => $db->where('user_id', $songData->publisher->id)->getValue(T_SONGS, 'count(*)'),
    'COUNT_USER_FOLLOWERS' => number_format_mm($db->where('following_id', $songData->publisher->id)->getValue(T_FOLLOWERS, 'COUNT(*)')),
    'period' => $period,
    'LIKES' => '',
    'DISLIKES' => '',
    'VIEWS' => '',
    'month_days' => $month_days,
    'total_earning' => number_format_mm($total_earning)
]);