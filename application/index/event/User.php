<?php
namespace app\index\event;

use \think\Db;


class User
{
	public function checkPassword($email,$pw)
	{
		$pw = $this->encryptPw($pw);
		return Db::name('users')->field('nickname')->where('email','=',$email)->where('password','=',md5($pw))->find();
	}

	public function changeNickname($uid,$email,$nickname)
	{
		return Db::name('users')->where('email','=',$email)->setField('nickname',$nickname);
	}

	public function changePassword($uid,$email,$opw,$npw)
	{
		$npw = $this->encryptPw($npw);
		$opw = $this->encryptPw($opw);
		return Db::name('users')->where('email','=',$email)->where('_id','=',$uid)->where('password','=',md5($opw))->setField('password',md5($npw));
	}

	public function findUser($email)
	{
		return Db::name('users')->field('id')->where('email','=',$email)->limit(1)->find();
	}

	public function findUserById($uid)
	{
		return Db::name('users')->field('password','_id',true)->where('_id','=',$uid)->limit(1)->find();
	}

	public function addUser($user)
	{

		$user['password'] = md5($this->encryptPw($user['password']));
		return Db::name('users')->insertGetId($user);
	}

	public function encryptPw($char)
	{
		$encrypt = "Nyis.Market";
		return $char.$encrypt;
	}

	public function updateUser($user,$uid)
	{
		$update = Db::name('users')->where('_id', $uid)->update($user);
		if ($update==0)
			return false;
		else
			return true;
	}

}
