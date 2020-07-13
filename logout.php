<?php
// ini_set('display_errors', "On");
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
require_once './makeLog.php'; //ログファイル書き込み関数を呼び出し
if(isset($_SESSION['sessionname'])) { //セッションがすでにある
	makeLog($_SESSION['sessionname'].' => 正常にログアウト') ;
	unset($_SESSION['sessionname']); //セッションの中身をクリア
}else{
	$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;
	makeLog($url.' => 【エラー】ログインなしでダイレクトアクセスし、ログインページに飛ばされる。') ;
	header( "Location: ./login.php" ) ; //ログインページにとぶ
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0" />
<meta name="robots" content="noindex,nofollow,noarchive" /> <!-- 検索エンジンに登録させない -->
<meta http-equiv="refresh" content=" 1; url=./login.php">
<title>ログアウト</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
</head>
<body>
<div class="contentS">
ログアウトしています。お待ちください・・・。
</div>
</body> 
</html>