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

        $updateUser = $db->where('id', $user->id)->update(T_USERS, ['is_pro' => 1, 'pro_time' => time()]);
        if ($updateUser) {
            CreatePayment(array(
                'user_id'   => $user->id,
                'amount'    => $music->config->pro_price,
                'type'      => 'PRO',
                'pro_plan'  => 1,
                'info'      => '',
                'via'       => 'Stripe'
            ));
            $data = array(
                'status' => 200,
                'url' => "$site_url/upgraded"
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