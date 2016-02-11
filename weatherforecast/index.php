<?php 
header("Access-Control-Allow-Origin: *");
function converticon($icon) {
if($icon=='clear-day')
$icon='clear.png';
else if($icon=='clear-night')
$icon='clear_night.png';
else if($icon=='rain')
$icon='rain.png';
else if($icon=='snow')
$icon='snow.png';
else if($icon=='sleet')
$icon='sleet.png';
else if($icon=='wind')
$icon='wind.png';    
else if($icon=='fog')
$icon='fog.png';
else if($icon=='cloudy')
$icon='cloudy.png';
else if($icon=='partly-cloudy-day')
$icon='cloud_day.png';
else if($icon=='partly-cloudy-night')
$icon='cloud_night.png';
else
$icon ='';
//$imgsrc='http://cs-server.usc.edu:45678/hw/hw8/images/'.$icon;
return $icon;
}
function convertwindspeed($speedv,$unit){
if(isset($speedv['windSpeed']))
	$speed=$speedv['windSpeed'];
else
 return 'N.A';
$speed=round($speed); 
if($unit=='si')
 {return $speed.' m/s';}
 return $speed.' mph';
}
function convertvisibility($visib,$unit){
if(isset($visib['visibility']))
 $visibility=$visib['visibility'];
else
 return 'N.A';
 $visibility=round($visibility);
 if($unit=='si')
 {return $visibility.' km';}
 return $visibility.' mi';
}
function convertprecep($precepintv,$unit){
if(isset($precepintv['precipIntensity']))
	$precepint=$precepintv['precipIntensity'];
else
 return 'N.A';
if($unit=='si')
  $precepint=$precepint*0.0393701; 
if($precepint==0)
 {return 'None';}
 else if($precepint<=0.002)
 {return 'Very Light';}
 else if($precepint<=0.017)
 {return 'Light';}
 else if($precepint<=0.01)
 {return 'Moderate”';}
 else 
 {return 'Heavy';}
}
function convertpercernt($value){
    $value*=100;
    return $value.'%';
}
function convertTemp($value,$unit){
 $value=round($value);
  return $value.='&deg';  
}
function converttunit($unit){
 //$value=round($value);
 if($unit=='si')
 {return '&degC';}
  return '&degF';  
}
function convertTime($timestamp,$timezone){
 date_default_timezone_set($timezone);
 return date("h:i A",$timestamp);
}
function convertpressure($visib,$unit){
 if($unit=='si')
 {return $visib.' hPa';}
 return $visib.' mb';
}
function getcurrent($result,$unit)
{	

    $table = array(
    'Precipitation'=>convertprecep($result['currently'],$unit),
    'Chance of Rain'=>convertpercernt($result['currently']['precipProbability']),
    'Wind Speed'=>convertwindspeed($result['currently'],$unit),
    'Dew Point'=>$result['currently']['dewPoint'].converttunit($unit),
    'Humidity'=>convertpercernt($result['currently']['humidity']),
    'Visibility'=>convertvisibility($result['currently'],$unit),
    'Sunrise'=>convertTime($result['daily']['data'][0]['sunriseTime'],$result['timezone']),
    'Sunset'=>convertTime($result['daily']['data'][0]['sunsetTime'],$result['timezone']),
	);
	$mobj= array('icon' =>converticon($result['currently']['icon']) , 
		'summary' => $result['currently']['summary'],
		'temp'=> round($result['currently']['temperature']),
		'unit'=>converttunit($unit),
		'tempmin'=>round($result['daily']['data'][0]['temperatureMin']).'&deg',
		'tempmax'=>round($result['daily']['data'][0]['temperatureMax']).'&deg',
		'lat'=>$result['latitude'],
		'long'=>$result['longitude'],
		'table'=> $table
		);
	return $mobj;
 
}
function gethourly($result,$unit)
{
  $harray=$result['hourly']['data'];
  $hobj=[];
  for ($i = 0; $i < count($harray)&& $i<24; ++$i) {
      $exdata= array('Wind Speed'=>convertwindspeed($harray[$i],$unit),
    'Humidity'=>convertpercernt($harray[$i]['humidity']),
    'Visibility'=>convertvisibility($harray[$i],$unit),
    'Pressure'=>convertpressure($harray[$i]['pressure'],$unit));
       $mdata = array(
    'Time'=>convertTime($harray[$i]['time'],$result['timezone']),
    'Summary'=>converticon($harray[$i]['icon']),
    'Cloud Cover'=>convertpercernt($harray[$i]['cloudCover']),
    'Temp'=> $harray[$i]['temperature']  
	);
     $table = array(
     	'maindata'=>$mdata,
     	'extradata'=>$exdata
     	);
    array_push($hobj, $table);
    }
  return $hobj;
}
function getdaily($result,$unit)
{

 $darray=$result['daily']['data'];
  $dobj=[];
  for ($i = 1; $i < count($darray); ++$i) {
       $table = array(
    'Day'=>date("l",$darray[$i]['time']),
    'Month Date'=>date("M d",$darray[$i]['time']),
    'Icon Image'=>converticon($darray[$i]['icon']),
    'Min Temp'=> convertTemp($darray[$i]['temperatureMin'],$unit),
    'Max Temp'=>convertTemp($darray[$i]['temperatureMax'],$unit),
    'Summary'=>$darray[$i]['summary'],
    'Humidity'=>convertpercernt($darray[$i]['humidity']),
    'Visibility'=>convertvisibility($darray[$i],$unit),
    'Wind Speed'=>convertwindspeed($darray[$i],$unit),
    'Sunrise'=>convertTime($darray[$i]['sunriseTime'],$result['timezone']),
    'Sunset'=>convertTime($darray[$i]['sunsetTime'],$result['timezone']),
    'Pressure'=>convertpressure($darray[$i]['pressure'],$unit),
	);
    array_push($dobj, $table);
    }
  return $dobj;
}
function outputForecast($saddress,$city,$state,$sunit)
{
$googleurl= "https://maps.googleapis.com/maps/api/geocode/xml?key=AIzaSyDISWh9GHBOX2VYSgHw0969f-G6ShYYpMQ&address=";
$address =$saddress.','.$city.','.$state;
$geocodeurl=$googleurl.urlencode($address);
try{$response_xml_data = @file_get_contents($geocodeurl);}
catch(Exception $e){ $err = array('error' => $e->getMessage() );
echo json_encode($err);
return ;}
if($response_xml_data==FALSE)
{$err = array('error' =>"Fetching address failed.Please try again!" );
  echo json_encode($err);
 return;   
}
$simplexml=simplexml_load_string($response_xml_data);
$lats =$simplexml->xpath('//location/lat');
$lngs =$simplexml->xpath('//location/lng');
$formataddress=$simplexml->xpath('//status');
 if(count($lats)==0||count($lngs)==0||strpos($formataddress[0],'ZERO_RESULTS')==true)
{$err = array('error' => "No results returned" );
 return;
 }   
$lat=$lats[0];
$lng=$lngs[0];
$forecasturl= "https://api.forecast.io/forecast/f2f049b51c218c7578dfccf9b55c2b08/";
$urlpram=$lat.','.$lng.'?units='.$sunit.'&exclude=flags';
$forecasturl.=$urlpram;
try{$response_xml_data = @file_get_contents($forecasturl);}
catch(Exception $e){
	$err = array('error' =>$e->getMessage() );
	echo json_encode($err);
 		return;}
if($response_xml_data==FALSE)
{$err = array('error' =>"Fetching address failed.Please try again!" );
echo json_encode($err);
return;   
}
$jsonObject=json_decode($response_xml_data, true);
$formatjson=array('Right Now' => getcurrent($jsonObject,$sunit),
	'Next 24 Hours' =>gethourly($jsonObject,$sunit),
	'Next 7 Days' =>getdaily($jsonObject,$sunit)

 );
echo json_encode($formatjson);
}

if(isset($_POST["submit"])) 
     {        if(isset($_POST[ 'saddress'])&&isset($_POST[ 'city'])&&isset($_POST[ 'state'])&&isset($_POST[ 'degree']))
                    { 
                        outputForecast($_POST[ 'saddress'],$_POST[ 'city'],$_POST[ 'state'],$_POST[ 'degree']);  
                    }
                 else
                 	{
                 		$err = array('error' => "Error in sent data" );
						echo json_encode($err);
                 	}
      }
else if(isset($_GET["submit"])) 
     {        if(isset($_GET[ 'saddress'])&&isset($_GET[ 'city'])&&isset($_GET[ 'state'])&&isset($_GET[ 'degree']))
                    { 
                        outputForecast($_GET[ 'saddress'],$_GET[ 'city'],$_GET[ 'state'],$_GET[ 'degree']);  
                    }
                 else
                 	{
                 		$err = array('error' => "Error in sent data" );
						echo json_encode($err);
                 	}
      }    
  else
  	{
                 		$err = array('error' => "Error on Submit" );
						echo json_encode($err);
    }
?>