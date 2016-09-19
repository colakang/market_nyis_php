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

	public function search()
	{
		$services = controller('Service','event');
		$categories = input('category');
		$area = input('stdLocation');
		$keyword = input('legalService');
		$like = "=";
		switch(true) {
			case (empty($categories)):
				$result = $services->findByName($keyword,$area);
				break;
			case (count($categories)!=8):
				$like = "like";
				$categories = "^".$categories;
			default:	
				$result = $services->findByCategories($categories,$like,$area);
				break;
	   	} 
		$nickname = false;
		if (Session::has('isLogin'))
		{
			$nickname = Session::get('nickname');
		}
	 	$view = new View();
		$view->nickname = $nickname;
		$view->keyword = $keyword;
		$view->assign('result',$result);
		return $view->fetch();

	}

	public function view()
	{
		$services = controller('Service','event');
		$id = input('id');
		$result = $services->findById($id);
		$nickname = false;
		if (Session::has('isLogin'))
		{
			$nickname = Session::get('nickname');
		}
	 	$view = new View();
		$view->nickname = $nickname;
		$view->assign('result',$result);
		return $view->fetch();

	}

	public function update()
	{
	 	$users = controller('User','event');
		$uid = Session::get('uid');
		if (empty($_POST))
		{
			$put=file_get_contents('php://input');
			$put=json_decode($put,1);
			if (is_array($put))
			{
				foreach ($put as $key=>$value)
				{
					$_POST[$key] = $value;
				}
			}
		}
		switch(input('oper')) 
		{
			case ('profile'):
				$data = array();
				if (input('nickname'))
					$data['nickname'] = input('nickname');
				if (!empty($_POST['baseInfo']))
					$data['baseInfo'] = $_POST['baseInfo'];
				if (!empty($_POST['idInfo']))
					$data['idInfo'] = $_POST['idInfo'];
				if (empty($data))
				{
					$data['update'] = 'Fail';
					$data['reasons'] = 'Empty Data!!';
				} else {
					$result = $users->updateUser($data,$uid);
					if ($result)
						$data['update'] = 'Success';
					else
					{
						$data['update'] = 'Fail';
						$data['reasons'] = 'Data Save Error!!';

					}						
				}
				return json($data,200);
				break;
			default:	
				$data['update'] = 'Fail';
				$data['reasons'] = 'Oper Not Found';
				return json($data,200);	
				break;
	   	} 
	}

}
