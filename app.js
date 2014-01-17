
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
   var sqlite3 = require('sqlite3').verbose();
   var db = new sqlite3.Database('test/eem_1.sql');

db.serialize(function() {
//console.log('db connected!');
db.each("SELECT * FROM Surfaces", function(err, row){
    var str = row.SurfaceIndex + ',' + row.SurfaceName + ',' + row.Area + '\n';
  console.log(str);
  
});
});
db.close(); 

});

app.post('/rmt', openstudio.simulateOpenstudio);

http.createServer(app).listen(app.get('port'), function(){
  console.log('Express server listening on port ' + app.get('port'));
});
