<?php
namespace app\index\event;

use \think\Db;


class Service
{


	public function findByNew($action=false)
	{
		return Db::name('services')->limit(15)->order('createTime','desc')->where('sellerid','57e041fc421aa9293f0e5d11')->select();
		//return Db::name('services')->limit(15)->order('createTime','desc')->where('status',0)->where('sellerid','57e041fc421aa9293f0e5d11')->select();
	}

	public function findByName($name,$area)
	{
		return Db::name('services')->where('name','like',$name)->where('status',1)->order('createTime','desc')->paginate(50,true);		//未支持all选项;只显示已审核的service；临时去除地区
		//return Db::name('services')->where('name','like',$name)->where('area','like',$area)->where('status',1)->order('createTime','desc')->paginate(30,true);		//未支持all选项;只显示已审核的service
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
		return Db::name('services')->where('categories',$like,$categories)->where('status',1)->paginate(50,true);	//临时去除地区
		//return Db::name('services')->where('categories',$like,$categories)->where('area','like',$area)->paginate(50,true);
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

	public function findAllByStatus($status=0)
	{
		//$services = Db::name('services')->where('status',"=",$status)->order('createTime','desc')->paginate(10,true);
		$services = Db::name('services')->where('status',"=",$status)->order('createTime','desc')->limit(20)->select();
///*
		foreach ($services as $key=>$service)
		{
			$services[$key]['status'] = $this->getStatusAttr($service['status']);
			if (!empty($service['createTime']))
			{
				$services[$key]['cMM'] = $this->getDateAttr($service['createTime'],'F');
				$services[$key]['cDD'] = $this->getDateAttr($service['createTime'],'d');
			}
		}
//*/
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

	public function approveService($data,$serviceId)
	{
		$update = Db::name('services')->where('_id', $serviceId)->update($data);
		if ($update==0)
			return false;
		else
			return true;
	}
}
