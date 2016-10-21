<?php
namespace app\index\event;

use \think\Db;


class Review
{


	public function findAllByUid($id)
	{
		return Db::name('reviews')->where('uid','=',$id)->select();
	}

	public function findAllBySellerid($id)
	{
		return Db::name('reviews')->where('sellerid','=',$id)->select();
	}

	public function findAllByServiceid($id)
	{
		return Db::name('reviews')->where('serviceid','=',$id)->select();
	}

	public function findAllByCaseid($id)
	{
		return Db::name('reviews')->where('caseid','=',$id)->select();
	}

	public function addReview($data)
	{
		return Db::name('reviews')->insertGetId($data);
	}

	public function updateReview($data,$uid,$reviewid)
	{
		$update = Db::name('reviews')->where('uid',$uid)->where('_id', $reviewid)->update($data);
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
