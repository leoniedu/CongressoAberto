import xmlrpclib
import sys
import datetime
import time
import MySQLdb
import re

## wordpress api login
user = 'admin'
passwd = '&A9V$nunJAFS'
url = 'http://congressoaberto.eduardoleoni.com/xmlrpc.php'
server = xmlrpclib.ServerProxy(url)
blog_id = 0


## default custom fields
custom_fields_default = [{"key": "disable_wpautop", "value" : "1"},
                         {"key": "disable_wptexturize", "value" : "1"},
                         {'key':"disable_convert_chars", "value" : "1"},
                         {"key": "disable_convert_smilies", "value" : "1"}]

db=MySQLdb.connect(read_default_file="~/.my.cnf",read_default_group="congressoaberto",use_unicode=True)

##db=MySQLdb.connect(user="monte",host="mysql.cluelessresearch.com",db="congressoaberto",passwd="e123456")
c=db.cursor()
## select deputies pages
# SELECT bioid FROM br_deputados_current
# SELECT post_content FROM wp_hufib7_posts where post_content like "bioid = 108355"

c.execute("""
SELECT ID,post_content FROM wp_hufib7_posts where post_content like "%legislator.php%" AND post_type='page'; 
""")
deps = c.fetchall()
## get the dep bioids
p = re.compile('.*bioid.=.([0-9]*).*', re.VERBOSE)
wpdeps = list(deps)
wpdepsBioid = range(len(wpdeps))
for i in wpdepsBioid:
    m = p.search(wpdeps[i][1])
    wpdepsBioid[i] = m.groups()[0]

## what are the current deps?
# c.execute("""
# SELECT bioid FROM br_deputados_current;
# """)
# All deps
c.execute("""
SELECT bioid FROM br_deputados_current;
""")
cdeps = c.fetchall()
cdepids = range(len(cdeps))
for i in cdepids :
    cdepids[i] = cdeps[i][0]
## FIX: custom_fields duplicated when updating

## df page id
# c.execute("""
# SELECT ID FROM wp_hufib7_posts where post_title=\"Deputados Federais\" AND post_type='page';""")
# df_pageid = c.fetchall()
# if len(df_pageid)==1 :
#     df_pageid=df_pageid[0][0]
# else :
#     ## FIX throw exception
#     print "stop"+1
    
    

## for all current deps currently not on wp
## post the page
## Fix: should do this for all deps (not just current)
rnow = range(len(cdepids))
for i in rnow :
    bioid = cdepids[i]
    if str(bioid) in wpdepsBioid :
        print bioid
    else:
        c.execute("""
            SELECT a.bioid, b.namelegis, a.state FROM br_deputados_current as a, br_bio as b where a.bioid=b.bioid and a.bioid=%s
            """,bioid)
        idname = c.fetchone()        
        c.execute("""
            SELECT ID FROM wp_hufib7_posts where post_title=%s and post_type='page'
        """,(idname[2].upper(),))
        parent_id = c.fetchone()[0] ## should look for the state here?
        blog_content = { 'title' : idname[1] , 
                         'description' : '<script language=\"php\">$bioid = '+str(bioid)+';include( TEMPLATEPATH . \'/legislator.php\');</script>'
                         ,'custom_fields' : custom_fields_default
                         ,"wp_page_parent_id" : parent_id
                         }
        server.wp.newPage(blog_id, user, passwd, blog_content,1)
        


    

# Legis
# <script language="php">
# $bioid = 104014;
# include( TEMPLATEPATH . '/legislator.php');
# </script>


# gmap
