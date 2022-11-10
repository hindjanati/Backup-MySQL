<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body id="top">
          <form action="index.php" method="POST">
            <h1>Connexion</h1>
            <input type="text" name="username" id="username" placeholder="Email" required />
            <input type="password" name="password" id="password" placeholder="Mot de passe" required />
            <label for="">
              <?php
              login();
              ?>
            </label>
            <input type="submit" name="btn_login_sub" class="btn" value="Se connecter" />
          </form>  
</body>

</html>

<?php

session_start();
use PHPMailer\PHPMailer\PHPMailer;


// function to send the email 
function sendmail(){
  $namef = '';

  foreach (new DirectoryIterator('backupSQL') as $fileInfo) {
    if($fileInfo->isDot()) continue;
    $namef .= $fileInfo->getFilename() . "<br>\n";
  }
  $name = "name"; 
  $to = "email to send in";  
  $subject = "subject";
  $body = "your message";
  $from = "your email";
  $password = "password";   // not your real password 

  // declaration
  require_once "PHPMailer/PHPMailer.php";
  require_once "PHPMailer/SMTP.php";
  require_once "PHPMailer/Exception.php";
  $mail = new PHPMailer();

  $mail->isSMTP();
  $mail->Host = "smtp.gmail.com"; 
  $mail->SMTPAuth = true;
  $mail->Username = $from;
  $mail->Password = $password;
  $mail->Port = 587;  
  $mail->SMTPSecure = "tls";
  $mail->smtpConnect([
     'ssl' => [
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true
      ]
  ]);

  $mail->isHTML(true);
  $mail->setFrom($from, $name);
  $mail->addAddress($to); 
  $mail->Subject = ("$subject");
  $mail->Body = $body;
  if ($mail->send()) {
    echo '<script>alert("the mail has sent to your email !")</script>';
  } else {
      echo "Something is wrong: <br><br>" . $mail->ErrorInfo;
  }
}

// the login function

function login()
{

  if (isset($_REQUEST['btn_login_sub'])) {

    $login_email = $_REQUEST['username'];
    $login_pass = $_REQUEST['password'];

    $con = mysqli_connect('localhost', 'root', '', 'bdName');

      $rqt_valid_login = mysqli_query($con, "SELECT * FROM `login` WHERE username = '" . $login_email . "' and password = '" . $login_pass . "';");

      if (mysqli_num_rows($rqt_valid_login) >= 1) {
        $rqt =  $con->prepare("SELECT * FROM `login` WHERE username = '" . $login_email . "' and password = '" . $login_pass . "';");
        $rqt->execute();
        $rqt1 = $rqt->get_result();
        while ($rows = $rqt1->fetch_assoc()) {
          $_SESSION["userID"] = $rows["id"];
          $_SESSION["username"] = $rows["username"];
        }
      $today = date("y.m.d"); 
      
      $rqt_test_login =  mysqli_query($con, "SELECT * FROM `firstlogin` WHERE userID = '" . $_SESSION["userID"] . "' and date = '" . $today . "';");

      if (mysqli_num_rows($rqt_test_login) < 1) {
        include 'backup.php';
        sendmail();
        $annonce_query = mysqli_query($con, "INSERT INTO `firstlogin` (`id`, `username`, `date`, `userID`) VALUES (NULL, '" . $_SESSION["username"] . "',  '$today' ,'" . $_SESSION["userID"] . "');");
      }

    } else {
      echo "your email or password not correct";
    }
  }
}

?>
