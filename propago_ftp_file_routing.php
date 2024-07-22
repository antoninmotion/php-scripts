<?php

/* $output_locations = array(
	"KS-ProfIndBC" => "A:\/Imposition1\/",
	"BIZ-24up-44" => "A:\/Imposition2\/",
	"BIZ-12up-1/0-RaisedBlack" => "\\\\wipserver\\WIP Array\\Digital Services\\Test\\Imposition1\\",
); */

$ftp_host = "ftp.arboroakland.com";
$ftp_un = "ablaga";
$ftp_pw = "@Arbor33";
$local_path = ".\\automation\\tmp\\";
$ftp_path = "\\FTP Data\\FTP\\Propago\\";
$apogee_path = "\\\\aog-apollo-2\\HotFolderRoot\\";
$export_digital = "\\\\aog-apollo-2\\ExportRoot\\Digital\\";
$test_path = "\\\\wipserver\\WIP Array\\Digital Services\\Test\\";
$test_path2 = "\\\\wipserver\\WIP Array\\Digital Services\\Test\\Digital\\";

$ftp_conn = ftp_connect($ftp_host,21)
	or die("Could not connect.");  // Could add additional error handling here.
	
$login = ftp_login($ftp_conn, $ftp_un, $ftp_pw);
	
if($login) {
	
	ftp_pasv($ftp_conn, true);	
	echo "Connection successful. \n\n";	
	// Gets list of files and directories from specific FTP server folder and puts it into array.
	$file_list = ftp_nlist($ftp_conn, $ftp_path);
	
	if($file_list == false){
		echo "An error occurred.";		
	}
	
	//  Sets variable for length of array minus 1 since counting starts at 0.
	$len = count($file_list) - 1;
	//echo "\n\n" . $len . "\n\n";
	
	//  Loops through the array and takes action on XML files.
	for($i=0; $i <= $len; $i++) {
		
		$extension = substr($file_list[$i], -3);		
		
		if($extension == "xml")  {
			
			// Downloads XML file.
			ftp_get($ftp_conn, $local_path . $file_list[$i], $ftp_path.$file_list[$i], FTP_ASCII);
			
			// Turns XML file into object.
			$xml = simplexml_load_file($local_path . $file_list[$i]);
						
			//$tkt_tmp = $xml->OrderLines->OrderLine->Internal_Part_Id->__toString();
			$hr_file = $xml->OrderLines->OrderLine->Print_File_Name->__toString();
			$lr_file = $xml->OrderLines->OrderLine->Custom_Identifier->__toString()."-LR.pdf";
			$imposition = $xml->OrderLines->OrderLine->Item_Type->__toString();
			
			// This section is commented out and used for troubleshooting.
			//var_dump($xml);
			//echo $tkt_tmp . "\n" . $hr_file;
			//echo "\n" . $output_locations[$tkt_tmp];
			//echo "\n" . gettype($tkt_tmp);
			
			// Gets designated print file and downloads it to the associated imposition folder.
			// *** OLD ** ftp_get($ftp_conn, $output_locations[$tkt_tmp].$hr_file, "\\FTP Data\\FTP\\Propago\\Test\\".$hr_file, FTP_ASCII);
			if($imposition != "") {
				
				if($imposition == "EXPORT-DIGITAL") {
				
					// Routes HR print file to imposition folder called out in XML file.
					// *** CHANGE TEST_PATH2 VAR TO EXPORT_DIGITAL WHEN LIVE ***
					ftp_get($ftp_conn, $export_digital . $hr_file, $ftp_path.$hr_file, FTP_BINARY);
					// Moves all associated files to the "Routed" folder on the FTP server.
					ftp_rename($ftp_conn, $ftp_path.$file_list[$i], $ftp_path . "Routed\\".$file_list[$i]);
					ftp_rename($ftp_conn, $ftp_path.$hr_file, $ftp_path . "Routed\\".$hr_file);
					ftp_rename($ftp_conn, $ftp_path.$lr_file, $ftp_path . "Routed\\".$lr_file);
				
					echo $imposition . "\n";					
					
				}
				else {
					
					// Routes HR print file to imposition folder called out in XML file.
					// *** CHANGE TEST_PATH VAR TO APOGEE_PATH WHEN LIVE ***
					ftp_get($ftp_conn, $apogee_path . $imposition ."\\". $hr_file, $ftp_path.$hr_file, FTP_BINARY);
					// Moves all associated files to the "Routed" folder on the FTP server.
					ftp_rename($ftp_conn, $ftp_path.$file_list[$i], $ftp_path . "Routed\\".$file_list[$i]);
					ftp_rename($ftp_conn, $ftp_path.$hr_file, $ftp_path . "Routed\\".$hr_file);
					ftp_rename($ftp_conn, $ftp_path.$lr_file, $ftp_path . "Routed\\".$lr_file);
				
					echo $imposition . "\n";
					
				}				
			}
			else {
				
				//  If no imposition value is set.
				// Moves all associated files to the "Routed" folder on the FTP server.
				ftp_rename($ftp_conn, $ftp_path.$file_list[$i], $ftp_path . "No Imposition\\".$file_list[$i]);
				ftp_rename($ftp_conn, $ftp_path.$hr_file, $ftp_path . "No Imposition\\".$hr_file);
				ftp_rename($ftp_conn, $ftp_path.$lr_file, $ftp_path . "No Imposition\\".$lr_file);
				
				echo "No imposition set.\n";
				
			}			
		}		
	}
	// Closes FTP connection.
	ftp_close($ftp_conn);
	
	echo "\nFinished \n";
}

//phpinfo();

?>
