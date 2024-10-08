<?php

function yoomoney_config() {
    return [
        "FriendlyName" => [
            "Type" => "System",
            "Value" => "ЮMoney",
        ],
        "receiver" => [
            "FriendlyName" => "Номер кошелька ЮMoney",
            "Type" => "text",
            "Size" => "25",
            "Description" => "Введите номер кошелька ЮMoney для получения платежей",
        ],
        "testmode" => [
            "FriendlyName" => "Тестовый режим",
            "Type" => "yesno",
            "Description" => "Включить для тестирования без реального списания средств",
        ],
        "notification_secret" => [
            "FriendlyName" => "Секретное слово для уведомлений",
            "Type" => "text",
            "Size" => "50",
            "Description" => "Введите секретное слово для проверки уведомлений от ЮMoney(https://yoomoney.ru/transfer/myservices/http-notification)",
        ],
    ];
}

function yoomoney_link($params) {
    $receiver = $params['receiver'];
    $testmode = $params['testmode'];
    $notification_secret = $params['notification_secret'];
    $invoiceid = $params['invoiceid'];
    $description = "Оплата счета #{$invoiceid}";
    $amount = $params['amount'];

    $postData = [
        'receiver' => $receiver,
        'quickpay-form' => 'button',
        'paymentType' => 'PC',
        'sum' => $amount,
        'label' => $invoiceid,
        'successURL' => $params['systemurl'] . '/modules/gateways/callback/yoomoney.php',
    ];

    if ($testmode === 'on') {
        $postData['test_payment'] = 'true';
    }

    $form = '<form method="post" action="https://yoomoney.ru/quickpay/confirm">';
    foreach ($postData as $key => $value) {
        $form .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '" />';
    }
    $form .= '<input type="submit" value="Оплатить" />';
    $form .= '</form>';

    return $form;
}
