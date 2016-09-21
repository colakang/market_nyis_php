<?php
namespace app\index\event;

use \think\Db;


class Seller
{
	public function checkPassword($email,$pw)
	{
		$pw = $this->encryptPw($pw);
		return Db::name('sellers')->field('name')->where('email','=',$email)->where('password','=',md5($pw))->find();
	}

	public function changeName($uid,$email,$name)
	{
		return Db::name('sellers')->where('email','=',$email)->setField('name',$name);
	}

	public function changePassword($sid,$email,$opw,$npw)
	{
		$npw = $this->encryptPw($npw);
		$opw = $this->encryptPw($opw);
		return Db::name('sellers')->where('email','=',$email)->where('_id','=',$sid)->where('password','=',md5($opw))->setField('password',md5($npw));
	}

	public function findUser($email)
	{
		return Db::name('sellers')->field('id')->where('email','=',$email)->limit(1)->find();
	}

	public function findUserById($sid)
	{
		$seller = Db::name('sellers')->field('password','_id',true)->where('_id','=',$sid)->limit(1)->find();
		if(empty($seller['status']))
			$seller['status'] = 0;
		$seller['status'] = $this->getStatusAttr($seller['status']);
		return $seller;
	}

	public function addUser($user)
	{

		$user['password'] = md5($this->encryptPw($user['password']));
		return Db::name('sellers')->insertGetId($user);
	}

	public function encryptPw($char)
	{
		$encrypt = "Nyis.Market.Seller";
		return $char.$encrypt;
	}
	
	public function updateUser($user,$sid)
	{
		$update = Db::name('sellers')->where('_id', $sid)->update($user);
		if ($update==0)
			return false;
		else
			return true;
	}

	public function addService($data)
	{
		return Db::name('services')->insertGetId($data);
	}

    	public function getStatusAttr($value)
    	{
        	$status = [0=>'Registered',1=>'Certified',2=>'VIP'];
        	return $status[$value];
    	}



}
