<?php
// ini_set('display_errors', "On");
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars定義関数
require_once './makeLog.php'; //ログファイル書き込み関数を呼び出し
if(isset($_SESSION['sessionname'])) { //セッションがすでにある
	header( "Location: ./controlpanel.php" ) ; //コントロールパネルにとぶ
	exit;
}
if(!isset($_POST["user_name"])){ //ポストなし
	header( "Location: ./login.php" ) ; //ログインページにとぶ
	exit;
}

// セッショントークンの確認
$token = $_POST['token']; //tokenを変数に入れる
if(!(hash_equals($token, $_SESSION['token']) && !empty($token))) { 
	$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;
	makeLog($url.' => 【不正アクセス注意】何者かが不正なアクセスによりログインを試みたが失敗') ;
	exit("不正アクセスの可能性があります。");
}

// サーバーキャッシュのクリアのための処理
$fileName = "accountData.php" ;
copy($fileName, $fileName.'copy'); // コピーを作成
unlink($fileName);                 // 原本を削除
copy($fileName.'copy', $fileName); // コピーから原本を再作成
unlink($fileName.'copy');          // コピーを削除
	
require_once 'accountData.php'; //accountData.phpを呼び出し

$passHash  = hash("sha256",($_POST["password"]."opendata")); //ソルト入りパスワードをまず１回ハッシュ化
for ($i = 0; $i < 1000; $i++){ //ストレッチング1000回
	$passHash  = hash("sha256",$passHash);
}

$login = false ; //ログイン成功したかどうか（成功=true）

///////////////////////////////////////////  ①ログイン成功
//管理者 かつ パスワード合致 かつ ロックされていない
if($admin_info[0]['id']===$_POST["user_name"] && $admin_info[0]['pass']===$passHash && $admin_info[0]['rock']===0){

	$_SESSION['sessionname'] = $_POST["user_name"] ; //セッションをセット
	
	// ログ記録
	makeLog($_POST["user_name"].' => 管理者として正常にログイン') ;
	
	unset($_SESSION['loginFailure']); //ログイン失敗セッションをクリア
	
	$login = true ;
	
	// 深いところにありheaderでは難しいのでメタタグでリダイレクト
	// header( "Location: ./controlpanel.php" ) ; //会員ページにとぶ
	echo '<meta http-equiv="refresh" content=" 0; url=./controlpanel.php">'; 
	exit;
	
}else{
	for ($i=0; $i<count($user_info); $i++) {
		//ユーザー かつ パスワード合致 かつ ロックされていない
		if($user_info[$i]['id']===$_POST["user_name"] && $user_info[$i]['pass']===$passHash && $user_info[$i]['rock']===0 ){
			$_SESSION['sessionname'] = $_POST["user_name"] ; //セッションをセット
			
			// ログ記録
			makeLog($_POST["user_name"].' => 正常にログイン') ;
			
			$login = true ;
			unset($_SESSION['loginFailure']); //ログイン失敗セッションをクリア
			
			// 深いところにありheaderでは難しいのでメタタグでリダイレクト
			// header( "Location: ./controlpanel.php" ) ; //会員ページにとぶ
			echo '<meta http-equiv="refresh" content=" 0; url=./controlpanel.php">'; 
			exit;
			
			break;
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0" />
<meta name="robots" content="noindex,nofollow,noarchive" /> <!-- 検索エンジンに登録させない -->
<title>ログインの判定</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
</head>
<body>
<div class="contentS">

<?php

$rockFlag = false ; //ロックされているかどうか（ロック=true）

///////////////////////////////////////////  ロックがかかっている場合
//管理者
if($_POST["user_name"] === $admin_info[0]['id'] && $admin_info[0]['rock'] === 1){
	$rockFlag = true ;
	echo "ID:".$_POST["user_name"]."はロックされています。対応方法を管理者のメールへ送信していますのでご確認ください。<br><br>" ;
	
	// ログ記録
	makeLog('ロックされている管理者のID:'.$_POST["user_name"].' でログインを試みたが失敗') ;
	
	echo "<a href='login.php'>ログインフォームへ</a><br><br>" ;
	$login = true ; //成功でないが成功扱いにして失敗時の処理に移行させないようにする。
}
//一般ユーザー
for ($j=0; $j<count($user_info); $j++) {
	if($_POST["user_name"] === $user_info[$j]['id'] && $user_info[$j]['rock'] === 1){ 
		$rockFlag = true ;
		echo "ID:".$_POST["user_name"]."はロックされています。ロックを解除する場合は管理者に依頼してください。<br><br>" ;
			
		// ログ記録
		makeLog('ロックされているID:'.$_POST["user_name"].' でログインを試みたが失敗') ;
	
		echo "<a href='login.php'>ログインフォームへ</a><br><br>" ;
		$login = true ; //成功でないが成功扱いにして失敗時の処理に移行させないようにする。
		break;
	}
}

///////////////////////////////////////////  ログイン失敗
if(!$login){ 
	unset($_SESSION['sessionname'] ); //セッションの中身をクリア
	echo "ID またはパスワードが違います。<br><br>" ;
	echo "<a href='login.php'>ログインフォームへ</a><br><br>" ;
	
	
	if(!isset($_SESSION['loginFailure'])){ //ログイン失敗のセッションが無ければ失敗セッションを新規作成
		$_SESSION['loginFailure'][] = array('ID'=>$_POST["user_name"] , 'count'=>1 );
		
		// ログ記録
		makeLog('ID:'.$_POST["user_name"].' でのログイン失敗（ID又はパスワード違い） / 失敗'.$_SESSION['loginFailure'][0]['count'].'回目') ;

	}else{ //ログイン失敗のセッションが既にある場合
		$newFailFlag = true; //新規のIDかどうかの判定フラグ
		for ($i=0; $i<count($_SESSION['loginFailure']); $i++) {
			if($_SESSION['loginFailure'][$i]['ID'] ===  $_POST["user_name"]){ //失敗のIDがあれば
				$_SESSION['loginFailure'][$i]['count']++ ; //カウントを増やす
				$newFailFlag = false ; //新規のIDではなかったフラグ
				break;
			}
		}
		if($newFailFlag){ //新規フラグの場合
			$_SESSION['loginFailure'][] = array('ID'=>$_POST["user_name"] , 'count'=>1 ); //配列の最後に追加	
		}

		// ログ記録
		makeLog('ID:'.$_POST["user_name"].' でのログイン失敗（ID又はパスワード違い） / 失敗'.$_SESSION['loginFailure'][$i]['count'].'回目') ;
	}

	for ($i=0; $i<count($_SESSION['loginFailure']); $i++) { //$_SESSION['loginFailure']の配列数をカウント
		
		if($_SESSION['loginFailure'][$i]['ID'] === $_POST["user_name"]){ 
		
			// アカウントのロックフラグをたてる処理
			if($_SESSION['loginFailure'][$i]['count']>4){ //失敗カウントが５になったら

				$_SESSION['loginFailure'][$i]['count'] = 0; //セッションの失敗カウントを0にリセット

				if($admin_info[0]['id'] === $_POST["user_name"]){

					echo "アカウントID: ".$_POST["user_name"]." はロックされました。<br>" ;
					
					// ログ記録
					makeLog('【不正アクセスに注意】ID:'.$_POST["user_name"].'（管理者）でのログインに連続5回失敗したのでアカウントロック') ;

					// makeLog('アカウントロック解除のための案内メールをシステムから管理者のメールアドレスへ送信') ;

					$admin_info[0]['rock'] = 1 ; //管理者をロック
					sendMessage( $admin_info[0]['mail'], $admin_info[0]['id'] ) ; // ロック解除するためのメール送信関数
					
				}
				for ($j=0; $j<count($user_info); $j++) {
					if($user_info[$j]['id'] === $_POST["user_name"]){ 
						echo "アカウントID: ".$_POST["user_name"]." はロックされました。ロックを解除する場合は管理者に依頼してください。<br><br>" ;
						
						// ログ記録
						makeLog('【不正アクセスに注意】ID:'.$_POST["user_name"].' でのログインに連続5回失敗したのでアカウントロック') ;
						
						$user_info[$j]['rock'] = 1 ; //ユーザーをロック（ユーザーの場合はメールなし）
						break;
					}
				}
				//書き込むテキストの生成
				$inputText = '<?php'."\n";
				$inputText .= '$admin_info = '.var_export($admin_info,true).' ;'."\n"; //var_export の第２引数にtrueを入れると文字列として変数に代入できる
				$inputText .= '$user_info = '.var_export($user_info,true).' ;'."\n"; //var_export の第２引数にtrueを入れると文字列として変数に代入できる
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
			break;
		}
	}		
}

//ロック解除するためのメール送信
function sendMessage($mailAddress, $id) {
	mb_language("Japanese"); 
	mb_internal_encoding("UTF-8");

	// 変数の設定
	$email = $mailAddress ;
	$subject = "【自動応答】アカウントのロックについて";
	//$strHeader = "From: automail@yo-kan.com";
	$body = "（これはYo-KANシステムからの自動応答メールです。このメールには返信できません。）"."\n"."\n";
	$body .= "管理者アカウントへのログインに５回失敗したため、管理者アカウントがロックされました。"."\n";
	$body .= "ロックを解除する場合は以下のURLにアクセスしてください"."\n";
	
	$myPath = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; //このファイルのフルパス

	//$dirname = dirname($myPath); //親ディレクトリのパス //以下の通り変更
	$dirname = str_replace('/loginProcess.php', '', $myPath);
	$randomTxt = substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 36).".php"; //36文字のランダムテキスト + 拡張子php
	$body .= $dirname . "/" . $randomTxt ; //解除のURL

	//headerを設定
	$charset = "UTF-8";
	$headers['MIME-Version'] = "1.0";
	$headers['Content-Type'] = "text/plain; charset=".$charset;
	$headers['Content-Transfer-Encoding'] = "8bit";
	$headers['From'] = "automail@yo-kan.com" ; 

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
		$emailArr->setFrom("automail@yo-kan.com", "automail-yo-kan");
		$emailArr->setSubject($subject);
		$emailArr->addTo($email, "サイト管理者");
		$emailArr->addContent("text/plain", $body);
		$sendgrid = new \SendGrid($_ENV['SENDGRID_API_KEY']);
		
		try {
			$sendgrid->send($emailArr);
			makeFileForRelease($randomTxt, $id, $mailAddress, $dirname); //解除のためのファイル作成関数
			echo "対応方法を管理者のメールへ送信しましたのでご確認ください。 SendGrid<br>" ;
			echo "※ メールが届かない場合は迷惑メールとしてブロックされている可能性がありますのでご確認ください。" ;
			makeLog('アカウントロック解除のための案内メールをシステムから管理者のメールアドレスへ送信') ;
			
		} catch (Exception $e) {
			
			echo 'サーバーのエラーで、アカウントロック解除のための案内メールを送信できませんでした。 error1'."\n";
			makeLog('アカウントロック解除のための案内メールを送信しようとしたがサーバーエラーで送信できず') ;
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
		$mailer->setFrom("automail@yo-kan.com", "automail-yo-kan") ;

		$mailer->AddAddress($email);
		$mailer->Subject = mb_encode_mimeheader($subject, "utf-8");
		$mailer->Body    = mb_convert_encoding($body, "utf-8" );
		
		try {
			$mailer->Send();
			makeFileForRelease($randomTxt, $id, $mailAddress, $dirname); //解除のためのファイル作成関数
			echo "対応方法を管理者のメールへ送信しましたのでご確認ください。 SMTP<br>" ;
			echo "※ メールが届かない場合は迷惑メールとしてブロックされている可能性がありますのでご確認ください。" ;
			makeLog('アカウントロック解除のための案内メールをシステムから管理者のメールアドレスへ送信') ;
			
		} catch (Exception $e) {

			echo 'サーバーのエラーで、アカウントロック解除のための案内メールを送信できませんでした。 error2'."\n";
			makeLog('アカウントロック解除のための案内メールを送信しようとしたがサーバーエラーで送信できず') ;
		}

	// 3 ローカルSMTP利用
	}else{

		//　mb_send_mail関数は、「警告エラー」の場合（Azure）と、関数は起動するがメール送信をせずFalseを返す場合（Heroku）があることに留意
		try {
			if(mb_send_mail($email, $subject, $body , $strHeader)){ // mb_send_mail が使えるサーバーの場合（レンタルサーバー等）
				makeFileForRelease($randomTxt, $id, $mailAddress, $dirname); //解除のためのファイル作成関数
				echo "対応方法を管理者のメールへ送信しましたのでご確認ください。 <br>" ;
				echo "※ メールが届かない場合は迷惑メールとしてブロックされている可能性がありますのでご確認ください。" ;
				makeLog('アカウントロック解除のための案内メールをシステムから管理者のメールアドレスへ送信') ;
				
			} else { // mb_send_mail関数は起動するがメール送信をせずFalseを返す場合
				echo 'サーバーのエラーで、アカウントロック解除のための案内メールを送信できませんでした。 error3'."\n";
				makeLog('アカウントロック解除のための案内メールを送信しようとしたがサーバーエラーで送信できず') ;
			}
			
		} catch (Exception $e) { // mb_send_mail関数が警告エラーとなる場合
			echo 'サーバーのエラーで、アカウントロック解除のための案内メールを送信できませんでした。 error4'."\n";
			makeLog('アカウントロック解除のための案内メールを送信しようとしたがサーバーエラーで送信できず') ;
		}
	}
}


//ロック解除するためのファイル作成
function makeFileForRelease($filename, $id, $mailAddress, $dirname) {
	$inputText1 = '<!DOCTYPE html><html><head><meta charset=\'UTF-8\'><title>ロック解除</title></head><body>'."\n";
	$inputText1 .= "<?php"."\n";
	
	// ログ記録
	$inputText1 .= 'require_once \'./makeLog.php\';'."\n";
	$inputText1 .= 'makeLog(\'管理者のパスワード再発行のメールをシステムから管理者のメールアドレスへ送信。\') ;'."\n";
	
	//ロックを解除
	$inputText1 .= '$fileName = \'accountData.php\' ;'."\n";
	$inputText1 .= 'copy($fileName, $fileName.\'copy\');'."\n";
	$inputText1 .= 'unlink($fileName);'."\n";
	$inputText1 .= 'copy($fileName.\'copy\', $fileName);'."\n";
	$inputText1 .= 'unlink($fileName.\'copy\');'."\n";
	$inputText1 .= 'require_once \'accountData.php\';'."\n";
	$inputText1 .= '$admin_info[0][\'rock\'] = 0 ;'."\n";

	//新パスワード生成
	$randomPass = substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 10) ; //10文字のランダムテキスト

	// JSと同一のハッシュ作成ロジック
	$passHash = $randomPass.'opendata'.$randomPass ; //レインボーテーブル対策のソルト
	$passHash  = hash("sha256",$passHash);		
	for ($i = 0; $i < 1000; $i++){ //ストレッチング1000回
		$passHash  = hash("sha256",$passHash);
	}
		
	// ハッシュ化
	$passHash  = hash("sha256",($passHash."opendata")); //ソルト入りパスワードをまず１回ハッシュ化
	for ($i = 0; $i < 1000; $i++){ //ストレッチング1000回
		$passHash  = hash("sha256",$passHash);
	}
	
	$inputText1 .= '$admin_info[0]["pass"] = \''.$passHash.'\' ;'."\n";

	//メールを再送信する部分
	$inputText1 .= 'mb_language(\'Japanese\'); '."\n";
	$inputText1 .= 'mb_internal_encoding(\'UTF-8\'); '."\n";
	$inputText1 .= '$email = \''.$mailAddress.'\' ; '."\n";
	$inputText1 .= '$subject = \'【自動応答】管理者パスワードの再発行について\'; '."\n";
	$inputText1 .= '$body = \'（これはYo-KANシステムからの自動応答メールです。このメールには返信できません。）\'."\n"."\n"; '."\n";
	$inputText1 .= '$body .= \'管理者アカウントのパスワードを再発行しました。以下のIDとパスワードでログインしてください。\'."\n"; '."\n";
	$inputText1 .= '$body .= \'ログイン後、パスワードは変更できます。\'."\n"."\n"; '."\n";
	$inputText1 .= '$body .= \'管理者アカウントID：　\'; '."\n";
	$inputText1 .= '$body .= \''.$id.'\'."\n"; '."\n";
	$inputText1 .= '$body .= \'管理者パスワード：　\'; '."\n";
	$inputText1 .= '$body .= \''.$randomPass.'\'."\n"."\n"; '."\n";
	
	$inputText1 .= '$charset = "UTF-8";'."\n";
	$inputText1 .= '$headers[\'MIME-Version\'] = "1.0";'."\n";
	$inputText1 .= '$headers[\'Content-Type\'] = "text/plain; charset=".$charset;'."\n";
	$inputText1 .= '$headers[\'Content-Transfer-Encoding\'] = "8bit";'."\n";
	$inputText1 .= '$headers[\'From\'] = "automail@yo-kan.com" ; '."\n";
	$inputText1 .= 'foreach ($headers as $key => $val) {'."\n";
	$inputText1 .= '$arrheader[] = $key . \': \' . $val;}'."\n";
	$inputText1 .= '$strHeader = implode("\n", $arrheader);'."\n";
		
	$inputText1 .= 'if(isset($_ENV[\'SENDGRID_API_KEY\'])){'."\n";
	$inputText1 .= '$body .= \'SendGrid\'."\n"."\n"; '."\n";
	$inputText1 .= 'require \'vendor/autoload.php\';'."\n"; 
	$inputText1 .= '$emailArr = new \SendGrid\Mail\Mail();'."\n";
	$inputText1 .= '$emailArr->setFrom(\'automail@yo-kan.com\', \'\');'."\n";
	$inputText1 .= '$emailArr->setSubject($subject);'."\n";
	$inputText1 .= '$emailArr->addTo($email,\'\');'."\n";
	$inputText1 .= '$emailArr->addContent(\'text/plain\', $body);'."\n";
	$inputText1 .= '$sendgrid = new \SendGrid($_ENV[\'SENDGRID_API_KEY\']);'."\n";
	$inputText1 .= '$sendgrid->send($emailArr);'."\n";
	
	$inputText1 .= '}else if(isset($_ENV[\'SMTP_HOST\']) && isset($_ENV[\'SMTP_ACCOUNT\']) && isset($_ENV[\'SMTP_PASSWORD\'])){'."\n";
	$inputText1 .= '$body .= \'SMTP\'."\n"."\n"; '."\n";
	$inputText1 .= 'require_once( dirname( __FILE__ ).\'/vendor/phpmailer/phpmailer/src/PHPMailer.php\' );'."\n";
	$inputText1 .= 'require_once( dirname( __FILE__ ).\'/vendor/phpmailer/phpmailer/src/Exception.php\' );'."\n";
	$inputText1 .= 'require_once( dirname( __FILE__ ).\'/vendor/phpmailer/phpmailer/src/SMTP.php\' );'."\n";
	$inputText1 .= '$mailer = new \PHPMailer\PHPMailer\PHPMailer(true);'."\n";
	$inputText1 .= '$mailer->CharSet = "UTF-8";'."\n";
	$inputText1 .= '$mailer->Encoding = "7bit";'."\n";
	$inputText1 .= '$mailer->IsSMTP();'."\n";
	$inputText1 .= '$mailer->Host = $_ENV[\'SMTP_HOST\'];'."\n";
	$inputText1 .= '$mailer->SMTPAuth = true;'."\n";
	$inputText1 .= '$mailer->SMTPDebug = 0;'."\n";
	$inputText1 .= '$mailer->SMTPSecure = "tls";'."\n";
	$inputText1 .= '$mailer->Port = 587;'."\n";
	$inputText1 .= '$mailer->Username = $_ENV[\'SMTP_ACCOUNT\'] ;'."\n";
	$inputText1 .= '$mailer->Password = $_ENV[\'SMTP_PASSWORD\'] ;'."\n";
	$inputText1 .= '$mailer->setFrom("automail@yo-kan.com", "automail-yo-kan") ;'."\n";
	$inputText1 .= '$mailer->AddAddress($email);'."\n";
	$inputText1 .= '$mailer->Subject = mb_encode_mimeheader($subject, "utf-8");'."\n";
	$inputText1 .= '$mailer->Body    = mb_convert_encoding($body, "utf-8" );'."\n";
	$inputText1 .= '$mailer->Send();'."\n";
	
	$inputText1 .= '}else{'."\n";
	$inputText1 .= 'mb_send_mail($email, $subject, $body , $strHeader);'."\n";
	$inputText1 .= '}'."\n";
	
	//アカウントデータを上書きするコードの生成
	$inputText1 .= '$inputText = \'<?php\'."\n";'."\n";
	$inputText1 .= '$inputText .= \'$admin_info = \'.var_export($admin_info,true).\' ;\'."\n";'."\n";
	$inputText1 .= '$inputText .= \'$user_info = \'.var_export($user_info,true).\' ;\'."\n";'."\n";
	$inputText1 .= '$inputText .= \'$site_setting = \'.var_export($site_setting,true).\' ;\'."\n";'."\n";
	$inputText1 .= '$inputText .= \'?>\'."\n";'."\n";
	
	$inputText1 .= '$fp = fopen(\'accountData.php\', \'a\');'."\n";
	$inputText1 .= 'if (flock($fp, LOCK_EX)) {'."\n";
	$inputText1 .= 'ftruncate($fp, 0);'."\n";
	$inputText1 .= 'fwrite($fp, $inputText);'."\n";
	$inputText1 .= 'fflush($fp);'."\n";
	$inputText1 .= 'flock($fp, LOCK_UN);'."\n";
	$inputText1 .= '}fclose($fp);'."\n";
	
	//メッセージ
	$inputText1 .= 'echo "管理者アカウントのロックを解除し、パスワードを再発行しました。<br>" ;'."\n";
	$inputText1 .= 'echo "仮パスワードをメールで送信しましたので確認してください。<br><br>" ;'."\n";
	$inputText1 .= 'echo "<a href=\"login.php\">ログインフォームへ</a><br><br>" ;'."\n";
	
	//自己ファイルを削除
	$inputText1 .= 'unlink(\''.$filename.'\');'."\n"; // 検証の際はコメント化する
		
	$inputText1 .= '?>'."\n";
	$inputText1 .= '</body></html>'."\n";
	//解除ファイル作成
	$fp = fopen($filename, "w");
	fwrite($fp, $inputText1);
	fclose($fp);
}
?>
</div>
</body> 
</html>