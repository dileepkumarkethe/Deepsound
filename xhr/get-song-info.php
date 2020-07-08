<?php

$track_id = 0;
if (!empty($_GET['id'])) {
    $track_id = secure($_GET['id']);
}
if (empty($track_id)) {
    exit("Invalid Track ID");
}

$id = secure($_GET['id']);
$getSong = $db->where('audio_id', $_GET['id'])->getOne(T_SONGS);
if (empty($getSong)) {
    exit("Invalid Track ID");
}


$data['status'] = 400;

$getSong = songData($getSong->id);

$time_seconds = formatSeconds($getSong->duration);
$waves = '';


$dark = $getSong->dark_wave;
$light = $getSong->light_wave;
$bar = '#363636';
$opacity = '';

if( $music->config->ffmpeg_system == 'off'){
    $dark = $getSong->light_wave;
    $light = $getSong->dark_wave;

    if($_COOKIE['mode'] == 'day'){
        $bar = 'rgb(191, 191, 191)';
        $opacity = 'opacity: 0.5;';
        if($getSong->ffmpeg == 0){
            $dark = $getSong->light_wave;
            $light = $getSong->dark_wave;
        }else{
            $dark = $getSong->dark_wave;
            $light = $getSong->light_wave;
        }

    }else{
        $opacity = '';
        if($getSong->ffmpeg == 0){
            $dark = $getSong->light_wave;
            $light = $getSong->dark_wave;
        }else{
            $dark = $getSong->dark_wave;
            $light = $getSong->light_wave;
        }

    }
}else{
    $dark = $getSong->dark_wave;
    $light = $getSong->light_wave;

    if($_COOKIE['mode'] == 'day'){
        $dark = str_replace('_dark.png','_day.png',$getSong->dark_wave);
        if(!file_exists( $dark ) ){
            $dark = $getSong->light_wave;
            $light = $getSong->dark_wave;
        }
        $bar = 'rgb(191, 191, 191)';
    }else{
        $dark = str_replace('_day.png','_dark.png',$getSong->dark_wave);

        if($getSong->ffmpeg == 0){
            $dark = $getSong->light_wave;
            $light = $getSong->dark_wave;
        }else{
            $dark = $getSong->dark_wave;
            $light = $getSong->light_wave;
        }

    }

}

$rl = 'left: 0;border-left: inherit!important;border-right: 1px solid '.$bar.' !important;';
if ( $music->language_type == 'rtl' ){
    $rl = 'right: 0;border-right: inherit!important;border-left: 1px solid '.$bar.' !important;';
}

if (!empty($getSong->dark_wave) && !empty($getSong->light_wave)) {
    $waves = '
	<div id="waveform" style="width: 100% !important;" data-id="' . $getSong->audio_id . '">
			<div class="images" style="width: 100%" id="dark-waves">
				<img src="' . getMedia($dark) . '" style="width: 100%;" id="dark-wave">
				<div class="comment-waves "></div>
				<div style="width: 0%; z-index: 111; position: absolute; overflow: hidden; top: 0; <?php echo $rl;?> " id="light-wave">
					<img src="' . getMedia($light) . '">
				</div>
			</div>
	</div>';
}

$getSongComments = $db->where('track_id', $getSong->id)->orderBy('id', 'DESC')->get(T_COMMENTS, 10);
$comment_html = '';
$comments_on_wave = '';
if (!empty($getSongComments)) {
    foreach ($getSongComments as $key => $comment) {
        $comment = getComment($comment, false);
        $commentUser = userData($comment->user_id);
        $music->comment = $comment;
        $comment_html .= loadPage('track/comment-list', [
            'comment_id' => $comment->id,
            'comment_seconds' => $comment->songseconds,
            'comment_percentage' => $comment->songpercentage,
            'USER_DATA' => $commentUser,
            'comment_text' => $comment->value,
            'comment_posted_time' => $comment->posted,
            'tcomment_posted_time' => date('c',strtotime($comment->posted)),
            'comment_seconds_formatted' => $comment->secondsFormated,
            'comment_song_id' => $getSong->audio_id,
            'comment_song_track_id' => $comment->track_id,
        ]);
        $comments_on_wave .= '<div class="comment-on-wave small-waves-icons" style="left: ' . ($comment->songpercentage * 100). '%;"><img src="' . $commentUser->avatar . '"><div class="comment-on-wave-data"><div><span class="comment-on-wave-time">' . $comment->secondsFormated . '</span><p>' . $comment->value . '</p></div></div></div>';
    }
}

$purchase = 'false';

if ($getSong->price > 0) {
    if (!isTrackPurchased($getSong->id)) {
        $purchase = 'true';
        if (IS_LOGGED == true) {
            if ($user->id == $getSong->user_id) {
                $purchase = 'false';
            }
        }
    }
}
$data = [
    'status' => 200,
    'songTitle' => $getSong->title,
    'artistName' => $getSong->publisher->name,
    'albumName' => 'Album',
    'songURL' => $getSong->secure_url,
    'coverURL' => $getSong->thumbnail,
    'songID' => $getSong->id,
    'songAudioID' => $getSong->audio_id,
    'songPageURL' => $getSong->url,
    'duration' => $time_seconds,
    'songDuration' => $getSong->duration,
    'songWaves' => $waves,
    'comments' => $comment_html,
    'waves' => $comments_on_wave,
    'purchase' => $purchase,
    'price' => $getSong->price,
    'favorite_button' => getFavButton($getSong->id, 'fav-icon'),
    'is_favoriated' => isFavorated($getSong->id),
    'age' => false,
    'showDemo' => (!empty($getSong->price) && $music->config->ffmpeg_system == 'on' && !empty($getSong->demo_track) && !isTrackPurchased($getSong->id)) ? 'true' : 'false'
];
$age = false;
if ($getSong->age_restriction == 1) {
    if (!IS_LOGGED) {
        $age = true;
    } else {
        if ($user->age < 18) {
            $age = true;
        }
    }
}

if ($age == true) {
    $data = ['status' => 200, 'age' => true];
}

?>