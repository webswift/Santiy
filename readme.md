## git branching/work

the [feature branch workflow](https://www.atlassian.com/git/tutorials/comparing-workflows/feature-branch-workflow) with small modifications is used  

main branches:

- master - state at the production  
- test - used at [test](https://test.sanityos.com) , master + contains new features, probably unworkable

workflow:

* create feature banch and push it clean to repository:
```
git pull
git checkout -b issue_NNN_some_description master
git push -u origin issue_NNN_some_description
```

* make changes (use tabs for indendation)

```
git citool/commit ...
git push 
```

* commit message formatting:

```
name of issue
    
- change detail 1
- change detail 2
    
https://gitlab.com/sosdevteam/sos/issues/NNN
```

* publish to test (when ready)

```
git checkout test
git pull
git merge issue_NNN_some_description
#git push -u origin test # at first time
git push
```

* update [test](https://test.sanityos.com), ssh as sanityos-test to server, in the /home/sanityos-test/sos
```
git pull
php composer.phar install
./artisan migrate
```

* as root (if needed)
```
supervisorctl restart test_massmail_send:*
supervisorctl restart test_sanityos_queue
```

* production update
```
./artisan down
git pull
php composer.phar install
# make install-dependencies-production
make production-optimize
./artisan migrate
./artisan up
```
* as root:
```
supervisorctl restart all
```

* delete feature branch

```
git branch -d issue_NNN
git push origin --delete issue_NNN
```




## crontab

```
* * * * * php /home/ubuntu/www5/sanityos/artisan schedule:run >> /dev/null 2>&1
00 03 * * * /usr/local/bin/gdrive sync upload --no-progress /home/ubuntu/www5/sanityos/storage/app/http---www.sanityos.com 0B42xBSzBM3VaQnhuMDFFM3NMcFU
```

## Installation 

config templates: server/etc

```
timedatectl set-timezone UTC
apt-get install tmux zsh htop iotop iptraf etckeeper

wget https://repo.percona.com/apt/percona-release_0.1-4.$(lsb_release -sc)_all.deb
dpkg -i percona-release_0.1-4.$(lsb_release -sc)_all.deb
apt-get update
apt-get install percona-server-server-5.7

vim /etc/mysql/percona-server.conf.d/mysqld.cnf
mysql

<<<
CREATE DATABASE IF NOT EXISTS dev_sanityos CHARSET=utf8;
CREATE USER 'dev_sanityos_user'@'%' IDENTIFIED BY 'dev_sanityos_password';
GRANT ALL PRIVILEGES ON dev_sanityos.* TO 'dev_sanityos_user'@'%';
>>>

mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root -p mysql

echo 'deb http://nginx.org/packages/mainline/ubuntu/ xenial nginx' >> /etc/apt/sources.list
echo 'deb-src http://nginx.org/packages/mainline/ubuntu/ xenial nginx' >> /etc/apt/sources.list
wget -q -O- http://nginx.org/keys/nginx_signing.key | sudo apt-key add -

add-apt-repository ppa:ondrej/php

apt-get update
apt-get install curl software-properties-common python-software-properties 
apt-get install supervisor git zip  memcached 
apt-get install nginx php7.0 php7.0-curl php7.0-dev php7.0-gd php7.0-mcrypt php7.0-mysql php7.0-memcached php7.0-fpm php7.0-mbstring php7.0-xml php7.0-zip
apt-get install npm ruby ruby-dev libsqlite3-dev

ufw allow OpenSSH
ufw enable

ufw allow 5022
ufw allow 80
ufw allow 443
#ufw allow proto tcp from 178.165.23.159 to any port 80
#ufw allow proto tcp from 178.165.23.159 to any port 443
#ufw allow proto tcp from 178.165.23.159 to any port 1080

addgroup --gid 500 sanityos
adduser sanityos -gid 500 --uid 500

sudo -i -u sanityos
git clone git@gitlab.com:sosdevteam/sos.git

cd sos
./bootstrap.sh
./configure
make install-dependencies-production

cp .env.example .env
./artisan key:generate

sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
chown -R sanityos-test.www-data /home/sanityos-test/sos/storage/logs
chmod -R ugo+rwx /home/sanityos-test/sos/storage/logs

chown -R sanityos.www-data /home/sanityos/sos/public/assets/fileman/Uploads
chmod -R ugo+rwx /home/sanityos/sos/public/assets/fileman/Uploads

chown -R sanityos.www-data /home/sanityos/sos/public/assets/uploads/forms/logo
chmod -R ugo+rwx /home/sanityos/sos/public/assets/uploads/forms/logo


chown -R sanityos.www-data /home/sanityos/sos/public/.htaccess
chmod -R ugo+rw /home/sanityos/sos/public/.htaccess

mkdir -p -m 777 /home/sanityos/sos/vendor/paypal/rest-api-sdk-php/var
chmod -R ugo+rwx /home/sanityos/sos/vendor/paypal/rest-api-sdk-php/var

gem install mailcatcher
mailcatcher --help
mailcatcher
/*to stop */curl -v -X DELETE http://127.0.0.1:1080

supervisorctl status
```

## Zabbix:

```
wget http://repo.zabbix.com/zabbix/3.4/ubuntu/pool/main/z/zabbix-release/zabbix-release_3.4-1+xenial_all.deb
wget http://repo.zabbix.com/zabbix/3.2/ubuntu/pool/main/z/zabbix-release/zabbix-release_3.2-1+xenial_all.deb
dpkg -i zabbix-release_3.2-1+xenial_all.deb 
apt-get update
apt install zabbix-frontend-php
apt install --no-install-recommends zabbix-server-mysql
apt install snmpd lm-sensors snmp-mibs-downloader snmptrapd php7.0-bcmath
mysql:
create database zabbix character set utf8 collate utf8_bin;
grant all privileges on zabbix.* to zabbix@localhost identified by '2sdm,msd';

zcat /usr/share/doc/zabbix-server-mysql/create.sql.gz | mysql -uzabbix -p zabbix

vim /etc/zabbix/zabbix_server.conf

apt install zabbix-agent
install percona-zabbix-templates
```

## Laravel PHP Framework

[![Build Status](https://travis-ci.org/laravel/framework.svg)](https://travis-ci.org/laravel/framework)
[![Total Downloads](https://poser.pugx.org/laravel/framework/d/total.svg)](https://packagist.org/packages/laravel/framework)
[![Latest Stable Version](https://poser.pugx.org/laravel/framework/v/stable.svg)](https://packagist.org/packages/laravel/framework)
[![Latest Unstable Version](https://poser.pugx.org/laravel/framework/v/unstable.svg)](https://packagist.org/packages/laravel/framework)
[![License](https://poser.pugx.org/laravel/framework/license.svg)](https://packagist.org/packages/laravel/framework)

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Laravel attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as authentication, routing, sessions, queueing, and caching.

Laravel is accessible, yet powerful, providing powerful tools needed for large, robust applications. A superb inversion of control container, expressive migration system, and tightly integrated unit testing support give you the tools you need to build any application with which you are tasked.

## Official Documentation

Documentation for the framework can be found on the [Laravel website](http://laravel.com/docs).

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](http://laravel.com/docs/contributions).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

### License

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
