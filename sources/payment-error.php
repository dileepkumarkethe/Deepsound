<?php 
if (IS_LOGGED == false) {
	header("Location: $site_url");
	exit();
}

$reason = lang("Unexpected error found while processing your payment, please try again later.");
// if (!empty($_GET['reason'])) {
// 	switch ($_GET['reason']) {
// 		case 'invalid-payment':
// 			# code...
// 			break;
// 		case 'not-found':
// 			# code...
// 			break;
// 		case 'purchased':
// 			# code...
// 			break;
// 		case 'invalid-payment':
// 			# code...
// 			break;
// 		case 'invalid-payment':
// 			# code...
// 			break;
// 	}
// }

$music->site_title = lang('Payment Error');
$music->site_description = $music->config->description;
$music->site_pagename = "payment-error";
$music->site_content = loadPage("go-pro/payment-error", ['reason' => $reason]);