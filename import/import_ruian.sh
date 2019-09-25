#!/bin/bash

LASTDATE=`date -d "$(date +%Y-%m-01) -1 day" +%Y%m%d`
NAME="${LASTDATE}_OB_ADR_csv.zip"
CESTA_K_CSV="./CSV"  ## cesta, kde jsi rozbalil archiv (cesta až k souborům)
BASEDIR=$(dirname $0)

######################
USER=${DB_USER:-"ruian"}                  ## uživatel do DB
PASSWORD=${DB_PASSWORD:-"ruian"}        ## heslo do DB
DB=${DB_NAME:-"ruian"}                  ## databáze
TABLE=${DB_TABLE:-"ruian_adresy"}  ## tabulka v DB, kam se budou importovat data
HOST=${DB_HOST:-127.0.0.1}
PORT=${DB_PORT:-3306}
######################

echo "Stahuji seznam adres..."
curl -O /tmp/ruian.zip "http://vdp.cuzk.cz/vymenny_format/csv/$NAME"
unzip /tmp/ruian.zip -d ${CESTA_K_CSV}

echo "Inicializace databaze..."
mysql -h${HOST} -P${PORT} -u${USER} -p${PASSWORD} ${DB} < ${BASEDIR}/ruian_init.sql

# seznam souborů pro import
files=$(ls $CESTA_K_CSV)
TMP_DIR=$(mktemp -d)

# fix all encodings
echo Re-code files to utf-8
for f in $files
do
  iconv -f cp1250 -t utf-8 ${CESTA_K_CSV}/${f} > ${TMP_DIR}/${f}
done

# import
echo Import into mysql
for f in $files; do
  mysql -h${HOST} -P${PORT} -u${USER} -p${PASSWORD} --local_infile=1 ${DB} -e "LOAD DATA LOCAL INFILE '${TMP_DIR}/${f}' INTO TABLE $TABLE FIELDS TERMINATED BY ';' IGNORE 1 ROWS"
done

# transform database
echo "Transform database"
mysql -h${HOST} -P${PORT} -u${USER} -p${PASSWORD} ${DB} < ${BASEDIR}/ruian_transform.sql

rm -r ${TMP_DIR}
