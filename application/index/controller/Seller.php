<?php
namespace app\index\controller;

use \think\Controller;
use \think\View;
use \think\Session;
use \think\Request;
use \think\Lang;


class Seller extends Controller
{


    	public function _initialize()
    	{
		$request = Request::instance();
		$whiteList = array(
				'login',
				'register',
		);
		$action = strtolower($request->action());	
		if (!Session::has('isSellerLogin') and !in_array($action,$whiteList) )
			return $this->error(Lang::get('login_first'),'/seller/login');
		//此版本未生效;
    	}


    	public function index()
     	{
		$nickname = Session::get('sName');
	 	$view = new View();
		//$nickname = Lang::get('data type error');	使用多语言输出;
		$view->nickname = $nickname;
	 	return $view->fetch();
     	}

	public function logout()
	{
		Session::delete('isSellerLogin');
		Session::delete('sid');
		Session::delete('sName');
		return $this->success(Lang::get('logout_success'), '/seller');
	}

	public function profile()
	{
		$sid = Session::get('sid');
	 	$sellers = controller('Seller','event');
		$profile = $sellers->findUserById($sid);
		unset($profile['_id']);
		$view = new View();
		$view->assign('profile',$profile);
		$view->nickname = $profile['name'];
		return $view->fetch();
	}

	public function login()
	{
	 	$sellers = controller('Seller','event');
		switch(true) {
			case (Session::has('isSelerLogin')):
				return $this->success(Lang::get('login_success'), '/seller');
				break;
			case (empty($_POST)):
				$view = new View();
				return $view->fetch();
				break;
			case ( !captcha_check(input('verify')) ):
				return $this->error(Lang::get('captcha_fail'));
				break;
			default:	
				$username = input('email');
				$password = input('password');
				$seller = $sellers->checkPassword($username,$password);
				$isSellerId = (string)$seller['_id'];
				$isSellerName = $seller['name'];
				if (empty($isSellerId))
				{
					return $this->error(Lang::get('login_fail'));
				} else {
					
					Session::set('isSellerLogin',true);
					Session::set('sid',$isSellerId);
					Session::set('sName',$isSellerName);
					return $this->success(Lang::get('login_success'), '/seller');
		    		}
	   	}	 
	}

	public function register()
	{
	 	$sellers = controller('Seller','event');
		switch(true) {
			case (empty($_POST)):
				$view = new View();
				return $view->fetch();
				break;
			case ( input('password')!= input('retypePassword') ):
				return $this->error(Lang::get('password_not_match'));
				break;
			case ( !captcha_check(input('verify')) ):
				return $this->error(Lang::get('captcha_fail'));
				break;
			default:	
				$data = array (
						'email'=> input('email'),
						'password' => input('password'),
						'name' => input('name'),
						'wechat' => input('wechat'),
						'area' => input('area'),
						'status' => 0,
				);
				$isSellerId = $sellers->addUser($data);
				$isSellerName = $data['name'];
				if (empty($isSellerId))
				{
					return $this->error(Lang::get('login_fail'));
				} else {
					
					Session::set('isSellerLogin',true);
					Session::set('sid',$isSellerId);
					Session::set('sName',$isSellerName);
					return $this->success(Lang::get('registered'), '/seller');
		    		}
				break;
	   	} 
	}

	public function save()
	{
	 	$sellers = controller('Seller','event');
		switch(true) {
			case (empty($_POST)):
				return $this->error(Lang::get('data_fail'));
				break;
			default:	
				$data = array (
						//'email'=> input('email'),
						//'password' => input('password'),
						'name' => input('name'),
						'phone' => input('phone'),
						'wechat' => input('wechat'),
						'address' => input('address'),
						'practice' => input('practice'),
						'descript' => input('descript'),
						'status' => input('status'),
				);
				$sid = Session::get('sid');
				$isSellerId = $sellers->updateUser($data,$sid);
				$isSellerName = $data['name'];
				if (empty($isSellerId))
				{
					return $this->error(Lang::get('data_fail'));
				} else {
					
					//Session::set('isSellerLogin',true);
					//Session::set('sid',$isSellerId);
					Session::set('sName',$isSellerName);
					return $this->success(Lang::get('update_success'), '/seller/profile');
		    		}
				break;
	   	} 
	}

	public function myservice()
	{
		$sid = Session::get('sid');
	 	$services = controller('Service','event');
		$service = $services->findAllById($sid);
		$view = new View();
		$view->assign('service',$service);
		$view->nickname = Session::get('sName');
		return $view->fetch();
	

	}

	public function updateservcie()
	{
	 	$services = controller('Service','event');
		$service = $services->findById(input('_id'));
		unset($service['_id']);
		if (empty($service['name']))
			$service['name'] = "H1B";
		if (empty($service['createTime']))
			$service['createTime'] = time();
		if (empty($service['sellerName']))
			$service['sellerName'] = Session::get('sName');
		//$update = $services->updateService($service,input('_id'));
		exit();
	

	}

        public function addservice()
        {
                $services = controller('Service','event');
                switch(true) {
                        case (empty($_POST)):
				$nickname = Session::get('sName');
                                $view = new View();
				$view->nickname = $nickname;
                                return $view->fetch();
                                break;
                        default:
                                $sid = Session::get('sid');
                                $data = array (
                                                'name'=> input('name'),
                                                'sellerName'=> Session::get('sName'),
                                                'description'=> input('description'),
                                                'sellerid' => $sid,
                                                'categories' => input('categories'),
                                                'checklist' => $_POST['checklist'],
                                                'price' => input('price'),
                                                'area' => implode($_POST['state'],","),
                                                'fees' => input('fees'),
                                                'info' => input('info'),
                                                'type' => input('type'),
						'createTime' => time(),
                                                'status' => 0,
                                );
                                $serviceId = $services->addService($data);
                                if (empty($serviceId))
                                {
                                        return $this->error(Lang::get('service_fail'));
                                } else {

                                        return $this->success(Lang::get('service_success'), '/seller/addservice');
                                }
                                break;
                }
        }



}
