<?php
namespace app\index\controller;

use \think\Controller;
use \think\View;
use \think\Session;
use \think\Request;
use \think\Lang;
use \think\Db;

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
			return $this->redirect('/seller/login');	//Disable message page
			//return $this->error(Lang::get('login_first'),'/seller/login');
		//此版本未生效;
    	}


    	public function index()
     	{
		$this->redirect('/seller/myservice');
     	}

	public function logout()
	{
		Session::delete('isSellerLogin');
		Session::delete('sid');
		Session::delete('sName');
		Session::delete('level');
		return $this->redirect('/seller/login');	//Disable message page
		//return $this->success(Lang::get('logout_success'), '/seller');
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
					Session::set('level',$seller['status']);
					return $this->redirect('/seller');	//Disable message page
					//return $this->success(Lang::get('login_success'), '/seller');
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
					Session::set('level',$data['status']);
					return $this->redirect('/seller');	//Disable message page
					//return $this->success(Lang::get('registered'), '/seller');
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
						//'status' => input('status'),
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

	public function mycases()
	{
		$sid = Session::get('sid');
	 	$cases = controller('Cases','event');
		$case = $cases->findAllBySellerid($sid);
		$view = new View();
		$view->assign('cases',$case);
		$view->nickname = Session::get('sName');
		return $view->fetch();
	

	}
/*			
	public function updateservice()
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
*/
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
                                if (empty($_POST['fees']))
					$_POST['fees'] = 0;
                                $data = array (
                                                'name'=> input('name'),
                                                'sellerName'=> Session::get('sName'),
                                                'description'=> input('description'),
                                                'sellerid' => $sid,
                                                'categories' => input('categories'),
                                                'checklist' => $_POST['checklist'],
                                                'price' => $_POST['price'],
                                                'area' => implode($_POST['state'],","),
                                                'fees' => $_POST['fees'],
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

	public function update()
	{
		$sid = Session::get('sid');
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
	 			$sellers = controller('Seller','event');
				$emial = input('email');
				$opw = input('opw');
				$npw = input('npw');
				$result = $sellers->changePassword($sid,$email,$opw,$npw);
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
	 			$sellers = controller('Seller','event');
				$data = array();
				if (input('name'))
					$data['name'] = input('name');
				if (input('phone'))
					$data['phone'] = input('phone');
				if (input('wechat'))
					$data['wechat'] = input('wechat');
				if (input('area'))
					$data['area'] = input('area');
				if (input('address'))
					$data['address'] = input('address');
				if (input('practice'))
					$data['practice'] = input('practice');
				if (input('descript'))
					$data['descript'] = input('descript');
				//if (input('status'))				//系统管理员才能更改
				//	$data['status'] = input('status');
				//if (input('lawyerId'))			//系统管理员才能更改；关联lawyer
				//	$data['lawyerId'] = input('lawyerId');
				if (empty($data))
				{
					$data['update'] = 'Fail';
					$data['reasons'] = 'Empty Data!!';
				} else {
					$result = $sellers->updateUser($data,$sid);
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
			case ('service'):
	 			$services = controller('Service','event');
				$serviceId = input('id');
				$data = array();
				if (input('name'))
					$data['name'] = input('name');
				if (input('description'))
					$data['description'] = input('description');
				if (input('categories'))
					$data['categories'] = input('categories');
				if (input('type'))
					$data['type'] = input('type');
				if (!empty($_POST['checklist']))
					$data['checklist'] = $_POST['checklist'];
				if (!empty($_POST['price']))
					$data['price'] = $_POST['price'];
				if (!empty($_POST['fees']))
					$data['fees'] = $_POST['fees'];
				if (!empty($_POST['state']))
					$data['area'] = implode($_POST['state'],",");
				switch(true)
				{
					case (empty($serviceId)):
					{
						$data['update'] = 'fail';
						$data['reasons'] = 'empty ServiceId!!';
						break;
					}

					case (empty($data)):
					{
						$data['update'] = 'fail';
						$data['reasons'] = 'empty data!!';
						break;
					}
					default: 
					{
						$data['sellerName'] = Session::get('sName');
						$result = $services->updateService($data,$sid,$serviceId);	//sid & serviceId 
						if ($result)
							$data['update'] = 'Success';
						else
						{
							$data['update'] = 'Fail';
							$data['reasons'] = 'Data Save Error!!';

						}
						break;						
					}
				}
				return json($data,200);
				break;
			case ('delete'):
	 			$services = controller('Service','event');
				$serviceId = input('id');
				$data = array();
				if (empty($serviceId))
				{
					$data = $_POST;
					$data['update'] = 'Fail';
					$data['reasons'] = 'Empty Data!!';
				} else {
					$data['status'] = 9;
					$result = $services->updateService($data,$sid,$serviceId);	//sid & serviceId 
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
			case ('approve'):
	 			$services = controller('Service','event');
				$serviceId = input('id');
				$level = Session::get('level');
				$data = array();
				switch(true)
				{
					case (empty($serviceId)):
					{
						$data = $_POST;
						$data['update'] = 'Fail';
						$data['reasons'] = 'Empty Data!!';
						break;
					} 
					case ($level!=9):
					{
						//$data = $_POST;
						$data['update'] = 'Fail';
						$data['reasons'] = 'Access Denied!!';
						break;
					} 
					default: 
					{
						$data['status'] = 1;
						$result = $services->approveService($data,$serviceId);	//serviceId 
						if ($result)
							$data['update'] = 'Success';
						else
						{
							$data['update'] = 'Fail';
							$data['reasons'] = 'Data Save Error!!';

						}
						break;						
					}
				}
				return json($data,200);
				break;
			case ('approveCase'):
				$data = array();
				switch(true)
				{
					case (empty(input('caseid'))):
					{
						$data['update'] = 'Fail';
						$data['reasons'] = 'Caseid Error';
						break;
					}
					case (empty(input('finalPrice'))):
					{
						$data['update'] = 'Fail';
						$data['reasons'] = 'finalPrice Empty';
						break;
					}

					default: 
					{
						$data['status'] = 1;
						$caseid = input('caseid');
						$data['finalPrice'] = input('finalPrice');
		 				$cases = controller('Cases','event');
						$result = $cases->updateCases($data,$sid,$caseid,'sellerid');
						if ($result)
						{
							$data['approve'] = 'success';
						} else
						{
							$data['approve'] = 'Fail';
							$data['reasons'] = 'Case Save Error!!';
						}						
					}
				}
				return json($data,200);
				break;
			case ('rejectsCase'):
				$data = array();
				switch(true)
				{
					case (empty(input('caseid'))):
					{
						$data['update'] = 'Fail';
						$data['reasons'] = 'Caseid Error';
						break;
					}
					default: 
					{
						$data['status'] = 3;
						$caseid = input('caseid');
		 				$cases = controller('Cases','event');
						$result = $cases->updateCases($data,$sid,$caseid,'sellerid');
						if ($result)
						{
							$data['rejects'] = 'success';
						} else
						{
							$data['rejects'] = 'Fail';
							$data['reasons'] = 'Case Save Error!!';
						}						
					}
				}
				return json($data,200);
				break;
			case ('addMessage'):
				$data = array();
				if (input('caseid'))
					$data['caseid'] = input('caseid');
				if (input('content'))
					$data['content'] = input('content');
				if (empty($data))
				{
					$data['addReview'] = 'Fail';
					$data['reasons'] = 'Empty Data!!';
				} else {
	 				$cases = controller('Cases','event');
					$result = $cases->findByCaseId($data['caseid'],$sid);
					if ($result)
					{
						$data['sellerid'] = $result['sellerid'];
						$data['serviceid'] = $result['serviceid'];
						$data['owner'] = $sid;
						$data['uid'] = $result['uid'];
						$data['sellerName'] = $result['sellerName'];
						$data['clientName'] = $result['clientName'];
						$data['status'] = 1;	//1=正常;2=删除;
						$data['createTime'] = time();
	 					$messages = controller('Messges','event');
						$message = $messages->addMessage($data);
						if ($message)
						{
							$data['messageid'] = $message;
							$data['addMessage'] = 'success';
						} else {
							$data['addMessage'] = 'Fail';
							$data['reasons'] = 'Data Save Error!!';

						}
					} else
					{
						$data['addMessage'] = 'Fail';
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

        public function view()
        {
		$sid = Session::get('sid');
	 	$services = controller('Service','event');
		$serviceid = input('id')?input('id'):'57d13e59421aa90dd56fcaa3';	//Using FAKE ServiceId when id is emtpy;
		$service = $services->editById($sid,$serviceid);
		$view = new View();
		$view->assign('service',$service);
		$view->nickname = Session::get('sName');
		return $view->fetch();

    	}

	public function setadmin()
	{
		//var_dump(Session::get('sid'));
		$data = array();
		//$sid = input('id');
		$level = Session::get('level');
		$status = 9;
		$email = input('email');
		var_dump($email);
	 	$sellers = controller('Seller','event');
		$sid = $sellers->findUser($email);
		$sid = ((string)$sid["_id"]);
		var_dump($sid);
	 	$services = controller('Service','event');
		$result = $services->findAllById($sid);
		var_dump($result);
		exit();	
		$data['status'] = $status;
				if (empty($sid) or $level!=9)
				{
					$data['update'] = 'Fail';
					$data['reasons'] = 'Empty Data!!';
				} else {
					$result = $sellers->updateUser($data,$sid);
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


	}

	public function getcaseinfo()
	{
		$sid = Session::get('sid');
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


		$data = array();
		switch(true)
		{
			case (empty(input('caseid'))):
			{
				$data['update'] = 'Fail';
				$data['reasons'] = 'Caseid Error';
				break;
			}
			default: 
			{
				$caseid = input('caseid');
				$cases = controller('Cases','event');
				$result = $cases->findByCaseId($caseid,$sid,'sellerid');
				if ($result)
				{
					$data['status'] = 'Success';
					$data['caseinfo'] = $result['checklist'];
				}
				else
				{
					$data['status'] = 'Fail';
					$data['reasons'] = 'Case info not found!';
				}						
			}
		}
		return json($data,200);
	}

	public function addreviews()
	{
		//var_dump(Session::get('sid'));
		$data = array();
		$serviceid = input('id');
		$level = Session::get('level');
		if ($level!=9)
			exit();
	 	$services = controller('Service','event');
		$result = $services->findById($serviceid);
		//exit();	
		if ($result)
		{
			$data['serviceid'] = $serviceid;
			$data['sellerid'] = $result['sellerid'];
			if (input('content'))
				$data['content'] = input('content');
			if (input('nickname'))
				$data['nickname'] = input('nickname');
			if (input('rank'))
				$data['rank'] = input('rank');

			$data['status'] = 1;	//0=待审核; 1=成交;2=退回;3=拒绝
			$date['createTime'] = time();
			switch(true)
			{
				case(empty($data['serviceid'])):
					$data['addReview'] = 'Fail';
					$data['reasons'] = 'Serviceid Empty!!';
					break;
				case(empty($data['sellerid'])):
					$data['addReview'] = 'Fail';
					$data['reasons'] = 'Sellerid Empty!!';
					break;
				case(empty($data['content'])):
					$data['addReview'] = 'Fail';
					$data['reasons'] = 'Content Empty!!';
					break;
				case(empty($data['rank'])):
					$data['addReview'] = 'Fail';
					$data['reasons'] = 'Rank Empty!!';
					break;
				default:
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
			}
		} else
		{
			$data['addReview'] = 'Fail';
			$data['reasons'] = 'Serviceid not found!!';

		}						
		return json($data,200);


	}


        public function approve()
        {
		$sid = Session::get('sid');
	 	$services = controller('Service','event');
		$level = Session::get('level');
		if ($level!=9)
		{
			$data['reasons'] = "Access Denied!!";
			return json($data,200);
		}
		$service = $services->findAllByStatus();
		$view = new View();
		$view->assign('service',$service);
		$view->nickname = Session::get('sName');
		return $view->fetch();

    	}

        public function upload()
        {
		$sid = Session::get('sid');
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

		#$file = $_FILES['file']['tmp_name'];
		$data = array();
		$file = request()->file('file');
		if (empty($file))
			return json("No Data!!",200);
		$info = $file->move(ROOT_PATH . 'uploads');
	    	if($info){
        		$data["filename"] = $info->getFilename(); 
        		$data["path"] = $info->getSaveName(); 
    		}else{
        		echo $file->getError();
    		}
		switch(true)
		{
			case (empty(input('caseid'))):
			{
				$data['upload'] = 'Fail';
				$data['reasons'] = 'Caseid Error';
				break;
			}
			case (empty(input('description'))):
			{
				$data['upload'] = 'Fail';
				$data['reasons'] = 'description Error';
				break;
			}
			case (empty($file)):
			{
				$data['upload'] = 'Fail';
				$data['reasons'] = 'File Empty';
				break;
			}

			default: 
			{
				$caseid = input('caseid');
				$cases = controller('Cases','event');
				$result = $cases->findByCaseId($caseid,$sid,'sellerid');
				if ($result)
				{
					$data['uid'] = $result['uid'];
					$data['sellerid'] = $result['sellerid'];
					$data['owner'] = $result['sellerid'];
					$data['serviceid'] = $result['serviceid'];
					#$data['serviceName'] = $result['serviceName'];
					#$data['clientName'] = $result['clientName'];
					$data['caseid'] = $caseid;
					$data['description'] = input('description');
				}
				else
				{
					$data['status'] = 'Fail';
					$data['reasons'] = 'Case info not found!';
				}

 				$upfile = controller('Files','event');
				$result = $upfile->addFiles($data);
				if ($result)
				{
					$data['upload'] = 'success';
					$data['fileid'] = $result;
				} else
				{
					$data['upload'] = 'Fail';
					$data['reasons'] = 'File Create Error!!';
				}						
			}
		}
		return json($data,200);
		break;
   	}

        public function casedetail()
        {
		$sid = Session::get('sid');
		if (empty(input('id')))
			return json('Error',200);
		$caseid = input('id');
	 	$cases = controller('Cases','event');
		$result = $cases->findByCaseId($caseid,$sid,'sellerid');
		$view = new View();
		$view->assign('cases',$result);
		$view->nickname = Session::get('sName');
		return $view->fetch();

    	}

}
