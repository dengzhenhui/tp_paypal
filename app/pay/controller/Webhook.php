<?php

namespace app\pay\controller;
use app\BaseController;

class Webhook extends BaseController
{
    // sb-qgvue3198857@personal.example.com

    //https://9fb30d6229d1.ngrok.io/pay/Webhook/index

    public function index()
    {
        
        $all = $_SERVER;

        $filename1 = './test.log';
        $this->writeLog($filename1, json_encode($all));

        $input = file_get_contents("php://input");
        // if event[:resource_type] == 'refund' || event[:resource_type] == 'capture'
        $filename = './' . date('Ymd') . '.log';
        $this->writeLog($filename, $input);
    }

    public function writeLog($filename,$input)
    {
        $f = fopen($filename, 'a+');
        fwrite($f, "\n");
        fwrite($f, $input);
        fclose($f);
    }

}