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
		return $this->redirect('/');
	}

	public function profile()
	{
		if (!Session::has('isLogin'))
			return $this->error('请登陆','/');
		$uid = Session::get('uid');
	 	$users = controller('User','event');
		$profile = $users->findUserById($uid);
		unset($profile['_id']);
		if (input('api')=="v1")
		{
			//$case["nickname"] = Session::get('nickname');
			return json($profile,200);	
		}
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
				$redirect = input('redirect') ? input('redircet') : "/";
				return $this->success('登陆成功', $redirect);
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
					$link = input('redirect') ? input('redirect') : "/";
					$this->redirect($link);
					//$this->redirect('/');
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
		if (input('api')=="v1")
		{
			//$case["nickname"] = Session::get('nickname');
			return json($case,200);	
		}
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
						$data['clientName'] = Session::get('nickname');
						$data['submitPrice'] = $result['price'];
						$data['finalPrice'] = 0;
						$data['isComment'] = false;
						$data['createTime'] = time();
						$data['uid'] = $uid;
						$data['refNo'] = substr(md5($data['createTime'].$uid),0,8);
						$data['status'] = 10;	//0=待审核; 1=成交;2=律师锁定;3=律师拒绝;4=用户拒绝;10=草稿
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
				switch(true)
				{
					case (empty(input('caseid'))):
					{
						$data['update'] = 'Fail';
						$data['reasons'] = 'Caseid Error';
						$data['post'] = $_POST;
						break;
					}
					case (empty($_POST['checklist'])):
					{
						$data['update'] = 'Fail';
						$data['reasons'] = 'Empty Data!!';
						break;
					} 
					default: 
					{
						$caseid = input('caseid');
						$data['checklist'] = $_POST['checklist'];
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
				}
				return json($data,200);
				break;
			case ('submitCase'):
				$data = array();
				switch(true)
				{
					case (empty(input('caseid'))):
					{
						$data['update'] = 'Fail';
						$data['reasons'] = 'Caseid Error';
						break;
					}
					case (empty(input('sellerid'))):
					{
						$data['update'] = 'Fail';
						$data['reasons'] = 'Sellerid Error';
						break;
					}
					default: 
					{
						$data['status'] = 0;
						$caseid = input('caseid');
		 				$cases = controller('Cases','event');
						$result = $cases->updateCases($data,$uid,$caseid);
						if ($result)
						{
							$data['submit'] = 'success';
			 				$sellers = controller('Seller','event');
							$seller = $sellers->findUserById(input('sellerid'));
							$clientName = Session::get('nickname');
							$text = "New Case In!! Client: ".$clientName." Date:";
							$sendmail = $cases->sendmail($seller['email'],$seller['name'],$text);
							if ($sendmail)
								$data['sendmail'] = 'success';

						} else
						{
							$data['submit'] = 'Fail';
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
						$data['status'] = 4;
						$caseid = input('caseid');
		 				$cases = controller('Cases','event');
						$result = $cases->updateCases($data,$uid,$caseid);
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
							$caseData['isComment'] = true;
							$result =  $cases->updateCases($caseData,$uid,$data['caseid']);
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
		$mail->isSMTP();
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 2;
		//Ask for HTML-friendly debug output
		$mail->Debugoutput = 'html';
		//Set the hostname of the mail server
		$mail->Host = 'smtp.gmail.com';
		// use
		// $mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6
		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$mail->Port = 465;
		//Set the encryption system to use - ssl (deprecated) or tls
		$mail->SMTPSecure = 'ssl';
		//Whether to use SMTP authentication
		$mail->SMTPAuth = true;
		//Username to use for SMTP authentication - use full email address for gmail
		$mail->Username = "admin@nyis.com";
		//Password to use for SMTP authentication
		$mail->Password = "flvlzndkpmlhswle";
		//Set who the message is to be sent from
		$mail->setFrom('admin@nyis.com', 'NYIS');
		//Set an alternative reply-to address
		$mail->addReplyTo('admin@nyis.com', 'Reply');
		//Set who the message is to be sent to
		$mail->addAddress('colakang@gmail.com', 'C.K');
		//Set the subject line
		$mail->Subject = 'PHPMailer GMail SMTP test';
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
		//Replace the plain text body with one created manually
		$mail->AltBody = 'This is a plain-text message body';
		//Attach an image file
		//$mail->addAttachment('images/phpmailer_mini.png');
		$mail->Body = "Test Mail";
		//send the message, check for errors
		if (!$mail->send()) {
		    echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
		    echo "Message sent!";
		}
		exit();
	}

        public function upload()
        {
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

		#$file = $_FILES['file']['tmp_name'];
		$data = array();
		$file = request()->file('file');
		if (empty($file))
			return json("No Data!!",200);
		$info = $file->move(ROOT_PATH . 'uploads');
	    	if($info){
        		$data["filename"] = $info->getFilename(); 
        		$data["path"] = DS.$info->getSaveName(); 
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
				$result = $cases->findByCaseId($caseid,$uid,'uid');
				if ($result)
				{
					$data['uid'] = $result['uid'];
					$data['sellerid'] = $result['sellerid'];
					$data['owner'] = $result['uid'];
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

	public function download()
	{
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
 		switch(true)
		{
			case (empty(input('fileid'))):
			{
				$data['download'] = 'Fail';
				$data['reasons'] = 'fileid Error';
				break;
			}
			default: 
			{
				$files = controller('Files','event');
				$file = $files->findById(input('fileid'),$uid,"uid"); 
				if($file)
				{
					$filename = $file['filename'];
					$filepath = $file['path'];
					$path = ROOT_PATH . 'uploads/';			//文件实际路径
					header('Content-Description: File Transfer');
			    		header('Content-Type: application/octet-stream');
			    		$ua = $_SERVER["HTTP_USER_AGENT"];
			      		if (preg_match('/MSIE/', $ua)) {
			            		header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
			        	} elseif (preg_match("/Firefox/", $ua)) {
			            		header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
			        	} else {
			            		header('Content-Disposition: attachment; filename="' . $filename . '"');
			        	}
			        	header('Content-Transfer-Encoding: binary');
			        	header('Expires: 0');
			        	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			        	header('Pragma: public');
			        	header('Content-Length: ' . filesize($path.$filepath));
			        	if (strpos($_SERVER["SERVER_SOFTWARE"], 'Apache') !== false) {
			            		header('X-Sendfile: ' . $filepath);
			        	} elseif (strpos($_SERVER["SERVER_SOFTWARE"], 'nginx') !== false) {
			            		// 使用 nginx 服务器时，则把 文件下载交给 nginx 处理，这样效率高些
			            	header('X-Accel-Redirect: '. '/dl/' . $filepath);
			        	} else {
			          		set_time_limit(300);  // 避免下载超时
			            		ob_end_clean();  // 避免大文件导致超过 memory_limit 限制
			          		readfile($path.$filePath);
			    		}
				} else {
					$data['download'] = 'Fail';
				}
				break;
			}
		}
		return json($data,200);
	}

	public function getFileList()
	{
		$uid = Session::get('uid');
		$data = array();
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
 		switch(true)
		{
			case (empty(input('caseid'))):
			{
				$data['getFileList'] = 'Fail';
				$data['reasons'] = 'fileid Error';
				break;
			}
			default: 
			{
	 			$files = controller('Files','event');
				$file = $files->findAllByCaseId(input('caseid'),$uid,'uid');
				$data = $file;
			}
		}
		return json($data,200);

	}


}
