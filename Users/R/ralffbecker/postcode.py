import re
import scraperwiki           
import lxml.html

def postcode_to_lsoa(postcode):
    import requests, json, urllib
    url = 'http://mapit.mysociety.org/postcode/' + urllib.quote(postcode)
    j = json.loads(requests.get(url).text)
    for k,v in j['areas'].iteritems():
        if v['type_name'] == 'Lower Layer Super Output Area (Generalised)':
            return {
                'name': v['name'],
                'code': v['codes']['ons']
            }
print postcode_to_lsoa('WA27NE')
print postcode_to_lsoa('ws12be')
print postcode_to_lsoa('DL36QZ')
print postcode_to_lsoa('hp219hl')
print postcode_to_lsoa('BN35BA')
print postcode_to_lsoa('GU101EN')
print postcode_to_lsoa('CH36BY')
print postcode_to_lsoa('RM124YH')
print postcode_to_lsoa('EX44LD')
print postcode_to_lsoa('DE150JT')
print postcode_to_lsoa('BH123AF')
print postcode_to_lsoa('St58ey')
print postcode_to_lsoa('OX263XF')
print postcode_to_lsoa('DY121PX')
print postcode_to_lsoa('Po196pe')
print postcode_to_lsoa('HP198WA')
print postcode_to_lsoa('nn169tg')
print postcode_to_lsoa('B742NW')
print postcode_to_lsoa('b258yg')
print postcode_to_lsoa('EX122HF')
print postcode_to_lsoa('NE18QB')
print postcode_to_lsoa('TQ58AA')
print postcode_to_lsoa('cm16ss')
print postcode_to_lsoa('BS354NP')
print postcode_to_lsoa('KT151EQ')
print postcode_to_lsoa('TA66SH')
print postcode_to_lsoa('CM132PY')
print postcode_to_lsoa('GL143DE')
print postcode_to_lsoa('Se193pu')
print postcode_to_lsoa('SE173RX')
print postcode_to_lsoa('SG20QW')
print postcode_to_lsoa('GL513RL')
print postcode_to_lsoa('NW109LJ')
print postcode_to_lsoa('E48EF')
print postcode_to_lsoa('LA145NB')
print postcode_to_lsoa('SR68JJ')
print postcode_to_lsoa('pl253bt')
print postcode_to_lsoa('CT13AU')
print postcode_to_lsoa('Ex68eq')
print postcode_to_lsoa('BD229DL')
print postcode_to_lsoa('tf42fs')
print postcode_to_lsoa('rm65ju')
print postcode_to_lsoa('gu149xa')
print postcode_to_lsoa('S352YJ')
print postcode_to_lsoa('b988qf')
print postcode_to_lsoa('SG120EU')
print postcode_to_lsoa('SE264RE')
print postcode_to_lsoa('E125DL')
print postcode_to_lsoa('rh16aj')
print postcode_to_lsoa('BS251SL')
print postcode_to_lsoa('Rg93jy')
print postcode_to_lsoa('ss69ts')
print postcode_to_lsoa('BS66BT')
print postcode_to_lsoa('BH49BT')
print postcode_to_lsoa('SS93BG')
print postcode_to_lsoa('NG147EE')
print postcode_to_lsoa('B632ED')
print postcode_to_lsoa('WF50RJ')
print postcode_to_lsoa('GL54DF')
print postcode_to_lsoa('S434ud')
print postcode_to_lsoa('CM219RB')
print postcode_to_lsoa('DL41NF')
print postcode_to_lsoa('HU128RN')
print postcode_to_lsoa('SY14QP')
print postcode_to_lsoa('B295RY')
print postcode_to_lsoa('KT151UJ')
print postcode_to_lsoa('SE232SS')
print postcode_to_lsoa('NE215GD')
print postcode_to_lsoa('PE321HN')
print postcode_to_lsoa('DT118JN')
print postcode_to_lsoa('CV31EF')
print postcode_to_lsoa('al40ab')
print postcode_to_lsoa('LE184PX')
print postcode_to_lsoa('HR49NE')
print postcode_to_lsoa('AL15DH')
print postcode_to_lsoa('RG145LA')
print postcode_to_lsoa('EX25RH')
print postcode_to_lsoa('RH161LA')
print postcode_to_lsoa('bh189np')
print postcode_to_lsoa('GL53JS')
print postcode_to_lsoa('BH118TG')
print postcode_to_lsoa('DY14BS')
print postcode_to_lsoa('BN29NW')
print postcode_to_lsoa('ST47HG')
print postcode_to_lsoa('Rg64ee')
print postcode_to_lsoa('SS69TS')
print postcode_to_lsoa('Se41xy')
print postcode_to_lsoa('st74ut')
print postcode_to_lsoa('mk160jy')
print postcode_to_lsoa('LE183WN')
print postcode_to_lsoa('rh203ns')
print postcode_to_lsoa('PE275JJ')
print postcode_to_lsoa('Dt65rd')
print postcode_to_lsoa('TA12PZ')
print postcode_to_lsoa('pe297LJ')
print postcode_to_lsoa('Ba34nf')
print postcode_to_lsoa('BB44BW')
print postcode_to_lsoa('ST72PZ')
print postcode_to_lsoa('tn49ln')
print postcode_to_lsoa('KT212LF')
print postcode_to_lsoa('RG17UR')
print postcode_to_lsoa('WS66HF')
print postcode_to_lsoa('Po369lx')
print postcode_to_lsoa('LN12FF')
print postcode_to_lsoa('BN238AQ')
print postcode_to_lsoa('bn159se')
print postcode_to_lsoa('ST74JU')
print postcode_to_lsoa('CT162PG')
print postcode_to_lsoa('CM34RL')
print postcode_to_lsoa('SS00SS')
print postcode_to_lsoa('RG215SJ')
print postcode_to_lsoa('de241ap')
print postcode_to_lsoa('ba213ew')
print postcode_to_lsoa('SO224QQ')
print postcode_to_lsoa('EX83AE')
print postcode_to_lsoa('S446TN')
print postcode_to_lsoa('po377dx')
print postcode_to_lsoa('Gl528nw')
print postcode_to_lsoa('CH54ZB')
print postcode_to_lsoa('ub109ah')
print postcode_to_lsoa('PL276AE')
print postcode_to_lsoa('rh69ja')
print postcode_to_lsoa('CW124NU')
print postcode_to_lsoa('BN253EY')
print postcode_to_lsoa('WS87SA')
print postcode_to_lsoa('GU314QE')
print postcode_to_lsoa('sk230qb')
print postcode_to_lsoa('De656fh')
print postcode_to_lsoa('BN253QL')
print postcode_to_lsoa('Se266ut')
print postcode_to_lsoa('se12bx')
print postcode_to_lsoa('Sg188dz')
print postcode_to_lsoa('da175lu')
print postcode_to_lsoa('CV12DS')
print postcode_to_lsoa('NG229TE')
print postcode_to_lsoa('CM194Pj')
print postcode_to_lsoa('Se37td')
print postcode_to_lsoa('LS297RT')
print postcode_to_lsoa('sl25rn')
print postcode_to_lsoa('NW37BB')
print postcode_to_lsoa('DA74AE')
print postcode_to_lsoa('Hu179ut')
print postcode_to_lsoa('e176ae')
print postcode_to_lsoa('rm94ea')
print postcode_to_lsoa('LA14NR')
print postcode_to_lsoa('KT70QY')
print postcode_to_lsoa('WS42BW')
print postcode_to_lsoa('nw63ea')
print postcode_to_lsoa('N153HL')
print postcode_to_lsoa('IP30DU')
print postcode_to_lsoa('BD208BD')
print postcode_to_lsoa('S639AU')
print postcode_to_lsoa('SE232SH')
print postcode_to_lsoa('ls213hu')
print postcode_to_lsoa('sw24bg')
print postcode_to_lsoa('b675qp')
print postcode_to_lsoa('RG170BN')
print postcode_to_lsoa('TQ97ER')
print postcode_to_lsoa('NE23BY')
print postcode_to_lsoa('LS98LJ')
print postcode_to_lsoa('s63rl')
print postcode_to_lsoa('e34xa')
print postcode_to_lsoa('BB72PW')
print postcode_to_lsoa('SE85DP')
print postcode_to_lsoa('RM83RU')
print postcode_to_lsoa('SN110LG')
print postcode_to_lsoa('SE232PS')
print postcode_to_lsoa('BH234LW')
print postcode_to_lsoa('NE34AQ')
print postcode_to_lsoa('PR18HX')
print postcode_to_lsoa('Pr77bf')
print postcode_to_lsoa('Rg73qa')
print postcode_to_lsoa('TQ139QT')
print postcode_to_lsoa('N65NJ')
print postcode_to_lsoa('ME13QE')
print postcode_to_lsoa('bn208ds')
print postcode_to_lsoa('WN49EY')
print postcode_to_lsoa('WR143PB')
print postcode_to_lsoa('TN341EN')
print postcode_to_lsoa('cv61ly')
print postcode_to_lsoa('BR15JJ')
print postcode_to_lsoa('SW97TA')
print postcode_to_lsoa('BH211UJ')
print postcode_to_lsoa('LE126LD')
print postcode_to_lsoa('Bd59qe')
print postcode_to_lsoa('AL55AT')
print postcode_to_lsoa('OX93TF')
print postcode_to_lsoa('ex12nf')
print postcode_to_lsoa('cw72bb')
print postcode_to_lsoa('NG81BG')
print postcode_to_lsoa('Rg413dt')
print postcode_to_lsoa('tn92sd')
print postcode_to_lsoa('Po107rl')
print postcode_to_lsoa('SE249BA')
print postcode_to_lsoa('WA110JH')
print postcode_to_lsoa('KT64AH')
print postcode_to_lsoa('SE229DJ')
print postcode_to_lsoa('ME101DB')
print postcode_to_lsoa('Se145nr')
print postcode_to_lsoa('SG85HQ')
print postcode_to_lsoa('SE14NF')
print postcode_to_lsoa('rm124jf')
print postcode_to_lsoa('Tw170an')
print postcode_to_lsoa('M502EQ')
print postcode_to_lsoa('B927BX')
print postcode_to_lsoa('WD33ED')
print postcode_to_lsoa('N64HP')
print postcode_to_lsoa('TS233YB')
print postcode_to_lsoa('B604LX')
print postcode_to_lsoa('sr79py')
print postcode_to_lsoa('pr253zj')
print postcode_to_lsoa('sw128ju')
print postcode_to_lsoa('kt147qt')
print postcode_to_lsoa('WN49EY')
print postcode_to_lsoa('RG304HA')
print postcode_to_lsoa('B461BN')
print postcode_to_lsoa('SW1W8SW')
print postcode_to_lsoa('NG124BX')
print postcode_to_lsoa('NN71JX')
print postcode_to_lsoa('SE233XD')
print postcode_to_lsoa('SE240AX')
print postcode_to_lsoa('IG95PD')
print postcode_to_lsoa('tn393ah')
print postcode_to_lsoa('B743FE')
print postcode_to_lsoa('cv327as')
print postcode_to_lsoa('BL00HH')
print postcode_to_lsoa('ME174HN')
print postcode_to_lsoa('SO173TN')
print postcode_to_lsoa('S614PD')
print postcode_to_lsoa('EX174SW')
print postcode_to_lsoa('rg238ax')
print postcode_to_lsoa('YO179DP')
print postcode_to_lsoa('SW183NZ')
print postcode_to_lsoa('DA124SA')
print postcode_to_lsoa('W55EZ')
print postcode_to_lsoa('BR88DH')
print postcode_to_lsoa('SG13XX')
print postcode_to_lsoa('Wf170eb')
print postcode_to_lsoa('St216jw')
print postcode_to_lsoa('DH85BG')
print postcode_to_lsoa('PE292DR')
print postcode_to_lsoa('b322rx')
print postcode_to_lsoa('LS157PL')
print postcode_to_lsoa('SW112BJ')
print postcode_to_lsoa('LE128QH')
print postcode_to_lsoa('NW65SL')
print postcode_to_lsoa('B903JE')
print postcode_to_lsoa('RM191SE')
print postcode_to_lsoa('YO126UH')
print postcode_to_lsoa('sw178ln')
print postcode_to_lsoa('tw153de')
print postcode_to_lsoa('Wa53rz')
print postcode_to_lsoa('RH105DW')
print postcode_to_lsoa('SW21AZ')
print postcode_to_lsoa('B330HS')
print postcode_to_lsoa('SO206DP')
print postcode_to_lsoa('Ne77px')
print postcode_to_lsoa('B330HS')
print postcode_to_lsoa('SE152TU')
print postcode_to_lsoa('DE143LJ')
print postcode_to_lsoa('E47dx')
print postcode_to_lsoa('Ha13rt')
print postcode_to_lsoa('NE270BE')
print postcode_to_lsoa('BB30SX')
print postcode_to_lsoa('sw49pj')
print postcode_to_lsoa('ch76tn')
print postcode_to_lsoa('SP119PD')
print postcode_to_lsoa('SY36DR')
print postcode_to_lsoa('EN27BP')
print postcode_to_lsoa('MK418HP')
print postcode_to_lsoa('La12hw')
print postcode_to_lsoa('rh12bl')
print postcode_to_lsoa('DY102SX')
print postcode_to_lsoa('LE112HF')
print postcode_to_lsoa('CV59QG')
print postcode_to_lsoa('BN84PL')
print postcode_to_lsoa('SE116LJ')
print postcode_to_lsoa('CH637QS')
print postcode_to_lsoa('DA74RW')
print postcode_to_lsoa('se18pu')
print postcode_to_lsoa('Dl167ab')
print postcode_to_lsoa('TW123YT')
print postcode_to_lsoa('PL268AH')
print postcode_to_lsoa('HP111LP')
print postcode_to_lsoa('SE256JH')
print postcode_to_lsoa('UB100HU')
print postcode_to_lsoa('HG59LZ')
print postcode_to_lsoa('s359wj')
print postcode_to_lsoa('Ln12hn')
print postcode_to_lsoa('PO88DE')
print postcode_to_lsoa('IP52DQ')
print postcode_to_lsoa('yo323na')
print postcode_to_lsoa('IP143AH')
print postcode_to_lsoa('b721ah')
print postcode_to_lsoa('Se120uj')
print postcode_to_lsoa('CB246EF')
print postcode_to_lsoa('rh138bw')
print postcode_to_lsoa('B911QE')
print postcode_to_lsoa('Tn92lu')
print postcode_to_lsoa('KT130PF')
print postcode_to_lsoa('cm14az')
print postcode_to_lsoa('mk42jf')
print postcode_to_lsoa('M55AB')
print postcode_to_lsoa('WF58EG')
print postcode_to_lsoa('N42su')
print postcode_to_lsoa('Cv78dd')
print postcode_to_lsoa('cw124ub')
print postcode_to_lsoa('CO45DU')
print postcode_to_lsoa('Hu80lr')
print postcode_to_lsoa('Hu80lr')
print postcode_to_lsoa('TN56BZ')
print postcode_to_lsoa('HA53ES')
print postcode_to_lsoa('tn355jr')
print postcode_to_lsoa('bs494ju')
print postcode_to_lsoa('Se193RB')
print postcode_to_lsoa('SG20QW')
print postcode_to_lsoa('M335GS')
print postcode_to_lsoa('SK138QS')
print postcode_to_lsoa('AL11PD')
print postcode_to_lsoa('SO507NR')
print postcode_to_lsoa('sw193lr')
print postcode_to_lsoa('Tw11bx')
print postcode_to_lsoa('IP117PA')
print postcode_to_lsoa('TW122NG')
print postcode_to_lsoa('KT130PF')
print postcode_to_lsoa('so167hq')
print postcode_to_lsoa('rh139gr')
print postcode_to_lsoa('BR12PQ')
print postcode_to_lsoa('rg224re')
print postcode_to_lsoa('n52uz')
print postcode_to_lsoa('PO91RR')
print postcode_to_lsoa('SK132BE')
print postcode_to_lsoa('TA116PH')
print postcode_to_lsoa('L98DP')
print postcode_to_lsoa('RH107LW')
print postcode_to_lsoa('AL54TP')
print postcode_to_lsoa('B939BQ')
print postcode_to_lsoa('Bs251sl')
print postcode_to_lsoa('RH150TG')
print postcode_to_lsoa('NR180DB')
print postcode_to_lsoa('kt190ng')
print postcode_to_lsoa('L137HU')
print postcode_to_lsoa('ST31UF')
print postcode_to_lsoa('dy101sh')
print postcode_to_lsoa('BS251SL')
print postcode_to_lsoa('tw167tg')
print postcode_to_lsoa('GU220AE')
print postcode_to_lsoa('s188qx')
print postcode_to_lsoa('NN126TD')
print postcode_to_lsoa('PE25LA')
print postcode_to_lsoa('TQ47BH')
print postcode_to_lsoa('MK451UA')
print postcode_to_lsoa('ws109ha')
print postcode_to_lsoa('ha20pf')
print postcode_to_lsoa('NW71GF')
print postcode_to_lsoa('TN218YH')
print postcode_to_lsoa('SW166ru')
