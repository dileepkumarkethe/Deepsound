<?php
require_once('assets/import/stripe-php-3.20.0/vendor/autoload.php');
global $music;
$data = array();

$stripe = array(
    'secret_key' => $music->config->stripe_secret,
    'publishable_key' => $music->config->stripe_id
);
\Stripe\Stripe::setApiKey($stripe[ 'secret_key' ]);

$product        = Secure($_POST[ 'description' ]);
$realprice      = Secure($_POST[ 'price' ]);
$price          = Secure($_POST[ 'price' ]) * 100;
$amount         = 0;
$currency       = strtolower($music->config->stripe_currency);
$payType        = Secure($_POST[ 'payType' ]);
$membershipType = 0;
$token          = $_POST[ 'stripeToken' ];
$trackID        = Secure($_POST[ 'trackID' ]);

$getIDAudio = $db->where('audio_id', $trackID)->getValue(T_SONGS, 'id');

if (empty($getIDAudio)) {
    $data = array(
        'status' => 400,
        'message' => 'invalid track'
    );
}

if (isTrackPurchased($getIDAudio)) {
    $data = array(
        'status' => 400,
        'message' => 'You already purchase this track.'
    );
}

$songData = songData($getIDAudio);

if (empty($songData->price)) {
    $data = array(
        'status' => 400,
        'message' => 'no price.'
    );
}

if (empty($token)) {
    $data = array(
        'status' => 400,
        'message' => 'invalid token'
    );
}


try {
    $customer = \Stripe\Customer::create(array(
        'source' => $token
    ));
    $charge   = \Stripe\Charge::create(array(
        'customer' => $customer->id,
        'amount' => $price,
        'currency' => $currency
    ));
    if ($charge) {

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
                'via'       => 'Stripe'
            ));
            $addUserWallet = $db->where('id', $songData->user_id)->update(T_USERS, ['balance' => $db->inc($final_price)]);
            $create_notification = createNotification([
                'notifier_id' => $user->id,
                'recipient_id' => $songData->user_id,
                'type' => 'purchased',
                'track_id' => $songData->id,
                'url' => "track/$songData->audio_id"
            ]);
            $data = array(
                'status' => 200,
                'url' => "$site_url/track/{$songData->audio_id}"
            );
        } else {
            $data = array(
                'status' => 400,
                'message' => 'can not create payment'
            );
        }

    }
} catch (Exception $e) {
    $data = array(
        'status' => 400,
        'message' => $e->getMessage()
    );
}


header('Content-type: application/json; charset=UTF-8');
echo json_encode($data);
exit();