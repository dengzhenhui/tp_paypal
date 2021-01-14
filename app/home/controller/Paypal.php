<?php


namespace app\home\controller;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;
use think\facade\Config;


class Paypal
{
    private $client;

    public function __construct()
    {
        // Creating an environment
        if ('sandbox' === Config::get('paypal.environment')) {
            $clientId = Config::get('paypal.sandbox.client_id');
            $clientSecret = Config::get('paypal.sandbox.client_secret');
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        } else {
            $clientId = Config::get('paypal.live.client_id');
            $clientSecret = Config::get('paypal.live.client_secret');
            $environment = new ProductionEnvironment($clientId, $clientSecret);
        }
        $this->client = new PayPalHttpClient($environment);
    }

    public function createOrder()
    {
        // Construct a request object and set desired parameters
        // Here, OrdersCreateRequest() creates a POST request to /v2/checkout/orders
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => "test_ref_id1",
                "amount" => [
                    "value" => "100.00",
                    "currency_code" => "USD"
                ]
            ]],
            "application_context" => [
                "cancel_url" => Config::get('paypal.cancel_url'),
                "return_url" => Config::get('paypal.return_url')
            ]
        ];

        try {
            // Call API with your client and get a response for your call
            $response = $this->client->execute($request);

            // If call returns body in response, you can get the deserialized version from the result attribute of the response
            print_r($response);
        } catch (HttpException $ex) {
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }

    /**
     * 交易关闭 应该返回的页面
     */
    public function cancelOrder()
    {

    }

    /**
     * 同步回到
     */
    public function returnOrder()
    {

    }
}