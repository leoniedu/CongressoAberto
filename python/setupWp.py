## FIX: custom_fields duplicated when updating


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
c=db.cursor()



c.execute("""
            SELECT ID FROM wp_hufib7_posts where post_title=\"Deputados Federais\"
        """)
df_pageid = c.fetchall()
blog_content = { 'title' : 'Deputados Federais' , 
                 'description' : '<ul><?php global $post;$thePostID = $post->ID;wp_list_pages( \'child_of=\'.$thePostID.\'&title_li=\'); ?></ul>'
                 ,'custom_fields' : custom_fields_default
                 }
if len(df_pageid)==0 :
    # Deputado Federal page
    df_pageid = server.wp.newPage(blog_id, user, passwd, blog_content,1)
else :
    df_pageid = df_pageid[0][0]
    server.metaWeblog.editPost(df_pageid, user, passwd, blog_content,1)
        




## create state pages
states=('AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO')
for i in states:
    c.execute("""
            SELECT ID FROM wp_hufib7_posts where post_title=%s and post_type='page'
        """,(i,))
    pid = c.fetchall()            
    blog_content = { 'title' : i , 
                     'description' : '<ul><?php global $post;$thePostID = $post->ID;wp_list_pages( \'child_of=\'.$thePostID.\'&title_li=\'); ?></ul>'
                     ,'custom_fields' : custom_fields_default
                     ,'wp_page_parent_id' : df_pageid
                     }
    if len(pid)==0 :
        pid = server.wp.newPage(blog_id, user, passwd, blog_content,1)
    else :
        pid = pid[0][0]
        server.metaWeblog.editPost(pid, user, passwd, blog_content,1)
