<?php
/*
sqlite> PRAGMA table_info(TabularDataWithStrings);
0|Value|TEXT|0||0
1|ReportName|TEXT|0||0
2|ReportForString|TEXT|0||0
3|TableName|TEXT|0||0
4|RowName|TEXT|0||0
5|ColumnName|TEXT|0||0
6|Units|TEXT|0||0
7|RowId|INTEGER|0||0
*/

class EEB_SQLITE3 {
	
	private $sql_file = NULL;

	/*
	 *  Initialize the sql_file while declaration
	 *
	 */	
	function __construct($sql_file) {
		$this->sql_file = $sql_file;
	}

    function getFilePath(){
		return $this->sql_file;
    }
	
	/*
	 *  Get 2D array values from a single table by row
	 *
	 */	
	function getValues($reportName, $reportForString, $tableName, $units)
	{
		$db = new SQLite3("$this->sql_file");
		if($db==NULL) die("sql_file is not declared!");
		if(!$db) die("Error: File is Not found!\n"); 
		
		$sql = "Select Distinct * From TabularDataWithStrings
			  	Where ReportName Like '$reportName' 
				  And ReportForString Like '$reportForString'
				  And TableName = '$tableName' 
				  And Units Like '$units'";

		$result = $db->query("$sql");
	
		if(!$result) die("Error: Query is incorrect!\n");

		$values = NULL;
		while($row=$result->fetchArray(SQLITE3_ASSOC)) {
		    
			$curColumnName=$row['ColumnName'];
		    $curRowName=$row['RowName'];
			$values["$curRowName"]["$curColumnName"] = number_format($row['Value'], 1, '.', ''); 		
		}

		return $values;
	}

	/*
	 *  Get Report For String For Zones Info Only
	 */
	function getReportForStrings($reportName) {

		$db = new SQLite3("$this->sql_file");
		if($db==NULL) die("sql_file is not declared!");
		if(!$db) die("Error: File is Not found!\n"); 
		
		$sql = "Select Distinct ReportForString From TabularDataWithStrings
			  	Where ReportName = '$reportName'";

		$result = $db->query("$sql");
	
		if(!$result) die("Error: Query is incorrect!\n");

		$for_str = NULL;
		$index = 0;
		while($row=$result->fetchArray(SQLITE3_ASSOC)) {
		    
			$for_str[$index++] = $row['ReportForString']; 		
		}

		return $for_str;
		
	}

	/*
	 *  Get 2D array values from a single table by column
	 *
	 */	
	function getValuesByColumn($reportName, $reportForString, $tableName, $units) {

		$db = new SQLite3("$this->sql_file");
		if($db==NULL) die("sql_file is not declared!");
		if(!$db) die("Error: File is Not found!\n"); 
		
		$sql = "Select Distinct * From TabularDataWithStrings
			  	Where ReportName Like '$reportName' 
				  And ReportForString Like '$reportForString'
				  And TableName = '$tableName' 
				  And Units Like '$units'";

		$result = $db->query("$sql");
	
		if(!$result) die("Error: Query is incorrect!\n");

		$values = NULL;
		while($row=$result->fetchArray(SQLITE3_ASSOC)) {
		    
			$curColumnName=$row['ColumnName'];
		    $curRowName=$row['RowName'];
			$values["$curColumnName"]["$curRowName"] = number_format($row['Value'], 1, '.', ''); 		
		}

		return $values;
	}

	/*
	 *	Get Monthly Value without Minimum of Months, Annual Sum or Average, and Maximum of Months
	 */
	function getValuesByMonthly($reportName, $reportForString, $tableName, $units) {

		$db = new SQLite3("$this->sql_file");
		if($db==NULL) die("sql_file is not declared!");
		if(!$db) die("Error: File is Not found!\n"); 
		
		$sql = "Select Distinct * From TabularDataWithStrings
			  	Where ReportName Like '$reportName' 
				  And ReportForString Like '$reportForString'
				  And TableName = '$tableName' 
				  And Units Like '$units'";

		$result = $db->query("$sql");
	
		if(!$result) die("Error: Query is incorrect!\n");

		$values = NULL;
		while($row=$result->fetchArray(SQLITE3_ASSOC)) {
		    
			$curColumnName=$row['ColumnName'];
		    $curRowName=$row['RowName'];
			if($curColumnName =='' | $curRowName == '' | $curRowName == 'Minimum of Months' | $curRowName == 'Annual Sum or Average' | $curRowName == 'Maximum of Months') {
				
			}else {
				$values["$curColumnName"]["$curRowName"] = number_format($row['Value'], 1, '.', ''); 	
			}	
		}

		return $values;
	}

	/*
	 *	Get Monthly Value without Minimum of Months, Annual Sum or Average, and Maximum of Months
	 */
	function getValuesByCategory($reportName, $reportForString, $tableName, $units) {

		$db = new SQLite3("$this->sql_file");
		if($db==NULL) die("sql_file is not declared!");
		if(!$db) die("Error: File is Not found!\n"); 
		
		$sql = "Select Distinct * From TabularDataWithStrings
			  	Where ReportName Like '$reportName' 
				  And ReportForString Like '$reportForString'
				  And TableName = '$tableName' 
				  And Units Like '$units'";

		$result = $db->query("$sql");
	
		if(!$result) die("Error: Query is incorrect!\n");

		$values = NULL;
		while($row=$result->fetchArray(SQLITE3_ASSOC)) {
		    
			$curColumnName=$row['ColumnName'];
		    $curRowName=$row['RowName'];
			if($curColumnName =='' | $curRowName == '' | $curRowName == 'Minimum of Months' | $curRowName == 'Annual Sum or Average' | $curRowName == 'Maximum of Months') {
				
			}else {
				$values["$curRowName"]["$curColumnName"] = number_format($row['Value'], 1, '.', ''); 	
			}	
		}

		return $values;
	}

}  // END of EEB_SQLITE3

?>
