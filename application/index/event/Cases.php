<?php
namespace app\index\event;

use \think\Db;


class Cases
{


	public function findByName($name,$area)
	{
		return Db::name('cases')->where('name','like',$name)->where('area','like',$area)->paginate(30,true);
	}

	public function findById($id)
	{
		return Db::name('cases')->where('_id','=',$id)->limit(1)->find();
	}

	public function findByCategories($categories,$like,$area)
	{
		return Db::name('cases')->where('categories',$like,$categories)->where('area','like',$area)->paginate(30,true);
	}

	public function encryptPw($char)
	{
		$encrypt = "Nyis.Market";
		return $char.$encrypt;
	}

	public function addCases($data)
	{
		return Db::name('cases')->insertGetId($data);
	}

	public function findAllById($uid)
	{
		$services = Db::name('cases')->where('uid',"=",$uid)->select();
		foreach ($services as $key=>$service)
		{
			$services[$key]['status'] = $this->getStatusAttr($service['status']);
			if (!empty($service['createTime']))
			{
				$services[$key]['cMM'] = $this->getDateAttr($service['createTime'],'F');
				$services[$key]['cDD'] = $this->getDateAttr($service['createTime'],'d');
			}
		}
		return $services;
	}

    	public function getStatusAttr($value)
    	{
        	$status = [-1=>'删除',0=>'待审核',1=>'正常',2=>'审核中'];
        	return $status[$value];
    	}

    	public function getDateAttr($value,$p)
    	{
        	return date($p,$value);
    	}

	public function updateCases($data,$uid,$caseid)
	{
		$update = Db::name('cases')->where('uid',$uid)->where('_id', $caseid)->update($data);
		if ($update==0)
			return false;
		else
			return true;
	}
}
