<?php


namespace app\controller;

use think\facade\Db;
use think\facade\Log;

/*********************学生管理系统数据定时任务***************************************/
class StudentsCron
{
    public function http_curl($url, $type = 'get', $res = 'json', $arr = '')
    {
        //1.初始化curl
        $ch = curl_init();
        //2.设置curl的参数
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }
        //3.采集
        $output = curl_exec($ch);
        //4.关闭
        curl_close($ch);
        if ($res == 'json') {
            return json_decode($output, true);
        }
        return [];
    }

    public function startStudentsCron()
    {
        var_dump(11);die;

        set_time_limit(0);
        // 读取文件记录的时间 存在就用那个时间开始，没有 就是第一次运行
        $start = $this->read();
        if ($start) {
            $startYmd = $start;
        }else {
            $startYmd = date("Y-m-d", strtotime("+1 day", strtotime("-1 year")));
        }
        if (strtotime("+30 day", strtotime($startYmd)) >= strtotime(date('Y-m-d',time()))) {
            $endYmd = date("Y-m-d",time());
        }else {
            $endYmd = date("Y-m-d", strtotime("+30 day", strtotime($startYmd)));
        }
        $this->getCanada($startYmd,$endYmd);
        $this->getUnitedKingdom($startYmd,$endYmd);
        $this->getUnitedStates($startYmd,$endYmd);
        $this->getJapan($startYmd,$endYmd);

    }

    public function read() {
        $filename = "./start_students_time.txt";
        if (file_exists($filename) && filesize ($filename)) {
            $handle = fopen($filename, "r");//读取二进制文件时，需要将第二个参数设置成'rb'
            //通过filesize获得文件大小，将整个文件一下子读到一个字符串中
            $contents = fread($handle, filesize ($filename));
            fclose($handle);
            return $contents;
        }else {
            $handle = fopen($filename,'w');
//            chown($filename,0755);// 这个函数在linux上被禁用了
            fclose($handle);
            return '';
        }
    }

    public function getCanada($startYmd,$endYmd)
    {
        $access_token = '40a1c9a72fdf2948e29ac87331cf5a88bcfaafd06fb069867dfb00b28ea8d1f0';
        $brand_uid = '5e1b2ffd-a50c-4720-8b37-abbfffe90f5f';

        $formatUrl = 'https://api.studentbeans.com/offers/api/v1/data/leads?access_token=%s&brand_uid=%s&start_date=%s&end_date=%s';
        while (true) {
            $url = sprintf($formatUrl, $access_token, $brand_uid, $startYmd, $endYmd);
            $res = $this->http_curl($url);
            if (empty($res) || isset($res['error'])){
                Log::error($startYmd.'---'.$endYmd.'--getCanada-' . json_encode($res));
            }else {
                $this->insertStudentsData($res, 'canada_students');
            }
            $startYmd = $endYmd;
            // 如果结束时间大于等于今天 那么结束时间就是今天
            if (strtotime("+30 day", strtotime($startYmd)) >= time()) {
                $endYmd = date("Y-m-d",time());
            }else {
                $endYmd = date("Y-m-d", strtotime("+30 day", strtotime($startYmd)));
            }
            $this->append('./start_students_time.txt', $endYmd, 'w');
            if (strtotime(date('Y-m-d',strtotime($startYmd))) >= strtotime(date('Y-m-d',time()))) break;
        }
    }

    public function getUnitedKingdom($startYmd,$endYmd)
    {
        $access_token = '196a182600342c64a9c11f80d678d2cc270dc2f905e11dda41ff8eb2b24031ba';
        $brand_uid = '7dbcd7d3-d3b8-41b5-9495-fe6888ab4c9b';

        $formatUrl = 'https://api.studentbeans.com/offers/api/v1/data/leads?access_token=%s&brand_uid=%s&start_date=%s&end_date=%s';
        while (true) {
            $url = sprintf($formatUrl, $access_token, $brand_uid, $startYmd, $endYmd);
            $res = $this->http_curl($url);
            if (empty($res) || isset($res['error'])){
                Log::error($startYmd.'---'.$endYmd.'--getUnitedKingdom-' . json_encode($res));
            }else {
                $this->insertStudentsData($res, 'united_kingdom_students');
            }
            $startYmd = $endYmd;
            if (strtotime("+30 day", strtotime($startYmd)) >= time()) {
                $endYmd = date("Y-m-d",time());
            }else {
                $endYmd = date("Y-m-d", strtotime("+30 day", strtotime($startYmd)));
            }
            $this->append('./start_students_time.txt', $endYmd, 'w');
            if (strtotime(date('Y-m-d',strtotime($startYmd))) >= strtotime(date('Y-m-d',time()))) break;
        }
    }

    public function getUnitedStates($startYmd, $endYmd)
    {
        $access_token = '96cbd31ac74072d93a51f22bdc5c43dd5d941861404054a84a400fc91e3d0c78';
        $brand_uid = '4f53caf0-d0b1-41ec-ae01-f1df7b79596d';

        $formatUrl = 'https://api.studentbeans.com/offers/api/v1/data/leads?access_token=%s&brand_uid=%s&start_date=%s&end_date=%s';
        while (true) {
            $url = sprintf($formatUrl, $access_token, $brand_uid, $startYmd, $endYmd);
            $res = $this->http_curl($url);
            if (empty($res) || isset($res['error'])){
                Log::error($startYmd.'---'.$endYmd.'--getUnitedStates-' . json_encode($res));
            }else {
                $this->insertStudentsData($res, 'united_states_students');
            }
            $startYmd = $endYmd;
            if (strtotime("+30 day", strtotime($startYmd)) >= time()) {
                $endYmd = date("Y-m-d",time());
            }else {
                $endYmd = date("Y-m-d", strtotime("+30 day", strtotime($startYmd)));
            }
            $this->append('./start_students_time.txt', $endYmd, 'w');
            if (strtotime(date('Y-m-d',strtotime($startYmd))) >= strtotime(date('Y-m-d',time()))) break;
        }
    }

    public function getJapan($startYmd, $endYmd)
    {
        $access_token = '40dcd18f91df03f4a15cdc94aefa814a144508919741ff6aa0aaa460c90a0da0';
        $brand_uid = 'c984215f-7c4f-4bc3-9799-65cad52a002d';

        $formatUrl = 'https://api.studentbeans.com/offers/api/v1/data/leads?access_token=%s&brand_uid=%s&start_date=%s&end_date=%s';
        while (true) {
            $url = sprintf($formatUrl, $access_token, $brand_uid, $startYmd, $endYmd);
            $res = $this->http_curl($url);

            if (empty($res) || isset($res['error'])){
                Log::error($startYmd.'---'.$endYmd.'--getJapan-' . json_encode($res));
            }else {
                $this->insertStudentsData($res, 'japan_students');
            }
            $startYmd = $endYmd;
            if (strtotime("+30 day", strtotime($startYmd)) >= time()) {
                $endYmd = date("Y-m-d",time());
            }else {
                $endYmd = date("Y-m-d", strtotime("+30 day", strtotime($startYmd)));
            }
            $this->append('./start_students_time.txt', $endYmd, 'w');
            if (strtotime(date('Y-m-d',strtotime($startYmd))) >= strtotime(date('Y-m-d',time()))) break;
        }
    }

    public function append($filename, $data, $mode='a+')
    {
        $myfile = fopen($filename, $mode) or die("Unable to open file!");
        $data = $data . "\r\n";
        fwrite($myfile, $data);
        //记得关闭流
        fclose($myfile);
    }


    public function insertStudentsData($data, $tableName)
    {
        $this->append($tableName . '.txt', json_encode($data));
        date_default_timezone_set('GMT');
        $newData = [];
        foreach ($data as $k => $v) {
            $id = Db::table($tableName)->field('id')->where(['email' => $v['email']])->find();
            if (!$id) {
                $newData[] = [
                    'email' => $v['email'],
                    'name' => $v['name'],
                    'gender' => $v['gender'],
                    'date_of_birth' => $v['date_of_birth'],
                    'country' => $v['country'],
                    'university' => $v['university'],
                    'grad_year' => $v['grad_year'],
                    'marketing_consent' => $v['marketing_consent'],
                    'marketing_consent_updated_at' => $v['marketing_consent_updated_at'],
                    'record_created_at' => date('Y-m-d H:i:s', strtotime($v['record_created_at'])),
                    'create_time' => date('Y-m-d H:i:s',time())
                ];
            }
        }
        if (!empty($newData)) {
            Db::table($tableName)->data($newData)->insertAll();
        }
    }

}