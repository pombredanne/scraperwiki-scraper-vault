import scraperwiki
from lxml.html import parse
import urllib
from unidecode import unidecode

# Blank Python
url_base = "http://www.zap.com.br/imoveis/brasil/?q="
enderecos = ['Rua Decio Vilares']

enderecos = ['Rua Decio Vilares']

enderecos = ['Rua Decio Vilares']

def scrapeia(url):
    soup = parse(url).getroot()
    resultados = soup.cssselect(".item.itemOf")
    
    for r in resultados:
        data = {}
        data['url'] = r.cssselect('.valorOferta')[0].get('href')
        data['bairro'] = r.cssselect('h3 a')[0].text_content()
        data['endereco'] = r.cssselect('h3 a')[3].text_content()
        caracteristicas = r.cssselect('.labelCar')
        for c in caracteristicas:
            data[unidecode(c.text)] = c.tail
        data['preco'] = r.cssselect('.valorOferta')[0].text_content()
        scraperwiki.sqlite.save(['url'], data)

for endereco in enderecos:
    url = url_base + urllib.quote_plus(unidecode(endereco).lower() + " rio de janeiro rj")
    try:
        scrapeia(url)
    except IOError:
        print url + ' url com problemas'