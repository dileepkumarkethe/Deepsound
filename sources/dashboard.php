<?php 

if (IS_LOGGED == false) {
	header("Location: $site_url");
	exit();
}

$currentDays = date('t');

$dayStart = "";
$playsThisMonth = '';
$LikesThisMonth = '';
$DownloadsThisMonth = '';
$SalesThisMonth = '';

for ($i=1; $i <= $currentDays; $i++) { 
	$f = sprintf("%02d", $i);
	$dayStart .= "'$f',";

	$thisMonthPlayCount = $db->where("track_id IN (SELECT id FROM " . T_SONGS . " WHERE user_id = ?) AND DAY(FROM_UNIXTIME(`time`)) = $i", [$user->id])->getValue(T_VIEWS, 'count(*)');
	$playsThisMonth .= "'$thisMonthPlayCount',";

	$thisMonthLikeCount = $db->where("track_id IN (SELECT id FROM " . T_SONGS . " WHERE user_id = ?) AND DAY(FROM_UNIXTIME(`time`)) = $i", [$user->id])->getValue(T_LIKES, 'count(*)');
	$LikesThisMonth .= "'$thisMonthLikeCount',";

	$thisMonthDownloadsCount = $db->where("track_id IN (SELECT id FROM " . T_SONGS . " WHERE user_id = ?) AND DAY(FROM_UNIXTIME(`time`)) = $i", [$user->id])->getValue(T_DOWNLOADS, 'count(*)');
	$DownloadsThisMonth .= "'$thisMonthDownloadsCount',";

	$thisMonthSalesCount = $db->where("track_id IN (SELECT id FROM " . T_SONGS . " WHERE user_id = ?) AND DAY(FROM_UNIXTIME(`time`)) = $i", [$user->id])->getValue(T_PURCHAES, 'count(*)');
	$SalesThisMonth .= "'$thisMonthSalesCount',";
}

$thisMonthDays =  "[" . $dayStart . "]";

$playsThisMonth =  "[" . $playsThisMonth . "]";
$LikesThisMonth =  "[" . $LikesThisMonth . "]";
$DownloadsThisMonth =  "[" . $DownloadsThisMonth . "]";
$SalesThisMonth =  "[" . $SalesThisMonth . "]";

$mostPlayedSongsHTML = '<div class="no-track-found"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M21,3V15.5A3.5,3.5 0 0,1 17.5,19A3.5,3.5 0 0,1 14,15.5A3.5,3.5 0 0,1 17.5,12C18.04,12 18.55,12.12 19,12.34V6.47L9,8.6V17.5A3.5,3.5 0 0,1 5.5,21A3.5,3.5 0 0,1 2,17.5A3.5,3.5 0 0,1 5.5,14C6.04,14 6.55,14.12 7,14.34V6L21,3Z" /></svg>' . lang("No songs found") . '</div>';
$mostCommentedSongHTML = '<div class="no-track-found"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M21,3V15.5A3.5,3.5 0 0,1 17.5,19A3.5,3.5 0 0,1 14,15.5A3.5,3.5 0 0,1 17.5,12C18.04,12 18.55,12.12 19,12.34V6.47L9,8.6V17.5A3.5,3.5 0 0,1 5.5,21A3.5,3.5 0 0,1 2,17.5A3.5,3.5 0 0,1 5.5,14C6.04,14 6.55,14.12 7,14.34V6L21,3Z" /></svg>' . lang("No songs found") . '</div>';
$mostLikedSongsHTML = '<div class="no-track-found"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M21,3V15.5A3.5,3.5 0 0,1 17.5,19A3.5,3.5 0 0,1 14,15.5A3.5,3.5 0 0,1 17.5,12C18.04,12 18.55,12.12 19,12.34V6.47L9,8.6V17.5A3.5,3.5 0 0,1 5.5,21A3.5,3.5 0 0,1 2,17.5A3.5,3.5 0 0,1 5.5,14C6.04,14 6.55,14.12 7,14.34V6L21,3Z" /></svg>' . lang("No songs found") . '</div>';
$mostDownloadedSongsHTML = '<div class="no-track-found"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M21,3V15.5A3.5,3.5 0 0,1 17.5,19A3.5,3.5 0 0,1 14,15.5A3.5,3.5 0 0,1 17.5,12C18.04,12 18.55,12.12 19,12.34V6.47L9,8.6V17.5A3.5,3.5 0 0,1 5.5,21A3.5,3.5 0 0,1 2,17.5A3.5,3.5 0 0,1 5.5,14C6.04,14 6.55,14.12 7,14.34V6L21,3Z" /></svg>' . lang("No songs found") . '</div>';

$mostPlayedSongs = $db->rawQuery("SELECT " . T_SONGS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . "
FROM " . T_SONGS . " LEFT JOIN " . T_VIEWS . " ON " . T_SONGS . ".id = " . T_VIEWS . ".track_id
WHERE " . T_SONGS . ".user_id = " . $user->id . "
GROUP BY " . T_SONGS . ".id
ORDER BY " . T_VIEWS . " DESC LIMIT 7");

if (!empty($mostPlayedSongs)) {
	$mostPlayedSongsHTML = "";
	foreach ($mostPlayedSongs as $key => $userSong) {
		$songData = songData($userSong->id);
		$songData->songArray['count'] = $userSong->views;
		$songData->songArray['key'] = $key + 1;
		$mostPlayedSongsHTML .= loadPage("dashboard/list", $songData->songArray);
	}
}

$mostCommentedSongs = $db->rawQuery("SELECT " . T_SONGS . ".*, COUNT(" . T_COMMENTS . ".id) AS " . T_COMMENTS . "
FROM " . T_SONGS . " LEFT JOIN " . T_COMMENTS . " ON " . T_SONGS . ".id = " . T_COMMENTS . ".track_id
WHERE " . T_SONGS . ".user_id = " . $user->id . "
GROUP BY " . T_SONGS . ".id
ORDER BY " . T_COMMENTS . " DESC LIMIT 7");

if (!empty($mostCommentedSongs)) {
	$mostCommentedSongHTML = "";
	foreach ($mostCommentedSongs as $key => $userSong) {
		$songData = songData($userSong->id);
		$songData->songArray['count'] = $userSong->comments;
		$songData->songArray['key'] = $key + 1;
		$mostCommentedSongHTML .= loadPage("dashboard/list", $songData->songArray);
	}
}

$mostLikedSongs = $db->rawQuery("SELECT " . T_SONGS . ".*, COUNT(" . T_LIKES . ".id) AS " . T_LIKES . "
FROM " . T_SONGS . " LEFT JOIN " . T_LIKES . " ON " . T_SONGS . ".id = " . T_LIKES . ".track_id
WHERE " . T_SONGS . ".user_id = " . $user->id . "
GROUP BY " . T_SONGS . ".id
ORDER BY " . T_LIKES . " DESC LIMIT 7");

if (!empty($mostLikedSongs)) {
	$mostLikedSongsHTML = "";
	foreach ($mostLikedSongs as $key => $userSong) {
		$songData = songData($userSong->id);
		$songData->songArray['count'] = $userSong->likes;
		$songData->songArray['key'] = $key + 1;
		$mostLikedSongsHTML .= loadPage("dashboard/list", $songData->songArray);
	}
}

$mostDownloadedSongs = $db->rawQuery("SELECT " . T_SONGS . ".*, COUNT(" . T_DOWNLOADS . ".id) AS " . T_DOWNLOADS . "
FROM " . T_SONGS . " LEFT JOIN " . T_DOWNLOADS . " ON " . T_SONGS . ".id = " . T_DOWNLOADS . ".track_id
WHERE " . T_SONGS . ".user_id = " . $user->id . "
GROUP BY " . T_SONGS . ".id
ORDER BY " . T_DOWNLOADS . " DESC LIMIT 7");

if (!empty($mostDownloadedSongs)) {
	$mostDownloadedSongsHTML = "";
	foreach ($mostDownloadedSongs as $key => $userSong) {
		$songData = songData($userSong->id);
		$songData->songArray['count'] = $userSong->downloads;
		$songData->songArray['key'] = $key + 1;
		$mostDownloadedSongsHTML .= loadPage("dashboard/list", $songData->songArray);
	}
}

$recentSalesHTML = '<div class="no-track-found"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M5,6H23V18H5V6M14,9A3,3 0 0,1 17,12A3,3 0 0,1 14,15A3,3 0 0,1 11,12A3,3 0 0,1 14,9M9,8A2,2 0 0,1 7,10V14A2,2 0 0,1 9,16H19A2,2 0 0,1 21,14V10A2,2 0 0,1 19,8H9M1,10H3V20H19V22H1V10Z" /></svg>' . lang("No sales found") . '</div>';

$recentSales = $db->where('track_id IN (SELECT id FROM ' . T_SONGS . ' WHERE user_id = ?)', [$user->id])->get(T_PURCHAES, 50);
if (!empty($recentSales)) {
	$recentSalesHTML = "";
	foreach ($recentSales as $key => $userSale) {
		$songData = songData($userSale->track_id);
		$songData->songArray['count'] = '$' . $userSale->final_price;
		$songData->songArray['key'] = '#' . $userSale->id;
		$recentSalesHTML .= loadPage("dashboard/list", $songData->songArray);
	}
}


$music->site_title = lang('Dashboard');
$music->site_description = $music->config->description;
$music->site_pagename = "dashboard";
$music->site_content = loadPage("dashboard/content", [
	'TOTAL_SONGS' => number_format($db->where('user_id', $user->id)->getValue(T_SONGS, 'count(*)')),
	'TOTAL_PLAYS' => number_format($db->where('track_id IN (SELECT id FROM ' . T_SONGS . ' WHERE user_id = ?)', [$user->id])->getValue(T_VIEWS, 'count(*)')),
	'TOTAL_DOWNLOADS' => number_format($db->where('track_id IN (SELECT id FROM ' . T_SONGS . ' WHERE user_id = ?)', [$user->id])->getValue(T_DOWNLOADS, 'count(*)')),
	'TOTAL_SALES' => number_format($db->where('track_id IN (SELECT id FROM ' . T_SONGS . ' WHERE user_id = ?)', [$user->id])->getValue(T_PURCHAES, 'SUM(final_price)'), 2),
	'TOTAL_SALES_THIS_WEEK' => number_format($db->where('track_id IN (SELECT id FROM ' . T_SONGS . ' WHERE user_id = ?) AND MONTH(`timestamp`) = MONTH(CURDATE())', [$user->id])->getValue(T_PURCHAES, 'SUM(final_price)'), 2),
	'TOTAL_SALES_TODAY' => number_format($db->where('track_id IN (SELECT id FROM ' . T_SONGS . ' WHERE user_id = ?) AND DATE(`timestamp`) = CURDATE()', [$user->id])->getValue(T_PURCHAES, 'SUM(final_price)'), 2),

	'THIS_MONTH' => $thisMonthDays,

	'PLAYS_THIS_MONTH' => $playsThisMonth,
	'LIKES_THIS_MONTH' => $LikesThisMonth,
	'DOWNLOADS_THIS_MONTH' => $DownloadsThisMonth,
	'SALES_THIS_MONTH' => $SalesThisMonth,

	'MOST_PLAYED_SONGS' => $mostPlayedSongsHTML,
	'MOST_COMMENTED_SONG' => $mostCommentedSongHTML,
	'MOST_LIKED_SONGS' => $mostLikedSongsHTML,
	'MOST_DOWNLOADED_SONGS' => $mostDownloadedSongsHTML,

	'RECENT_SALES' => $recentSalesHTML
]);