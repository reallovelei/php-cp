<?php
namespace phpcp\phpserver;

$autoload = require_once dirname(dirname(__DIR__)).'/vendor/autoload.php';

var_dump($autoload); //die;
//echo dirname(dirname(__DIR__)).'/vendor/autoload.php';
//include "MysqlPool.php";

class CpServer {

    function start() {
        $serv = new \swoole_server('0.0.0.0', 9527, SWOOLE_BASE, SWOOLE_SOCK_TCP);
        $serv->set(array(
            'worker_num' => 1,
            'daemonize' => false,
            'backlog' => 128,
            //'open_eof_split' => true, //打开 EOF_SPLIT检测
            //'open_eof_check' => true, //打开 EOF检测
            //'package_eof' => "\r\n\r\n", //设置EOF
            //'dispatch_mode' => 5,
        ));

        $serv->on('connect', function (\swoole_server $server, $fd, $from_id) {
            echo "server: connect with fd:{$fd} from_id:{$from_id} \n";
            $time=date("Y-m-d H:i:s");
            echo "connect:connecttime={$time}\n";
        });

        $serv->on('WorkerStart', function (\swoole_server $serv, $worker_id) {
            echo "{$worker_id} onWorkerStart \n";
            MysqlPool::getInstance()->init();

            //$this->getCondition($worker_id);
            /*
            // 定时加载最新的 配置
            \Swoole\Timer::tick($this->ts, function() use ($worker_id) {
                $this->getCondition($worker_id);
            });
            */
        });

        $serv->on('close', function (\swoole_server $serv, $fd) {
            $time=date("Y-m-d H:i:s") ;
            echo "{$fd} onclose,closetime= {$time} \n";
        });

        $serv->on('receive', function (\swoole_server $serv, $fd, $reactor_id, $sqlstr) {
            echo "\nworker:{$serv->worker_id}  fd:{$fd} server receive data:{$sqlstr}";
            $sqlstr = trim($sqlstr, "\r\n");
            $conn = MysqlPool::getInstance()->getConnection();
            var_dump($conn);
        });
        $serv->start();
    }
}


$o = new CpServer();
$o->start();

