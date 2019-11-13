<?php
/**
 * Контроллер для расчета стоимости доставки
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class CalculatorController extends BaseController
{
    public function run($xml)
    {
        if(!$this->validateXml($xml)) return;
        $data = $this->getDataFromXml($xml);

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getCalc($data), true);

        if($result['success']){
            ResponseContainer::$readyResponse = $this->makeXmlResponse($result);
        } else {
            ResponseContainer::setMainTagName('calc');
            if(isset($result['errorNumber'])) ResponseContainer::addMainTagAttribute('error', $result['errorNumber']);
            if(isset($result['error'])) ResponseContainer::setMainTagValue($result['error']);
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
        $response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><calculator/>');

        if(isset($data['calculator']) & is_array($data['calculator'])){
            $count = count($data['calculator']);
            $response->addAttribute('count', $count);

            foreach ($data['calculator'] as $calcArray) {
                $calc = $response->addChild('calc');
                foreach ($calcArray as $key => $item) {
                    $calc->addChild($key, $item);
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
        $result = [];

        if(strval($xml->calc->attributes()->townfrom)) $result['townfrom'] = strval($xml->calc->attributes()->townfrom);
        if(strval($xml->calc->attributes()->townto)) $result['townto'] = strval($xml->calc->attributes()->townto);
        if(strval($xml->calc->attributes()->mass)) $result['mass'] = strval($xml->calc->attributes()->mass);

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
        $tagName = '';
        $tagValue = '';
        $attributes = [];
        if(!$xml->calc) {
            $tagName = 'calc';
            $tagValue = 'field [town to] cannot be empty';
            $attributes['error'] = 19;
        } elseif (!strval($xml->calc->attributes()->townto) || !strval($xml->calc->attributes()->townfrom) || !strval($xml->calc->attributes()->mass)) {
            $tagName = 'calc';
            $emptyAttr = "";
            if(!strval($xml->calc->attributes()->mass)) {
                $emptyAttr = '[mass]';
                $attributes['error'] = 23;
            } elseif (!strval($xml->calc->attributes()->townfrom)) {
                $emptyAttr = '[town from]';
                $attributes['error'] = 19;
            } elseif (!strval($xml->calc->attributes()->townto)) {
                $emptyAttr = '[town to]';
                $attributes['error'] = 19;
            }
            $tagValue = "field {$emptyAttr} cannot be empty";
        }

        if($tagName && $tagValue){
            ResponseContainer::setMainTagName($tagName);
            ResponseContainer::setMainTagValue($tagValue);
            foreach ($attributes as $attrName => $attrValue) {
                ResponseContainer::addMainTagAttribute($attrName, $attrValue);
            }
            return false;
        }
        return true;
    }
}