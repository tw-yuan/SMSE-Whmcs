<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

function smse_ibon_MetaData()
{
    return array(
        'DisplayName' => 'smse_ibon',
        'APIVersion' => '1.0',
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function smse_ibon_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'smse (速買配) 7-11 ibon - 背景取號',
        ),
        // a text field type allows for single line text input
        'Dcvc' => array(
            'FriendlyName' => 'Dcvc (商店代號)',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Dcvc',
        ),
        // a password field type allows for masked text input
        'Verify_key' => array(
            'FriendlyName' => 'Verify_key',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Verify_key',
        ),
        'Rvg2c' => array(
            'FriendlyName' => 'Rvg2c',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Rvg2c',
        ),
        'Verify_code' => array(
            'FriendlyName' => '驗證參數',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => '驗證參數',
        ),
    );
}

function smse_ibon_link($params)
{
    // 網關參數
    $Dcvc = $params['Dcvc'];
    $Verify_key = $params['Verify_key'];
    $Rvg2c = $params['Rvg2c'];

    // 帳單參數
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    //由於支付接口不支援小數金額，因此取整數金額。
    $amount = round($params['amount']);
    $amount = $amount . '.00';

    // 客戶參數
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // 系統參數
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];
    $callback_url = $systemUrl . '/modules/gateways/callback/smse.php';

    // 確認資料庫資料
    $result = Capsule::table('smse_pay_record')->where('invoice_id', $invoiceId)->first();
    if (($result->IbonNo != null)) {
        $code = '<div class="text-left alert alert-info"><p><b>繳費代碼：</b><code>' . $result->IbonNo . '</code></p>' .
            '<p><b>應繳金額：</b><code>' . $result->Amount . ' TWD</code></p>' .
            '<p><b>繳費期限：</b><code>' . $result->IbonNo_PayEndDate . '</code></p></div>';
        return $code;
    } else {
        //交易參數
        $data = array(
            'Dcvc' => trim($Dcvc),
            'Verify_key' => trim($Verify_key),
            'Rvg2c' => trim($Rvg2c),
            'Data_id' => $invoiceId,
            'Amount' => $amount,
            'Email' => $email,
            'Remark' => $companyName . $invoiceId,
            'Pay_zg' => "4",
            'Roturl' => $callback_url
        );

        $url = 'https://ssl.smse.com.tw/api/SPPayment.asp';
        $url .= '?' . http_build_query($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        $data = simplexml_load_string($result);
        $data = json_encode($data);
        $data = json_decode($data, true);

        Capsule::table('smse_pay_record')->updateOrInsert(
            ['invoice_id' => $invoiceId],
            [
                'IbonNo' => $data['IbonNo'],
                'Amount' => $data['Amount'],
                'IbonNo_PayEndDate' => $data['PayEndDate'],
                'ibon_SmilePayNO' => $data['SmilePayNO'],
            ]
        );

        $code = '<div class="text-left alert alert-info"><p><b>繳費代碼：</b><code>' . $data['IbonNo'] . '</code></p>' .
            '<p><b>應繳金額：</b><code>' . $data['Amount'] . ' TWD</code></p>' .
            '<p><b>繳費期限：</b><code>' . $data['PayEndDate'] . '</code></p></div>';

        return $code;
    }

    echo '<meta http-equiv="refresh" content="0;url=' . $params['returnurl'] . '" />';
}
