<?php 
if (IS_LOGGED == false) {
    $data = array('status' => 400, 'error' => 'Not logged in');
    echo json_encode($data);
    exit();
}

if (!empty($_GET['thumb'])) {
	if (in_array($_GET['thumb'], $_SESSION['uploads'])) {
		$key = array_search ($_GET['thumb'], $_SESSION['uploads']);
		unlink($_GET['thumb']);
		unset($_SESSION['uploads'][$key]);
	}
}
if (!empty($_GET['song'])) {
	if (in_array($_GET['song'], $_SESSION['uploads'])) {
		$key = array_search ($_GET['song'], $_SESSION['uploads']);
		unlink($_GET['song']);
		unset($_SESSION['uploads'][$key]);
	}
}
?>