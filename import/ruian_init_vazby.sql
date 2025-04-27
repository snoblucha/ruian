DROP TABLE IF EXISTS `ruian_adresy_vazby`;
DROP TABLE IF EXISTS `ruian_vazby_cr`;
DROP TABLE IF EXISTS `ruian_soudrznosti`;
DROP TABLE IF EXISTS `ruian_kraje`;
DROP TABLE IF EXISTS `ruian_okresy`;
DROP TABLE IF EXISTS `ruian_adresy_vazby`;


CREATE TABLE `ruian_soudrznosti` (
  `id`    INT(11)     NOT NULL,
  `nazev` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO ruian_soudrznosti VALUES
  ('60', 'Jihovýchod'),
  ('35', 'Jihozápad'),
  ('86', 'Moravskoslezsko'),
  ('19', 'Praha'),
  ('51', 'Severovýchod'),
  ('43', 'Severozápad'),
  ('27', 'Střední Čechy'),
  ('78', 'Střední Morava');


CREATE TABLE `ruian_kraje` (
  `id`            INT(11)     NOT NULL,
  `nazev`         VARCHAR(64) NOT NULL,
  `soudrznost_id` INT(11)     NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ruian_kraje VALUES
  (19, 'Hlavní město Praha', 19),
  (35, 'Jihočeský kraj', 35),
  (116, 'Jihomoravský kraj', 60),
  (51, 'Karlovarský kraj', 43),
  (108, 'Kraj Vysočina', 60),
  (86, 'Královéhradecký kraj', 51),
  (78, 'Liberecký kraj', 51),
  (132, 'Moravskoslezský kraj', 86),
  (124, 'Olomoucký kraj', 78),
  (94, 'Pardubický kraj', 51),
  (43, 'Plzeňský kraj', 35),
  (27, 'Středočeský kraj', 27),
  (60, 'Ústecký kraj', 43),
  (141, 'Zlínský kraj', 78);

#KOD;NAZEV;VUSC_KOD;KRAJ_1960_KOD;NUTS_LAU;PLATI_OD;PLATI_DO;DATUM_VZNIKU
CREATE TABLE `ruian_okresy` (
  `id`           INT(11)     NOT NULL,
  `nazev`        VARCHAR(64) NOT NULL,
  `kraj_id`      INT(11)     NOT NULL,
  `kraj_1960_id` INT(11)     NOT NULL,
  `nuts_lau`     INT(11)     NOT NULL,
  `plati_od`     DATETIME    NOT NULL,
  `plati_do`     DATETIME    NOT NULL,
  `datum_vzniku` DATETIME    NOT NULL,
  PRIMARY KEY (`id`)

)
  ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOAD DATA LOCAL INFILE 'celky/UI_OKRES.csv' INTO TABLE ruian_okresy
CHARACTER SET cp1250 FIELDS TERMINATED BY ';' IGNORE 1 LINES;


CREATE TABLE `ruian_vazby_cr` (
  `cast_obce_id`      INT(11) NOT NULL,
  `obec_id`           INT(11) NOT NULL,
  `poverena_obec_id`  INT(11) NOT NULL,
  `rozsirena_obec_id` INT(11) NOT NULL,
  `kraj_id`           INT(11) NOT NULL,
  `soudrznost_id`     INT(11) NOT NULL,
  `stat_id`           INT(11) NOT NULL
)
  ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#COBCE_KOD;OBEC_KOD;OKRES_KOD;KRAJ_1960_KOD;STAT_KOD
CREATE TABLE `ruian_vazby_okresy` (
  `cast_obce_id` INT(11) NOT NULL,
  `obec_id`      INT(11) NOT NULL,
  `okres_id`     INT(11) NOT NULL,
  `kraj_1960_id` INT(11) NOT NULL,
  `stat_id`      INT(11) NOT NULL
)
  ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#ADM_KOD;ULICE_KOD;COBCE_KOD;MOMC_KOD;MOP_KOD;SPRAVOBV_KOD;OBEC_KOD;POU_KOD;ORP_KOD;VUSC_KOD;VO_KOD
CREATE TABLE `ruian_adresy_vazby` (
  `adresa_id`              INT(11) NOT NULL,
  `ulice_id`               INT(11) NOT NULL,
  `cast_obce_id`           INT(11) NOT NULL,
  `mestsky_obvod_id`       INT(11) NOT NULL,
  `mestsky_obvod_praha_id` INT(11) NOT NULL,
  `sprav_obec_id`          INT(11) NOT NULL,
  `obec_id`                INT(11) NOT NULL,
  `pou_id`                 INT(11) NOT NULL,
  `orp_id`                 INT(11) NOT NULL,
  `vusc_id`                INT(11) NOT NULL,
  `vo_id`                  INT(11) NOT NULL
)
  ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
