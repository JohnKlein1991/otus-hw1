<?php
/**
 * Контроллер для справочника городов
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class TownlistController extends BaseController
{
    public function run($xml)
    {
        $data = $this->getDataFromXml($xml);

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getTownList($data), true);

        if($result['success']){
            ResponseContainer::$readyResponse = $this->makeXmlResponse($result);
        } else {
            ResponseContainer::addAttachment('townlist', '', [
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
        $response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><townlist/>');

        $page = $data['page'] ?? '';
        $totalCount = $data['totalcount'] ?? '';
        $totalPages = $data['totalpages'] ?? '';

        $response->addAttribute('page', $page);
        $response->addAttribute('totalCount', $totalCount);
        $response->addAttribute('totalPages', $totalPages);

        if(isset($data['towns']) & is_array($data['towns'])){
            $count = count($data['towns']);
            $response->addAttribute('count', $count);

            foreach ($data['towns'] as $townArray) {
                $town = $response->addChild('town');
                if(isset($townArray['code'])) $town->addChild('code', $townArray['code']);
                if(isset($townArray['name'])) $town->addChild('name', $townArray['name']);
                if(isset($townArray['fiascode'])) $town->addChild('fiascode', $townArray['fiascode']);
                if(isset($townArray['kladrcode'])) $town->addChild('kladrcode', $townArray['kladrcode']);
                if(isset($townArray['shortname'])) $town->addChild('shortname', $townArray['shortname']);
                if(isset($townArray['typename'])) $town->addChild('typename', $townArray['typename']);
                if(isset($townArray['city']) && is_array($townArray['city'])) {
                    $city = $town->addChild('city');
                    if(isset($townArray['city']['code'])) $city->addChild('code', $townArray['city']['code']);
                    if(isset($townArray['city']['name'])) $city->addChild('name', $townArray['city']['name']);
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
            if($codesearch->zipcode) $result['zipcode'] = strval($codesearch->zipcode);
            if($codesearch->kladrcode) $result['kladrcode'] = strval($codesearch->kladrcode);
            if($codesearch->fiascode) $result['fiascode'] = strval($codesearch->fiascode);
            if($codesearch->code) $result['code'] = strval($codesearch->code);
        } else {
            if($conditions = $xml->conditions){
                if($conditions->city) $result['conditions']['city'] = strval($conditions->city);
                if($conditions->namecontains) $result['conditions']['namecontains'] = strval($conditions->namecontains);
                if($conditions->namestarts) $result['conditions']['namestarts'] = strval($conditions->namestarts);
                if($conditions->name) $result['conditions']['name'] = strval($conditions->name);
                if($conditions->fullname) $result['conditions']['fullname'] = strval($conditions->fullname);
                if($conditions->country) $result['conditions']['country'] = strval($conditions->country);

                if($limit = $xml->limit){
                    if($limit->limitfrom) $result['limit']['limitfrom'] = strval($limit->limitfrom);
                    if($limit->limitcount) $result['limit']['limitcount'] = strval($limit->limitcount);
                    if($limit->countall) $result['limit']['countall'] = strval($limit->countall);
                }
            }
        }
        return $result;
    }
}