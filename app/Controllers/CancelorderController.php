<?php
/**
 * Контроллер для отмены заказа
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\Models\CancelOrderModel;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class CancelorderController extends BaseController
{
    public function run($xml)
    {
        if (!$this->validateXml($xml)) return;

        foreach ($xml->order as $order) {
            if(!$this->validateOrder($order)) continue;

            $orderno = strval($order->attributes()->orderno) ? strval($order->attributes()->orderno) : null;
            $ordercode = strval($order->attributes()->ordercode) ? strval($order->attributes()->ordercode) : null;
            $orderInfo = [
                'orderno' => $orderno,
                'ordercode' => $ordercode
            ];
            $model = new CancelOrderModel($orderInfo);
            $model->save();

            //todo временная заглушка, в будущем будет удалена
            $milHelper = new MilHelper();
            $result = json_decode($milHelper->cancelOrder($orderInfo), true);
            if($result['success']){
                ResponseContainer::addAttachment('order', '', [
                    'orderno' => $orderno,
                    'ordercode' => $ordercode,
                    'error' => 0,
                    'errormsg' => 'OK',
                ]);
            } else {
                ResponseContainer::addAttachment('order', '', [
                    'orderno' => $orderno,
                    'ordercode' => $ordercode,
                    'error' => $result['error'],
                    'errormsg' => $result['errorNumber'],
                ]);
            }
        }
    }

    /**
     * Проверяет валидность xml для данного вида запроса (наличие обязательных полей)
     *
     * @param $xml SimpleXMLElement
     * @return bool
     */
    private function validateXml($xml)
    {
        if(!$xml->order) {
            ResponseContainer::addAttachment('error', '', [
                'errormsg' => 'Не передано обязательное поле order'
            ]);
            return false;
        }
        return true;
    }

    /**
     * Проверяет наличие обязательных полей и атрибутов у сделки
     *
     * @param $order SimpleXMLElement
     * @return bool
     */
    private function validateOrder($order)
    {
        if(!(strval($order->attributes()->ordercode)) && !(strval($order->attributes()->orderno))) {
            ResponseContainer::addAttachment('error', '', [
                'errormsg' => 'Наличие хотя бы одного атрбута из orderno и ordercode обязательно для поля order'
            ]);
            return false;
        }

        return true;
    }
}