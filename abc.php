<?php
session_start();
if (!isset($_SESSION['user'])) {
	header("location:index.php?error=login_required");
}
$username = $_SESSION['user'];
$login_info = $_SESSION['login_info'];
$user_designation = $_SESSION['designation'];
 $dropdown_disable = "";
//Redirect to LeadLanding is the user is not a QAEngineer
 if($user_designation == "QAEngineer") {
  $dropdown_disabled = "disabled" ;
 }
if(isset($_GET['username_passed'])) {
	$username = $_GET['username_passed'];
}


$dbhost = 'localhost';
$dbuser = 'root';
$conn = mysql_connect($dbhost, $dbuser);
if(!$conn )
{
  die('Could not connect: ' . mysql_error());
}
$sql = "SELECT * FROM user_info where username = 	'$username';";

mysql_select_db('DASHBOARDDB');
$result = mysql_query( $sql, $conn );

if(! $result )
{
  die('Could not get data: ' . mysql_error());
}
//Get user info
$chooseDropdown = "";
while($row = mysql_fetch_array($result))
  {
  $name =  $row['name'];
  $username = $row['username'];
  $user_passed_designation = $row['designation'];
 }

 
//Get productivity from database 
$sql = "SELECT sum(tc_create_count) as total_tc, sum(tc_create_hours) as total_hours,sum(tc_execute_count) as total_tc_exec, sum(tc_execute_hours) as total_hours_exec  FROM qa_productivity where username = '$username';";

mysql_select_db('DASHBOARDDB');
$result = mysql_query( $sql, $conn );

if(! $result )
{
  die('Could not get data: ' . mysql_error());
}
//Get user info
while($row = mysql_fetch_array($result))
  {
  $tc_create_count =  $row['total_tc'];
  $tc_create_hours = $row['total_hours'];
  $tc_execute_count =  $row['total_tc_exec'];
  $tc_execute_hours = $row['total_hours_exec'];
   
 }
$creation_productivity = $tc_create_count/$tc_create_hours;
$creation_productivity =  round($creation_productivity,2);
$execution_productivity = $tc_execute_count/$tc_execute_hours;
$execution_productivity =  round($execution_productivity,2);
$sql_total_hours = "SELECT count(distinct (date)) as sum_hours  FROM qa_productivity where username = '$username';";
mysql_select_db('DASHBOARDDB');
$result = mysql_query( $sql_total_hours, $conn );
//Get user info
while($row = mysql_fetch_array($result))
  {
  $sum_hours =  $row['sum_hours'] * 40;
   }
 $effective_util = ($tc_create_hours + $tc_execute_hours)/($sum_hours);
 $effective_util =  round($effective_util,2) * 100;

//Get productivity trend 
$trend_sql = "SELECT qp.date,display_date, tc_create_count FROM qa_productivity qp, wsr_dates wd where qp.date = wd.date and username = '$username' order by qp.date asc;";

mysql_select_db('DASHBOARDDB');
$trend_result = mysql_query( $trend_sql, $conn );
$ticks = "[";
$tc_created = "[";
while($row = mysql_fetch_array($trend_result)){
	$week = $row['display_date'];
	$tc_count = $row['tc_create_count'];
	$ticks = $ticks . "'" . $week . "'," ;
	//$tc_created = $tc_created . "'" . $tc_count . "'," ;
	$tc_created = $tc_created . $tc_count . "," ;
}
$ticks = rtrim($ticks, ",");
$ticks = $ticks . "]";
$tc_created = rtrim($tc_created, ",");
$tc_created = $tc_created . "]";
mysql_close($conn);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Track productivity</title>
<link rel="stylesheet" type="text/css" href="css/view.css" media="all">
<script type="text/javascript" src="js/dashboard.js"></script>

<!--Includes for JQPLOT-->
<script language="javascript" type="text/javascript" src="js/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="js/plugins/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="js/plugins/jqplot.cursor.min.js"></script>
<script type="text/javascript" src="js/plugins/jqplot.dateAxisRenderer.min.js"</script>
<script type="text/javascript" src="js/plugins/jqplot.pieRenderer.min.js"></script>
<script type="text/javascript" src="js/plugins/jqplot.donutRenderer.min.js"></script>
<script type="text/javascript" src="js/plugins/jqplot.barRenderer.min.js"></script>
<script type="text/javascript" src="js/plugins/jqplot.canvasTextRenderer.min.js"></script>
<script type="text/javascript" src="js/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>
<script type="text/javascript" src="js/plugins/jqplot.categoryAxisRenderer.min.js"></script>



<script type="text/javascript" src="js/plugins/jqplot.pointLabels.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery.jqplot.css" />
</head>
<body id="main_body" >
	<!--Top shadow image-->
	<img id="top" src="images/top.png" alt="">
	<!--Body here-->
	<div id="form_container" style="overflow:hidden">
		<!--Header-->
		<div id="snapon_logo"><img src="images/snapon_logo.png"></div>
		
		<!--Form-->
		<form id="update_profile" class="appnitro appnitrooverride"  method="post" action="profileUpdatedConfirmation.php">
			<!--Internal container-->
			<div class="form_description">
				<!--Home/LoggOff button-->
				<div class="floatright">
					<a href="dashboardLanding.php" class="button green medium" >Home</a> <a href="/logOff.php" class="button orange medium" id="logoff">Logoff</a>
				</div>
				<!--HPage Heading-->
				<h2><?php echo $name ?></h2>
				<!--<p>
					This is a dummy text
				</p>-->
			</div>
			<div class="form_description">
			<!--TODO: Functionality to add user image-->
			<!--input type="image" src="images/profile.png" alt=""/-->
				<!--HPage Heading-->
				<ul>
				<div style="width:180px; border-bottom:1px dotted #ccc; margin:5px; padding-bottom:10px; font-weight:bold;">Test case creation</div>
					<li>
						<label class="productivity">Created:</label>
						<?php echo $tc_create_count ?>
						<br/>
					</li>
					<li>
						<label class="productivity">Hours:</label>
						<?php echo $tc_create_hours ?>
						<br/>
					</li>
					<li>
						<label class="productivity">Average Productivity:</label>
						<?php echo $creation_productivity ?> TC/hr
						<br/>
					</li>
					<li>
						<label class="productivity">Average Weekly Productivity:</label>
						<?php echo $creation_productivity*8*5 ?> TC/week
						<br/>
					</li>					
					<br/><br/>
				<div style="width:180px; border-bottom:1px dotted #ccc; margin:5px; padding-bottom:10px; font-weight:bold;">Test case execution</div>
					<li>
						<label class="productivity">Executed:</label>
						<?php echo $tc_execute_count ?>
						<br/>
					</li>
					<li>
						<label class="productivity">Hours:</label>
						<?php echo $tc_execute_hours ?>
						<br/>
					</li>
					<li>
						<label class="productivity">Average Productivity:</label>
						<?php echo $execution_productivity ?>
						<br/>
					</li>
				<br/><br/>
				<div style="width:180px; border-bottom:1px dotted #ccc; margin:5px; padding-bottom:10px; font-weight:bold;">Utilization</div>					
					<li>
						<label class="productivity">Budgeted hours:</label>
						<?php echo $sum_hours ?>
						<br/>
					</li>
					<li>
						<label class="productivity">Effective utilization:</label>
						<?php echo $effective_util ?>%
						<br/>
					</li>				
					<input type='hidden' id='transaction' name='transaction' value='update' />
				</ul>
				<br/>
				<div id="graphs">
					<h3><b>Productivity Trend (Test case creation - week on week)</b></h3>
					<div id="chart1" style="height:500px;width:600px;float:left;"></div>
				</div>	
			</div>
		</form>
		<!--Preload the dropdowns-->
		<script type="text/javascript">
			<?php echo $chooseDropdown ?>
			$(document).ready(function(){
  var line1 = [['Cup Holder Pinion Bob', 7], ['Generic Fog Lamp', 9], ['HDTV Receiver', 15], 
  ['8 Track Control Module', 12], [' Sludge Pump Fourier Modulator', 3], 
  ['Transcender/Spice Rack', 6], ['Hair Spray Danger Indicator', 18]];
 
  var plot1 = $.jqplot('chart1', [line1], {
    title: 'Concern vs. Occurrance',
    series:[{renderer:$.jqplot.BarRenderer}],

    axes: {
      xaxis: {
        renderer: $.jqplot.CategoryAxisRenderer,
		        tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
        tickOptions: {
          angle: 90,
          fontSize: '10pt'
        }
      }
    }
  });
});
		</script>
		<div id="footer">
			<a href="mailto:kriti.mehta@snapon.com?subject=Bug/feature in Donut Page">Report issues/Suggest feature</a>
		</div>
	</div>
	<img id="bottom" src="images/bottom.png" alt="">
</body>
</html>