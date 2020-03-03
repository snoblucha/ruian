<!DOCTYPE html>
<html>
<head>
  <!--Import Google Icon Font-->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!--Import materialize.css-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
  <script src="https://maps.googleapis.com/maps/api/js?sensor=false&key=<?= MAP_API_KEY ?>"></script>

  <!--Let browser know website is optimized for mobile-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>

<body>
<div class="row">
  <div class="col s12 m12">
    <div class="card blue-grey darken-1">
      <div class="card-content white-text">
        <span class="card-title"><?= $adresa['ulice'] ?>
          <?= $adresa['cp'] ?><?= $adresa['co'] ? '/' . $adresa['co'] : '' ?><?= $adresa['znak_co'] ?>,
          <?= $adresa['obec']['nazev'] ?>
          <?= $adresa['cast_obce']['psc'] ?>
          <small>( <?= $adresa['cast_obce']['nazev']  ?> | <?= $adresa['cast_obce']['mo'] ?>)</small>
        </span>
        <div id="map_canvas" style="width: 100%; height: 400px;"></div>
      </div>
      <div class="card-action">
      </div>
    </div>
  </div>
</div>


<script>
  function initialize() {
    var map_canvas = document.getElementById('map_canvas');
    var map_options = {
      center: new google.maps.LatLng(<?=$adresa['gps']['lat']?>, <?= $adresa['gps']['lng']?>),
      zoom: 15,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    var map = new google.maps.Map(map_canvas, map_options);
    var myLatLng = {lat: <?=$adresa['gps']['lat']?>, lng: <?= $adresa['gps']['lng']?>};

    var marker = new google.maps.Marker({
      position: myLatLng,
      map: map,
      title: ''
    });
  }

  google.maps.event.addDomListener(window, 'load', initialize);
</script>


<!--Import jQuery before materialize.js-->
<script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
</body>
</html>
