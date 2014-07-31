<?php
require 'PHPMailerAutoload.php';
/**
 * Author: CC
 * Date: 10/23/2013
 * [smtp_mail_mp Send mail function for MP]
 * @param  [type] $send_to [receiver's mail address]
 * @param  [type] $subject [mail subject]
 * @param  [type] $body    [mail content]
 * @return [type]          [description]
 */
function smtp_mail_mp($send_to, $subject, $body,$cc_mail) {
	$send_from = "mp@pactera.com";
	//Create a new PHPMailer instance
	$mail = new PHPMailer();
	//Tell PHPMailer to use SMTP
	$mail->IsSMTP();
	//Enable SMTP debugging
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
	$mail->SMTPDebug  = 0;
	//Ask for HTML-friendly debug output
	$mail->Debugoutput = 'html';
	//Set the hostname of the mail server
	$mail->Host       = 'email.pactera.com';
	//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
	//$mail->Port       = 465;
	//Set the encryption system to use - ssl (deprecated) or tls
	//$mail->SMTPSecure = 'ssl';
	//Whether to use SMTP authentication
	$mail->SMTPAuth   = true;

	$mail->CharSet = "UTF-8";
	$mail->Username   = "wuxi\mp";
	//Password to use for SMTP authentication
	$mail->Password   = "CTXS_200611";
	//Set who the message is to be sent from
	$mail->SetFrom($send_from);
	$mail->FromName = "Management Portal Admin";
        $to_email = explode(";", $send_to);
        foreach($to_email as $te_val){
            if(!empty($te_val)){
                $mail->AddAddress($te_val);
            }
        }
	//Set the subject line
	$mail->Subject = $subject;
	$mail->IsHTML(true);
	//Read an HTML message body from an external file, convert referenced images to embedded, convert HTML into a basic plain-text alternative body
	//Replace the plain text body with one created manually
	$mail->Body = $body;
        //Add multiple cc -email addresses
        $cc_mails = explode(';', $cc_mail);
        foreach ($cc_mails as $value) {
            if(!empty($value)){
                $mail->addCC($value);
            }
        }
	//Send the message, check for errors
	if(!$mail->Send()) {
		echo "Mailer Error: " . $mail->ErrorInfo;
	} else {
		//echo "Message sent!";
	}
}
//smtp_mail_mp("chengchong.zhang@pactera.com" ,"Test Mail", "This is just a test");
?>

