<?php
// ini_set('display_errors', "On");
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars
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
	header( "Location: ./login.php" ) ;
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0" />
<meta name="robots" content="noindex,nofollow,noarchive" /> <!-- 検索エンジンに登録させない -->
<title>操作ログの確認</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
</head>
<body>
<div class="contentS">
<?php
echo "管理者アカウント ".hsc($_SESSION['sessionname'])." でログイン中です。" ;
echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
echo "<hr>"."\n";

foreach(glob('log/{*.php}',GLOB_BRACE) as $fileName){ // logディレクトリ内のファイルを.php の拡張子を指定してサーチ
	if(is_file($fileName)){
		$fileName = $fileName ; // これで最新の（一番大きい）ファイルネームが取得できる
	}
}

// 番号だけに加工
$fileNum = str_replace('log/', '', $fileName) ; // log/ をとる
$fileNum = str_replace('.php', '', $fileNum) ; // .php をとる
$fileNum = (int)$fileNum ; //int型に変換		

for ($i=0; $i<$fileNum; $i++) {
	echo '<li><a href="./log/check/log'.($i + 1).'.php">ログデータ No.'.($i + 1).'</a></li>'."\n";
}
echo "<hr>"."\n";
echo '<a href="redirect.html">管理画面に戻る</a>'; 
?>
</div>
</body> 
</html>