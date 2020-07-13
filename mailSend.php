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
	exit("不正アクセスの可能性があります。");
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
<?php
mb_language('japanese');
mb_internal_encoding('UTF-8');

$email = $admin_info[0]['mail'] ;
$subject = '【自動応答】Yo-KANのお問い合わせフォームからのメッセージを受信';

$mail = $_SESSION['mail'];
$name = $_SESSION['name'];
$comment = $_SESSION['comment'];

$body = "（Yo-KAN のお問い合わせフォームから以下のメッセージを受信しました。このメールには返信できません。）"."\n";
$body .= "-------------------------------------------"."\n";
$body .= "送信者 : ".hsc($name)."\n"."\n";
$body .= "メールアドレス : ".$mail."\n"."\n";
$body .= "お問い合わせ内容 : "."\n";
$body .= $comment ;

//headerを設定
$charset = "UTF-8";
$headers['MIME-Version'] = "1.0";
$headers['Content-Type'] = "text/plain; charset=".$charset;
$headers['Content-Transfer-Encoding'] = "8bit";
$headers['From'] = "system@yo-kan.com" ; 

//headerを編集
foreach ($headers as $key => $val) {
	$arrheader[] = $key . ': ' . $val;
}
$strHeader = implode("\n", $arrheader);

// Worning（警告エラー）をException（例外エラー）に変えるハンドラ
set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// 1 SENDGRID利用
if(isset($_ENV['SENDGRID_API_KEY'])){

	require 'vendor/autoload.php';
	$emailArr = new \SendGrid\Mail\Mail();
	$emailArr->setFrom("system@yo-kan.com", "automail-yo-kan");
	$emailArr->setSubject($subject);
	$emailArr->addTo($email, "サイト管理者");
	$emailArr->addContent("text/plain", $body);
	$sendgrid = new \SendGrid($_ENV['SENDGRID_API_KEY']);
	
	try {
		$response = $sendgrid->send($emailArr);
		echo '<br><br><br>サイト管理者へメールを送信しました。ありがとうございました。 SendGrid<br><br><br>' ;
		
	} catch (Exception $e) {
		
		echo '<br><br><br>申し訳ありません。サーバーのエラーのため送信できませんでした。 error1<br><br><br><br><br>' ;
	}
	
// 2 外部SMTP利用
}else if(isset($_ENV['SMTP_HOST']) && isset($_ENV['SMTP_ACCOUNT']) && isset($_ENV['SMTP_PASSWORD'])){

	require_once( dirname( __FILE__ ).'/vendor/phpmailer/phpmailer/src/PHPMailer.php' );
	require_once( dirname( __FILE__ ).'/vendor/phpmailer/phpmailer/src/Exception.php' );
	require_once( dirname( __FILE__ ).'/vendor/phpmailer/phpmailer/src/SMTP.php' );

	$mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
	$mailer->CharSet = "UTF-8";
	$mailer->Encoding = "7bit";
	$mailer->IsSMTP();
	$mailer->Host = $_ENV['SMTP_HOST'];
	$mailer->SMTPAuth = true;
	$mailer->SMTPDebug = 0;
	$mailer->SMTPSecure = "tls";
	$mailer->Port = 587;
    $mailer->Username = $_ENV['SMTP_ACCOUNT'] ;
    $mailer->Password = $_ENV['SMTP_PASSWORD'] ;
    $mailer->setFrom("system@yo-kan.com", "automail-yo-kan") ;

	$mailer->AddAddress($email);
	$mailer->Subject = mb_encode_mimeheader($subject, "utf-8");
	$mailer->Body    = mb_convert_encoding($body, "utf-8" );
	
	try {
		$mailer->Send();
		echo '<br><br><br>サイト管理者へメールを送信しました。ありがとうございました。 SMTP<br><br><br>' ;
		
	} catch (Exception $e) {
		
		echo '<br><br><br>申し訳ありません。サーバーのエラーのため送信できませんでした。 error2<br><br><br><br><br>' ;
	}

// 3 ローカルSMTP利用
}else{

	//　mb_send_mail関数は、「警告エラー」の場合（Azure）と、関数は起動するがメール送信をせずFalseを返す場合（Heroku）があることに留意
	try {
		if(mb_send_mail($email, $subject, $body , $strHeader)){ // mb_send_mail が使えるサーバーの場合（レンタルサーバー等）
			echo '<br><br><br>サイト管理者へメールを送信しました。ありがとうございました。 mb_send_mail<br><br><br>' ;
			
		} else { // mb_send_mail関数は起動するがメール送信をせずFalseを返す場合
			echo '<br><br><br>申し訳ありません。サーバーのエラーのため送信できませんでした。 error3<br><br><br><br><br>' ;
		}
		
	} catch (Exception $e) { // mb_send_mail関数が警告エラーとなる場合
		echo '<br><br><br>申し訳ありません。サーバーのエラーのため送信できませんでした。 error4<br><br><br><br><br>' ;
	}
}

?>
</div>
<footer class="site-footer">
<div class="site-footer-inner">
<?php echo $site_setting[0]['s8_footerText']."\n"; ?>
</div>
</footer>
</body>
</html>