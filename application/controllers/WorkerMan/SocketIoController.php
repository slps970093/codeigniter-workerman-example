<?php

use PHPSocketIO\Socket;
use Workerman\Worker;
use PHPSocketIO\SocketIO;

class SocketIoController extends CI_Controller
{
    /**
     * @var SocketIO
     */
    private $socketIo;

    public function __construct() {
        $this->socketIo = new SocketIO(9025);
        parent::__construct();
    }

    public function start() {

        $socketIoOwmer = $this->socketIo;


        $this->socketIo->on('connection',function ($socket) use ($socketIoOwmer) {
            /** @var Socket $socket*/
            /** @var SocketIO $socketIoOwmer */
            echo "new connection coming\n";
            $socket->on('say hello', function ($messaage) use ($socketIoOwmer) {
                $socketIoOwmer->emit("systemMessage","Hello world");
            });

            $socket->on('get product',function ($messaage) use ($socketIoOwmer) {
                $controller = & get_instance();
                $controller->load->model('WorkerMan/ProductModel');

                $productRes = $controller->ProductModel->getProductByPrimaryKey(1);

                $socketIoOwmer->emit('product',json_encode($productRes));
            });

            $socket->on('product num',function ($message) use ($socketIoOwmer) {

                for ($i = 20; $i >= 0; $i--) {
                    sleep(2);
                    $socketIoOwmer->emit('product-num',json_encode(['total' => 20 - (20 - $i)]));
                }
            });

            echo "";
        });
        Worker::runAll();
    }
}