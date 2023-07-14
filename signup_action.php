<?php

session_start();
include("../include/connect.php");
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
if (!IS_AJAX) {
    //Request identified as not ajax request  
    echo "SORRY";
    die();
}


if (isSet($_POST)) {
   

    global $webname;
    global $email;
    global $pass;
    global $phone;
    global $phonecode;
    global $phonenumber;
    global $str;
    global $str_error;
    $webname = strtolower(mysqli_real_escape_string($mysqli, $_POST['store']));
    $phonecode = mysqli_real_escape_string($mysqli, $_POST['countrycode']);
    $phone = mysqli_real_escape_string($mysqli, $_POST['phone']);
    $pass = mysqli_real_escape_string($mysqli, $_POST['pwd']);
    $phonenumber = mysqli_real_escape_string($mysqli, $_POST['phone_number']);
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);

    function checkExists($phone, $phonecode) {
        global $mysqli;
        global $str_error;
        $str_sql = "SELECT *  
           FROM user  
           WHERE phone='" . $phone . "' and callingcode='" . $phonecode . "'";
        //echo $str_sql;  
        $result = $mysqli->query($str_sql) or trigger_error($mysqli->error . " [$str_sql]");
        if ($result->num_rows > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    function clean($string) {
        $string = str_replace(' ', '', $string);
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }

    function checkWebname($webname) {
        global $mysqli;
        global $str_error;
        global $webnamecheck;
        $cleanwebname = clean($webname);
        $str_sql = "SELECT webname  
           FROM user  
           WHERE webname='$cleanwebname' ";
        /* echo $str_sql; */
        $result = $mysqli->query($str_sql) or trigger_error($mysqli->error . " [$str_sql]");
        if ($result->num_rows > 0) {
            $webnamecheck = 1;
        } else {
            $webnamecheck = 0;
        }
        return $webnamecheck;
    }

    if (checkExists($phone, $phonecode)):
        $str_error .= "User with same phone number already exists !";
    endif;

    if (checkWebname($webname)):
        $str_error .= "Store Name already exists !";
    endif;

     function checkExistspsi($phonenumber) {
        global $mysqli;
        global $str_error;
        $str_sql = "SELECT count(mobilenumber) as cnt FROM psi_administrator WHERE mobilenumber='" . $phonenumber . "'";
        $result = $mysqli->query($str_sql) or trigger_error($mysqli->error . " [$str_sql]");
        if (($result->cnt) ==1) {
            return 1;
        } else {
            return 0;
        }
    }

    function updateTable() {
       global $str_error;
        global $mysqli;
        global $webname;
        global $email;
        global $pass;
        global $phone;
        global $phonecode;
        global $phonenumber;

        $time = time();
        $hash = md5($phone . $time);
         $str = mt_rand(1000, 9999);



        //need to check not tested  
        $str_sql = "SELECT c.country_id, cc.currency_code, cc.currency_symbol  
  FROM country c INNER JOIN countrycurrency cc ON (c.iso2 = cc.iso_alpha2)  
  WHERE (c.calling_code = '" . $phonecode . "')";
        $result = $mysqli->query($str_sql);
        while ($row = $result->fetch_array(MYSQLI_BOTH)) {
            $currency_code = $row['currency_code'];
            $currency_symbol = $row['currency_symbol'];
        }
        $psiname= $webname.".shopygo.com";
        $str_sql = "INSERT INTO user (
           callingcode,  
           phone,       
           mobilenumber,       
           password,  
           createdOn,  
           uniqueId,  
           countryId,currency_code,currency_symbol,smartphone,webname,email,reg_domain,becomeaseller,user_type,otp,psi_name,seller_setup_status)  
           VALUES ( 
           '$phonecode',  
           '$phone',  
           '$phonenumber',            
           '" . md5($pass) . "',  
           NOW(),  
           '$hash', 
           '$country','$currency_code','$currency_symbol','N','$webname','$email','shopygo.com','1','S','$str','$psiname','1')";
        // echo $str_sql ;exit;  
        $mysqli->query($str_sql);
        $lastID = $mysqli->insert_id;

        if($lastID >0){
			
         //createopt  

            $_SESSION['optmobile'] = $phonenumber;
            
            if($phonenumber=="915555555555" || $phonenumber=="916666666666"|| $phonenumber=="917777777777" || $phonenumber=="918888888888"){
                $otpphonenumber="919003017999,917558949855,8870922164,9400824616,8056316578";

            }else{
               $otpphonenumber= $phonenumber;
            }
            
               
                    $authKey = "126313AQqzLcXZ57e68eac";
                    /* Multiple mobiles numbers separated by comma  
                      $mobileNumber = $phonenumbers; */
                     $mobileNumber=$otpphonenumber;
                    
                    /* Sender ID,While using route4 sender id should be 6 characters long.  */
                   
                        $senderId = "SHOPYG";
                     
                   
                    
                    /*$message = urlencode("Thanks for registering. Your One Time Password (OTP) is $str");
                    $messageforlog = "Thanks for registering. Your One Time Password (OTP) is $str"; */
                    $message=urlencode("Your OTP for new registration is $str\nFrom shopygo.com\nPowered by Shopygo and Streetbell");
                    $messageforlog="Your OTP for new registration is $str\nFrom shopygo.com\nPowered by Shopygo and Streetbell";
                    //Your message to send, Add URL encoding here.  
                    //$message = urlencode("Test message");  
                    //Define route   
                    $route = 4;
                    $countrycode = 0;
                    //Prepare you post parameters  
                    $postData = array(
                        'authkey' => $authKey,
                        'mobiles' => $mobileNumber,
                        'message' => $message,
                        'sender' => $senderId,
                        'route' => $route,
                        'DLT_TE_ID'=> "1207161770323863654"
                       /* 'country' => $countrycode*/
                    );
                    //API URL  
                   /* $url = "https://control.msg91.com/sendhttp.php";*/
                      $url = "http://api.msg91.com/api/sendhttp.php";
                    // init the resource  
                    $ch = curl_init();
                    curl_setopt_array($ch, array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $postData
                            //,CURLOPT_FOLLOWLOCATION => true  
                    ));
                    //Ignore SSL certificate verification  
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    //get response  
                    $output = curl_exec($ch);
                    //Print error if any  
                    if (curl_errno($ch)) {
                        echo 'error:' . curl_error($ch);
                    }
                    curl_close($ch);
                    //echo $output;  
           
        
        $sql = "INSERT INTO smslog (createdon,psiname, mobilenumber,message,smstype) VALUES (NOW(),'shopygo.com', '$phonenumber','$messageforlog','0')";
        $res = $mysqli->query($sql);
        }
    if (checkExistspsi($phonenumber)):
        $str_error .= "This mobilenumber is already registered as Admin. Please contact support@shopygo.com";
    else:
        $psiname= $webname.".shopygo.com";
        $str_sqla = "INSERT INTO psi_administrator (
                    email,
                    password,
                    createdOn,
                    uniqueId,
                    user_type,
                    callingcode,  
                    phone,
                    mobilenumber,
                    psi_domain
                    )
                    VALUES (
                    '$email',
                    '" . md5($pass) . "',
                    NOW(),
                    '" . md5($phonenumber) . "',
                    'A',
                    '$phonecode',  
                    '$phone',
                    '$phonenumber',
                    '$psiname')";

        $mysqli->query($str_sqla);
    endif;

    }

    if ($str_error == ""):
        updateTable(); // update table  
      if($str_error !=""){
           //echo "<div ><strong>TIP:</strong>";
        echo $str_error;
        //echo "</div>";
      }else{
        echo "success";
      }
    else:
       // echo "<div ><strong>TIP:</strong>";
        echo $str_error;
       // echo "</div>";
    endif;
}
?>  