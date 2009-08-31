发布"Fix rss feed"插件V1.0

插件名称：Fix rss feed插件功能：修复了当从http://www.feedburner.com烧录wordpress rss feed时发生的"Error on line 2: The processing instruction target matching "[xX][mM][lL]" is not allowed."错误，也修复在firefox中发生的"XML or text declaration not at start of entity"错误，还有在opera中发生的"XML declaration not at beginning of document"错误。

插件原理：出现上面所提到的错误，是因为rss feed前面有空行，而标准的rss feed是要求<xml>在最前面，造成有空行的原因是，有些wordpress插件或者主题模板中的php代码头部或者尾部有空行，wordpress在生成rss feed时候，会调用所用到的插件和主题，这样就出现了rss feed错误，详细情况大家可以看我的文章“怎样改正wordpress中rss feed的Error on line 2: The processing instruction target matching “[xX][mM][lL]” is not allowed错误?”。如果手工查找空行，工作量太大，还容易出错，所以我就编写了这个插件，减少大家的体力劳动，此插件就是将wordpress里面的所有的php文件（除了wp-admin和wp-includes目录）扫描一遍，发现头尾有空行的，就删除。

作者: flyaga li

版本: 1.03

发布日期：2009-05-24

作者网站: http://www.flyaga.info/

发布历史:
2008-12-30 发布v1.0
2009-02-04 发布v1.01, 修改些小错误, 增加了在php文件改变之前进行文件备份功能
2009-02-16 发布v1.02, 修改些小错误
2009-05-24 发布v1.03, 增加检测空行错误按钮,谢谢Wanda的建议

下载地址:
http://wordpress.org/extend/plugins/fix-rss-feed/
http://www.flyaga.info/blog/download/fix-rss-feed.rar


安装步骤：
1. 下载插件，解压缩，你将会看到一个文件夹fix-rss-feed，请确认文件夹里面没有二级目录，然后将其放置到插件目录下，插件目录通常是 'wp-content/plugins/'；
2. 在后台对应的插件管理页激活该插件；
3. 安装完成；

使用步骤：
1. 进入后台 admin->选项->fix rss feed
2. 点击"fix wordpress rss feed"按钮，将查找所有文件夹（除了wp-admin和wp-includes目录）中的php文件是否头部和尾部有空行，有则删除空行（请放心，不会删除文件中间的空行，只删除头部和尾部的空行，所以对你的php程序没有影响, 如果真的影响了你的php程序,可以将.bak改名成.php,就可以恢复你以前的php文件了）。
3. 修改完成后，最后会列出头部和尾部有空行的文件名称和修改状态，如果你的文件不可写，则提示错误，你可将文件改成可写后，再次点击"fix wordpress rss feed"按钮进行再次修复。
4. 全部修改完成后，你就会发现你的wordpress rss feed没有错误了，^_^。

卸载步骤：
1. 进入插件管理界面，取消该插件。
2. 卸载完成

FAQ:

1. 此插件是否会损坏php文件？
不用担心，此插件只删除php文件的头尾的空行，不会删除文件中间的空行，不会影响你的php程序的运行，放心好了，如果还是不放心的话，可以先将文件进行备份，再运行此插件进行修复。

2. 提示文件不可写，该怎么办？
如果你的系统是windows，则检查你的文件是否是只读，是的话，在资源管理器中右键单击文件，选属性，将只读属性取消，然后确认，你的文件就可以写了。
如果你的系统是linux，则用ftp或者winscp进入到你的服务器，找到文件，右键单击文件，选属性，将文件权限设置为777，然后确认，你的文件就可以写了。
如果是linux的ssh登录，则进入到服务器，找到文件，用chmod命令进行权限设定，格式是chmod 777 你的文件名，然后你的文件就可以写了。

屏幕截图:
http://www.flyaga.info/image/fix-rss-feed-screenshot.jpg