<?php
/**
 * Created by PhpStorm.
 * User: mikhail
 * Date: 08.12.19
 * Time: 22:40
 */

namespace Saraykin\Delivery\Models;

use Bitrix\Main\Loader;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Config\Option;


class Core
{
    /**
     * Получение параметров доставки
     *
     * @param int    $productId    Id битриксового продукта
     * @param string $siteId       Id битриксового сайта, например "s1"
     * @param int    $userId       Id битриксового пользователя
     * @param int    $personTypeId Id плательщика
     * @param int    $paySystemId  Id платежной системы
     * @param int    $userCityId   Id местоположения
     *
     * @return mixed
     */
    public static function getDeliveryByProductId(int $productId, string $siteId, int $userId, int $personTypeId, int $paySystemId, int $userCityId)
    {
        $result = null;

        Loader::includeModule('catalog');
        Loader::includeModule('sale');

        $products = array(
            array(
                'PRODUCT_ID' => $productId,
                'QUANTITY' => 1,
            ),
        );

        $basket = \Bitrix\Sale\Basket::create($siteId);

        foreach ($products as $product) {
            $item = $basket->createItem("catalog", $product["PRODUCT_ID"]);
            unset($product["PRODUCT_ID"]);
            $item->setFields($product);
        }

        $order = \Bitrix\Sale\Order::create($siteId, $userId);
        $order->setPersonTypeId($personTypeId);
        $order->setBasket($basket);

        $orderProperties = $order->getPropertyCollection();
        $orderDeliveryLocation = $orderProperties->getDeliveryLocation();
        $orderDeliveryLocation->setValue($userCityId); // В какой город "доставляем" (куда доставлять).
        $shipmentCollection = $order->getShipmentCollection();
        $paymentCollection = $order->getPaymentCollection();
        $payment = $paymentCollection->createItem(
            \Bitrix\Sale\PaySystem\Manager::getObjectById($paySystemId)
        );
        $payment->setField("SUM", $order->getPrice());
        $payment->setField("CURRENCY", $order->getCurrency());
        $deliveryList = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();

        foreach ($deliveryList as $deliveryArray) {
            if ($deliveryArray['CLASS_NAME'] != "\Bitrix\Sale\Delivery\Services\AutomaticProfile") continue;

            $delivery = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($deliveryArray['ID']);
            $shipment = $shipmentCollection->createItem($delivery);
            $shipmentItemCollection = $shipment->getShipmentItemCollection();

            foreach ($basket as $basketItem) {
                $item = $shipmentItemCollection->createItem($basketItem);
                $item->setQuantity($basketItem->getQuantity());
            }

            $deliveryPrice = $order->getDeliveryPrice();
            $shipment->delete();

            if ($deliveryPrice === '') {
                $deliveryPrice = null;
            }

            $deliveryArray['PRICE'] = $deliveryPrice;

            $currentDelivery = [
                'NAME' => $deliveryArray['NAME'],
                'PRICE' => $deliveryPrice
            ];

            $result[] = $currentDelivery;
        }

        return $result;
    }
}