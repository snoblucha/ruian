<!DOCTYPE html>
<html>
<head>
  <!--Import Google Icon Font-->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!--Import materialize.css-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.css">
  <script src="https://maps.googleapis.com/maps/api/js?sensor=false&key=<?= MAP_API_KEY ?>"></script>

  <!--Let browser know website is optimized for mobile-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>

<body>
<div class="row">
  <div class="col s12 m12">
    <div class="card darken-1">
      <div class="card-content">
        <span class="card-title">Vyhledat RUIAN</span>
        <input type="text" id="ruian" placeholder="ruian"/>
      </div>
      <div class="card-action">
        RUIAN: <a href="" class="ruian-url" target="_blank"><span id="ruian-value"></span></a>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col s12 m12">
    <div class="card darken-1">
      <div class="card-content">
        <span class="card-title">Vyhledat adresu dle RUIAN</span>
        <input type="text" id="ruian-detail" placeholder="ruian"/>
        <span class="card-title">Najít podle RUIAN</span>
        <iframe id="ruian-detail-result" width="100%" height="400px" src="about:blank"></iframe>
      </div>
      <div class="card-action">
        <a href="" class="ruian-url" id="ruian-url" target="_blank"></a>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col s12 m12">
    <div class="card darken-1">
      <div class="card-content">
        <span class="card-title">Endpointy</span>
        <ul>
          <li> /obce[?q=]</li>
          <li> /obec/{id_obce}</li>
          <li> /casti_obce/{id_obce}</li>
          <li> /ulice/{id_obce}[?q={ulice}]</li>
          <li> /adresa/{id_adresy}</li>
          <li> /adresy_obce/{id_obce}[?limit=&offset=]</li>
          <li> /adresy_casti_obce/{id_cast_obce}[?limit=&offset=]</li>
          <li> /search?q=street number,city</li>
          <li> /detail/{ruian}</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!--Import jQuery before materialize.js-->
<script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.3/js/standalone/selectize.js"></script>

<script>
  $(function () {

    var ruian_url = 'https://ruian.viridium.cz/';
    $('#ruian-detail').on('change', function () {
      load_detail($(this).val());
      console.log('changed' + $(this).val());
    });

    function load_detail(ruian) {
      $('#ruian-detail-result').attr('src', ruian_url + 'detail/' + ruian);
      var url = ruian_url + 'adresa/' + ruian;
      $('.ruian-url').attr('href', url)
      $('#ruian-url').text(url)


    }

    $('#ruian').selectize({
      valueField: 'id',
      searchField: ['ulice', 'cp', 'co', 'obec'],
      create: false,
      delimiter: ';',
      maxItems: 1,
      render: {
        option: function (item, escape) {
          res = '<div>' +
            '<span class="title">' +
            '<span class="ulice">' + escape(item.ulice) + ' ' + '</span>' +
            '<span class="cp">' + escape(item.cp != '0' ? item.cp : '') + '</span>' +
            '<span class="co">' + escape(item.co != '0' ? '/' + item.co : '') + '</span>, ' +
            '<span class="mesto">' + escape(item.obec) + '</span>' +
            ' <span class="psc">' + escape(item.psc) + '</span>' +
            '</span>' +
            '</div>';
          return res;
        },
        item: function (item, escape) {
          return '<div>' + escape(item.ulice) + ' ' + escape(item.cp) + ', ' + item.obec + ' ' + item.psc + '</div>';
        }
      },
      onChange: function (value) {
        $('#ruian-value').text(value);
        $('#ruian-detail').val(value)
        load_detail(value);
      },
      score: function (search) {
        var score = this.getScoreFunction(search.replace(',', ''));
        return function (item) {
          return score(item);
        };
      },
      load: function (query, callback) {
        if (!query.length) return callback();
        $.ajax({
          url: ruian_url + '/search?q=' + encodeURIComponent(query),
          type: 'GET',
          error: function () {
            callback();
          },
          success: function (res) {
            var items = res.slice(0, 20).map(function (item) {
              return {
                id: item.id,
                ulice: item.ulice,
                cp: item.cp,
                co: item.co,
                obec: item.obec.nazev,
                psc: item.cast_obce.psc
              }
            });
            callback(items);
          }
        });
      }
    });

  });


</script>
</body>
</html>
