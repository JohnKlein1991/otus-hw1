<?php
/**
 * Контроллер для справочника регионов
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class RegionlistController extends BaseController
{
    public function run($xml)
    {
        $data = $this->getDataFromXml($xml);

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getRegionList($data), true);

        if($result['success']){
            ResponseContainer::$readyResponse = $this->makeXmlResponse($result);
        } else {
            ResponseContainer::addAttachment('regionlist', '', [
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
        $response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><regionlist/>');
        
        if(isset($data['cities']) & is_array($data['cities'])){
            $count = count($data['cities']);
            $response->addAttribute('count', $count);

            foreach ($data['cities'] as $cityArray) {
                $city = $response->addChild('city');
                if(isset($cityArray['code'])) $city->addChild('code', $cityArray['code']);
                if(isset($cityArray['name'])) $city->addChild('name', $cityArray['name']);
                if(isset($cityArray['country']) && is_array($cityArray['country'])) {
                    $country = $city->addChild('country');
                    if(isset($cityArray['country']['code'])) $country->addChild('code', $cityArray['country']['code']);
                    if(isset($cityArray['country']['name'])) $country->addChild('name', $cityArray['country']['name']);
                    if(isset($cityArray['country']['id'])) $country->addChild('id', $cityArray['country']['id']);
                    if(isset($cityArray['country']['ShortName1'])) $country->addChild('ShortName1', $cityArray['country']['ShortName1']);
                    if(isset($cityArray['country']['ShortName2'])) $country->addChild('ShortName2', $cityArray['country']['ShortName2']);
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
        if ($codesearch = $xml->codesearch){
            if($codesearch->code) $result['code'] = strval($codesearch->code);
        } else {
            if($conditions = $xml->conditions){
                if($conditions->namecontains) $result['conditions']['namecontains'] = strval($conditions->namecontains);
                if($conditions->namestarts) $result['conditions']['namestarts'] = strval($conditions->namestarts);
                if($conditions->fullname) $result['conditions']['fullname'] = strval($conditions->fullname);
                if($conditions->country) $result['conditions']['country'] = strval($conditions->country);
            }
        }
        return $result;
    }
}