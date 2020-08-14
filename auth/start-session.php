<?php
include_once(dirname(__FILE__)."/../config.php");
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname);
$json = file_get_contents('php://input');
function GUID(){
if (function_exists('com_create_guid') === true)
{return trim(com_create_guid(), '{}');}
return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}
$data = (array) json_decode($json);
$nonce = GUID();
if (!isset($data['token'])){die();};
if (!isset($data['address'])){die();};
$sql = "INSERT INTO `idena_auth` (nonce,token, addr)
VALUES ('".'signin-'.$nonce."', '".$data['token']."', '".$data['address']."')";
$conn->query($sql);
$conn->close();
header('Content-Type: application/json');
?>
{"success":true,"data":{"nonce":"signin-<?php echo $nonce; ?>"}}
