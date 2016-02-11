<!DOCTYPE HTML>
<?php 
function converticon($icon,$condition) {
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
$imgsrc='<img src="http://cs-server.usc.edu:45678/hw/hw6/images/'.$icon.'" alt="'.$condition.'" title="'.$condition.'">';
return $imgsrc;
}
function convertwindspeed($speed,$unit){
$speed=round($speed); 
if($unit=='si')
 {return $speed.' mps';}
 return $speed.' mph';
}
function convertvisibility($visib,$unit){
 $visib=round($visib);
 if($unit=='si')
 {return $visib.' km';}
 return $visib.' mi';
}
function convertprecep($precepint,$unit){
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
 if($unit=='si')
 {return $value.=' &degC';}
  return $value.=' &degF';  
    
}
function convertTime($timestamp,$timezone){
 date_default_timezone_set($timezone);
 return date("h:i A",$timestamp);
}
function outputForecast($saddress,$city,$state,$sunit)
{
$googleurl= "https://maps.googleapis.com/maps/api/geocode/xml?key=AIzaSyDISWh9GHBOX2VYSgHw0969f-G6ShYYpMQ&address=";
$address =$saddress.','.$city.','.$state;
$geocodeurl=$googleurl.urlencode($address);
try{$response_xml_data = @file_get_contents($geocodeurl);}
catch(Exception $e){echo '<script language="javascript">';
 echo 'alert("',$e->getMessage(),'")';
 echo '</script>';
 return;}
if($response_xml_data==FALSE)
{echo '<script language="javascript">';
 echo 'alert("Fetching address failed.Please try again!")';
 echo '</script>';
 return;   
}
$simplexml=simplexml_load_string($response_xml_data);
$lats =$simplexml->xpath('//location/lat');
$lngs =$simplexml->xpath('//location/lng');
$formataddress=$simplexml->xpath('//status');
 if(count($lats)==0||count($lngs)==0||strpos($formataddress[0],'ZERO_RESULTS')==true)
{echo '<div id="forecast" class="diverror">';
 echo "<h2>No results returned!</h2>\n <h2>Please try another address.</h2>";
 echo '</div>';
 return;
 }   
$lat=$lats[0];
$lng=$lngs[0];
$forecasturl= "https://api.forecast.io/forecast/f2f049b51c218c7578dfccf9b55c2b08/";
$urlpram=$lat.','.$lng.'?units='.$sunit.'&exclude=flags';
$forecasturl.=$urlpram;
try{$response_xml_data = @file_get_contents($forecasturl);}
catch(Exception $e){echo '<script language="javascript">';
 echo 'alert("',$e->getMessage(),'")';
 echo '</script>';
 return;}
if($response_xml_data==FALSE)
{echo '<script language="javascript">';
 echo 'alert("Fetching weather forcast failed.Please try again!")';
 echo '</script>';
 return;   
}
$jsonObject=json_decode($response_xml_data, true);
$wCondition=$jsonObject['currently']['summary'];
$wTemp=convertTemp($jsonObject['currently']['temperature'],$sunit);
$wicon=converticon($jsonObject['currently']['icon'],$wCondition);
$wprecp=convertprecep($jsonObject['currently']['precipIntensity'],$sunit);
$wprecprob=convertpercernt($jsonObject['currently']['precipProbability']);
$wwspeed=convertwindspeed($jsonObject['currently']['windSpeed'],$sunit);
$wdewpoint=round($jsonObject['currently']['dewPoint']);
$whumid=convertpercernt($jsonObject['currently']['humidity']);
if(isset($jsonObject['currently']['visibility']))
    $wvisib=convertvisibility($jsonObject['currently']['visibility'],$sunit);
else
   $wvisib=convertvisibility(0,$sunit);
$wsunrise=convertTime($jsonObject['daily']['data'][0]['sunriseTime'],$jsonObject['timezone']);
$wsunset=convertTime($jsonObject['daily']['data'][0]['sunsetTime'],$jsonObject['timezone']);
echo '<div id="forecast" class="divtable"><h2>'.$wCondition.'</h2><h2>'.$wTemp.'</h2>'.$wicon;
echo '<table><tr><td>Precipitation:</td><td>'.$wprecp.'</td></tr>';
echo '<tr><td>Chance of Rain:</td><td>'.$wprecprob.'</td></tr>';
echo '<tr><td>Wind Speed:</td><td>'.$wwspeed.'</td></tr>';
echo '<tr><td>Dew Point:</td><td>'.$wdewpoint.'</td></tr>';
echo '<tr><td>Humidity:</td><td>'.$whumid.'</td></tr>';
echo '<tr><td>Visibility:</td><td>'.$wvisib.'</td></tr>';
echo '<tr><td>Sunrise:</td><td>'.$wsunrise.'</td></tr>';
echo '<tr><td>Sunset:</td><td>'.$wsunset.'</td></tr></table></div>';
}
function checkSelected($val){
    if (isset($_POST[ 'state']))
        {if($_POST[ 'state']==$val)
         echo "selected";
        }
}
?>
    <html>

    <head>
        <title>Forecast Search</title>
        <style>
            body {
                text-align: center;
                padding-top: 40px;
            }
            
            .divform {
                margin: auto;
                padding: 10px;
                width: 350px;
                height: 180px;
                border: 2px solid black;
            }
            
            .divtable {
                margin: auto;
                margin-top: 40px;
                padding: 10px;
                width: 450px;
                height: 380px;
                border: 2px solid black;
            }
            .diverror {
                margin: auto;
                margin-top: 40px;
                padding: 20px;
                width: 450px;
                height:100  px;
                border: 2px solid black;
            }
            
            form {
                display: table;
            }
            
            h2 {
                size: 10px;
                margin: 0px;
                margin-bottom: 5px;
            }
            
            img {
                width: 100px;
                height: 100px;
            }
            
            input,
            select {
                display: table-column;
                margin-left: 5px;
                margin-bottom: 5px;
            }
            
            p {
                display: table-row;
                text-align: left;
                padding: 10px;
            }
            
            label {
                display: table-cell;
                text-align: left;
            }
            
            table {
                margin: 0 auto;
                text-align: left;
            }
            
            td {
                padding-left: 50px;
                width: 150px;
            }
        </style>
        <script>
            function validparameters() {
                var myform = document.getElementById("myform");
                var isaddress = myform.saddress.value;
                if (!isaddress.match(/\S/)) {
                    alert("Please enter a value for Street Address.");
                    return false;
                }
                var icity = myform.city.value;
                if (!icity.match(/\S/)) {
                    alert("Please enter a value for City");
                    return false;
                }
                var istate = myform.state.value;
                if (istate == "NIL") {
                    alert("Please select a State from dropbox.");
                    return false;
                }
                return true;
            }

            function clearform() {
                var myform = document.getElementById("myform");
                myform.saddress.value = "";
                myform.city.value = "";
                myform.state.value = "NIL";
                myform.degree.value = "us";
                var fordiv = document.getElementById("forecast");
                fordiv.innerHTML = "";
                fordiv.style.border = "thick solid white";
            }
        </script>
    </head>

    <body>
        <h1>Forecast Search</h1>
        <div class="divform">
            <form id="myform" method="POST" onsubmit="return validparameters()" action="<?php echo
htmlspecialchars($_SERVER["PHP_SELF"]);?>" autocomplete="off">
                <p>
                    <label>Street Address:*</label>
                    <input  type="text" name="saddress" maxlength="50" value="<?php if(isset($_POST['saddress'])) { echo $_POST['saddress']; }?>">
                </p>
                <p>
                    <label>City:*</label>
                    <input type="text" name="city" maxlength="50" value="<?php if(isset($_POST['city'])) { echo $_POST['city']; }?>">
                </p>
                <p>
                    <label>State:*</label>
                    <select name="state">
                        <option value="NIL" <?php checkSelected( "NIL") ?>>Select your state..</option>
                        <option value="AL" <?php checkSelected( "AL") ?>>Alabama</option>
                        <option value="AK" <?php checkSelected( "AK") ?>>Alaska</option>
                        <option value="AZ" <?php checkSelected( "AZ") ?>>Arizona</option>
                        <option value="AR" <?php checkSelected( "AR") ?>>Arkansas</option>
                        <option value="CA" <?php checkSelected( "CA") ?>>California</option>
                        <option value="CO" <?php checkSelected( "CO") ?>>Colorado</option>
                        <option value="CT" <?php checkSelected( "CT") ?>>Connecticut</option>
                        <option value="DE" <?php checkSelected( "DE") ?>>Delaware</option>
                        <option value="DC" <?php checkSelected( "DC") ?>>District Of Columbia</option>
                        <option value="FL" <?php checkSelected( "FL") ?>>Florida</option>
                        <option value="GA" <?php checkSelected( "GA") ?>>Georgia</option>
                        <option value="HI" <?php checkSelected( "HI") ?>>Hawaii</option>
                        <option value="ID" <?php checkSelected( "ID") ?>>Idaho</option>
                        <option value="IL" <?php checkSelected( "IL") ?>>Illinois</option>
                        <option value="IN" <?php checkSelected( "IN") ?>>Indiana</option>
                        <option value="IA" <?php checkSelected( "IA") ?>>Iowa</option>
                        <option value="KS" <?php checkSelected( "KS") ?>>Kansas</option>
                        <option value="KY" <?php checkSelected( "KY") ?>>Kentucky</option>
                        <option value="LA" <?php checkSelected( "LA") ?>>Louisiana</option>
                        <option value="ME" <?php checkSelected( "ME") ?>>Maine</option>
                        <option value="MD" <?php checkSelected( "MD") ?>>Maryland</option>
                        <option value="MA" <?php checkSelected( "MA") ?>>Massachusetts</option>
                        <option value="MI" <?php checkSelected( "MI") ?>>Michigan</option>
                        <option value="MN" <?php checkSelected( "MN") ?>>Minnesota</option>
                        <option value="MS" <?php checkSelected( "MS") ?>>Mississippi</option>
                        <option value="MO" <?php checkSelected( "MO") ?>>Missouri</option>
                        <option value="MT" <?php checkSelected( "MT") ?>>Montana</option>
                        <option value="NE" <?php checkSelected( "NE") ?>>Nebraska</option>
                        <option value="NV" <?php checkSelected( "NV") ?>>Nevada</option>
                        <option value="NH" <?php checkSelected( "NH") ?>>New Hampshire</option>
                        <option value="NJ" <?php checkSelected( "NJ") ?>>New Jersey</option>
                        <option value="NM" <?php checkSelected( "NM") ?>>New Mexico</option>
                        <option value="NY" <?php checkSelected( "NY") ?>>New York</option>
                        <option value="NC" <?php checkSelected( "NC") ?>>North Carolina</option>
                        <option value="ND" <?php checkSelected( "ND") ?>>North Dakota</option>
                        <option value="OH" <?php checkSelected( "OH") ?>>Ohio</option>
                        <option value="OK" <?php checkSelected( "OK") ?>>Oklahoma</option>
                        <option value="OR" <?php checkSelected( "OR") ?>>Oregon</option>
                        <option value="PA" <?php checkSelected( "PA") ?>>Pennsylvania</option>
                        <option value="RI" <?php checkSelected( "RI") ?>>Rhode Island</option>
                        <option value="SC" <?php checkSelected( "SC") ?>>South Carolina</option>
                        <option value="SD" <?php checkSelected( "SD") ?>>South Dakota</option>
                        <option value="TN" <?php checkSelected( "TN") ?>>Tennessee</option>
                        <option value="TX" <?php checkSelected( "TX") ?>>Texas</option>
                        <option value="UT" <?php checkSelected( "UT") ?>>Utah</option>
                        <option value="VT" <?php checkSelected( "VT") ?>>Vermont</option>
                        <option value="VA" <?php checkSelected( "VA") ?>>Virginia</option>
                        <option value="WA" <?php checkSelected( "WA") ?>>Washington</option>
                        <option value="WV" <?php checkSelected( "WV") ?>>West Virginia</option>
                        <option value="WI" <?php checkSelected( "WI") ?>>Wisconsin</option>
                        <option value="WY" <?php checkSelected( "WY") ?>>Wyoming</option>
                    </select>
                </p>
                <p>
                    <label>Degree:*</label>
                    <input <?php if (!isset($_POST[ 'degree'])) echo "checked"; else if ($_POST[ 'degree']=="us" )echo "checked"; ?> type="radio" name="degree" value="us">Fahrenheit
                    <input <?php if (isset($_POST[ 'degree']) && $_POST[ 'degree']=="si" ) echo "checked";?> type="radio" name="degree" value="si">Celsius</p>
                <p>
                    <label></label>
                    <input type="submit" name="submit" value="Search">
                    <input type="button" value="Clear" onclick="clearform();">
                </p>
            </form>
            <p>
                <label>*-<i>Mandatory Fields</i></label>
            </p>
            <a href="http://forecast.io/">Powered by Forecast.io</a>
        </div>
        <?php if(isset($_POST["submit"])) 
                if(isset($_POST[ 'saddress'])&&isset($_POST[ 'city'])&&isset($_POST[ 'state'])&&isset($_POST[ 'degree']))
                    { 
                        outputForecast($_POST[ 'saddress'],$_POST[ 'city'],$_POST[ 'state'],$_POST[ 'degree']);  
                    }
        ?>
    </body>

    </html>