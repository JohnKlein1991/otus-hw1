<?php
/**
 * Контроллер для запроса на получение детализации актов передачи денег
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class SmadetailController extends BaseController
{
    public function run($xml)
    {
        if(!$this->validateXml($xml)) return;
        $data = $this->getDataFromXml($xml);

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getSmaDetail($data), true);

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

        if(isset($data['smadetail']) & is_array($data['smadetail'])){
            $count = count($data['smadetail']);
            $response->addAttribute('count', $count);

            foreach ($data['smadetail'] as $smaArray) {
                $sma = $response->addChild('specialsma');
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
        $result['code'] = strval($xml->code);

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
        if(!strval($xml->code)) {
            ResponseContainer::addAttachment('error', '', [
                'errormsg' => 'Не передано обязательное поле code'
            ]);
            return false;
        }
        return true;
    }
}