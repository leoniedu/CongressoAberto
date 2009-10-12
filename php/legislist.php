<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
  google.load('visualization', '1', {packages: ['table']});
</script>
<script type="text/javascript">
var visualization;
var data;
var options = {'showRowNumber': true};
function drawVisualization() {
  var query = new google.visualization.Query('/php/query.php?form=legislist');    
    query.send(handleQueryResponse);
}
function handleQueryResponse(response) {
  if (response.isError()) {
    alert('Error in query: ' + response.getMessage() + ' ' + response.getDetailedMessage());
    return;
  }
  var post = new google.visualization.TablePatternFormat('<a href="/?p={1}"> {0} </a>');
  var rate = new google.visualization.TablePatternFormat('{0} / {1}');
  var data = response.getDataTable();
  post.format(data, [0,1]);
  rate.format(data, [5,6]);
  rate.format(data, [7,8]);
  options['page'] = 'enable';
  options['pageSize'] = 20;
  options['pagingButtonsConfiguration'] = 'auto';
  options['allowHtml'] = true;
  var view = new google.visualization.DataView(data);
  view.hideColumns([1,6,8]);
  visualization = new google.visualization.Table(document.getElementById('table1'));
  visualization.draw(view, options);
}
 
google.setOnLoadCallback(drawVisualization);
 
</script>
<h3> em Exercício </h3>
<p>Dados da legislatura 2007-2010 atualizados diariamente</p>
<div id="table1">
</div>
<p> Observação: Não levamos em consideração ausencias justificadas ou licensas médicas. </p>