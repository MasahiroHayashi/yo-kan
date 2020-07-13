<?php
// ini_set('display_errors', "On");
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars定義関数
if(isset($_SESSION['sessionname'])) { //セッションがすでにある（ログイン状態）
	header( "Location: ./controlpanel.php" ) ; //コントロールパネルにとぶ
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0" />
<meta name="robots" content="noindex,nofollow,noarchive" /> <!-- 検索エンジンに登録させない -->
<title>ログインフォーム</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
<script type="text/javascript" src="js/sha.js"></script><!-- パスワードをハッシュ化するライブラリ -->
</head>
<body>
<div class="contentS">
<?php
if (!isset($_SESSION['token'])) { // ワンタイムトークン生成
	$_SESSION['token'] = sha1(random_bytes(30));
}

if(file_exists ('accountData.php')){ //accountData.php（アカウント情報）が存在する
	
	echo '<h3>ログインしてください。</h3><hr>'."\n";
	echo '<form action="loginProcess.php" name="sousin_submit" method="POST">'."\n";
	echo '<span style="display:inline-block;width:150px;">アカウントID：</span><input class="textBoxN" name="user_name" id="account1" type="text"><br/>'."\n";
	echo '<span style="display:inline-block;width:150px;">パスワード：</span><input class="textBoxN" type="password" id="pass_word1" value="" pattern="^[0-9A-Za-z]+$" required><br>'."\n"; //このinputはpostしない（ダミー）
	echo '<input type="hidden" name="token" value="'.$_SESSION["token"].'" >'."\n"; //セッショントークン
	echo '<input type="hidden" name="password" id="pass_hide1" value="" >'."\n"; //この隠しフィールドのvalueをjsでハッシュ化したものをpost
	echo '<div style="padding:8px 0 8px 0;"><input type="button" id="sosinbutton" value="ログイン" onclick="excute_submit1()"></div>'."\n";
	echo '</form>'."\n";
	echo '<hr>'."\n";
	echo 'このログインフォームのURLは公開されませんので、ブックマークをしておいてください。'."\n";
	echo '<hr>'."\n";
	echo '<a href="./">ホーム画面へ</a><br>'."\n";
	
}else{ //アカウント情報が存在しない
	
	echo '<h3>はじめに管理者アカウントを作成してください。</h3><hr>'."\n";
	echo '<form action="addAdmin.php" name="sousin_submit" method="POST">'."\n";
	echo '<span style="display:inline-block;width:210px;">管理者アカウントID：</span><input class="textBoxN" name="user_name" id="account1" type="text" value="" pattern="^[0-9A-Za-z]+$" required> ※半角英数 6～30字<br>'."\n";
	echo '<div class="kaisetsuArea">アカウントIDは変更できませんので慎重に決めてください。</div>'."\n";
	echo '<span style="display:inline-block;width:210px;">パスワード：</span><input class="textBoxN" type="password" id="pass_word1" value="" pattern="^[0-9A-Za-z]+$" required> ※半角英数 10～30字<br>'."\n"; //このinputはpostしない（ダミー）
	echo '<div class="kaisetsuArea">パスワードは後から変更できます。</div>'."\n";
	echo '<span style="display:inline-block;width:210px;">連絡用メールアドレス：</span><input class="textBoxW" name="mail" type="text" value="" required><br>'."\n";
	echo '<div class="kaisetsuArea">この連絡用メールアドレスはお問い合わせフォームのメール送達先となります。また管理者アカウントがロックされた場合の、解除方法の連絡メールの送達先にもなります。よって確実に届くアドレスを入力してください。メールアドレスは後から変更できます。</div>'."\n";
	echo '<input type="hidden" name="token" value="'.$_SESSION["token"].'" >'."\n"; //セッショントークン
	echo '<input type="hidden" name="password" id="pass_hide1" value="" >'."\n"; //この隠しフィールドのvalueをjsでハッシュ化したものをpost
	echo '<input type="button" id="sosinbutton" value="アカウント作成" onclick="excute_submit2()">'."\n";
	echo '</form>'."\n";
}
?>
</div>

<script type="text/javascript">
function excute_submit1(){ 
	let account1 = document.getElementById('account1').value; //見えているフィールドからvalueを取得
	let pass1 = document.getElementById('pass_word1').value; //見えているフィールドからvalueを取得
	if(account1 == ""){
		alert("アカウントIDが空欄です。");
		return ;
	}else if(pass1 == ""){
		alert("パスワードが空欄です。");
		return ;
	}
	document.getElementById('pass_word1').value = null; //ポストされる平文パスをnullに（これ重要！！！）

	//暗号化
	pass1 = pass1 + 'opendata' + pass1 ; //レインボーテーブル対策のソルト
	let shaObj1 = new jsSHA("SHA-256", "TEXT");
	shaObj1.update(pass1);
	let passhash1 = shaObj1.getHash("HEX"); //パスワードハッシュ作成

	//1000回ストレッチング
	for (i=0; i<1000; i++) { 
		let shaObj2 = new jsSHA("SHA-256", "TEXT");
		shaObj2.update(passhash1);
		passhash1 = shaObj2.getHash("HEX"); 
	}
	document.getElementById('pass_hide1').value = passhash1; //隠しフィールドのvalueにハッシュを入れる
	document.getElementById("sosinbutton").disabled = true ;
	document.getElementById("sosinbutton").value= "お待ち下さい..." ;
	const timersubmit = function(){
		document.sousin_submit.submit();
	}
	setTimeout(timersubmit, 500); 
}

function excute_submit2(){ 
	let account1 = document.getElementById('account1').value; //見えているフィールドからvalueを取得
	if(account1 == ""){
		alert("アカウントIDが空欄です。");
		return ;
	}else if (account1.length < 6 || account1.length > 30 ) {
		alert("アカウントIDは6～30字の間にしてください。");
		return;
	}
	let pass1 = document.getElementById('pass_word1').value; //見えているフィールドからvalueを取得
	document.getElementById('pass_word1').value = null; //ポストされる平文パスをnullに（これ重要！！！）
	
	//判定
	const regex = new RegExp(/^[0-9A-Za-z]+$/); //正規表現パターン（半角英数字）
	if(pass1 == ""){
		alert("パスワードが空欄です。");
		return ;
	}else if (!regex.test(pass1)) {
		alert("パスワードに使える文字は半角英数字のみです。");
		return;
	}else if (pass1.length < 10 || pass1.length > 30 ) {
		alert("パスワードは10～30字の間にしてください。");
		return;
	}
	
	//暗号化
	pass1 = pass1 + 'opendata' + pass1 ; //レインボーテーブル対策のソルト
	let shaObj1 = new jsSHA("SHA-256", "TEXT");
	shaObj1.update(pass1);
	let passhash1 = shaObj1.getHash("HEX"); //パスワードハッシュ作成

	//1000回ストレッチング
	for (i=0; i<1000; i++) { 
		let shaObj2 = new jsSHA("SHA-256", "TEXT");
		shaObj2.update(passhash1);
		passhash1 = shaObj2.getHash("HEX"); 
	}
	document.getElementById('pass_hide1').value = passhash1; //隠しフィールドのvalueにハッシュを入れる
	document.getElementById("sosinbutton").disabled = true ;
	document.getElementById("sosinbutton").value= "お待ち下さい..." ;
	const timersubmit = function(){
		document.sousin_submit.submit();
	}
	setTimeout(timersubmit, 1500); 
}
</script>
</body> 
</html>