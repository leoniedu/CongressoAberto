<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
  google.load('visualization', '1', {packages: ['table']});
</script>
<script type="text/javascript">
var visualization;
var data;
var options = {'showRowNumber': true};
function drawVisualization() {
  var query = new google.visualization.Query('/php/query.php?form=partylist');    
    query.send(handleQueryResponse);
}
function handleQueryResponse(response) {
  if (response.isError()) {
    alert('Error in query: ' + response.getMessage() + ' ' + response.getDetailedMessage());
    return;
  }
  var post = new google.visualization.TablePatternFormat('<a href="/?p={1}"> {0} </a>');
  var data = response.getDataTable();
  post.format(data, [0,1]);
  options['page'] = 'enable';
  options['pageSize'] = 20;
  options['pagingButtonsConfiguration'] = 'auto';
  options['allowHtml'] = true;
  var map = new google.visualization.TablePatternFormat('<img src="/php/timthumb.php?src=/images/partymaps/{0}map.png&w=80&h=0&zc=0&q=90">');
  map.format(data, [2]);
  var view = new google.visualization.DataView(data);
  view.hideColumns([1]);
  visualization = new google.visualization.Table(document.getElementById('table1'));
  visualization.draw(view, options);
}

google.setOnLoadCallback(drawVisualization);

</script>
<h3>  Atual Legislatura (2007-2010) </h3>
<explain> Somente partidos com mais de cinco legisladores </explain>
<div id="table1">
</div>
<p> Observação: Não levamos em consideração ausencias justificadas ou licensas médicas. </p>

