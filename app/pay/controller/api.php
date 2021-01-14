<?php

namespace app\pay\controller;

use app\BaseController;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use think\facade\Config;
use think\facade\Log;
use think\facade\View;

/**
 * 使用后台发送请求
 * Class api
 * @package app\pay\controller
 */
class api extends BaseController
{
    public function index()
    {
        View::assign(
            [
                'client_id' => Config::get('paypal.client_id'),
            ]
        );
        return View::fetch();
    }


//您可能需要修改必需和可选的HTTP请求标头才能进行集成。
//https://developer.paypal.com/docs/api/reference/api-requests/#http-request-headers
    //$request = new OrdersCreateRequest();
    //$request->headers["prefer"] = "return=representation";
    //传递合作伙伴归因ID
    public function createOrder()
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = self::buildRequestBody();
        // 3. Call PayPal to set up a transaction
        $client = PayPalClient::client();
        //var_dump($request);die;
        $response = $client->execute($request);
        /*if ($debug)
        {
            print "Status Code: {$response->statusCode}\n";
            print "Status: {$response->result->status}\n";
            print "Order ID: {$response->result->id}\n";
            print "Intent: {$response->result->intent}\n";
            print "Links:\n";
            foreach($response->result->links as $link)
            {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }

            // To print the whole response body, uncomment the following line
            // echo json_encode($response->result, JSON_PRETTY_PRINT);
        }*/
        // 4. Return a successful response to the client.
        //return $response;
        if (201 === $response->statusCode) {
            return json(['orderID' => $response->result->id]);
        }
    }

    private function buildRequestBody()
    {
        return [
            'intent' => 'CAPTURE', // required
            'payer' => [
                'name' => [
                    'given_name' => 'd',
                    'surname' => 'zh'
                ],
                'email_address' => '792327182@qq.com',
            ],
            'application_context' => [  // 参考 https://developer.paypal.com/docs/api/orders/v2#definition-order_application_context
                'return_url' => 'http://test.paypal.test:88/pay/api/success', // 客户批准付款后将客户重定向到的URL。
                'cancel_url' => 'http://test.paypal.test:88/pay/api/cancel', // 客户取消付款后，将客户重定向到的URL。
                'brand_name' => 'EXAMPLE INC',//该标签将覆盖PayPal网站上PayPal帐户中的公司名称。
                //'locale' => 'en-US', //贝宝付款体验显示的BCP 47格式的页面区域设置。PayPal支持五个字符的代码。例如，da-DK，he-IL，id-ID，ja-JP，no-NO，pt-BR，ru-RU，sv-SE，th-TH，zh-CN，zh-HK，或zh-TW。
                //      LOGIN。当客户点击PayPal Checkout时，客户将被重定向到一个页面以登录PayPal并批准付款。
                //      BILLING。当客户单击PayPal Checkout时，客户将被重定向到一个页面，以输入信用卡或借记卡以及完成购买所需的其他相关账单信息。
                //      NO_PREFERENCE。当客户单击“ PayPal Checkout”时，将根据其先前的交互方式将其重定向到页面以登录PayPal并批准付款，或重定向到页面以输入信用卡或借记卡以及完成购买所需的其他相关账单信息使用PayPal。
                //'landing_page' => 'LOGIN',
                //      GET_FROM_FILE。使用贝宝网站上客户提供的送货地址。
                //      NO_SHIPPING。从PayPal网站编辑送货地址。推荐用于数字商品。
                //      SET_PROVIDED_ADDRESS。使用商家提供的地址。客户无法在PayPal网站上更改此地址。
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                'user_action' => 'PAY_NOW', // 配置继续或立即付款结帐流程。
            ],
            'purchase_units' => [ // 参考 https://developer.paypal.com/docs/api/orders/v2#definition-purchase_unit_request
                [
                    'reference_id' => 'ruby里面存的是支付ID', // API调用者为购买单位提供的外部ID。必须通过来更新订单时，多个购买单位必填PATCH。如果您忽略此值，并且订单仅包含一个购买单位，则PayPal将此值设置为default。
                    'description' => 'Sporting Goods', // 购买说明。
                    'custom_id' => 'CUST-HighFashions', // API调用者提供的外部ID。用于协调客户交易与PayPal交易。显示在交易和结算报告中，但对付款人不可见。
                    //'soft_descriptor' => 'HighFashions', // 软描述符是用于构造显示在付款人卡对帐单上的对帐单描述符的动态文本。
                    //总订单金额，并提供可选明细，可提供详细信息，例如总项目金额，总税额，运输，装卸，保险和折扣（如果有）。
                    //如果指定amount.breakdown，金额等于item_total加上tax_total加shipping加上handling加insurance减shipping_discount减优惠。
                    //金额必须为正数。有关支持的货币和小数精度的列表，请参阅PayPal REST API货币代码。
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => '30.00', //
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ],
                            'shipping' => [
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ],
                            'handling' => [
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ],
                            'tax_total' => [
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ],
                            'shipping_discount' => [
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ],
                        ],
                    ],
                    'items' => [
                        [
                            'name' => 'T-Shirt', // required
                            'description' => 'Green XL', // required
                            'sku' => 'sku01',
                            // 商品价格或单价。如果指定unit_amount，purchase_units[].amount.breakdown.item_total则为必填。
                            // unit_amount * quantity所有项目必须相等。unit_amount.value不能为负数。
                            'unit_amount' => [ // required
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ],
                            // 每个单位的商品税。如果tax指定，purchase_units[].amount.breakdown.tax_total则为必填。
                            // tax * quantity所有项目必须相等。tax.value不能为负数。
                            'tax' => [
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ],
                            'quantity' => '1', // required
                            'category' => 'PHYSICAL_GOODS', //或者 DIGITAL_GOODS
                        ],
                        /*[
                            'name' => 'Shoes',
                            'description' => 'Running, Size 10.5',
                            'sku' => 'sku02',
                            'unit_amount' => [
                                'currency_code' => 'USD',
                                'value' => '45.00',
                            ],
                            'tax' => [
                                'currency_code' => 'USD',
                                'value' => '5.00',
                            ],
                            'quantity' => '2',
                            'category' => 'PHYSICAL_GOODS',
                        ],*/
                    ],
                    'shipping' => [
                        //'method' => 'United States Postal Service',
                        'address' => [
                            'address_line_1' => '123 Townsend St',
                            'address_line_2' => 'Floor 6',
                            'admin_area_2' => 'San Francisco',
                            'admin_area_1' => 'CA',
                            'postal_code' => '94107',
                            'country_code' => 'US',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * 送货地址变化后
     * 客户端会传data数据给后台
     *
     * 在以下情况下，贝宝会调用您的onShippingChange函数：
     *
     * 买家最初登录其帐户（如果买家已经拥有PayPal帐户）。
     * 买方提交其帐单/付款信息（如果买方没有PayPal帐户）。
     * 买家在查看您的付款页面上更改了他们的收货地址。
     */
    public function shippingChange()
    {
        $input = input();
        //贝宝为您的onShippingChange功能提供以下参数：
        //
        //data：包含买方送货地址的对象。由以下字段组成：
        //
        //orderID：ID代表订单。
        //paymentID：ID代表付款。
        //paymentToken：代表资源的ID /令牌。
        //shipping_address：买家选择的城市，州和邮政编码。
        //selected_shipping_option：买方选择的送货方式。
        //actions：一个对象，其中包含更新买家购物车中的内容并与PayPal Checkout交互的方法。包括以下方法：
        //
        //resolve：向PayPal指示您不需要对购物车进行任何更改。
        //reject：向PayPal表示您将不支持买家提供的送货地址。
        //order：客户端订单API方法：
        //
        //PATCH：要进行更新，请在请求中传递一系列更改操作，如 订单更新API参考。响应返回一个承诺。
        Log::INFO(date('Y-m-d H:i:s') . 'shippingChange' . json_encode($input));
        return json(['code' => 0]);
    }

    public function success()
    {
        return View::fetch();
    }

    public function cancel()
    {
        return View::fetch();
    }

    // 捕获交易
    public function captureOrder()
    {
        $orderId = input('orderID');
        $request = new OrdersCaptureRequest($orderId);
        // 3. Call PayPal to capture an authorization
        $client = PayPalClient::client();
        $response = $client->execute($request);
        // 4. Save the capture ID to your database. Implement logic to save capture to your database for future reference.
//        if ($debug)
//        {
//            print "Status Code: {$response->statusCode}\n";
//            print "Status: {$response->result->status}\n";
//            print "Order ID: {$response->result->id}\n";
//            print "Links:\n";
//            foreach($response->result->links as $link)
//            {
//                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
//            }
//            print "Capture Ids:\n";
//            foreach($response->result->purchase_units as $purchase_unit)
//            {
//                foreach($purchase_unit->payments->captures as $capture)
//                {
//                    print "\t{$capture->id}";
//                }
//            }
//            // To print the whole response body, uncomment the following line
//            // echo json_encode($response->result, JSON_PRETTY_PRINT);
//        }
        return json($response->result);
        //return $response;
    }
}