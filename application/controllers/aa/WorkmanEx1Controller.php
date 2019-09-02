<?php

use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class WorkmanEx1Controller extends CI_Controller
{

    /**
     * @var Worker
     */
    private $worker;

    public function __construct()
    {
        $this->worker = new Worker("websocket://127.0.0.1:8080/msg");
    }


    public function start() {
        $this->worker->count = 4;

        $this->worker->onMessage = function ($connection ,$data) {
            /**@var TcpConnection $connection */
            $connection->send("hello world");
        };
        $this->worker::runAll();
    }
}