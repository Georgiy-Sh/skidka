<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Скидки");

// Проверка авторизации пользователя
global $USER;
if (!$USER->IsAuthorized()) {
    LocalRedirect('/auth/');
}
?>

<style>
    .block {
        border: 1px solid black;
        padding: 10px;
        margin-bottom: 10px;
    }

    .field {
        margin-bottom: 10px;
    }
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function () {
        $("#getDiscountButton").click(function () {
            $.ajax({
                url: "skidka_back.php",
                type: "POST",
                data: { generate: 1 },
                success: function (response) {
                    var arr_response = JSON.parse(response);
                    var discount = arr_response.discount;
                    var code = arr_response.code;
                    $("#discountField").text(discount);
                    $("#codeField").text(code);
                    $("#discountField").parent().show();
                    $("#codeField").parent().show();
                }
            });
        });

        $("#checkDiscountButton").click(function () {
            var enteredCode = $("#codeInput").val();
            $.ajax({
                url: "skidka_back.php",
                type: "POST",
                data: { code: enteredCode },
                success: function (response) {
                    if (response == "Скидка недоступна") {
                        $("#discountMessage").text(response);
                    } else {
                        $("#discountMessage").text("Скидка: " + response);
                    }
                }
            });
        });
    });
</script>

<div class="block">
    <h2>Получить скидку</h2>
    <div class="field">
        <button id="getDiscountButton">Получить скидку</button>
    </div>
    <div class="field" style="display: none;">
        <label for="discountField">Скидка:</label>
        <span id="discountField"></span>
    </div>
    <div class="field" style="display: none;">
        <label for="codeField">Уникальный код:</label>
        <span id="codeField"></span>
    </div>
</div>
<div class="block">
    <h2>Проверить скидку</h2>
    <div class="field">
        <label for="codeInput">Введите код:</label>
        <input type="text" id="codeInput">
    </div>
    <div class="field">
        <button id="checkDiscountButton">Проверить скидку</button>
    </div>
    <div class="field">
        <span id="discountMessage"></span>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
