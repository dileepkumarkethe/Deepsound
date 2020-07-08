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

$audio_id = secure($path['options'][2]);
$getIDAudio = $db->where('audio_id', $audio_id)->getValue(T_SONGS, 'id');

if (empty($getIDAudio)) {
	header("Location: $site_url/payment-error?reason=not-found");
	exit();
}

if (isTrackPurchased($getIDAudio)) {
	header("Location: $site_url/payment-error?reason=purchased");
	exit();
}

$songData = songData($getIDAudio);

if (empty($songData->price)) {
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
        $final_price = round((($getAdminCommission * $songData->price) / 100), 2);
    	$addPurchase = [
    		'track_id' => $songData->id,
    		'user_id' => $user->id,
    		'price' => $songData->price,
            'track_owner_id' => $songData->user_id,
            'final_price' => $final_price,
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
            $addUserWallet = $db->where('id', $songData->user_id)->update(T_USERS, ['balance' => $db->inc($final_price)]);
            $create_notification = createNotification([
                'notifier_id' => $user->id,
                'recipient_id' => $songData->user_id,
                'type' => 'purchased',
                'track_id' => $songData->id,
                'url' => "track/$songData->audio_id"
            ]);
    		header("Location: $site_url/track/{$songData->audio_id}");
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
