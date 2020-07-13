<?php
// ini_set('display_errors', "On");
session_cache_expire(0);
session_cache_limiter('private_no_expire'); //戻るボタンのWebページの有効期限切れ対策
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars定義関数
if (!isset($_SESSION['token'])) { // ワンタイムトークン生成
	$_SESSION['token'] = sha1(random_bytes(30));
}
$fileName = 'accountData.php' ;
if (!(file_exists($fileName))) {
	header( "Location: ./login.php" ) ; //ログインページにとぶ
	exit;
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
	<h2>お問い合わせ</h2>
	<form action="mailConfirm.php" method="POST">
		お名前<br>
		<input class="mailTextArea1" type="text" name="name" required><br><br>
		メールアドレス<br>
		<input class="mailTextArea1" type="text" name="adress_mail"><br><br>
		お問い合わせ内容<br>
		<textarea class="mailTextArea2" name="comment" required></textarea><br><br>
		<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>"> <!-- 隠し項目にトークン -->
		<div class='excute_button'><input class='excute_button_inner' type="submit" value="送信内容の確認"></div>
	</form>
</div>
<footer class="site-footer">
<div class="site-footer-inner">
<?php echo $site_setting[0]['s8_footerText']."\n"; ?>
</div>
</footer>
</body>
</html>