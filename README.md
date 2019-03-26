# RUIAN API 

## Skript na import dat

Importuje RUIAN data z 

    http://nahlizenidokn.cuzk.cz/StahniAdresniMistaRUIAN.aspx
    
Je potřeba nastavit ve skriptu `import_ruian.sh`  a `import_vazby.sh` připojení do databáze

Základ použit z http://forum.root.cz/index.php?topic=6542.0

# Instalace

- Clone, nebo rozbalit archiv.
- přejmenat a doplnit `config.default.php` na `config.php`

## API

### Sdílené parametry

- `limit` [int] Omezit na X výsledků. Jako v SQL
- `offset` [int] Posun o X jako v SQL
- `q` [string] Začátek slova. Case insensitive (předán jako LIKE '$q%')

### Vyhledání RUIAN ID 

`/najit?o=OBEC&oid=obec_id&u=ULICE&c=CISLO_POPISNE&co=CISLO_ORIENTACNI`

- `oid` [int] RUIAN_ID obce
- `o` [string] : Obec
- `u` [string] ulice
- `uid` [string] id ulice
- `c` [int] Číslo ulice. 
- `co` [int|string] Číslo orientační, může obsahovat znak. Př. 18a

Parametry jsou nepovinné, krom [o|oid].  Vrací pole s odpovídajícími záznamy.

### Vyhledání obce 

`/obce?q=ZACATEK_JMENA&limit=LIMIT&offset=OFFSET]`

Parametry jsou nepovinné

Vrátí seznam obcí.
`[{...},{...}]`

Příklad: `/obce?limit=50&q=Ma`


### Detail obce

`/obec/ID`

Vrací objekt.
`/obec/574686`
`{"id":"574686","nazev":"\u017d\u010f\u00e1r nad Metuj\u00ed"}`
    
### Adresy

`/adresy/ID_OBCE?q=ULICE&limit=LIMIT&offset=OFFSET`
  
Seznam adres v obci

Vrací pole adres `[{...},{...}]` 

### Adresa

    `/adresa/ID`

Vrací

    {"id":"7386761","obec_id":"574686","casti_obce_id":"195189","ulice":"\u017d\u010f\u00e1r nad Metuj\u00ed","typ_so":"\u010d.p.","cp":"36","co":"0","znak_co":"","x":"1008603.56","y":"609754.00","gps":{"lat":50.543314085949,"lng":16.214786593153},"obec":{"id":"574686","nazev":"\u017d\u010f\u00e1r nad Metuj\u00ed"},"cast_obce":{"id":"195189","nazev":"\u017d\u010f\u00e1r nad Metuj\u00ed","psc":"54955","nazev_mop":"","nazev_momc":""}}

### Části obce

`/casti_obce/ID_OBCE?q=ULICE&limit=LIMIT&offset=OFFSET`
  
Seznam částí obce

Vrací pole `[{...},{...}]` 


### Část obce
`/cast_obce/ID_CASTI_OBCE`

Příklad
`/cast_obce/195189`
`{"id":"195189","obec_id":"574686","nazev":"\u017d\u010f\u00e1r nad Metuj\u00ed","psc":"54955","nazev_momc":"","nazev_mop":""}`

    
## Demo klienta - vyhledávací pole

https://codepen.io/snoblucha/pen/GeaNEY

        





