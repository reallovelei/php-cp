<?php
namespace phpcp\phpserver;
interface InterfaceServer
{
    public function init();
    public function release();
    public function getConnection($timeOut = 3);
    public function gcConn();
    public function after();
}
