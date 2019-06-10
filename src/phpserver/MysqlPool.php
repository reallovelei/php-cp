<?php
namespace phpcp\phpserver;

//include "AbstractServer.php";

class MysqlPool extends AbstractServer
{
    protected $type = 'mysql'; // 种类 如:redis/mysql/mongo/elasticsearch 等

    public static $instance;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new MysqlPool();
        }
        return self::$instance;
    }

    protected function createDb($config)
    {
        $db = new \Swoole\Coroutine\Mysql();
        $db->connect($config);
        return $db;
    }
}

