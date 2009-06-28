## First do the unix account setup http://wiki.dreamhost.com/Unix_account_setup
cd ~/soft
curl -O ftp://ftp.gnu.org/gnu/readline/readline-6.tar.gz
tar xzvf readline-6.tar.gz
cd readline-6
./configure --prefix=$HOME/run
make
make install
cd ~/soft
wget http://cran.r-project.org/src/base-prerelease/R-2.9.1.gz
tar xvzf R-2.9.1.tar.gz
cd R-2.9.1
./configure --prefix=/home/leoniedu/run  --with-x=no LDFLAGS=-L/home/leoniedu/run/lib
make
make install