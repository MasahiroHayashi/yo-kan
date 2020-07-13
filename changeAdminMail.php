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
	// ログ記録
	$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;
	makeLog($_SESSION['sessionname'].' => '.$url.' => 【エラー】ダイレクトアクセスし、ログインページに飛ばされる') ;
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
<title>管理者メールアドレス変更</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
</head>
<body>
<div class="contentS">
<?php
echo "管理者アカウント ".hsc($_SESSION['sessionname'])." でログイン中です。" ;
echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
echo "<hr>"."\n";

if(isset($_POST["change"])) {
	
	$reg_str = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
	if (!preg_match($reg_str, $_POST['mail'])) {

		echo "【エラー】正しくないメールアドレスです。入力し直してください。<br><br>"."\n";
		echo '<form action="changeAdminMail.php" name="myform" method="POST">'."\n";
		echo '<span style="display:inline-block;width:230px;">現在のメールアドレス：</span>'.hsc($admin_info[0]['mail']).'<br>'."\n";
		echo '<span style="display:inline-block;width:230px;">変更後メールアドレス：</span><input class="textBoxW" name="mail" type="text" required>'."\n";
		echo '<div class="kaisetsuArea">このメールアドレスはお問い合わせフォームのメール送達先となります。また管理者アカウントがロックされた場合の、解除方法の連絡メールの送達先にもなります。よって確実に届くアドレスを入力してください。</div>'."\n";
		echo '<input name="change" type="submit" value="メールアドレス変更">';
		echo '</form>';
		
	}else{
		
		// ログ記録
		makeLog($_SESSION['sessionname'].' => 管理者のメールアドレスが '.$admin_info[0]['mail'].' から '.$_POST["mail"].' に正常に変更された') ;
		echo 'メールアドレスを変更しました。<br><br>'."\n";
		
		//メモリ上の配列を修正
		$admin_info[0]['mail'] = $_POST["mail"] ;
		
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
}else{
	
	echo '<h2>管理者 '.hsc($_SESSION['sessionname'])." のメールアドレス変更</h2>"."\n";
	echo '<form action="changeAdminMail.php" name="myform" method="POST">'."\n";
	echo '<span style="display:inline-block;width:230px;">現在のメールアドレス：</span>'.hsc($admin_info[0]['mail']).'<br>'."\n";
	echo '<span style="display:inline-block;width:230px;">変更後メールアドレス：</span><input class="textBoxW" name="mail" type="text" required>'."\n";
	echo '<div class="kaisetsuArea">このメールアドレスはお問い合わせフォームのメール送達先となります。また管理者アカウントがロックされた場合の、解除方法の連絡メールの送達先にもなります。よって確実に届くアドレスを入力してください。</div>'."\n";
	echo '<input name="change" type="submit" value="メールアドレス変更">';
	echo '</form>';
}
echo "<hr>"."\n";
echo '<a href="redirect.html">管理画面に戻る</a>'; 
?>
</div>
</body> 
</html>