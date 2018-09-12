#!/bin/bash

LASTDATE=`date -d "$(date +%Y-%m-01) -1 day" +%Y%m%d`
NAME_STRUKT="${LASTDATE}_strukt_ADR.csv.zip"

CESTA_K_CSV_STRUKT="./strukturovane-CSV"  ## cesta, kde se rozbalil archiv struktury
SEZNAM="/tmp/seznam.txt"     ## může zůstat přednastaveno, je to jen dočasný soubor

######################
USER="ruian"                  ## uživatel do DB
PASSWORD="ruian"        ## heslo do DB
DB="ruian"                  ## databáze
TABLE="ruian_adresy"  ## tabulka v DB, kam se budou importovat data
HOST=127.0.0.1
PORT=3306
######################


echo "Stahuji strukturu..."
wget "http://vdp.cuzk.cz/vymenny_format/csv/$NAME_STRUKT"
unzip ${NAME_STRUKT}
rm ${NAME_STRUKT}

echo "Inicializace databaze..."
mysql -h${HOST} -P${PORT} -u ${USER} -p${PASSWORD} --local_infile=1 ${DB} < ruian_init_vazby.sql
mysql -h${HOST} -P${PORT} -u ${USER} -p${PASSWORD} --local_infile=1 ${DB} -e "LOAD DATA LOCAL INFILE '${CESTA_K_CSV_STRUKT}/adresni-mista-vazby-cr.csv' INTO TABLE ruian_adresy_vazby CHARACTER SET cp1250 FIELDS TERMINATED BY ';' IGNORE 1 LINES"
mysql -h${HOST} -P${PORT} -u ${USER} -p${PASSWORD} --local_infile=1 ${DB} -e "LOAD DATA LOCAL INFILE '${CESTA_K_CSV_STRUKT}/vazby-cr.csv' INTO TABLE ruian_vazby_cr CHARACTER SET cp1250 FIELDS TERMINATED BY ';' IGNORE 1 LINES"

rm -rf ${CESTA_K_CSV_STRUKT}
exit;
