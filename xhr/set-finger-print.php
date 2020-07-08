<?php 	
if (empty($_POST['components'])) {
	$_POST['components'] = sha1(time());
}

$fingerPrint = sha1(json_encode($_POST['components']));

$_SESSION['fingerPrint'] = $fingerPrint;
?>