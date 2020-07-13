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
/* index_api.php 専用 */
.apiNameArea { 
	width: 180px; 
	display: inline-block;
	margin:5px 0;
}
.apiTextArea1 { width: 50px; }
.apiTextArea2 { width: 500px;font-size:large; }
.apiTextArea3 { width: 200px; }
.apiKaisetsuArea {
	font-size:small;
	width:100%;
	border: 1px solid #aaa;
	margin-bottom:30px;
	border-radius: 5px;
	color:#444;
	padding:4px;
}
@media screen and (max-width: 650px) {
	.apiTextArea2 { width: 100%;font-size:medium; }
	.apiTextArea3 { width: 80%; }
}
</style>
<?php
copy($fileName, $fileName.'copy'); // コピーを作成
unlink($fileName);                 // 原本を削除
copy($fileName.'copy', $fileName); // コピーから原本を再作成
unlink($fileName.'copy');          // コピーを削除
require_once './accountData.php'; //allDataList.phpを呼び出し

$urlStr1 = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // 現在のURLを取得
$urlStr = str_replace('index_api.php', '', $urlStr1); // [index_api.php]を削除してベースURLを確定

echo '<meta name="description" content="'.$site_setting[0]['s2_snsText'].'" />'."\n";
echo '<!--OGP設定-->'."\n";
echo '<meta property="og:site_name" content="'.$site_setting[0]['s0_siteName'].'" />'."\n";
echo '<meta property="og:title" content="WEB API / '.$site_setting[0]['s0_siteName'].'" />'."\n";
echo '<meta property="og:url" content="'.$urlStr1.'" />'."\n";
echo '<meta property="og:image" content="'.$urlStr.'img_uploaded/'.$site_setting[0]['s3_snsImg'].'" />'."\n";
echo '<meta property="og:description" content="'.$site_setting[0]['s2_snsText'].'" />'."\n";
echo '<meta property="og:type" content="website" />'."\n";
echo '<!--Twitter Card設定-->'."\n";
echo '<meta name="twitter:card" content="summary_large_image">'."\n";
echo '<meta name="twitter:url" content="'.$urlStr1.'" />'."\n";
echo '<meta name="twitter:title" content="WEB API / '.$site_setting[0]['s0_siteName'].'" />'."\n";
echo '<meta name="twitter:description" content="'.$site_setting[0]['s2_snsText'].'">'."\n";
echo '<meta name="twitter:image:src" content="'.$urlStr.'img_uploaded/'.$site_setting[0]['s3_snsImg'].'">'."\n";
echo '<title>WEB API / '.$site_setting[0]['s0_siteName'].'</title>';
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
<div style="margin-top:140px;">  </div>
<div class="content2">
<h2>WEB API</h2>
	<form>
	<span class="apiNameArea">エンドポイントURL</span><input class="apiTextArea2" id="endpoint" type="text" value="" disabled><br><br>
	<span class="apiNameArea">パラメータ: sort</span><input class="apiTextArea1" id="sort" type="text" value="">
	<div class="apiKaisetsuArea">
	AからLまでの半角英字1字を設定することによりデータの並べ替えを行います。<br>
	A:データ番号順(昇順)　B:データ番号順(降順)　
	C:登録(更新)年月日順(昇順)　D:登録(更新)年月日順(降順)　
	E:ダウンロード数順(昇順)　F:ダウンロード数順(降順)　
	G:ファイルサイズ順(昇順)　H:ファイルサイズ順(降順)　
	I:データ名順(昇順)　J:データ名順(降順)　
	K:ファイル名順(昇順)　L:ファイル名順(降順)
	</div>
	<span class="apiNameArea">パラメータ: key</span><input class="apiTextArea3" id="key" type="text" value="">
	<div class="apiKaisetsuArea">
	データをキーワード検索します。日本語の全角文字が使えます。<br>
	検索対象となる項目は「データ名」「データの解説」「ファイル名」「データの著作権者」「登録者」です。<br>
	複数キーワードでAND検索をすることができ、その場合の区切り文字は @@（半角アットマーク2個）です。（例：猫@@にゃん）<br>
	OR検索はできません。
	</div>
	<span class="apiNameArea">パラメータ: license</span><input class="apiTextArea1" id="license" type="text" value="">
	<div class="apiKaisetsuArea">
	AからMまでの半角英字1字を設定することによりライセンス種別による絞り込みを行います。<br>
	A:CC0　B:CC-BY　C:CC-BY-SA　D:CC-BY-ND　E:CC-BY-NC　F:CC-BY-NC-SA　G:CC-BY-NC-ND　H:Public domain　
	I:MIT License　J:GNU General Public License　K:GNU Free Documentation License　L:Apache License　M:その他
	</div>
	<span class="apiNameArea">パラメータ: offset</span><input class="apiTextArea1" id="offset" type="text" value="">
	<div class="apiKaisetsuArea">
	データを取得する開始位置を指定します。（半角数字）
	</div>
	<span class="apiNameArea">パラメータ: limit</span><input class="apiTextArea1" id="limit" type="text" value="3">
	<div class="apiKaisetsuArea">
	データを取得する上限数を指定します。（半角数字）
	</div>
	<span class="apiNameArea">パラメータ: jsonld</span><input class="apiTextArea1" id="jsonld" type="text" value="">
	<div class="apiKaisetsuArea">
	半角数字の 1 を設定すると、Linked Data形式のJSONである「JSON-LD」で出力されます。
	</div>
	<span class="apiNameArea">パラメータ: jsonp</span><input class="apiTextArea3" id="jsonp" type="text" value="">
	<div class="apiKaisetsuArea">
	JSONPでレスポンスを受けたい場合は、このパラメータに「コールバック関数名(半角英字)」を指定してください。<br>
	この画面で結果を確認したい場合、パラメータを onCallback にすると、コールバック関数が起動し画面に結果が表示されます。<br>
	もし全角文字が文字化けして困る場合は formatパラメータを 2 か 3 にし、ユニコードエスケープ文字で結果を受け取った後、デコードしてください。
	</div>
	<span class="apiNameArea">パラメータ: format</span><input class="apiTextArea1" id="format" type="text" value="1">
	<div class="apiKaisetsuArea">
	0から3までの半角数字1字を設定することにより、出力結果のフォーマットを指定します。<br>
	0:JSON整形なし/全角2バイト文字使用　　1:JSON整形あり/全角2バイト文字使用<br>
	2:JSON整形なし/ユニコードエスケープ文字使用　　3:JSON整形あり/ユニコードエスケープ文字使用
	</div>
	<div class='excute_button'><input class='excute_button_inner' type="button" value="APIにリクエスト" onclick="execute()"></div>
	</form>
	<div id="results1" style="margin:15px 0;"></div>
	<div id="resultsC" style="margin:15px 0;"></div>
	<div id="results2" style="margin:15px 0;"></div>
	<hr>
	<div id="results3" style="margin:15px 0; font-size:small">ここに結果が表示されます。</div>
</div>
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
<script type="text/javascript">
// 現在のURLを取得（PHPだと　XREA(SSL)がうまく取得できないのでJSに変更）
let locationText = location.href;
	locationText = locationText.replace("index_", "" );
document.getElementById('endpoint').value = locationText;
// ボタンクリック時の動作
function execute() { 
	document.getElementById("results1").innerHTML = "";
	document.getElementById("results2").innerHTML = "";
	document.getElementById("resultsC").innerHTML = "";
	document.getElementById("results3").innerHTML = "";
    const endpoint = document.getElementById('endpoint').value ;
    const param_sort = document.getElementById('sort').value ;
    const param_key = document.getElementById('key').value ;
    const param_license = document.getElementById('license').value ;
    const param_offset = document.getElementById('offset').value ;
    let param_limit = document.getElementById('limit').value ;
    const param_jsonld = document.getElementById('jsonld').value ;
    const param_jsonp = document.getElementById('jsonp').value ;
    const param_format = document.getElementById('format').value ;
    let requestUrl = endpoint  ;
	if(param_sort != ""){
		requestUrl += '?sort=' + param_sort ; 
	}
	if(param_key != ""){
		if(requestUrl === endpoint){
			requestUrl += '?key=' + param_key ; 
		}else{
			requestUrl += '&key=' + param_key ; 
		}
	}
	if(param_license != ""){
		if(requestUrl === endpoint){
			requestUrl += '?license=' + param_license ; 
		}else{
			requestUrl += '&license=' + param_license ; 
		}
	}
	if(param_offset != ""){
		if(requestUrl === endpoint){
			requestUrl += '?offset=' + param_offset ;
		}else{
			requestUrl += '&offset=' + param_offset ;
		}
	}
	if(param_limit != ""){
		if(requestUrl === endpoint){
			requestUrl += '?limit=' + param_limit ; 
		}else{
			requestUrl += '&limit=' + param_limit ; 
		}
	}
	if(param_jsonld != ""){
		if(requestUrl === endpoint){
			requestUrl += '?jsonld=' + param_jsonld ; 
		}else{
			requestUrl += '&jsonld=' + param_jsonld ; 
		}
	}
	if(param_jsonp != ""){
		if(requestUrl === endpoint){
			requestUrl += '?jsonp=' + param_jsonp ;
		}else{
			requestUrl += '&jsonp=' + param_jsonp ;
		}
	}
	if(param_format != ""){
		if(requestUrl === endpoint){
			requestUrl += '?format=' + param_format ; 
		}else{
			requestUrl += '&format=' + param_format ; 
		}
	}
	document.getElementById("results1").innerHTML = "<hr>リクエストURL：<br>" + requestUrl;
	if(param_jsonp == ""){
		sendQuery(requestUrl,"GET") ; //GETでパラメータ送信
	}else{
		// jsonpの記述
		var apiScript = document.createElement("script");
		apiScript.src = requestUrl; 
		document.body.appendChild(apiScript);
	}
}
// jsonpのコールバック関数
var onCallback = function(jsonObj){
	document.getElementById("resultsC").innerHTML = "<hr>JSONPのコールバック関数（onCallback）が起動しました。";
	const param_jsonld = document.getElementById('jsonld').value ;
	if(param_jsonld != 1){
		if(jsonObj["result"]){
			document.getElementById("results2").innerHTML = "<hr>" + jsonObj["result"].length + "件のデータのレスポンスがありました。";
		}
	}else{
		if(jsonObj["result"]){
			document.getElementById("results2").innerHTML = "<hr>" + jsonObj["result"]["@graph"].length + "件のデータのレスポンスがありました。";
		}
	}
	let jsonStr = JSON.stringify(jsonObj,undefined,1);
	document.getElementById("results3").innerText = jsonStr;
}
// XMLHttpRequestでクエリ送信
function sendQuery(endpoint,method) { 
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open(method, endpoint + "&dummy=" + (new Date()).getTime(), true); //タイムスタンプ文字列つきのダミーのパラメータを送信することによりキャッシュを読むのを回避する。
	xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');
    xmlhttp.setRequestHeader("Accept", "application/json");
    xmlhttp.onreadystatechange = function() {
        if(xmlhttp.readyState == 4) {
            if(xmlhttp.status == 200 || xmlhttp.status == 201 ) {
                onSuccessQuery(xmlhttp.responseText);
            } else {
				//alert(xmlhttp.responseText);
                document.getElementById("results3").innerHTML = "エラーです。" ;
            }
        }
    }
    xmlhttp.send();
}
// 結果(JSON文字列)を配列に格納
function onSuccessQuery(text) { 
	if(text != "No data yet."){
		const jsonObj = JSON.parse(text);
		const param_jsonld = document.getElementById('jsonld').value ;
		if(param_jsonld != 1){
			if(jsonObj["result"]){
				document.getElementById("results2").innerHTML = "<hr>" + jsonObj["result"].length + "件のデータのレスポンスがありました。";
			}
		}else{
			if(jsonObj["result"]){
				document.getElementById("results2").innerHTML = "<hr>" + jsonObj["result"]["@graph"].length + "件のデータのレスポンスがありました。";
			}
		}
	}
	document.getElementById("results3").innerText = text;
}
</script>
</body> 
</html>