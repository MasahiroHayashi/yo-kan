<?php
// ini_set('display_errors', "On");
$flag = true ; //ログファイルがあるかどうかの判定につかうフラグ
function makeLog($logText) {

	// logディレクトリがなければ作成
	if(!file_exists("./log")){
		mkdir("./log") ;
		mkdir("./log/check") ; // 管理者呼び出し用も作っておく
		// 空の 1.php も作っておく
		$inputText = '<?php'."\n";
		$inputText .= '$log_data  = [] ;'."\n"; 
		$inputText .= '?>'."\n";
		$fp = fopen("./log/1.php", "w");
		flock($fp, LOCK_EX); //共有ロック
		fwrite($fp, $inputText);
		flock($fp, LOCK_UN); //ロック解除
		fclose($fp);
		$flag = false ;
	}else{
		$flag = true ;
	}
		
	foreach(glob('log/{*.php}',GLOB_BRACE) as $fileName){ // logディレクトリ内のファイルを.php の拡張子を指定してサーチ
		if(is_file($fileName)){
			$fileName = $fileName ; // これで最新の（一番大きい）ファイルネームが取得できる
		}
	}

	if($flag) { //ログファイルがすでにある（2回め以降の場合）
		
		// サーバーキャッシュのクリアのための処理
		copy($fileName, $fileName.'copy'); // コピーを作成
		unlink($fileName);                 // 原本を削除
		require $fileName.'copy' ;         // requireでコピーのほうを呼び出し （require_onceだとなぜかエラーおきる）
		copy($fileName.'copy', $fileName); // コピーから原本を再作成
		unlink($fileName.'copy');          // コピーを削除
		
	}else{
		// 新規用に空の配列 $log_data 作成
		$log_data = [] ; 	
	}

	// 番号だけに加工
	$fileNum = str_replace('log/', '', $fileName) ; // log/ をとる
	$fileNum = str_replace('.php', '', $fileNum) ; // .php をとる
	$fileNum = (int)$fileNum ; //int型に変換		

	date_default_timezone_set('Asia/Tokyo');
	$rec_time = date("Y-m-d").'T'.date("H:i:s");
	
	if(count($log_data) < 100){ 
		$log_data[] = $rec_time.' ## '.$_SERVER["REMOTE_ADDR"].' ## '.$logText ;
	}else{
		$log_data = [] ; // 新規用に空にする
		$log_data[] = $rec_time.' ## '.$_SERVER["REMOTE_ADDR"].' ## '.$logText ;
		$fileNum++ ;
	}

	//書き込むテキストの生成
	$inputText = '<?php'."\n";
	$inputText .= '$log_data = '.var_export($log_data,true).' ;'."\n"; 
	$inputText .= '?>'."\n";	

	$fp = fopen("./log/".$fileNum.".php", "a"); // ロックをかけるためaモードでオープン
	if (flock($fp, LOCK_EX)) {  // 排他ロックを確保
		ftruncate($fp, 0);      // ファイルを切り詰め
		fwrite($fp, $inputText);
		fflush($fp);            // 出力をフラッシュしてからロックを解放
		flock($fp, LOCK_UN);    // ロックを解放
	}
	fclose($fp); //ファイルをクローズ
	
	//書き込むテキストの生成（管理者呼び出し用）
	$inputText2 = '<?php'."\n";
	$inputText2 .= 'function hsc($str){return htmlspecialchars($str, ENT_QUOTES, "UTF-8");}'."\n";
	$inputText2 .= 'session_start();'."\n";
	$inputText2 .= 'if(isset($_SESSION[\'sessionname\'])) { require_once \'../../accountData.php\';'."\n";
	$inputText2 .= 'if($_SESSION[\'sessionname\'] === $admin_info[0][\'id\']){'."\n";
	$inputText2 .= '$log_data = '.var_export($log_data,true).' ;'."\n"; 
	$inputText2 .= 'foreach($log_data as $value){echo hsc($value)."<br>"."\n";};'."\n"; 
	$inputText2 .= '}else{header(\'Location: ../../login.php\');}}else{header(\'Location: ../../login.php\');}'."\n";
	$inputText2 .= '?>'."\n";
	
	$fp2 = fopen("./log/check/log".$fileNum.".php", "a");
	if (flock($fp2, LOCK_EX)) {
		ftruncate($fp2, 0);
		fwrite($fp2, $inputText2);
		fflush($fp2);
		flock($fp2, LOCK_UN);
	}
	fclose($fp2);
}
?>