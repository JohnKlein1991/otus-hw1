<?php
/**
 * Контроллер для изменения статуса агентом
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\Models\ImageModel;
use App\Models\UpdatingOrderImagesModel;
use App\Models\UpdatingOrderItemsModel;
use App\Models\UpdatingOrdersModel;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class SetorderinfoController extends BaseController
{
    public function run($xml)
    {
        if(!$this->validateXml($xml)) return;

        foreach ($xml->order as $order) {
            if(!$this->validateOrder($order)) continue;
            $data = $this->getDataFromOrder($order);
            $this->logDataIntoOrdersUpdatedByClients($data);

            //todo временная заглушка, в будущем будет удалена
            $milHelper = new MilHelper();
            $result = json_decode($milHelper->chandeOrderStatus($data), true);

            if($result['success']){
                ResponseContainer::addAttachment('order', '', [
                    'ordercode' => strval($order->attributes()->ordercode),
                    'error' => 0,
                    'errormsg' => 'OK'
                ]);
            } else {
                ResponseContainer::addAttachment('order', '', [
                    'ordercode' => strval($order->attributes()->ordercode),
                    'error' => $result['errorNumber'],
                    'errormsg' => $result['error']
                ]);
            }
        }
    }

    /**
     * Записывает данные из запроса в таблицу
     *
     * @param $data array
     * @result boll
     */
    private function logDataIntoOrdersUpdatedByClients($data)
    {
        $model = new UpdatingOrdersModel($data['data']);
        if($model->save()) {
            $updatingOrderId = $model->id;
            if(isset($data['items'])){
                foreach ($data['items'] as $item) {
                    $itemModel = new UpdatingOrderItemsModel($item);
                    $itemModel->updating_order_id = $updatingOrderId;
                    $itemModel->save();
                }
            }
            if(isset($data['images'])){
                foreach ($data['images'] as $image) {
                    $imageModel = new UpdatingOrderImagesModel($image);
                    $imageModel->updating_order_id = $updatingOrderId;
                    $imageModel->save();
                }
            }
        }
    }

    /**
     * Забирает нужные данные из xml и возвращает их в виде массива
     *
     * @param $order SimpleXMLElement
     * @return array
     */
    private function getDataFromOrder($order)
    {
        $result = [];

        $data = [];
        $data['ordercode'] = strval($order->attributes()->ordercode);
        if(isset($order->status)) {
            $data['status'] = strval($order->status);
            $data['eventtime'] = strval($order->eventtime);
        }
        if(isset($order->message)) $data['message'] = strval($order->message);
        if(isset($order->paytype)) $data['paytype'] = strval($order->paytype);
        $result['data'] = $data;

        if($order->items->item){
            $items = [];
            foreach ($order->items->item as $item) {
                $array = [];
                if(isset($item->attributes()->code)) $array['code'] = strval($item->attributes()->code);
                if(isset($item->attributes()->quantity)) $array['quantity'] = strval($item->attributes()->quantity);
                if(isset($item->attributes()->reason)) $array['reason'] = strval($item->attributes()->reason);
                $items[] = $array;
            }
            $result['items'] = $items;
        }

        if($order->image){
            $images = [];
            foreach ($order->image as $image) {
                $array = [];
                if(isset($image->attributes()->filename)) $array['filename'] = strval($image->attributes()->filename);
                $textData = strval($image);
                $imagePath = $this->saveImage($textData);
                if($imagePath) {
                    $link = $this->createImageLink($imagePath);
                    $array['link'] = $link;
                }
                $images[] = $array;
            }
            $result['images'] = $images;
        }

        return $result;
    }


    /**
     * Создает ссылку на изображение
     *
     * @param $path string
     * @return string
     */
    public function createImageLink($path)
    {
        $domain = $_SERVER['HTTP_HOST'];
        return $domain . $path;
    }

    /**
     * Сохраняет изображение из переданного текста
     *
     * @param $string string
     * @return bool
     */
    private function saveImage($string)
    {
        $data = base64_decode($string);
        $randName = base64_encode(uniqid());
        $folder = "/app/Storage/".date('Ymd')."/";
        if(!is_dir(ROOT . $folder)) mkdir(ROOT . $folder);
        $file = fopen(ROOT . $folder . $randName, 'w');
        if($file && fwrite($file, $data)) {
            return $folder . $randName;
        } else {
            return false;
        }
    }

    /**
     * Проверяет наличие обязательных полей и атрибутов у сделки
     *
     * @param $order SimpleXMLElement
     * @return bool
     */
    private function validateOrder($order)
    {
        if(!(strval($order->attributes()->ordercode))) {
            ResponseContainer::addAttachment('order', '', [
                'errormsg' => 'Атрибут ordercode обязателен для поля order'
            ]);
            return false;
        }

        if($order->status) {
            if(!strval($order->eventtime)){
                ResponseContainer::addAttachment('order', '', [
                    'errormsg' => 'Поле eventtime обязательно при наличие поля status'
                ]);
                return false;
            }
        }

        return true;
    }


    /**
     * Проверяет валидность xml для данного вида запроса (наличие обязательных полей)
     *
     * @param $xml SimpleXMLElement
     * @return bool
     */
    private function validateXml($xml)
    {
        if(!$xml->order) {
            ResponseContainer::addAttachment('error', '', [
                'errormsg' => 'Не передано обязательное поле order'
            ]);
            return false;
        }
        return true;
    }
}