<?php
// ini_set('display_errors', "On");
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN'); //クリックジャッキング対策
function hsc($str){return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');} //htmlspecialchars定義関数
$fileName = 'accountData.php' ;
if (!(file_exists($fileName))) {
	header( "Location: ./login.php" ) ; //ログインページにとぶ
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="js/index.js"></script>
<link rel="stylesheet" type="text/css" href="css/index.css">
<style type="text/css">
/* index_list.php 専用 */
.index_list_h2{
	margin: 0 ;
	float: left;
}
.index_list_select {
	margin: 10px 0 ;
	float: right;
	height:40px;
	font-size:large;
}
.index_list_hr{
	margin: 50px 0 30px 0 ;
	clear: both;
}
.counter_parent { /* ページ番号の設定クラス */
	width:100%; 
	text-align:center;
	padding: 15px 0 ;
}
.counter_parent a {
	text-decoration: none;
}
.counter_child {
	padding-top:7px;
	display:inline-block;
	border: 1px solid #999;	
	border-radius: 3px;
	width:35px;
	height:28px;
	margin-right:6px;
	
}
.counter_child_bold {
	font-weight:bold; 
	background:#777;
}
</style>
<?php
copy($fileName, $fileName.'copy'); // コピーを作成
unlink($fileName);                 // 原本を削除
copy($fileName.'copy', $fileName); // コピーから原本を再作成
unlink($fileName.'copy');          // コピーを削除
require_once './accountData.php'; //allDataList.phpを呼び出し

$urlStr1 = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // 現在のURLを取得
$urlStr = str_replace('index_list.php', '', $urlStr1); // [index_list.php]を削除してベースURLを確定 

echo '<meta name="description" content="'.$site_setting[0]['s2_snsText'].'" />'."\n";
echo '<!--OGP設定-->'."\n";
echo '<meta property="og:site_name" content="'.$site_setting[0]['s0_siteName'].'" />'."\n";
echo '<meta property="og:title" content="オープンデータ一覧 / '.$site_setting[0]['s0_siteName'].'" />'."\n";
echo '<meta property="og:url" content="'.$urlStr1.'" />'."\n";
echo '<meta property="og:image" content="'.$urlStr.'img_uploaded/'.$site_setting[0]['s3_snsImg'].'" />'."\n";
echo '<meta property="og:description" content="'.$site_setting[0]['s2_snsText'].'" />'."\n";
echo '<meta property="og:type" content="website" />'."\n";
echo '<!--Twitter Card設定-->'."\n";
echo '<meta name="twitter:card" content="summary_large_image">'."\n";
echo '<meta name="twitter:url" content="'.$urlStr1.'" />'."\n";
echo '<meta name="twitter:title" content="オープンデータ一覧 / '.$site_setting[0]['s0_siteName'].'" />'."\n";
echo '<meta name="twitter:description" content="'.$site_setting[0]['s2_snsText'].'">'."\n";
echo '<meta name="twitter:image:src" content="'.$urlStr.'img_uploaded/'.$site_setting[0]['s3_snsImg'].'">'."\n";
echo '<title>オープンデータ一覧 / '.$site_setting[0]['s0_siteName'].'</title>';
echo '<style type="text/css">'."\n";
echo 'body{ background-image: url(./img_uploaded/'.$site_setting[0]['s1_backgroundImg'].') ;} '."\n";
echo '</style>'."\n";
?>
</head>
<body>
<header class="site-header">
	<div class="site-logo">
		<a href="./">
		<?php
		if($site_setting[0]['s5_headerBanner'] != "N"){
			echo '<img src="./img_uploaded/'.$site_setting[0]['s5_headerBanner'].'"/>'."\n";
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
				<li class="gnav__menu__item"><a href="./">ホーム</a></li>
				<li class="gnav__menu__item"><a href="index_list.php">オープンデータ一覧</a></li>
				<li class="gnav__menu__item"><a href="index_mail.php">お問い合わせ</a></li>
				<li class="gnav__menu__item"><a href="index_api.php">WEB API</a></li>
			</ul>
		</nav>
	</div>
</header> 
<div id="menu_close"></div><!-- ハンバーガーメニューをクローズするためのサイドバー -->
<div class="content1">
<form action="index_list.php?page=1" name="search_submit" method="GET" enctype="multipart/form-data">

<!-- ///////////// 検索ボックスの生成 ///////////// -->
<?php
$sessionStr = "" ;
if (isset($_GET["searchFromTop"])){ // トップからのポスト
	$_SESSION['search'] = $_GET["searchFromTop"] ; 
	$sessionStr = $_SESSION['search'];
}else if(isset($_GET["search"])){
	$_SESSION['search'] = $_GET["search"] ; // このページからのポスト
	$sessionStr = $_SESSION['search'];
}else if(!isset($_GET["page"])){ // ゲットがない
	unset($_SESSION['search']); //セッションの中身をクリア
}
echo '<input id="sbox5" name="search" type="text" value="'.$sessionStr.'">'."\n";
echo '<input type="hidden" name="page" value="1" >'."\n";
echo '<input type="button" id="sbtn5" value="検索" onclick="onChangeSelect()">'."\n";
?>

</div> <!-- content1はここまでだがformは閉じておらず下に続いている -->

<div class="content2">
<h2 class="index_list_h2">オープンデータ一覧</h2>

<!-- ///////////// ドロップダウンリストの生成 ///////////// -->
<select name="sort_select" id="sort_select" onChange="onChangeSelect()" class="index_list_select">
<?php
if (!isset($_GET["sort_select"])){ // ゲットなし
	$_SESSION['sort_select'] = "A"; //セッションをAに
	
}else if (isset($_GET["sort_select"])){
	$_SESSION['sort_select'] = $_GET["sort_select"] ; // ポストがあればセッションに格納	
	
}
if (isset($_SESSION['sort_select'])){

	if ($_SESSION['sort_select']==="A"){
		echo '<option value="A" selected >新着順</option>';
		echo '<option value="B">登録日の古い順</option>';
		echo '<option value="C">ダウンロード数順</option>';
		echo '<option value="D">50音(文字コード)順</option>';
		echo '<option value="E">50音(文字コード)順 (降順)</option>';
	}else if ($_SESSION['sort_select']==="B"){
		echo '<option value="A">新着順</option>';
		echo '<option value="B" selected >登録日の古い順</option>';
		echo '<option value="C">ダウンロード数順</option>';
		echo '<option value="D">50音(文字コード)順</option>';
		echo '<option value="E">50音(文字コード)順 (降順)</option>';
	}else if ($_SESSION['sort_select']==="C"){
		echo '<option value="A">新着順</option>';
		echo '<option value="B">登録日の古い順</option>';
		echo '<option value="C" selected >ダウンロード数順</option>';
		echo '<option value="D">50音(文字コード)順</option>';
		echo '<option value="E">50音(文字コード)順 (降順)</option>';
	}else if ($_SESSION['sort_select']==="D"){
		echo '<option value="A">新着順</option>';
		echo '<option value="B">登録日の古い順</option>';
		echo '<option value="C">ダウンロード数順</option>';
		echo '<option value="D" selected >50音(文字コード)順</option>';
		echo '<option value="E">50音(文字コード)順 (降順)</option>';
	}else if ($_SESSION['sort_select']==="E"){
		echo '<option value="A">新着順</option>';
		echo '<option value="B">登録日の古い順</option>';
		echo '<option value="C">ダウンロード数順</option>';
		echo '<option value="D">50音(文字コード)順</option>';
		echo '<option value="E" selected >50音(文字コード)順 (降順)</option>';
	}
}else{
	echo '<option value="A" selected >新着順</option>';
	echo '<option value="B">登録日の古い順</option>';
	echo '<option value="C">ダウンロード数順</option>';
	echo '<option value="D">50音(文字コード)順</option>';
	echo '<option value="E">50音(文字コード)順 (降順)</option>';
}
echo '</select>'."\n";
?>
</form>
<hr class="index_list_hr">
	
<!-- ///////////// オープンデータ一覧の生成 ///////////// -->
<?php
$fileName = 'allDataList.php' ;
if (!file_exists($fileName)) {
	echo 'データがまだ登録されていません。<br><br>' ;
	
}else{

	copy($fileName, $fileName.'copy'); // コピーを作成
	unlink($fileName);                 // 原本を削除
	copy($fileName.'copy', $fileName); // コピーから原本を再作成
	unlink($fileName.'copy');          // コピーを削除
	require_once './allDataList.php'; //allDataList.phpを呼び出し

	// １ページに表示する最大数
	$limit = $site_setting[0]['s9_maxDisplayData'] ;  
	// 配列数よりリミットのほうが大の場合は配列数に合わせる
	if($limit > count($alldata)){
		$limit = count($alldata) ;
	}
	
	//ページ数（リミット数で除して小数点以下切り上げ）
	if($limit != 0){
		$pageCount = ceil(count($alldata) / $limit) ; 
	}else{ // 0で除算のときは0をセット
		$pageCount = 0 ; 
	}
		
	$alldata_result1 = makeSearchResult($alldata,$admin_info,$user_info) ; //検索の結果を返す関数
	
	if ($_SESSION['sort_select']==="A"){
		foreach ($alldata_result1 as $key => $value) {
		  $arr[$key] = $value['rectime'];
		}
		if(isset($arr)){
			array_multisort($arr, SORT_DESC, $alldata_result1);
		}
	}else if ($_SESSION['sort_select']==="B"){
		foreach ($alldata_result1 as $key => $value) {
		  $arr[$key] = $value['rectime'];
		}
		if(isset($arr)){
			array_multisort($arr, SORT_ASC, $alldata_result1);
		}
	}else if ($_SESSION['sort_select']==="C"){
		foreach ($alldata_result1 as $key => $value) {
		  $arr[$key] = $value['counter'];
		}
		if(isset($arr)){
			array_multisort($arr, SORT_DESC, $alldata_result1);
		}
	}else if ($_SESSION['sort_select']==="D"){
		foreach ($alldata_result1 as $key => $value) {
		  $arr[$key] = $value['dataname'];
		}
		if(isset($arr)){
			array_multisort($arr, SORT_ASC, $alldata_result1);
		}
	}else if ($_SESSION['sort_select']==="E"){
		foreach ($alldata_result1 as $key => $value) {
		  $arr[$key] = $value['dataname'];
		}
		if(isset($arr)){
			array_multisort($arr, SORT_DESC, $alldata_result1);
		}
	}
	$alldata_result2 = pageOffset($alldata_result1 , $limit) ;  // ページの表示数にリミットオフセットを適用する関数
	displayResult($alldata_result2) ; // 画面に表示させる関数に渡す
	displayPageCounter($alldata_result1 , $limit) ; //最下部にページ番号を表示させる関数に渡して終了	
}

//検索の結果を返す関数
function makeSearchResult($alldata,$admin_info,$user_info){

	$keyArr = [] ;
	
	//ポスト文字列からキーワードを取り出し
	if (isset($_SESSION['search'])){ 

		$keyString = $_SESSION['search'] ;
		$keyString = str_replace('　', ' ', $keyString); //大文字スペースを小文字スペースに置換
		$keyString = str_replace('            ', '           ', $keyString); 
		$keyString = str_replace('           ', '          ', $keyString); 
		$keyString = str_replace('          ', '         ', $keyString); 
		$keyString = str_replace('         ', '        ', $keyString); 
		$keyString = str_replace('        ', '       ', $keyString); 
		$keyString = str_replace('       ', '      ', $keyString); 
		$keyString = str_replace('      ', '     ', $keyString); 
		$keyString = str_replace('     ', '    ', $keyString); 
		$keyString = str_replace('    ', '   ', $keyString); 
		$keyString = str_replace('   ', '  ', $keyString); 
		$keyString = str_replace('  ', ' ', $keyString); 
		$keyString = str_replace(' ', '蓴鵲孅', $keyString); //ありえない文字を区切り文字に
		$keyArr = explode('蓴鵲孅', $keyString); //キーワードを配列に格納
		
		for ($i=0; $i<count($keyArr); $i++) { 
			$keyArr[$i] = mb_convert_kana($keyArr[$i], "KVa"); //全角文字と半角文字を統一
			$keyArr[$i] = mb_strtolower($keyArr[$i]); //大文字は小文字に変換
		}
	}
	
	$alldata_result = [] ;
	for ($i=0; $i<count($alldata); $i++) {
		
		//$targetStringに登録者名を追加するための変数 $registrantName（検索用）
		if($alldata[$i]["id"] === $admin_info[0]["id"]){
				$registrantName = $admin_info[0]["name"];
		}else{
			for ($j=0; $j<count($user_info); $j++) {
				if($alldata[$i]["id"] === $user_info[$j]["id"]){
					$registrantName = $user_info[$j]["name"];
					break;
				}
			}
		}
		
		//キーワード検索の対象となる項目の文字列を連結
		$targetString = $alldata[$i]['filename'].' '.$alldata[$i]['dataname'].' '.$alldata[$i]['comment'].' '.$alldata[$i]['copyright'].' '.$registrantName ; 
		$targetString = mb_convert_kana($targetString, "KVa"); //全角文字と半角文字を統一
		$targetString = mb_strtolower($targetString); //大文字は小文字に変換
		$hit_count = 0;
		$unhit_count = 0;
		
		//キーワードの配列を順番に見ていき、ヒットしたらカウント増やす
		for ($j=0; $j<count($keyArr); $j++) { 
			if($keyArr[$j] && $keyArr[$j]!=''){ //キーが存在するかつブランクでない
				if(strpos($targetString , $keyArr[$j]) !== false){ 
					$hit_count++;
				}
			}else{
				$unhit_count++;
			}
		}
		if(count($keyArr) === ($hit_count + $unhit_count)){
			array_push($alldata_result , $alldata[$i] );
		}
	}
	return $alldata_result ;
}

//ページの表示数にリミットオフセットを適用する関数
function pageOffset($alldataOffset , $limit){
	
	//ページ数（リミット数で除して小数点以下切り上げ）
	if($limit != 0){
		$pageCount = ceil(count($alldataOffset) / $limit) ; 
	}else{ // 0で除算のときは0をセット
		$pageCount = 0 ; 
	}
	//ループのスタートに入れる配列の番号
	$startCount = 0 ;
	if(isset($_GET["page"])){
		$startCount = (($_GET["page"] - 1 ) * $limit) ;
	}
	//ループのラストに入れる配列の番号
	if (isset($_GET["page"]) && $_GET["page"] == $pageCount) { //ゲットとページ総数が同じ（＝ラストページ）の場合
		$lastCount = count($alldataOffset) ; //最終数はデータの配列数に同数
	}else if($pageCount == 0){ // ヒット数が０件だった場合
		$lastCount = 0 ; 
		echo '<br><br>検索結果にマッチしたデータはありませんでした。<br><br>';
	}else{
		$lastCount = ($startCount + $limit) ; //スタート数にLIMIT数を加算
	}
	//このページに表示させる配列データの生成
	$resultData = [] ;
	for ($i=$startCount; $i<$lastCount; $i++) {
		array_push($resultData , $alldataOffset[$i] );
	}
	return $resultData ;
}

// 画面に表示させる関数
function displayResult($alldata_result){
	
	for ($i=0; $i<count($alldata_result); $i++) { 
	
		echo '<p>';
		echo '<span class="datetime">';
		echo hsc( substr($alldata_result[$i]['rectime'],0,10) ); // 左から10文字切り出し
		echo ' 公開</span>';
		echo '<span class="dataname">';
		echo '<a href="./data/'.$alldata_result[$i]['num'].'/">';
		echo hsc($alldata_result[$i]['dataname']);
		echo '</a>';
		echo '</span>';
		echo '<span class="filesizeClass">';

		// ファイルサイズ変換
		$filesize = $alldata_result[$i]['filesize'] ;
		if( $filesize <= 1024 ) {
			$filesize = $filesize.' byte' ;
		}else if( $filesize <= 1048576 ){
			$filesize = $filesize / 1024 ;
			$filesize = floor($filesize * pow(10,2) ) / pow(10,2 ).' KB' ; //小数点第3位以下を切り捨ててKBをつける
		}else{
			$filesize = $filesize / 1048576 ;
			$filesize = floor($filesize * pow(10,2) ) / pow(10,2 ).' MB' ; 
		}
		echo $filesize ;
		
		echo '</span>';
		echo '<span class="dlcountClass">DL数:';
		echo $alldata_result[$i]['counter'] ;
		echo '</span>';
		echo '</p>'."\n";
	}
}

//最下部にページ番号を表示させる関数
function displayPageCounter($alldata , $limit){
	
	// $_GET["sort_select"]がnullの場合はAをセット
	$sort_param = "A" ;
	if (isset($_GET["sort_select"])){
		$sort_param = $_GET["sort_select"] ;
	}
	echo '<div class="counter_parent">'."\n";
	
	//ページ数（リミット数で除して小数点以下切り上げ）
	if($limit != 0){
		$pageCount = ceil(count($alldata) / $limit) ; 
	}else{ // 0で除算のときは0をセット
		$pageCount = 0 ; 
	}
	
	$search_param = "" ;
	if (isset($_GET["search"])){
		$search_param = $_GET["search"] ;
	}
	for ($i=0; $i<$pageCount; $i++) {
		if((!isset($_GET["page"]) && $i == 0) || ( (isset($_GET["page"]) && ($_GET["page"] == ($i + 1))) )){ // ゲットなし　または　ゲットのページ番号の場合
			echo '<a href="index_list.php?search='.$search_param.'&sort_select='.$sort_param.'&page='.($i + 1).'" class="counter_child  counter_child_bold">'.($i + 1).'</a> '."\n";
		}else{
			echo '<a href="index_list.php?search='.$search_param.'&sort_select='.$sort_param.'&page='.($i + 1).'" class="counter_child">'.($i + 1).'</a> '."\n";
		}
	}
	echo '</div>'."\n";
}	
?>
</div> <!-- content2ここまで -->
<footer class="site-footer">
<div class="site-footer-inner">
<?php echo $site_setting[0]['s8_footerText']."\n"; ?>
</div>
</footer>
<div class="yokan">
	<span id="fb"></span>
	<span id="tw"></span>
	<span><a href="https://mirko.jp/yo-kan/"><img src="img/YKimg.png"></a></span>
</div>
<script type="text/javascript">
// 検索ボタンをクリック　または　ドロップダウンを変更したときにサブミットする関数
function onChangeSelect(){ 
	document.getElementById("sbtn5").disabled = true ;
	const timersubmit = function(){
		document.search_submit.submit();
	}
	setTimeout(timersubmit, 100); 
}
</script>
<script type="text/javascript">
// SNSボタンの表示
let fb_text  = '<a href="http://www.facebook.com/share.php?u=';
	fb_text += encodeURI(location.href) ;
	fb_text += '" ><img src="img/FBimg.png"></a>';
let tw_text  = '<a href="http://twitter.com/share?url=';
	tw_text += encodeURI(location.href) ;
	tw_text += '" rel="nofollow"><img src="img/TWimg.png"></a>';
document.getElementById('fb').innerHTML = fb_text ;
document.getElementById('tw').innerHTML = tw_text ;
</script>
</body> 
</html>