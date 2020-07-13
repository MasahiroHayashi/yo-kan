<?php
// ini_set('display_errors', "On");
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars定義関数
require_once './makeLog.php'; //ログファイル書き込み関数を呼び出し
// セッショントークンの確認
$token = $_POST['token']; //tokenを変数に入れる
if(!(hash_equals($token, $_SESSION['token']) && !empty($token))) { 
	$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;
	makeLog($url.' => 【不正アクセス注意】何者かが不正なアクセスにより、データの変更を試みたが失敗') ;
	exit("不正アクセスの可能性があります。");
}
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
<title>データの修正</title>
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

// サーバーキャッシュのクリアのための処理
$fileName = "allDataList.php" ;
copy($fileName, $fileName.'copy'); // コピーを作成
unlink($fileName);                 // 原本を削除
copy($fileName.'copy', $fileName); // コピーから原本を再作成
unlink($fileName.'copy');          // コピーを削除
	
require_once './allDataList.php'; //allDataList.phpを呼び出し

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

if(isset($_POST["chan"])) {
	for ($i=0; $i<count($alldata); $i++) {
		if($alldata[$i]['num']===$_POST["chan"]){

			if (!isset($_SESSION['token'])) { // ワンタイムトークン生成
				$_SESSION['token'] = sha1(random_bytes(30));
			}
						
			echo '<h2>データの修正</h2>'."\n" ;
			echo '<form action="dataChange2.php" name="change" method="post" enctype="multipart/form-data">'."\n";
			echo '<input type="hidden" name="num" value="'.$alldata[$i]['num'].'">'."\n"; //隠し要素（ナンバー）
			echo '<input type="hidden" name="filename" value="'.$alldata[$i]['filename'].'">'."\n"; //隠し要素（ファイル名）
			echo '差し替えファイルを選択（※ファイルを差し替える必要がなければ選択不要）<br><input type="file" name="fname"> <br><br>'."\n";
			echo 'データの名称（修正後）<br><input id="dsName" class="textBoxW" name="dataname" type="text" value="'.$alldata[$i]['dataname'].'" required>（必須）<br>'."\n";
			echo 'データの説明（修正後）<br><textarea class="textBoxH" name="comment" rows="3" cols="20">'.$alldata[$i]['comment'].'</textarea><br>'."\n" ;
			echo 'データのライセンス（修正後）<br><select class="textBoxW" name=\'license\'>'."\n";
			
			$li1 = ''; $li2 = ''; $li3 = ''; $li4 = ''; $li5 = ''; $li6 = ''; $li7 = ''; $li8 = '';  $li9 = '';  $li10 = '';  $li11 = '';  $li12 = ''; 
			if($alldata[$i]['license']==='CC0'){
				$li1 = 'selected';
			}else if($alldata[$i]['license']==='CC-BY'){
				$li2 = 'selected';
			}else if($alldata[$i]['license']==='CC-BY-SA'){
				$li3 = 'selected';
			}else if($alldata[$i]['license']==='CC-BY-ND'){
				$li4 = 'selected';
			}else if($alldata[$i]['license']==='CC-BY-NC'){
				$li5 = 'selected';
			}else if($alldata[$i]['license']==='CC-BY-NC-SA'){
				$li6 = 'selected';
			}else if($alldata[$i]['license']==='CC-BY-NC-ND'){
				$li7 = 'selected';
			}else if($alldata[$i]['license']==='PD'){
				$li8 = 'selected';
			}else if($alldata[$i]['license']==='MIT License'){
				$li9 = 'selected';
			}else if($alldata[$i]['license']==='GNU General Public License (GPL 2.0)'){
				$li10 = 'selected';
			}else if($alldata[$i]['license']==='Apache License 2.0'){
				$li11 = 'selected';
			}else if($alldata[$i]['license']==='その他'){
				$li12 = 'selected';
			}
			echo '<option value=\'CC0\' '.$li1.'>CC0（パブリックドメイン宣言）</option>'."\n";
			echo '<option value=\'CC-BY\' '.$li2.'>CC-BY</option>'."\n";
			echo '<option value=\'CC-BY-SA\' '.$li3.'>CC-BY-SA</option>'."\n";
			echo '<option value=\'CC-BY-ND\' '.$li4.'>CC-BY-ND</option>'."\n";
			echo '<option value=\'CC-BY-NC\' '.$li5.'>CC-BY-NC</option>'."\n";
			echo '<option value=\'CC-BY-NC-SA\' '.$li6.'>CC-BY-NC-SA</option>'."\n";
			echo '<option value=\'CC-BY-NC-ND\' '.$li7.'>CC-BY-NC-ND</option>'."\n";
			echo '<option value=\'PD\' '.$li8.'>Public Domain（著作権保護期間満了）</option>'."\n";
			echo '<option value=\'MIT License\' '.$li9.'>MIT License</option>'."\n";
			echo '<option value=\'GNU General Public License (GPL 2.0)\' '.$li10.'>GNU General Public License (GPL 2.0)</option>'."\n";
			echo '<option value=\'Apache License 2.0\' '.$li11.'>Apache License 2.0</option>'."\n";
			echo '<option value=\'その他\' '.$li12.'>その他</option>'."\n";
			echo '</select><br>'."\n";
			echo 'データの著作権者（修正後）<br><input id="dsLicense" class="textBoxW" name="copyright" type="text" value="'.$alldata[$i]['copyright'].'" required>（必須）<br><br>'."\n";
			echo '<input type="hidden" name="token" value="'.$_SESSION["token"].'" >'."\n"; //セッショントークン
			//echo '<input type="submit" value="データの修正"></form>'."\n";
			echo '<input type="button" id="changebutton" value="データの修正" onclick="timer1()"></form>'."\n";
			
			break;
		}
	}
}else if(isset($_POST["filename"])) {
	
	echo 'メッセージ：'.$_POST["num"].' のデータを修正しました。';
	
	//ファイルの差し替え
	$tempfile = $_FILES['fname']['tmp_name'];
	$filename2 = $_POST['filename'];
	
	if (is_uploaded_file($tempfile)) { //一時ディレクトリに移動できたらtrue
		
		// ファイル本体を削除
		$del_filename = './data/'.$_POST["num"].'/'.$_POST['filename'] ; //削除する差し替え前のファイル名
		unlink($del_filename); //差し替え前ファイルを削除
	
		// サムネ画像もあれば削除
		$del_filename = 'data/'.$_POST["num"].'/thumbnail_img_yokan.*' ; 
		foreach ( glob($del_filename) as $val ) {
			unlink($val);
		}
	
		$filename = './data/'.$_POST["num"].'/'.$_FILES['fname']['name']; //新ファイル名
		$filename2 = $_FILES['fname']['name']; //新ファイル名
		move_uploaded_file($tempfile , $filename );	//一時ディレクトリから指定ディレクトリに移動
		
		// サムネイル画像を作る
		makeThumbnail($_FILES['fname']['name'] , $_POST["num"]);
	}
			
	//メモリ上のデータの修正
	for ($i=0; $i<count($alldata); $i++) {
		if($alldata[$i]['num']===$_POST["num"]){
			
			$alldata[$i]['filename'] = $filename2 ;
			$alldata[$i]['dataname'] = $_POST["dataname"] ;
			$alldata[$i]['comment'] = $_POST["comment"] ;
			$alldata[$i]['license'] = $_POST["license"] ;
			$alldata[$i]['copyright'] = $_POST["copyright"] ;
			
			if($filename2 != $_POST['filename']){ // ファイルの差し替えがあった場合のみ filesize と updtime を更新
				$alldata[$i]['filesize'] = filesize($filename) ;
			
				date_default_timezone_set('Asia/Tokyo');
				$rec_time = date("Y-m-d").'T'.date("H:i:s");
				$alldata[$i]['updtime'] = $rec_time ; 
			}
			break;
		}
	}
	
	// ログ記録
	makeLog($_SESSION['sessionname'].' => ['.$_POST["num"].'] のデータを修正') ;
	
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
	
}else{
	echo "メッセージ：修正したいデータにチェックを入れてください。<br>"."\n";
}

echo "<hr>"."\n";
echo '<a href="dataChangeRedirect.html">修正するデータの選択画面に戻る</a><br>'; 
echo '<a href="redirect.html">管理画面に戻る</a>'; 


function makeThumbnail($imgFileName , $dir_num){
 
    $maxWidth = 250;    // 最大幅
    $maxHeight = 250;   // 最大高さ
 
    //$imgFileName = "back1.jpg";
 
	//echo $imgFileName ;
	
	$imgDirFileName = 'data/'.$dir_num.'/'.$imgFileName ;
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($imgDirFileName);
 
    if($mime == 'image/jpeg' || $mime == 'image/pjpeg'){
        $ext = '.jpg';
        $image1 = imagecreatefromjpeg($imgDirFileName);
    } elseif($mime == 'image/png' || $mime == 'image/x-png'){
        $ext = '.png';
        $image1 = imagecreatefrompng($imgDirFileName);
    } elseif($mime == 'image/gif'){
        $ext = '.gif';
        $image1 = imagecreatefromgif($imgDirFileName);
    } else {
        return false;
    }
     
    list($width1, $height1) = getimagesize($imgDirFileName);
 
    if($width1 <= $maxWidth && $height1 <= $maxHeight){
        $scale = 1.0;
    } else {
        $scale = min($maxWidth / $width1, $maxHeight / $height1);
    }
 
    $width2 = $width1 * $scale;
    $height2 = $height1 * $scale;
 
    $image2 = imagecreatetruecolor($width2, $height2);
 
    if($ext == '.gif'){
        $transparent1 = imagecolortransparent($image1);
        if($transparent1 >= 0){
            $index = imagecolorsforindex($image1, $transparent1);
            $transparent2 = imagecolorallocate($image2, $index['red'], $index['green'], $index['blue']);
            imagefill($image2, 0, 0, $transparent2);
            imagecolortransparent($image2, $transparent2);
        }
    } elseif($ext == '.png'){
        imagealphablending($image2, false);
        $transparent = imagecolorallocatealpha($image2, 0, 0, 0, 127);
        imagefill($image2, 0, 0, $transparent);
        imagesavealpha($image2, true);
    }
 
    imagecopyresampled($image2, $image1, 0, 0, 0, 0, $width2, $height2, $width1, $height1);
 
    // 保存先のディレクトリ
    //$dir = __DIR__ . $dName ;
    $dir = __DIR__ . "/data/". $dir_num ;

	$filename = "thumbnail_img_yokan" . $ext;
    $saveTo = rtrim($dir, '/\\') . '/' . $filename;
 
    if($ext == '.jpg'){
        $quality = 80;
        imagejpeg($image2, $saveTo, $quality);
    } else if($ext == '.png'){
        imagepng($image2, $saveTo);
    } else if($ext == '.gif'){
        imagegif($image2, $saveTo);
    }
 
    imagedestroy($image1);
    imagedestroy($image2);
}
?>
</div>
<script type="text/javascript">
function timer1(){ 
	let dsName = document.getElementById('dsName').value; 
	let dsLicense = document.getElementById('dsLicense').value; 
	if  (dsName == "") {
		alert("データの名称を入力してください。");
		return;
	}else if  (dsLicense == "") {
		alert("データの著作権者を入力してください。");
		return;
	}
	document.getElementById("changebutton").disabled = true ;
	document.getElementById("changebutton").value= "お待ち下さい..." ;
	var timersubmit = function(){
		document.change.submit();
	}
	setTimeout(timersubmit, 100);
}
</script>
</body> 
</html>