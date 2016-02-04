DROP TABLE IF EXISTS ruian_obce;

CREATE TABLE ruian_obce
SELECT kod_obce,nazev_obce
FROM `ruian_adresy`
GROUP BY kod_obce;

ALTER TABLE `ruian_obce` ADD PRIMARY KEY `kod_obce` (`kod_obce`);


ALTER TABLE `ruian_adresy` DROP `nazev_obce`;

DROP TABLE IF EXISTS ruian_casti_obce;

CREATE TABLE ruian_casti_obce
SELECT kod_obce,kod_casti_obce,nazev_casti_obce,psc,nazev_momc,nazev_mop
FROM `ruian_adresy` GROUP BY kod_casti_obce;

UPDATE `ruian_adresy` SET nazev_ulice=nazev_casti_obce WHERE NOT nazev_ulice;

ALTER TABLE `ruian_adresy` DROP `nazev_casti_obce`, DROP `psc`, DROP `nazev_momc`, DROP `nazev_mop`;


ALTER TABLE `ruian_casti_obce`
ADD PRIMARY KEY `kod_casti_obce` (`kod_casti_obce`),
ADD INDEX `kod_obce` (`kod_obce`);

ALTER TABLE `ruian_adresy`
ADD INDEX `kod_casti_obce` (`kod_casti_obce`),
ADD INDEX `kod_obce` (`kod_obce`);
