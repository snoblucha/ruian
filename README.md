# RUIAN API 

## Skript na import dat

Importuje RUIAN data z 

    http://nahlizenidokn.cuzk.cz/StahniAdresniMistaRUIAN.aspx
    
Je potřeba nastavit název souboru ve skriptu `import_ruian.sh`     

Základ použit z http://forum.root.cz/index.php?topic=6542.0

# API


## Vyhledání RUIAN ID `/najit?m=MESTO&u=ULICE&c=CISLO_POPISNE&co=CISLO_ORIENTACNI`


/najit?m=MESTO&u=ULICE&c=CISLO_POPISNE&co=CISLO_ORIENTACNI
    
    
    
## Obce `/obce[?limit=Integer][&offset=Integer][&q=Start]`
  
Příklad:
    
    /obce?limit=50&q=Albert
    

        



