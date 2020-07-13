<?php
// ini_set('display_errors', "On");
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars定義関数
require_once './makeLog.php'; //ログファイル書き込み関数を呼び出し
if (!isset($_SESSION['token'])) { // ワンタイムトークン生成
	$_SESSION['token'] = sha1(random_bytes(30));
}
if(!isset($_SESSION['sessionname'])) { //セッションがない場合
	// ログ記録
	$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;
	makeLog($url.' => 【エラー】ログインなしでダイレクトアクセスし、ログインページに飛ばされる') ;
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
<title>データの修正</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
</head>
<body>
<div class="contentS">
<?php
// サーバーキャッシュのクリアのための処理
$fileName = "accountData.php" ;
copy($fileName, $fileName.'copy'); // コピーを作成
unlink($fileName);                 // 原本を削除
copy($fileName.'copy', $fileName); // コピーから原本を再作成
unlink($fileName.'copy');          // コピーを削除
	
require_once './accountData.php'; //accountData.phpを呼び出し

//管理者でログインした場合
if($_SESSION['sessionname']===$admin_info[0]['id']){

	echo "管理者アカウント ".hsc($_SESSION['sessionname'])." でログイン中です。" ;
	echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
	echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
	echo "<hr>"."\n";

//ユーザーでログインした場合
}else{
	for ($i=0; $i<count($user_info); $i++) {
		if($user_info[$i]['id'] === $_SESSION['sessionname'] ){
			echo hsc($_SESSION['sessionname'])." でログイン中です。" ;
			echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
			echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
			echo "<hr>"."\n";
		}
	}
}

if(file_exists ('./allDataList.php')){ // allDataList.php が「ある」＝「初回でない」場合
	
	// サーバーキャッシュのクリアのための処理
	$fileName = "allDataList.php" ;
	copy($fileName, $fileName.'copy'); // コピーを作成
	unlink($fileName);                 // 原本を削除
	copy($fileName.'copy', $fileName); // コピーから原本を再作成
	unlink($fileName.'copy');          // コピーを削除
	
	require_once './allDataList.php'; //allDataList.phpを呼び出し 

	echo '<h2>データの修正</h2>';
	echo '<div style="font-size:small;">';
	echo '<form action="dataChange2.php" name="change" method="post" style="display:inline">'."\n";
	echo '<input type="hidden" name="token" value="'.$_SESSION["token"].'" >'."\n"; //セッショントークン

	if($_SESSION['sessionname']===$admin_info[0]['id']) { //管理者の場合

		for ($i=0; $i<count($alldata); $i++) { //全アカウント分を表示
			echo '<input type="radio" name="chan" value="'.$alldata[$i]['num'].'">';
			echo 'No.'.$alldata[$i]['num'];
			echo '　登録者:'.hsc($alldata[$i]['id']);
			echo '　データ名:'.hsc($alldata[$i]['dataname']);
			echo '　ファイル名:'.hsc($alldata[$i]['filename']);
			echo '<br>'."\n";
		}
	}else{ //管理者以外の場合

		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]['id']===$_SESSION['sessionname']){ //自己アカウント分のみを表示
				echo '<input type="radio" name="chan" value="'.$alldata[$i]['num'].'">';
				echo 'No.'.$alldata[$i]['num'];
				echo '　登録者:'.hsc($alldata[$i]['id']);
				echo '　データ名:'.hsc($alldata[$i]['dataname']);
				echo '　ファイル名:'.hsc($alldata[$i]['filename']);
				echo '<br>'."\n";
			}
		}
	}
	echo '</div><br>';
	if($i > 0){
		echo '<input type="button" id="changebutton" value="チェックを入れたデータの修正" onclick="timer1()"></form>'."\n";
	}
	
}else{
	echo '<h3>データの削除</h3>';
	echo "登録されたデータがありません。<br>"."\n";
}
echo "<hr>"."\n";
echo '<a href="redirect.html">管理画面に戻る</a>'; 
?>
</div>
<script>
function timer1(){ 
	document.getElementById("changebutton").disabled = true ;
	document.getElementById("changebutton").value= "お待ち下さい..." ;
	var timersubmit = function(){
		document.change.submit();
	}
	setTimeout(timersubmit, 100);
}
</script>
</body> 
</html>