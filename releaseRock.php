<?php
// ini_set('display_errors', "On");
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars定義関数
require_once './makeLog.php'; //ログファイル書き込み関数を呼び出し
if(!isset($_SESSION['sessionname'])) { //ログインセッションなし
	// ログ記録
	$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;
	makeLog($url.' => 【エラー】ログインなしでダイレクトアクセスし、ログインページに飛ばされる') ;
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
if(!($_SESSION['sessionname'] === $admin_info[0]['id'])){ //管理者でない
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
<title>ロック解除</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
<script type="text/javascript" src="js/sha.js"></script><!-- パスワードをハッシュ化するライブラリ -->
</head>
<body>
<div class="contentS">
<?php
echo "管理者アカウント ".hsc($_SESSION['sessionname'])." でログイン中です。" ;
echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
echo "<hr>"."\n";

if(!isset($_POST["release"])) {
	echo '<h2>ロック中のアカウント</h2>';
	echo '<form action="releaseRock.php" name="release_submit" method="post" style="display:inline">'."\n";
	
	$rockFlag = 0 ;
	for ($i=0; $i<count($user_info); $i++) {
		if($user_info[$i]['rock'] === 1){
			echo '<input type="radio" name="release" value="'.$i.'">';
			echo 'アカウントID：'.hsc($user_info[$i]['id']).'　ユーザー名：'.hsc($user_info[$i]['name']).'<br/>'."\n" ;
			$rockFlag = 1 ;
		}
	}
	if($rockFlag === 0){
		echo 'ロック中のアカウントはありません。';
	}else{
		echo '<input type="button" id="releasebutton" value="チェックを入れたアカウントのロック解除" onclick="excute_submit()"></form>'."\n";
	}

}else{

	$user_info[$_POST["release"]]['rock'] = 0 ;
	
	echo hsc($user_info[$_POST["release"]]['id']).' のロックを解除しました。<br>';
	echo 'パスワードを忘れてログインできない場合は、<a href="addUser.php">一般アカウントの追加 / 削除</a> から当該アカウントを一旦削除し、同じアカウントを再作成してください。<br>';
	
	makeLog($_SESSION['sessionname'].' => ID:'.$user_info[$_POST["release"]]['id'].' のアカウントロックを解除') ;
			
	//書き込むテキストの生成
	$inputText = '<?php'."\n";
	$inputText .= '$admin_info = '.var_export($admin_info,true).' ;'."\n"; 
	$inputText .= '$user_info = '.var_export($user_info,true).' ;'."\n"; 
	$inputText .= '$site_setting = '.var_export($site_setting,true).' ;'."\n"; 
	$inputText .= '?>'."\n";
	
	$fp = fopen("accountData.php", "a");
	if (flock($fp, LOCK_EX)) {  // 排他ロックを確保
		ftruncate($fp, 0);      // ファイルを切り詰め
		fwrite($fp, $inputText);
		fflush($fp);            // 出力をフラッシュしてから
		flock($fp, LOCK_UN);    // ロックを解放
	}
	fclose($fp);
}
echo "<hr>"."\n";
echo '<a href="redirect.html">管理画面に戻る</a>'; 
?>
</div>

<script type="text/javascript">
function excute_submit(){ 
	document.getElementById("releasebutton").disabled = true ;
	document.getElementById("releasebutton").value= "お待ち下さい..." ;
	const timersubmit = function(){
		document.release_submit.submit();
	}
	setTimeout(timersubmit, 1500); 
}
</script>
</body> 
</html>