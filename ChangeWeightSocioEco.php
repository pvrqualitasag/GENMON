<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<link rel="stylesheet" href="style.css" media="screen"/>
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
  $log->lfile('/tmp/' . $date . '_mylog_ChangeWeightSocioEco.log');

  // write message to the log file
  $log->lwrite(' * Starting ChangeWeightSocioEco.php ...');
}
include("header.php");
$string_error="";
if(isset($_GET["error"])==1){
	if($_GET["error"]=="sum"){
		$string_error="Be careful: the sum of the weight you entered does not equal 1! Please change your weights!";
		if ($debug){ $log->lwrite(' * ERROR-sum -- string_error: ' . $string_error); }
	}
	elseif($_GET["error"]=="threshold"){
		$string_error="Be careful: the satisfaction threshold (t1) and the non-satisfaction threshold (t2) must be different from each other!";
		if ($debug){ $log->lwrite(' * ERROR-threshold -- string_error: ' . $string_error); }
	}
}
?>
<body>
<div id="page">
<?php
if ($debug){ $log->lwrite(' * Begin of page-body ... '); }
include("connectDataBase.php");
$dbh=db_connect();
include("FunctionsChangeWeight.php");
$table_name="thres_weight";
if(isset($_GET["error"])==1){
	if($_GET["error"]=="sum"){
		$string_error="Be careful: the sum of the weight you entered does not equal 1! Please change your weights!";
		if ($debug){ $log->lwrite(' * ERROR-sum -- written to page - string_error: ' . $string_error); }
		?>
		<strong><?php echo $string_error ;?></strong><br /><br />
	<?php
	}
	elseif($_GET["error"]=="threshold"){
		$string_error="Be careful: the satisfaction threshold (t1) and the non-satisfaction threshold (t2) must be different from each other!";
		if ($debug){ $log->lwrite(' * ERROR-threshold -- written to page - string_error: ' . $string_error); }
		?>
		<strong><?php echo $string_error ;?></strong><br /><br />
	<?php
	}
}
if(isset($_SESSION['user'])){
  if ($debug){ 
    $log->lwrite(' * Call change_weight_form with arguments:');
    $log->lwrite(' ** table_name: ' . $table_name);
    $log->lwrite(' ** criterium:  SocioEco');
    $log->lwrite(' ** owner:      ' . $_SESSION['user']);
    $log->lwrite(' ** species:    default');
  }  
  change_weight_form($table_name,'SocioEco',$_SESSION['user'],'default');
}
?>
Make sure that the sum of the weight equals 1!<br />

				<ol>
				<li>t1: threshold at which the criteria is completely not satisfactory</li>
				<li>t2: threshold at which the criteria is completely satisfactory</li><br/>
				Note that if you are trying to minimize a criterion (for example the number of inhabitant aged 65 or more), t1 will be bigger than t2.
				<li>weight: The weight of the criteria. Note that the sum of the weights must equal one</li></ol>

				<br></br><br></br><br></br><br></br><br></br>
</body>
<?php
if(isset($_POST["00"])==1){
	if(isset($_SESSION['user'])){
		$total_weight=change_weight_db($table_name,'SocioEco', $_SESSION['user'], 'default');
		if ($debug){ 
      $log->lwrite(' * Call change_weight_db with arguments:');
      $log->lwrite(' ** table_name: ' . $table_name);
      $log->lwrite(' ** criterium:  SocioEco');
      $log->lwrite(' ** owner:      ' . $_SESSION['user']);
      $log->lwrite(' ** species:    default');
      $log->lwrite(' * Result total_weight: ' . $total_weight);
    }  
		if ($total_weight <> 1){
		  if ($debug){ $log->lwrite(' * total_weight not 1 ==> call error-sum: ' . $total_weight); }
			db_disconnect($dbh);
			header("Location:ChangeWeightSocioEco.php?error=sum");
		} else {
			$sql_thres="select count(*) from thres_weight where (t1-t2)=0";
			$thres0=pg_query($sql_thres);
			$thres=pg_fetch_result($thres0,0,0);
			if ($debug){ $log->lwrite(' * Nr Thres with (t1=t2): ' . $thres); }
			if($thres<>0){
			  if ($debug){ $log->lwrite(' * Thres <> 0 ==> call error-threshold: ' . $thres); }
  			header("Location:ChangeWeightFinal.php?error=threshold");
	  		exit();
			} else {			
			  $sql_year="SELECT cast(substring(table_name, 13,4) as integer) as table_name2 FROM information_schema.tables WHERE table_schema='public' and table_name like 'plz_socioec_%' order by table_name2 desc limit 1" ; //select the most recent year for which we have a plz_socioec table
			  if ($debug){ $log->lwrite(' * sql_year: ' . $sql_year); }
			  $res_year=pg_query($sql_year);
			  $year=pg_fetch_result($res_year,0,0);
			  if ($debug){ $log->lwrite(' * year: ' . $year); }
			  include("FunctionsCalcIndex.php");	
			  if ($debug){ 
          $log->lwrite(' * Call change_weight_db with arguments:');
          $log->lwrite(' ** year: ' . $year);
          $log->lwrite(' ** owner: ' . $_SESSION['user']);
        }  
			  IndexSocioEcPLZ($year, $_SESSION['user']); //to calculate the score for each PLZ from FunctionsCalcIndex.php
			  db_disconnect($dbh);
        # // close log file
        if ($debug){
          $log->lwrite(' * Call index.php ...');
          $log->lclose();
        }
			  header("Location:index.php");		
			}
		}
	}
}
include("footer.php");
?>
</html>
