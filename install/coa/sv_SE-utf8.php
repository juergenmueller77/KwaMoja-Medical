<?php
InsertRecord('accountsection',array('sectionid'),array(10),array('sectionid','sectionname'),array(10,'Tillg'));
InsertRecord('accountsection',array('sectionid'),array(20),array('sectionid','sectionname'),array(20,'Skulder'));
InsertRecord('accountsection',array('sectionid'),array(30),array('sectionid','sectionname'),array(30,'Inkomst'));
InsertRecord('accountsection',array('sectionid'),array(40),array('sectionid','sectionname'),array(40,'Kostnad'));
InsertRecord('accountgroups',array('groupname'),array('Byggnader'),array('groupname','sectioninaccounts','pandl','sequenceintb','parentgroupname'),array('Byggnader','10','0','11000',''));
InsertRecord('accountgroups',array('groupname'),array('Eget kapital'),array('groupname','sectioninaccounts','pandl','sequenceintb','parentgroupname'),array('Eget kapital','20','0','20000',''));
InsertRecord('accountgroups',array('groupname'),array('Eget kapital, oskattat'),array('groupname','sectioninaccounts','pandl','sequenceintb','parentgroupname'),array('Eget kapital, oskattat','20','0','21000',''));
InsertRecord('accountgroups',array('groupname'),array('F'),array('groupname','sectioninaccounts','pandl','sequenceintb','parentgroupname'),array('F','30','1','30000',''));
InsertRecord('accountgroups',array('groupname'),array('Kassa/Bank'),array('groupname','sectioninaccounts','pandl','sequenceintb','parentgroupname'),array('Kassa/Bank','10','0','19000',''));
InsertRecord('accountgroups',array('groupname'),array('Kortfristiga fordringar'),array('groupname','sectioninaccounts','pandl','sequenceintb','parentgroupname'),array('Kortfristiga fordringar','10','0','15000',''));
InsertRecord('accountgroups',array('groupname'),array('Kortfristiga skulder'),array('groupname','sectioninaccounts','pandl','sequenceintb','parentgroupname'),array('Kortfristiga skulder','20','0','24000',''));
InsertRecord('accountgroups',array('groupname'),array('Kostnader'),array('groupname','sectioninaccounts','pandl','sequenceintb','parentgroupname'),array('Kostnader','40','1','32767',''));
InsertRecord('accountgroups',array('groupname'),array('L'),array('groupname','sectioninaccounts','pandl','sequenceintb','parentgroupname'),array('L','20','0','22000',''));
InsertRecord('accountgroups',array('groupname'),array('Maskiner/Inventarier'),array('groupname','sectioninaccounts','pandl','sequenceintb','parentgroupname'),array('Maskiner/Inventarier','10','0','12000',''));
InsertRecord('accountgroups',array('groupname'),array('Varuink'),array('groupname','sectioninaccounts','pandl','sequenceintb','parentgroupname'),array('Varuink','40','1','32767',''));
InsertRecord('accountgroups',array('groupname'),array('Varulager'),array('groupname','sectioninaccounts','pandl','sequenceintb','parentgroupname'),array('Varulager','10','0','14000',''));
InsertRecord('chartmaster',array('accountcaode'),array('1110'),array('accountcode','accountname','group_'),array('1110','Byggnader','Byggnader'));
InsertRecord('chartmaster',array('accountcaode'),array('1119'),array('accountcode','accountname','group_'),array('1119','Ack avskr byggnader','Byggnader'));
InsertRecord('chartmaster',array('accountcaode'),array('1210'),array('accountcode','accountname','group_'),array('1210','Maskiner och andra tekn anl','Maskiner/Inventarier'));
InsertRecord('chartmaster',array('accountcaode'),array('1219'),array('accountcode','accountname','group_'),array('1219','Ack avskr mast/andra tekn anl','Maskiner/Inventarier'));
InsertRecord('chartmaster',array('accountcaode'),array('1220'),array('accountcode','accountname','group_'),array('1220','Inventarier och verktyg','Maskiner/Inventarier'));
InsertRecord('chartmaster',array('accountcaode'),array('1229'),array('accountcode','accountname','group_'),array('1229','Ack avskr invent och verktyg','Maskiner/Inventarier'));
InsertRecord('chartmaster',array('accountcaode'),array('1240'),array('accountcode','accountname','group_'),array('1240','Bilar och andra transportmedel','Maskiner/Inventarier'));
InsertRecord('chartmaster',array('accountcaode'),array('1249'),array('accountcode','accountname','group_'),array('1249','Ack avskr bilar o andra transp','Maskiner/Inventarier'));
InsertRecord('chartmaster',array('accountcaode'),array('1291'),array('accountcode','accountname','group_'),array('1291','Konst och liknande tillg','Maskiner/Inventarier'));
InsertRecord('chartmaster',array('accountcaode'),array('1350'),array('accountcode','accountname','group_'),array('1350','Aktier, andelar och v','Maskiner/Inventarier'));
InsertRecord('chartmaster',array('accountcaode'),array('1380'),array('accountcode','accountname','group_'),array('1380','Andra l','Maskiner/Inventarier'));
InsertRecord('chartmaster',array('accountcaode'),array('1400'),array('accountcode','accountname','group_'),array('1400','Lager','Varulager'));
InsertRecord('chartmaster',array('accountcaode'),array('1470'),array('accountcode','accountname','group_'),array('1470','P','Varulager'));
InsertRecord('chartmaster',array('accountcaode'),array('1490'),array('accountcode','accountname','group_'),array('1490','F','Varulager'));
InsertRecord('chartmaster',array('accountcaode'),array('1510'),array('accountcode','accountname','group_'),array('1510','Kundfordringar','Kortfristiga fordringar'));
InsertRecord('chartmaster',array('accountcaode'),array('1515'),array('accountcode','accountname','group_'),array('1515','Os','Kortfristiga fordringar'));
InsertRecord('chartmaster',array('accountcaode'),array('1518'),array('accountcode','accountname','group_'),array('1518','Ej reskontraf','Kortfristiga fordringar'));
InsertRecord('chartmaster',array('accountcaode'),array('1610'),array('accountcode','accountname','group_'),array('1610','Fordringar hos anst','Kortfristiga fordringar'));
InsertRecord('chartmaster',array('accountcaode'),array('1640'),array('accountcode','accountname','group_'),array('1640','Skattefordringar','Kortfristiga fordringar'));
InsertRecord('chartmaster',array('accountcaode'),array('1650'),array('accountcode','accountname','group_'),array('1650','Momsfordran','Kortfristiga fordringar'));
InsertRecord('chartmaster',array('accountcaode'),array('1680'),array('accountcode','accountname','group_'),array('1680','Andra kortfristiga fordringar','Kortfristiga fordringar'));
InsertRecord('chartmaster',array('accountcaode'),array('1690'),array('accountcode','accountname','group_'),array('1690','V','Kortfristiga fordringar'));
InsertRecord('chartmaster',array('accountcaode'),array('1700'),array('accountcode','accountname','group_'),array('1700','F','Kortfristiga fordringar'));
InsertRecord('chartmaster',array('accountcaode'),array('1800'),array('accountcode','accountname','group_'),array('1800','Kortfristiga placeringar','Kortfristiga fordringar'));
InsertRecord('chartmaster',array('accountcaode'),array('1890'),array('accountcode','accountname','group_'),array('1890','V','Kortfristiga fordringar'));
InsertRecord('chartmaster',array('accountcaode'),array('1910'),array('accountcode','accountname','group_'),array('1910','Kassa','Kassa/Bank'));
InsertRecord('chartmaster',array('accountcaode'),array('1920'),array('accountcode','accountname','group_'),array('1920','Plusgiro','Kassa/Bank'));
InsertRecord('chartmaster',array('accountcaode'),array('1930'),array('accountcode','accountname','group_'),array('1930','Bankkonto','Kassa/Bank'));
InsertRecord('chartmaster',array('accountcaode'),array('1940'),array('accountcode','accountname','group_'),array('1940','Bank (','Kassa/Bank'));
InsertRecord('chartmaster',array('accountcaode'),array('2010'),array('accountcode','accountname','group_'),array('2010','Eget kapital,','Eget kapital'));
InsertRecord('chartmaster',array('accountcaode'),array('2020'),array('accountcode','accountname','group_'),array('2020','Eget kapital, del','Eget kapital'));
InsertRecord('chartmaster',array('accountcaode'),array('2050'),array('accountcode','accountname','group_'),array('2050','Avs','Eget kapital'));
InsertRecord('chartmaster',array('accountcaode'),array('2081'),array('accountcode','accountname','group_'),array('2081','Aktiekapital','Eget kapital'));
InsertRecord('chartmaster',array('accountcaode'),array('2086'),array('accountcode','accountname','group_'),array('2086','Reservfond','Eget kapital'));
InsertRecord('chartmaster',array('accountcaode'),array('2091'),array('accountcode','accountname','group_'),array('2091','Balanserad vinst eller f','Eget kapital'));
InsertRecord('chartmaster',array('accountcaode'),array('2098'),array('accountcode','accountname','group_'),array('2098','Vinst eller f','Eget kapital'));
InsertRecord('chartmaster',array('accountcaode'),array('2099'),array('accountcode','accountname','group_'),array('2099','','Eget kapital'));
InsertRecord('chartmaster',array('accountcaode'),array('2110'),array('accountcode','accountname','group_'),array('2110','Periodiseringsfonder','Eget kapital, oskattat'));
InsertRecord('chartmaster',array('accountcaode'),array('2125'),array('accountcode','accountname','group_'),array('2125','Periodiseringsfond 2005','Eget kapital, oskattat'));
InsertRecord('chartmaster',array('accountcaode'),array('2126'),array('accountcode','accountname','group_'),array('2126','Periodiseringsfond 2006','Eget kapital, oskattat'));
InsertRecord('chartmaster',array('accountcaode'),array('2127'),array('accountcode','accountname','group_'),array('2127','Periodiseringsfond 2007','Eget kapital, oskattat'));
InsertRecord('chartmaster',array('accountcaode'),array('2128'),array('accountcode','accountname','group_'),array('2128','Periodiseringsfond 2008','Eget kapital, oskattat'));
InsertRecord('chartmaster',array('accountcaode'),array('2150'),array('accountcode','accountname','group_'),array('2150','Ackumulerade','Eget kapital, oskattat'));
InsertRecord('chartmaster',array('accountcaode'),array('2199'),array('accountcode','accountname','group_'),array('2199','','Eget kapital, oskattat'));
InsertRecord('chartmaster',array('accountcaode'),array('2220'),array('accountcode','accountname','group_'),array('2220','Avs','L'));
InsertRecord('chartmaster',array('accountcaode'),array('2290'),array('accountcode','accountname','group_'),array('2290','','L'));
InsertRecord('chartmaster',array('accountcaode'),array('2330'),array('accountcode','accountname','group_'),array('2330','Checkr','L'));
InsertRecord('chartmaster',array('accountcaode'),array('2350'),array('accountcode','accountname','group_'),array('2350','Andra skulder till kreditinstitut','L'));
InsertRecord('chartmaster',array('accountcaode'),array('2393'),array('accountcode','accountname','group_'),array('2393','L','L'));
InsertRecord('chartmaster',array('accountcaode'),array('2399'),array('accountcode','accountname','group_'),array('2399','','L'));
InsertRecord('chartmaster',array('accountcaode'),array('2410'),array('accountcode','accountname','group_'),array('2410','Kortfr l','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2420'),array('accountcode','accountname','group_'),array('2420','F','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2430'),array('accountcode','accountname','group_'),array('2430','P','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2440'),array('accountcode','accountname','group_'),array('2440','Leverant','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2448'),array('accountcode','accountname','group_'),array('2448','Ej reskontraf','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2490'),array('accountcode','accountname','group_'),array('2490','','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2510'),array('accountcode','accountname','group_'),array('2510','Skatteskulder','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2610'),array('accountcode','accountname','group_'),array('2610','Utg','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2615'),array('accountcode','accountname','group_'),array('2615','Utg','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2616'),array('accountcode','accountname','group_'),array('2616','Utg','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2620'),array('accountcode','accountname','group_'),array('2620','Utg','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2630'),array('accountcode','accountname','group_'),array('2630','Utg','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2640'),array('accountcode','accountname','group_'),array('2640','Ing','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2645'),array('accountcode','accountname','group_'),array('2645','Ing','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2650'),array('accountcode','accountname','group_'),array('2650','Redovisningskonto f','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2710'),array('accountcode','accountname','group_'),array('2710','Personalens k','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2720'),array('accountcode','accountname','group_'),array('2720','Personalens kvarskatt','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2730'),array('accountcode','accountname','group_'),array('2730','Lagst sociala avg och l','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2740'),array('accountcode','accountname','group_'),array('2740','Avtalade sociala avgifter','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2760'),array('accountcode','accountname','group_'),array('2760','Semesterkassa','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2790'),array('accountcode','accountname','group_'),array('2790','','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2840'),array('accountcode','accountname','group_'),array('2840','Kortfristiga l','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2890'),array('accountcode','accountname','group_'),array('2890','','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('2900'),array('accountcode','accountname','group_'),array('2900','Uppl kostnader, f','Kortfristiga skulder'));
InsertRecord('chartmaster',array('accountcaode'),array('3011'),array('accountcode','accountname','group_'),array('3011','F','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3015'),array('accountcode','accountname','group_'),array('3015','F','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3016'),array('accountcode','accountname','group_'),array('3016','F','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3500'),array('accountcode','accountname','group_'),array('3500','Fakturerade kostnader','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3520'),array('accountcode','accountname','group_'),array('3520','Fakturerade frakter','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3600'),array('accountcode','accountname','group_'),array('3600','R','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3700'),array('accountcode','accountname','group_'),array('3700','Int','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3740'),array('accountcode','accountname','group_'),array('3740','','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3800'),array('accountcode','accountname','group_'),array('3800','Aktiv arbete f','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3910'),array('accountcode','accountname','group_'),array('3910','Hyres- och arrendeint','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3950'),array('accountcode','accountname','group_'),array('3950','','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3960'),array('accountcode','accountname','group_'),array('3960','Valkursvinst fordr/skuld av r','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3970'),array('accountcode','accountname','group_'),array('3970','Vinst avyttr imm o mat anl tillg','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3980'),array('accountcode','accountname','group_'),array('3980','Erh','F'));
InsertRecord('chartmaster',array('accountcaode'),array('3990'),array('accountcode','accountname','group_'),array('3990','','F'));
InsertRecord('chartmaster',array('accountcaode'),array('4010'),array('accountcode','accountname','group_'),array('4010','Ink','Varuink'));
InsertRecord('chartmaster',array('accountcaode'),array('4050'),array('accountcode','accountname','group_'),array('4050','Ink','Varuink'));
InsertRecord('chartmaster',array('accountcaode'),array('4600'),array('accountcode','accountname','group_'),array('4600','Legoarbeten och underentrepren','Varuink'));
InsertRecord('chartmaster',array('accountcaode'),array('4700'),array('accountcode','accountname','group_'),array('4700','Reduktion av ink','Varuink'));
InsertRecord('chartmaster',array('accountcaode'),array('4900'),array('accountcode','accountname','group_'),array('4900','F','Varuink'));
InsertRecord('chartmaster',array('accountcaode'),array('4970'),array('accountcode','accountname','group_'),array('4970','F','Varuink'));
InsertRecord('chartmaster',array('accountcaode'),array('5000'),array('accountcode','accountname','group_'),array('5000','Lokalkostnader (gruppkonto)','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('5100'),array('accountcode','accountname','group_'),array('5100','Fastighetskostnader','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('5200'),array('accountcode','accountname','group_'),array('5200','Hyra av anl','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('5300'),array('accountcode','accountname','group_'),array('5300','Energikostnader','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('5400'),array('accountcode','accountname','group_'),array('5400','F','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('5500'),array('accountcode','accountname','group_'),array('5500','Reparation och underh','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('5600'),array('accountcode','accountname','group_'),array('5600','Kostnader f','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('5700'),array('accountcode','accountname','group_'),array('5700','Frakter och transporter','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('5800'),array('accountcode','accountname','group_'),array('5800','Resekostnader','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('5900'),array('accountcode','accountname','group_'),array('5900','Reklam och PR','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6071'),array('accountcode','accountname','group_'),array('6071','Representation, avdragsgill','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6072'),array('accountcode','accountname','group_'),array('6072','Representation, ej avdragsgill','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6090'),array('accountcode','accountname','group_'),array('6090','','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6100'),array('accountcode','accountname','group_'),array('6100','Kontorsmaterial och trycksaker','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6200'),array('accountcode','accountname','group_'),array('6200','Tele och post','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6310'),array('accountcode','accountname','group_'),array('6310','F','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6350'),array('accountcode','accountname','group_'),array('6350','F','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6351'),array('accountcode','accountname','group_'),array('6351','Konstaterade kundf','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6352'),array('accountcode','accountname','group_'),array('6352','Befarade kundf','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6380'),array('accountcode','accountname','group_'),array('6380','F','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6390'),array('accountcode','accountname','group_'),array('6390','','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6420'),array('accountcode','accountname','group_'),array('6420','Revisionsarvoden','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6490'),array('accountcode','accountname','group_'),array('6490','','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6530'),array('accountcode','accountname','group_'),array('6530','Redovisningstj','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6540'),array('accountcode','accountname','group_'),array('6540','ADB-tj','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6590'),array('accountcode','accountname','group_'),array('6590','','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6970'),array('accountcode','accountname','group_'),array('6970','Tidningar, tidskrift, facklitteratur','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6980'),array('accountcode','accountname','group_'),array('6980','F','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6981'),array('accountcode','accountname','group_'),array('6981','F','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6982'),array('accountcode','accountname','group_'),array('6982','F','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6990'),array('accountcode','accountname','group_'),array('6990','','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6991'),array('accountcode','accountname','group_'),array('6991','','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6992'),array('accountcode','accountname','group_'),array('6992','','Kostnader'));
InsertRecord('chartmaster',array('accountcaode'),array('6995'),array('accountcode','accountname','group_'),array('6995','Dr','Kostnader'));
?>