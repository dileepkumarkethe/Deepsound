<?php
$track_id = 0;
if (!empty($_GET['id'])) {
    $track_id = secure($_GET['id']);
}
if (empty($track_id)) {
    exit("Invalid Track ID");
}

$id = secure($_GET['id']);
$getAlbum = $db->where('album_id', $id)->getOne(T_ALBUMS);
if (empty($getAlbum)) {
    exit("Invalid Album ID");
}


$data['status'] = 400;

if (IS_LOGGED == false) {
    $data['status'] = 300;
} else {
    $getLink = createPurchasePayPalAlbumLink($getAlbum);
    if (!empty($getLink)) {
        $data['status'] = 200;
        $data['url'] = $getLink['url'];
    }
}
?>