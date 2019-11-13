#!/usr/bin/php
<?php
/**
 * Синхрозатор для таблиц clients в нашей бд и милевского
 * test http://api.postsrvs.test/app/CronTasks/ClientsSynchronizer.php
 */

class ClientsSynchronizer
{
    private $locDB;
    private $milDB;
    private $tableName = 'clients';

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

    public function run()
    {
        $this->log('------START-------');
        try {
            $lastLocalId = $this->getLastId($this->locDB);
            $lastMilId = $this->getLastId($this->milDB);
            if($lastLocalId < $lastMilId){
                $newData = $this->getDataFromMilDB($lastLocalId);
                $result = $this->insertDataIntoLocalDB($newData);
            }
        } catch (Exception $e) {
            $this->log("Ошибка: ".$e->getMessage());
        }
    }


    /**
     * Пишет логи в файл в папке logs
     *
     * @param $data mixed
     */
    private function log($data)
    {
        if(is_array($data)) $data = print_r($data, true);

        $fileName = 'client_sync'.date('Ymd').'.log';
        $filePath = __DIR__ . '/logs/';
        $text = date('Y-m-d H:i:s').' pid:' . getmypid() . ': ' . $data . PHP_EOL;

        $file = fopen($filePath . $fileName, 'a');

        if($file) fwrite($file, $text);
    }

    /**
     * получает последний PK из заданной таблицы
     *
     * @param $db PDO
     * @return string
     */
    public function getLastId($db)
    {
        $sql = 'SELECT max(code) as last_id from '.$this->tableName;
        $result = (integer) $db->query($sql)->fetch()['last_id'];
        return $result;
    }

    /**
     * Получает необходимые данные из БД милевского
     *
     * @param $id string
     * @return array
     */
    private function getDataFromMilDB($id)
    {
        $sql = "SELECT code, company, login, pass 
                FROM clients
                WHERE code>{$id}
                ";
        $result = $this->milDB->query($sql)->fetchAll();
        return $result;
    }

    /**
     * Записывает новые данные в нашу БД
     *
     * @param $data array
     * @return bool
     */
    private function insertDataIntoLocalDB($data)
    {
        $sql = "INSERT INTO clients
                (code, company, login, pass, hash)
                VALUES ";
        foreach ($data as $row){
            $passwordHash = '';
            if($row['pass']) {
                $passwordHash = password_hash($row['pass'], PASSWORD_BCRYPT);
            }
            $sql .= "({$this->locDB->quote($row['code'])}, {$this->locDB->quote($row['company'])}, 
                    {$this->locDB->quote($row['login'])}, {$this->locDB->quote($row['pass'])},
                    {$this->locDB->quote($passwordHash)}),";
        }
        $sql = substr($sql, 0, -1);
        $result = $this->locDB->exec($sql);
        return $result;
    }
}

$conf = require_once __DIR__.'/config.php';
$sync = new ClientsSynchronizer($conf);
$sync->run();