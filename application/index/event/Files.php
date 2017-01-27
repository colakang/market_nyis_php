<?php
namespace app\index\event;

use \think\Db;


class Files
{

	public function findById($fileid,$id,$filter='sellerid')
	{
		return Db::name('files')->where('_id','=',$fileid)->where($filter,'=',$id)->limit(1)->find();
	}


	public function findByCaseId($caseid,$id,$filter='sellerid')
	{
		return Db::name('files')->where('caseid','=',$caseid)->where($filter,'=',$id)->limit(1)->find();
	}

	public function findAllByCaseId($caseid,$id,$filter='sellerid')
	{
		$services = Db::name('files')->where('caseid','=',$caseid)->where($filter,'=',$id)->select();
		foreach ($services as $key=>$service)
		{
			$services[$key]['fileid'] = (string) $service['_id'];
		}
		return $services;

	}

	public function addFiles($data)
	{
		return Db::name('files')->insertGetId($data);
	}


	public function deleteFiles($id,$ownid,$caseid)
	{
		$update = Db::name('files')->where('caseid',$caseid)->where('_id', $id)->where('owner',$ownid)->delete();
		if ($update==0)
			return false;
		else
			return true;
	}


}
