<?php
require_once __DIR__ . '/../../mysql.php';
require_once __DIR__ . '/../../debug.php';
if(isset($_POST['submit'])){
	$task_id=$_POST['task_id'];
	$scenario_id = $_POST['scenario_id'];
	$setup_time = $_POST['setup_time'];
	$execution_time =  $_POST['execution_time'];
	$src_task_id = "SELECT CASE_ID FROM case_library WHERE CASE_ID = '$task_id';";
	$rst_task_count = $db -> query($src_task_id);
	$row_task_count = $rst_task_count -> fetch_all(MYSQLI_ASSOC);
	if(empty($row_task_count)){
		echo "Sorry. no such Task ID found!";
		exit();
	}else{
		$src_scenario_id = "SELECT SCENARIO_ID FROM case_library WHERE CASE_ID = '$task_id' AND SCENARIO_ID = '$scenario_id';";
		$rst_scenario_count = $db -> query($src_scenario_id);
		$row_scenario_count = $rst_scenario_count -> fetch_all(MYSQLI_ASSOC);

		if(!empty($row_scenario_count)){
			echo "This Scenario is already existed!";
			exit();
		}
	}

	$qry_add_scenario="INSERT INTO case_library 
	(CASE_ID,CASE_TITLE,SCENARIO_ID,SCENARIO_TITLE,SETUP_TIME,EXECUTION_TIME,ESTIMATE_SETUPTIME,ESTIMATE_EXECTIME) VALUES
	('$task_id', '$task_id', '$scenario_id', '$scenario_id', '$setup_time', '$execution_time', '$setup_time', '$execution_time');";
	//echo $qry_add_scenario;
	$rst_add_scenario = $db -> query($qry_add_scenario);
}
?>
<html>
	<body>
		<form action='add_scenario.php' method='POST'>
			<input type='text' id='task_id' name='task_id' required placeholder='Task ID'>
			<input type='text' id = 'scenario_id' name = 'scenario_id' required placeholder = 'Scenario ID'>
			<input type='text' id = 'setup_time' name = 'setup_time' required placeholder = 'Estimete Setup Time'>
			<input type='text' id = 'execution_time' name = 'execution_time' required placeholder = 'Estimete Eexecute Time'>
			<button type='submit' name='submit'>Submit</button>
		</form>
	</body>
</html>