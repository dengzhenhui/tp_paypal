<?php

namespace app\pay\controller;
use app\BaseController;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use think\facade\Config;
use think\facade\View;

class index extends BaseController
{
    public function index()
    {
        View::assign(
            [
                'client_id' =>Config::get('paypal.client_id'),

            ]
        );
        return View::fetch();
    }

}