<?php

namespace app\pay\controller;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use think\facade\Config;

ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

class PaypalClient
{
    /**
     * Returns PayPal HTTP client instance with environment that has access
     * credentials context. Use this instance to invoke PayPal APIs, provided the
     * credentials have access.
     */
    public static function client($env='sanbox')
    {
        return new PayPalHttpClient(self::environment($env));
    }

    /**
     * Set up and return PayPal PHP SDK environment with PayPal access credentials.
     * This sample uses SandboxEnvironment. In production, use LiveEnvironment.
     */
    public static function environment($env)
    {
        if ('sanbox' === $env) {
            $clientId = Config::get('paypal.client_id');
            $clientSecret = Config::get('paypal.client_secret');
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        }else {
            $clientId = Config::get('paypal.client_id');
            $clientSecret = Config::get('paypal.client_secret');
            $environment = new ProductionEnvironment($clientId, $clientSecret);
        }
        return $environment;
    }
}