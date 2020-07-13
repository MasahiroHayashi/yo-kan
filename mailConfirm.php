<?php
// ini_set('display_errors', "On");
session_cache_expire(0);
session_cache_limiter('private_no_expire'); //戻るボタンのWebページの有効期限切れ対策
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars定義関数
$fileName = 'accountData.php' ;
if (!(file_exists($fileName))) {
	header( "Location: ./login.php" ) ; //ログインページにとぶ
	exit;
}

$token = $_POST['token']; //tokenを変数に入れる
if(!(hash_equals($token, $_SESSION['token']) && !empty($token))) { // トークンを確認し、確認画面を表示
	$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;
	makeLog($url.' => 【不正アクセス注意】何者かが不正なアクセスにより、メールフォームから送信しようとしたが失敗') ;
	exit("不正アクセスの可能性があります");
}

//POSTされたデータを各変数に入れる
$name = isset($_POST['name']) ? $_POST['name'] : NULL;
$mail = isset($_POST['adress_mail']) ? $_POST['adress_mail'] : NULL;
$comment = isset($_POST['comment']) ? $_POST['comment'] : NULL;

//前後にある半角全角スペースを削除する関数
function spaceTrim ($str) {
	$str = preg_replace('/^[ 　]+/u', '', $str); // 行頭
	$str = preg_replace('/[ 　]+$/u', '', $str); // 末尾
	return $str;
}
//前後にある半角全角スペースを削除
$name = spaceTrim($name);
$mail = spaceTrim($mail);
$comment = spaceTrim($comment);

// メール形式の判定に使う正規表現を格納した変数
$reg_str = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";

if ($name == ''){
	exit("エラー：名前が入力されていません。");
}else if ($mail == ''){
	exit("エラー：メールアドレスが入力されていません。");
}else if (!preg_match($reg_str, $mail)) {
	exit("エラー：メールアドレスが不正です。");
}else if ($comment == ''){
	exit("エラー：コメントが入力されていません。");
}else { //エラーが無ければセッションに登録
	$_SESSION['name'] = $name;
	$_SESSION['mail'] = $mail;
	$_SESSION['comment'] = $comment;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="js/index.js"></script>
<link rel="stylesheet" type="text/css" href="css/index.css">
<style type="text/css">
/* index_mail.php / mailConfirm.php 専用 */
.mailTextArea1 {
	width: 300px;
	font-size:large;
}
.mailTextArea2 {
	width: 95%;
	height: 200px;
	font-size:large;
}
.mailKakunin1 {
	width: 90%;
	padding: 20px 0 5px 10px ;
	color: #666 ;
}
.mailKakunin2 {
	width: 90%;
	padding: 0 0 10px 20px ;
}
@media screen and (max-width: 650px) {
	.mailTextArea1 {
		width: 95%;
	}
}
</style>
<?php
$fileName = 'accountData.php' ;

copy($fileName, $fileName.'copy'); // コピーを作成
unlink($fileName);                 // 原本を削除
copy($fileName.'copy', $fileName); // コピーから原本を再作成
unlink($fileName.'copy');          // コピーを削除
require_once './accountData.php'; //allDataList.phpを呼び出し

echo '<title>お問い合わせ / '.$site_setting[0]['s0_siteName'].'</title>';
echo '<style type="text/css">'."\n";
echo 'body{ background-image: url(./img_uploaded/'.$site_setting[0]['s1_backgroundImg'].') ;} '."\n";
echo '</style>'."\n";
?>
</head>
<body>
<header class="site-header">
	<div class="site-logo">
		<a href="./">
		<?php
		if($site_setting[0]['s5_headerBanner'] != "N"){
			echo '<img src="./img_uploaded/'.$site_setting[0]['s5_headerBanner'].'"/>'."\n";
		}
		if($site_setting[0]['s4_headerName'] != ""){
			echo '<h1>'.$site_setting[0]['s4_headerName'].'</h1>'."\n";
		}
		?>
		</a>
	</div>
	<div id="wrapper">
		<p class="btn-gnavi">
			<span></span>
			<span></span>
			<span></span>
		</p>
		<nav id="global-navi">
			<ul class="gnav__menu">
				<li class="gnav__menu__item"><a href="./">ホーム</a></li>
				<li class="gnav__menu__item"><a href="index_list.php">オープンデータ一覧</a></li>
				<li class="gnav__menu__item"><a href="index_mail.php">お問い合わせ</a></li>
				<li class="gnav__menu__item"><a href="index_api.php">WEB API</a></li>
			</ul>
		</nav>
	</div>
</header> 
<div id="menu_close"></div><!-- ハンバーガーメニューをクローズするためのサイドバー -->
<div style="margin-top:140px;">  </div>
<div class="content2">
<form method="post" action="mailSend.php">
<div class="mailKakunin1">お名前</div>
<div class="mailKakunin2"><?php echo hsc($name); ?></div>
<div class="mailKakunin1">メールアドレス</div>
<div class="mailKakunin2"><?php echo hsc($mail); ?></div>
<div class="mailKakunin1">お問い合わせ内容</div>
<div class="mailKakunin2"><?php echo nl2br(hsc($comment)); ?></div>
<div class='excute_button'>
<input class='excute_button_inner' type="button" value="戻る" onClick="history.back()">　
<input type="hidden" name="token" value="<?php echo hsc($token); ?>">
<button class='excute_button_inner' >メール送信</button>
</div>
</form>
</div>
<footer class="site-footer">
<div class="site-footer-inner">
<?php echo $site_setting[0]['s8_footerText']."\n"; ?>
</div>
</footer>
</body>
</html>