<?php
/**
 *
 * JWT 應用整合 搭配 Socket.IO
 *
 *
 * @another Yu-Hsien, Chou
 */

use Carbon\Carbon;
use Firebase\JWT\JWT;
use PHPSocketIO\Socket;
use PHPSocketIO\SocketIO;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;


class JwtSocketIoController extends CI_Controller
{
    private $jwtKey= 'helloworld';


    public function getjwttoken() {

        $postData = $this->input->post();
        $responceCode = 400;
        if ( strcmp($postData['username'],"admin") == 0 && strcmp($postData['password'],"admin") == 0) {
            $token = [
                "iss" => "http://" . $_SERVER['SERVER_NAME'],
                "aud" => "http://" . $_SERVER['SERVER_NAME'],
                "exp" => Carbon::now()->addHour(5)->timestamp,
                "iat" => Carbon::now()->timestamp,
                "member_id" => 1111
            ];

            $jwtToken = JWT::encode($token,$this->jwtKey);

            $responceCode = 201;
            $responceData = [
                'status' => true,
                'result' => [
                    'jwt_token' => $jwtToken,
                    'expired_timestamp' => $token['exp'],
                    'create_timestamp' => $token['iat']
                ]
            ];
        }else {
            $responceData = [
                'status' => false,
            ];
        }

        $this->output->set_content_type('application/json')
            ->set_status_header($responceCode)
            ->set_output(json_encode($responceData));
    }


    public function start() {
        $socketio = new SocketIO(9025);

        $socketio->on('onConnect',function($connection) {
            /** @var TcpConnection $connection  */

            $controller = & get_instance();

            $connection->httpClientData = [
                'get' => $connection->input->get(),
                'post' => $connection->input->post(),
                'header' => $connection->input->request_headers(true)
            ];
        });

        $socketIoOwmer  = $socketio;

        $socketio->on('connection',function ($socket) use ($socketIoOwmer) {
            /** @var Socket $socket*/
            /** @var SocketIO $socketIoOwmer */

            $socket->on('jwt', function ($messaage) use ($socketIoOwmer) {

                echo "client :" . $messaage . "\n";


                $clientMessage = json_decode(trim($messaage),true);

                $controller =& get_instance();
                $controller->jwtKey;
                $arr = JWT::decode($clientMessage['token'],$controller->jwtKey,['HS256']);
                $socketIoOwmer->emit("systemMessage","Hello world member : ". $arr->member_id);
            });

            echo "";
        });


        Worker::runAll();
    }






}