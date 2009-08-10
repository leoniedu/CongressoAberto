## First do the unix account setup http://wiki.dreamhost.com/Unix_account_setup
cd ~/soft
curl -O ftp://ftp.gnu.org/gnu/readline/readline-6.tar.gz
tar xzvf readline-6.tar.gz
cd readline-6
./configure --prefix=$HOME/run
make
make install
cd ~/soft
wget http://cran.r-project.org/src/base/R-2/R-2.9.1.tar.gz
tar xvzf R-2.9.1.tar.gz
cd R-2.9.1
./configure --prefix=$HOME/run  --with-x=no LDFLAGS=-L/home/leoniedu/run/lib
make
make install



##install RCurl
## first install curl
cd ~/soft
wget http://curl.haxx.se/download/curl-7.19.5.tar.gz
tar -vxzf curl-7.19.5.tar.gz 
cd curl-7.19.5
./configure --prefix=$HOME/run  LDFLAGS=-L/home/leoniedu/run/lib
make
make install
## now RCurl
cd ~/soft
wget http://cran.r-project.org/src/contrib/RCurl_0.98-1.tar.gz
R CMD INSTALL RCurl_0.98-1.tar.gz --configure-args='exec_prefix=/home/leoniedu/run'


##Install Python
cd ~/soft
wget http://www.python.org/ftp/python/2.5.1/Python-2.5.1.tgz
tar xvzf Python-2.5.1.tgz
cd Python-2.5.1
./configure --prefix=$HOME/run/ --enable-unicode=ucs4
make
make install

## install python mysql package
easy_install MySQL_python



