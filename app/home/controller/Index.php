<?php


namespace app\home\controller;

use GuzzleHttp\Client;
use think\facade\Config;
use think\facade\View;

class Index
{
    protected $baseUrl = 'http://test.paypal.test:88';

    public function index()
    {
        View::assign([
            'client_id'=> Config::get('paypal.client_id'),
            'url_token'  => $this->baseUrl.'/home/index/createOrder',
            'cancel_url'=> $this->baseUrl.'/home/index/cancel',
            'return_url'=> $this->baseUrl.'/home/index/ecValidation'
        ]);
        // 模板输出
        return View::fetch('index');
    }

    public function createOrder()
    {
        $cancel_url = $this->baseUrl.'/home/index/cancel';
        $return_url = $this->baseUrl.'/home/index/ecValidation';
        $paypal = new Paypal();
        $cres = $paypal->createOrder($cancel_url, $return_url);
        $orderId = $cres->result->id;
//        $this->approveOrder($orderId);
        return json(['success'=>true, 'token'=>$orderId]);

    }

    // 捕获交易
    public function capturingOrder($orderId)
    {
        $orderId = input('orderId');
        $paypal = new Paypal();
        $res = $paypal->capturingOrder($orderId);
        //var_dump($res);die;
        return $res;
    }

    // 直接使用服务端实现
    public function approveOrder($orderId)
    {
        $client = new Client();
        $response = $client->request('GET', 'https://www.sandbox.paypal.com/checkoutnow?token='.$orderId);

//        echo $response->getStatusCode(); // 200
//        echo $response->getHeaderLine('content-type'); // 'application/json; charset=utf8'
//        echo $response->getBody(); // '{"id": 1420053, "name": "guzzle", ...}'

        return $response;
    }
    // https://www.sandbox.paypal.com/checkoutnow?token=57F51171VL751693S


    public function ecValidation()
    {
        var_dump(input());
        var_dump("ecValidation");die;
    }

    public function cancel()
    {
        var_dump('cancel');die;
    }

    public function test()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://api.github.com/repos/guzzle/guzzle');

        echo $response->getStatusCode(); // 200
        echo $response->getHeaderLine('content-type'); // 'application/json; charset=utf8'
        echo $response->getBody(); // '{"id": 1420053, "name": "guzzle", ...}'
    }
}

