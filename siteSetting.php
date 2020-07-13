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
// img/uploadディレクトリがなければ作成
if(!file_exists("./img_uploaded")){
	mkdir("./img_uploaded") ;
}
// サーバーキャッシュのクリアのための処理
$fileName = "accountData.php" ;
copy($fileName, $fileName.'copy'); // コピーを作成
unlink($fileName);                 // 原本を削除
copy($fileName.'copy', $fileName); // コピーから原本を再作成
unlink($fileName.'copy');      
require 'accountData.php';
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
<title>サイトのタイトル / 表示させる文章 / 見た目の設定・管理</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
</head>
<body>
<div class="contentS">
<?php
echo "管理者アカウント ".hsc($_SESSION['sessionname'])." でログイン中です。" ;
echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
echo "<hr>"."\n";
echo '<h2>サイトの表示項目の修正</h2>'."\n" ;
echo '<form action="siteSetting2.php" name="siteSettingPost" method="post" enctype="multipart/form-data">'."\n";
echo 'このWEBサイトの名前<br><input class="textBoxW" name="s0_siteName" type="text" value="'.$site_setting[0]['s0_siteName'].'" required>'."\n";
echo '<div class="kaisetsuArea">この項目はWEBサイトの＜title＞タグの要素です。ブックマークしたときのサイト名称となります。またSNSでシェアされたときのサイトの名称ともなります。文字数制限はありませんが、全角なら20字くらいまでがよろしいでしょう。</div>'."\n";
echo 'このWEBサイト（全ページ共通）の背景画像<br><select class="textBoxN" name=\'s1_backgroundImg\'>'."\n";
echo '<option value="N">なし</option>'."\n";
foreach(glob('./img_uploaded/*') as $file){ 
	if(is_file($file)){
		$imgFileName = str_replace( './img_uploaded/' , '' , $file);
		
		if($imgFileName == $site_setting[0]['s1_backgroundImg']){
			echo '<option value="'.$imgFileName.'" selected>'.$imgFileName.'</option>'."\n";
		}else{
			echo '<option value="'.$imgFileName.'">'.$imgFileName.'</option>'."\n";
		}
	}
}
echo '</select>'."\n";
echo '<div class="kaisetsuArea">この項目はWEBサイトの背景画像の設定です。必要に応じて、このページの下部の「画像ファイルのアップロード」から画像をアップロードしご利用ください。画像は画面の横幅いっぱいに拡大縮小されます。縦長の写真(jpeg)などが適していると思います。あまり解像度が高すぎると読み出しに時間がかかりますので適当に調整しましょう。サンプルは<a href="./img/sample_background.jpg" target="_blank">こちら</a></div>'."\n";
echo 'SNSでシェアされたときに表示される一文<br><input class="textBoxW" name="s2_snsText" type="text" value="'.$site_setting[0]['s2_snsText'].'"required>'."\n";
echo '<div class="kaisetsuArea">この項目はFacebookやTwitterでシェアされたときに、サイト名称の下に表示される「説明文」です。全角なら30字くらいまでがよろしいでしょう。</div>'."\n";
echo 'SNSでシェアされたときに表示される画像<br><select class="textBoxN" name=\'s3_snsImg\'>'."\n";
echo '<option value="N">なし</option>'."\n";
//イメージディレクトリ内の画像の一覧を取得する
foreach(glob('./img_uploaded/*') as $file){ 
	if(is_file($file)){
		$imgFileName = str_replace( './img_uploaded/' , '' , $file);
		
		if($imgFileName == $site_setting[0]['s3_snsImg']){
			echo '<option value="'.$imgFileName.'" selected>'.$imgFileName.'</option>'."\n";
		}else{
			echo '<option value="'.$imgFileName.'">'.$imgFileName.'</option>'."\n";
		}
			
	}
}
echo '</select>'."\n";
echo '<div class="kaisetsuArea">この項目はFacebookやTwitterでシェアされたときに表示される画像です。必要に応じて、このページの下部の「画像ファイルのアップロード」から画像をアップロードしご利用ください。推奨サイズは1200x630pxです。サンプルは<a href="./img/sample_fb_tw_img.jpg" target="_blank">こちら</a>。</div>'."\n";
echo 'ヘッダー（全ページ共通）のタイトル文字<br><input class="textBoxW" name="s4_headerName" type="text" value="'.$site_setting[0]['s4_headerName'].'"required>'."\n";
echo '<div class="kaisetsuArea">この項目はサイトのヘッダー部分（上部の黒っぽい帯の部分）に表示される文字です。全角なら20字くらいまでがよろしいでしょう。横長のバナー画像をヘッダー部分に用いる場合は「空欄」にするのもありです。ブラウザの横幅を変更しながら、画像と文字のバランスを考えて上手に調整してみてください。（ブラウザの横幅が650px以下になるとスマホ用画面に切り替わります。）</div>'."\n";
echo 'ヘッダー（全ページ共通）のアイコン画像 / タイトル画像<br><select class="textBoxN" name=\'s5_headerBanner\'>'."\n";
echo '<option value="N">なし</option>'."\n";
//イメージディレクトリ内の画像の一覧を取得する
foreach(glob('./img_uploaded/*') as $file){ 
	if(is_file($file)){
		$imgFileName = str_replace( './img_uploaded/' , '' , $file);
		
		if($imgFileName == $site_setting[0]['s5_headerBanner']){
			echo '<option value="'.$imgFileName.'" selected>'.$imgFileName.'</option>'."\n";
		}else{
			echo '<option value="'.$imgFileName.'">'.$imgFileName.'</option>'."\n";
		}
			
	}
}
echo '</select>'."\n";
echo '<div class="kaisetsuArea">この項目はサイトのヘッダー部分に表示される画像（アイコンやタイトル画像）です。ブラウザの横幅を変更しながら、タイトル文字とのバランスを考えて上手に調整してみてください。サンプル： <a href="./img/sample_icon.png" target="_blank">アイコン</a> / <a href="./img/sample_logo.png" target="_blank">横長タイトル画像</a></div>'."\n";
echo 'トップページのコンテンツ本文（上段） ※HTMLタグ利用可<br><textarea class="textBoxH" name="s6_contentTextTop" rows="3" cols="20">'.$site_setting[0]['s6_contentTextTop'].'</textarea><br><br>'."\n" ;
echo 'トップページのコンテンツ本文（下段） ※HTMLタグ利用可<br><textarea class="textBoxH" name="s7_contentTextBottom" rows="3" cols="20">'.$site_setting[0]['s7_contentTextBottom'].'</textarea><br><br>'."\n" ;
echo 'フッター（全ページ共通）の本文 ※HTMLタグ利用可<br><textarea class="textBoxH" name="s8_footerText" rows="3" cols="20">'.$site_setting[0]['s8_footerText'].'</textarea><br><br>'."\n" ;
echo '１ページの最大表示データ数　<select name=\'s9_maxDisplayData\'>'."\n"; 
if($site_setting[0]['s9_maxDisplayData'] == "5"){
	echo '<option value="5" selected>5件</option>'."\n";
	echo '<option value="10">10件</option>'."\n";
	echo '<option value="20">20件</option>'."\n";
	echo '<option value="50">50件</option>'."\n";
	echo '<option value="100">100件</option>'."\n";
}else if($site_setting[0]['s9_maxDisplayData'] == "10"){
	echo '<option value="5">5件</option>'."\n";
	echo '<option value="10" selected>10件</option>'."\n";
	echo '<option value="20">20件</option>'."\n";
	echo '<option value="50">50件</option>'."\n";
	echo '<option value="100">100件</option>'."\n";
}else if($site_setting[0]['s9_maxDisplayData'] == "20"){
	echo '<option value="5">5件</option>'."\n";
	echo '<option value="10">10件</option>'."\n";
	echo '<option value="20" selected>20件</option>'."\n";
	echo '<option value="50">50件</option>'."\n";
	echo '<option value="100">100件</option>'."\n";
}else if($site_setting[0]['s9_maxDisplayData'] == "50"){
	echo '<option value="5">5件</option>'."\n";
	echo '<option value="10">10件</option>'."\n";
	echo '<option value="20">20件</option>'."\n";
	echo '<option value="50" selected>50件</option>'."\n";
	echo '<option value="100">100件</option>'."\n";
}else if($site_setting[0]['s9_maxDisplayData'] == "100"){
	echo '<option value="5">5件</option>'."\n";
	echo '<option value="10">10件</option>'."\n";
	echo '<option value="20">20件</option>'."\n";
	echo '<option value="50">50件</option>'."\n";
	echo '<option value="100" selected>100件</option>'."\n";
}else {
	echo '<option value="5">5件</option>'."\n";
	echo '<option value="10" selected>10件</option>'."\n";
	echo '<option value="20">20件</option>'."\n";
	echo '<option value="50">50件</option>'."\n";
	echo '<option value="100">100件</option>'."\n";
}
echo '</select>'."\n";
echo '<div class="kaisetsuArea">この項目は「オープンデータ一覧」のページに表示される最大データ数です。お好みに応じて設定してください。</div>'."\n";
echo '<input type="hidden" name="token" value="'.$_SESSION["token"].'" >'."\n"; //セッショントークン
echo '<input type="button" id="excuteButton" value="　------   上記の内容を反映させる   ------  " onclick="excute1()"></form>'."\n";
echo "<hr>"."\n";
echo '<h3>画像ファイルのアップロード（背景・アイコン・タイトル用　PNGまたはJPEG形式のみ）</h3>'."\n" ;
echo '<form action="siteSetting2.php" name="upload" method="post" enctype="multipart/form-data">'."\n";
echo '<input type="file"  accept=".png,.jpg,.jpeg"  name="fname"><br>'."\n";
echo '<input type="hidden" name="token" value="'.$_SESSION["token"].'" >'."\n"; //セッショントークン
echo '<br><input type="button" id="upbutton" value="アップロードする" onclick="excute2()"></form>'."\n";
echo "<hr>"."\n";
echo '<h3>画像ファイルの削除</h3>'."\n" ;
echo '<form action="siteSetting2.php" name="delete" method="post" enctype="multipart/form-data">'."\n";
foreach(glob('./img_uploaded/*') as $file){ 
	if(is_file($file)){
		$imgFileName = str_replace( './img_uploaded/' , '' , $file);
		echo '<input type="radio" name="del" value="'.$imgFileName.'">'.$imgFileName.'<br>'."\n";
	}
}
echo '<input type="hidden" name="token" value="'.$_SESSION["token"].'" >'."\n"; //セッショントークン
echo '<input type="button" id="delbutton" value="削除する" onclick="excute3()"></form>'."\n";
echo "<hr>"."\n";
echo '<a href="redirect.html">管理画面に戻る</a>';
?>
</div>

<script>
function excute1(){ 
	document.getElementById("excuteButton").disabled = true ;
	document.getElementById("excuteButton").value= "お待ち下さい..." ;
	document.getElementById("upbutton").disabled = true ;
	document.getElementById("upbutton").value= "お待ち下さい..." ;
	document.getElementById("delbutton").disabled = true ;
	document.getElementById("delbutton").value= "お待ち下さい..." ;
	var timersubmit = function(){
		document.siteSettingPost.submit();
	}
	setTimeout(timersubmit, 1000);
}
function excute2(){ 
	document.getElementById("excuteButton").disabled = true ;
	document.getElementById("excuteButton").value= "お待ち下さい..." ;
	document.getElementById("upbutton").disabled = true ;
	document.getElementById("upbutton").value= "お待ち下さい..." ;
	document.getElementById("delbutton").disabled = true ;
	document.getElementById("delbutton").value= "お待ち下さい..." ;
	var timersubmit = function(){
		document.upload.submit();
	}
	setTimeout(timersubmit, 1000);
}
function excute3(){ 
	document.getElementById("excuteButton").disabled = true ;
	document.getElementById("excuteButton").value= "お待ち下さい..." ;
	document.getElementById("upbutton").disabled = true ;
	document.getElementById("upbutton").value= "お待ち下さい..." ;
	document.getElementById("delbutton").disabled = true ;
	document.getElementById("delbutton").value= "お待ち下さい..." ;
	var timersubmit = function(){
		document.delete.submit();
	}
	setTimeout(timersubmit, 1000);
}
</script>
</body> 
</html>