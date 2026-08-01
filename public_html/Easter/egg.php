<?php
session_start();

if(isset($_GET['reset'])){
    session_destroy();
    echo "Session cleared.";
    exit();
}

$egg_code = $_GET['code'] ?? '';
echo $egg_code;
if(!$egg_code){
    echo "No egg code found.";
    exit();
}
// if player not logged in, show intro
//if(!isset($_SESSION['hunter_id'])){
//echo "hello";
    include('EasterIntro.php');
  //  exit();

//}
// if player is logged in
//echo "Welcome back! You scanned egg: " . htmlspecialchars($egg_code);
?>
