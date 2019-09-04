<?php

use Workerman\Worker;
use Workerman\Connection\TcpConnection;

class HelloWebSocketController extends CI_Controller
{

    /**
     * @var Workerman\Worker
     */
    private $worker;

    public function __construct() {
        $this->worker = new Worker("websocket://127.0.0.1:8000");
    }

    public function start() {

        $this->worker->onWebSocketConnect = function ($connection) {
            /** @var $connection TcpConnection */

            $connection->urlGetData = $_GET;
        };

        $this->worker->onConnect = function ($connection) {
            /** @var $connection TcpConnection */
            $connection->urlGetData = $_GET;
        };

        $this->worker->onMessage = function ($connection,$data) {
            /** @var $connection TcpConnection */
            $connection->send('hello ' . $data);
        };

        $this->worker::runAll();
    }
}