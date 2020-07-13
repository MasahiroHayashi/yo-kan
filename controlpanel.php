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
$fileName = 'accountData.php' ;
if (file_exists($fileName)) {
	copy($fileName, $fileName.'copy'); // コピーを作成
	unlink($fileName);                 // 原本を削除
	copy($fileName.'copy', $fileName); // コピーから原本を再作成
	unlink($fileName.'copy');          // コピーを削除
	require_once './accountData.php'; //allDataList.phpを呼び出し
}else{
	unset($_SESSION['sessionname']); //セッションの中身をクリア
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
<title>管理画面ホーム</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
</head>
<body>
<div class="contentS">
<?php
//管理者でログインした場合
if($_SESSION['sessionname']===$admin_info[0]['id']){
	
	echo "管理者アカウント ".hsc($_SESSION['sessionname'])." でログイン中です。" ;
	echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
	echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
	echo "<hr>"."\n";
	echo "--- ユーザー情報 ---<br>"."\n" ;
	echo "アカウントID : ".hsc($_SESSION['sessionname'])."<br>"."\n" ;
	echo "ユーザー名 : ".hsc($admin_info[0]['name'])."<br>"."\n" ;
	echo "メールアドレス : ".hsc($admin_info[0]['mail']).""."\n" ;
	echo "<hr>" ;
	echo '<h2>管理画面ホーム（管理者用）</h2>'."\n" ;
	echo '<li><a href="siteSetting.php">サイトのタイトル / 表示させる文章 / 見た目の設定・管理</a></li>'."\n" ;
	echo '<li><a href="dataUpload.php">公開データの新規登録</a></li>'."\n" ;
	echo '<li><a href="dataChange.php">公開データの修正</a></li>'."\n" ;
	echo '<li><a href="dataDelete.php">公開データの削除</a></li>'."\n" ;
	echo '<li><a href="changeAdminPass.php">管理者アカウントのログインパスワード変更</a></li>'."\n" ;
	echo '<li><a href="changeAdminName.php">管理者アカウントのユーザー名変更</a></li>'."\n" ;
	echo '<li><a href="changeAdminMail.php">管理者アカウントのメールアドレス変更</a></li>'."\n" ;
	echo '<li><a href="addUser.php">一般アカウントの追加 / 削除</a></li>'."\n" ;
	echo '<li><a href="releaseRock.php">一般アカウントのロック解除</a></li>'."\n" ;
	echo '<li><a href="makeLogCheck.php">操作ログの確認</a></li>'."\n" ;
	echo '<li><a href="resetting.php">操作ログの消去 / データの全消去 / システムの初期化など</a></li>'."\n" ;
	echo "<hr>" ;
	echo '<a href="./" target="_blank">トップページを確認</a>（別タブで開きます）'."\n" ;

//ユーザーでログインした場合
}else{
	for ($i=0; $i<count($user_info); $i++) {
		
		if($user_info[$i]['id'] === $_SESSION['sessionname'] ){
			
			echo hsc($_SESSION['sessionname'])." でログイン中です。" ;
			echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
			echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
			echo "<hr>"."\n";
			echo "--- ユーザー情報 ---<br>"."\n" ;
			echo "アカウントID : ".hsc($_SESSION['sessionname'])."<br>"."\n" ;
			echo "ユーザー名 : ".hsc($user_info[$i]['name'])."<br>"."\n" ;
			echo "<hr>" ;
			echo '<h2>管理画面ホーム（一般ユーザー用）</h2>'."\n" ;
			echo '<li><a href="dataUpload.php">公開データの新規登録</a></li>'."\n" ;
			echo '<li><a href="dataChange.php">公開データの修正</a></li>'."\n" ;
			echo '<li><a href="dataDelete.php">公開データの削除</a></li>'."\n" ;
			echo '<li><a href="changeUserPass.php">ログインパスワード変更</a></li>'."\n" ;
			echo '<li><a href="changeUserName.php">ユーザー名の変更</a></li>'."\n" ;
			echo "<hr>" ;
			echo '<a href="./" target="_blank">トップページを確認</a>（別タブで開きます）'."\n" ;
			break;
		}
	}
}
?>
</div>
</body> 
</html>