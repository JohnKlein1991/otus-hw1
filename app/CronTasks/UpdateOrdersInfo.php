#!/usr/bin/php
<?php
/**
 * Класс для обнвления некоторых данных о заказах
 * (необходимо для запросов об изменившихся статусах)
 *
 * test http://api.postsrvs.test/app/CronTasks/UpdateOrdersInfo.php
 */

namespace App\CronTasks;

use PDO;

class UpdateOrdersInfo
{
    private $locDB;
    private $milDB;

    public function __construct($conf)
    {
        $locHost = $conf['LOC_DB_HOST'];
        $locUser = $conf['LOC_DB_USER'];
        $locPass = $conf['LOC_DB_PASSWORD'];
        $locDBName = $conf['LOC_DB_NAME'];

        $milHost = $conf['MIL_DB_HOST'];
        $milUser = $conf['MIL_DB_USER'];
        $milPass = $conf['MIL_DB_PASSWORD'];
        $milDBName = $conf['MIL_DB_NAME'];

        $this->locDB = new PDO("mysql:host=$locHost;dbname=$locDBName;charset=utf8;", $locUser, $locPass);
        $this->milDB = new PDO("mysql:host=$milHost;dbname=$milDBName;charset=utf8;", $milUser, $milPass);
    }

    /**
     * Основной метод, который дергает другие. Запускается по крону
     *
     */
    public function run()
    {
        $this->log('------START-------');
        $orders = $this->getAllActiveOrders();
        foreach ($orders as $order) {
            if ($this->needToUpdate($order)) {
                $newData = $this->getNewDataFromMilDB($order);

                if ($newData) {
                    $this->log('Оновленные данные:');
                    $this->log($newData);
                    if($this->updateOrder($order['id'], $newData)) {
                        $this->log('Данные заказа успешно обновлены');
                    } else {
                        $this->log('Не удалось обновить данные');
                    }
                } else {
                    $this->log('Не удалось получить обновленные данные по заказу id=' . $order['id']);
                }
            }
        }
    }

    /**
     * Пишет логи в файл в папку logs
     *
     * @param $data mixed
     */
    private function log($data)
    {
        if(is_array($data)) $data = print_r($data, true);

        $fileName = 'update_orders'.date('Ymd').'.log';
        $filePath = __DIR__ . '/logs/';
        $text = date('Y-m-d H:i:s').' pid:' . getmypid() . ': ' . $data . PHP_EOL;

        $file = fopen($filePath . $fileName, 'a');

        if($file) fwrite($file, $text);
    }

    /**
     * Возвращает все активные(не закрытые) заказы из бд апи
     *
     * @return array
     */
    private function getAllActiveOrders()
    {
        //todo уточнить по статусам (возможно, больше или равно 9)
        $sql = 'SELECT * FROM orders WHERE status NOT IN (9, 12)';
        $result = $this->locDB->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Проверяет, есть ли изменения по данному заказу в БД Милевского
     *
     * @param $order array
     * @return bool
     */
    private function needToUpdate($order)
    {
        $sql = "
            select z.source as client_id,
                   a.StrBarCode as orderno,
                   a.date_put   as delivereddate,
                   a.time_put   as deliveredtime,
                   a.date_putn  as receiver_date,
                   a.address    as receiver_address,
                   a.rur        as price,
                   t.State as status
            from address as a
            left join trace as t on a.code=t.Address
            left join zakaz as z on a.zakaz=z.code
            where a.code=:address_id
                and z.source=:client_id
                and a.StrBarCode=:orderno
                and t.State=:status
                and a.address=:receiver_address
                ".
            ($order['delivereddate'] ? ' and a.date_put=:delivereddate ' : ' and a.date_put is null ').
            ($order['deliveredtime'] ? ' and a.time_put=:deliveredtime ' : ' and a.time_put is null ').
            ($order['receiver_date'] ? ' and a.date_putn=:receiver_date ' : ' and a.date_putn is null ').
            ($order['price'] ? ' and a.rur=:price ' : ' and a.rur is null ');

        $stmt = $this->milDB->prepare($sql);
        $stmt->bindValue(':address_id', $order['address_id']);
        $stmt->bindValue(':client_id', $order['client_id']);
        $stmt->bindValue(':orderno', $order['orderno']);
        $stmt->bindValue(':status', $order['status']);
        $stmt->bindValue(':receiver_address', $order['receiver_address']);

        if($order['delivereddate']) $stmt->bindValue(':delivereddate', $order['delivereddate']);
        if($order['deliveredtime']) $stmt->bindValue(':deliveredtime', $order['deliveredtime']);
        if($order['receiver_date']) $stmt->bindValue(':receiver_date', $order['receiver_date']);
        if($order['price']) $stmt->bindValue(':price', $order['price']);

        $stmt->execute();
        $result = (bool) $stmt->rowCount();
        return !$result;
    }


    /**
     * Возвращает обновленные данные о заказе
     *
     * @param $order array
     * @return bool
     */
    private function getNewDataFromMilDB($order)
    {
        $sql = "
            select z.source as client_id,
                   a.StrBarCode as orderno,
                   a.date_put   as delivereddate,
                   a.time_put   as deliveredtime,
                   a.date_putn  as receiver_date,
                   a.address    as receiver_address,
                   a.rur        as price,
                   t.State as status
            from address as a
            left join trace as t on a.code=t.Address
            left join zakaz as z on a.zakaz=z.code
            where a.code=:address_id";

        $stmt = $this->milDB->prepare($sql);
        $stmt->bindValue(':address_id', $order['address_id']);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Обновляет данные о заказе в БД апи
     *
     * @param $orderId string
     * @param $data array
     * @return bool
     */
    private function updateOrder($orderId, $data)
    {
        $sql = "
            UPDATE orders SET
                status = :status,
                orderno = :orderno,
                delivereddate = :delivereddate,
                deliveredtime = :deliveredtime,
                receiver_date = :receiver_date,
                receiver_address = :receiver_address,
                price = :price,
                is_shown = 0,
                is_committed = 0
            where id = {$orderId}
        ";

        $stmt = $this->locDB->prepare($sql);
        $stmt->bindValue(':status', $data['status']);
        $stmt->bindValue(':orderno', $data['orderno']);
        $stmt->bindValue(':delivereddate', $data['delivereddate']);
        $stmt->bindValue(':deliveredtime', $data['deliveredtime']);
        $stmt->bindValue(':receiver_date', $data['receiver_date']);
        $stmt->bindValue(':receiver_address', $data['receiver_address']);
        $stmt->bindValue(':price', $data['price']);

        $stmt->execute();
        $result = $stmt->rowCount();

        if($result){
            $this->log("Обновленная информация по заказу id:{$orderId}", json_encode($data));
        } else {
            $this->log("UpdateOrdersByCron","Не удалось обновить информацию по сделке id: {$orderId}");
        }
        return $result;
    }
}

require_once __DIR__.'/../../vendor/autoload.php';
$conf = require_once __DIR__.'/config.php';
$sync = new UpdateOrdersInfo($conf);
$sync->run();