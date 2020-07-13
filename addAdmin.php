<?php
// ini_set('display_errors', "On");
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars定義関数
require_once './makeLog.php'; //ログファイル書き込み関数を呼び出し

// ポスト有無の確認
if(!isset($_POST["user_name"])){ // ポストがない場合
	unset($_SESSION['sessionname']); //セッションの中身をクリア
	// ログ記録
	$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;
	makeLog($url.' => 不正なアクセスにより、ログインページに飛ばされる') ;
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
<title>管理者の登録</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
</head>
<body>
<div class="contentS">
<?php
$id_length = strlen($_POST["user_name"]);
$ps_length = strlen($_POST["password"]);
$reg_str = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/"; // メールの判定
	
if($ps_length != 64){
	makeLog('【不正アクセス注意】何者かが不正なアクセスにより、ID:'.$_POST["user_name"].' で管理者登録しようとしたが失敗') ;
	exit("不正アクセスの可能性があります。");
	
}else if( $id_length < 6 || $id_length > 30 ){
	echo 'アカウントIDは 6字以上 30字以内 にしてください。<br><br>'."\n";
	echo '<a href="login.php">管理者アカウント登録フォームへ</a>'."\n";

}else if (!preg_match($reg_str, $_POST['mail'])) {
	echo "メールアドレスの形式が不正です。入力し直してください。<hr/>"."\n";
	echo '<a href="login.php">管理者アカウント登録フォームへ</a>'."\n";

}else{
	// ハッシュ化
	$passHash  = hash("sha256",($_POST["password"]."opendata")); //ソルトを入れてハッシュ
	for ($i = 0; $i < 1000; $i++){ //ストレッチング1000回
		$passHash  = hash("sha256",$passHash);
	}
	
	//メモリ上の配列に追加
	$admin_info[] = array('id'=>$_POST['user_name']) + array('pass'=>$passHash) + array('name'=>'管理者') + array('mail'=>$_POST['mail']) + array('rock'=>0) ;
	$user_info = array();
	$site_setting[] = array('s0_siteName'=>'オープンデータストア（仮）') 
		+ array('s1_backgroundImg'=>'N') 
		+ array('s2_snsText'=>'FacebookやTwitterでシェアされたときに表示される一文です。') 
		+ array('s3_snsImg'=>'N') 
		+ array('s4_headerName'=>'オープンデータストア（仮）') 
		+ array('s5_headerBanner'=>'N') 
		+ array('s6_contentTextTop'=>'この部分はトップページコンテンツの「上段部分」です。自由に編集できます。<b>HTMLタグが使えます。</b><br>ログインフォームは<a href="login.php">こちら</a>です。') 
		+ array('s7_contentTextBottom'=>'この部分はトップページコンテンツの「下段部分」です。自由に編集できます。<b>HTMLタグが使えます。</b>') 
		+ array('s8_footerText'=>'この部分は全ページ共通のフッターです。<br><b>HTMLタグが使えます。</b>') 
		+ array('s9_maxDisplayData'=>'10') ;

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
	
	// ログ記録
	makeLog('ID:'.$_POST["user_name"].' により管理者の登録が正常に行われた') ;
	
	echo '管理者用アカウントを作成しました。<br><br>'."\n";
	echo '<a href="login.php">ログインフォームへ</a>'."\n";		
}
?>
</div>
</body> 
</html>