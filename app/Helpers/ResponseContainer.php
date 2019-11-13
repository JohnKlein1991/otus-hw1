<?php
/**
 * Контейнер для хранения результатов работы методов апи
 * (нужен для формирования ответа пользователю)
 *
 */

namespace App\Helpers;


use SimpleXMLElement;

class ResponseContainer
{
    private static $mainResponseTag = [];

    private static $attachmentsContainer = [];

    public static $readyResponse;

    /**
     * Добавляет в ответ вложенный элемент с переданными параметрами
     *
     * @param $tagName string
     * @param $tagValue string
     * @param $attributes array
     */
    static public function addAttachment($tagName, $tagValue, $attributes = [])
    {
        $attachment = [
            $tagName => [
                'value' => $tagValue,
                'attr' => $attributes
            ]
        ];
        self::$attachmentsContainer[] = $attachment;
    }

    /**
     * Добавляет корневому элементу ответа атрибут
     *
     * @param $name string
     * @param $value string
     */
    static public function addMainTagAttribute($name, $value)
    {
        self::$mainResponseTag['attributes'][$name] = $value;
    }

    /**
     * Возвращает атрибуты корневого элемента
     *
     * @return array|null
     */
    static public function getMainTagAttributes()
    {
        return self::$mainResponseTag['attributes'] ?? [];
    }

    /**
     * Возвращает данные о вложенных элементах
     *
     * @return array|null
     */
    static public function getAttachments()
    {
        return self::$attachmentsContainer ?? [];
    }

    /**
     * Устанавливает значение корневого элемента
     *
     * @param $value string
     */
    static public function setMainTagValue($value)
    {
        self::$mainResponseTag['value'] = $value;
    }

    /**
     * Возвращает значение корневого элемента
     *
     * @return string|null
     */
    static public function getMainTagValue()
    {
        return self::$mainResponseTag['value'] ?? null;
    }

    /**
     * Устанавливает имя корневого элемента
     *
     * @param $value string
     */
    static public function setMainTagName($value)
    {
        self::$mainResponseTag['name'] = $value;
    }

    /**
     * Возвращает имя корневого элемента
     *
     * @return string|null
     */
    static public function getMainTagName()
    {
        return self::$mainResponseTag['name'] ?? null;
    }

    /**
     * Возвращает готовый ответ в формате xml
     *
     * @return \SimpleXMLElement
     */
    static public function getResponseInXML()
    {
        $mainTagAttr = self::getMainTagAttributes();
        $mainTagName = self::getMainTagName();
        $mainTagValue = self::getMainTagValue();
        $attachments = self::getAttachments();

        $response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><'.$mainTagName.'/>');

        if($mainTagValue) $response[0] = $mainTagValue;

        foreach ($mainTagAttr as $attributeName => $attributeValue){
            $response->addAttribute($attributeName, $attributeValue);
        }

        foreach ($attachments as $item){
            $tagName = key($item);
            $tagValue = $item[$tagName]['value'];
            $attributesArray = $item[$tagName]['attr'];
            $elem = $response->addChild($tagName, $tagValue);
            foreach ($attributesArray as $attributeName => $attributeValue) {
                $elem->addAttribute($attributeName, $attributeValue);
            }
        }
        return $response;
    }
    /**
     * Возвращает готовый ответ в формате xml
     *
     * @return \SimpleXMLElement
     */
    static public function getResponseInXML1()
    {
        $test = [
            'order' => [
                'attr' => [
                    'che' => 'pu',
                    'ke' => 'go'
                ],
                'value' => [

                ]
            ]
        ];
    }
}