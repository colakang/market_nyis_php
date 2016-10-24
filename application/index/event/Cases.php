<?php
namespace app\index\event;

use \think\Db;


class Cases
{



	public function findById($id,$uid)
	{
		return Db::name('cases')->where('_id','=',$id)->where('uid',$uid)->limit(1)->find();
	}

	public function findAllBySellerid($sellerid,$status=0)
	{
		$services = Db::name('cases')->where('sellerid',$sellerid)->select();
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

	public function addCases($data)
	{
		return Db::name('cases')->insertGetId($data);
	}

	public function findAllByUid($uid)
	{
		$services = Db::name('cases')->where('uid',"=",$uid)->select();
		foreach ($services as $key=>$service)
		{
			//$name = Db::name('services')->where('_id',$service['serviceid'])->field('name')->limit(1)->find();
			//$services[$key]['name'] = $name['name'];
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
        	$status = [9=>'删除',0=>'待审核',1=>'确认',2=>'退回',3=>'拒绝',4=>'已支付',5=>"处理中",6=>"补资料",7=>"完成"];
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
