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
<title>アカウント追加・削除</title>
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
echo '<h2>アカウントの追加</h2>'."\n";
echo '<form action="addUser.php" name="sousin_submit" method="POST">'."\n";
echo '<div class="kaisetsuArea">このユーザーアカウント（一般アカウント）は、データの追加や変更、削除のみ可能なアカウントです。変更や削除はこのアカウントで登録したもののみ可能です。それ以外の機能は利用できません。</div>'."\n";
echo '<span style="display:inline-block;width:140px;">アカウントID：</span><input class="textBoxN" id="user_name1" name="user_name" type="text" pattern="^[0-9A-Za-z]+$" required> ※半角英数 6～30字<br>'."\n";
echo '<span style="display:inline-block;width:140px;">パスワード：</span><input class="textBoxN" type="password" id="pass_word1" pattern="^[0-9A-Za-z]+$" required> ※半角英数 10～30字<br>'."\n";
echo '<input type="hidden" name="password" id="pass_hide1" value="" >'."\n"; //この隠しフィールドのvalueをjsでハッシュ化したものをpost
echo '<span style="display:inline-block;width:140px;">ユーザー名：</span><input class="textBoxN" name="name"  type="text" required> ※全角OK<br><br>'."\n";
echo '<input type="button" id="sosinbutton" value="アカウントの追加" onclick="excute_submit()">'."\n"; 
echo '</form>'."\n";
echo '<hr>メッセージ：'."\n";
echo ''."\n";

//追加の場合
if(isset($_POST["user_name"])) {

	$id_length = strlen($_POST["user_name"]);
	$ps_length = strlen($_POST["password"]);
	if($ps_length != 64){
		echo '【エラー】不正なパスワードです。';
		
		// ログ記録
		makeLog($_SESSION['sessionname'].' =>【不正アクセス注意】不正なアクセスにより、ID:'.$_POST["user_name"].' PASS:'.$_POST["password"].' でユーザー登録しようとしたが失敗') ;
		
	}else if($id_length < 6 || $id_length > 30){
		echo '【エラー】アカウントIDは 6字以上 30字以内 にしてください。';
		
	}else if($_POST["user_name"] === $admin_info[0]['id']){
		echo '【エラー】管理者IDと同じアカウントIDは使用できません。';
		
	}else{
		
		$addFlag = true;
		for ($i=0; $i<count($user_info); $i++) {
			if($_POST["user_name"] === $user_info[$i]['id']){
				echo '【エラー】アカウントID：'.hsc($_POST["user_name"]).'はすでに使われています。';
				$addFlag = false;
				break;
			}			
		}
		
		if($addFlag){ //追加可能な場合
			echo hsc($_POST["user_name"]).' を追加しました。';	
			
			// ログ記録
			makeLog($_SESSION['sessionname'].' => ユーザー:'.$_POST["user_name"].' が正常に追加された') ;

			// ハッシュ化
			$passHash  = hash("sha256",($_POST["password"]."opendata")); 
			for ($i = 0; $i < 1000; $i++){ //ストレッチング1000回
				$passHash  = hash("sha256",$passHash);
			}
			
			//メモリ上の配列を修正
			$user_info[] = array('id'=>$_POST['user_name']) + array('pass'=>$passHash) + array('name'=>$_POST['name']) + array('rock'=>0) ;

			//書き込むテキストの生成
			$inputText = '<?php'."\n";
			$inputText .= '$admin_info = '.var_export($admin_info,true).' ;'."\n"; 
			$inputText .= '$user_info = '.var_export($user_info,true).' ;'."\n"; 
			$inputText .= '$site_setting = '.var_export($site_setting,true).' ;'."\n"; 
			$inputText .= '?>'."\n";

			//ファイルに書き出し
			$fp = fopen("accountData.php", "a");
			if (flock($fp, LOCK_EX)) {  // 排他ロックを確保
				ftruncate($fp, 0);      // ファイルを切り詰め
				fwrite($fp, $inputText);
				fflush($fp);            // 出力をフラッシュしてから
				flock($fp, LOCK_UN);    // ロックを解放
			}
			fclose($fp);
		}
	}
	
//削除の場合
}else if(isset($_POST["del"])) {
	
	echo hsc($user_info[$_POST["del"]]['id']).' を削除しました。';
	
	// ログ記録
	makeLog($_SESSION['sessionname'].' => ユーザー:'.$user_info[$_POST["del"]]['id'].' が正常に削除された') ;
	
	unset($user_info[$_POST["del"]]); //配列から削除
	$user_info = array_values($user_info); //歯抜けになった配列を詰める
	
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

echo '<hr>'."\n";
echo '<h3>アカウントの削除</h3>';
echo '<form action="addUser.php" name="delete_submit" method="post" style="display:inline">'."\n";
for ($i=0; $i<count($user_info); $i++) {
	echo '<input type="radio" name="del" value="'.$i.'">';
	echo 'アカウントID：'.hsc($user_info[$i]['id']).'　ユーザー名：'.hsc($user_info[$i]['name'])."\n" ;
	if($user_info[$i]['rock'] === 1){
		echo '　※アカウントロック中';
	}
	echo "<br>"."\n";
}
if($i > 0){
	echo '<input type="button" id="delbutton" value="チェックを入れたアカウントの削除" onclick="del_submit()"></form>'."\n";
}else{
	echo '<input type="hidden" id="delbutton"></form>'."\n";
}
echo "<hr>"."\n";
echo '<a href="redirect.html">管理画面に戻る</a>'; 
?>
</div>
<script type="text/javascript">
function excute_submit(){ 
	let pass1 = document.getElementById('pass_word1').value; //見えているフィールドからvalueを取得
	let idName1 = document.getElementById('user_name1').value; //見えているフィールドからvalueを取得
	document.getElementById('pass_word1').value = null; //ポストされる平文パスをnullに
		
	//判定
	const regex = new RegExp(/^[0-9A-Za-z]+$/); //正規表現パターン（半角英数字）
	if (!regex.test(idName1)) {
		alert("アカウントIDに使える文字は半角英数字のみです。");
		return;
	}else if (idName1.length < 6 || idName1.length > 30 ) {
		alert("アカウントIDは6～30字の間にしてください。");
		return;
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
	document.getElementById("delbutton").disabled = true ;
	const timersubmit = function(){
		document.sousin_submit.submit();
	}
	setTimeout(timersubmit, 1000); 
}
function del_submit(){ 
	document.getElementById("sosinbutton").disabled = true ;
	document.getElementById("delbutton").disabled = true ;
	document.getElementById("delbutton").value= "お待ち下さい..." ;
	const timersubmit = function(){
		document.delete_submit.submit();
	}
	setTimeout(timersubmit, 1000); 
}
</script>
</body> 
</html>