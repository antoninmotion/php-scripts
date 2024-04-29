<?php

$ftp_host = "***INSERT***";
$ftp_un = "***INSERT***";
$ftp_pw = "***INSERT***";
$local_path = "***INSERT***";
$ftp_path = "***INSERT***";
$apogee_path = "***INSERT***";
$export_digital = "***INSERT***";
$test_path = "***INSERT***";
$test_path2 = "***INSERT***";

$ftp_conn = ftp_connect($ftp_host)
	or die("Could not connect.");
	
if($ftp_conn) {
	
	$login = ftp_login($ftp_conn, $ftp_un, $ftp_pw);
	
	echo "Connection successful. \n\n";
	
	// Gets list of files and directories from specific FTP server folder and puts it into array.
	$file_list = ftp_nlist($ftp_conn, $ftp_path);
	
	//  Sets variable for length of array minus 1 since counting starts at 0.
	$len = count($file_list) - 1;

	//  Loops through the array and takes action on XML files.
	for($i=0; $i <= $len; $i++) {
		
		$extension = substr($file_list[$i], -3);		
		
		if($extension == "xml")  {
			
			// Downloads XML file.
			ftp_get($ftp_conn, $local_path . $file_list[$i], $ftp_path.$file_list[$i], FTP_ASCII);
			
			// Turns XML file into object.
			$xml = simplexml_load_file($local_path . $file_list[$i]);
						
			//$tkt_tmp = $xml->OrderLines->OrderLine->Internal_Part_Id->__toString();
			$hr_file = $xml->OrderLines->OrderLine->Custom_Identifier->__toString()."-HR.pdf";
			$lr_file = $xml->OrderLines->OrderLine->Custom_Identifier->__toString()."-LR.pdf";
			$imposition = $xml->OrderLines->OrderLine->Item_Type->__toString();
			
			// Gets designated print file and downloads it to the associated imposition folder.
			if($imposition != "") {
				
				if($imposition == "EXPORT-DIGITAL") {
				
					// Routes HR print file to imposition folder called out in XML file.
					ftp_get($ftp_conn, $export_digital . $hr_file, $ftp_path.$hr_file, FTP_BINARY);
					// Moves all associated files to the "Routed" folder on the FTP server.
					ftp_rename($ftp_conn, $ftp_path.$file_list[$i], $ftp_path . "Routed\\".$file_list[$i]);
					ftp_rename($ftp_conn, $ftp_path.$hr_file, $ftp_path . "Routed\\".$hr_file);
					ftp_rename($ftp_conn, $ftp_path.$lr_file, $ftp_path . "Routed\\".$lr_file);
				
					echo $imposition . "\n";					
					
				} else {
					
					// Routes HR print file to imposition folder called out in XML file.
					ftp_get($ftp_conn, $apogee_path . $imposition ."\\". $hr_file, $ftp_path.$hr_file, FTP_BINARY);
					// Moves all associated files to the "Routed" folder on the FTP server.
					ftp_rename($ftp_conn, $ftp_path.$file_list[$i], $ftp_path . "Routed\\".$file_list[$i]);
					ftp_rename($ftp_conn, $ftp_path.$hr_file, $ftp_path . "Routed\\".$hr_file);
					ftp_rename($ftp_conn, $ftp_path.$lr_file, $ftp_path . "Routed\\".$lr_file);
				
					echo $imposition . "\n";
					
				}				
			} else {
				
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

?>