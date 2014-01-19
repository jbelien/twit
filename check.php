<?php
date_default_timezone_set('Europe/Brussels');
require_once('request.php');

$user = 'jbelien';

$ini = parse_ini_file('settings.ini', TRUE);

$time = date('Y-m-d H:i:s');

$req = request($ini['oauth'], 'https://api.twitter.com/1.1/users/show.json', array('screen_name' => $user));

$id = $req->id;
$followers_count = $req->followers_count;
$friends_count = $req->friends_count;
$statuses_count = $req->statuses_count;

$req = request($ini['oauth'], 'https://api.twitter.com/1.1/followers/ids.json', array('user_id' => $id));
$followers = implode(',', $req->ids);

$req = request($ini['oauth'], 'https://api.twitter.com/1.1/friends/ids.json'  , array('user_id' => $id));
$friends = implode(',', $req->ids);

$qsz  = "INSERT INTO `check` VALUES(";
$qsz .= $id;
$qsz .= ", 0";
$qsz .= ", '".$time."'";
$qsz .= ", ".$statuses_count;
$qsz .= ", ".$followers_count;
$qsz .= ", '".$followers."'";
$qsz .= ", ".$friends_count;
$qsz .= ", '".$friends."'";
$qsz .= ")";

$dblink = new MySQLi($ini['mysql']['host'], $ini['mysql']['username'], $ini['mysql']['passwd'], $ini['mysql']['dbname']);
if ($dblink->connect_error) trigger_error('Erreur de connexion : '.$dblink->connect_error);
$dblink->query($qsz) or trigger_error($dblink->error);
$dblink->close();

exit(0);