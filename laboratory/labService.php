<? 
header('Content-Type: text/javascript; charset=UTF-8');  
chdir('..');
require_once "backend.php"; 
switch ($_REQUEST['task']) {
	case 'loadViral':
		$recordArray = array();
		$recordArray = json_decode($_REQUEST['params']);
		$cs = 0;  // count successful loads
		$cnf = 0; // count orders not found
		$cd = 0;  // count result already entered
		initErrorTable();
		$flag=1;
		//foreach ($recordArray as $vr) {
		foreach ($recordArray as $rowArray) { 
			$cliniqueCode =  $rowArray[0];
			if (strlen($cliniqueCode) > 5) continue;
			$comPatient = $rowArray[1];
			$patientID  = $rowArray[2];	
			// dummy dob 3
			// dummy sexe 4
			$orderID    = $rowArray[5];
			$visitDate  = $rowArray[6];
			$resultDate = $rowArray[7];
			$tInd       = $rowArray[8];
			$resultat   = $rowArray[9];
			$note       = $rowArray[10];
			// remove sitecode from encID
			$encID      = substr($orderID,5);
			// fix up the draw date/result date
			$visitDate  = fixDate($visitDate);
			$resultDate = fixDate($resultDate);
			/* Old code when we were attempting to match on ST number
			if ($resultLab != 1) {
				$patientID = getPatient($comPatient,$cliniqueCode);
				switch ($patientID) {
					case 'Patient non trouv&eacute;':
						$cnf++;
						writeErrorRecord($patientID, $rowArray);
						break;
					case 'Patient dupliqu&eacute;':
						$cd++;
						writeErrorRecord($patientID, $rowArray);
						break;
					default:
						$orderDate = checkOrderDate($patientID,$cliniqueCode,$visitDate);
						if ($orderDate == null) $result = saveEncounter($patientID,$cliniqueCode,$visitDate);
						$resultLab = saveViralResult($patientID,$cliniqueCode,$visitDate,$resultat,$resultDate,$note);
						$cs++;
						break;
				}
			} */
			$resultLab = updateViral($cliniqueCode,$encID,$tInd,$resultat,$resultDate,$note);
			switch ($resultLab) {
				case 0:      // update already entered
					$cd++;
					break;
				case -1:     // order not found
					$cnf++;
					writeErrorRecord('Ordre non trouvée', $rowArray);
					break;
				default:
					$cs++;
					break;
			}
		}
		echo $cs . ": Tests chargés;\n" . $cnf .": Ordre non retrouvés;\n" . $cd . ": Mise à jour entrée précédemment.\n";
		break;
	case 'getOrdered':  
	        // check to see if order has been sent; hide OE catalog items with no results; show OE catalog with results 
	        $sql = "select count(*) as cnt from encounterQueue 
	        where encounter_id = " . $_REQUEST['eid'] . " and left('" . $_REQUEST['pid'] . "',5) = sitecode and encounterStatus in (1,2,6)";
	        $queue = databaseSelect()->query($sql)->fetchAll(PDO::FETCH_ASSOC); 
		if (intval($queue[0]['cnt']) > 0) { 
		  /* the 1st select in the union gets non-OE-catalog items when there is an OE order
		   * the 2nd select in the union gets OE items that have results already returned
		   */
                  $sql = "(
                    select visitDate as vDate, labID as labid, labGroup, replace(testNameFr,'\'','’') as testNameFr, 
                        result, result2, result3, resultAbnormal, resultRemarks, ymdtodate(resultdateyy,resultdatemm,resultdatedd) resultDate, 
        		accessionNumber, sendingSiteName, sampleType as sampletype, 'T' as theType, 1
        		from a_labs 
        		where encounter_id = " . $_REQUEST['eid'] . " and 
        		patientid = '" . $_REQUEST['pid'] . "' 
        		and (labid < 1000 or sampletype in ('isante', 'iSanté', 'isanté', 'iSante'))
        	    ) union (
        	    select ymdtodate(visitDateyy,visitdatemm,visitdatedd), labID, labGroup, replace(testNameFr,'\'','’'),
        	        result, result2, result3, resultAbnormal, resultRemarks, date(resultTimestamp), 
        		q.accessionNumber, sendingSiteName, sampleType, 'T', 1
        		from labs l, encounterQueue q 
        		where patientid = '" . $_REQUEST['pid'] . "' and
        		l.sitecode = left('" . $_REQUEST['pid'] . "',5) and
        		l.sitecode = q.sitecode and
        		q.encounter_id = " . $_REQUEST['eid'] . " and  
        		left(l.accessionNumber,instr(l.accessionNumber,'-')-1) = q.accessionNumber and
        		q.encounterStatus = 6)";
		} else {  
		        $where = getEncounterWhere ($_REQUEST['eid'], $_REQUEST['pid']); 
		        /* this query fetches all order items when OE is not configured or when the order has not yet been sent to the lab
		         * the 1st select gets all simple order items and the 2nd select gets panels if OE is configured
		         */
        		$sql = "
        		(select visitDate as vDate, labID as labid, o.labgroup as labGroup, replace(testNameFr,'\'','’') as testNameFr, 
                        result, result2, result3, resultAbnormal, resultRemarks, ymdtodate(resultdateyy,resultdatemm,resultdatedd) as resultDate, 
        		accessionNumber, sendingSiteName, sampleType as sampletype, 'T' as theType, labGroupLookup_id from openLabs o, labGroupLookup g 
        		where o.formVersion >= 3 and encounter_id = " . $_REQUEST['eid'] . " and patientid = '" . $_REQUEST['pid'] . "' and o.labgroup = g.labgroup)";
        		if (getConfig('labOrderUrl') != NULL) $sql .= " union 
                	(select ymdtodate(visitdateyy,visitdatemm,visitdatedd), l.labid, p.labGroup, p.panelName, '','','','','','','','','Panel','P', labGroupLookup_id from labs l, labPanelLookup p, labGroupLookup g 
        	        where " . $where . " and l.labid = p.labPanelLookup_id and l.labGroup = g.labGroup and l.labGroup = p.labGroup) 
        	        order by 15,13,4"; 
        	}
		$labitems = databaseSelect()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$data = json_encode($labitems);  
		echo '({"total":"' . count($labitems) . '","results":' . $data . '})';
		break;
	case 'queueEncounter': 
		$sql = 'insert into encounterQueue (encounterType, sitecode, dbSite, encounter_id, encounterStatus, createDateTime, lastStatusUpdate) values (' . $_REQUEST['type'] . ',' . $_REQUEST['site'] . ',' . DB_SITE . ',' . $_REQUEST['eid'] . ',1, now(), now())';
		$rc = database()->query($sql)->rowCount();
		if ($rc != 1) echo '{"retVal":false,"mess1":"insert to queue failed","mess2":"' . $sql . '"}';  
                else {
                        runEncounterQueue (); 
                        $sql = "select q.* from encounterQueue q where q.encounter_id = " . $_REQUEST['eid'] . " and " . $_REQUEST['site'] . " = q.sitecode order by lastStatusUpdate desc";
        	        $queue = databaseSelect()->query($sql)->fetchAll(PDO::FETCH_ASSOC); 
        	        if (count($queue) > 0) {
        	                $statusNumber = $queue[0]['encounterStatus'];
        	                if ($statusNumber == 2)
                                        echo '{"retVal":true,"mess1":"successfully sent and accepted","mess2":"' . $sql . '"}';
        	                else 
        	                        echo '{"retVal":false,"mess1":"order rejected:' . $statusNumber . '","mess2":"' . $sql . '"}';
			} else 
			        echo '{"retVal":false,"mess1":"queue query failed","mess2":"' . $sql . '"}';
                }
		break;
	case 'updateLab': 
		echo "Ok";
		break;
	case 'saveOrderedLabs':
		$records = json_decode($_REQUEST['data'],true); 
		$whereArray = explode("'",getEncounterWhere ($_REQUEST['eid'], $_REQUEST['pid']));
		// delete the current set of rows 
		$sql = "delete from labs where patientid = ? and dbsite = ? and sitecode = ? and visitdatedd = ? and visitdatemm = ? and visitdateyy = ? and seqNum = ?"; 
		$keyArray = array($whereArray[1], DB_SITE, $whereArray[3], $whereArray[5], $whereArray[7], $whereArray[9], $whereArray[11]);    
		$rc = database()->query($sql, $keyArray)->rowCount(); 
		//echo "Delete rowcount = " . $rc;
		$retcode = saveRows($records, $keyArray);
		if ($retcode == 0) echo '{"success":"false"}';
		else {
			$qry = "update encounter set lastModified = now() where encounter_id = ? and patientid = ?"; 
			$rowCnt = database()->query($qry, array($_REQUEST['eid'], $_REQUEST['pid']))->rowCount();
			$mess = 'results saved';           
			echo '{"success":"true","message":"' . $mess . '"}'; 
		}
		break;
	case 'fetchViralErrors':
		$qry = "select * from viralLoadErrors";
		$result = database()->query($qry)->fetchAll(PDO::FETCH_ASSOC);
		$table="<table id=\"keywords\" cellspacing=\"0\" cellpadding=\"0\"><thead><tr>
			<th>CLINIQUE CODE</th>
			<th>COMENTAIRES DE PATIENT</th>
			<th>NDM</th>
			<th>DDN</th>
			<th>SEXE</th>
			<th># SÉJOUR</th>
			<th>DATE DE PRÉLEVÉ</th>
			<th>VALIDE DATE</th>
			<th>T IND</th>
			<th>RÉSULTAT</th>
			<th>COMMENTAIRES DE RÉSULTAT</th>
			<th>ERREURS</th></tr></thead>";
		foreach($result as $row) {
		$record=explode(',',$row['originalRecord']);
		$table.="<tbody><tr>
			<td>".$record[0]."</td>
			<td>".$record[1]."</td>
			<td>".$record[2]."</td>
			<td>".$record[3]."</td>
			<td>".$record[4]."</td>
			<td>".$record[5]."</td>
			<td>".$record[6]."</td>
			<td>".$record[7]."</td>
			<td>".$record[8]."</td>
			<td>".$record[9]."</td>
			<td>".$record[10]."</td>
			<td>".$row['errorType']."</td></tr>";
	}
	$table.="</tbody></table>";
		print_r($table);
} 

function saveRows($records,$keyArray) { 
	foreach ($records as $rec) {
		$sql = "insert into labs (patientID, dbsite, sitecode, visitdateDd, visitdateMm, visitdateYy, seqNum, labid, ordered, labGroup, testnamefr,sampletype,result,result2,result3,resultAbnormal,resultdateyy,resultdatemm,resultdatedd,resultRemarks) values (?,?,?,?,?,?,?,?,1,?,?,?,?,?,?,?,?,?,?,?)"; 
		$itemArray = $keyArray;
		$resultDateArray = split('-', $rec['resultDate']);
		$ry ='';
		$rm ='';
		$rd ='';
		if (count($resultDateArray) == 3) {
			$ry = substr($resultDateArray[0],2);
			$rm = $resultDateArray[1];
			$rd = $resultDateArray[2];
		}
		array_push($itemArray,$rec['labid'], $rec['labGroup'],$rec['testNameFr'],$rec['sampletype'],$rec['result'],$rec['result2'],$rec['result3'],$rec['resultAbnormal'],$ry,$rm,$rd,$rec['resultRemarks']);
		$rc = database()->query($sql,$itemArray);
		if ($rc == 0) return 0;   
	}
	return 1;
}

function getPatient($st,$site){
	$sql = "SELECT patientID FROM patient WHERE location_id = ? AND DIGITS(clinicPatientid)+0 = DIGITS(?)+0";
	$result = array();
	$result = database()->query($sql, array($site, $st))->fetchAll(PDO::FETCH_ASSOC);
	$cnt = 0;
	foreach ($result as $row) {
		$cnt++;
		$pid = $row['patientID'];
	}
	switch ($cnt) {
		case 0:
			return 'Patient non trouv&eacute;';
			break;
		case 1:
			return $result[0]['patientID'];
			break;
		default:
			return 'Patient dupliqu&eacute;';
			break;
	}
}

function checkOrderDate($patientID,$site,$visit) {
	$sql = "SELECT ymdToDate(visitDateYy,visitDateMm,visitDateDd) as visitDate
	FROM labs 
	WHERE siteCode = ? AND patientID = ? AND ymdToDate(visitDateYy,visitDateMm,visitDateDd) = ? AND labID IN (103, 181)";
	$visitDate = "";
	$result = database()->query($sql, array($site,$patientID,'20' . $visit))->fetchAll(PDO::FETCH_ASSOC);
	foreach($result as $row) {
		$visitDate = $row['visitDate'];
	}
	return $visitDate;
}

function saveEncounter($patientID,$site,$visit) {
	$visitArray = split('-', $visit);
	$rd = $visitArray[2];
	$rm = $visitArray[1];
	$ry = $visitArray[0];
	$dbSite = DB_SITE;
	$sql = "INSERT INTO encounter (siteCode,patientID,visitDateDd,visitDateMm,visitDateYy,lastModified,
	encounterType,seqNum,encStatus,encComments,dbSite,visitPointer,formAuthor,formVersion,labOrDrugForm,
	creator,createDate,lastModifier,badVisitDate,visitDate) 
	VALUES (?,?,?,?,?,now(),?,?,?,?,?,?,?,?,?,?,now(),?,?,?)
	ON DUPLICATE KEY UPDATE lastModified = VALUES(lastModified)";
	$rc = database()->query($sql,array($site,$patientID,$rd,$rm,$ry,6,0,0,'',$dbSite,0,'admin',3,0, 'admin','admin',0,$visit));
	return $rc->rowCount();
}

function saveViralResult($patientID,$site,$visit,$resultat,$resultDate,$note) {
	$visitArray = split('-', $visit);
	$rd = $visitArray[2];
	$rm = $visitArray[1];
	$ry = $visitArray[0];
	$resultArray = split('-', $resultDate);
	$rdd = $resultArray[2];
	$rdy = $resultArray[0];
	$rdm = $resultArray[1];
	$sql = "INSERT INTO labs (patientid, sitecode, visitdateyy, visitdatemm,visitdatedd,seqNum, labID, 
		result, resultDateYy, resultDateMm, resultDateDd, resultRemarks)
		VALUES (?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE 
		result = VALUES(result), resultDateDd = VALUES(resultDateDd), resultDateMm = VALUES(resultDateMm), 
		resultDateYy = VALUES(resultDateDd), resultRemarks = VALUES(resultRemarks)";
	$result = database()->query($sql,array($patientID, $site, $ry, $rm, $rd, 0,103,$resultat,$rdy,$rdm,$rdd,$note));
	return $result->rowCount();
}

function updateViral($site,$encID,$tInd,$resultat,$resultDate,$note) {
	$sql = "UPDATE encounter set lastModified = now() WHERE sitecode = ? AND encounter_id = ?";
	$rc = database()->query($sql,array($site,$encID));
	$retVal = $rc->rowCount();
	if ($retVal === 1) {
		$sql = "UPDATE labs l, encounter e 
		SET l.result = ?, 
		l.resultRemarks = ?,
		l.resultDateDd = ?,
		l.resultDateMm = ?,
		l.resultDateYy = ?
		WHERE e.sitecode = ? AND e.encounter_id = ? AND
		e.sitecode = l.sitecode and e.patientid = l.patientid AND
		e.visitdateYy = l.visitdateYy and e.visitdateMm = l.visitdateMm AND
		e.visitdateDd = l.visitdateDd AND e.seqNum = l.seqNum AND
		l.labid in (103,181)";
		$resultDt = split('-', $resultDate);
		$rc = database()->query($sql,array($resultat,$tInd . " : " . $note,$resultDt[0],$resultDt[1],$resultDt[2],$site,$encID));
		$retVal = $rc->rowCount();
		if ($retVal === 1) return 1;
		else return 0;
	} else return -1;
}

function fixDate($dt) {
	$dArray = split('-',$dt);
	if (count($dArray) != 3) $dArray = split('/',$dt);
	return  $dArray[0] . '-' . $dArray[1] . '-' . substr($dArray[2],2);

}

function initErrorTable() {
	$qry = "DROP TABLE IF EXISTS viralLoadErrors; CREATE TABLE viralLoadErrors (errorType VARCHAR(25), originalRecord VARCHAR(200))";
	$result = database()->query($qry);
}

function writeErrorRecord ($err, $rw) {
	$qry = "INSERT INTO viralLoadErrors VALUES(?,?)";
	$result = database()->query($qry, array($err, implode(',',$rw)));
	if ($result->rowCount() != 1) echo "bad record insert failed";
}
?>  
