<?php 
include_once ("./public_html/config.php");
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname);
function getstatus($addressBB)
{
    if(strlen($addressBB) < 20){
    return 'Undefined';
    }
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
function solvegid($status)
{
    if ($status == 'Human')
    {
        return 11;
    }
    elseif ($status == 'Verified')
    {
        return 10;
    }
    elseif ($status == 'Newbie')
    {
        return 8;
    }
    elseif ($status == 'Suspended')
    {
        return 12;
    }
    elseif ($status == 'Zombie')
    {
        return 13;
    }
    elseif ($status == 'Undefined')
    {
        return 16;
    }
    elseif ($status == 'Candidate')
    {
        return 9;
    }
    elseif ($status == 'Killed')
    {
        return 15;
    }
    else
    {
        return 16;
    }
}

$sql = "SELECT * FROM `phpbb_users` where `user_id` > 48;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    
  while($row = $result->fetch_assoc()) {
    $status = getstatus($row['address']);
    $oldGID = $row['group_id'];
    $newGID = solvegid($status);
    $userID = $row['user_id'];
    $address = $row['address'];

    $sql1 = "UPDATE `phpbb_users` SET `group_id` = '".$newGID."' where  `address` = '".$address."';";
$conn->query($sql1);


$sql2 = "UPDATE `phpbb_user_group` SET `group_id` = '".$newGID."' where  `user_id` = '".$userID."';";
$conn->query($sql2);

  }}


?>