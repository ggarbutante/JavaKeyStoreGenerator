<?php
#################################################################################
## (c) Geronimo G Arbutante (ggarbutante@gmail.com). All rights reserved. ##
#################################################################################

## JavaScript

echo "
<script type=\"text/javascript\">

var addid = 0;

var addInput = function() {
    
    var addList = document.getElementById('addlist');
    var docstyle = addList.style.display;
    if (docstyle == 'none') addList.style.display = '';

    addid++;
    
    var text = document.createElement('div');
    text.id = 'additem_' + addid;
    text.innerHTML = \"<input type=\'text\' value='' class='emailRecepient_class' name='items[]' style='padding:1px;' /> <a href='javascript:void(0);' onclick='addInput(\" + addid + \")' id='addlink_\" + addid + \"'>add</a> | <a href='javascript:void(0);' onclick='removeInput(\" + addid + \")' id='removelink_\" + addid + \"'>remove</a>\";

    addList.appendChild(text);
}

function removeInput(id){
    var addList = document.getElementById('addlist');
    var text = document.getElementById('additem_'+id);
    var divChild = document.getElementById('addlist').childElementCount;
     if(divChild > 1){
	addList.removeChild(text);
     }
}

function sendEmail(pass,domainName){
    var jksPassword = pass;
    var fileName = domainName;
    var emailList = document.getElementsByClassName('emailRecepient_class');
    var arrayLengthEmailList = emailList.length;
    var recepientList = 'NULL';
    for (var i = 0; i < arrayLengthEmailList; i++) {
    	recepientList = recepientList+':'+emailList[i].value;	
    }
    var xmlhttp = new XMLHttpRequest();

    xmlhttp.onreadystatechange = function() {
   	 if (this.readyState == 4 && this.status == 200) {
	    document.getElementById('response_id').innerHTML = this.responseText;
  	 }
    }   
    document.getElementById('response_id').innerHTML = '<img src=\"./sendingMail.gif\" style=\"width:300px;height:100px;\" />';
    xmlhttp.open('POST','sendMail.php',true);
   // xmlhttp.setRequestHeader('Content-type', 'application/json');
    recepientList = recepientList.replace('NULL:','');
    xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xmlhttp.send('filename='+fileName+'&jksPassword='+jksPassword+'&emailList='+recepientList);
//alert(emailList.length);
//alert(recepientList);
}


</script>
";
## End JavaScript



if(isset($_POST["projectDomainName"])){
   $projectDomainName = $_POST['projectDomainName'];
   $projectUpload = shell_exec("mkdir /var/www/html/it-tools/tools/javakeystore/uploads/".$projectDomainName);
   $projectKeyStore = shell_exec("mkdir /var/www/html/it-tools/tools/javakeystore/keystore_files/".$projectDomainName);
//   echo $projectUpload;
//   echo $projectKeyStore;
}	

if(isset($_POST["keystorePass"])){
    $keystorePass = $_POST['keystorePass'];
}

if(isset($_POST["certPaste"])){
    $certPaste = $_POST['certPaste'];
}
if(isset($_POST["keyPaste"])){
    $keyPaste = $_POST['keyPaste'];
}

echo '<pre>';
#echo 'Project Name = '.$projectDomainName;
#echo '<br/>';
#echo 'Keystore Password = '.$keystorePass;
#echo '<br/>';
$tempKeyPass = "123Welcome123";
#echo 'Temporary Pass = '.$tempKeyPass;
#echo '<br/>';
#echo '<pre>CERT = <br/>'.$certPaste.'</pre>';
#echo '<br/>';
#echo '<pre>KEY = <br/>'.$keyPaste.'</pre>';

#echo '<pre>';
if(isset($_POST['submit'])){
        $fileCERT   	= $_FILES['certToUpload']['name'];  
        $temp_nameCERT  = $_FILES["certToUpload"]['tmp_name'];  
	$fileKEY    	= $_FILES['keyToUpload']['name'];        
	$temp_nameKEY	= $_FILES["keyToUpload"]['tmp_name'];

	if (isset($fileCERT) and !empty($fileCERT)){
            $uploaddir = '/var/www/html/it-tools/tools/javakeystore/uploads/'.$projectDomainName.'/';      
            if(move_uploaded_file($temp_nameCERT, $uploaddir.$fileCERT)){
                echo 'CERT File uploaded successfully!<br/>';
		$cert = $uploaddir.$fileCERT;
            }
        }
	elseif (isset($certPaste) and !empty($certPaste)){
	    $uploaddir = '/var/www/html/it-tools/tools/javakeystore/uploads/'.$projectDomainName.'/';
	    $cert = $uploaddir.$projectDomainName.'CERT.crt';
	    file_put_contents($cert,$certPaste);
	} 
	else {
            echo 'You should select a CERT file to upload !!<br/>';
        }
   	if(isset($fileKEY) and !empty($fileKEY)){
            $uploaddir = '/var/www/html/it-tools/tools/javakeystore/uploads/'.$projectDomainName.'/';
            if(move_uploaded_file($temp_nameKEY, $uploaddir.$fileKEY)){
                echo 'KEY File uploaded successfully!<br/>';
		$key = $uploaddir.$fileKEY;
            }
        } 
	elseif (isset($keyPaste) and !empty($keyPaste)){
            $uploaddir = '/var/www/html/it-tools/tools/javakeystore/uploads/'.$projectDomainName.'/';
            $key = $uploaddir.$projectDomainName.'KEY.pem';
            file_put_contents($key,$keyPaste);
        }
	else {
            echo 'You should select a KEY file to upload !!<br/>';
        }

  
   }

#echo 'Here is some more debugging info:';
#print_r($_FILES);

print "</pre>";

echo "<pre>";

$sourcekeystore = "/var/www/html/it-tools/tools/javakeystore/keystore_files/".$projectDomainName."/sourcekeystore.p12";
//echo "<br/>";
$destkeystore = "/var/www/html/it-tools/tools/javakeystore/keystore_files/".$projectDomainName."/".$projectDomainName.".jks";
//echo "<br/>";
ini_set("expect.timeout", 30);

### Create source keystore..
$stream = fopen("expect://openssl pkcs12 -export -in ".$cert." -inkey ".$key." -certfile ".$cert." -out ".$sourcekeystore, "r");

$cases = array(
    // array(pattern, value to return if pattern matched)
    array("Enter Export Password:", "askPassword"),
    array("Verifying - Enter Export Password:",  "verifyPassword")
);

while (true) {
    switch (expect_expectl($stream, $cases)) {
        case "askPassword":
            fwrite($stream, $tempKeyPass."\n");
            break;
        case "verifyPassword":
            fwrite($stream, $tempKeyPass."\n");
            break;
        case EXP_TIMEOUT:
        case EXP_EOF:
            break 2; // break both the switch statement and the while loop
        default:
            die ("Error has occurred!\n");
    }
}
#fclose($stream);
### -- End creating source keystore

### Create destination keystore JKS
$stream = fopen("expect://keytool -importkeystore -srckeystore ".$sourcekeystore." -srcstoretype pkcs12 -destkeystore ".$destkeystore." -deststoretype JKS", "r");

$cases = array(
    // array(pattern, value to return if pattern matched)
    array("Enter destination keystore password:", "askDestPassword"),
    array("Re-enter new password:",  "reEnterPassword"),
    array("Enter source keystore password:",  "askSourcePassword")
);

while (true) {
    switch (expect_expectl($stream, $cases)) {
        case "askDestPassword":
            fwrite($stream, $keystorePass."\n");
            break;
        case "reEnterPassword":
            fwrite($stream, $keystorePass."\n");
            break;
	case "askSourcePassword":
	    fwrite($stream, $tempKeyPass."\n");
            break;
        case EXP_TIMEOUT:
        case EXP_EOF:
            break 2; // break both the switch statement and the while loop
        default:
            die ("Error has occurred!\n");
    }
}
### -- End creating destination keystore JKS

### Change Keystore Alias

$newAlias = str_replace(".","_",$projectDomainName);
//sleep(1);
if (file_exists($destkeystore)) {
   // echo "\nThe file $projectDomainName exists.";
  //  echo "<br/>";
} else {
    echo "\nThe file $projectDomainName does not exist.";
    echo "<br/>";
}

$stream = fopen("expect://keytool -keystore ".$destkeystore." -changealias -alias 1 -destalias ".$newAlias, "r");

$cases = array(
    // array(pattern, value to return if pattern matched)
    array("Enter keystore password:", "askDestPassword"),
    array("Enter key password for <1>", "askPrevKey")
);

while (true) {
    switch (expect_expectl($stream, $cases)) {
        case "askDestPassword":
            fwrite($stream, $keystorePass."\n");
            break;
	case "askPrevKey":
            fwrite($stream, $tempKeyPass."\n");
            break;
        case EXP_TIMEOUT:
        case EXP_EOF:
            break 2; // break both the switch statement and the while loop
        default:
            die ("Error has occurred!\n");
    }
}

### -- End changing keystore alias

### -- Change private key password

#$stream = fopen("expect://keytool -keystore ".$destkeystore." -changealias -alias 1 -destalias ".$newAlias, "r");
$stream = fopen("expect://keytool -keypasswd -alias ".$newAlias." -keystore ".$destkeystore, "r");

$cases = array(
    // array(pattern, value to return if pattern matched)
    array("Enter keystore password:", "askDestPassword"),
    array("Enter key password for <".$newAlias.">", "askPrevKey"),
        array("New key password for <".$newAlias.">:", "askNewKey"),
        array("Re-enter new key password for <".$newAlias.">:", "askNewKeyAgain")
);

while (true) {
    switch (expect_expectl($stream, $cases)) {
        case "askDestPassword":
            fwrite($stream, $keystorePass."\n");
            break;
                case "askPrevKey":
            fwrite($stream, $tempKeyPass."\n");
            break;
                case "askNewKey":
            fwrite($stream, $keystorePass."\n");
            break;
                case "askNewKeyAgain":
            fwrite($stream, $keystorePass."\n");
            break;
        case EXP_TIMEOUT:
        case EXP_EOF:
            break 2; // break both the switch statement and the while loop
        default:
            die ("Error has occurred!\n");
    }
}


### -- End changing key password




fclose($stream);

if (file_exists($destkeystore)) {
//    echo "<br/>";
//    echo "The file $destkeystore exists";
//    echo "<br/>";
    $keystoreDownload = '/it-tools/tools/javakeystore/keystore_files/'.$projectDomainName.'/'.$projectDomainName.'.jks';
//    echo "<br/>";
    echo "<a href= $keystoreDownload download>Download JKS file here!</a>";
    echo "<br/><br/>";
    echo "or EMAIL TO:";
    echo "<div id='addlist' class='alt1' style='padding:5px;'></div>";
    echo "
	<script type=\"text/javascript\">
		addInput();
	</script>
	";
    echo "<button class='submitEmail' type='button style='padding:5px; onclick='sendEmail(\"$keystorePass\",\"$projectDomainName\")''>SEND</button><br/><br/><br/>";
    echo "<div id='response_id' class='response_class' style='padding:5px; background-color:white;'></div>";
   // echo "<a href='covidgenetics.com.jks' download>Download Here!</a>";
} else {
    echo "<br/>";
    echo "The file $destkeystore does not exist";
}

echo "</pre>";
?>
