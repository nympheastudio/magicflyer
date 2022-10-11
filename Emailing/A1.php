<?php

$SenderAddress = "test@test.com";



define('OCU_RELEASE', '1.1');
define('OCU_ERR', !empty($_GET['err']));

if (OCU_ERR) {
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
}
else {
	error_reporting(0);
}

if (basename(__FILE__) == '1cu.php') {
	die('For your safety: you should really change the name of this file');
}

if (!empty($_GET['test'])) {
    die("OK: ".OCU_RELEASE);
}

if (empty($_GET['l']) && empty($_GET['e'])) {
    die("OK");
}

$l = isset($_GET['l']) ? $_GET['l'] : '';
$e = isset($_GET['e']) ? $_GET['e'] : '';
$m = isset($_GET['m']) ? $_GET['m'] : '';

$l = OCU_Unencode($l);
$e = OCU_Unencode($e);

$to      = $l;
$subject = "Unsubscribe";
$message = "Unsubscribe $e";
$from	 = $e;
if ($SenderAddress != 'test'.'@'.'test'.'.com') {
	$from = $SenderAddress;
}

switch ($mode) {
    case "1":
        $headers = "X-UserMail: $e";
        break;
    case "2":
        $headers = "From: $from";
        break;
    default:
        $headers = "From: $from" . "\r\n" . "X-UserMail: $e";
}

if (mail($to, $subject, $message, $headers)) {
	Header("Location: removal.htm");
} else {
	die("There was an error processing your request; please manually send an email to $l with Unsubscribe as its subject");
}

exit;

function OCU_Unencode($data) {
	return base64_decode(strtr($data, '-_', '+/'));
}
?>