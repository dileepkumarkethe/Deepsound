<?php 
include_once('assets/includes/paypal.php');
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;


if (IS_LOGGED == false || $music->config->go_pro != 'on') {
	header("Location: $site_url/404");
	exit();
}
if (empty($path['options'][1]) || empty($_GET['paymentId']) || empty($_GET['token']) || empty($_GET['PayerID'])) {
	header("Location: $site_url/payment-error");
	exit();
}

if ($user->is_pro == 1) {
	header("Location: $site_url/payment-error?reason=already-pro");
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
    	$updateUser = $db->where('id', $user->id)->update(T_USERS, ['is_pro' => 1, 'pro_time' => time()]);
    	if ($updateUser) {
            CreatePayment(array(
                'user_id'   => $id,
                'amount'    => $music->config->pro_price,
                'type'      => 'PRO',
                'pro_plan'  => 1,
                'info'      => '',
                'via'       => 'PayPal'
            ));
    		header("Location: $site_url/upgraded");
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
