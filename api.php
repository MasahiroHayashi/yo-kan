<?php
// ini_set('display_errors', "On");
//CORSする場合はヘッダーをこうする。
header("Access-Control-Allow-Origin: *");
// 文字コード設定
header('Content-Type: text/html; charset=UTF-8');

if (file_exists('./allDataList.php')) {
	require_once './allDataList.php'; 
	require_once './accountData.php'; 
}else{
	print 'No data yet.';
	exit;
}

//$alldataからユーザーIDを削除し、登録者名を追加（IDを公開したくないため）
for ($i=0; $i<count($alldata); $i++) {
	if($alldata[$i]["id"] === $admin_info[0]["id"]){
			$alldata[$i]["registrant"] = $admin_info[0]["name"];
			unset($alldata[$i]["id"]);
	}else{
		for ($j=0; $j<count($user_info); $j++) {
			if($alldata[$i]["id"] === $user_info[$j]["id"]){
				$alldata[$i]["registrant"] = $user_info[$j]["name"];
				unset($alldata[$i]["id"]);
				break;
			}
		}
	}
}

// エスケープ(xss対策)して、小文字に統一
if(isset($_GET["sort"])){
	$sort = mb_strtolower( htmlspecialchars($_GET["sort"]) );
}
if(isset($_GET["key"])){
	$key = mb_strtolower( htmlspecialchars(urldecode($_GET["key"])) ); //URLデコードもしておく
}
if(isset($_GET["license"])){
	$license = mb_strtolower( htmlspecialchars($_GET["license"]) );
}
if(isset($_GET["offset"])){
	$offset = mb_strtolower( htmlspecialchars($_GET["offset"]) );
}
if(isset($_GET["limit"])){
	$limit = mb_strtolower( htmlspecialchars($_GET["limit"]) );
}
if(isset($_GET["jsonld"])){
	$jsonld = mb_strtolower( htmlspecialchars($_GET["jsonld"]) );
}
if(isset($_GET["jsonp"])){
	$jsonp = htmlspecialchars($_GET["jsonp"]) ;
}
if(isset($_GET["format"])){
	$format = mb_strtolower( htmlspecialchars($_GET["format"]) );
}

$arr["status"] = "yes"; //ステータスはyesがデフォルト

///////////////////////// sortが存在する　かつ　aからjのいずれか1文字（完全一致）で構成されているか
if(isset($_GET["sort"]) && preg_match('/^[a-l]$/', $sort)) {
	$alldata = sort_excute($alldata , $sort) ; // sortの関数を実行
// sortが存在するがパラメータが不正
}else if(isset($_GET["sort"]) && !preg_match('/^[a-l]$/', $sort)) {
	//ブランクでない場合は不正　ブランクの場合は何もせず
	if($sort != ""){
		$arr["status"] = "no"; 
		$arr["error"] = "sortパラメータが不正です。" ; 
	}
}

///////////////////////// keyが存在する（区切り文字は @@ ）
if(isset($_GET["key"])) {
	$alldata = keywordSearch($alldata , $key) ; // キーワード検索の関数を実行
}

///////////////////////// licenseが存在する　かつ　aからjのいずれか1文字（完全一致）で構成されているか
if(isset($_GET["license"]) && preg_match('/^[a-m]$/', $license)) { 
	$alldata = license_excute($alldata , $license) ; // sortの関数を実行
// licenseが存在するがパラメータが不正
}else if(isset($_GET["license"]) && !preg_match('/^[a-m]$/', $license)) {
	//ブランクでない場合は不正　ブランクの場合は何もせず
	if($license != ""){
		$arr["status"] = "no"; 
		$arr["error"] = "licenseパラメータが不正です。" ; 
	}
}

///////////////////////// offsetが存在する　かつ　数字のみで構成されているか
if(isset($_GET["offset"]) && !preg_match('/[^0-9]/', $offset) ) { 
	//ブランクでない場合のみ関数の実行（正規表現で制限してもブランクは通ってしまうため）
	if($offset != ""){
		$alldata = offset_excute($alldata , $offset) ; // sortの関数を実行
	}
///////////////////////// offsetが存在するがパラメータが不正
}else if(isset($_GET["offset"]) && preg_match('/[^0-9]/', $offset) ) {
	$arr["status"] = "no"; 
    $arr["error"] = "offsetパラメータが不正です。" ; 
}

///////////////////////// limitが存在する　かつ　数字のみで構成されているか
if(isset($_GET["limit"]) && !preg_match('/[^0-9]/', $limit)) { 
	//ブランクでない場合のみ関数の実行（正規表現で制限してもブランクは通ってしまうため）
	if($limit != ""){
		$alldata = limit_excute($alldata , $limit) ; // sortの関数を実行
	}
// limitが存在するがパラメータが不正
}else if(isset($_GET["limit"]) && preg_match('/[^0-9]/', $limit)) {
	$arr["status"] = "no"; 
    $arr["error"] = "limitパラメータが不正です。" ; 
}

///////////////////////// jsonldが存在する　かつ　０か１で構成されているか
if(isset($_GET["jsonld"]) && preg_match('/^[0-1]$/', $jsonld)) { 
	if($jsonld == 1){
		$alldata = jsonld_excute($alldata) ; // sortの関数を実行
	}
// jsonldが存在するがパラメータが不正
}else if(isset($_GET["jsonld"]) && !preg_match('/^[0-1]$/', $jsonld)) {
	if($jsonld != ""){
		$arr["status"] = "no"; 
		$arr["error"] = "jsonldパラメータが不正です。" ; 
	}
}

///////////////////////// jsonpが存在する　かつ　半角英数字で構成されているか
$jsonpFlag = false;
if(isset($_GET["jsonp"]) && preg_match("/^[a-zA-Z0-9]+$/", $jsonp)) { 
	//$alldata = jsonp_excute($alldata , $jsonp) ; // sortの関数を実行
	$jsonpFlag = true;
	
// jsonpが存在するがパラメータが不正
}else if(isset($_GET["jsonp"]) && !preg_match("/^[a-zA-Z0-9]+$/", $jsonp)) {
	if($jsonp != ""){
		$arr["status"] = "no"; 
		$arr["error"] = "jsonpパラメータが不正です。" ; 
	}
}

///////////////////////// formatが存在する　かつ　数字のみで構成されているか  
// json_encodeに渡す第二引数「SON_PRETTY_PRINT」は整形、
//「JSON_UNESCAPED_UNICODE」はユニコードエスケープをしない（日本語をそのまま表示）

// formatが存在しパラメータが正
if(isset($_GET["format"]) && preg_match('/^[0-3]$/', $format)) { 

	if($arr["status"] == "yes"){ //ここまでyesを保っていた場合はalldataをresultに格納
		$arr["result"] = $alldata ; 
	}
	
	if($format == "1"){  //整形あり　普通の２バイト文字列
		$result_data = json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); 
		if($jsonpFlag){
			header("Content-type: application/x-javascript; charset=utf-8"); //jsonpの場合はContent-typeをapplication/x-javascript
			$result_data = $jsonp.' ( '.$result_data.' ); '; //jsonpのコールバック関数を付加
		}
		// print stripslashes($result_data); // stripslashes関数でエスケープのバックスラッシュを取り除く
		print $result_data; // stripslashes関数でエスケープのバックスラッシュを取り除く
		
	}else if($format == "2"){ //整形なし　ユニコードエスケープ
		$result_data = json_encode($arr);
		if($jsonpFlag){
			header("Content-type: application/x-javascript; charset=utf-8");
			$result_data = $jsonp.' ( '.$result_data.' ); ';
		}
		print $result_data;
		
	}else if($format == "3"){ //整形あり　ユニコードエスケープ
		$result_data = json_encode($arr, JSON_PRETTY_PRINT);
		if($jsonpFlag){
			header("Content-type: application/x-javascript; charset=utf-8");
			$result_data = $jsonp.' ( '.$result_data.' ); ';
		}
		print $result_data;
		
	}else {  //整形なし　普通の２バイト文字列（デフォルト）
		$result_data = json_encode($arr, JSON_UNESCAPED_UNICODE);
		if($jsonpFlag){
			header("Content-type: application/x-javascript; charset=utf-8");
			$result_data = $jsonp.' ( '.$result_data.' ); '; 
		}
		print $result_data;
	}
	
// formatが存在するがパラメータが不正
}else if(isset($_GET["format"]) && !preg_match('/^[0-3]$/', $format)) {
	if($format != ""){ //formatに変な文字列が入っていた
		$arr["status"] = "no"; 
		$arr["error"] = "formatパラメータが不正です。" ; 
	}else if($arr["status"] == "yes"){ //formatが空欄 で yesを保っていた
		$arr["result"] = $alldata ; 
	}
	
	$result_data = json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); 
	if($jsonpFlag){
		$result_data = $jsonp.' ( '.$result_data.' ); '; //jspnp
	}
	print $result_data;
	
// formatが存在しない等
}else{
	if($arr["status"] == "yes"){ //yesを保っていた
		$arr["result"] = $alldata ; 
	}
	
	$result_data = json_encode($arr, JSON_UNESCAPED_UNICODE); 
	if($jsonpFlag){
		$result_data = $jsonp.' ( '.$result_data.' ); '; //jspnp
	}
	print $result_data;
}

exit;

//******************************************  以下ファンクション  ******************************************

function sort_excute($alldata , $sort){
	// a → データNo順（昇順）
	if ($sort==="a"){ 
		foreach ($alldata as $key => $value) {
			$arr1[$key] = $value['num'];
		}
		if($arr1){
			array_multisort($arr1, SORT_ASC, $alldata);
		}
	// b → データNo順（降順）
	}else if ($sort==="b"){
		foreach ($alldata as $key => $value) {
			$arr1[$key] = $value['num'];
		}
		if($arr1){
			array_multisort($arr1, SORT_DESC, $alldata);
		}
	// c → 登録日時順（昇順）
	}else if ($sort==="c"){ 
		foreach ($alldata as $key => $value) {
			// 更新日時があるデータはそちらを優先
			if($value['updtime'] != ""){
				$arr1[$key] = $value['updtime'];
			}else{
				$arr1[$key] = $value['rectime'];
			}
		}
		if($arr1){
			array_multisort($arr1, SORT_ASC, $alldata);
		}
	// d → 登録日時順（降順）
	}else if ($sort==="d"){
		foreach ($alldata as $key => $value) {
			// 更新日時があるデータはそちらを優先
			if($value['updtime'] != ""){
				$arr1[$key] = $value['updtime'];
			}else{
				$arr1[$key] = $value['rectime'];
			}
		}
		if($arr1){
			array_multisort($arr1, SORT_DESC, $alldata);
		}
	// e → ダウンロード数順（昇順）
	}else if ($sort==="e"){
		foreach ($alldata as $key => $value) {
			$arr1[$key] = $value['counter'];
		}
		if($arr1){
			array_multisort($arr1, SORT_ASC, $alldata);
		}
	// f → ダウンロード数順（降順）
	}else if ($sort==="f"){
		foreach ($alldata as $key => $value) {
			$arr1[$key] = $value['counter'];
		}
		if($arr1){
			array_multisort($arr1, SORT_DESC, $alldata);
		}
	// g → ファイルサイズ順（昇順）
	}else if ($sort==="g"){
		foreach ($alldata as $key => $value) {
			$arr1[$key] = $value['filesize'];
		}
		if($arr1){
			array_multisort($arr1, SORT_ASC, $alldata);
		}
	// h → ファイルサイズ順（降順）
	}else if ($sort==="h"){
		foreach ($alldata as $key => $value) {
			$arr1[$key] = $value['filesize'];
		}
		if($arr1){
			array_multisort($arr1, SORT_DESC, $alldata);
		}
	// i → データ名順（昇順）
	}else if ($sort==="i"){
		foreach ($alldata as $key => $value) {
			$arr1[$key] = $value['dataname'];
		}
		if($arr1){
			array_multisort($arr1, SORT_ASC, $alldata);
		}
	// j → データ名順（降順）
	}else if ($sort==="j"){
		foreach ($alldata as $key => $value) {
			$arr1[$key] = $value['dataname'];
		}
		if($arr1){
			array_multisort($arr1, SORT_DESC, $alldata);
		}
	// k → ファイル名順（昇順）
	}else if ($sort==="k"){
		foreach ($alldata as $key => $value) {
			$arr1[$key] = $value['filename'];
		}
		if($arr1){
			array_multisort($arr1, SORT_ASC, $alldata);
		}
	// l → ファイル名順（降順）
	}else if ($sort==="l"){
		foreach ($alldata as $key => $value) {
			$arr1[$key] = $value['filename'];
		}
		if($arr1){
			array_multisort($arr1, SORT_DESC, $alldata);
		}
	}
	return $alldata ;
}

//検索の結果を返す関数
function keywordSearch($alldata , $keyString){
	$keyArr = explode('@', $keyString); //キーワードを配列に格納　※区切り文字は半角アットマーク
	for ($i=0; $i<count($keyArr); $i++) { 
		$keyArr[$i] = mb_convert_kana($keyArr[$i], "KVa"); //全角文字と半角文字を統一
		$keyArr[$i] = mb_strtolower($keyArr[$i]); //大文字は小文字に変換
	}
	$alldata_result = [] ;
	for ($i=0; $i<count($alldata); $i++) {
		//キーワード検索の対象となる項目の文字列を連結
		$targetString = $alldata[$i]['filename'].' '.$alldata[$i]['dataname'].' '.$alldata[$i]['comment'].' '.$alldata[$i]['copyright'].' '.$alldata[$i]['registrant'] ; 
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

function license_excute($alldata , $license){
	$arr1 = [] ;
	if ($license === "a"){ 
		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]["license"] === "CC0" ){
				array_push($arr1 , $alldata[$i]) ;
			}
		}
	}else if ($license === "b"){ 
		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]["license"] === "CC-BY" ){
				array_push($arr1 , $alldata[$i]) ;
			}
		}
	}else if ($license === "c"){ 
		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]["license"] === "CC-BY-SA" ){
				array_push($arr1 , $alldata[$i]) ;
			}
		}
	}else if ($license === "d"){ 
		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]["license"] === "CC-BY-ND" ){
				array_push($arr1 , $alldata[$i]) ;
			}
		}
	}else if ($license === "e"){ 
		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]["license"] === "CC-BY-NC" ){
				array_push($arr1 , $alldata[$i]) ;
			}
		}
	}else if ($license === "f"){ 
		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]["license"] === "CC-BY-NC-SA" ){
				array_push($arr1 , $alldata[$i]) ;
			}
		}
	}else if ($license === "g"){ 
		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]["license"] === "CC-BY-NC-ND" ){
				array_push($arr1 , $alldata[$i]) ;
			}
		}
	}else if ($license === "h"){ 
		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]["license"] === "Public domain" ){
				array_push($arr1 , $alldata[$i]) ;
			}
		}
	}else if ($license === "i"){ 
		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]["license"] === "MIT License" ){
				array_push($arr1 , $alldata[$i]) ;
			}
		}
	}else if ($license === "j"){ 
		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]["license"] === "GNU General Public License" ){
				array_push($arr1 , $alldata[$i]) ;
			}
		}
	}else if ($license === "k"){ 
		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]["license"] === "GNU Free Documentation License" ){
				array_push($arr1 , $alldata[$i]) ;
			}
		}
	}else if ($license === "l"){ 
		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]["license"] === "Apache License" ){
				array_push($arr1 , $alldata[$i]) ;
			}
		}
	}else if ($license === "m"){ 
		for ($i=0; $i<count($alldata); $i++) { 
			if($alldata[$i]["license"] === "その他" ){
				array_push($arr1 , $alldata[$i]) ;
			}
		}
	}
	return $arr1 ;
}

function offset_excute($alldata , $offset){
	$arr1 = [] ;
	for ($i=intval($offset); $i<count($alldata); $i++) { 
		array_push($arr1 , $alldata[$i]) ;
	}
	return $arr1 ;
}

function limit_excute($alldata , $limit){
	$arr1 = [] ;
	
	if(count($alldata) < intval($limit)){
		$limit = count($alldata);
	}
	
	for ($i=0; $i<intval($limit); $i++) { 
		if($alldata[$i]){ //ヌルでない場合のみプッシュ
			array_push($arr1 , $alldata[$i]) ;
		}
	}
	return $arr1 ;
}

function jsonld_excute($alldata){
	$context = array(
	"xsd" => "http://www.w3.org/2001/XMLSchema#", 
	"rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#", 
	"rdfs" => "http://www.w3.org/2000/01/rdf-schema#",
	"dcterms" => "http://purl.org/dc/terms/", 
	"dcat" => "http://www.w3.org/ns/dcat#", 
	"cc" => "http://creativecommons.org/ns#",
	"sioc" => "http://rdfs.org/sioc/ns#", 
	);

	$graph = array();

	$urlStr = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // 現在のURLを取得
	$urlstrpos = strpos($urlStr, '?');              // パラメータの?の位置を取得
	$urlStr = mb_substr($urlStr, 0, $urlstrpos);    // パラメータ部分を削除
	$urlStr = str_replace('api.php', '', $urlStr); // [api.php]を削除してベースURLを確定

	$licenseUrl = "";
	for ($i=0; $i<count($alldata); $i++) {
		if($alldata[$i]['license'] == "CC0"){
			$licenseUrl = "https://creativecommons.org/publicdomain/zero/1.0/" ;
		}else if($alldata[$i]['license'] == "CC-BY"){
			$licenseUrl = "https://creativecommons.org/licenses/by/4.0/" ;
		}else if($alldata[$i]['license'] == "CC-BY-SA"){
			$licenseUrl = "https://creativecommons.org/licenses/by-sa/4.0/" ;
		}else if($alldata[$i]['license'] == "CC-BY-ND"){
			$licenseUrl = "https://creativecommons.org/licenses/by-nd/4.0/" ;
		}else if($alldata[$i]['license'] == "CC-BY-NC"){
			$licenseUrl = "https://creativecommons.org/licenses/by-nc/4.0/" ;
		}else if($alldata[$i]['license'] == "CC-BY-NC-SA"){
			$licenseUrl = "https://creativecommons.org/licenses/by-nc-sa/4.0/" ;
		}else if($alldata[$i]['license'] == "CC-BY-NC-ND"){
			$licenseUrl = "https://creativecommons.org/licenses/by-nc-nd/4.0/" ;
		}else if($alldata[$i]['license'] == "Public domain"){
			$licenseUrl = "https://creativecommons.org/publicdomain/mark/1.0/" ;
		}else if($alldata[$i]['license'] == "MIT License"){
			$licenseUrl = "https://opensource.org/licenses/mit-license.php" ;
		}else if($alldata[$i]['license'] == "GNU General Public License"){
			$licenseUrl = "https://www.gnu.org/licenses/gpl-3.0.html" ;
		}else if($alldata[$i]['license'] == "GNU Free Documentation License"){
			$licenseUrl = "https://www.gnu.org/licenses/fdl-1.3.html" ;
		}else if($alldata[$i]['license'] == "Apache License"){
			$licenseUrl = "http://www.apache.org/licenses/LICENSE-2.0" ;
		}

		$contents = array(
		"@id" => $urlStr.$alldata[$i]['num'].'/' , 
		"@type" => "dcat:Dataset", 
		"rdfs:label" => $alldata[$i]['num'] , 
		"dcat:accessURL" => array("@id" => $urlStr.$alldata[$i]['num'].'/' ), 
		"dcat:downloadURL" => array("@id" => $urlStr.$alldata[$i]['num'].'/'.$alldata[$i]['filename'] ), 
		"dcat:byteSize" => $alldata[$i]['filesize'], 
		"dcterms:title" => $alldata[$i]['dataname'], 
		"dcterms:description" => $alldata[$i]['comment'], 
		"dcterms:license" => $alldata[$i]['license'], 
		"cc:license" => array("@id" => $licenseUrl ), 
		"dcterms:rightsHolder" => $alldata[$i]['copyright'], 
		"dcterms:publisher" => $alldata[$i]['registrant'], 
		"dcterms:created" => array("@type" => "xsd:dateTime" , "@value" => $alldata[$i]['rectime']),
		"dcterms:modified" => array("@type" => "xsd:dateTime" , "@value" => $alldata[$i]['updtime']), 
		"sioc:num_views" => $alldata[$i]['counter'], 
		);
		array_push($graph , $contents); //$graph配列に$contentsオブジェクト追加
	}
	return array("@context" => $context , "@graph" => $graph) ;
}
?>