<?php

namespace app\pay\controller;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

class PaypalOrder
{

// 2. Set up your server to receive a call from the client
    /**
     *This is the sample function to create an order. It uses the
     *JSON body returned by buildRequestBody() to create an order.
     */
    public static function createOrder($debug=false)
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = self::buildRequestBody();
        // 3. Call PayPal to set up a transaction
        $client = PayPalClient::client();
        $response = $client->execute($request);
        if ($debug)
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
        }

        // 4. Return a successful response to the client.
        return $response;
    }

    /**
     * Setting up the JSON request body for creating the order with minimum request body. The intent in the
     * request body should be "AUTHORIZE" for authorize intent flow.
     *
     */
    private static function buildRequestBody()
    {
        return array(
            'intent' => 'CAPTURE',
            'application_context' =>
                array(
                    'return_url' => 'https://example.com/return',
                    'cancel_url' => 'https://example.com/cancel'
                ),
            'purchase_units' =>
                array(
                    0 =>
                        array(
                            'amount' =>
                                array(
                                    'currency_code' => 'USD',
                                    'value' => '1.00'
                                )
                        )
                )
        );
    }
}
