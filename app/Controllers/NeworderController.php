<?php
namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\Logger;
use App\Helpers\ResponseContainer;
use App\Models\DeliverysetModel;
use App\Models\ItemModel;
use App\Models\OrderModel;
use App\Models\PackageModel;

class NeworderController extends BaseController
{
    private $coreOrders;
    /**
     * @param $xml \SimpleXMLElement
     * @return null
     */
    public function run(\SimpleXMLElement $xml)
    {
//        header("Content-Type: application/json; charset=UTF-8");
        $this->coreOrders = new \orders();
        $orders = $this->prepareDataForModel($xml);
        $clientCode = AuthHelper::getUserCode();
        $newFolder = $orders['newFolder'];

//        $tableRules = new \tablesRules();
//        print_r($orders);
//        die;
        foreach ($orders['orders'] as $order){
            if(!$this->validateOrder($order)){
                ResponseContainer::addAttachment('createorder', '', [
                    'error' => '9',
                    'errormsg' => 'Empty receiver name and company'
                ]);
                continue;
            }
            if(isset($order['return']) && $order['return'] === "YES") {
                $flag = 'T';
            } else {
                $flag = 'F';
            }
            //todo ReceiverPays нет?
            $result = $this->coreOrders->createOrder([
                'number' => $order['orderno'] ?? false,
                'target' => $order['receiver_company'] ?? false,
                'name' => $order['receiver_name'] ?? false,
                'address' => $order['address'] ?? false,
                'phone' => $order['phone'] ?? false,
                'poruch' => $order['instruction'] ?? false,
                'vlog' => $order['enclosure'] ?? false,
                'date_putn' => $order['receiver_date'] ?? false,
                'time_put_min' => $order['receiver_time_min'] ?? false,
                'time_put_max' => $order['receiver_time_max'] ?? false,
                'source' => $clientCode, // test 758
                'StrBarCode' => $order['barcode'] ?? false,
                'mode' => $order['service'] ?? false,
                'type' => $order['type'] ?? false,
                'flag' => $flag,
                'mass' => $order['weight'] ?? false,
                'massv' => $order['return_weight'] ?? false,
                'Kol_vo' => $order['quantity'] ?? false,
                'PaymentType' => $order['paytype'] ?? false,
                'modev' => $order['return_service'] ?? false,
                'typev' => $order['return_type'] ?? false,
                'rur' => $order['price'] ?? false,
                'price' => $order['deliveryprice'] ?? false,
                'InshPrice' => $order['inshprice'] ?? false,
            ], $newFolder
            );

            $result = json_decode($result, true);
            if($result['success']){
                ResponseContainer::addAttachment('createorder', '', [
                    'orderno' => $result['clientId'],
                    'error' => '0',
                    'errormsg' => 'success'
                ]);

                $order['orderno'] = $result['clientId'];
                $order['address_id'] = $result['code'];
                $order['client_id'] = $clientCode;
                //статус 1 - новый заказ
                $order['status'] = 1;

                $model = new OrderModel($order);
                if($model->save()) {
                    $orderId = $model->id;
                    if(isset($order['items'])){
                        foreach ($order['items'] as $item) {
                            $itemModel = new ItemModel($item);
                            $itemModel->order_id = $orderId;
                            $itemModel->save();
                        }
                    }
                    if(isset($order['packages'])){
                        foreach ($order['packages'] as $package) {
                            $packageModel = new PackageModel($package);
                            $packageModel->order_id = $orderId;
                            $packageModel->save();
                        }
                    }
                    if(isset($order['deliverysets'])){
                        foreach ($order['deliverysets'] as $deliveryset) {
                            $deliverysetModel = new DeliverysetModel($deliveryset);
                            $deliverysetModel->order_id = $orderId;
                            $deliverysetModel->save();
                        }
                    }
                }
            } else {
                Logger::loging(ROOT. '/app/Logs/'.__FILE__.date('Ymd'),'Ошибка при созданиие заказа: '.$result['error']);
                ResponseContainer::addAttachment('createorder', '', [
//                    'orderno' => $resultOfCreating['orderno'],
                    'error' => $result['errorNumber'],
                    'errormsg' => $result['error']
                ]);
            }
        }
    }

    /**
     * Собирает из xml массив для модели
     *
     * @param $xml \SimpleXMLElement
     * @return array
     */
    private function prepareDataForModel($xml)
    {
        $data = [];

        $data['newFolder'] = (strtolower($xml->attributes()->newfolder) == 'yes') ? true : false;
        foreach ($xml->order as $order) {
            $data['orders'][] = $this->getDataFromOrder($order);
        }

        return $data;
    }

    /**
     * Возвращает из заказа нужные данные
     *
     * @param $order \SimpleXMLElement
     * @return array
     */
    private function getDataFromOrder($order)
    {
        // костыль для того, чтобы предвставить $order в виде массива
        $orderArray = json_decode(json_encode($order), true);
        $result = [];
        if ($order->attributes()->orderno) $result['orderno'] = (string) $order->attributes()->orderno;
        foreach ($orderArray as $key => $item) {
            if(is_array($item)) continue;
            $result[$key] = $item;
        }
        if(isset($orderArray['receiver'])){
            foreach ($orderArray['receiver'] as $key => $item) {
                $result['receiver_'.$key] = $item;
            }
        }
        if(isset($orderArray['sender'])){
            foreach ($orderArray['sender'] as $key => $item) {
                $result['sender_'.$key] = $item;
            }
        }
        if(isset($orderArray['items'])){
            $items = $order->xpath('items/item');
            foreach ($items as $item){
                $itemArray = [];
                $itemArray['title'] = (string) $item;
                foreach ($item->attributes() as $key => $attr){
                    $key = strtolower($key);
                    $itemArray[$key] = (string) $attr;
                }
                $result['items'][] = $itemArray;
            }
        }
        if(isset($orderArray['packages']['package'])){
            foreach ($orderArray['packages']['package'] as $package){
                if (isset($package['@attributes'])){
                    $packageArray = [];
                    foreach ($package['@attributes'] as $key => $attribute){
                        $packageArray[$key] = $attribute;
                    }
                    $result['packages'][] = $packageArray;
                }
            }
        }
        if(isset($orderArray['deliveryset'])){
            $deliverysetsAttr = [];
            if(isset($orderArray['deliveryset']['@attributes'])){
                foreach ($orderArray['deliveryset']['@attributes'] as $key => $attribute){
                    $deliverysetsAttr[$key] = $attribute;
                }
            }
            if(isset($orderArray['deliveryset']['below'])){
                foreach ($orderArray['deliveryset']['below'] as $below){
                    if(isset($below['@attributes'])){
                        $belowAttr = [];
                        foreach ($below['@attributes'] as $key => $attribute){
                            $belowAttr[$key] = $attribute;
                        }
                        if($deliverysetsAttr) {
                            $belowAttr = array_merge($deliverysetsAttr, $belowAttr);
                        }
                        $result['deliverysets'][] = $belowAttr;
                    }
                }
            }
            if(!isset($result['deliverysets']) && $deliverysetsAttr) $result['deliverysets'][] = $deliverysetsAttr;
        }
        return $result;
    }

    /**
     * Проверяет корректность данных о заказе
     * (пока только смотрит наличие имени отправителя или компании, иначе ошибка)
     *
     * @param $order array
     * @return bool
     */
    private function validateOrder($order)
    {
        if(!isset($order['receiver_person']) && !isset($order['receiver_company'])){
            return false;
        }
        return true;
    }
}