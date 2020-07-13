<?php
// ini_set('display_errors', "On");
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars定義関数
if(isset($_POST['download'])) { //ポストがあったときはダウンロード
	// セッショントークンの確認
	$token = $_POST['token']; //tokenを変数に入れる
	if(!(hash_equals($token, $_SESSION['token']) && !empty($token))) { 
		header( "Location: ./login.php" ) ; //ログインページにとぶ
		exit;
	}else{
		$fileName = '../../allDataList.php' ;
		copy($fileName, $fileName.'copy'); // コピーを作成
		unlink($fileName);                 // 原本を削除
		copy($fileName.'copy', $fileName); // コピーから原本を再作成
		unlink($fileName.'copy');          // コピーを削除

		require_once '../../allDataList.php'; //allDataList.phpを呼び出し 
		
		$dirName = substr(dirname(__FILE__), -5); //ディレクトリのパスの右から５文字切り出し（ディレクトリ名）
		
		//アクセスカウンターの処理
		for ($i=0; $i<count($alldata); $i++) {
			if($alldata[$i]['num']===$dirName){
				$alldata[$i]['counter']++ ;
				break;
			}
		}
		//書き込むテキストの生成
		$inputText = '<?php'."\n";
		$inputText .= '$datanum = '.$datanum.' ;'."\n"; 
		$inputText .= '$alldata = '.var_export($alldata,true).' ;'."\n"; 
		$inputText .= '?>'."\n";

		$fp = fopen('../../allDataList.php', 'a');
		if (flock($fp, LOCK_EX)) {  // 排他ロックを確保します
			ftruncate($fp, 0);      // ファイルを切り詰めます
			fwrite($fp, $inputText);
			fflush($fp);            // 出力をフラッシュしてからロックを解放します
			flock($fp, LOCK_UN);    // ロックを解放します
		}
		fclose($fp);
		
		// ファイルタイプを指定
		header('Content-Type: application/force-download');
		// ファイルサイズを取得し、ダウンロードの進捗を表示
		header('Content-Length: '.filesize($_POST['fileName']));
		// ファイルのダウンロード、リネームを指示
		header('Content-Disposition: attachment; filename="'.$_POST['fileName'].'"');
		// ファイルを読み込みダウンロードを実行
		readfile($_POST['fileName']);
		exit;
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="../../js/index.js"></script>
<link rel="stylesheet" type="text/css" href="../../css/index.css">
<style type="text/css">
/* index_dir.php 専用 */
.items_div {
	margin: 15px 0 ;
}
.itemsNameClass {
	display: inline-block ;
	vertical-align: middle ;
	width: 250px;
	color: #555 ;
}
.itemsContentClass {
	display: inline-block ;
	vertical-align: middle ;
	width: 520px;
	padding-left : 15px;
}
@media screen and (max-width: 650px) {
	.itemsContentClass {
		display: block ;
		width: 90%;
	}
}
</style>
<?php
if (!isset($_SESSION['token'])) { // ワンタイムトークン生成
	$_SESSION['token'] = sha1(random_bytes(30));
}

$fileName = '../../accountData.php' ;
copy($fileName, $fileName.'copy'); // コピーを作成
unlink($fileName);                 // 原本を削除
copy($fileName.'copy', $fileName); // コピーから原本を再作成
unlink($fileName.'copy');          // コピーを削除

$fileName = '../../allDataList.php' ;
copy($fileName, $fileName.'copy'); // コピーを作成
unlink($fileName);                 // 原本を削除
copy($fileName.'copy', $fileName); // コピーから原本を再作成
unlink($fileName.'copy');          // コピーを削除

require_once '../../allDataList.php'; //allDataList.phpを呼び出し 
require_once '../../accountData.php'; //accountData.phpも呼び出し 

$dirName = substr(dirname(__FILE__), -5); //ディレクトリのパスの右から５文字切り出し（ディレクトリ名）

for ($i=0; $i<count($alldata); $i++) {
	if($alldata[$i]['num']===$dirName){
		
		$urlStr1 = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // 現在のURLを取得
		$urlStr = str_replace('index.php', '', $urlStr1); // [/index.php]があれば削除
		$urlStr = str_replace('/data/'.$dirName, '', $urlStr); // 次に上位ディレクトリ名を削除
		
		echo '<meta name="description" content="'.$alldata[$i]['comment'].'" />'."\n";
		echo '<!--OGP設定-->'."\n";
		echo '<meta property="og:site_name" content="'.$site_setting[0]['s0_siteName'].'" />'."\n";
		echo '<meta property="og:title" content="'.$alldata[$i]['dataname'].'" />'."\n";
		echo '<meta property="og:url" content="'.$urlStr1.'" />'."\n";
		echo '<meta property="og:image" content="'.$urlStr.'img_uploaded/'.$site_setting[0]['s3_snsImg'].'" />'."\n";
		echo '<meta property="og:description" content="'.$alldata[$i]['comment'].'" />'."\n";
		echo '<meta property="og:type" content="website" />'."\n";
		echo '<!--Twitter Card設定-->'."\n";
		echo '<meta name="twitter:card" content="summary_large_image">'."\n";
		echo '<meta name="twitter:url" content="'.$urlStr1.'" />'."\n";
		echo '<meta name="twitter:title" content="'.$alldata[$i]['dataname'].'" />'."\n";
		echo '<meta name="twitter:description" content="'.$alldata[$i]['comment'].'">'."\n";
		echo '<meta name="twitter:image:src" content="'.$urlStr.'img_uploaded/'.$site_setting[0]['s3_snsImg'].'">'."\n";
		echo '<title>'.$alldata[$i]['num'].' / '.$alldata[$i]['dataname'].'</title>';
		break ;
	}
}
echo '<style type="text/css">'."\n";
echo 'body{ background-image: url(../../img_uploaded/'.$site_setting[0]['s1_backgroundImg'].') ;} '."\n";
echo '</style>'."\n";
?>
</head>
<body>
<header class="site-header">
	<div class="site-logo">
		<a href="../../">
		<?php
		if($site_setting[0]['s5_headerBanner'] != "N"){
			echo '<img src="../../img_uploaded/'.$site_setting[0]['s5_headerBanner'].'"/>'."\n";
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
				<li class="gnav__menu__item"><a href="../../">ホーム</a></li>
				<li class="gnav__menu__item"><a href="../../index_list.php">オープンデータ一覧</a></li>
				<li class="gnav__menu__item"><a href="../../index_mail.php">お問い合わせ</a></li>
				<li class="gnav__menu__item"><a href="../../index_api.php">WEB API</a></li>
			</ul>
		</nav>
	</div>
</header> 
<div id="menu_close"></div><!-- ハンバーガーメニューをクローズするためのサイドバー -->
<div style="margin-top:140px;">  </div>
<div class="content2">
<?php
// $i は上の方ですでに取得済みなのでここではループ不要
echo '<div class="items_div"><span class="itemsNameClass">データ番号</span><span class="itemsContentClass">'.$alldata[$i]['num'].'</span></div>'."\n";
echo '<hr>';
echo '<div class="items_div"><span class="itemsNameClass">データ名</span><span class="itemsContentClass">'.hsc($alldata[$i]['dataname']).'</span></div>'."\n";
echo '<hr>';
echo '<div class="items_div"><span class="itemsNameClass">データの解説</span><span class="itemsContentClass">'.hsc($alldata[$i]['comment']).'</span></div>'."\n";
echo '<hr>';
echo '<div class="items_div"><span class="itemsNameClass">データのライセンス</span>';
if($alldata[$i]['license'] == "CC0"){
	echo '<span class="itemsContentClass">CC0（パブリックドメイン宣言）<br>';
	echo '<a href="https://creativecommons.org/publicdomain/zero/1.0/deed.ja" target="_blank"><img src="../../img/cc0.png" style="width:120px;"></a>';
	
}else if($alldata[$i]['license'] == "CC-BY"){
	echo '<span class="itemsContentClass">CC-BY（表示）<br>';
	echo '<a href="https://creativecommons.org/licenses/by/4.0/legalcode.ja" target="_blank"><img src="../../img/by.png" style="width:120px;"></a>';
	
}else if($alldata[$i]['license'] == "CC-BY-SA"){
	echo '<span class="itemsContentClass">CC-BY-SA（表示－継承）<br>';
	echo '<a href="https://creativecommons.org/licenses/by-sa/4.0/legalcode.ja" target="_blank"><img src="../../img/by-sa.png" style="width:120px;"></a>';
	
}else if($alldata[$i]['license'] == "CC-BY-ND"){
	echo '<span class="itemsContentClass">CC-BY-ND（表示－改変禁止）<br>';
	echo '<a href="https://creativecommons.org/licenses/by-nd/4.0/legalcode.ja" target="_blank"><img src="../../img/by-nd.png" style="width:120px;"></a>';
	
}else if($alldata[$i]['license'] == "CC-BY-NC"){
	echo '<span class="itemsContentClass">CC-BY-NC（表示－非営利）<br>';
	echo '<a href="https://creativecommons.org/licenses/by-nc/4.0/legalcode.ja" target="_blank"><img src="../../img/by-nc.png" style="width:120px;"></a>';
	
}else if($alldata[$i]['license'] == "CC-BY-NC-SA"){
	echo '<span class="itemsContentClass">CC-BY-NC-SA（表示－非営利－継承）<br>';
	echo '<a href="https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode.ja" target="_blank"><img src="../../img/by-nc-sa.png" style="width:120px;"></a>';
	
}else if($alldata[$i]['license'] == "CC-BY-NC-ND"){
	echo '<span class="itemsContentClass">CC-BY-NC-ND（表示－非営利－改変禁止）<br>';
	echo '<a href="https://creativecommons.org/licenses/by-nc-nd/4.0/legalcode.ja" target="_blank"><img src="../../img/by-nc-nd.png" style="width:120px;"></a>';
	
}else if($alldata[$i]['license'] == "Public domain"){
	echo '<span class="itemsContentClass">Public domain … 著作権なし（著作性のないデータまたは著作権保護期間満了）<br>';
	echo '<a href="https://creativecommons.org/publicdomain/mark/1.0/deed.ja" target="_blank"><img src="../../img/pd.png" style="width:120px;"></a>';
	
}else if($alldata[$i]['license'] == "MIT License"){
	echo '<span class="itemsContentClass">MIT License<br>';
	echo '<a href="https://opensource.org/licenses/mit-license.php" target="_blank">https://opensource.org/licenses/mit-license.php</a>';
	
}else if($alldata[$i]['license'] == "GNU General Public License"){
	echo '<span class="itemsContentClass">GNU General Public License<br>';
	echo '<a href="https://www.gnu.org/licenses/gpl-3.0.html" target="_blank">https://www.gnu.org/licenses/gpl-3.0.html</a>';
	
}else if($alldata[$i]['license'] == "GNU Free Documentation License"){
	echo '<span class="itemsContentClass">GNU General Public License<br>';
	echo '<a href="https://www.gnu.org/licenses/fdl-1.3.html" target="_blank">https://www.gnu.org/licenses/fdl-1.3.html</a>';
	
}else if($alldata[$i]['license'] == "Apache License"){
	echo '<span class="itemsContentClass">Apache License<br>';
	echo '<a href="http://www.apache.org/licenses/LICENSE-2.0" target="_blank">http://www.apache.org/licenses/LICENSE-2.0</a>';
	
}else{
	echo '<span class="itemsContentClass">'.$alldata[$i]['license'] ;
	
}
echo '</span></div>'."\n";
echo '<hr>';
echo '<div class="items_div"><span class="itemsNameClass">データの著作権者</span><span class="itemsContentClass">'.hsc($alldata[$i]['copyright']).'</span></div>'."\n";
echo '<hr>';
echo '<div class="items_div"><span class="itemsNameClass">ファイル名</span><span class="itemsContentClass">'.hsc($alldata[$i]['filename']).'</span></div>'."\n";
echo '<hr>';

$filesize = $alldata[$i]['filesize'] ;
if( $filesize <= 1024 ) {
	$filesize = $filesize.' byte' ;
}else if( $filesize <= 1048576 ){
	$filesize = $filesize / 1024 ;
	$filesize = floor($filesize * pow(10,3) ) / pow(10,3 ).' KB' ; //小数点第4位以下を切り捨ててKBをつける
}else{
	$filesize = $filesize / 1048576 ;
	$filesize = floor($filesize * pow(10,3) ) / pow(10,3 ).' MB' ; 
}
echo '<div class="items_div"><span class="itemsNameClass">ファイルサイズ</span><span class="itemsContentClass">'.$filesize.'</span></div>'."\n";	
echo '<hr>';

$userName = $admin_info[0]["name"] ;
for ($j=0; $j<count($user_info); $j++) {
	if($user_info[$j]["id"] === $alldata[$i]['id']){
		$userName = $user_info[$j]["name"] ;
		break;
	}
}	

echo '<div class="items_div"><span class="itemsNameClass">登録者</span><span class="itemsContentClass">'.hsc($userName).'</span></div>'."\n";
echo '<hr>';
echo '<div class="items_div"><span class="itemsNameClass">登録年月日</span><span class="itemsContentClass">'.$alldata[$i]['rectime'].'</span></div>'."\n";
echo '<hr>';
echo '<div class="items_div"><span class="itemsNameClass">ファイル更新年月日</span><span class="itemsContentClass">'.$alldata[$i]['updtime'].'</span></div>'."\n";
echo '<hr>';
echo '<div class="items_div"><span class="itemsNameClass">ダウンロード数</span><span class="itemsContentClass">'.$alldata[$i]['counter'].'</span></div>'."\n";

$thum_filename = 'thumbnail_img_yokan.*' ; 
foreach ( glob($thum_filename) as $val ) {
	echo '<hr>';
	echo '<div class="items_div"><span class="itemsNameClass">プレビュー</span><span class="itemsContentClass"><img src="'.$val.'"/></span></div>'."\n";
	echo '<hr>';
}

echo '<form action="index.php" method="POST">'."\n";
echo '<div class=\'excute_button\'>'."\n";
echo '<input name="fileName" type="hidden" value="'.$alldata[$i]['filename'].'">'."\n";
echo '<input type="hidden" name="token" value="'.$_SESSION["token"].'" >'."\n"; //セッショントークン
echo '<input class=\'excute_button_inner\' name=\'download\' type=\'submit\' value=\'このデータをダウンロード\'></div></form>'."\n";
?>
</div>
<footer class="site-footer">
<div class="site-footer-inner">
<?php echo $site_setting[0]['s8_footerText']."\n"; ?>
</div>
</footer>
<div class="yokan">
	<span id="fb"></span>
	<span id="tw"></span>
	<span><a href="https://mirko.jp/yo-kan/"><img src="../../img/YKimg.png"></a></span>
</div>
<script type="text/javascript">
// SNSボタンの表示
let fb_text  = '<a href="http://www.facebook.com/share.php?u=';
	fb_text += encodeURI(location.href) ;
	fb_text += '" ><img src="../../img/FBimg.png"></a>';
let tw_text  = '<a href="http://twitter.com/share?url=';
	tw_text += encodeURI(location.href) ;
	tw_text += '" rel="nofollow"><img src="../../img/TWimg.png"></a>';
document.getElementById('fb').innerHTML = fb_text ;
document.getElementById('tw').innerHTML = tw_text ;
</script>
</body> 
</html>