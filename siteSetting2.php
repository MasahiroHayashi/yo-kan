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

// 「上記の内容を反映させる」がポストされた場合
if(isset($_POST["s0_siteName"])) {
	
	// ログ記録
	makeLog($_SESSION['sessionname'].' => [ サイトのタイトル / 表示させる文章 / 見た目の設定・管理 ] の修正が正常に実行された') ;
	
	echo '[ サイトのタイトル / 表示させる文章 / 見た目の設定・管理 ] の修正が正常に実行されました。<br><br>'."\n";
	echo '<a href="index.php" target="_blank">サイトのホーム画面を確認する</a><br><br>'; 
	
	//メモリ上の配列を修正
	$site_setting[0]['s0_siteName'] = $_POST["s0_siteName"] ;
	$site_setting[0]['s1_backgroundImg'] = $_POST["s1_backgroundImg"] ;
	$site_setting[0]['s2_snsText'] = $_POST["s2_snsText"] ;
	$site_setting[0]['s3_snsImg'] = $_POST["s3_snsImg"] ;
	$site_setting[0]['s4_headerName'] = $_POST["s4_headerName"] ;
	$site_setting[0]['s5_headerBanner'] = $_POST["s5_headerBanner"] ;
	$site_setting[0]['s6_contentTextTop'] = $_POST["s6_contentTextTop"] ;
	$site_setting[0]['s7_contentTextBottom'] = $_POST["s7_contentTextBottom"] ;
	$site_setting[0]['s8_footerText'] = $_POST["s8_footerText"] ;
	$site_setting[0]['s9_maxDisplayData'] = $_POST["s9_maxDisplayData"] ;
	
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
	
// 「アップロードする」がポストされた場合
}else if(isset($_FILES['fname'])) {
	
	$tempfile = $_FILES['fname']['tmp_name'];
	if (is_uploaded_file($tempfile)) { //まず一時ディレクトリに移動できたらtrue
		$filename = './img_uploaded/'.$_FILES['fname']['name'];
		//拡張子のチェック（下の方に独自関数あり）
		if (!checkExt($_FILES['fname']['name'])) {
			echo '【エラー】 '.$_FILES['fname']['name'].' はアップロードできません。アップロード可能な画像ファイルは JPEG または PNG です。<br><br>';
			
		}else{
			//move_uploaded_file関数で一時ディレクトリから指定ディレクトリに移動する。それが成功したらtrueを返す。
			if ( move_uploaded_file($tempfile , $filename )) {
				makeLog($_SESSION['sessionname'].' => サイトのデザイン用画像のアップロードが正常に実行された') ;
				echo '画像のアップロードが正常に実行されました。<br><br>'."\n";
				
			}else{
				makeLog($_SESSION['sessionname'].' => サイトのデザイン用画像のアップロードがサーバーエラーで失敗') ;
				echo '【エラー】サーバーのエラーでアップロードが失敗しました。<br><br>'."\n";
			}
		}
	}else{
		echo '【エラー】アップロードするファイルを選択してください。<br><br>'."\n";
	}
	
// 「削除する」がポストされた場合
}else if(isset($_POST["del"])) {
	
	$filename = './img_uploaded/'.$_POST["del"] ;
	unlink($filename);
	
	makeLog($_SESSION['sessionname'].' => サイトのデザイン用画像 ['.$_POST["del"].'] を削除') ;
	echo '画像を正常に削除しました。<br><br>'."\n";
	
}else{
	echo '【エラー】削除するファイルを選択してください。<br><br>'."\n";
}

echo "<hr>"."\n";
echo '<a href="siteSetting.php">ひとつ前（修正画面）に戻る</a><br>'; 
echo '<a href="redirect.html">管理画面に戻る</a>'; 

//ファイル名から拡張子を取得する関数
function getExt($filename) {
	return pathinfo($filename, PATHINFO_EXTENSION);
}
//アップロードされたファイル名の拡張子が許可されているか確認する関数
function checkExt($filename) {
	global $cfg;
	$ext = strtolower(getExt($filename));
	return in_array($ext, array('jpg', 'jpeg', 'png'));//アップロードを許可する拡張子
}
?>
</div>
</body> 
</html>