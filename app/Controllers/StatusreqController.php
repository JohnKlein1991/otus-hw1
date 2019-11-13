<?php
/**
 * Контроллер для проверки статуса заказа
 *
 */
namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\ResponseContainer;
use App\Models\OrderModel;
use SimpleXMLElement;

class StatusreqController extends BaseController
{
    private $coreOrders;
    private $coreGivn;
    // параметр done из запроса
    private $done = false;
    /**
     * @param SimpleXMLElement
     * @return null
     */
    public function run(SimpleXMLElement $xml)
    {
        $this->coreOrders = new \orders();
        $this->coreGivn =  new \givn();
//        $tableRules = new \tablesRules();
        $clientCode = AuthHelper::getUserCode();


//        header("Content-Type: application/json; charset=UTF-8");
//
//        $givn = $this->coreGivn->getGivn(['code','courierName', 'courierPhone', 'State', 'kurierstate'],
//
//            7795811
//        );
//        print_r($givn);
//
//        die;

        if ($this->isOnlyLast($xml)) {

            $ordersIds = OrderModel::getOrdersIdsForOnlyLastRequest($clientCode);
            $fields = $this->getFieldsForOrderRequest();

            // мы не можем передать в selectOrders сразу весь массив с id, поэтому по каждому ордеру делаем отдельный запрос
            // и складываем все полученные данные в ordersData
            // keysArray - массив ключей (приходит первым в массиве данных, которые призодят в core методах)
            $ordersData = [];
            $keysArray = [];
            $idsShownOrders = [];
            foreach ($ordersIds as $ordersId) {
                $orderData = $this->coreOrders->selectOrders($fields, false, false, $ordersId);
                $orderData = json_decode($orderData, true);
                if($orderData['success']) {
                    if(!$keysArray) $keysArray = array_flip($orderData[0][0]);
                    $ordersData[] = $orderData[0][1];
                    $idsShownOrders[] = $ordersId;
                }
            }
            OrderModel::markOrdersAsShown($idsShownOrders);
            ResponseContainer::$readyResponse = $this->makeXmlResponse($ordersData, $keysArray);
        } else {
            $datesArray = $this->getDates($xml);
            $additionalData = $this->getAdditionalData($xml);

            $fields = $this->getFieldsForOrderRequest();
            $foldersIds = $this->getFoldersIds($datesArray['dateFrom'], $datesArray['dateTo'], $clientCode);
            $code = $additionalData['ordercode'];

            $res = $this->coreOrders->selectOrders($fields, false, false, $code);
            $result = json_decode($res, true);

            if($result['success']){
                $data = $result[0];
                $keysArray = array_flip(array_shift($data));
                ResponseContainer::$readyResponse = $this->makeXmlResponse($data, $keysArray);
            } else {
                ResponseContainer::addAttachment('error', '', [
                    'error' => isset($result['errorNumber']) ? $result['errorNumber'] : '',
                    'errormsg' => isset($result['error']) ? $result['error'] : '',
                ]);
            }
        }
    }

    /**
     * Возвращает значения $dateFrom и $dateTo - диапазон для поиска заказов в БД
     *
     * @param $xml \SimpleXMLElement
     * @return array
     */
    private function getDates($xml)
    {
        $dateFrom = (string)$xml->datefrom;
        $dateTo = (string)$xml->dateto;

        if (!$dateFrom && !$dateTo) {
            $dateTo = date('Y-m-d');
            $dateFrom = date('Y-m-d', strtotime('-2 months'));
        } elseif (!$dateTo && $dateFrom) {
            $dateTo = strtotime("+2 months", strtotime($dateFrom));
        } elseif ($dateTo && !$dateFrom) {
            $dateFrom = strtotime("-2 months", strtotime($dateTo));
        }

        return compact('dateFrom', 'dateTo');
    }

    /**
     * Проверяет, есть ли в запросе параметр only_last
     *
     * @param $xml \SimpleXMLElement
     * @return bool
     */
    private function isOnlyLast($xml)
    {
        return $xml->changes == 'ONLY_LAST';
    }

    /**
     * Возвращает массив со значениями необязательных аттрибутов
     *
     * @param $xml \SimpleXMLElement
     * @return array
     */
    private function getAdditionalData($xml)
    {
        $result = [
            'isAgent' => false,
            'orderno' => false,
            'ordercode' => false,
            'orderno2' => false,
            'target' => false,
            'done' => false,
            'changes' => false,
            'quickstatus' => true,
        ];

        if ($xml->client == 'AGENT') $result['isAgent'] = true;
        if ($xml->orderno) $result['orderno'] = (string) $xml->orderno;
        if ($xml->ordercode) $result['ordercode'] = (string) $xml->ordercode;
        if ($xml->orderno2) $result['orderno2'] = (string) $xml->orderno2;
        if ($xml->target) $result['target'] = (string) $xml->target;
        switch ($xml->done) {
            case 'ONLY_NOT_DONE': $result['done'] = 'ONLY_NOT_DONE';
                break;
            case 'ONLY_DONE': $result['done'] = 'ONLY_DONE';
                break;
            case 'ONLY_NEW': $result['done'] = 'ONLY_NEW';
                break;
            case 'ONLY_DELIVERY': $result['done'] = 'ONLY_DELIVERY';
                break;
        }
        if($result['done']) $this->done = $result['done'];
        if ($xml->quickstatus == 'NO') $result['quickstatus'] = false;

        return $result;
    }

    /**
     * Возращает массив с id папок по заданным датам и клиент id
     *
     * @param $dateFrom string
     * @param $dateTo string
     * @param $clientId string
     * @return array
     */
    private function getFoldersIds($dateFrom, $dateTo, $clientId)
    {
        $folders = json_decode($this->coreOrders->selectFolders(['code'], $dateFrom, $dateTo, [$clientId]), true);
        if($folders['success']) {
            $result = [];
            // first element is only a keys description
            array_shift($folders[0]);
            foreach ($folders[0] as $folder) {
                $result[] = $folder[0];
            }
            return $result;
        } else {
            return [];
        }
    }

    /**
     * Проверяет , соответствует ли статус заказа условию, переданому в параметре done
     *
     * @param $status string
     * @return bool
     */
    private function checkStatus($status)
    {
        return true;
    }

    /**
     * Делает из массива данных xml для ответа пользователю
     *
     * @param $data array
     * @return \SimpleXMLElement
     */

    private function makeXmlResponse($data, $keysArray)
    {
        $response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><statusreq/>');
        $count = count($data);
        $response->addAttribute('count', htmlspecialchars($count));

        foreach ($data as $order) {
//            print_r($keysArray);
//            print_r($order);
            // проверка статусы, если был передан параметр done
            $status = $order[$keysArray['State1Name']];
            if($this->done && !$this->checkStatus($status)) continue;

            $orderXml = $response->addChild('order');
            $orderXml->addChild('barcode', $order[$keysArray['strbarcode']]);
            $orderXml->addAttribute('orderno', $order[$keysArray['strbarcode']]);
            $orderXml->addAttribute('ordercode', $order[$keysArray['code']]);
            // todo add another attributes orderno="4351819" awb="4351819" orderno2="4351819" ordercode="7636044" givencode="">

            $sender = $orderXml->addChild('sender');
            $sender->addChild('company', htmlspecialchars($order[$keysArray['senderCompany']]));
            $sender->addChild('person', htmlspecialchars($order[$keysArray['senderName']]));
            $senderTown = $sender->addChild('town', $order[$keysArray['TownFrom']]);
            $senderTown->addAttribute('code', $order[$keysArray['TownFrom']]);
            $sender->addChild('address', htmlspecialchars($order[$keysArray['senderAddress']]));
            $sender->addChild('phone', htmlspecialchars($order[$keysArray['phone']]));
            $senderContacts = $sender->addChild('contacts');
            $senderContacts->addChild('phone');
            $senderContacts->addChild('email');
            $sender->addChild('date', $order[$keysArray['date_putn2']]);
            $sender->addChild('time_min', $order[$keysArray['time_put_min2']]);
            $sender->addChild('time_max', $order[$keysArray['time_put_max2']]);

            $receiver = $orderXml->addChild('receiver');
            $receiver->addChild('company', htmlspecialchars($order[$keysArray['target']]));
            $receiver->addChild('person', htmlspecialchars($order[$keysArray['name']]));
            $receiver->addChild('phone', htmlspecialchars($order[$keysArray['senderPhone']]));
            $receiverContacts = $receiver->addChild('contacts');
            $receiverContacts->addChild('phone');
            $receiverContacts->addChild('email');
            $receiver->addChild('zipcode', $order[$keysArray['ZipCode']]);
            $receiver->addChild('town', $order[$keysArray['TownTo']]);
            $receiver->addChild('address', htmlspecialchars($order[$keysArray['address']]));
            $receiver->addChild('date', $order[$keysArray['date_putn']]);
            $receiver->addChild('time_min', $order[$keysArray['time_put_min']]);
            $receiver->addChild('time_max', $order[$keysArray['time_put_max']]);
            $receiverCoords = $receiver->addChild('coords');
            $receiverCoords->addAttribute('lat', '');
            $receiverCoords->addAttribute('lon', '');

            $orderXml->addChild('return', $order[$keysArray['flag']]);
            $orderXml->addChild('weight', $order[$keysArray['mass']]);
            $orderXml->addChild('return_weight', $order[$keysArray['massv']]);
            $orderXml->addChild('quantity', $order[$keysArray['Kol_vo']]);
            $orderXml->addChild('paytype', $order[$keysArray['PaymentType']]);

//            if(isset($order['print_check'])) $orderXml->addChild('print_check', $order['print_check']);

            $orderXml->addChild('service', $order[$keysArray['mode']]);
            $orderXml->addChild('return_service', $order[$keysArray['modev']]);
            $orderXml->addChild('type', $order[$keysArray['type']]);
            $orderXml->addChild('return_type', $order[$keysArray['typev']]);
            $orderXml->addChild('waittime', $order[$keysArray['Waited']]);
            $orderXml->addChild('price', $order[$keysArray['rur']]);
            $orderXml->addChild('inshprice', $order[$keysArray['InshPrice']]);
            $orderXml->addChild('enclosure', htmlspecialchars($order[$keysArray['vlog']]));
            $orderXml->addChild('instruction', htmlspecialchars($order[$keysArray['poruch']]));

            $currcoords = $orderXml->addChild('currcoords');
            $currcoords->addAttribute('lat', '');
            $currcoords->addAttribute('lon', '');
            $currcoords->addAttribute('accuracy', '');
            $currcoords->addAttribute('RequestDateTime', '');

            $courierCode = '';
            $courierName = '';
            $courierPhone = '';
            $courierInfo = json_decode($this->coreGivn->getGivn(['code','courierName', 'courierPhone', 'State', 'kurierstate'],
                $order[$keysArray['code']]
            ), true);
            if($courierInfo['success']){
                foreach ($courierInfo[0] as $item){
                    if($item['3'] === '5' && $item['4'] === '5') {
                        $courierCode = $item[0];
                        $courierName = $item[1];
                        $courierPhone = $item[2];
                    }
                }
            }
            $courier = $orderXml->addChild('courier');
            $courier->addChild('code', $courierCode);
            $courier->addChild('name', $courierName);
            $courier->addChild('phone', $courierPhone);
            //todo нужно доделать. Нужен метод, который будет возввращать все виды доп услуг, они записываются в тэг deliveryprice
            $orderXml->addChild('deliveryprice');
            $receiverPays = ($order[$keysArray['ReceiverPays']] == 'F') ? 'NO' : 'YES';
            $orderXml->addChild('receiverpays', $receiverPays);

//            $tr = new \tablesRules();
//            print_r($tr->tableRules(5, true));
//            die;

            $statusesInfo = json_decode($this->coreOrders->getOrderStatuses(['Store', 'CreateTime', 'Statetime', 'Message', 'Name', 'code', 'Advansed', 'State'],$order[$keysArray['code']]), true);
//            $statusesInfo = json_decode($this->coreOrders->getOrderStatuses(['Store', 'CreateTime', 'Statetime', 'Message', 'Name', 'code', 'Advansed', 'State'],7782096, true));
//            print_r($statusesInfo);
//            die;

            $lastStatus = end($statusesInfo[0]);
            $lastStatusXml = $orderXml->addChild('status', $lastStatus[6]);
            $lastStatusXml->addAttribute('eventstore', $lastStatus[0]);
            $lastStatusXml->addAttribute('eventtime', $lastStatus[1]);
            $lastStatusXml->addAttribute('createtimegmt', $lastStatus[2]);
            $lastStatusXml->addAttribute('message', $lastStatus[3]);
            $lastStatusXml->addAttribute('title', $lastStatus[4]);

            $statusHistory = $orderXml->addChild('statushistory');
            if($statusesInfo['success']){
                array_shift($statusesInfo[0]);
                foreach ($statusesInfo[0] as $statusItem) {
                    $statusItemXml = $statusHistory->addChild('status', $statusItem[6]);
                    $statusItemXml->addAttribute('eventstore', $statusItem[0]);
                    $statusItemXml->addAttribute('eventtime', $statusItem[1]);
                    $statusItemXml->addAttribute('createtimegmt', $statusItem[2]);
                    $statusItemXml->addAttribute('message', $statusItem[3]);
                    $statusItemXml->addAttribute('title', $statusItem[4]);
                }
            }

            $orderXml->addChild('customstatecode', $order[$keysArray['State1']]);
            $orderXml->addChild('deliveredto', $order[$keysArray['message']]);
            $orderXml->addChild('delivereddate', $order[$keysArray['date_put']]);
            $orderXml->addChild('deliveredtime', $order[$keysArray['time_put']]);
            $orderXml->addChild('outstrbarcode', $order[$keysArray['OutStrBarcode']]);
        }

        return $response;
    }

    /**
     * В элементе phone может быть записана любая строка. Метод вычленяет из нее телефон(ы) и email(ы)
     *
     * @param $str string
     * @return array
     */
    private function parsePhoneElement(string $str)
    {
        $phonePattern = '';
        $emailPattern = '';
    }

    /**
     *
     */
    private function getFieldsForOrderRequest()
    {
        return [
            'code',
            'strbarcode', // barcode
            'senderCompany',
            'senderName',
            'TownFrom', // sender->town->attrt(code)
            'senderPhone',
            'senderAddress',
            'date_putn2', // sender->date
            'time_put_min2', // sender->time_min
            'time_put_max2', // sender->time_max

            'target', // receiver->company
            'name', // receiver->person
            'phone', // receiver->phone
            'TownTo', // receiver->town->code
            // town
            'ZipCode', // zipcode
            'address', // receiver->address
            'PVZCode', // PVZ->code
            'date_putn', // receiver->date
            'time_put_min', // receiver->time_min
            'time_put_max', // receiver->time_max

            'flag', // return
            'mass', // weight
            'massv', // return_weight
            'Kol_vo', // quantity
            'PaymentType', // paytype
            'mode', // service
            'modev', // return_service
            'type', // type
            'typev', // return_type
            'Waited', // waittime
            'rur', // price
            //print_check
            'InshPrice', // inshprice
            'vlog', // enclosure
            'poruch', // instructions
            // currcoords
            'kurier', // courier->code
            'kurierName', // courier->name
            // courier->phone
            'price', // deliveryprice->total
            'ReceiverPays', // receiverpays
            'State1Name', //todo статусы надо дополнить
            'State1', // customstatecode
            //clientstatecode
            'message', //deliveredto
            'date_put', // delivereddate
            'time_put', // deliveredtime
            'OutStrBarcode', //outstrbarcode

        ];
    }
}