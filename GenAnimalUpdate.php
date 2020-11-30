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
//Change the information depending on what the user has entered
if(isset($_POST['cultural_value']) && isset($_POST['cultural_value_trend']) && isset($_POST['number_farm']) && isset($_POST['number_farm_past']) && isset($_POST['frozen_semen']) && isset($_POST['cryo_plan'])){
	$cultural_score=($_POST['cultural_value']+$_POST['cultural_value_trend'])/2;
	$log->lwrite(' * Cultural Score: ' . $cultural_score);
	if($_POST['number_farm_past']==0){$_POST['number_farm_past']=1;}
	$farm_trend=($_POST['number_farm']-$_POST['number_farm_past'])/$_POST['number_farm_past']*100;
	$log->lwrite(' * Farm trend: ' . $farm_trend);
	$cryo_score=($_POST['frozen_semen']+$_POST['cryo_plan'])/2;
	$log->lwrite(' * Cryo score: ' . $cryo_score);
}
else{
	header("Location:error.php?error=breed_info"); 
	exit();
}

$sql_update_cultvalue="UPDATE summary SET breed_cultural_value=".$cultural_score." WHERE breed_id=".$breed_id;
$log->lwrite(' * SQL Update cultvalue' . $sql_update_cultvalue);
$sql_update_farmtrend="UPDATE summary SET breed_num_farms_trend=".round($farm_trend,2)." WHERE breed_id=".$breed_id;
$log->lwrite(' * SQL Update farmtrend' . $sql_update_farmtrend);
$sql_update_cryovalue="UPDATE summary SET cryo_cons=".$cryo_score." WHERE breed_id=".$breed_id;
$log->lwrite(' * SQL Update cryovalue' . $sql_update_cryovalue);
pg_query($sql_update_cultvalue);
pg_query($sql_update_farmtrend); 
pg_query($sql_update_cryovalue);
# // close log file
$log->lclose();
$_SESSION['breed_id']=$breed_id;
header("Location:index.php");
?>
