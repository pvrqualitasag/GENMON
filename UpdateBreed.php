<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<link rel="stylesheet" href="style.css" media="screen"/>
<!-- This page is used to update the general information about a breed -->
<?php
include("header.php");
include("connectDataBase.php");
$dbh=db_connect();
if(isset($_POST['breed_id'])){
  $breed_id=$_POST['breed_id'];
}
?>
<body>
<div id="page">
Welcome to the update page.You are updating information for the breed:</br><h1>
<?php
if(isset($_POST['breed_id'])){
  $sql_name="select long_name, short_name from codes where db_code=".$breed_id."";
  $name0=pg_query($sql_name);
  $long_name=pg_fetch_result($name0,0,0);
  $short_name=pg_fetch_result($name0,0,1);
  echo $long_name." (".$short_name.")";
}
?>
</h1><br/><br/>
<form action="GenAnimalUpdate.php" method="post" enctype="multipart/form-data">
<h2>General information about the breed:</h2></br>
Does the breed have a cultural value </br>
<input type="radio" name="cultural_value" value="1">yes</option> </br>
<input type="radio" name="cultural_value" value="0">no</option> </br></br>
Does the cultural value of the breed decreased in the recent past </br>
<input type="radio" name="cultural_value_trend" value="0">yes</option> </br>
<input type="radio" name="cultural_value_trend" value="1">no</option> </br></br>
Please give the approximate number of farms <input type="number" name="number_farm"/></br>
Please give the approximate number of farms 5 years ago<input type="number" name="number_farm_past"/></br></br>
Does the breed have cryo-conserved semen </br>
<input type="radio" name="frozen_semen" value="1">yes</option> </br>
<input type="radio" name="frozen_semen" value="0">no</option> </br></br>
Does the breed have a cryo-conservation management plan? </br>
<input type="radio" name="cryo_plan" value="1">yes</option> </br>
<input type="radio" name="cryo_plan" value="0">no</option> </br></br>

<input type="hidden" name="breed_id" value="<?php echo $breed_id?>">
<input type="submit" value="Update breed information" /> </form></br> </br> </br> </br> </br> 
<?php
db_disconnect($dbh);
include("footer.php");
?>
</div>
</body>
</html>
