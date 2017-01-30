<?php
namespace app\index\event;

use \think\Db;


class Messages
{

	public function findById($fileid,$id,$filter='sellerid')
	{
		return Db::name('messages')->where('_id','=',$fileid)->where($filter,'=',$id)->limit(1)->find();
	}


	public function findByCaseId($caseid,$id,$filter='sellerid')
	{
		return Db::name('messages')->where('caseid','=',$caseid)->where($filter,'=',$id)->limit(1)->find();
	}

	public function findAllByCaseId($caseid,$id,$filter='sellerid')
	{
		$services = Db::name('messages')->where('caseid','=',$caseid)->where($filter,'=',$id)->select();
		foreach ($services as $key=>$service)
		{
			$services[$key]['messageid'] = (string) $service['_id'];
		}
		return $services;

	}

	public function addMessage($data)
	{
		return Db::name('messages')->insertGetId($data);
	}


	public function deleteMessage($id,$ownid,$caseid)
	{
		$update = Db::name('messages')->where('caseid',$caseid)->where('_id', $id)->where('owner',$ownid)->delete();
		if ($update==0)
			return false;
		else
			return true;
	}


}
