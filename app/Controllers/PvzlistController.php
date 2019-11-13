<?php
/**
 * Контроллер для запроса пунктов самовывоза
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class PvzlistController extends BaseController
{
    public function run($xml)
    {
        $data = $this->getDataFromXml($xml);

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getPvzList($data), true);

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
        $response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><pvzlist/>');

        if(isset($data['pvzlist']) & is_array($data['pvzlist'])){
            $count = count($data['pvzlist']);
            $response->addAttribute('count', $count);

            foreach ($data['pvzlist'] as $pvzArray) {
                $pvz = $response->addChild('pvz');
                foreach ($pvzArray as $key => $item) {
                    $pvz->addChild($key, $item);
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

        if($xml->town) $result['town'] = strval($xml->town);
        if($xml->parentcode) $result['parentcode'] = strval($xml->parentcode);
        if($xml->acceptcash) $result['acceptcash'] = strval($xml->acceptcash);
        if($xml->acceptcard) $result['acceptcard'] = strval($xml->acceptcard);
        if($xml->acceptfitting) $result['acceptfitting'] = strval($xml->acceptfitting);
        if($xml->acceptindividuals) $result['acceptindividuals'] = strval($xml->acceptindividuals);
        if($xml->lt) $result['lt'] = strval($xml->lt);
        if($xml->lg) $result['lg'] = strval($xml->lg);
        if($xml->rt) $result['rt'] = strval($xml->rt);
        if($xml->rg) $result['rg'] = strval($xml->rg);

        return $result;
    }
}