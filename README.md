# オープンデータプラットフォーム Yo-KAN

<img src="https://www.mirko.jp/yo-kan/img_uploaded/sample_logo.png" />
Yo-KAN は CKAN や DKAN と同じような、データ公開のためのオープンソースプログラムです。
Yo-KAN を利用すれば、WordPress風のデータストアが実に簡単に作成できます。
 
# DEMO
* <a href="https://www.mirko.jp/yo-kan/" target="_blank">（VPSサーバー）メインのYo-KAN紹介サイト</a><br>
* <a href="http://yokan.php.xdomain.jp/" target="_blank">（無料レンタルサーバー）XFREE に設置</a><br>
* <a href="http://yokan.starfree.jp/" target="_blank">（無料レンタルサーバー）スターサーバーフリー に設置</a><br>
* <a href="https://ss1.xrea.com/yookan.s1010.xrea.com/" target="_blank">（無料レンタルサーバー）XREA Freeに設置（SSL対応）</a><br>
* <a href="https://yo-kan.azurewebsites.net/" target="_blank">（PaaS cloud）Microsoft Azure（App Service 無料枠）</a><br>
* <a href="https://yo-kan.herokuapp.com/" target="_blank">（PaaS cloud）Heroku（無料枠）</a> ←寝てると起きるのに15秒ほどかかります<br>
* <a href="https://oracle-yo-kan.ga/" target="_blank">（IaaS cloud）Oracle Cloud（無料枠）</a><br>

# Installation
このリポジトリのファイル・ディレクトリをダウンロードし、FTPやGitなどお好みの方法で、PHPが動作するWEBサーバーへすべてまとめてアップロードしてください。
通常のレンタルサーバーの場合はFTPで流し込むだけです。WordPress可能なサーバーであればほぼ確実に動きます。

**Dockerコンテナイメージもあります。**
https://hub.docker.com/repository/docker/mirkomh/yo-kan

# Usage
WEBブラウザで、ファイルを置いたディレクトリのURLにアクセスしてください。<br>
最初に「管理者アカウント」の作成を求められますので、ID・パスワード・メールアドレスを入力し作成してください。
管理者アカウントを作成すると、Yo-KANのWEBサイトが公開されます。<br>
その後、管理画面にて、公開するデータをアップロードしたり、サイトの見た目の設定を行ってください。権限を限定した一般アカウントを作成することも可能です。

# Requirement
* PHP 7.0 以上（MySQLなどのデータベースは不要）<br>
【必須依存ライブラリ】<br>
　 ・ php-mbstring<br>
　 ・ GD<br> 
* メールの送信（お問い合わせフォーム・パスワードリセット）に必要なもの<br>
　・sendmail コマンドの使える環境（Postfix等）<br>
　・または 利用できる外部SMTPサーバー（Gmail等）（※ポート番号587（TLS）のSMTPのみ利用可）<br>
　・または SendGrid のAPIキー<br>
　【メール送信用の依存ライブラリ】<br>
 　　・ phpmailer 　 (外部SMTPサーバーを利用する場合のみ必要)<br>
 　　・ php-curl 　 (SendGridを利用する場合のみ必要)<br>
 　　・ SendGrid 　 (SendGridを利用する場合のみ必要)<br>

# Note
PHPの設定でアップロードできるファイルサイズが小さくなっている場合があります。<br>
（デフォルトではMAXが2MBになっていることが多いです。）<br>
その場合、**php.ini** の設定を変更し適宜調整してください。
```bash
（例）
　post_max_size = 20M
　upload_max_filesize = 20M
```

**php-mbstring** 及び **GD** は、PHPが利用できる通常のレンタルサーバーであれば、ほとんどの場合あらかじめインストールされていますので意識する必要はありませんが、クラウド等に自分でサーバーを立てた場合は各自でインストールしてください。<br>

メールの送信は、以下の順番で判定します。
1. 環境変数にSendGridのAPIキーが設定されている場合はSendGridを利用
2. 環境変数に外部SMTPサーバー（Gmail等）のホスト名・アカウント名・パスワードが設定されている場合はそれを利用
3. 上記以外の場合は **mb_send_mail**関数 で sendmail<br>

**SendGridを利用する場合の環境変数の設定**
```bash
    キー名                  値（例）
SENDGRID_API_KEY      SG.xxxx......................................
```
**外部SMTPサーバーを利用する場合の環境変数の設定**
```bash
    キー名                 値（例）
SMTP_HOST      　    smtp.gmail.com
SMTP_ACCOUNT         xxxxxx@gmail.com
SMTP_PASSWORD        yourgmailpass
```

**composer.json**
```bash
{
  "require": {
    "ext-mbstring": "*",
    "ext-gd": "*",
    "sendgrid/sendgrid": "~7",
    "curl/curl": "^2.0",
    "phpmailer/phpmailer": "^6.1"
  }
}
```

Microsoft Azure (App Service) 及び Heroku のPaaS環境で正常に動作することは確認しましたが、腕に覚えがある方はその他いろいろな環境でお試しいただければと思います。<br>

# Security
できるだけ<b>SSL対応（https）のサーバーに設置してください</b>。<br>
SSL未対応（http）のサーバーの場合は、ログインIDやパスワードが盗まれる可能性があります。<br>
なお、パスワードは平文10文字以上のものを、ブラウザJavascriptで SHA256ハッシュ（ソルト＆ストレッチング）した上で送信するため、http送信であっても解析が難しい状態にはなっています。このあたりは<b>自己責任において判断</b>してください。（20字程度のパスワードにすれば、よっぽどのことがない限り大丈夫でしょう。）<br><br>
<img src="https://www.mirko.jp/yo-kan/passimg.png" style="width:60%" /><br><br>
ログイン時にパスワードを5回連続誤ると、そのアカウントはロックされます。管理者アカウントがロックされると、解除するためのメールが管理者へ送信されます。

# Author
林　正洋
 
# License
[CCO](https://creativecommons.org/publicdomain/zero/1.0/deed.ja)




