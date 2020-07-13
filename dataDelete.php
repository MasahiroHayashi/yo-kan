<?php
// ini_set('display_errors', "On");
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars定義関数
require_once './makeLog.php'; //ログファイル書き込み関数を呼び出し
if(!isset($_SESSION['sessionname'])) { //セッションがない場合
	// ログ記録
	$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;
	makeLog($url.' => 【エラー】ログインなしでダイレクトアクセスし、ログインページに飛ばされる。') ;
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
<title>データの削除</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
</head>
<body>
<div class="contentS">
<?php
// サーバーキャッシュのクリアのための処理
$fileName = "accountData.php" ;
copy($fileName, $fileName.'copy'); // コピーを作成
unlink($fileName);                 // 原本を削除
copy($fileName.'copy', $fileName); // コピーから原本を再作成
unlink($fileName.'copy');          // コピーを削除

require_once './accountData.php'; //accountData.phpを呼び出し

//管理者でログインした場合
if($_SESSION['sessionname']===$admin_info[0]['id']){
	
	echo "管理者アカウント ".hsc($_SESSION['sessionname'])." でログイン中です。" ;
	echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
	echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
	echo "<hr>"."\n";

//ユーザーでログインした場合
}else{
	for ($i=0; $i<count($user_info); $i++) {
		if($user_info[$i]['id'] === $_SESSION['sessionname'] ){
			echo hsc($_SESSION['sessionname'])." でログイン中です。" ;
			echo '<form action="logout.php" method="POST" style="display:inline">'."\n";
			echo '<input name="logout" class="logoutButton" type="submit" value="ログアウト"></form>'."\n";
			echo "<hr>"."\n";
		}
	}
}

// ディレクトリの中身とディレクトリそのものを再帰的に全部削除する関数
function rmdirAll($dir) {
	// 指定されたディレクトリ内の一覧を取得
	$res = glob($dir.'/*');
 	// 一覧をループ
	foreach ($res as $f) {
		// is_file() を使ってファイルかどうかを判定
		if (is_file($f)) {
			// ファイルならそのまま出力
			unlink($f);
		} else {
			// ディレクトリの場合（ファイルでない場合）は再度rmdirAll()を実行
			rmdirAll($f);
		}
	}
	// 中身を削除した後、本体削除
	rmdir($dir);
}

if(file_exists ('./allDataList.php')){ // allDataList.php が「ある」＝「初回でない」場合
	
	// サーバーキャッシュのクリアのための処理
	$fileName = "allDataList.php" ;
	copy($fileName, $fileName.'copy'); // コピーを作成
	unlink($fileName);                 // 原本を削除
	copy($fileName.'copy', $fileName); // コピーから原本を再作成
	unlink($fileName.'copy');          // コピーを削除
		
	require_once './allDataList.php'; //allDataList.phpを呼び出し 

	if(isset($_POST["del"])) {
		for ($i=0; $i<count($alldata); $i++) {
			if($alldata[$i]['num']===$_POST["del"]){

				//指定したディレクトリの中身とディレクトリそのものを再帰的に全部削除
				rmdirAll('data/'.$_POST["del"]) ;
				
				//メモリ上のデータの処理
				unset($alldata[$i]); //配列から削除
				$alldata = array_values($alldata); //歯抜けになった配列を詰める
				break;
			}
		}
		
		//書き込むテキストの生成
		$inputText = '<?php'."\n";
		$inputText .= '$datanum = '.$datanum.' ;'."\n"; 
		$inputText .= '$alldata = '.var_export($alldata,true).' ;'."\n"; 
		$inputText .= '?>'."\n";
		
		$fp = fopen("./allDataList.php", "a");
		if (flock($fp, LOCK_EX)) {  // 排他ロックを確保します
			ftruncate($fp, 0);      // ファイルを切り詰めます
			fwrite($fp, $inputText);
			fflush($fp);            // 出力をフラッシュしてからロックを解放します
			flock($fp, LOCK_UN);    // ロックを解放します
		}
		fclose($fp);
	}
	echo '<h2>データの削除</h2>';
	echo '<div style="font-size:small;">';
	echo '<form action="dataDelete.php" name="delete" method="post" style="display:inline">'."\n";
	if($_SESSION['sessionname']===$admin_info[0]['id']) { //管理者の場合

		for ($i=0; $i<count($alldata); $i++) { //全アカウント分を表示
			echo '<input type="radio" name="del" value="'.$alldata[$i]['num'].'">';
			echo 'No.'.$alldata[$i]['num'];
			echo '　登録者:'.hsc($alldata[$i]['id']);
			echo '　データ名:'.hsc($alldata[$i]['dataname']);
			echo '　ファイル名:'.hsc($alldata[$i]['filename']);
			echo '<br>'."\n";
		}
	}else{ //管理者以外の場合

		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]['id']===$_SESSION['sessionname']){ //自己アカウント分のみを表示
				echo '<input type="radio" name="del" value="'.$alldata[$i]['num'].'">';
				echo 'No.'.$alldata[$i]['num'];
				echo '　登録者:'.hsc($alldata[$i]['id']);
				echo '　データ名:'.hsc($alldata[$i]['dataname']);
				echo '　ファイル名:'.hsc($alldata[$i]['filename']);
				echo '<br>'."\n";
			}
		}
	}
	echo '</div><br>';
	
	if($i > 0){
		echo '<input type="button" id="delbutton" value="チェックを入れたデータの削除" onclick="timer1()"></form>'."\n";
		echo '<hr>メッセージ：';
		
		if(isset($_POST["del"])) {
			echo hsc($_POST["del"]).' のデータを削除しました。';
			
			// ログ記録
			makeLog($_SESSION['sessionname'].' => ['.$_POST["del"].'] のデータを削除') ;
			
		}else{
			echo '削除するデータにチェックを入れてください。';
		}
	}
}else{
	echo '<h3>データの削除</h3>';
	echo "登録されたデータがありません。<br>"."\n";
}
echo "<hr>"."\n";
echo '<a href="redirect.html">管理画面に戻る</a>'; 
?>
</div>
<script>
function timer1(){ //デリート処理を遅延させるためのタイマー（連続クリックで動作不良をおこすため）
	document.getElementById("delbutton").disabled = true ;
	document.getElementById("delbutton").value= "お待ち下さい..." ;
	var timersubmit = function(){
		document.delete.submit();
	}
	setTimeout(timersubmit, 3000); //ここは3000にしておく
}
</script>
</body> 
</html>