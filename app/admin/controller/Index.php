<?php


namespace app\admin\controller;

use app\admin\utils\Captcha;
use app\admin\utils\ExcelUtils;
use app\admin\validate\Login;
use app\exception\CustomerException;
use app\utils\EncryptAndDecrypt;
use app\utils\RandomCodeUtil;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use think\facade\Cache;
use think\facade\Cookie;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Log;

class Index extends AdminBaseController
{

    public function index()
    {
        return view("Index/layout", ['admin_username' => session('admin.username')]);
    }


    public function indexPage()
    {
        return view("Index/index");
    }

    /**
     * 首页列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function indexData()
    {

        $page = $this->request->get('page') ?: 1;
        $pageSize = $this->request->get('limit') ?: 10;
        $adminId = $this->adminId;
        $keyword = trim($this->request->get('keyword')) ?: '';
        $where = [];
        if ($keyword) {
            $where[] = ['name|email', 'like', '%' . $keyword . '%'];
        }
        $field = [
            'id',
            'email',
            'name',
            'gender',
            'date_of_birth',
            'country',
            'university',
            'grad_year',
            'marketing_consent',
            'marketing_consent_updated_at',
            'record_created_at',
            'type'
        ];

        $s1 = Db::table('canada_students')->field($field)->where($where)->select();
        $s2 = Db::table('japan_students')->field($field)->where($where)->select();
        $s3 = Db::table('united_kingdom_students')->field($field)->where($where)->select();
        $s4 = Db::table('united_states_students')->field($field)->where($where)->select();
        if ($s1) {
            $userList = $s1->toArray();
        }
        if ($s2) {
            if ($userList) {
                $userList = array_merge($userList,$s2->toArray());
            }else {
                $userList = $s2->toArray();
            }
        }
        if ($s3) {
            if ($userList) {
                $userList = array_merge($userList,$s3->toArray());
            }else {
                $userList = $s3->toArray();
            }
        }
        if ($s4) {
            if ($userList) {
                $userList = array_merge($userList,$s4->toArray());
            }else {
                $userList = $s4->toArray();
            }
        }
        $count = count($userList);

        if ($count) {
            // 分页
            $userList = array_slice($userList, ($page - 1) * $pageSize, $pageSize);
        }
        $this->actionLog($adminId, '获取用户列表信息成功');
        return json(['code' => 0, 'message' => "获取成功", 'count' => $count, 'data' => $userList]);
    }

    /**
     * 登录页
     * @return \think\response\View
     */
    public function loginPage()
    {

        if (!empty(cookie('remember_admin_username')) && !empty(cookie('remember_admin_password'))) {
            $username = EncryptAndDecrypt::decrypt(cookie('remember_admin_username'), 'rembercookie');
            $password = EncryptAndDecrypt::decrypt(cookie('remember_admin_password'), 'rembercookie');

            $data = [
                'username' => $username,
                'password' => $password
            ];
        } else {
            $data = [
                'username' => '',
                'password' => ''
            ];
        }
        return view('Index/login', $data);

    }

    /**
     * 后台验证码生成
     * @return \think\response\Json
     */
    public function getCaptcha()
    {
        $checkcode = new Captcha();
        $checkcode->doimage();
        session('Code', strtolower($checkcode->get_code()), 600);
        return;
    }

    /**
     * 管理员登录
     * @return \think\response\Json
     * @throws CustomerException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loginData()
    {
        $post = $this->request->post();
        validate(Login::class)->check($post);
        $username = $post['username'];
        $password = $post['password'];
        $code = $post['code'];
        if (session('Code') == strtolower($code)) {
            session('Code', null);
            $res = Db::table('huion_admin')->where(['username' => $username, 'status' => 1])->find();
            if ($res) {
                // 验证密码
                if (password_verify($password . $res['encrypt'], $res['password'])) {
                    // remember
                    return $this->afterLoginSuccess($post, $res);
                } else {
                    throw new CustomerException('1001', '用户名或密码错误', 200);
                }
            } else {
                throw new CustomerException('1002', '用户名不存在', 200);
            }
        } else {
            throw new CustomerException('1011', '验证码不正确', 200);
        }
    }

    /**
     * 登录成功之后的操作
     * @param $post
     * @param $res
     * @return \think\response\Json
     * @throws \Exception
     */
    private function afterLoginSuccess($post, $res)
    {
        $token = RandomCodeUtil::createUniqueToken();

        if (1 == $post['remember']) {
            $prefix_username_key = EncryptAndDecrypt::encrypt($post['username'], 'rembercookie');
            $prefix_password_key = EncryptAndDecrypt::encrypt($post['password'], 'rembercookie');
            cookie('remember_admin_username', $prefix_username_key, 0);
            cookie('remember_admin_password', $prefix_password_key, 0);
        } else {
            cookie('remember_admin_username', null);
            cookie('remember_admin_password', null);
        }

        unset($res['password']);
        unset($res['encrypt']);

        session('admin.id', $res['id'], 7200);
        session('admin.username', $res['username'], 7200);
        session('admin_username_info', json_encode($res), 7200);

        //更新用户信息
        $data = [
            'login_times' => $res['login_times'] + 1,
            'last_login_ip' => $this->request->ip(),
            'last_login_time' => time(),
            'update_time' => time()
        ];

        Db::startTrans();
        try {
            $update = Db::table('huion_admin')->where('id', $res['id'])->update($data);
            // 记录管理员日志
            $this->actionLog($res['id'], '登录');
            Db::commit();
            // 验证通过 生成 token信息
            return json([
                'errorCode' => 0,
                'message' => '登录成功',
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }


    public function logout()
    {
        Session::delete('admin.id');
        Session::delete('admin.username');
        Session::clear();
        return redirect('/admin/Index/loginPage');
    }

    /**
     * 后台管理员重置用户密码页面
     * @return \think\response\View
     */
    public function resetPasswordByAdminPage()
    {
        $id = $this->request->get('id');
        return view("Index/resetPasswordByAdminPage", [
            'id' => $id
        ]);
    }

    /**
     * 后台管理员重置用户密码放法
     * @throws CustomerException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function resetPasswordByAdminData()
    {
        $adminId = $this->adminId;
        $post = $this->request->post();

        $user_id = (int)$post['id'];
        $password = trim($post['password']);
        $confirm_password = trim($post['confirm_password']);
        if ($password == $confirm_password
            && strlen($password) >= 6 && strlen($password) <= 18
            && strlen($confirm_password) >= 6 && strlen($confirm_password) <= 18) {
            $where = ['id' => $user_id];
            $oldUserInfo = Db::table('huion_user')->where($where)->find();
            if ($oldUserInfo) {
                // 密码加密
                $salt = RandomCodeUtil::randomSalt();
                // 密码加密
                $newPassword = password_hash($password . $salt, PASSWORD_BCRYPT);

                $res = Db::table('huion_user')->where($where)->data(['password' => $newPassword, 'encrypt' => $salt])->update();
                if (false !== $res) {
                    $errmessage = sprintf('管理员id:%d为用户id:%d,手机号:%s,邮箱:%s的用户重置密码成功', $adminId, $oldUserInfo['id'], $oldUserInfo['phone'], $oldUserInfo['email']);
                    $this->actionLog($adminId, $errmessage);
                    return json(['errorCode' => 0, 'message' => '重置成功']);
                } else {
                    $errmessage = sprintf('管理员id:%d为用户id:%d,手机号:%s,邮箱:%s的用户重置密码失败', $adminId, $oldUserInfo['id'], $oldUserInfo['phone'], $oldUserInfo['email']);
                    $this->actionLog($adminId, $errmessage);
                }
            } else {
                throw new CustomerException(1002, '用户不存在');
            }
        } else {
            throw new CustomerException(2000, '两次密码不一致，密码长度在6-18位之间');
        }
    }

    // 定义导出类型
    private $types = ['united_kingdom', 'canada', 'japan', 'united_states', 'all']; // 官网 中心 ...


    /**
     * 用户导出
     * TODO 优化它 并添加日志
     * @throws CustomerException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function exportExcel()
    {
        $type = $this->request->get('type');
        if (!in_array($type, $this->types)) {
            throw new CustomerException(1012, '导出类型错误', 200);
        }

        set_time_limit(0);

        $userInfo = $this->selectStudents($type);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $fileName = date('YmdHis') . '.xlsx';
        $title = 'students';
//        $title = iconv("utf-8", "GB2312//TRANSLIT", $title);
        //$title = iconv("utf-8", "GBK", $title);
        //var_dump($title);die;
        $sheet->setTitle($title);

        // 显示对齐方式
        $styleArray = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER, //水平居中
                'vertical' => Alignment::VERTICAL_CENTER, //垂直居中
            ],
        ];

        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1','Email');
        $sheet->setCellValue('C1', 'Name');
        $sheet->setCellValue('D1', 'Gender');
        $sheet->setCellValue('E1', 'Birthday');
        $sheet->setCellValue('F1', 'Country');
        $sheet->setCellValue('G1', 'University');
        $sheet->setCellValue('H1', 'Grad Year');
        $sheet->setCellValue('I1', 'Marketing Consent');
        $sheet->setCellValue('J1', 'Marketing Consent Updated Time');
        $sheet->setCellValue('K1', 'Record Created Time');
        $sheet->setCellValue('L1', 'Source');
        // 居中
        $sheet->getStyle('A1:L1')->applyFromArray($styleArray);

        foreach ($userInfo as $k => $v) {
            $index = $k + 2;
            $sheet->setCellValue('A' . $index, $k+1);
            $sheet->setCellValue('B' . $index, $v['email']);
            $sheet->setCellValue('C' . $index, $v['name']);
            if($v['gender'] == 'M'){
                $gender = 'male';
            }elseif ($v['gender'] == 'F') {
                $gender = 'female';
            }else {
                $gender = 'na';
            }
            $sheet->setCellValue('D' . $index, $gender );
            $sheet->setCellValue('E' . $index, $v['date_of_birth']);
            $sheet->setCellValue('F' . $index, $v['country']);
            $sheet->setCellValue('G' . $index, $v['university']);

            $sheet->setCellValue('H' . $index, $v['grad_year']);
            $sheet->setCellValue('I' . $index, $v['marketing_consent']);
            $sheet->setCellValue('J' . $index, $v['marketing_consent_updated_at']);
            $sheet->setCellValue('K' . $index, $v['marketing_consent_updated_at']);
            $sheet->setCellValue('L' . $index, $v['type']);
            // 居中
            $sheet->getStyle('A' . $index . ':L' . $index)->applyFromArray($styleArray);
        }

        //自动计算列宽
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename=' . iconv("utf-8", "GB2312//TRANSLIT", $fileName));
        header('Cache-Control: max-age=0');
        ob_clean();
        ob_start();
        $write = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $write->save('php://output');
        ob_end_flush();

        exit();
    }

    private function selectStudents($type)
    {
        $field = [
            'id',
            'email',
            'name',
            'gender',
            'date_of_birth',
            'country',
            'university',
            'grad_year',
            'marketing_consent',
            'marketing_consent_updated_at',
            'record_created_at',
            'type'
        ];

        $s1 = Db::table('canada_students')->field($field)->select();
        $s2 = Db::table('japan_students')->field($field)->select();
        $s3 = Db::table('united_kingdom_students')->field($field)->select();
        $s4 = Db::table('united_states_students')->field($field)->select();
        if ($s1) {
            $userList = $s1->toArray();
        }
        if ($s2) {
            if ($userList) {
                $userList = array_merge($userList,$s2->toArray());
            }else {
                $userList = $s2->toArray();
            }
        }
        if ($s3) {
            if ($userList) {
                $userList = array_merge($userList,$s3->toArray());
            }else {
                $userList = $s3->toArray();
            }
        }
        if ($s4) {
            if ($userList) {
                $userList = array_merge($userList,$s4->toArray());
            }else {
                $userList = $s4->toArray();
            }
        }
        return $userList;
    }
}