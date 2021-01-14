<?php

namespace app\home\controller;

use PayPalHttp\HttpException;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Sample\PayPalClient;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use think\facade\Config;

class Paypal
{
    private $client;

    public function __construct()
    {
        // Creating an environment
        $clientId = Config::get('paypal.client_id');
        $clientSecret = Config::get('paypal.client_secret');
        $environment = new SandboxEnvironment($clientId, $clientSecret);
        $this->client = new PayPalHttpClient($environment);
    }

    public function createOrder($cancel_url, $return_url)
    {
        $request = new OrdersCreateRequest();
        //$request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "amount" => [
                    "value" => "1.00",
                    "currency_code" => "USD"
                ]
            ]],
            "application_context" => [
                "cancel_url" => $cancel_url,
                "return_url" => $return_url
            ]
        ];
        try {
            $response = $this->client->execute($request);
            return $response;
        } catch (HttpException $ex) {
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }

    public function capturingOrder($orderId)
    {
        $request = new OrdersCaptureRequest($orderId);
        $request->prefer('return=representation');
        try {
            // Call API with your client and get a response for your call
            $response = $this->client->execute($request);
            // If call returns body in response, you can get the deserialized version from the result attribute of the response
            return $response;
        } catch (HttpException $ex) {
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }
}