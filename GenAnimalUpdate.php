<?php
// Logging
include("logger.php");
# logging
// Logging class initialization
$log = new Logging();

// set path and name of log file (optional)
$date=date('YmdHis');
$log->lfile('/tmp/' . $date . '_mylog_GenAnimalUpdate.log');

// write message to the log file
$log->lwrite(' * Starting GenAnimalUpdate.php ...');

//This page updates information about a breed (UpdateBreed.php)
include("connectDataBase.php");
$dbh=db_connect();
if(isset($_POST['breed_id'])){
  $breed_id=$_POST['breed_id'];
}
$log->lwrite(' * Breed ID: ' . $breed_id);
//Change the information depending on what the user has entered
if(isset($_POST['cultural_value']) && isset($_POST['cultural_value_trend']) && isset($_POST['number_farm']) && isset($_POST['number_farm_past']) && isset($_POST['frozen_semen']) && isset($_POST['cryo_plan'])){
  $log->lwrite(' * Cultural value: ' . $_POST['cultural_value']);
  $log->lwrite(' * Cultural trend: ' . $_POST['cultural_value_trend']);
	$cultural_score=($_POST['cultural_value']+$_POST['cultural_value_trend'])/2;
	$log->lwrite(' * Cultural score: ' . $cultural_score);
	if($_POST['number_farm_past']==0){$_POST['number_farm_past']=1;}
	$log->lwrite(' * Farm number: ' . $_POST['number_farm']);
	$log->lwrite(' * Farm number past: ' . $_POST['number_farm_past']);
	$farm_trend=($_POST['number_farm']-$_POST['number_farm_past'])/$_POST['number_farm_past']*100;
	$log->lwrite(' * Farm trend: ' . $farm_trend);
	$log->lwrite(' * Frozen semen: ' . $_POST['frozen_semen']);
	$log->lwrite(' * Cryo Plan: ' . $_POST['cryo_plan']);
	$cryo_score=($_POST['frozen_semen']+$_POST['cryo_plan'])/2;
	$log->lwrite(' * Cryo score: ' . $cryo_score);
}
else{
	header("Location:error.php?error=breed_info"); 
	exit();
}

# update cultural score, farm trend and cryovalue
$sql_update_cultvalue="UPDATE summary SET breed_cultural_value=".$cultural_score." WHERE breed_id=".$breed_id;
$log->lwrite(' * SQL Update cultvalue' . $sql_update_cultvalue);
$sql_update_farmtrend="UPDATE summary SET breed_num_farms_trend=".round($farm_trend,2)." WHERE breed_id=".$breed_id;
$log->lwrite(' * SQL Update farmtrend' . $sql_update_farmtrend);
$sql_update_cryovalue="UPDATE summary SET cryo_cons=".$cryo_score." WHERE breed_id=".$breed_id;
$log->lwrite(' * SQL Update cryovalue' . $sql_update_cryovalue);
pg_query($sql_update_cultvalue);
pg_query($sql_update_farmtrend); 
pg_query($sql_update_cryovalue);

# update indices
$sql_owner="SELECT owner FROM summary where breed_id=".$breed_id.""; 
$result_owner=pg_query($sql_owner);
$owner=pg_fetch_result($result_owner, 0, 0);
$sql_species="SELECT species FROM summary where breed_id=".$breed_id.""; 
$result_species=pg_query($sql_species);
$species=pg_fetch_result($result_species, 0, 0);
$log->lwrite(' * Owner: ' . $owner);
$log->lwrite(' * Species: ' . $species);
if(isset($_SESSION['user']) && $_SESSION['user']==$owner){
  $index_demo=IndexCalc($breed_id,'demo', $_SESSION['user'], $species); //FunctionsCalcIndex.php
  $index_final=IndexCalc($breed_id,'final', $_SESSION['user'], $species); //FunctionsCalcIndex.php
  $log->lwrite(' * Compute index_demo: ' . $index_demo);
  $log->lwrite(' * Computed index_final: ' . $index_final);
}


# disconnect from db
db_disconnect($dbh);
# // close log file
$log->lclose();
$_SESSION['breed_id']=$breed_id;
header("Location:index.php");
?>
