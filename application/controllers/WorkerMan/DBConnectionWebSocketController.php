<?php

use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class DBConnectionWebSocketController extends CI_Controller
{

    /**
     * @var Workerman\Worker
     */
    private $worker;

    public function __construct() {
        $this->worker = new Worker('websocket://192.168.0.54:8000/product');
        parent::__construct();
    }

    public function start() {

        $this->worker->onWebSocketConnect = function ($connection , $header) {
            /** @var TcpConnection $connection */
            // validate Client
            $ci =& get_instance();
//            if (strcmp($ci->input->get('key'),'testproduct') !== 0) {
//                $connection->close();
//            }

            $connection->clientData = array (
                'get' => $ci->input->get(),
                'post' => $ci->input->post()
            );

            $connection->send('連線成功');
        };

        $this->worker->onMessage = function ($connection , $data) {
            /** @var TcpConnection $connection */
            $ci = & get_instance();

            if ( $ci->db instanceof CI_DB) {
                $ci->db->reconnect();
            } else {
                $ci->load->database();
            }

            $monolog = new Logger('socket-log');

            $monolog->pushHandler(new StreamHandler(APPPATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . "ws-" . date('Y-m-d'). ".log"));

            $monolog->info($data);
            $clientSocketData = json_decode($data,true);

            if (strcmp($clientSocketData['action'],'product_info') == 0) {
                $ci->load->model('WorkerMan/ProductModel');

                $product = $ci->ProductModel->getProductByPrimaryKey(1);

                $connection->send(json_encode($product));
            }
        };

        $this->worker::runAll();
    }
}