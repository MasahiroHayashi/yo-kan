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
	makeLog($url.' => 【不正アクセス注意】何者かが不正なアクセスにより、データの登録を試みたが失敗') ;
	exit("不正アクセスの可能性があります。");
}
if(!isset($_SESSION['sessionname'])) { //ログインセッションなし
	// ログ記録
	$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;
	makeLog($url.' => 【エラー】ログインなしでダイレクトアクセスし、ログインページに飛ばされる') ;
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
<title>アップロード</title>
<link rel="stylesheet" type="text/css" href="css/setting.css">
</head>
<body>
<div class="contentS">
<?php
//PHPの実行ファイルのアップロードははじく
$str = substr($_FILES['fname']['name'], -4); //ファイル名の右から４文字（拡張子）を切り出し
$str = mb_strtolower($str); // 小文字に統一
if($str == '.php'){
	echo 'この種類のファイルはアップロードできません。'."\n";
	echo '<hr/><a href="redirect.html">管理画面に戻る</a><br>'."\n";
	exit;
}

$tempfile = $_FILES['fname']['tmp_name'];

if (is_uploaded_file($tempfile)) { //まず一時ディレクトリに移動できたらtrue

	// dataディレクトリがなければ作成
	if(!file_exists("./data")){
		mkdir("./data") ;
		
		// 空の allDataList.php も作っておく
		$inputText = '<?php'."\n";
		$inputText .= '$datanum = 0 ;'."\n"; 
		$inputText .= '$alldata = [] ;'."\n"; 
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
	
	// サーバーキャッシュのクリアのための処理
	$fileName = "allDataList.php" ;
	copy($fileName, $fileName.'copy'); // コピーを作成
	unlink($fileName);                 // 原本を削除
	copy($fileName.'copy', $fileName); // コピーから原本を再作成
	unlink($fileName.'copy');          // コピーを削除
		
	require_once './allDataList.php'; //allDataList.phpを呼び出し
	
	$datanum++ ;
	$dir_num = str_pad($datanum, 5, '0', STR_PAD_LEFT); //0で埋めて5桁の文字列にする
	mkdir('./data/'.$dir_num );
	$filename = './data/'.$dir_num.'/'.$_FILES['fname']['name'];

	//move_uploaded_file関数で一時ディレクトリから指定ディレクトリに移動する。それが成功したらtrueを返す。
	if ( move_uploaded_file($tempfile , $filename )) {
		
		echo '"' . hsc($_FILES['fname']['name']) . '" をアップロードしました。<hr/>'."\n";
		echo '<a href="dataUpload.php">続けて他のデータもアップロードする</a><br>'."\n";
		echo '<a href="redirect.html">管理画面に戻る</a><br>'."\n";
		
		// ログ記録
		makeLog($_SESSION['sessionname'].' => ['.$dir_num.'] に ['.hsc($_FILES['fname']['name']) .'] をアップロード') ;
		
		$filesize = filesize($filename) ;
		
		date_default_timezone_set('Asia/Tokyo');
		$rec_time = date("Y-m-d").'T'.date("H:i:s");
		
		//メモリ上の配列に追加 //2020.6.9 先頭に追加に変更
		$items = array('num'=>$dir_num) 
				   + array('id'=>$_SESSION['sessionname']) 
				   + array('filename'=>$_FILES['fname']['name']) 
				   + array('dataname'=>$_POST['dataname']) 
				   + array('comment'=>$_POST['comment']) 
				   + array('license'=>$_POST['license']) 
				   + array('copyright'=>$_POST['copyright']) 
				   + array('filesize'=>$filesize) 
				   + array('rectime'=>$rec_time) 
				   + array('updtime'=> '' ) 
				   + array('counter'=> 0 ) ;
		array_unshift($alldata, $items);
		
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
		
		// index.phpをつくる
		copy('index_dir.php', './data/'.$dir_num.'/index.php'); // コピーを作成
		
		// サムネイル画像を作る
		makeThumbnail($_FILES['fname']['name'] , $dir_num);
					
	} else {
		
		rmdir('./data/'.$dir_num); //アップ不能な場合は作成したディレクトリを削除
		echo "【エラー】ファイルをアップロードできません。管理者へお問い合わせください。";

		// ログ記録
		makeLog($_SESSION['sessionname'].' => 【エラー】ファイルのアップロードを試みたがサーバーのエラーで中断') ;
	}
} else {
	echo "ファイルが選択されていません。<hr/>"."\n";
	echo '<a href="dataUpload.php">続けて他のデータもアップロードする</a><br>'."\n";
	echo '<a href="redirect.html">管理画面に戻る</a><br>'."\n";
} 

function makeThumbnail($imgFileName , $dir_num){
 
    $maxWidth = 250;    // 最大幅
    $maxHeight = 250;   // 最大高さ

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
</body> 
</html>