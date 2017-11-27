#!/bin/bash


NAME_STRUKT="20171031_strukt_ADR.csv.zip"

CESTA_K_CSV_STRUKT="./strukturovane-CSV"  ## cesta, kde se rozbalil archiv struktury
SEZNAM="/tmp/seznam.txt"     ## může zůstat přednastaveno, je to jen dočasný soubor

######################
USER="ruian"                  ## uživatel do DB
PASSWORD="ruian"        ## heslo do DB
DB="ruian"                  ## databáze
TABLE="ruian_adresy"  ## tabulka v DB, kam se budou importovat data
######################


#echo "Stahuji strukturu..."
#wget "http://vdp.cuzk.cz/vymenny_format/csv/$NAME_STRUKT"
#unzip ${NAME_STRUKT}
#rm ${NAME_STRUKT}

echo "Inicializace databaze..."
mysql --local_infile=1 -u ${USER} -p${PASSWORD} ${DB} < ruian_init_vazby.sql

mysql -u ${USER} -p${PASSWORD} --local_infile=1 ${DB} -e "LOAD DATA LOCAL INFILE '${CESTA_K_CSV_STRUKT}/adresni-mista-vazby-cr.csv' INTO TABLE ruian_adresy_vazby CHARACTER SET cp1250 FIELDS TERMINATED BY ';' IGNORE 1 LINES"

mysql -u ${USER} -p${PASSWORD} --local_infile=1 ${DB} -e "LOAD DATA LOCAL INFILE '${CESTA_K_CSV_STRUKT}/vazby-cr.csv' INTO TABLE ruian_vazby_cr CHARACTER SET cp1250 FIELDS TERMINATED BY ';' IGNORE 1 LINES"


#rm -rf ${CESTA_K_CSV_STRUKT}
exit;