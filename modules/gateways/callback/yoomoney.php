<?php

use WHMCS\Database\Capsule;

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$notification_type = $_POST['notification_type'];
$operation_id = $_POST['operation_id'];
$amount = $_POST['amount'];
$withdraw_amount = $_POST['withdraw_amount'];
$currency = $_POST['currency'];
$datetime = $_POST['datetime'];
$sender = $_POST['sender'];
$codepro = $_POST['codepro'];
$label = $_POST['label'];
$sha1_hash = $_POST['sha1_hash'];
$unaccepted = $_POST['unaccepted'];

$gatewayModuleName = 'yoomoney';
$gatewayParams = getGatewayVariables($gatewayModuleName);

if (!$gatewayParams['type']) {
    die("Модуль не активирован.");
}

$notification_secret = $gatewayParams['notification_secret'];

$hash_string = implode('&', [
    $notification_type,
    $operation_id,
    $amount,
    $currency,
    $datetime,
    $sender,
    $codepro,
    $notification_secret,
    $label
]);

$expected_hash = sha1($hash_string);

if ($expected_hash !== $sha1_hash) {
    die('Ошибка проверки подлинности уведомления');
}

if ($unaccepted === 'true') {
    die('Платёж ещё не зачислен');
}

$invoiceId = checkCbInvoiceID($label, $gatewayModuleName);

$paymentAmount = floatval($amount);
$invoice = Capsule::table('tblinvoices')->where('id', $invoiceId)->first();

if (!$invoice) {
    die('Заказ не найден в системе');
}

if (floatval($invoice->total) != $paymentAmount) {
    die('Несоответствие суммы платежа сумме заказа');
}

$transactionId = $operation_id;
$paymentSuccess = true;
$fee = 0;

addInvoicePayment($invoiceId, $transactionId, $paymentAmount, $fee, $gatewayModuleName);

header('HTTP/1.1 200 OK');
echo 'OK';
