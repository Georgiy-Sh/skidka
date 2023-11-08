<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader; 
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity;

// Подключение необходимых модулей
Loader::includeModule('highloadblock');

function generateCouponCode($length = 9) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';

    for ($i = 0; $i < $length; $i++) {
        $randomIndex = rand(0, strlen($characters) - 1);
        $code .= $characters[$randomIndex];
    }

    return $code;
}

$hlblockId = 0;
$hlblockName = 'TestDiscount2023';

// Проверка наличия highload-блока с названием TestDiscount
$hlblock = HighloadBlockTable::getList([
    'filter' => ['NAME' => $hlblockName]
])->fetch();

if (!$hlblock) {
    // Создание highload-блока, если он не существует
    $result = HighloadBlockTable::add([
        'NAME' => $hlblockName,
        'TABLE_NAME' => 'b_hlbd_' . strtolower($hlblockName),
    ]);
    
    if ($result->isSuccess()) {
        $hlblockId = $result->getId();

        // Создание пользовательских полей для highload-блока
        $fields = [
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
                'FIELD_NAME' => 'UF_PRODUCT_ID',
                'USER_TYPE_ID' => 'integer',
                'MANDATORY' => 'Y',
                'EDIT_FORM_LABEL' => [
                    'ru' => 'ID товара',
                    'en' => 'Product ID'
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
                'FIELD_NAME' => 'UF_USER_ID',
                'USER_TYPE_ID' => 'integer',
                'MANDATORY' => 'Y',
                'EDIT_FORM_LABEL' => [
                    'ru' => 'ID пользователя',
                    'en' => 'User ID'
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
                'FIELD_NAME' => 'UF_DISCOUNT',
                'USER_TYPE_ID' => 'double',
                'MANDATORY' => 'Y',
                'EDIT_FORM_LABEL' => [
                    'ru' => 'Скидка в %',
                    'en' => 'Discount %'
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
                'FIELD_NAME' => 'UF_CREATED',
                'USER_TYPE_ID' => 'datetime',
                'MANDATORY' => 'Y',
                'EDIT_FORM_LABEL' => [
                    'ru' => 'Дата и время создания скидки',
                    'en' => 'Discount creation date and time'
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
                'FIELD_NAME' => 'UF_EXPIRATION',
                'USER_TYPE_ID' => 'datetime',
                'MANDATORY' => 'Y',
                'EDIT_FORM_LABEL' => [
                    'ru' => 'Дата и время окончания действия скидки',
                    'en' => 'Discount expiration date and time'
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
                'FIELD_NAME' => 'UF_COUPON',
                'USER_TYPE_ID' => 'string',
                'MANDATORY' => 'N',
                'EDIT_FORM_LABEL' => [
                    'ru' => 'Код купона',
                    'en' => 'Coupon code'
                ]
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
                'FIELD_NAME' => 'UF_XML_ID',
                'USER_TYPE_ID' => 'string',
                'MANDATORY' => 'N',
                'EDIT_FORM_LABEL' => [
                    'ru' => 'XML ID',
                    'en' => 'XML ID'
                ]
            ]
        ];

        foreach ($fields as $field) {
            $userTypeEntity    = new CUserTypeEntity();
            $userTypeId = $userTypeEntity->Add($field);

            if (!$userTypeId) {
                // Обработка ошибки создания пользовательского поля
                var_dump($APPLICATION->GetException()->GetString());
                die();
            }
        }

        echo "таблица создана<br>";

    } else {
        // Обработка ошибки создания highload-блока
        file_put_contents('errors.log', print_r($result->getErrorMessages(), true));
        throw new Exception(implode(', ', $result->getErrorMessages())); 
    }
} else { 
    $hlblockId = $hlblock['ID'];
    echo "таблица уже была создана ранее<br>";
}

?>