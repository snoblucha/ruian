#!/bin/bash

NAME="20160131_OB_ADR_csv.zip"
CESTA_K_CSV="./CSV"  ## cesta, kde jsi rozbalil archiv (cesta až k souborům)
SEZNAM="/tmp/seznam.txt"     ## může zůstat přednastaveno, je to jen dočasný soubor

######################
USER="ruian"                  ## uživatel do DB
PASSWORD="ruian"        ## heslo do DB
DB="ruian"                  ## databáze
TABLE="ruian_adresy"  ## tabulka v DB, kam se budou importovat data
######################

wget "http://vdp.cuzk.cz/vymenny_format/csv/$NAME"
unzip ${NAME}
rm ${NAME}

mysql -u ${USER} -p${PASSWORD} ${DB} < ruian_init.sql

# seznam souborů pro import
find ${CESTA_K_CSV} -type f > ${SEZNAM}

# import
while read line; do
        mysql -u ${USER} -p${PASSWORD} --local_infile=1 ${DB} -e "LOAD DATA LOCAL INFILE '$line' INTO TABLE $TABLE CHARACTER SET cp1250 FIELDS TERMINATED BY ';' IGNORE 1 LINES"
done < ${SEZNAM}

mysql -u ${USER} -p${PASSWORD} ${DB} < ruian_transform.sql

rm ${SEZNAM};
rm -rf ${CESTA_K_CSV}
exit;