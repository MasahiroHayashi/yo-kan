<?php
// ini_set('display_errors', "On");
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars定義関数
require_once './makeLog.php'; //ログファイル書き込み関数を呼び出し
if (!isset($_SESSION['token'])) { // ワンタイムトークン生成
	$_SESSION['token'] = sha1(random_bytes(30));
}
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
<title>データの新規登録</title>
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

require 'accountData.php'; 

//管理者でログインした場合
if($_SESSION['sessionname'] === $admin_info[0]['id']){
	
	echo "管理者アカウント ".hsc($_SESSION['sessionname'])." でログイン中です。" ;

//ユーザーでログインした場合
}else{
	for ($i=0; $i<count($user_info); $i++) {
		
		if($user_info[$i]['id'] === $_SESSION['sessionname'] ){
			
			echo hsc($_SESSION['sessionname'])." でログイン中です。" ;
			break;
		}
	}
}
echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
echo "<hr>"."\n";
echo '<h2>データの新規登録</h2>'."\n" ;
echo '<form action="dataUpload2.php" name="upload" method="post" enctype="multipart/form-data">'."\n";
echo 'アップロードするファイルを選択：<input id="upFile" type="file" name="fname"><br><br>'."\n";
echo 'データの名称<br><input id="dsName" name="dataname" class="textBoxW" type="text" required>（必須）<br>'."\n";
echo 'データの説明<br><textarea name="comment" class="textBoxH" rows="3" cols="20"></textarea><br>'."\n" ;
echo 'データのライセンス<br><select class="textBoxW" name=\'license\'>'."\n";
echo '<option value=\'CC0\'>CC0（パブリックドメイン宣言）</option>'."\n";
echo '<option value=\'CC-BY\'>CC-BY</option>'."\n";
echo '<option value=\'CC-BY-SA\'>CC-BY-SA</option>'."\n";
echo '<option value=\'CC-BY-ND\'>CC-BY-ND</option>'."\n";
echo '<option value=\'CC-BY-NC\'>CC-BY-NC</option>'."\n";
echo '<option value=\'CC-BY-NC-SA\'>CC-BY-NC-SA</option>'."\n";
echo '<option value=\'CC-BY-NC-ND\'>CC-BY-NC-ND</option>'."\n";
echo '<option value=\'Public domain\'>Public domain（著作権の対象でない 又は 著作権保護期間満了）</option>'."\n";
echo '<option value=\'MIT License\'>MIT License</option>'."\n";
echo '<option value=\'GNU General Public License\'>GNU General Public License</option>'."\n";
echo '<option value=\'GNU Free Documentation License\'>GNU Free Documentation License</option>'."\n";
echo '<option value=\'Apache License\'>Apache License</option>'."\n";
echo '<option value=\'その他\'>その他</option>'."\n";
echo '</select><br>'."\n";
echo 'データの著作権者<br><input id="dsLicense" class="textBoxW" name="copyright" type="text" required>（必須）<br><br>'."\n";
echo '<input type="hidden" name="token" value="'.$_SESSION["token"].'" >'."\n"; //セッショントークン
echo '<input type="button" id="upbutton" value="アップロード" onclick="timer1()"></form>'."\n";
echo "<hr>"."\n";
echo '<a href="redirect.html">管理画面に戻る</a>'; 
?>
</div>
<script>
function timer1(){ 
	let dsName = document.getElementById('dsName').value; 
	let dsLicense = document.getElementById('dsLicense').value; 
	let upFile = document.getElementById('upFile').value; 
	if (upFile == ""){
		alert("アップロードするファイルが選択されていません。");
		return;
	}else if  (dsName == "") {
		alert("データの名称を入力してください。");
		return;
	}else if  (dsLicense == "") {
		alert("データの著作権者を入力してください。");
		return;
	}
	document.getElementById("upbutton").disabled = true ;
	document.getElementById("upbutton").value= "お待ち下さい..." ;
	var timersubmit = function(){
		document.upload.submit();
	}
	setTimeout(timersubmit, 100);
}
</script>
</body> 
</html>