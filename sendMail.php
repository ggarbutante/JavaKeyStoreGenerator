<?php
#################################################################################
## (c) Geronimo G Arbutante (ggarbutante@gmail.com). All rights reserved.      ##
#################################################################################

$list = $_POST['emailList'];

$pass =  $_POST['jksPassword'];

$filename = $_POST['filename'];

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

$bodytext = "KeyStore Password = ".$pass;

$email = new PHPMailer();
$email->SetFrom('IT_Support@yourdomain.com', 'My Company IT Support'); //Name is optional
$email->Subject   = 'JKS Generator';
$email->Body      = $bodytext;

$addresses = explode(':',$list);
foreach($addresses as $address){
	$email->AddAddress($address);
}

$file_to_attach = './keystore_files/'.$filename.'/'.$filename.'.jks';

$email->AddAttachment($file_to_attach);

try {
    $email->Send();
    sleep(5);
    echo "\nEmail sent! --crossfingers--\n\n";
} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}

?>
