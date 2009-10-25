server=174.143.181.9
passwd=ca1eu2qwI



## TODO
## .screenrc
# # Suppress screen's startup message
# startup_message off
# # Define a bigger scrollback, default is 100 lines
# defscrollback 10000
# # An alternative hardstatus to display a bar at the bottom listing the
# # windownames and highlighting the current windowname in blue.
# hardstatus alwayslastline "%{.bW}%-w%{.rW}%n %t%{-}%+w %=%{..G} %H %{..Y} %m/%d\
#  %C%a "
# # Execute .bash_profile on startup
# shell -$SHELL
# # Use C-z instead of C-a as this makes more sense for Emacs
# escape ^za
# # Detach on hangup
# autodetach on
# termcapinfo xterm-color kD=\E[3~
# # STARTUP SCREENS
# screen -t Shell 0 bash
# select 0


## we will install everything on root, at least for now


## from http://www.casualcode.com/2008/05/18/how-to-enable-cron-log-in-ubuntu/
# Edit /etc/syslog.conf and uncomment the line starting with cron.*
#/etc/init.d/sysklogd restart
#/etc/init.d/cron restart



## first, lets set up ssh keys so we don't need to type passwords
cd
mkdir .ssh
echo 'ssh-dss AAAAB3NzaC1kc3MAAACBAKxow4SmgvdGARpRx2ur935xGNeatMqxNZSI/04VRoCEeudlfr8/+a5DDHNrZc5b2sB5RlC9LHEFko4lXybs/rYA243X3qu3U4AZM/SUiyzRsXTfTAetiOS7VamSwClh9z4BajLrEpABj7uNUMZxaVqfZNXnovMK2fzBT3gNcW51AAAAFQDn577+pa/9+BbmpMgiNpCoepsJuwAAAIAJWz/FCzv9vPWSrM+uXbO/EalYZ1K8mVjleBcqg7QlD++o7oCx00WunyXByfbouEZu0iMxthih/Leb+lKU81+GvS1pPly3HQ7w2gss+2WaPeNKUTBlL9RpaBM7uRR6PonyJ3j2yTv0SXEVnafXed41cHaOTPVUv8e+FEtUMP67hAAAAIBu0PwKTMwhWmsOTa8HZJ4AYe28IewK6iC2AAvYMrRw6P931m82rNSO3VgE2gvb+qonB6YzGMHHz/jlZhuYXvrArvVw2gI2CVzlamI6wNc5+POFKXdxUPorKW8omD+6GC/TDUwE/QI/ChaW3IMkTfo5kamEKeSikwKG84VH7KO78Q== eduardo@neguinha.local' > .ssh/authorized_keys2



## rsync for synchronization
aptitude install rsync


## ubuntu locales
##https://help.ubuntu.com/community/Locale
aptitude install locales
dpkg-reconfigure locales
locale-gen en_US.UTF-8
##echo en_US.UTF-8 UTF-8 > /etc/locale.gen 
echo 'export LC_CTYPE="en_US.UTF-8"' > ~/.bash_profile


## set up apache (web server) 
apt-get  install -y apache2
## check from local machine if the web server works


apt-get install man

## set up mysql
apt-get install libmysqlclient15-dev mysql-server-5.1

#cron
apt-get install cron


## MySQL



## in mysql
## mysql root password criutad4
## article on configuration:
## http://abbysays.wordpress.com/2008/05/20/how-to-startstop-mysql-server-on-ubuntu-804/
## configure mysql
# put this into  /etc/mysql/my.cnf

## (server ip) find the line with bind address and substitute with
bind-address=174.143.181.9


##character-set-database=utf8 ##not working`
character-set-server=utf8
collation-server=utf8_general_ci
default-character-set=utf8
default-collation=utf8_general_ci
init-connect='SET NAMES utf8'

##restart 
/etc/init.d/mysql restart


mysql -u root -p
CREATE DATABASE congressoaberto;
GRANT ALL PRIVILEGES ON congressoaberto.* TO "monte"@"%" IDENTIFIED BY "e123456";
CREATE DATABASE br;
CREATE DATABASE br_chamber;
GRANT ALL PRIVILEGES ON br.* TO "monte"@"%" IDENTIFIED BY "e123456";
FLUSH PRIVILEGES;


## copy dumpfile from dreamhost
bunzip2 backupbr.sql.bz2
mysql -u monte -pe123456 br  < backupbr.sql



## install php5
apt-get  install -y  php5  php5-cgi php5-cli php5 libapache2-mod-php5 php5-mysql php5-gd

## the default memory for php is too low. up it!
##edit /etc/php5/apache2/php.ini, change: memory_limit = 64M 


## test php
/etc/init.d/apache2 restart 
## test php install
echo '<?
phpinfo();
?>' > /var/www/info.php
## open in a browser
http://www.congressoaberto.com.br/info.php




## more stuff
apt-get install -y subversion screen
apt-get install -y git-core 
apt-get install imagemagick
apt-get installscreen  emacs  texlive




## create user
useradd -d /home/ca -m ca
passwd ca
su ca
## assing var/www to this user
cd /var/
chown -R ca:ca www



cd
cd
mkdir .ssh
echo 'ssh-dss AAAAB3NzaC1kc3MAAACBAKxow4SmgvdGARpRx2ur935xGNeatMqxNZSI/04VRoCEeudlfr8/+a5DDHNrZc5b2sB5RlC9LHEFko4lXybs/rYA243X3qu3U4AZM/SUiyzRsXTfTAetiOS7VamSwClh9z4BajLrEpABj7uNUMZxaVqfZNXnovMK2fzBT3gNcW51AAAAFQDn577+pa/9+BbmpMgiNpCoepsJuwAAAIAJWz/FCzv9vPWSrM+uXbO/EalYZ1K8mVjleBcqg7QlD++o7oCx00WunyXByfbouEZu0iMxthih/Leb+lKU81+GvS1pPly3HQ7w2gss+2WaPeNKUTBlL9RpaBM7uRR6PonyJ3j2yTv0SXEVnafXed41cHaOTPVUv8e+FEtUMP67hAAAAIBu0PwKTMwhWmsOTa8HZJ4AYe28IewK6iC2AAvYMrRw6P931m82rNSO3VgE2gvb+qonB6YzGMHHz/jlZhuYXvrArvVw2gI2CVzlamI6wNc5+POFKXdxUPorKW8omD+6GC/TDUwE/QI/ChaW3IMkTfo5kamEKeSikwKG84VH7KO78Q== eduardo@neguinha.local' > .ssh/authorized_keys2
mkdir reps
cd reps
git clone git://github.com/leoniedu/CongressoAberto.git
mkdir ~/reps/CongressoAberto/images/camara
## sometimes (not sure why) the rsync process does not overwrite older files
## in the git repository. nothing that a, for instance, rm ~/reps/CongressoAberto/R cannot resolve :)

 


apt-get install libcurl4-gnutls-dev







##apt-get install phpmyadmin ## not working, hosing mysql install







## wordpress through svn
##http://codex.wordpress.org/Installing/Updating_WordPress_with_Subversion
cd /var/www/
rm index.html
## look for new versions
svn co http://core.svn.wordpress.org/tags/2.8.4 .
cp wp-config-sample.php wp-config.php
## remmber define ('WPLANG', 'pt_br');
## open the server in the browser and configure wordpress
## themes
cd /var/www
touch .htaccess
chmod -v 666 .htaccess
## apache rewrites (htaccess = i think)
sudo a2enmod rewrite
#  /etc/apache2/sites-available/default
# <Directory /var/www/>
# 		Options Indexes FollowSymLinks MultiViews
# 		AllowOverride All <----------
# 		Order allow,deny
# 		allow from all
# 	</Directory>
cd /var/www/
mkdir php
mkdir images
cd php
mkdir cache
chmod 777 cache

## after sync
chmod 777 timthumb.php








##install R
echo deb http://cran.r-project.org/bin/linux/ubuntu/ jaunty/  >> /etc/apt/sources.list
gpg --keyserver subkeys.pgp.net --recv-key E2A11821
gpg -a --export E2A11821 | sudo apt-key add -
apt-get update
apt-get -y -t unstable install r-base r-base-dev r-cran-xml tidy


echo 'options(repos= c(CRAN="http://cran.wustl.edu/"))' > .Rprofile
echo 'install.packages(c("ggplot2", "car", "RMySQL", "XML", "wnominate", "pcsl", "arm", "maptools", "car", "ggplot2", "wnominate"), dep=TRUE)'  > installpackages.R
R --no-save < installpackages.R





## delete tables using R
wpClean()
lapply(grep("^t.*|^wp.*", dbListTables(connect), value=TRUE), function(x) dbRemoveTable(connect, x))



## firewall (not needed)
##/sbin/iptables -A INPUT -i eth0 -p tcp --destination-port 3306 -j ACCEPT

















## DEBIAN

##pico /etc/locale.gen
##dpkg-reconfigure
gpg -a --export 381BA480 > jranke_cran.asc
echo deb http://cran.r-project.org/bin/linux/debian/ lenny-cran/ >> /etc/apt/sources.list 
sudo locale-gen








echo deb http://cran.r-project.org/bin/linux/debian/ lenny-cran/ >> /etc/apt/sources.list



gpg -a --export 381BA480 > jranke_cran.asc
apt-key add jranke_cran.asc
apt-get update
apt-get -t unstable install r-base r-base-dev
echo en_US.UTF-8 UTF-8 > /etc/locale.gen 
git clone git://github.com/leoniedu/CongressoAberto.git
mkdir reps
mv CongressoAberto reps/.
apt-get -t unstable install screen
apt-get -t unstable install openssh
apt-get -t unstable install ssh

$server=174.143.181.9
ssh-keygen -t dsa
scp ~/.ssh/id_dsa.pub root@$server:.ssh/authorized_keys2

pico /etc/sshd/sshd_config
/etc/init.d/ssh restart
pico /etc/sshd/sshd_config


apt-get install debconf
dpkg-reconfigure




install.packages(c("ggplot2", "car", "RMySQL", "XML", "wnominate", "pcsl", dep=TRUE)