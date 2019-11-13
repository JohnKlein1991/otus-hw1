<?php
/**
 * Контроллер для трекинга заказа по номеру
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class TrackingController extends BaseController
{
    public function run($xml)
    {
        $orderno = (string) $xml->orderno;

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getTrackingByOrderno($orderno), true);

        if($result['success']){
            $response = $this->makeXmlResponse($result['tracking'], $orderno);
            ResponseContainer::$readyResponse = $response;
        } else {
            ResponseContainer::addAttachment('error', '', [
                'error' => isset($result['errorNumber']) ? $result['errorNumber'] : '',
                'errormsg' => isset($result['error']) ? $result['error'] : '',
            ]);
        }
    }

    /**
     *
     * @param $tracking array
     * @param $orderno string
     * @return SimpleXMLElement
     */
    private function makeXmlResponse($tracking, $orderno)
    {
        $response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><tracking/>');

        $order = $response->addChild('order');
        $order->addAttribute('orderno', $orderno);

        foreach ($tracking as $key => $item) {
            $itemXml = $order->addChild($key);
            if(isset($item['attr'])){
                foreach ($item['attr'] as $attrName => $attrVal){
                    $itemXml->addAttribute($attrName, $attrVal);
                }
            }
            if(isset($item['value']) && !is_array($item['value'])){
                $itemXml[0] = $item['value'];
            } elseif (isset($item['value']) && is_array($item['value'])) {
                foreach ($item['value'] as $elem) {
                    foreach ($elem as $innerName => $innerItem){
                        $innerItemXml = $itemXml->addChild($innerName, $innerItem['value'] ?? '');
                        if(isset($innerItem['attr'])){
                            foreach ($innerItem['attr'] as $innerAttrName => $innerAttrVal) {
                                $innerItemXml->addAttribute($innerAttrName, $innerAttrVal);
                            }
                        }
                    }
                }
            }
        }

        return $response;
    }
}