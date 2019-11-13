<?php
/**
 * Контроллер для запроса на получение списка актов передачи денег
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class SmalistController extends BaseController
{
    public function run($xml)
    {
        $data = $this->getDataFromXml($xml);

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getSmalist($data), true);

        if($result['success']){
            ResponseContainer::$readyResponse = $this->makeXmlResponse($result);
        } else {
            ResponseContainer::addAttachment('error', '', [
                'error' => isset($result['errorNumber']) ? $result['errorNumber'] : '',
                'errormsg' => isset($result['error']) ? $result['error'] : '',
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
        $response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><smalist/>');

        if(isset($data['smalist']) & is_array($data['smalist'])){
            $count = count($data['smalist']);
            $response->addAttribute('count', $count);

            foreach ($data['smalist'] as $smaArray) {
                $sma = $response->addChild('sma');
                foreach ($smaArray as $key => $item) {
                    $sma->addChild($key, $item);
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

        if(strval($xml->datefrom)) $result['datefrom'] = strval($xml->datefrom);
        if(strval($xml->dateto)) $result['dateto'] = strval($xml->dateto);

        return $result;
    }
}