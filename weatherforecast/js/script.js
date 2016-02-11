
var images="http://cs-server.usc.edu:45678/hw/hw8/images/";
var bgcolors = ["#357CB4", "#EB4343", "#E58D4E", "#A6A338", "#966FA6", "#F27B7D", "#CD4470"];
function mymap(lat,lon) {
   $("#mymap").html("");
   try {
    var lonlat = new OpenLayers.LonLat(lon, lat).transform('EPSG:4326', 'EPSG:3857');
        }
    catch(err)
    {
     $("#mymap").html("Unable to Load Map.");
     return;
    }
    var map = new OpenLayers.Map({
        div: "mymap",
        center: lonlat,
    });
    var mapnik = new OpenLayers.Layer.OSM();
    var cloudlayer = new OpenLayers.Layer.XYZ(
        "clouds",
        "http://${s}.tile.openweathermap.org/map/clouds/${z}/${x}/${y}.png", {
            isBaseLayer: false,
            opacity: 0.4,
            sphericalMercator: true
        }
    );
    var preceplayer = new OpenLayers.Layer.XYZ(
        "precipitation",
        "http://${s}.tile.openweathermap.org/map/precipitation/${z}/${x}/${y}.png", {
            isBaseLayer: false,
            opacity: 0.4,
            sphericalMercator: true
        }
    );
    try{
      map.addLayers([mapnik,cloudlayer,preceplayer]);
      map.zoomTo(9);
       $('a[data-toggle="tab"]').off('shown.bs.tab');
     }
     catch(err)
     {
       $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
      var target = $(e.target).attr("href");
      if ((target == '#rightnow')) {
           mymap(lat,lon);
          }
        });
     }
}
function streetvalidate()
{
 if(($.trim($("#streetaddress").val())).length==0)
     {$("#streeterror").html("Please enter the street address");
 		return false;
	}
  else
  	{
  		$("#streeterror").html("");
        return true;
  	}
}
function cityvalidate()
{
if(($.trim($("#city").val())).length==0)
     { $("#cityerror").html("Please enter the city");
 			return false;
     }
 else
  	{$("#cityerror").html("");
		return true;
		}
}
function statevalidate()
{
	if($("#state").val()=="NIL")
     { $("#stateerror").html("Please select a state");
       return false;
    }
  else
  	{ $("#stateerror").html("");
      return true;
	}
}

function setrignowimage(icon,summary)
{
	$("#rightnowimage").attr("src",images+icon);
	$("#rightnowimage").attr("title",summary);
	$("#rightnowimage").attr("alt",summary);
}

function rightnow(djson)
{
	var str="<div class=\"col-md-12 rightnowdiv\">";
  str+="<div class=\"col-md-6 text-center\"> <img id=\"rightnowimage\" src=\""+images+djson['icon']+"\" title=\""+djson['summary']+"\" alt=\""+djson['summary']+"\"></div>";
  str+="<div class=\"col-md-6 text-center\">";
  str+="<h5 style=\"color:white;margin:0;font-weight:bold;\">"+djson['summary']+" in "+ $('#city').val()+","+$('#state').val()+"</h5>";
  str+="<span style=\"color:white;font-weight:bold;font-size:80px;\">"+djson['temp']+"</span>";
  str+="<span style=\"color:white;font-weight:bold;font-size:20px;vertical-align:150%\">"+djson['unit']+"</span>";
  str+="<br><span style=\"color:blue;font-weight:bold;\">L:"+djson['tempmin']+"</span>|";
  str+="<span style=\"color:green;font-weight:bold;\">H:"+djson['tempmax']+"</span>";
  str+="<img id=\"fbimage\" src=\"http://cs-server.usc.edu:45678/hw/hw8/images/fb_icon.png\" alt=\"Facebook\" title=\"Facebook\" onclick=\"fbfeed('"+djson['summary']+"','"+djson['temp']+"','"+djson['icon']+"')\" style=\"width:40px;position: absolute; right: 0; bottom: 0;\">";
  str+="</div></div>";
	var i=0;
	str+="<table id=\"rightnowtable\" class=\"table table-striped\" style=\"width=100%;margin-bottom:0px\"><tbody>";	
		for (key in djson['table']){
            if(i%2==0)
            	str+="<tr><td>"+key + "</td><td>" + djson.table[key]+"</td></tr>";
            else
            	str+="<tr style=\"background-color:#f2dede\"><td>"+key + "</td><td>" + djson.table[key]+"</td></tr>";
            i++;
 			}
    str+="</tbody></table>";
    $("#rightnowinfo").html(str);   
}

function formatData(result)
{
    fresult=JSON.parse(result);
    if(fresult.hasOwnProperty('error'))
      {alert(fresult['error']);
       return false;
     }
    
    rightnow(fresult['Right Now']);
    hourlyformat(fresult['Next 24 Hours']);
    dailyformat(fresult['Next 7 Days']);
    document.getElementById("demo").style.visibility = 'visible';
    mymap(fresult['Right Now']['lat'],fresult['Right Now']['long']);
  
}
function getResult() {
 
  $.ajax({ url: 'http://forecast36252-env.elasticbeanstalk.com/',
         data: {saddress: $('#streetaddress').val(),city:$('#city').val(), state:$('#state').val(), degree:$('input:radio[name=degree]:checked').val(), submit:'Search' },
         type: 'post',
         success: function(output) {
                   formatData(output);
                  }
});
}
function completevalidate()
{
  var stv=streetvalidate();
  var cv=cityvalidate();
  var sv=statevalidate();
	if(stv&&cv&&sv)
	{
		getResult();
	}
	return false;
}
function hourlyformat(djson){
	var mainjson;
var extrajson;
var str="<thead><tr class=\"text-muted \" style=\"background: #367DB5;\"><td>Time</td><td>Summary</td><td>Cloud Cover</td>";
if($('input:radio[name=degree]:checked').val()=="us")
{str+="<td>Temp(°F)</td>";}
else
{str+="<td>Temp(°C)</td>";}
str+="<td>View Details</td></tr><tbody>";
for(i=0;i<djson.length;i+=1){
mainjson=djson[i]['maindata'];
str+="<tr style=\"background: #FFFFFF;\">";
for (key in mainjson){
if(key=="Summary")
str+="<td>" + "<img src=\"http://cs-server.usc.edu:45678/hw/hw8/images/"+mainjson[key]+"\"style=\"height:40px;\"></td>";
else
str+="<td style=\"vertical-align: middle;\">" + mainjson[key]+"</td>";
}
str+="<td style=\"vertical-align: middle;\"><a data-toggle=\"collapse\" data-target=\"#hour"+i+"\" class=\"accordion-toggle collapsed\" hre=\"#\" aria-expanded=\"false\"><span class=\"glyphicon glyphicon-plus\"></span></a></td></tr>";
str+="<tr><td colspan=\"5\" style=\"padding:0px;background-color:#F1F1F1;\">";
str+="<div id=\"hour"+i+"\" class=\"accordion-body collapse\" style=\"margin: 10px; height: 0px;\" aria-expanded=\"false\">";
str+="<table class=\"table\" style=\"table-layout:fixed;\">";
str+="<thead><tr ><th class=\"text-center\">Wind</th><th class=\"text-center\">Humidity</th><th class=\"text-center\">Visibility</th><th class=\"text-center\">Pressure</th></tr></thead><tbody><tr style=\"background-color:#F1F1F1;\">";
extrajson=djson[i]['extradata'];
for (key in extrajson){str+="<td>"+extrajson[key]+"</td>";}
str+="</tr></tbody></table></div></td></tr>";
}

str+="</tbody>";
$("#next24table").html(str);
}
function dailyformat(djson)
{

	var str="<div class=\"col-md-2\"></div>";
	for(i=0;i<djson.length;i+=1)
	 {
	 	str+="<div class=\"col-md-1 daydivs\" style=\"background-color:"+bgcolors[i]+"\" data-toggle=\"modal\" data-target=\"#day"+i+"\" >";
	 	str+="<h5>"+djson[i]['Day']+"<br><br>"+djson[i]['Month Date']+"</h5>";
	 	str+="<img src=\""+images+djson[i]['Icon Image']+"\" class=\"daysimg\" >";
	 	str+="<br>Min<br>Temp<h2>"+djson[i]['Min Temp']+"</h2>Max<br>Temp<h2>"+djson[i]['Max Temp']+"</h2></div>"
	 }
	 str+="<div class=\"col-md-2\"><div>";
   for(i=0;i<djson.length;i+=1)
   {
   str+="<div id=\"day"+i+"\" class=\"modal fade\"  role=\"dialog\" style=\"display: none;\">";
   str+="<div class=\"modal-dialog\"><div class=\"modal-content\"><div class=\"modal-header\">";
   str+="<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">×</span></button>";
   str+="<h4 class=\"modal-title\" style=\"font-weight:bold;\">Weather in "+$('#city').val()+" on "+djson[i]['Month Date']+"</h4></div>";
   str+="<div class=\"modal-body\" style=\"text-align:center;\">";
   str+="<img src=\""+images+djson[i]['Icon Image']+"\" title=\""+djson[i]['Summary']+"\" alt=\""+djson[i]['Summary']+"\" class=\"daymodalimage\">";
   str+="<div class=\"col-md-12\"><h3  style=\"font-weight:bold;\">"+djson[i]['Day']+": <span style=\"color:orange\">"+djson[i]['Summary']+"</span></h3></div>";
   str+="<div class=\"row\"><div class=\"col-md-4\"><h4 style=\"font-weight:bold;\">Sunrise Time</h4>"+djson[i]['Sunrise']+"</div><div class=\"col-md-4\"><h4 style=\"font-weight:bold;\">Sunset Time</h4>"+djson[i]['Sunset']+"</div><div class=\"col-md-4\"><h4 style=\"font-weight:bold;\">Humidity</h4>"+djson[i]['Humidity']+"</div></div>";
   str+="<div class=\"row\"><div class=\"col-md-4\"><h4 style=\"font-weight:bold;\">Wind Speed</h4>"+djson[i]['Wind Speed']+"</div><div class=\"col-md-4\"> <h4 style=\"font-weight:bold;\">Visibility</h4>"+djson[i]['Visibility']+"</div> <div class=\"col-md-4\"><h4 style=\"font-weight:bold;\">Pressure</h4>"+djson[i]['Pressure']+"</div></div>";
   str+="</div><div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button></div>";
   str+="</div></div></div>";
  }
	 $("#next7").html(str);

}

function fbfeed(summary,temp,icon)
{
   var unit;
   if($('input:radio[name=degree]:checked').val()=="us")
      {unit="°F";}
   else
      {unit="°C";}
   var feedname='Current Weather in ' + $("#city").val()+', '+ $("#state").val();

    var feedobj = {
                method: 'feed',
                name: feedname,
                caption: 'WEATHER INFORMATION FROM FORCAST.IO',
                description: summary+ "," + temp + unit,
                picture: images+icon,
                link: 'http://forecast.io'
            };
    FB.init({
      appId      : '1285822098110683',
      xfbml      : true,
      status     : true,
      cookie     : true,
      version    : 'v2.5'
    });

    FB.ui(feedobj, function (response) {
                if (response && !response.error_message) {
                    alert('Posted Successfully');
                } else {
                    alert('Not Posted');
                }
            });  
}
function cleardata()
{
 $("#stateerror").html("");
 $("#cityerror").html("");
 $("#streeterror").html("");
 $("#next24table").html("");
 $("#next7").html("");
 $("#rightnowinfo").html(""); 
 $("#mymap").html("");
 document.getElementById("demo").style.visibility = 'hidden';
 $('a[href="#rightnow"]').tab('show');
}
