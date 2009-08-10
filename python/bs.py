import urllib2
from BeautifulSoup import BeautifulSoup 
page = urllib2.urlopen("http://www.camara.gov.br/sileg/Prop_Lista.asp?Sigla=MPV&Numero=447&Ano=2008")
soup = BeautifulSoup(page)
soup.contents[3].contents[0]
soup.findAll('p', align="center")
