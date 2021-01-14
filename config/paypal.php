<?php


return [
    // app name test
    // 'client_id' => 'AVCO2DYTtuaC6AHfGEtuVxuOQOWxnMpm5Rg8rvFHKWPgXPmCjPlBIxBDJyZd1ez9FXchOhTAIjyONWiY',
    // 'client_secret'=>'EK_mchtXVV5gAxMsi9mu9mYSrz_c-EecjteJKv2OkCC5AGahq_t4e1Dc0BORFElzAGYogQ9okCRVHcJ7',

    // 'client_id' => 'Afywo0dusL4VtltplfEV6-giTBl3y3NB6RpB43rFbyF9rGjIKvPve28HDgVhikbJxtyx_CmXac3-DxKO',
    // 'client_secret' => 'EDIvDcj-cmY33a2GmDLJdLsEVKhYDZNlgedXAE4NkQO97Eo0voFtrjzAN-IMzGQbfk8IoTtfpSys4cyS',

    'environment' => 'sandbox',
    // 新建的 local-artisul
    'sandbox' => [
        'client_id' => 'AeBcyxA8qOO87ncv_s3qGUqD6Bh2YJ4yGf-BmytFGtHqOFcLvKZrwczQicwHrUneC1JsnX3dZoi0ZaTG',
        'client_secret' => 'EPQR62mGRsW4Y2pkn0tREsCM-OEKjRGdHDR6qWrI-cfmIZRfTHvgoEf0ro19MZ1_2xxSn86nDBfYoyJM',
    ],
    'live' => [
        'client_id' => 'AeBcyxA8qOO87ncv_s3qGUqD6Bh2YJ4yGf-BmytFGtHqOFcLvKZrwczQicwHrUneC1JsnX3dZoi0ZaTG',
        'client_secret' => 'EPQR62mGRsW4Y2pkn0tREsCM-OEKjRGdHDR6qWrI-cfmIZRfTHvgoEf0ro19MZ1_2xxSn86nDBfYoyJM',
    ],
    'cancel_url' => '/Home/Paypal/cancelOrder',
    'return_url' => '/Home/Paypal/returnOrder'

];
