<?php
/**
 * Контроллер для запроса видов срочности
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class ServicesController extends BaseController
{
    public function run($xml)
    {
        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getServices(), true);

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
        $response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><services/>');

        if(isset($data['services']) & is_array($data['services'])){
            $count = count($data['services']);
            $response->addAttribute('count', $count);

            foreach ($data['services'] as $serviceArray) {
                $service = $response->addChild('service');
                foreach ($serviceArray as $key => $item) {
                    $service->addChild($key, $item);
                }
            }
        }

        return $response;
    }
}