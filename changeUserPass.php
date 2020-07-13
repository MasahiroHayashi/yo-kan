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
<title>ユーザーパスワード変更</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
<script type="text/javascript" src="js/sha.js"></script><!-- パスワードをハッシュ化するライブラリ -->
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

if(isset($_POST["password1"])) { //ポストがある
	
	$passHash1  = hash("sha256",($_POST["password1"]."opendata"));
	$passHash2  = hash("sha256",($_POST["password2"]."opendata"));
	for ($i = 0; $i < 1000; $i++){ 
		$passHash1  = hash("sha256",$passHash1);
		$passHash2  = hash("sha256",$passHash2);
	}

	$ps_length = strlen(($passHash1);
	if($ps_length != 64 || $passHash1 != $passHash2){
		echo '不正なパスワードです。'."\n";
		// ログ記録
		makeLog($_SESSION['sessionname'].' =>【不正アクセス注意】不正なアクセスにより、ID:'.$_SESSION['sessionname'].' のパスワードを '.$_POST["password1"].' に変更を試みたが失敗') ;
			
	}else{
		echo 'パスワードを変更しました。<br><br>'."\n";
	
		for ($i=0; $i<count($user_info); $i++) {
			if($_SESSION['sessionname'] === $user_info[$i]["id"]){
									
				// ログ記録
				makeLog($_SESSION['sessionname'].' => ユーザー:'.$_SESSION['sessionname'].' のパスワードが正常に変更された') ;
			
				//メモリ上の配列を修正
				$user_info[$i]["pass"] = $passHash1 ;
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
	}

}else{
	
	echo '<h2>ユーザー '.$_SESSION['sessionname']." のパスワード変更</h2>"."\n";
	echo '<form action="changeUserPass.php" name="sousin_submit" method="POST">'."\n";
	echo '<span style="display:inline-block;width:230px;">変更後パスワード：</span><input class="textBoxN" id="pass_word1" type="password" pattern="^[0-9A-Za-z]+$" required> ※半角英数 10～30字<br>'."\n";
	echo '<span style="display:inline-block;width:230px;">変更後パスワード(再入力)：</span><input class="textBoxN" id="pass_word2" type="password" pattern="^[0-9A-Za-z]+$" required> ※半角英数 10～30字<br>'."\n";
	echo '<input name="password1" id="pass_hide1" type="hidden">'."\n";
	echo '<input name="password2" id="pass_hide2" type="hidden">'."\n";
	echo '<input type="button" id="sosinbutton" value="パスワード変更" onclick="excute_submit()">'."\n";
	echo '</form>';

}
echo "<hr>"."\n";
echo '<a href="redirect.html">管理画面に戻る</a>'; 
?>
</div>
<script type="text/javascript">
function excute_submit(){ 
	let pass1 = document.getElementById('pass_word1').value; 
	let pass2 = document.getElementById('pass_word2').value; 
	document.getElementById('pass_word1').value = null; //ポストされる平文パスをnullに
	document.getElementById('pass_word2').value = null; //ポストされる平文パスをnullに
	const regex = new RegExp(/^[0-9A-Za-z]+$/); 
	if (pass1 != pass2){
		alert("再入力のパスワードが一致しません。");
		return;
	}else if (!regex.test(pass1)) {
		alert("使える文字は半角英数字のみです。");
		return;
	}else if (pass1.length < 10 || pass1.length > 30 ) {
		alert("10～30字の間にしてください。");
		return;
	}
	pass1 = pass1 + 'opendata' + pass1 ;
	pass2 = pass2 + 'opendata' + pass2 ;
	let shaObj1 = new jsSHA("SHA-256", "TEXT");
	let shaObj2 = new jsSHA("SHA-256", "TEXT");
	shaObj1.update(pass1);
	shaObj2.update(pass2);
	let passhash1 = shaObj1.getHash("HEX");
	let passhash2 = shaObj2.getHash("HEX");
	for (i=0; i<1000; i++) { 
		let shaObj3 = new jsSHA("SHA-256", "TEXT");
		let shaObj4 = new jsSHA("SHA-256", "TEXT");
		shaObj3.update(passhash1);
		shaObj4.update(passhash2);
		passhash1 = shaObj3.getHash("HEX"); 
		passhash2 = shaObj4.getHash("HEX"); 
	}
	document.getElementById('pass_hide1').value = passhash1 ;
	document.getElementById('pass_hide2').value = passhash2 ;
	document.getElementById("sosinbutton").disabled = true ;
	document.getElementById("sosinbutton").value= "お待ち下さい..." ;
	const timersubmit = function(){
		document.sousin_submit.submit();
	}
	setTimeout(timersubmit, 1000);
}
</script>
</body> 
</html>