<?php
namespace app\index\event;

use \think\Db;


class Service
{


	public function findByName($name,$area)
	{
		return Db::name('services')->where('name','like',$name)->where('area','like',$area)->paginate(30,true);
	}

	public function findById($id)
	{
		return Db::name('services')->where('_id','=',$id)->limit(1)->find();
	}

	public function editById($sid,$id)
	{
		$service = Db::name('services')->where('sellerid','=',$sid)->where('_id','=',$id)->limit(1)->find();
		if (empty($service))
			$service = Db::name('services')->where('sellerid','=',$sid)->limit(1)->find();
		
		if (empty($service))
			return false;
		$service['status'] = $this->getStatusAttr($service['status']);
		if (!empty($service['createTime']))
		{
			$service['cMM'] = $this->getDateAttr($service['createTime'],'F');
			$service['cDD'] = $this->getDateAttr($service['createTime'],'d');
		}
		return $service;
	}

	public function findByCategories($categories,$like,$area)
	{
		return Db::name('services')->where('categories',$like,$categories)->where('area','like',$area)->paginate(30,true);
	}

	public function encryptPw($char)
	{
		$encrypt = "Nyis.Market";
		return $char.$encrypt;
	}

	public function addService($data)
	{
		return Db::name('services')->insertGetId($data);
	}

	public function findAllById($sid)
	{
		$services = Db::name('services')->where('sellerid',"=",$sid)->select();
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
        	$status = [9=>'删除',0=>'待审核',1=>'正常',2=>'审核中'];
        	return $status[$value];
    	}

    	public function getDateAttr($value,$p)
    	{
        	return date($p,$value);
    	}

	public function updateService($data,$sid,$serviceId)
	{
		$update = Db::name('services')->where('sellerid',$sid)->where('_id', $serviceId)->update($data);
		if ($update==0)
			return false;
		else
			return true;
	}
}
