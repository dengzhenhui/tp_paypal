<?php


namespace app\home\controller;


use think\facade\Cache;
use think\facade\Log;

class Totu
{
    protected $accessKey;
    protected $secretAccessKey;

    protected $appToken;
    protected $merchantId;

    protected $baseUrl = 'https://open.tongtool.com/api-service';

    protected $cacheAppTokenPre = 'tongtool:appToken';

    protected $cacheMerchantIdPre = 'tongtool:merchantId';

    public function __construct()
    {
        $this->accessKey = 'e90d43ff652448a49ddcb37d0c744468';
        $this->secretAccessKey = 'f07600fffc7046a5a4399ac0549741593a9db45b11e745cd9b219d8e4a4ee2e7';

//        $this->accessKey = '82b76df24da14895b21ed5efa80d35b8';
//        $this->secretAccessKey = '096ab7aa62af4b308098c4ada5fb24435382508794c849cdb6f67517793c9b9d';

        $this->getAppToken();
        $this->getPartnerOpenId();
    }

    public function index()
    {
        //

    }

    // step 1
    public function getAppToken()
    {
        if (!Cache::get($this->cacheAppTokenPre)) {
            $url = 'https://open.tongtool.com/open-platform-service/devApp/appToken';
            $param = ['accessKey' => $this->accessKey, 'secretAccessKey' => $this->secretAccessKey];
            $res = $this->httpCurl($url, $param, 'GET', ["Content-Type: application/json"]);
            $resArr = json_decode($res, true);
            // {"success":true,"code":0,"message":"成功!","datas":"02509d05ca4d41206e3cb0d26ba1a3ac","others":null}
            if (isset($resArr['success']) && $resArr['success'] && isset($resArr['code']) && 0 === $resArr['code']) {
                Cache::set($this->cacheAppTokenPre, $resArr['datas'], 7200);
                $this->appToken = $resArr['datas'];
            } else {
                Log::ERROR($res);
            }
        } else {
            $this->appToken = Cache::get($this->cacheAppTokenPre);
        }
    }

    /**
     * 签名
     * @param array $param
     * @return string
     */
    private function sign(array $param)
    {
        if (!empty($param)) {
            $str = '';
            ksort($param);
            foreach ($param as $k => $v) {
                $str .= $k . $v;
            }
            $str = md5($str . $this->secretAccessKey);
            return $str;
        }
        return '';
    }

    // 获取合作伙伴的信息
    public function getPartnerOpenId()
    {
        if (!Cache::get($this->cacheMerchantIdPre)) {
            $url = 'https://open.tongtool.com/open-platform-service/partnerOpenInfo/getAppBuyerList';
            $signParam = ['app_token' => $this->appToken, 'timestamp' => time()];
            $sign = $this->sign($signParam);
            $param = ['app_token' => $this->appToken, 'timestamp' => time(), 'sign' => $sign];
            $res = $this->httpCurl($url, $param, 'GET', ["Content-Type: application/json"]);
            // "{"success":true,"code":0,"message":"成功","datas":[{"tokenId":"330c6f32ea6c4d72982328d9d3328bba","devId":"055094","devAppId":"445646636140462080","accessKey":"e90d43ff652448a49ddcb37d0c744468","appToken":"02509d05ca4d41206e3cb0d26ba1a3ac","appTokenExpireDate":253402214400000,"partnerOpenId":"4856ccfed466565f0e873a2ca4bf2745","partnerName":"","userOpenId":"bf8818d184aead3f7229545cd6728a54","userName":"dzh","buyDate":1600226589000,"price":0.0,"createdDate":1600226589000,"createdBy":"202009120006539252","updatedDate":1600226589000,"updatedBy":"202009120006539252"}],"others":null}"
            $resArr = json_decode($res, true);
            if (isset($resArr['success']) && $resArr['success'] && isset($resArr['code']) && 0 === $resArr['code']) {
                $partnerOpenId = $resArr['datas'][0]['partnerOpenId'];
                Cache::set($this->cacheMerchantIdPre, $partnerOpenId, 7000);
                $this->merchantId = $partnerOpenId;
            } else {
                Log::ERROR($res);
            }
        } else {
            $this->merchantId = Cache::get($this->cacheMerchantIdPre);
        }
    }

    public function createProduct()
    {
        // /openapi/tongtool/createProduct
        ///openapi/tongtool/ordersQuery?app_token=36780dd67049006cf6d86dff0bbeccf0&timestamp=1544166843695&sign=08406606fa2a8479600bb75c39703595
        $productCode = '0923001'; // 商品编号PCL
        $productName = 'test' . time(); // 商品名称
        $goodsSku = 'test000000001';

        $signParam = ['app_token' => $this->appToken, 'timestamp' => time()];
        $sign = $this->sign($signParam);
        $urlF = $this->baseUrl . '/openapi/tongtool/createProduct?app_token=%s&timestamp=%s&sign=%s';
        $url = sprintf($urlF, $this->appToken, $signParam['timestamp'], $sign);
//        $data = [
//            'merchantId' => $this->merchantId,// 商户ID
//            'productCode' => '001', // 商品编号PCL
//            'productName' => 'test' . time(),
//            'productStatus' => 1, // 商品状态；停售：0，在售：1，试卖：2，清仓：4
//            'salesType' => 0, // 销售类型；普通销售：0，变参销售：1；暂不支持其他类型
//        ];
        $data = [
            "accessories" => [ // 配件
                [
                    'accessoriesName' => null,
                    'accessoriesQuantity' => null
                ]
            ],
            'attributes' => [
                [
                    'attributeKey' => null,
                    'attributeValue' => null
                ]
            ],
            'qualityMeasures' => [
                [
                    'itemName' => null,
                    'itemValue' => null,
                    'measureName' => null,
                ]
            ],
            'detailDescriptions' => [
                [
                    'content' => null,
                    'descLanguage' => null,
                    'title' => null,
                ]
            ],
            'suppliers' => [
                [
                    'minPurchaseQuantity' => null,
                    'purchaseRemark' => null,
                    'supplierName' => null
                ]
            ],
            'goods' => [
                [
                    'goodsAverageCost' => 0.1,
                    'goodsCurrentCost' => 0.1,
                    'goodsSku' => $goodsSku,
                    'goodsVariation' => [
                        [
                            'variationName' => '颜色',
                            'variationValue' => '白色',
                        ]
                    ],
                    'goodsWeight' => null
                ]
            ],
            "brandCode" => null,
            "categoryCode" => null,
            "declareCnName" => null,
            "declareEnName" => null,
            "detailImageUrls" => null,
            "developerName" => null,
            "hsCode" => null,
            "imgUrls" => null,
            "inquirerName" => null,
            "merchantId" => $this->merchantId,
            "packageHeight" => null,
            "packageLength" => null,
            "packageMaterial" => null,
            "packageWidth" => null,
            "packagingCost" => null,
            "packagingWeight" => 10,
            "productAverageCost" => null,
            "productCode" => $productCode,
            "productCurrentCost" => null,
            "productFeature" => null,
            "productHeight" => null,
            "productLabelIds" => null,
            "productLength" => null,
            "productName" => $productName,
            "productPackingEnName" => null,
            "productPackingName" => null,
            "productRemark" => null,
            "productStatus" => 1,
            "productWeight" => null,
            "productWidth" => null,
            "purchaserName" => null,
            "salesType" => 0 // 销售类型；普通销售：0，变参销售：1；暂不支持其他类型
        ];
        $res = $this->httpCurl($url, json_encode($data, JSON_UNESCAPED_UNICODE), 'POST', ["Content-Type: application/json", "api_version: 3.0"]);
        return $res;
        // {"code":200,"message":"Success","datas":"{}","others":{"__requestId":"78ffd91c-eebb-4f4b-97ad-cad359ab17b3"}}
        // {"code":524,"message":"58.250.45.16, 172.16.1.208不在白名单列表里.","datas":null,"others":{}}
        // {"code":524,"message":"未授权的请求","datas":null,"others":{}}

    }

    /**
     * 创建订单
     * @return bool|string
     */
    public function createOrder()
    {
        // 渠道编码 platformCode
        // 渠道账号名称 sellerAccountCode
        // 配置这两个 就会识别为私有渠道

        $saleRecordNum = time(); // 订单号
        $sku = '0923001'; // 这里关联的是商品创建里面的 productCode
        $signParam = ['app_token' => $this->appToken, 'timestamp' => time()];
        $sign = $this->sign($signParam);
        $urlF = $this->baseUrl . '/openapi/tongtool/orderImport?app_token=%s&timestamp=%s&sign=%s';
        $url = sprintf($urlF, $this->appToken, $signParam['timestamp'], $sign);
        $data = [
            'addOnExists' => 'N',
            'order' => [
                'buyerInfo' => [
                    'buyerAccount' => 'test',
                    'buyerAddress1' => '河南省商丘市',
                    'buyerAddress2' => 'test address2',
                    'buyerAddress3' => 'test address3',
                    'buyerCity' => '商丘',
                    'buyerCountryCode' => 'CN',
                    'buyerEmail' => '792327182@qq.com',
                    'buyerMobilePhone' => '15012364568',
                    'buyerName' => '张三',
                    'buyerPhone' => '15012364568',
                    'buyerPostalCode' => '510000',
                    'buyerState' => '河南省',
                ],
                'currency' => 'CNY',
                'insuranceIncome' => '0',
                'insuranceIncomeCurrency' => 'CNY',
                'notes' => 'test notes',
                'ordercurrency' => 'CNY',
                'paymentInfos' => [
                    [
                        'orderAmount' => '0.00',
                        'orderAmountCurrency' => 'CNY',
                        'paymentAccount' => 'abc',
                        'paymentDate' => date("Y-m-d H:i:s", strtotime("-1 day")),
                        'paymentNotes' => '无',
                        'paymentTransactionNum' => time(),
                        'recipientAccount' => 'cba',
                        'url' => 'https://www.baidu.com',
                    ],
                ],
                'platformCode' => 'artisul',
                'saleRecordNum' => $saleRecordNum, // *
                'sellerAccountCode' => 'artisul',
                'taxIncome' => '1',
                'taxIncomeCurrency' => 'CNY',
                'totalPrice' => '10',
                'totalPriceCurrency' => '20',
                'transactions' => [
                    [
                        'goodsDetailRemark' => '货品备注',
                        'productsTotalPrice' => '2',
                        'productsTotalPriceCurrency' => 'CNY',
                        'quantity' => '2',
                        'shipType' => '',
                        'shippingFeeIncome' => '2',
                        'shippingFeeIncomeCurrency' => 'CNY',
                        'sku' => $sku,
                    ],
                ],
            ],
            'merchantId' => $this->merchantId,
        ];
        $res = $this->httpCurl($url, json_encode($data, JSON_UNESCAPED_UNICODE), 'POST', ["Content-Type: application/json", "api_version: 3.0"]);
        return $res;
        // {"code":200,"message":"Success","datas":"test-test001","others":{"__requestId":"73fe95aa-f4fd-4f7e-adf3-3e1e1d3e380d"}}

    }

    /**
     * 根据订单查询物流单号
     */
    public function trackingNumberQuery()
    {
        // /openapi/tongtool/trackingNumberQuery
        $signParam = ['app_token' => $this->appToken, 'timestamp' => time()];
        $sign = $this->sign($signParam);
        $urlF = $this->baseUrl . '/openapi/tongtool/trackingNumberQuery?app_token=%s&timestamp=%s&sign=%s';
        $url = sprintf($urlF, $this->appToken, $signParam['timestamp'], $sign);
        $data = [
            "merchantId" => $this->merchantId,
//            "orderIds" => ["artisul-artisul-1600926583","artisul-order-1600849020","artisul-1600854376"],
//            "orderIds" => ["artisul-1600994510"],
            "orderIds" => ["artisul-IGQHWJTTY"],
            "pageNo" => "1",
            "pageSize" => "100"
        ];
        $res = $this->httpCurl($url, json_encode($data, JSON_UNESCAPED_UNICODE), 'POST', ["Content-Type: application/json", "api_version: 3.0"]);
        return $res;
        // {"code":200,"message":"Success","datas":{"array":[]},"others":{"__requestId":"1e2da454-56d0-4582-af44-cb26ca9e8ac9"}}
        // {"code":200,"message":"Success","datas":{"array":[{"shippingMethodCode":"GF","carrierName":"递四方货代(1)","orderId":"artisul-artisul-1600926583","carrierCode":"TT-4PX-1","thirdPartyCode":null,"shippingMethodName":"河南小包挂号","trackingNumber":"3edfsfgsdgv"}]},"others":{"__requestId":"5ab4bfa2-e932-43b3-bfb6-9d34ceff7df5"}}

        // {"code":200,"message":"Success","datas":{"array":[{"shippingMethodCode":"GF","carrierName":"递四方货代(1)","orderId":"artisul-artisul-1600926583","carrierCode":"TT-4PX-1","thirdPartyCode":null,"shippingMethodName":"河南小包挂号","trackingNumber":"3edfsfgsdgv"}]},"others":{"__requestId":"42696a7f-c5b1-403d-83f4-019ad6e0160e"}}

        // {"code":200,"message":"Success","datas":{"array":[{"shippingMethodCode":"GF","carrierName":"递四方货代(1)","orderId":"artisul-IGQHWJTTY","carrierCode":"TT-4PX-1","thirdPartyCode":null,"shippingMethodName":"河南小包挂号","trackingNumber":"20200929qwqewetret"}]},"others":{"__requestId":"ccc642f4-1ed4-4e22-a609-71bfc15d543c"}}
    }

    /**
     * 异步查询商户包裹信息
     */
    public function asyncPackagesQuery()
    {
        $signParam = ['app_token' => $this->appToken, 'timestamp' => time()];
        $sign = $this->sign($signParam);
        $urlF = $this->baseUrl . '/openapi/tongtool/asyncPackagesQuery?app_token=%s&timestamp=%s&sign=%s';
        $url = sprintf($urlF, $this->appToken, $signParam['timestamp'], $sign);
        $data = [
//            "assignTimeFrom" => "2018-01-01 00:00:00",
//            "assignTimeTo" => "2018-02-01 00:00:00",
//            "despatchTimeFrom" => "2018-01-01 00:00:00",
//            "despatchTimeTo" => "2018-02-01 00:00:00",
            "merchantId" => $this->merchantId,
            "orderId" => "artisul-1600854376",
//            "packageStatus" => "waitPrint",
//            "pageNo" => "1",
//            "pageSize" => "100",
//            "shippingMethodName" => "中邮小包"
        ];
        $res = $this->httpCurl($url, json_encode($data, JSON_UNESCAPED_UNICODE), 'POST', ["Content-Type: application/json", "api_version: 3.0"]);
        var_dump($res);
        // {"code":200,"message":"Success","datas":{"taskId":"3491d365d4f14e028c976eeba88eece0"},"others":{"__requestId":"f81d3b42-9578-455b-a763-acff2dd91bdf"}}
    }

    /**
     * 查询异步任务执行结果
     */
    public function asyncDataResultQuery()
    {
        $signParam = ['app_token' => $this->appToken, 'timestamp' => time()];
        $sign = $this->sign($signParam);
        $urlF = $this->baseUrl . '/openapi/tongtool/asyncDataResultQuery?app_token=%s&timestamp=%s&sign=%s';
        $url = sprintf($urlF, $this->appToken, $signParam['timestamp'], $sign);
        $data = [
//            "pageNo" => "1",
            "taskId" => "3491d365d4f14e028c976eeba88eece0"
        ];
        $res = $this->httpCurl($url, json_encode($data, JSON_UNESCAPED_UNICODE), 'POST', ["Content-Type: application/json", "api_version: 3.0"]);
        return $res;
        // "{"code":200,"message":"Success","datas":"/home/data/nas/api-controller/fileupload/3491d365d4f14e028c976eeba88eece0.json0","others":{"__requestId":"8550b02c-054a-4e29-8e7d-f165bcddf658"}}"
    }

    /**
     * 查询异步接口调用任务状态
     */
    public function asyncDataStatusQuery()
    {
        $signParam = ['app_token' => $this->appToken, 'timestamp' => time()];
        $sign = $this->sign($signParam);
        $urlF = $this->baseUrl . '/openapi/tongtool/asyncDataStatusQuery?app_token=%s&timestamp=%s&sign=%s';
        $url = sprintf($urlF, $this->appToken, $signParam['timestamp'], $sign);
        $data = [
            "taskId" => "3491d365d4f14e028c976eeba88eece0"
        ];
        $res = $this->httpCurl($url, json_encode($data, JSON_UNESCAPED_UNICODE), 'POST', ["Content-Type: application/json", "api_version: 3.0"]);
        return $res;
        // {"code":200,"message":"Success","datas":{"taskId":"3491d365d4f14e028c976eeba88eece0","code":"SUCCESS","desc":"task is
        //finished!"},"others":{"__requestId":"93bc7ff0-d1c0-4a74-82d8-6c7fa613385a"}}
    }



    public function httpCurl($url, $params, $method = 'POST', $header = array())
    {
        date_default_timezone_set('PRC');
        $opts = array(
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_COOKIESESSION => true,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_COOKIE => session_name() . '=' . session_id(),
        );
        /* 根据请求类型设置特定参数 */
        switch (strtoupper($method)) {
            case 'GET':
                // 链接后拼接参数  &  非？
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new Exception('不支持的请求方式！');
        }
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) {
            throw new Exception('请求发生错误：' . $error);
        }
        return $data;
    }

    public function callback()
    {
        //var_dump(2222);die;
        $jsonStr = file_get_contents("php://input");
        Log::INFO($jsonStr);
        // http://7376c022a7b5.ngrok.io
    }
}