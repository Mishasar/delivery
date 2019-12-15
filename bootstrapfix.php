<?
/**
 * Не смог найти настройки nginix на тестовом сервере.
 * Для маршрутизации обычно использую FastRoute
 * Пример импользования local/modules/saraykin.delivery/lib/router/bootstrap.php
 */

use Bitrix\Main\Loader;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
Loader::includeModule('saraykin.delivery');

Saraykin\Delivery\Controllers\Delivery::getAll($_POST);
