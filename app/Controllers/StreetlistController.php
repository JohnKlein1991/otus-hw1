<?php
/**
 * Контроллер для справочника улиц
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class StreetlistController extends BaseController
{
    public function run($xml)
    {
        if(!$this->validateXml($xml)) return;
        $data = $this->getDataFromXml($xml);

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getStreetList($data), true);

        if($result['success']){
            ResponseContainer::$readyResponse = $this->makeXmlResponse($result);
        } else {
            ResponseContainer::addAttachment('streetlist', '', [
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
        $response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><streetlist/>');

        $page = $data['page'] ?? '';
        $totalCount = $data['totalcount'] ?? '';
        $totalPages = $data['totalpages'] ?? '';

        $response->addAttribute('page', $page);
        $response->addAttribute('totalCount', $totalCount);
        $response->addAttribute('totalPages', $totalPages);

        if(isset($data['streetlist']) & is_array($data['streetlist'])){
            $count = count($data['streetlist']);
            $response->addAttribute('count', $count);

            foreach ($data['streetlist'] as $streetArray) {
                $street = $response->addChild('street');
                if(isset($streetArray['name'])) $street->addChild('name', $streetArray['name']);
                if(isset($streetArray['shortname'])) $street->addChild('shortname', $streetArray['shortname']);
                if(isset($streetArray['typename'])) $street->addChild('typename', $streetArray['typename']);
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

        if($conditions = $xml->conditions){
            if($conditions->city) $result['conditions']['town'] = strval($conditions->city);
            if($conditions->namecontains) $result['conditions']['namecontains'] = strval($conditions->namecontains);
            if($conditions->namestarts) $result['conditions']['namestarts'] = strval($conditions->namestarts);
            if($conditions->name) $result['conditions']['name'] = strval($conditions->name);
            if($conditions->fullname) $result['conditions']['fullname'] = strval($conditions->fullname);

            if($limit = $xml->limit){
                if($limit->limitfrom) $result['limit']['limitfrom'] = strval($limit->limitfrom);
                if($limit->limitcount) $result['limit']['limitcount'] = strval($limit->limitcount);
                if($limit->countall) $result['limit']['countall'] = strval($limit->countall);
            }
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
        if(!$xml->conditions && !strval($xml->conditions->town)) {
            ResponseContainer::addAttachment('error', '', [
                'errormsg' => 'Не передано обязательное поле town в тэге conditions'
            ]);
            return false;
        }
        return true;
    }
}