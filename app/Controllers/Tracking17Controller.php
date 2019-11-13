<?php
/**
 * Контроллер для трекинга заказа по номеру
 * Ответ в виде JSON (скорее всего, такой один, все остальные возвращают клиенту XML)
 * Поэтому используется exit
 *
 */
namespace App\Controllers;

use App\TempClasses\MilHelper;

class Tracking17Controller extends BaseController
{
    public function run($xml)
    {
        $orderno = (string) $xml->orderno;

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getTrackingByOrderno($orderno), true);

        if($result['success']){
            $response = $this->makeArrayResponse($result['tracking'], $orderno);
        } else {
            $response = [
                'error' => [
                    'error' => isset($result['errorNumber']) ? $result['errorNumber'] : '',
                    'errormsg' => isset($result['error']) ? $result['error'] : '',
                ]
            ];
        }
        header('Content-type: application/json');
        echo json_encode($response);
        exit();
    }

    /**
     *
     * @param $tracking array
     * @param $orderno string
     * @return array
     */
    private function makeArrayResponse($tracking, $orderno)
    {
        $response = [];

        $response['number'] = $orderno;
        $response['oriNumber'] = $orderno;

        if(isset($tracking['receiver']['value'])){
            foreach ($tracking['receiver']['value'] as $elem) {
                if(isset($elem['town'])) {
                    if(isset($elem['town']['attr']['country'])) $response['destCountry'] = $elem['town']['attr']['country'];
                }
            }
        }
        if(isset($tracking['sender']['value'])){
            foreach ($tracking['sender']['value'] as $elem) {
                if(isset($elem['town'])) {
                    if(isset($elem['town']['attr']['country'])) $response['oriCountry'] = $elem['town']['attr']['country'];
                }
            }
        }
        if(isset($tracking['status']['value'])) $response['status'] = $tracking['status']['value'];

        $response['events'] = [];

        if (isset($tracking['statushistory']['value'])) {
            foreach ($tracking['statushistory']['value'] as $event) {
                $response['events'][] = [
                    'time' => $event['status']['attr']['eventtime'],
                    'location' => $event['status']['attr']['country'],
                    'content' => $event['status']['value'],
                ];
            }
        }


        return $response;
    }
}