<?php
namespace app\common\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'username' => 'require|alphaDash|length:5,50|unique:admin',
        'password' => 'require|/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/|length:6,32',
        'repassword' => 'require|confirm:password',
        'name' => 'chsDash|length:1,20',
        'mobile' => 'mobile|unique:admin',
        'email' => 'email|unique:admin',
        'status' => 'require|in:0,1',
    ];
    
    protected $message = [
        'username' => 'username_require',
        'username.unique' => 'username_unique',
        'password' => 'password_require',
        'repassword' => 'repassword_require',
        'name' => 'name_require',
        'mobile' => 'mobile_require',
        'mobile.unique' => 'mobile_unique',
        'email' => 'email_require',
        'email.unique' => 'email_unique',
        'status' => 'status_require',
    ];
    
    protected $scene = [
        'create' => ['username', 'password', 'repassword', 'name', 'mobile', 'email', 'status'],
        'edit_password' => ['password', 'repassword', 'name', 'mobile', 'email', 'status'],
        'edit'   => ['name', 'mobile', 'email', 'status'],
        'name'   => ['name'],
        'mobile' => ['mobile'],
        'email'  => ['email'],
        'status' => ['status'],
    ];
}