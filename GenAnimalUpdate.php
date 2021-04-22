<?php
// set debugging flag
$debug=FALSE;
// Logging
if ($debug){
  include("logger.php");
  # logging
  // Logging class initialization
  $log = new Logging();

  // set path and name of log file (optional)
  $date=date('YmdHis');
  $log->lfile('/tmp/' . $date . '_mylog_GenAnimalUpdate.log');

  // write message to the log file
  $log->lwrite(' * Starting GenAnimalUpdate.php ...');
}
//This page updates information about a breed (UpdateBreed.php)
include("FunctionsCalcIndex.php");
include("connectDataBase.php");
$dbh=db_connect();
if(isset($_POST['breed_id'])){
  $breed_id=$_POST['breed_id'];
}
if ($debug){$log->lwrite(' * Breed ID: ' . $breed_id);}
//Change the information depending on what the user has entered
if(isset($_POST['cultural_value']) && isset($_POST['cultural_value_trend']) && isset($_POST['number_farm']) && isset($_POST['number_farm_past']) && isset($_POST['frozen_semen']) && isset($_POST['cryo_plan'])){
	$cultural_score=($_POST['cultural_value']+$_POST['cultural_value_trend'])/2;
	if($_POST['number_farm_past']==0){$_POST['number_farm_past']=1;}
	$farm_trend=($_POST['number_farm']-$_POST['number_farm_past'])/$_POST['number_farm_past']*100;
	$cryo_score=($_POST['frozen_semen']+$_POST['cryo_plan'])/2;
  if ($debug){
    $log->lwrite(' * Cultural value: ' . $_POST['cultural_value']);
    $log->lwrite(' * Cultural trend: ' . $_POST['cultural_value_trend']);
  	$log->lwrite(' * Cultural score: ' . $cultural_score);
  	$log->lwrite(' * Farm number: ' . $_POST['number_farm']);
	  $log->lwrite(' * Farm number past: ' . $_POST['number_farm_past']);
  	$log->lwrite(' * Farm trend: ' . $farm_trend);
  	$log->lwrite(' * Frozen semen: ' . $_POST['frozen_semen']);
	  $log->lwrite(' * Cryo Plan: ' . $_POST['cryo_plan']);
  	$log->lwrite(' * Cryo score: ' . $cryo_score);
  }
}
else{
	header("Location:error.php?error=breed_info"); 
	exit();
}

# update cultural score, farm trend and cryovalue
$sql_update_cultvalue="UPDATE summary SET breed_cultural_value=".$cultural_score." WHERE breed_id=".$breed_id;
$sql_update_farmtrend="UPDATE summary SET breed_num_farms_trend=".round($farm_trend,2)." WHERE breed_id=".$breed_id;
$sql_update_cryovalue="UPDATE summary SET cryo_cons=".$cryo_score." WHERE breed_id=".$breed_id;
if ($debug){
  $log->lwrite(' * SQL Update cultvalue' . $sql_update_cultvalue);
  $log->lwrite(' * SQL Update farmtrend' . $sql_update_farmtrend);
  $log->lwrite(' * SQL Update cryovalue' . $sql_update_cryovalue);
}
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
if ($debug){
  $log->lwrite(' * Owner: ' . $owner);
  $log->lwrite(' * Species: ' . $species);
  $log->lwrite(' * User: ' . $_SESSION['user']);
}

# determine the year that is used to compute the IndexSocioEcPLZ
#$sql_year="SELECT cast(substring(table_name, 13,4) as integer) as table_name2 FROM information_schema.tables WHERE table_schema='public' and table_name like 'plz_socioec_%' order by table_name2 desc limit 1" ; //select the most recent year for which we have a plz_socioec table
#$res_year=pg_query($sql_year);
#$year=pg_fetch_result($res_year,0,0);
#if ($debug){ $log->lwrite(' * Year to compute socio-econ index: ' . $year); }

# call different version of the index functions, depending on logging-state
if ($debug){
  $index_demo=LoggedIndexCalc($breed_id,'demo', $owner, $species, $debug, $log); //FunctionsCalcIndex.php
  $index_final=LoggedIndexCalc($breed_id,'final', $owner, $species, $debug, $log); //FunctionsCalcIndex.php  
  #LoggedIndexSocioEcPLZ($year,  $owner, $debug, $log);
  $log->lwrite(' * Compute index_demo: ' . $index_demo);
  $log->lwrite(' * Computed index_final: ' . $index_final);
} else {
  $index_demo=IndexCalc($breed_id,'demo', $owner, $species); //FunctionsCalcIndex.php
  $index_final=IndexCalc($breed_id,'final', $owner, $species); //FunctionsCalcIndex.php
  #IndexSocioEcPLZ($year, $owner);
}

# disconnect from db
db_disconnect($dbh);
# // close log file
if ($debug){$log->lclose();}
$_SESSION['breed_id']=$breed_id;
header("Location:index.php");
?>
