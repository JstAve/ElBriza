<?php 

  require('../admin/inc/db_config.php');
  require('../admin/inc/essentials.php');
  require("../inc/sendgrid/sendgrid-php.php");

  date_default_timezone_set("Asia/Manila");



  if(isset($_POST['register']))
  {
    $data = filteration($_POST);

    // match password and confirm password field

    if($data['pass'] != $data['cpass']) {
      echo 'pass_mismatch';
      exit;
    }

    // check user exists or not

    $u_exist = select("SELECT * FROM `user_cred` WHERE `email` = ? OR `phonenum` = ? LIMIT 1",
      [$data['email'],$data['phonenum']],"ss");

    if(mysqli_num_rows($u_exist)!=0){
      $u_exist_fetch = mysqli_fetch_assoc($u_exist);
      echo ($u_exist_fetch['email'] == $data['email']) ? 'email_already' : 'phone_already';
      exit;
    }

    // upload user image to server

    $img = uploadUserImage($_FILES['profile']);

    if($img == 'inv_img'){
      echo 'inv_img';
      exit;
    }
    else if($img == 'upd_failed'){
      echo 'upd_failed';
      exit;
    }

    $enc_pass = password_hash($data['pass'],PASSWORD_BCRYPT);


    // -->>> UN-COMMENT the below lines if you want to use email verification

    // -- START
    /* 
      $token = bin2hex(random_bytes(16));
      if(!send_mail($data['email'],$token,"email_confirmation")){
        echo 'mail_failed';
        exit;
      }  
      $query = "INSERT INTO `user_cred`(`name`, `email`, `address`, `phonenum`, `pincode`, `dob`,`profile`, `password`, `token`) VALUES (?,?,?,?,?,?,?,?,?)";
      $values = [$data['name'],$data['email'],$data['address'],$data['phonenum'],$data['pincode'],$data['dob'],$img,$enc_pass,$token];
    */
    // -- END


    // --->>> COMMENT these lines if you are using email verification

    // -- START

    $query = "INSERT INTO `user_cred`(`name`, `email`, `address`, `phonenum`, `pincode`, `dob`,`profile`, `password`, `is_verified`) VALUES (?,?,?,?,?,?,?,?,?)";
    $values = [$data['name'],$data['email'],$data['address'],$data['phonenum'],$data['pincode'],$data['dob'],$img,$enc_pass,'1'];

    // -- END

    if(insert($query,$values,'sssssssss')){
      echo 1;
    }
    else{
      echo 'ins_failed';
    }

  }

  if(isset($_POST['login']))
  {
    $data = filteration($_POST);

    $u_exist = select("SELECT * FROM `user_cred` WHERE `email`=? OR `phonenum`=? LIMIT 1",
    [$data['email_mob'],$data['email_mob']],"ss");

    if(mysqli_num_rows($u_exist)==0){
      echo 'inv_email_mob';
    }
    else{
      $u_fetch = mysqli_fetch_assoc($u_exist);
      if($u_fetch['is_verified']==0){
        echo 'not_verified';
      }
      else if($u_fetch['status']==0){
        echo 'inactive';
      }
      else{
        if(!password_verify($data['pass'],$u_fetch['password'])){
          echo 'invalid_pass';
        }
        else{
          session_start();
          $_SESSION['login'] = true;
          $_SESSION['uId'] = $u_fetch['id'];
          $_SESSION['uName'] = $u_fetch['name'];
          $_SESSION['uPic'] = $u_fetch['profile'];
          $_SESSION['uPhone'] = $u_fetch['phonenum'];
          echo 1;
        }
      }
    }
  }

  if(isset($_POST['forgot_pass']))
{
    $data = filteration($_POST);
    
    $u_exist = select("SELECT * FROM `user_cred` WHERE `email`=? LIMIT 1", [$data['email']], "s");

    if(mysqli_num_rows($u_exist) == 0) {
        echo 'inv_email';
    } else {
        $u_fetch = mysqli_fetch_assoc($u_exist);
        if($u_fetch['is_verified'] == 0) {
            echo 'not_verified';
        } else if($u_fetch['status'] == 0) {
            echo 'inactive';
        } else {
            // Generate a token for account recovery
            $token = bin2hex(random_bytes(16));

            // Email subject and body
            $subject = "Password Reset Request";
            $reset_link = "https://yourdomain.com/reset_password.php?token=" . $token;
            $message = "
                <html>
                <head>
                    <title>Password Reset Request</title>
                </head>
                <body>
                    <p>Hi {$u_fetch['name']},</p>
                    <p>You requested a password reset. Please click the link below to reset your password:</p>
                    <p><a href='{$reset_link}'>Reset Password</a></p>
                    <p>If you didn't request this, please ignore this email.</p>
                    <p>Regards,<br>Your Website Team</p>
                </body>
                </html>
            ";

            // Email headers
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: no-reply@yourdomain.com" . "\r\n";

            // Send the email
            if(!mail($data['email'], $subject, $message, $headers)) {
                echo 'mail_failed';
            } else {
                $date = date("Y-m-d");

                // Update token and expiration in the database
                $query = mysqli_query($con, "UPDATE `user_cred` SET `token`='$token', `t_expire`='$date' 
                    WHERE `id`='{$u_fetch['id']}'");

                if($query) {
                    echo 1;
                } else {
                    echo 'upd_failed';
                }
            }
        }
    }
}

  if(isset($_POST['recover_user']))
  {
    $data = filteration($_POST);
    
    $enc_pass = password_hash($data['pass'],PASSWORD_BCRYPT);

    $query = "UPDATE `user_cred` SET `password`=?, `token`=?, `t_expire`=? 
      WHERE `email`=? AND `token`=?";

    $values = [$enc_pass,null,null,$data['email'],$data['token']];

    if(update($query,$values,'sssss'))
    {
      echo 1;
    }
    else{
      echo 'failed';
    }
  }
  
?>