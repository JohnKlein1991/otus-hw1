<?php
/**
 * Контроллер для справочника номенклатуры
 *
 */
namespace App\Controllers;

use App\Helpers\ResponseContainer;
use App\TempClasses\MilHelper;
use SimpleXMLElement;

class ItemlistController extends BaseController
{
    public function run($xml)
    {
        $data = $this->getDataFromXml($xml);

        //todo временная заглушка, в будущем будет удалена
        $milHelper = new MilHelper();
        $result = json_decode($milHelper->getItemlist($data), true);

        if($result['success']){
            ResponseContainer::$readyResponse = $this->makeXmlResponse($result);
        } else {
            ResponseContainer::addAttachment('itemlist', '', [
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
        $response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><itemlist/>');

        $page = $data['page'] ?? '';
        $totalCount = $data['totalcount'] ?? '';
        $totalPages = $data['totalpages'] ?? '';

        $response->addAttribute('page', $page);
        $response->addAttribute('totalCount', $totalCount);
        $response->addAttribute('totalPages', $totalPages);

        if(isset($data['itemlist']) & is_array($data['itemlist'])){
            $count = count($data['itemlist']);
            $response->addAttribute('count', $count);

            foreach ($data['itemlist'] as $itemArray) {
                $item = $response->addChild('item');
                if(isset($itemArray['code'])) $item->addChild('code', $itemArray['code']);
                if(isset($itemArray['name'])) $item->addChild('name', $itemArray['name']);
                if(isset($itemArray['article'])) $item->addChild('article', $itemArray['article']);
                if(isset($itemArray['barcode'])) $item->addChild('barcode', $itemArray['barcode']);
                if(isset($itemArray['retprice'])) $item->addChild('retprice', $itemArray['retprice']);
                if(isset($itemArray['purchprice'])) $item->addChild('purchprice', $itemArray['purchprice']);
                if(isset($itemArray['weight'])) $item->addChild('weight', $itemArray['weight']);
                if(isset($itemArray['length'])) $item->addChild('length', $itemArray['length']);
                if(isset($itemArray['width'])) $item->addChild('width', $itemArray['width']);
                if(isset($itemArray['height'])) $item->addChild('height', $itemArray['height']);
                if(isset($itemArray['CountInPallet'])) $item->addChild('CountInPallet', $itemArray['CountInPallet']);
                if(isset($itemArray['HasSerials'])) $item->addChild('HasSerials', $itemArray['HasSerials']);
                if(isset($itemArray['CountryOfOrigin'])) $item->addChild('CountryOfOrigin', $itemArray['CountryOfOrigin']);
                if(isset($itemArray['Message'])) $item->addChild('Message', $itemArray['Message']);
                if(isset($itemArray['Message2'])) $item->addChild('Message2', $itemArray['Message2']);
                if(isset($itemArray['quantity'])) $item->addChild('quantity', $itemArray['quantity']);
                if(isset($itemArray['reserved'])) $item->addChild('reserved', $itemArray['reserved']);
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
            if($codesearch->article) $result['article'] = strval($codesearch->article);
            if($codesearch->barcode) $result['barcode'] = strval($codesearch->barcode);
            if($codesearch->code) $result['code'] = strval($codesearch->code);
        } else {
            if($conditions = $xml->conditions){
                if($conditions->namecontains) $result['conditions']['namecontains'] = strval($conditions->namecontains);
                if($conditions->namestarts) $result['conditions']['namestarts'] = strval($conditions->namestarts);
                if($conditions->name) $result['conditions']['name'] = strval($conditions->name);
                if($conditions->quantity) $result['conditions']['quantity'] = strval($conditions->quantity);

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