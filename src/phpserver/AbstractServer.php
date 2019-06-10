<?php
namespace phpcp\phpserver;

use Swoole\Coroutine as co;
abstract class AbstractServer {
    protected $type = 'mysql'; // 种类  默认为mysql

    protected $count;      //当前连接数
    protected $connections;// 连接池组
    protected $idleTime;   // 用于空闲连接回收判断

    //数据库配置
    protected $config = array(
    );

    private $inited = false;

    protected abstract function createDb($config);

    public function __construct()
    {
    }

    protected function createObject($cnf)
    {
        $obj = null;
        $db = $this->createDb($cnf);
        if ($db) {
            $obj = [
                'last_used_time' => time(),
                'db' => $db,
            ];
        }
        return $obj;
    }

    /**
     * init
     * 初始化最小数量连接池
     *
     * @access public
     * @author nemolzhang <nemolzhang@tencent.com>
     * @time 2019-06-08
     * @return void
     */
    public function init()
    {
        $this->configs = include "config.php";
        if (isset($this->configs[$this->type])) {
            $cnfs = $this->configs[$this->type];
            foreach ($cnfs as $key => $cnf) {
                $this->connections[$key] = new co\Channel($cnf['max'] + 1);

                for ($i = 0; $i < $cnf['min']; $i++) {
                    $obj = $this->createObject($cnf);
                    if (isset($this->count[$key])) {
                        $this->count[$key]++;
                    } else {
                        $this->count[$key] = 1;
                    }
                    $this->connections[$key]->push($obj);
                }
            }
        }
        var_dump($this->count);
        return $this;
    }

    /**
     * getConnection
     * 获得指定实例的连接
     *
     * @param string $key
     * @param int $timeOut
     * @access public
     * @author nemolzhang <nemolzhang@tencent.com>
     * @time 2019-06-08
     * @return void
     */
    public function getConnection($key, $timeOut = 3)
    {
        $obj = null;
        if ($this->connections[$key]->isEmpty()) {
            if ($this->count < $this->max) {//连接数没达到最大，新建连接入池
                $this->count++;
                $obj = $this->createObject();#1
            } else {
                $obj = $this->connections[$key]->pop($timeOut);#2
            }
        } else {
            $obj = $this->connections[$key]->pop($timeOut);#3
        }
        return $obj;
    }

    /**
     * release
     * 将连接放回连接池
     * @param mixed $key 标记具体 哪个实例的链接
     * @param mixed $obj 链接对象
     * @access public
     * @author nemolzhang <nemolzhang@tencent.com>
     * @time 2019-06-08
     * @return void
     */
    public function release($key, $obj)
    {
        if ($obj) {
            $this->connections[$key]->push($obj);
        }
    }

    /**
     * 处理空闲连接
     */
    public function gcIdle()
    {
        //大约5秒检测一次连接  这尼玛只能固定时间了
        swoole_timer_tick(50000, function () {
            $list = [];
            unset($list);
        });
    }
}
