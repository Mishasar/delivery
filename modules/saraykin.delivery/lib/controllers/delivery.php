<?php
/**
 * Created by PhpStorm.
 * User: mikhail
 * Date: 15.12.2019
 * Time: 12:56
 */
namespace Saraykin\Delivery\Controllers;

use Saraykin\Delivery\Models\Storage;
use Saraykin\Delivery\Models\Core;

class Delivery extends Controller
{
    /**
     * AJAX
     * Возвращает все доступные службы доставки с их стоимостями
     *
     * @param $request
     */
    public static function getAll(array $request)
    {
        global $USER;

        $result = Core::getDeliveryByProductId(
            $request['PRODUCT_ID'],
            SITE_ID,
            $USER->GetID(),
            Storage::PERSON_TYPE,
            Storage::PAY_SYSTEM_ID,
            $request['LOCATION_ID']
        );

        if (empty($result)) {
            echo Controller::sendError('Доставок не обнаружено');
        } else {
            echo Controller::sendAnswer($result);
        }
    }
}