<?php
namespace app\index\event;

use \think\Db;


class Cases
{



	public function findById($id,$uid)
	{
		return Db::name('cases')->where('_id','=',$id)->where('uid',$uid)->limit(1)->find();
	}

	public function findByCaseId($caseid,$id,$filter='sellerid')
	{
		return Db::name('cases')->where('_id','=',$caseid)->where($filter,$id)->limit(1)->find();
	}

	public function findAllBySellerid($sellerid,$status=9)
	{
		$services = Db::name('cases')->where('sellerid',$sellerid)->where('status','<',$status)->select();
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
        	$status = [9=>'删除',0=>'待审核',1=>'确认',2=>'退回',3=>'拒绝',4=>'已支付',5=>"处理中",6=>"补资料",7=>"完成",10=>"草稿"];
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

	public function sendmail($toAddress="admin@nyis.com",$toName="test",$body="Test Mail",$subject='New Case')
	{
		$mail = new \PHPMailer(true);
		$mail->isSMTP();
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 0;
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
		$mail->addAddress($toAddress, $toName);
		//$mail->addAddress("colakang@gmail.com", $toName);
		//Set the subject line
		$mail->Subject = $subject;
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
		//Replace the plain text body with one created manually
		$mail->AltBody = 'This is a plain-text message body';
		//Attach an image file
		//$mail->addAttachment('images/phpmailer_mini.png');
		$mail->Body = $body;
		//send the message, check for errors
		if (!$mail->send()) {
			return false;
		    //echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
			return true;
		    //echo "Message sent!";
		}
	}

}
