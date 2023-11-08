<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Type\DateTime;

Loader::includeModule("highloadblock");

// Проверка авторизации пользователя
global $USER;
if (!$USER->IsAuthorized()) {
    die("Доступ запрещен. Пожалуйста, авторизуйтесь.");
}

// Получение ID текущего пользователя
$userId = $USER->GetID();

// Получение ID highload-блока по его названию
$hlblockName = 'TestDiscount2023';
$hlblock = HighloadBlockTable::getList(['filter' => ['NAME' => $hlblockName]])->fetch();
$hlblockId = $hlblock['ID'];

$hlblock = HighloadBlockTable::getById($hlblockId)->fetch();
$hlentity = HighloadBlockTable::compileEntity($hlblock); // сущность

$discount = 0;

$request = Context::getCurrent()->getRequest();

if ($request->isPost()) {
    if ($request->getPost('generate')) {
        // Проверка, есть ли у пользователя скидка, сгенерированная менее 1 часа назад
        $select = ['UF_USER_ID', 'UF_DISCOUNT', 'UF_EXPIRATION', 'UF_COUPON'];
        $filter = [
            'UF_USER_ID' => $userId,
            '>UF_CREATED' => DateTime::createFromTimestamp(strtotime('-1 hour')),
        ];

        $hlentity_data_class = $hlentity->getDataClass();
        $rsData = $hlentity_data_class::getList([
            'order' => ['ID' => 'ASC'],
            'select' => $select,
            'filter' => $filter
        ]);

        while ($el = $rsData->fetch()) {
            $discount = $el['UF_DISCOUNT'];
            $code = $el['UF_COUPON'];
        }

        if (!$discount) {
            // Свежей (менее 1 часа) скидки нет, можно сгенерировать новую
            $discount = rand(1, 50);
            $code = generateCouponCode(9);

            $hlblockClass = $hlentity->getDataClass();

            // Формируем данные для добавления
            $addRecord = [
                'UF_PRODUCT_ID' => 1,
                'UF_USER_ID' => $userId,
                'UF_DISCOUNT' => $discount,
                'UF_CREATED' => DateTime::createFromTimestamp(time()),
                'UF_EXPIRATION' => DateTime::createFromTimestamp(strtotime('+3 hours')),
                'UF_COUPON' => $code,
            ];

            $result = $hlblockClass::add($addRecord);

            if (!$result->isSuccess()) {
                $errorMessage = "Ошибка при добавлении записи: " . implode(", ", $result->getErrorMessages());
                file_put_contents('error.log', $errorMessage, FILE_APPEND); // Запись ошибки в файл
                throw new Exception($errorMessage);
            }
        }

        $response = ["discount" => $discount . "%", "code" => $code];
        echo json_encode($response);
    } elseif ($request->getPost('code')) {
        $enteredCode = trim($request->getPost('code'));

        $select = ['UF_USER_ID', 'UF_DISCOUNT', 'UF_EXPIRATION', 'UF_COUPON'];
        $filter = [
            'UF_USER_ID' => $userId,
            '>UF_EXPIRATION' => DateTime::createFromTimestamp(time()),
            'UF_COUPON' => $enteredCode,
        ];

        $hlentity_data_class = $hlentity->getDataClass();
        $rsData = $hlentity_data_class::getList([
            'order' => ['ID' => 'ASC'],
            'select' => $select,
            'filter' => $filter
        ]);

        while ($el = $rsData->fetch()) {
            $discount = $el['UF_DISCOUNT'];
        }

        if ($discount > 0) {
            echo $discount . '%';
        } else {
            echo 'Скидка недоступна';
        }
    }
}

function generateCouponCode($length = 9)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $randomIndex = rand(0, strlen($characters) - 1);
        $code .= $characters[$randomIndex];
    }
    return $code;
}
?>
