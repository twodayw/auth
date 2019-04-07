<?php
namespace PhalApi\Auth\Auth\Domain;

use PhalApi\Auth\Auth\Model\User as Model_Auth_User;
/**
 * 用户领域类
 *
 * @author hms
 */
class User
{
    private static $Model = null;

    public function  __construct()
    {
        if (self::$Model == null) {
            self::$Model = new Model_Auth_User();
        }
    }
    
    public function getUserInfo($uid) {
        $r=self::$Model->getUserInfo($uid);
        return $r;
    }

 

}
