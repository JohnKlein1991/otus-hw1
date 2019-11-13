<?php
/**
 * Контроллер для получения документов для печати
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class WaybillController extends BaseController
{
    public function run($xml)
    {
        if (!$this->validateXml($xml)) return;

        $data = $this->getDataFromXml($xml);

        if(!$data) return;

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $content = json_decode($milHelper->getWaybillByData($data), true);

        if ($content['success']) {
            ResponseContainer::addAttachment('content', $content['content'], []);
        } else {
            ResponseContainer::addAttachment('error', '', [
                'error' => $content['errorNumber'],
                'errormsg' => $content['error']
            ]);
        }
    }

    /**
     *
     * @param $xml SimpleXMLElement
     * @return array|false
     */
    private function getDataFromXml($xml)
    {
        $result = [];

        foreach ($xml->orders->order as $order) {
            if (!$this->validateOrder($order)) return false;
            $array = [];
            if(strval($order->attributes()->ordercode)) $array['ordercode'] = strval($order->attributes()->ordercode);
            if(strval($order->attributes()->orderno)) $array['orderno'] = strval($order->attributes()->orderno);
            if($array) $result['orders'][] = $array;
        }
        if(strval($xml->form)) {
            $result['form'] = strval($xml->form);
        } else {
            $result['form'] = 1;
        }
        return $result;
    }

    /**
     * Проверяет валидность xml для данного вида запроса (наличие обязательных полей)
     *
     * @param $xml SimpleXMLElement
     * @return bool
     */
    private function validateXml($xml)
    {
        if(!$xml->orders) {
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