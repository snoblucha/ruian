<?php

/**
 * Class Ruian
 * Priklad tridy pro backend
 */
class Ruian
{
    /**
     * @var null|string
     */
    private $url = '';

    /**
     * Ruian constructor.
     * @param String|null $url Null, pak nacte z nastaveni
     */
    public function __construct($url )
    {
        $this->url = $url;


    }

    /**
     * Staticka metoda pro rychlejsi volani
     *
     * @param String $url
     * @return Ruian
     */
    public static function api($url )
    {
        return new Ruian($url);
    }

    public function find($id)
    {
        return $this->query('adresa/' . $id);
    }

    /**
     * @param String $query Cesta
     * @return mixed
     */
    public function query($query)
    {
        $api_url = "{$this->url}/{$query}";
        $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
        $result = file_get_contents($api_url, false, $context);
        return json_decode($result, true);
    }

    /**
     * @param String $adresa ulice a cp. pr.: Anezky ceske 628/1
     * @param String $obec mesto
     * @return array
     */
    public function search($adresa, $obec)
    {
        $query = array(
            'u' => $adresa,
            'o' => $obec,
            'c' => '',
            'co' => '',
        );

        if (preg_match('/(.*) ([\d\/]+[a-zA-Z]?)/', $query['u'], $res)) {
            $query['u'] = $res[1];
            list($query['c'], $query['co']) = array_merge(explode('/', $res[2]), array(''));
        }
        return $this->query("najit?" . http_build_query($query));
    }

}