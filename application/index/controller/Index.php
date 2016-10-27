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
		$services = controller('Service','event');
		$promotion = $services->findByNew('promotion');
	 	$view = new View();
		$view->nickname = $nickname;
		$view->assign('promotion',$promotion);
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
					$this->redirect('/');
					//return $this->success('登陆成功', '/');
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
		$promotion = $services->findByNew('promotion');
		$history = $promotion;
		if (Session::has('isLogin'))
		{
			$nickname = Session::get('nickname');
		}
	 	$view = new View();
		$view->nickname = $nickname;
		$view->keyword = $keyword;
		$view->assign('result',$result);
		$view->assign('promotion',$promotion);
		$view->assign('history',$history);
		return $view->fetch();

	}

	public function view()
	{
		$services = controller('Service','event');
		$sellers = controller('Seller','event');
		$reviews = controller('Review','event');
		$id = input('id');
		$result = $services->findById($id);
		$seller = $sellers->findUserById($result['sellerid']);
		$promotion = $services->findByNew('promotion');
		$review = $reviews->findAllByServiceid($id);
		$history = $promotion;
		$nickname = false;
		if (Session::has('isLogin'))
		{
			$nickname = Session::get('nickname');
		}
	 	$view = new View();
		$view->nickname = $nickname;
		$view->assign('result',$result);
		$view->assign('seller',$seller);
		$view->assign('promotion',$promotion);
		$view->assign('history',$history);
		$view->assign('review',$review);
		return $view->fetch();

	}

	public function apply()
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
	public function mycases()
	{
		$uid = Session::get('uid');
	 	$cases = controller('Cases','event');
		$case = $cases->findAllByUid($uid);
		$view = new View();
		$view->assign('cases',$case);
		$view->nickname = Session::get('nickname');
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
			case ('password'):
				$data = array();
				$emial = input('email');
				$opw = input('opw');
				$npw = input('npw');
				$result = $users->changePassword($uid,$email,$opw,$npw);
				if ($result)
					$data['update'] = "Success";
				else {
					$data = $_POST;
					$data['update'] = 'Fail';
					$data['reasons'] = 'Data Save Error!!';

				}
				return json($data,200);
				break;
			case ('profile'):
				$data = array();
				if (input('nickname'))
					$data['nickname'] = input('nickname');
				if (!empty($_POST['basicInfo']))
					$data['basicInfo'] = $_POST['basicInfo'];
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
			case ('addcase'):
				$data = array();
				if (input('serviceid'))
					$data['serviceid'] = input('serviceid');
				if (empty($data))
				{
					$data['addcase'] = 'Fail';
					$data['reasons'] = 'Empty Data!!';
				} else {
	 				$services = controller('Service','event');
					$result = $services->findById($data['serviceid']);
					if ($result)
					{
						$data['sellerName'] = $result['sellerName'];
						$data['serviceName'] = $result['name'];
						$data['sellerid'] = $result['sellerid'];
						$data['clientname'] = Session::get('nickname');
						$data['submitPrice'] = $result['price'];
						$data['finalPrice'] = 0;
						$data['isComment'] = false;
						$data['createTime'] = time();
						$data['uid'] = $uid;
						$data['status'] = 0;	//0=待审核; 1=成交;2=退回;3=拒绝
						$data['checklist']['include'] = $result['checklist'];
	 					$cases = controller('Cases','event');
						$case = $cases->addCases($data);
						if ($case)
						{
							$data['caseid'] = $case;
							$data['addcase'] = 'success';
						} else {
							$data['addcase'] = 'Fail';
							$data['reasons'] = 'Data Save Error!!';

						}
					} else
					{
						$data['addcase'] = 'Fail';
						$data['reasons'] = 'Serviceid not found!!';

					}						
				}
				return json($data,200);
			case ('addInfo'):
				$data = array();
				if (input('caseid'))
					$caseid = input('caseid');
				if (!empty($_POST['checklist']))
					$data['checklist'] = $_POST['checklist'];
				if (empty($data))
				{
					$data['update'] = 'Fail';
					$data['reasons'] = 'Empty Data!!';
				} else {
	 				$cases = controller('Cases','event');
					$result = $cases->updateCases($data,$uid,$caseid);
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
			case ('addReview'):
				$data = array();
				if (input('caseid'))
					$data['caseid'] = input('caseid');
				if (input('content'))
					$data['content'] = input('content');
				if (input('nickname'))
					$data['nickname'] = input('nickname');
				if (input('rank'))
					$data['rank'] = input('rank');
				if (empty($data))
				{
					$data['addReview'] = 'Fail';
					$data['reasons'] = 'Empty Data!!';
				} else {
	 				$cases = controller('Cases','event');
					$result = $cases->findById($data['caseid'],$uid);
					if ($result)
					{
						$data['sellerid'] = $result['sellerid'];
						$data['serviceid'] = $result['serviceid'];
						$data['uid'] = $uid;
						$data['status'] = 1;	//0=待审核; 1=成交;2=退回;3=拒绝
						$data['createTime'] = time();
	 					$reviews = controller('Review','event');
						$review = $reviews->addReview($data);
						if ($review)
						{
							$data['reviewid'] = $review;
							$data['addReview'] = 'success';
						} else {
							$data['addReview'] = 'Fail';
							$data['reasons'] = 'Data Save Error!!';

						}
					} else
					{
						$data['addReview'] = 'Fail';
						$data['reasons'] = 'Caseid not found!!';

					}						
				}
				return json($data,200);

			default:	
				$data['update'] = 'Fail';
				$data['reasons'] = 'Oper Not Found';
				return json($data,200);	
				break;
	   	} 
	}

	public function testmail()
	{
		$mail = new \PHPMailer(true);
		dump($mail);
		exit();
	}

}
