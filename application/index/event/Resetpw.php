<?php
namespace app\index\event;

use \think\Db;


class Resetpw
{


	public function find($token,$email,$type)
	{
		return Db::name('resetpw')->where('_id','=',$token)->where('email','=',$email)->where('status','=',1)->where('type','=',$type)->limit(1)->find();
	}

	public function addResetpw($data)
	{
		return Db::name('resetpw')->insertGetId($data);
	}

	public function updateResetpw($data,$token,$email)
	{
		$update = Db::name('resetpw')->where('email',$email)->where('_id', $token)->delete();
		if ($update==0)
			return false;
		else
			return true;
	}
    	public function getStatusAttr($value)
    	{
        	$status = [9=>'删除',0=>'待审核',1=>'正常',2=>'审核中'];
        	return $status[$value];
    	}

    	public function getDateAttr($value,$p)
    	{
        	return date($p,$value);
    	}




}
