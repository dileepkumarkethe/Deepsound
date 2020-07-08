<?php
include_once('assets/includes/paypal.php');
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;


if (IS_LOGGED == false) {
	header("Location: $site_url/404");
	exit();
}
if (empty($path['options'][1]) || empty($path['options'][2]) || empty($_GET['paymentId']) || empty($_GET['token']) || empty($_GET['PayerID'])) {
	header("Location: $site_url/payment-error");
	exit();
}

$album_id = secure($path['options'][2]);
$getIDAlbum = $db->where('album_id', $album_id)->getValue(T_ALBUMS, 'id');

if (empty($getIDAlbum)) {
	header("Location: $site_url/payment-error?reason=not-found");
	exit();
}

if (isUserBuyAlbum($getIDAlbum)) {
	header("Location: $site_url/payment-error?reason=purchased");
	exit();
}

$albumData = albumData($getIDAlbum);

if (empty($albumData->price)) {
    header("Location: $site_url/payment-error?reason=no-price");
    exit();
}
$PayerID = secure($_GET['PayerID']);
$token = secure($_GET['token']);
$paymentId = secure($_GET['paymentId']);

$payment   = Payment::get($paymentId, $paypal);
$execute   = new PaymentExecution();
$execute->setPayerId($PayerID);

try{
    $result = $payment->execute($execute, $paypal);
    if ($result) {
        $getAdminCommission = $music->config->commission;
        $final_price = 0;

        $createPayment = false;
        foreach ($albumData->songs as $key => $song){
            $final_price += round((($getAdminCommission * $song->price) / 100), 2);
            $addPurchase = [
                'track_id' => $song->id,
                'user_id' => $user->id,
                'price' => $song->price,
                'track_owner_id' => $song->user_id,
                'final_price' => round((($getAdminCommission * $song->price) / 100), 2),
                'commission' => $getAdminCommission,
                'time' => time()
            ];

            $createPayment = $db->insert(T_PURCHAES, $addPurchase);
            if ($createPayment) {
                CreatePayment(array(
                    'user_id'   => $user->id,
                    'amount'    => $final_price,
                    'type'      => 'TRACK',
                    'pro_plan'  => 0,
                    'info'      => $songData->audio_id,
                    'via'       => 'PayPal'
                ));
                $create_notification = createNotification([
                    'notifier_id' => $user->id,
                    'recipient_id' => $song->user_id,
                    'type' => 'purchased',
                    'track_id' => $song->id,
                    'url' => "track/$song->audio_id"
                ]);
            }
        }

        if ($createPayment) {
            $updatealbumpurchases = $db->where('album_id', $album_id)->update(T_ALBUMS, array('purchases' => $db->inc(1) ));
            $addUserWallet = $db->where('id', $albumData->user_id)->update(T_USERS, ['balance' => $db->inc($final_price)]);
    		header("Location: $site_url/album/{$album_id}");
	        exit();
    	} else {
    		header("Location: $site_url/payment-error?reason=cant-create-payment");
			exit();
    	}
    }
}

catch (Exception $e) {
    header("Location: $site_url/payment-error?reason=invalid-payment");
	exit();
}
