## unix account setup http://wiki.dreamhost.com/Unix_account_setup
wget http://cran.r-project.org/src/base/R-2/R-2.9.0.tar.gz
tar xvzf R-2.9.0.tar.gz
cd R-2.9.0
./configure --prefix=/home/leoniedu/run --with-readline=no --with-x=no
##There is a problem in the make file http://tolstoy.newcastle.edu.au/R/e6/devel/09/04/1434.html substitute the following in src/modules/Makefile
# @if test "$(R_MODULES)"	!= ""; then \
#                 for d in "$(R_MODULES)"; do \
#                   (cd $${d} && $(MAKE) $@) || exit 1; \
#                 done; \ 
# 	fi
make
make install