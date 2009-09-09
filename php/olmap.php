<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
<title>Votações</title>
<script src="http://www.openlayers.org/api/OpenLayers.js" type="text/javascript"></script>
<script src="http://openstreetmap.org/openlayers/OpenStreetMap.js" type="text/javascript"></script>
<script language="php">
$state = $_GET["state"];
$candno = $_GET["candno"];
echo "<script src=\"/maps/eleicao2006/dataLoc".$state.".js\" type=\"text/javascript\"></script>";
echo "<script src=\"/maps/eleicao2006/data". $candno.$state.".js\" type=\"text/javascript\"></script>";
echo "<script src=\"/maps/eleicao2006/dataAdd".$state.".js\" type=\"text/javascript\"></script>";
</script>

<script type="text/javascript">
function Geometry(symbol, maxSize, maxValue){
  this.symbol = symbol;
  this.maxSize = maxSize;
  this.maxValue = maxValue;
  
  this.getSize = function(value){
		switch(this.symbol) {
		case 'circle': // Returns radius of the circle
		case 'square': // Returns length of a side
		return Math.sqrt(value/this.maxValue)*this.maxSize;
		case 'bar': // Returns height of the bar
		return (value/this.maxValue)*this.maxSize;
		case 'sphere': // Returns radius of the sphere
			case 'cube': // Returns length of a side
		return Math.pow(value/this.maxValue, 1/3)*this.maxSize;
		}
	}
 }

var map, wms;

function init(){
  //var lon = -66.94796;
  //var lat = -9.933975;
  // FIX AREA!
  var zoom = 4;
  var max_value = 1000000;
  var symbol = new Geometry('circle', 1, max_value);
  var context = {
  getSize: function(feature) {
      return symbol.getSize(Math.pow(feature.data.value,1)) * Math.pow(2,map.getZoom()-1);
    }
  };
  
  // Setting the feature style
  var template = {
  strokeColor : 'red', 
  strokeWidth: 2,
  strokeOpacity: 0.9,
  fillColor : 'red', 
  fillOpacity : 0.1, 
  pointRadius: "${getSize}"
  };
  
  // Assigning the feature style
  var style = new OpenLayers.Style(template, {context: context});
  var styleMap = new OpenLayers.StyleMap({
      'default': style, 
	'select': {
      fillColor: 'red',
	  fillOpacity: 0.75
	  }
    });
  
  var options = {
  numZoomLevels: 10,
  controls: []  // Remove all controls
  };

  map = new OpenLayers.Map( 'map', options );
  // WMS map layer
  wms = new OpenLayers.Layer.WMS( 
		"OpenLayers WMS",
		"http://labs.metacarta.com/wms/vmap0?",
		{
		layers: 'basic'
		    },
		{
		isBaseLayer: true
		    } 
				  );
  // FIX: Google maps projection is off
  map.addLayer(wms);
  feature_layer.styleMap = styleMap;
  feature_layer.isBaseLayer = false;
  map.addLayer(feature_layer);
  map.setCenter(new OpenLayers.LonLat(lon, lat), zoom);
  
  map.addControl(new OpenLayers.Control.LayerSwitcher());
  map.addControl(new OpenLayers.Control.PanZoomBar());
  map.addControl(new OpenLayers.Control.Navigation({zoomWheelEnabled: false}));
  
  function show_label(feature) {
    var selectedFeature = feature;
    var label = selectedFeature.data.label + ' - ' + selectedFeature.data.value + " votos.";
    var hed = document.getElementById('info');
		hed.innerHTML = label;
  }
  
  function hide_label() {
    var hed = document.getElementById('info');
    hed.innerHTML = "Coloque o mouse sobre um município para ver a votação do candidato.";
  }
	
  var hover_control = new OpenLayers.Control.SelectFeature(feature_layer, {
    hover: true,
	onSelect: show_label,
	onUnselect: hide_label
	});
  
	map.addControl(hover_control);
	hover_control.activate();
}
</script>

</head>
<body onload="init()">
<h1>Votações</h1>
<h2><div id="info">Hover!</div></h2>
<div id="map" style="width: 500px; height: 300px;"></div> 
<!-- <p>The programming part was lifted from <a href="http://blog.thematicmapping.org/2008/04/proportional-symbol-mapping-with.html">this nice blog post</a> at thematicmapping.org</p>. -->

</body>

