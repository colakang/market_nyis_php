<?php
namespace app\index\controller;

use \think\Controller;
use \think\View;
use \think\Session;


class Index extends Controller
{


    	public function _initialize()
    	{
		//if (!Session::has('isLogin'))
		//	return $this->error('请登陆','/index/login');
		//此版本未生效;
    	}


    	public function index()
     	{
		$nickname = false;
		if (Session::has('isLogin'))
		{
			$nickname = Session::get('nickname');
		}
	 	$view = new View();
		$view->nickname = $nickname;
	 	return $view->fetch();
     	}

	public function logout()
	{
		Session::delete('isLogin');
		Session::delete('uid');
		Session::delete('nickname');
		return $this->success('退出成功', '/');
	}

	public function profile()
	{
		if (!Session::has('isLogin'))
			return $this->error('请登陆','/');
		$uid = Session::get('uid');
	 	$users = controller('User','event');
		$profile = $users->findUserById($uid);
		unset($profile['_id']);
		$view = new View();
		$view->assign('profile',$profile);
		$view->nickname = $profile['nickname'];
		return $view->fetch();
	}

	public function login()
	{
	 	$users = controller('User','event');
		switch(true) {
			case (Session::has('isLogin')):
				return $this->success('登陆成功', '/');
				break;
			case (empty($_POST)):
				$this->redirect('/');
				break;
			default:	
				$username = input('email');
				$password = input('password');
				$user = $users->checkPassword($username,$password);
				$isUserId = (string)$user['_id'];
				$isUserName = $user['nickname'];
				if (empty($isUserId))
				{
					return $this->error('用户名或密码错误');
				} else {
					
					Session::set('isLogin',true);
					Session::set('uid',$isUserId);
					Session::set('nickname',$isUserName);
					return $this->success('登陆成功', '/');
		    		}
	   	}	 
	}

	public function register()
	{
	 	$users = controller('User','event');
		switch(true) {
			case (Session::has('isLogin')):
				return $this->redirect('/');
				break;
			case (empty($_POST)):
				return $this->redirect('/');
				break;
			case ( input('password')!= input('retypePassword') ):
				return $this->redirect('/');
				break;
			default:	
				$data = array (
						'email'=> input('email'),
						'password' => input('password'),
						'nickname' => input('nickname'),
						'age' => input('age'),
				);
				$isUserId = $users->addUser($data);
				$isUserName = $data['nickname'];
				if (empty($isUserId))
				{
					return $this->error('用户名或密码错误');
				} else {
					
					Session::set('isLogin',true);
					Session::set('uid',$isUserId);
					Session::set('nickname',$isUserName);
					return $this->success('注册成功', '/');
		    		}
				break;
	   	} 
	}
}
