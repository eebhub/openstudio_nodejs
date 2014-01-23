
/**
 * Module dependencies.
 */

var express = require('express');
var http = require('http');
var path = require('path');
var routes = require('./routes/routes.js');
var openstudio = require('./routes/openstudio.js');

var app = express();

// all environments
app.set('port', process.env.PORT || 3000);
app.set('views', __dirname + '/views');
app.set('view engine', 'ejs');
app.use(express.favicon());
app.use(express.logger('dev'));
app.use(express.bodyParser());
app.use(express.methodOverride());
app.use(app.router);
app.use(express.static(path.join(__dirname, 'public')));

// development only
if ('development' == app.get('env')) {
  app.use(express.errorHandler());
}

app.get('/', routes.getHome);
app.get('/form', routes.getForm);

app.get('/eplus_out', function(req, res){
    
/*test simple selections*/
//   var sqlite3 = require('sqlite3').verbose();
//   var db = new sqlite3.Database('test/eem_1.sql');
// var str = '';
// db.serialize(function() {

// db.each("SELECT * FROM Surfaces", function(err, row){
//     str = row.SurfaceIndex + ',' + row.SurfaceName + ',' + row.Area + '\n';
//   console.log(str);
  
// });
// });
// db.close(); 


/*test getValue*/
// var sqlite3= require('./lib/eeb_sqlite3.js');
// sqlite3.getValues('ENVELOPE%', 'ENTIRE%', 'Opaque Exterior', 'Btu%', 'test/eem_1.sql');

/*test getReportForStrings*/
// var sqlite3= require('./lib/eeb_sqlite3.js');
// sqlite3.getReportForStrings('ShadingSummary', 'test/eem_1.sql');

/*test getValuesByMonthly*/
var sqlite3 = require('./lib/eeb_sqlite3.js');
sqlite3.getValuesByMonthly('ENVELOPE%', 'ENTIRE%', 'Opaque Exterior', 'Btu%', 'test/eem_1.sql');



});

app.post('/rmt', openstudio.simulateOpenstudio);

http.createServer(app).listen(app.get('port'), function(){
  console.log('Express server listening on port ' + app.get('port'));
});
