DROP TABLE IF EXISTS `ruian_adresy`;
CREATE TABLE `ruian_adresy` (
  `id` int(11) NOT NULL,
  `obec_id` int(11) NOT NULL,
  `nazev_obce` varchar(64) NOT NULL,
  `nazev_momc` varchar(64) NOT NULL,
  `nazev_mop` varchar(64) NOT NULL,
  `casti_obce_id` int(11) NOT NULL,
  `nazev_casti_obce` varchar(64) NOT NULL,
  `nazev_ulice` varchar(64) NOT NULL,
  `typ_so` varchar(16) NOT NULL,
  `cislo_domovni` int(11) NOT NULL,
  `cislo_orientacni` int(11) NOT NULL,
  `znak_cisla_orientacniho` varchar(4) NOT NULL,
  `psc` mediumint(9) NOT NULL,
  `souradnice_y` decimal(12,2) NOT NULL,
  `souradnice_x` decimal(12,2) NOT NULL,
  `plati_od` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

#ADM_KOD;ULICE_KOD;COBCE_KOD;MOMC_KOD;MOP_KOD;SPRAVOBV_KOD;OBEC_KOD;POU_KOD;ORP_KOD;VUSC_KOD;VO_KOD
CREATE TABLE `ruian_adresy_vazby` (
  `adresa_id` INT(11) NOT NULL,
  `ulice_id` INT(11) NOT NULL,
  `cast_obce_id` INT(11) NOT NULL,
  `mestsky_obvod_id` INT(11) NOT NULL,
  `mestsky_obvod_praha_id` INT(11) NOT NULL,
  `sprav_obec_id` INT(11) NOT NULL,
  `obec_id` INT(11) NOT NULL,
  `pou_id` INT(11) NOT NULL,
  `orp_id` INT(11) NOT NULL,
  `vusc_id` INT(11) NOT NULL,
  `vo_id` INT(11) NOT NULL
)




