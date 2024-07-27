<?php
//加載所需的庫
use WHMCS\Database\Capsule;
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
//取得輸入參數
$invoiceId = $_REQUEST['Data_id'];
$Smseid = $_REQUEST['Smseid'];
$paydate = $_REQUEST['Process_date'];
//取得資料庫資料
$result = Capsule::table('smse_pay_record')->where('invoice_id', $invoiceId)->first();
//根據回傳通知判斷付款方式以確認模組名稱
if ($_REQUEST['Classif'] == 'B') {
    $payway = 'smse_bank';
    $db_smseid = $result->atm_SmilePayNO;
} elseif ($_REQUEST['Classif'] == 'E') {
    $payway = 'smse_ibon';
    $db_smseid = $result->ibon_SmilePayNO;
} elseif ($_REQUEST['Classif'] == 'F') {
    $payway = 'smse_fami';
    $db_smseid = $result->Fami_SmilePayNO;
}
$gatewayParams = getGatewayVariables($payway);

if ($Smseid == $db_smseid) {
    $command = 'AddInvoicePayment';
    $postData = array(
        'invoiceid' => $invoiceId,
        'transid' => $Smseid,
        'gateway' => $payway,
        'date' => $paydate,
    );

    $results = localAPI($command, $postData);
    $results = json_encode($results);
    $results = json_decode($results, true);
    $results = strtoupper($results['result']);
    Capsule::table('smse_pay_record')->where('invoice_id', $invoiceId)->delete();
}

$output = $_REQUEST['Roturl_status'];
echo "<Roturlstatus>$output</Roturlstatus>";
