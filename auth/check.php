<?php
session_start();
define('IN_PHPBB', true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.') , 1);
include_once ("../config.php");
include ($phpbb_root_path . 'common.' . $phpEx);
include_once ('../includes/functions_user.php');
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname);

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0;$i < $length;$i++)
    {
        $randomString .= $characters[rand(0, $charactersLength - 1) ];
    }
    return $randomString;
}
function getstatus($addressBB)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, 'https://api.idena.org/api/identity/' . $addressBB);
    $result = curl_exec($ch);
    curl_close($ch);
    $resultJSON = json_decode($result, true);
    if (isset($resultJSON['result']['state']))
    {
        return $resultJSON['result']['state'];
    }
    else
    {
        return 'Undefined';
    }

}
function doublecheckexist($addressZOO,$conn){
    $sql = "SELECT `address` FROM `phpbb_users` WHERE `address` = '" . $addressZOO . "'LIMIT 1;";
    $result = $conn->query($sql);
    $rowx = $result->fetch_row();
    return isset($rowx[0]);
}
function solvegid($status){
if($status == 'Human'){
return 11;
}elseif($status == 'Verified'){
    return 10;
}elseif($status == 'Newbie'){
    return 8;
}elseif($status == 'Suspended'){
    return 12;
}elseif($status == 'Zombie'){
    return 13;
}elseif($status == 'Undefined'){
    return 16;
}elseif($status == 'Candidate'){
    return 9;
}else{
    return 16;
}
}
function getid($username,$conn){
    $sql = "SELECT `user_id` FROM `phpbb_users` WHERE `username` = '" . $username . "'LIMIT 1;";
    $result = $conn->query($sql);
    $rowx = $result->fetch_row();
    return $rowx[0];
}
function CreateAccount($addressZZ, $conn)
{   
    if(doublecheckexist($addressZZ,$conn) == true){
        header("Location: ../index.php");
        die();
    };
    $Newusername = generateRandomString(15);
    $status = getstatus($addressZZ);
    $gid = solvegid($status);
    if($gid == false){
        header("Location: ../index.php");
        die();
    }
    $user_row = array(
        'username' => $Newusername,
        'user_password' => phpbb_hash('321admin123') ,
        'user_email' => $Newusername . '@idena.io',
        'group_id' => $gid, // by default, the REGISTERED user group is id 2
        'user_lang' => 'en',
        'user_type' => USER_NORMAL,
        'user_ip' => $user->ip,
        'user_regdate' => time() ,
        'address' => $addressZZ,
        'status' => $status,
    );

    // Register user...
    $user_id = user_add($user_row);
    $sql = "SELECT `username` FROM `phpbb_users` WHERE `user_id` = '" . $user_id . "'LIMIT 1;";
    $result = $conn->query($sql);
    $rowx = $result->fetch_row();
    return $rowx[0];
}
if (!isset($_SESSION['token']))
{

    header("Location: ../index.php");
    die();

}
// check token if authenticated and return address
$sql = "SELECT `addr` FROM `idena_auth` WHERE `token` = '" . $_SESSION['token'] . "' AND `authenticated` = '1' LIMIT 1;";
$result = $conn->query($sql);
$rowx = $result->fetch_row();
$address = $rowx[0];
if ($address == '' || $address == null)
{
    header("Location: ../index.php");
    die();
}

$sql = "SELECT `username` FROM `phpbb_users` WHERE `address` = '" . $address . "' LIMIT 1;";
$result = $conn->query($sql);
$rowx = $result->fetch_row();
$username = $rowx[0];
if ($username == '' || $username == null)
{
    $username = CreateAccount($address, $conn);
    $newaccount = true;
}
if ($username == '' || $username == null)
{
    header("Location: ../index.php");
    die();
}

$user->session_begin();
$auth->acl($user->data);
$user->setup();
$GETtype = $request->variable('type', '');
if ($user->data['is_registered'])
{
    header("Location: ../index.php");
    die();
}
else
{

    
     $user->session_kill();

     $result =  $user->session_create(getid($username,$conn), 1,0, 1);
    if ($result)
    {
    unset($_SESSION['token']);
    if($newaccount){
    header("Location: ../memberlist.php?mode=viewprofile&u=".getid($username,$conn));
    die();
    }
    header("Location: ../index.php");
    die();
    }
    else
    {
        unset($_SESSION['token']);
        header("Location: ../index.php");
        die();
    }
}

?>
