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
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0" />
<meta name="robots" content="noindex,nofollow,noarchive" /> <!-- 検索エンジンに登録させない -->
<title>ユーザー名の変更</title>
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

require_once 'accountData.php'; //accountData.phpを呼び出し

echo hsc($_SESSION['sessionname'])." でログイン中です。" ;
echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
echo "<hr>"."\n";

if(isset($_POST["newname"])) {
	
	echo 'ユーザー名を変更しました。<br><br>'."\n";
	
	for ($i=0; $i<count($user_info); $i++) {
		if($_SESSION['sessionname'] === $user_info[$i]["id"]){
			
			// ログ記録
			makeLog($_SESSION['sessionname'].' => ユーザー:'.$_SESSION['sessionname'].' のユーザー名が ['.$user_info[$i]["name"].'] から ['.$_POST["newname"].'] に正常に変更された') ;
			
			//メモリ上の配列を修正
			$user_info[$i]["name"] = $_POST["newname"] ;
			break;
		}
	}

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

}else{
	
	$userNameThis = "" ;
	for ($i=0; $i<count($user_info); $i++) {
		if($_SESSION['sessionname'] === $user_info[$i]["id"]){
			$userNameThis = $user_info[$i]["name"] ;
			break;
		}
	}
	
	echo '<h2>ユーザー '.hsc($_SESSION['sessionname'])." のユーザー名変更</h2>"."\n";
	echo '<form action="changeUserName.php" name="myform" method="POST">'."\n";
	echo '<span style="display:inline-block;width:170px;">現在のユーザー名：</span>'.hsc($userNameThis).'<br>'."\n";
	echo '<span style="display:inline-block;width:170px;">新しいユーザー名：</span><input class="textBoxN" name="newname" type="text" required> ※全角OK<br>'."\n";
	echo '<input name="change" type="submit" value="ユーザー名変更">';
	echo '</form>';

}
echo "<hr>" ;
echo '<a href="redirect.html">管理画面に戻る</a>'; 
?>
</div>
</body> 
</html>