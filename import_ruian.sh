#!/bin/bash

NAME="20160131_OB_ADR_csv.zip"

#wget "http://vdp.cuzk.cz/vymenny_format/csv/$NAME"
#unzip $NAME
#rm $NAME

CESTA_K_CSV="./CSV"  ## cesta, kde jsi rozbalil archiv (cesta až k souborům)
SEZNAM="/tmp/seznam.txt"     ## může zůstat přednastaveno, je to jen dočasný soubor

######################
USER="ruian"                  ## uživatel do DB
PASSWORD="ruian"        ## heslo do DB
DB="ruian"                  ## databáze
TABLE="adresni_mista"  ## tabulka v DB, kam se budou importovat data
######################

# seznam souborů pro import
find $CESTA_K_CSV -type f > $SEZNAM

mysql -u $USER -p$PASSWORD --default-character-set=latin2 --local_infile=1 $DB -e "DROP TABLE IF EXISTS $TABLE"
mysql -u $USER -p$PASSWORD --default-character-set=latin2 --local_infile=1 $DB -e "CREATE TABLE IF NOT EXISTS `adresni_mista` (
  `kod_adm` int(11) NOT NULL,
  `kod_obce` int(11) NOT NULL,
  `nazev_obce` text NOT NULL,
  `nazev_momc` text NOT NULL,
  `nazev_mop` text NOT NULL,
  `kod_casti_obce` int(11) NOT NULL,
  `nazev_casti_obce` text NOT NULL,
  `nazev_ulice` text NOT NULL,
  `typ_so` text NOT NULL,
  `cislo_domovni` text NOT NULL,
  `cislo_orientacni` int(11) NOT NULL,
  `znak_cisla_orientacniho` text NOT NULL,
  `psc` int(11) NOT NULL,
  `souradnice_y` text NOT NULL,
  `souradnice_x` int(11) NOT NULL,
  `plati_od` datetime NOT NULL
) ENGINE=InnoDB"


# import
while read line; do
        mysql -u $USER -p$PASSWORD --local_infile=1 $DB -e "LOAD DATA LOCAL INFILE '$line' INTO TABLE $TABLE CHARACTER SET cp1250 FIELDS TERMINATED BY ';' IGNORE 1 LINES"
done < $SEZNAM

mysql -u $USER -p$PASSWORD $DB < ruian_transform.sql

rm $SEZNAM;
exit;