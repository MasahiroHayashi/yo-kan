<?php
// ini_set('display_errors', "On");
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars定義関数
require_once './makeLog.php'; //ログファイル書き込み関数を呼び出し
if(!isset($_SESSION['sessionname'])) { //ログインセッションなし
	header( "Location: ./login.php" ) ; //ログインページにとぶ
	exit;
}
// サーバーキャッシュのクリアのための処理
$fileName = "accountData.php" ;
copy($fileName, $fileName.'copy'); // コピーを作成
unlink($fileName);                 // 原本を削除
copy($fileName.'copy', $fileName); // コピーから原本を再作成
unlink($fileName.'copy');          // コピーを削除
require_once 'accountData.php'; //accountData.phpを呼び出し
//管理者でない
if(!($_SESSION['sessionname']===$admin_info[0]['id'])){
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
<title>システムの初期化</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
</head>
<body>
<div class="contentS">
<?php
if(!isset($_POST['reset'])){
				
	if (!isset($_SESSION['token'])) { // ワンタイムトークン生成
		$_SESSION['token'] = sha1(random_bytes(30));
	}
		
	echo "管理者アカウント ".hsc($_SESSION['sessionname'])." でログイン中です。" ;
	echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
	echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
	echo "<hr>"."\n";			
	echo "以下のボタンを押すとこれまでの操作ログがリセットされます。" ;
	echo '<form action="resetting.php" name="system_reset1" method="POST">'."\n";
	echo '<input type="hidden" name="reset" value="log_reset" >'."\n"; 
	echo '<input type="hidden" name="token" value="'.$_SESSION["token"].'" >'."\n"; //セッショントークン
	echo '<input type="button" id="sys_reset1" value="操作ログのリセット" onclick="timer1()"></form>'."\n";
	echo "<hr>"."\n";
	echo "以下のボタンを押すと登録したデータがすべて消去されます。<br>データのアクセス数の記録もすべて消去されます。本当によろしいですか？" ;
	echo '<form action="resetting.php" name="system_reset2" method="POST">'."\n";
	echo '<input type="hidden" name="reset" value="data_reset" >'."\n"; 
	echo '<input type="hidden" name="token" value="'.$_SESSION["token"].'" >'."\n"; //セッショントークン
	echo '<input type="button" id="sys_reset2" value="登録したデータをすべて消去" onclick="timer2()"></form>'."\n";
	echo "<hr>"."\n";
	echo "以下のボタンを押すとシステム全体が完全に初期化されます。<br>登録したデータ及び操作ログはすべて消去され、登録したアカウント（管理者含む）もすべて削除されます。<br>本当によろしいですか？" ;
	echo '<form action="resetting.php" name="system_reset3" method="POST">'."\n";
	echo '<input type="hidden" name="reset" value="all_reset" >'."\n"; 
	echo '<input type="hidden" name="token" value="'.$_SESSION["token"].'" >'."\n"; //セッショントークン
	echo '<input type="button" id="sys_reset3" value="システム全体の完全な初期化" onclick="timer3()"></form>'."\n";
	echo "<hr>"."\n";
	echo '<a href="redirect.html">管理画面に戻る</a>'; 

}else{
	
	if($_POST["reset"]==="log_reset"){
						
		// セッショントークンの確認
		$token = $_POST['token']; //tokenを変数に入れる
		if(!(hash_equals($token, $_SESSION['token']) && !empty($token))) { 
			
			$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;
			makeLog($url.' => 【不正アクセス注意】何者かが不正なアクセスにより、システムの初期化を試みたが失敗') ;
			exit("不正アクセスの可能性があります。");
		}
		//log の中身を再帰的に全部削除
		if (file_exists('./log')) {
			rmdirAll('log');
		}
		echo "管理者アカウント ".hsc($_SESSION['sessionname'])." でログイン中です。" ;
		echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
		echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
		echo "<hr>"."\n";
		echo '操作ログをリセットしました。<br>';
		echo '<hr><a href="redirect.html">管理画面に戻る</a>'; 
		makeLog($_SESSION['sessionname'].' => 操作ログをリセット') ; 
		
	}else if($_POST["reset"]==="data_reset"){
		
		//data の中身を再帰的に全部削除
		if (file_exists('./data')) {
			rmdirAll('data');
		}
		if (file_exists('./allDataList.php')) {
			unlink('./allDataList.php');
		}
		echo "管理者アカウント ".hsc($_SESSION['sessionname'])." でログイン中です。" ;
		echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
		echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
		echo "<hr>"."\n";
		echo 'データがすべて消去されました。<br>';
		echo '<hr><a href="redirect.html">管理画面に戻る</a>'; 
		makeLog($_SESSION['sessionname'].' => データの初期化（全消去）を行った') ;
		
	}else if($_POST["reset"]==="all_reset"){
		
		unset($_SESSION['sessionname']); //セッションの中身をクリア
		unset($_SESSION['loginFailure']); //セッションの中身をクリア

		//data の中身を再帰的に全部削除
		if (file_exists('./data')) {
			rmdirAll('data');
		}
		//log の中身を再帰的に全部削除
		if (file_exists('./log')) {
			rmdirAll('log');
		}
		//img_uploaded の中身を再帰的に全部削除
		if (file_exists('./img_uploaded')) {
			rmdirAll('img_uploaded');
		}
		if (file_exists('./allDataList.php')) {
			unlink('./allDataList.php');
		}
		if (file_exists('./accountData.php')) {
			unlink('./accountData.php'); 
		}
		echo 'システム全体が完全に初期化されました。<br>登録したデータ及び操作ログはすべて消去され、登録したアカウント（管理者含む）もすべて削除されました。';
	}
}

function rmdirAll($dir) {
	// 指定されたディレクトリ内の一覧を取得
	$res = glob($dir.'/*');
 	// 一覧をループ
	foreach ($res as $f) {
		// is_file() を使ってファイルかどうかを判定
		if (is_file($f)) {
			// ファイルならそのまま出力
			unlink($f);
		} else {
			// ディレクトリの場合（ファイルでない場合）は再度rmdirAll()を実行
			rmdirAll($f);
		}
	}
	// 中身を削除した後、本体削除
	rmdir($dir);
}
?>
</div>

<script type="text/javascript">
function timer1(){ 
	document.getElementById("sys_reset1").disabled = true ;
	document.getElementById("sys_reset1").value= "お待ちください..." ;
	document.getElementById("sys_reset2").disabled = true ;
	document.getElementById("sys_reset2").value= "お待ちください..." ;
	document.getElementById("sys_reset3").disabled = true ;
	document.getElementById("sys_reset3").value= "お待ちください..." ;
	var timersubmit = function(){
		document.system_reset1.submit();
	}
	setTimeout(timersubmit, 3000);
}
function timer2(){ 
	document.getElementById("sys_reset1").disabled = true ;
	document.getElementById("sys_reset1").value= "お待ちください..." ;
	document.getElementById("sys_reset2").disabled = true ;
	document.getElementById("sys_reset2").value= "お待ちください..." ;
	document.getElementById("sys_reset3").disabled = true ;
	document.getElementById("sys_reset3").value= "お待ちください..." ;
	var timersubmit = function(){
		document.system_reset2.submit();
	}
	setTimeout(timersubmit, 3000);
}
function timer3(){ 
	document.getElementById("sys_reset1").disabled = true ;
	document.getElementById("sys_reset1").value= "お待ちください..." ;
	document.getElementById("sys_reset2").disabled = true ;
	document.getElementById("sys_reset2").value= "お待ちください..." ;
	document.getElementById("sys_reset3").disabled = true ;
	document.getElementById("sys_reset3").value= "お待ちください..." ;
	var timersubmit = function(){
		document.system_reset3.submit();
	}
	setTimeout(timersubmit, 3000);
}
</script>
</body> 
</html>