<?php
/**
 * Контроллер движения номенклатуры
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class ItemmovementsController extends BaseController
{
    public function run($xml)
    {
        if(!$this->validateXml($xml)) return;
        $data = $this->getDataFromXml($xml);

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getItemMovement($data), true);

        if($result['success']){
            ResponseContainer::$readyResponse = $this->makeXmlResponse($result);
        } else {
            ResponseContainer::addAttachment('error', '', [
                'error' => $result['error'],
                'errormsg' => $result['errorNumber'],
            ]);
        }
    }

    /**
     * Собирает корректный xml ответ для пользователя
     *
     * @param $data array
     * @return SimpleXMLElement
     */
    function makeXmlResponse($data)
    {
        $response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><itemmovements/>');

        if(isset($data['itemmovements']) & is_array($data['itemmovements'])){
            $count = count($data['itemmovements']);
            $response->addAttribute('count', $count);

            foreach ($data['itemmovements'] as $itemmovementArray) {
                $itemmovement = $response->addChild('itemmovement');
                if(isset($itemmovementArray['code'])) $itemmovement->addChild('code', $itemmovementArray['code']);
                if(isset($itemmovementArray['date'])) $itemmovement->addChild('date', $itemmovementArray['date']);
                if(isset($itemmovementArray['retprice'])) $itemmovement->addChild('retprice', $itemmovementArray['retprice']);
                if(isset($itemmovementArray['quantity'])) $itemmovement->addChild('quantity', $itemmovementArray['quantity']);
                if(isset($itemmovementArray['delivered'])) $itemmovement->addChild('delivered', $itemmovementArray['delivered']);
                if(isset($itemmovementArray['item']) && is_array($itemmovementArray['item'])) {
                    $item = $itemmovement->addChild('item');
                    if(isset($itemmovementArray['item']['code'])) $item->addChild('code', $itemmovementArray['item']['code']);
                    if(isset($itemmovementArray['item']['name'])) $item->addChild('name', $itemmovementArray['item']['name']);
                }
                if(isset($itemmovementArray['status']) && is_array($itemmovementArray['status'])) {
                    $status = $itemmovement->addChild('status');
                    if(isset($itemmovementArray['status']['code'])) $status->addChild('code', $itemmovementArray['status']['code']);
                    if(isset($itemmovementArray['status']['name'])) $status->addChild('name', $itemmovementArray['status']['name']);
                }
                if(isset($itemmovementArray['store']) && is_array($itemmovementArray['store'])) {
                    $store = $itemmovement->addChild('store');
                    if(isset($itemmovementArray['store']['code'])) $store->addChild('code', $itemmovementArray['store']['code']);
                    if(isset($itemmovementArray['store']['name'])) $store->addChild('name', $itemmovementArray['store']['name']);
                }
                if(isset($itemmovementArray['order']) && is_array($itemmovementArray['order'])) {
                    $order = $itemmovement->addChild('order');
                    if(isset($itemmovementArray['order']['ordercode'])) $order->addChild('ordercode', $itemmovementArray['order']['ordercode']);
                    if(isset($itemmovementArray['order']['number'])) $order->addChild('number', $itemmovementArray['order']['number']);
                    if(isset($itemmovementArray['order']['date'])) $order->addChild('date', $itemmovementArray['order']['date']);
                    if(isset($itemmovementArray['order']['orderno'])) $order->addChild('orderno', $itemmovementArray['order']['orderno']);
                    if(isset($itemmovementArray['order']['barcode'])) $order->addChild('barcode', $itemmovementArray['order']['barcode']);
                    if(isset($itemmovementArray['order']['company'])) $order->addChild('company', $itemmovementArray['order']['company']);
                    if(isset($itemmovementArray['order']['address'])) $order->addChild('address', $itemmovementArray['order']['address']);
                    if(isset($itemmovementArray['order']['delivereddate'])) $order->addChild('delivereddate', $itemmovementArray['order']['delivereddate']);
                    if(isset($itemmovementArray['order']['deliveredtime'])) $order->addChild('deliveredtime', $itemmovementArray['order']['deliveredtime']);
                    if(isset($itemmovementArray['order']['deliveredto'])) $order->addChild('deliveredto', $itemmovementArray['order']['deliveredto']);
                }
                if(isset($itemmovementArray['document']) && is_array($itemmovementArray['document'])) {
                    $document = $itemmovement->addChild('document');
                    if(isset($itemmovementArray['document']['code'])) $document->addChild('code', $itemmovementArray['document']['code']);
                    if(isset($itemmovementArray['document']['number'])) $document->addChild('number', $itemmovementArray['document']['number']);
                    if(isset($itemmovementArray['document']['date'])) $document->addChild('date', $itemmovementArray['document']['date']);
                    if(isset($itemmovementArray['document']['message'])) $document->addChild('message', $itemmovementArray['document']['message']);
                }
            }
        }

        return $response;
    }

    /**
     * Получает нужные данные из xml и возвращает их в виде массива
     *
     * @param $xml SimpleXMLElement
     * @return array
     */
    private function getDataFromXml($xml)
    {
        $code = strval($xml->code);
        $result = [
            'code' => $code
        ];

        return $result;
    }

    /**
     * Проверяет валидность xml для данного вида запроса (наличие обязательных элементов)
     *
     * @param $xml SimpleXMLElement
     * @return bool
     */
    private function validateXml($xml)
    {
        if(!$xml->code || !strval($xml->code)) {
            ResponseContainer::addMainTagAttribute('error', 62);
            ResponseContainer::setMainTagValue('attribute [code] is not set');
            return false;
        }
        return true;
    }
}