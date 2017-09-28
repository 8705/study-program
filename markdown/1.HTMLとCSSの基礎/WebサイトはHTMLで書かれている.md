* そうなんです
* webなんです！

# プログラミング塾の生徒用サーバー構築
![](https://s3-ap-northeast-1.amazonaws.com/codecamp-rails/production/banners/image/81/original.png?1505960016)
## 生徒作成スクリプト
ポイントは
* ユーザーを必ず`student`グループに属させること
* $HOMEはchroot対象ディレクトリなのでroot以外には書き込み権限をつけれない（chrootの仕様）のでその下に`$HOME/files`ディレクトリを作ってそこにアップロードしてもらう
* vhostsのドキュメントルートは`$HOME/files/public`を指定いる


```bash
useradd someuser
passwd someuser #ここ自動生成やな
usermod -aG student someuser
chmod 755 /home/someuser
chown root.root /home/someuser
mkdir -pm 755 /home/someuser/files/public
chown someuser.student /home/someuser/files /home/someuser/files/public

# mysql
CREATE DATABASE db_someuser;
GRANT ALL PRIVILEGES ON db_test.* TO someuser@"%" IDENTIFIED BY 'pass^Pass123';

# echo 'ユーザー名','パスワード'
```
### ディレクトリ構成
```
/home/
    ├ [生徒A]    
    │   └ files/ # ←ここ以下は自由にファイルアップロード可能
    │       └ public/  # ←vhostsのDocumentRoot
    ├ [生徒B]    
    │   └ files/ # ←ここ以下は自由にファイルアップロード可能
    │       └ public/  # ←vhostsのDocumentRoot
    ・
    ・
    ・
```

## 以下構築記録
![](https://s3-ap-northeast-1.amazonaws.com/codecamp-rails/production/banners/image/80/original.png?1505959901)
## yumでapacheをインストール
CentOS7からはyumの標準リポジトリでapache2.4系を提供。

```
# yum install -y httpd
# httpd -V

Server version: Apache/2.4.6 (CentOS)
Server built:   Aug 23 2017 15:47:21
Server's Module Magic Number: 20120211:24
Server loaded:  APR 1.4.8, APR-UTIL 1.6.0
Compiled using: APR 1.4.8, APR-UTIL 1.5.2
Architecture:   64-bit
Server MPM:     prefork
  threaded:     no
    forked:     yes (variable process count)
Server compiled with....
 -D APR_HAS_SENDFILE
 -D APR_HAS_MMAP
 -D APR_HAVE_IPV6 (IPv4-mapped addresses enabled)
 -D APR_USE_SYSVSEM_SERIALIZE
 -D APR_USE_PTHREAD_SERIALIZE
 -D SINGLE_LISTEN_UNSERIALIZED_ACCEPT
 -D APR_HAS_OTHER_CHILD
 -D AP_HAVE_RELIABLE_PIPED_LOGS
 -D DYNAMIC_MODULE_LIMIT=256
 -D HTTPD_ROOT="/etc/httpd"
 -D SUEXEC_BIN="/usr/sbin/suexec"
 -D DEFAULT_PIDLOG="/run/httpd/httpd.pid"
 -D DEFAULT_SCOREBOARD="logs/apache_runtime_status"
 -D DEFAULT_ERRORLOG="logs/error_log"
 -D AP_TYPES_CONFIG_FILE="conf/mime.types"
 -D SERVER_CONFIG_FILE="conf/httpd.conf"

```

### firewalld(iptablesのCentOS7版）でhttp開放
[RHEL/CentOS7ではiptablesではなくfirewalld \- Qiita](https://qiita.com/sak_2/items/fe996c518e8075214b49)

```
// すべてのzone確認
firewall-cmd --list-all-zones

// http(80)を開放
firewall-cmd --add-service=http --zone=public --permanent

// 直接3306を開放
# firewall-cmd --add-port=3306/tcp --zone=public --permanent
# firewall-cmd --reload
```

### mod_vhost_aliasで動的に
[mod\_vhost\_aliasでサブドメイン動的生成 \- Artsnet](http://artsnet.jp/archives/32/)

virtualDocumentRoot設定

```
<VirtualHost *:80>
  ServerName study.8705.co
  ServerAlias *.study.8705.co
  VirtualDocumentRoot /home/%1/files/public #←"%1"にワイルドカードの文字列が入る

  DirectoryIndex  index.php index.html
  <FilesMatch \.php$>
    SetHandler application/x-httpd-php
    # とりあえず7.1.8固定
    SetHandler "proxy:unix:/var/run/php-fpm-7.1.8.sock|fcgi://localhost"
  </FilesMatch>
  # ProxyPassMatch ^/(.*\.php(/.*)?)$ fcgi://127.0.0.1:9000/home/%1/files/public/$1
  <Proxy fcgi://127.0.0.1:9000>
     ProxySet timeout=1800
  </Proxy>
  <Directory "/home">
    AllowOverride All
    Options -Indexes +FollowSymLinks
    Require all granted
  </Directory>
</VirtualHost>

```


## 生徒の権限、php-fpmの権限
生徒一人ずつにユーザーを作成。php-fpmの実行ユーザーを`student`にして生徒ユーザーを`student`グループにいれる。
* apacheの実行ユーザーも`student`
* php-fpmの実行ユーザーも`student`
* chrootの仕様としてchroot先のディレクトリの所有者はrootじゃないとだめ、つまり生徒ユーザーを作成後root所有にする必要がある

## sshdの設定（ログイン許可まわり）

```
# vi /etc/ssh/sshd_config

// ssh接続しても良いグループを制限
AllowGroups root student

// 追加 sdudentグループのユーザーはchroot適応
Match Group student
  ChrootDirectory ~
  ForceCommand internal-sftp # sshで接続しても強制的にsfptに流される
  PasswordAuthentication yes
```

[chrootされたsftp専用ユーザを作るメモ \- Qiita](https://qiita.com/kawaz/items/53d1c837dd762337eb3b)


## phpenvのインストール

```
curl -L https://raw.github.com/CHH/phpenv/master/bin/phpenv-install.sh | bash
```

`~/.bashrc`に追記

```
export PATH=$HOME/.phpenv/bin:$PATH
eval "$(phpenv init -)
```
php-buildをプラグインとしてインストール

```
mkdir ~/.phpenv/plugins
cd ~/.phpenv/plugins
git clone git://github.com/php-build/php-build.git
```
phpをビルドするのに必要なモジュールをインストール

```
yum install -y bison re2c httpd-devel libxml2-devel bzip2-devel libjpeg-devel libpng-devel libXpm-devel freetype freetype-devel recode-devel gmp-devel readline-devel php-imap php-mcrypt libc-client-devel libmcrypt libmcrypt-devel libcurl curl-devel libicu-devel libtidy-devel libxslt-devel
```

phpenvでインストールできるバージョンの確認

```
phpenv install -l
```
`7.1.8`をインストール

```
phpenv install 7.1.8

phpenv global 7.1.8
php -v

PHP 7.1.8 (cli) (built: Sep 20 2017 11:08:53) ( NTS )
Copyright (c) 1997-2017 The PHP Group
Zend Engine v3.1.0, Copyright (c) 1998-2017 Zend Technologies
    with Zend OPcache v7.1.8, Copyright (c) 1999-2017, by Zend Technologies
    with Xdebug v2.5.5, Copyright (c) 2002-2017, by Derick Rethans
```
ユーザーの変更／TCPからUNIXソケットへの変更

```
# vi /root/.phpenv/versions/7.1.8/etc/php-fpm.d/www.conf

----------------------
user = student
group = student
listen = /var/run/php-fpm-7.1.8.sock
listen.owner = student
listen.group = student
listen.mode = 0660
----------------------

```


## mysql 5.7インストール

```
// 元もとはいっているmariadbを削除
# yum remove -y mariadb-libs

// リポジトリ追加
# yum install -y http://dev.mysql.com/get/mysql57-community-release-el7-7.noarch.rpm

// mysql インストール
# yum install -y mysql  mysql-devel mysql-server mysql-utilities

// 設定ファイル
# cp /etc/my.cnf /etc/my.cnf.orig
# vi /etc/my.cnf

-------------------
[client]
default-character-set = utf8

[mysqld]
# デフォルトで利用するストレージエンジンにInnoDBを指定
default-storage-engine=InnoDB

# InnoDBのファイルをテーブル毎に作成するように指定
innodb_file_per_table

skip-character-set-client-handshake
character-set-server = utf8
collation-server = utf8_general_ci
init-connect = SET NAMES utf8
datadir=/var/lib/mysql
socket=/var/lib/mysql/mysql.sock
user=mysql

# Disabling symbolic-links is recommended to prevent assorted security risks
symbolic-links=0

log-error=/var/log/mysqld.log
pid-file=/var/run/mysqld/mysqld.pid

[mysqldump]
default-character-set = utf8

[mysql]
default-character-set = utf8

[mysqld_safe]
log-error=/var/log/mysqld.log
pid-file=/var/run/mysqld/mysqld.pid

!includedir /etc/my.cnf.d

-------------------

// 起動
# systemctl start mysqld
# systemctl enable mysqld
// パスワード変更
# mysql -uroot -p
mysql > set password for root@localhost=password('passwordPASSWORD@999');
```
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
## ↓ボツ
## Apacheインストール
[Apache httpd 2\.4 をソースからインストールする手順 \(CentOS/RedHat\) \| WEB ARCH LABO](https://weblabo.oscasierra.net/install-apache24-1/)

記事と現在有効なバージョンが異なるので調整

```
# yum install -y gcc make pcre pcre-devel wget expat-devel openssl-devel

# cd /usr/local/src
# wget http://ftp.kddilabs.jp/infosystems/apache//apr/apr-1.6.2.tar.gz
# tar zxvf apr-1.6.2.tar.gz
# cd apr-1.6.2
# ./configure --prefix=/opt/apr/apr-1.6.2
# make
# make install

# cd /usr/local/src
# wget http://ftp.kddilabs.jp/infosystems/apache//apr/apr-util-1.6.0.tar.gz
# tar zxvf apr-util-1.6.0.tar.gz
# cd apr-util-1.6.0
# ./configure --prefix=/opt/apr-util/apr-util-1.6.0 --with-apr=/opt/apr/apr-1.6.2 --with-expat=builtin
# make
# make install

# cd /usr/local/src
# wget http://ftp.tsukuba.wide.ad.jp/software/apache//httpd/httpd-2.4.27.tar.gz
# tar zxvf httpd-2.4.27.tar.gz
# cd httpd-2.4.27
# ./configure --prefix=/opt/httpd/httpd-2.4.47 \
--enable-ssl \
--with-ssl \
--with-mpm=prefork \
--enable-so \
--enable-mods-shared=all \
--enable-mpms-shared=all \
--with-apr=/opt/apr/apr-1.6.2 \
--with-apr-util=/opt/apr-util/apr-util-1.6.0/bin　\
--enable-rewrite \
--enable-proxy \
--enable-proxy-balancer
# make
# make install

// 起動スクリプト用意
cat > /usr/lib/systemd/system/httpd.service << EOF
[Unit]
Description=The Apache HTTP Server (event MPM)
After=syslog.target network.target remote-fs.target nss-lookup.target

[Service]
Type=forking
PIDFile=/var/run/httpd/httpd.pid
#EnvironmentFile=/etc/sysconfig/httpd
ExecStart=/opt/httpd/httpd-2.4.47/bin/apachectl $OPTIONS -k start
ExecReload=/opt/httpd/httpd-2.4.47/bin/apachectl $OPTIONS -t
ExecReload=/bin/kill -HUP $MAINPID
ExecStop=/opt/httpd/httpd-2.4.47/bin/apachectl $OPTIONS -k stop
PrivateTmp=true

[Install]
WantedBy=multi-user.target

EOF

# systemctl daemon-reload
# systemctl start httpd
# systemctl enable httpd

```
httpd起動でエラーになった。詳細はシステムログに書いてある
[systemdの"code=exited, status=203/EXEC"エラー \- ベスパライフ](http://takeg.hatenadiary.jp/entry/2017/02/14/233109)

### ソースインストールしたhttpdは通常はsystemctlで起動が上手くいかないらしい
回避→[Apache2\.4をインストールする\(ソースからコンパイル\) for CentOS7\.3 \(systemd対応\) \- Qiita](https://qiita.com/shadowhat/items/163ee5fdd56c51100e9e)

## ↓ボツ
## openssl1.0.2,openssh,curlをインストール
### openssl1.0.2
```
yum -y install zlib-devel perl-core && \
cd /usr/local/src && \
wget https://www.openssl.org/source/openssl-1.0.2l.tar.gz && \
tar xvzf openssl-1.0.2l.tar.gz && \
cd openssl-1.0.2l && \
./config -fPIC shared && \
make && \
make install && \
echo /usr/local/ssl/lib > /etc/ld.so.conf.d/usr-local-ssl-lib.conf && \
ldconfig && \
/usr/local/ssl/bin/openssl version
OpenSSL 1.0.2l  25 May 2017

```

## openssh
```
// install openssh
# echo /usr/local/ssl > /etc/ld.so.conf.d/ssl.conf

# yum install -y pam-devel
# cd /usr/local/src
# wget http://ftp.jaist.ac.jp/pub/OpenBSD/OpenSSH/portable/openssh-7.5p1.tar.gz
# tar xvzf openssh-7.5p1.tar.gz
# cd openssh-7.5p1
# ./configure \
--with-zlib \
--with-tcp-wrappers \
--with-ssl-dir=/usr/local/openssl-1.0.2l \
--with-ssl-engine \
--with-pam \
--with-md5-passwords \
--bindir=/usr/bin \
--sbindir=/usr/sbin \
--libexecdir=/usr/libexec \
--sysconfdir=/etc/ssh
# make
# make install

# ssh -V
OpenSSH_7.5p1, OpenSSL 1.0.2l  25 May 2017

```
