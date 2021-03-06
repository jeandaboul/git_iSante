<?php

// For the given date, decide for which patients to create a report
function caseNotif ($site = "", $dt = "", $pdt = "", $tbls = array ()) {
  set_time_limit (0);

  if (empty ($site)) {
    echo "ERROR: A site must be specified!";
    return (0);
  }
  if (empty ($dt)) $dt = date ("Y-m-d");
  
  // Do we only want changed records since a previous run?
  //if (strtotime ($dt) == strtotime($pdt)) {
  //  $updatesOnly = 0;
 // } else {
  //  $updatesOnly = 1;
  //}
  
  $updatesOnly = 0;


  // Data structure to hold site data
  $siteData = array ();

  // Get site data
  $result = dbQuery ("
    SELECT c.clinic, l.lastModified
    FROM itech.clinicLookup c, caseNotification.lastModifiedDates l
    WHERE c.siteCode = '$site'
     AND c.siteCode = l.siteCode");
  while ($row = psRowFetch ($result)) {
    $siteData["siteName"] = trim ($row[0]);
    $siteData["lastModified"] = $row[1];
  }

  // Create initial final output doc
  $dom = new DOMDocument ("1.0", "UTF-8");
  $root = $dom->createElement ("ClinicalDocumentCollection");
  $root->setAttribute ("specificationVersion", "0.1");
  $root->setAttribute ("creationTime", date ("YmdHisO"));
  $root->setAttribute ("authorInstitution", $siteData["siteName"]);
  $root->setAttribute ("authorInstitutionCode", $site);
  $root->setAttribute ("authorInstitutionDbSite", DB_SITE);
  $dom->appendChild ($root);

  $joinFrom = "";
  $joinWhere = "";

  // Pull data for previous run date if flag is set
  if ($updatesOnly) {
    $joinFrom = ", tempUpdatedPids t";
    $joinWhere = "    AND p.patientID = t.pid";

    // Build a temp table to hold only those patients that have had modified
    // forms since the previous run.
    dbQuery ("DROP TABLE IF EXISTS tempUpdatedPids");
    dbQuery ("CREATE TEMPORARY TABLE tempUpdatedPids (pid VARCHAR (11))");
    dbQuery ("CREATE INDEX pid_idx ON tempUpdatedPids (pid)");
    dbQuery ("
      INSERT INTO tempUpdatedPids
      SELECT DISTINCT patientID
      FROM encValidAll
      WHERE siteCode = '$site'
      AND lastModified >= '" . $siteData["lastModified"] . "'
      AND encounterType IN (1,2,3,4,5,6,7,9,10,11,12,13,14,15,16,17,18,19,20,21,24,25,26,27,28,29,31)");

      // Temp table to hold max runDate per patient since previous run
      dbQuery ("DROP TABLE IF EXISTS tempMaxRunDate");
      dbQuery ("CREATE TEMPORARY TABLE tempMaxRunDate (pid VARCHAR (11), maxDate DATE)");
      dbQuery ("CREATE INDEX pid_idx ON tempMaxRunDate (pid)");
      dbQuery ("
        INSERT INTO tempMaxRunDate
        SELECT patientID, MAX(runDate)
        FROM caseNotification.cdaDigestHistory
        WHERE runDate <= '$pdt'
        GROUP BY 1");
  }

  // For which patients should I collect data?
  // Should only be run on a per site basis, otherwise data overload.
  $result = dbQuery ("
    SELECT DISTINCT p.patientID
    FROM encounter p $joinFrom
    WHERE p.encounterType IN (1,2,16,17)
	and p.encStatus<255
    $joinWhere
    AND p.visitDate <= '$dt'
    AND p.siteCode = '$site'
	UNION
	SELECT DISTINCT p.patientid 
	FROM labs p $joinFrom 
	WHERE p.labid in ( 100,1220,1221,1222,1223,1224,1225,1241,1242,1243,1652,1653,1654) 
	AND (result like '%pos%' or result = '1')
	$joinWhere
    AND ymdToDate(p.visitDateYy,p.visitDateMm,p.visitDateDd) <= '$dt'
    AND p.siteCode = '$site'
	UNION
	SELECT DISTINCT p.patientid from prescriptions p, encounter e $joinFrom 
	WHERE e.encountertype in (5,18) AND encStatus < 255 
	$joinWhere
	AND p.patientid = e.patientid 
	AND p.sitecode = e.sitecode 
	AND p.visitdateyy = e.visitdateyy 
	AND p.visitdatemm = e.visitdatemm 
	AND p.visitdatedd = e.visitdatedd 
	AND p.seqNum = e.seqNum 
	AND drugid in ( 1, 3, 4, 5, 6, 7, 8, 10, 11, 12, 15, 16, 17, 20, 21, 22, 23, 26, 27, 28, 29, 31, 32, 33, 34 )  
	AND (dispensed = 1 or dispAltNumPills is not null or isdate(ymdtodate(dispdateyy,dispdatemm,dispdatedd)) = 1 or dispAltNumDays is not null or dispAltDosage is not null)
	AND ymdToDate(p.visitDateYy,p.visitDateMm,p.visitDateDd) <= '$dt'
    AND p.siteCode = '$site'
	UNION
	SELECT DISTINCT p.patientid from obs o, encounter p $joinFrom
	WHERE p.encStatus < 255 
	$joinWhere
	AND o.encounter_id = p.encounter_id 
	AND o.location_id = p.sitecode 
	AND o.concept_id in (70858,71138,71139)
	AND p.visitDate <= '$dt'
    AND p.siteCode = '$site'
	UNION
	SELECT DISTINCT p.patientid from obs o, encounter p $joinFrom 
	WHERE p.encStatus < 255 
	$joinWhere
	AND o.encounter_id = p.encounter_id 
	AND o.location_id = p.sitecode 
	AND o.concept_id = 71205 
	AND o.value_numeric = 4
	AND p.visitDate <= '$dt'
    AND p.siteCode = '$site'
	ORDER BY patientID");
  while ($row = psRowFetch ($result)) {
    // Data structure to hold patient data
    $patData = array ();

    // Get patient data
    $patData = caseNotifPatData ($row[0], $dt, $siteData["siteName"], $tbls);

    // Build CDA
    $case = caseNotifCDA ($dom, $row[0], $dt, $siteData, $patData);

    // Get digest for current CDA
    $digest = cdaDigest ($case);

    // Only keep generated XML if there are changes from previous run
    $keepXML = 0;
    if ($updatesOnly) {
      // Pull digest for previous run date to compare with current
      $result2 = dbQuery ("
        SELECT c.sha256Hash
        FROM caseNotification.cdaDigestHistory c, tempMaxRunDate t
        WHERE c.patientID = '" . $row[0] . "'
        AND c.patientID = t.pid
        AND c.runDate = t.maxDate");
      while ($row2 = psRowFetch ($result2)) {
        $prevDigest = $row2[0];
      }

      // If digests are different, keep XML
      if ($prevDigest != $digest) $keepXML = 1;
    } else {
      $keepXML = 1;
    }

    // Append CDA to final output, if so directed
    if ($keepXML) {
      // Store digest for the run date
      dbQuery ("
        REPLACE caseNotification.cdaDigestHistory (patientID, runDate, sha256Hash)
        VALUES ('" . implode ("', '",
          array ($row[0], $dt, $digest)) . "')");

      // Append to final output
      $root->appendChild ($case);
    }
  }

  // Update lastModified date for this site (bookkeeping for future runs).
  // O.k. to save the lastModified date as earlier than the actual max value
  // as this just helps weed out records obviously not changed.
  // This relies on the local servers clocks being accurate.
  dbQuery ("
    REPLACE INTO caseNotification.lastModifiedDates
    SELECT l.siteCode, CASE WHEN e.lastModified IS NULL THEN DATEADD(mm, -1, NOW()) WHEN MAX(e.lastModified) > NOW() THEN DATEADD(mm, -1, NOW()) ELSE MAX(e.lastModified) END
    FROM clinicLookup l
     LEFT JOIN encounter e ON l.siteCode = e.siteCode
    WHERE l.inCPHR = 1
     AND e.siteCode = '$site'
    GROUP BY 1");

  // Cleanup
  dbQuery ("DROP TABLE IF EXISTS tempUpdatedPids");
  dbQuery ("DROP TABLE IF EXISTS tempMaxRunDate");

  if ($root->hasChildNodes()) {
    return ($dom->saveXML ());
  } else {
    return ("");
  }
}

// Pull case notification data for the given patient
function caseNotifPatData ($pid, $dt, $siteName = "", $tbls) {

  // Data structure to hold patient data
  $patData = array ();

  // Determine ped. or adult, use type of intake form to decide
  // Due to an error processing deleted encounters, it's currently possible
  // for there to be multiple, non-deleted intake encounters for a patient
  // in the consolidated datbases. Thus, we must use the most recently created
  // one and store encounter_id to be used in later joins. - JS 2010-11-09

  unset ($adult);
  $result = dbQuery ("
    SELECT TOP 1 encounterType, visitDate, encounter_id
    FROM encounter
    WHERE patientID = '$pid'
    AND encounterType IN (1,2,5,6,10,14,15,16,17,18,19,20,24,25,27,28,29,31)
    AND visitDate <= '$dt'
    ORDER BY createDate DESC");
  while ($row = psRowFetch ($result)) {
    if ($row[0] == 1 || $row[0] == 2 || $row[0] == 5 || $row[0] == 6 || $row[0] == 10 || $row[0] == 14 || $row[0] == 24 || $row[0] == 25 || $row[0] == 27 || $row[0] == 28) {
      $adult = 1;
    } else if ($row[0] == 15 || $row[0] == 16 || $row[0] == 17 || $row[0] == 18 || $row[0] == 19 || $row[0] == 20 || $row[0] == 29 || $row[0] == 31) {
      $adult = 0;
    }
    $patData["adult"] = $adult;

    // Capture date of intake for later use in CDA
    if (!empty ($row[1])) $patData["intake"] = date ("Ymd", strtotime ($row[1]));
    $patData["intakeEid"] = $row[2];
  }

  if (isset ($adult)) {
    populateData ($pid, $dt, $siteName, $patData, $tbls);

    return ($patData);
  } else {
    echo "ERROR: Could not determine if patient is adult or pediatric: pid = $pid\n";
  }
}

// Populate data structure with appropriate data from the patient's record
function populateData ($pid, $dt, $site, &$patData, $tbls) {

  // Demographic info
  $result = dbQuery ("
    SELECT DISTINCT TOP 1 lname, fname, sex, addrDistrict, 
	  case when addrSection='Port-au-Prince' or addrSection='Port au Prince' or addrSection='Port-au- Prince' or addrSection='PORT-AU-PRINCE I' or addrSection='PORT-AU-PRINCE III' or addrSection='PORT-AU-PRINCE II' then '1'
	  when addrSection='Carrefour' then '2'
	  when addrSection='Delmas' then '3'
	  when addrSection='Pétion ville' or addrSection='PETION-VILLE' then '4'
	  when addrSection='Kenscoff' then '5'
	  when addrSection='Cité Soleil' or addrSection='SOLEIL' then '6'
	  when addrSection='Gressier' then '7'
	  when addrSection='Tabarre' then '8'
	  when addrSection='Léogane' then '9'
	  when addrSection='Petit Goave' or addrSection='PETIT-GOAVE' then '10'
	  when addrSection='Grand Goave' or addrSection='GRAND-GOAVE' then '11'
	  when addrSection='Anse à Galets' then '12'
	  when addrSection='Pte à Raquette' or addrSection='Pte Ã  Raquette' then '13'
	  when addrSection='Cx des Bouquets' or addrSection='Croix des Bouquets' or addrSection='CROIX-DE- BOUQUETS' or addrSection='CROIX-DES-BOUQUETS' then '14'
	  when addrSection='Ganthier' then '15'
	  when addrSection='Thomazeau' then '16'
	  when addrSection='Cornillon' then '17'
	  when addrSection='Fonds Verrettes' then '18'
	  when addrSection='Arcahaie' then '19'
	  when addrSection='Cabaret' then '20'
	  when addrSection='Jacmel' then '21'
	  when addrSection='Cayes Jacmel' or addrSection='LES CAYES-JACMEL' then '22'
	  when addrSection='Marigot' then '23'
	  when addrSection='La Vallée' then '24'
	  when addrSection='Bainet' then '25'
	  when addrSection='Cote de Fer' then '26'
	  when addrSection='Belle Anse' or addrSection='BELLE-ANSE' then '27'
	  when addrSection='Anse-a-Pitres' then '28'
	  when addrSection='Grand Gosier' then '29'
	  when addrSection='Thiotte' then '30'
	  when addrSection='Cap Haïtien' or addrSection='CAP-HAITIEN' then '31'
	  when addrSection='Limonade' then '32'
	  when addrSection='Quartier Morin' then '33'
	  when addrSection='L''ACUL-DU-NORD' or addrSection='Acul du Nord' then '34'
	  when addrSection='Plaine du Nord' then '35'
	  when addrSection='Milot' then '36'
	  when addrSection='GRANDE-RIVIERE DU NORD' or addrSection='Grande Rivière du Nord' then '37'
	  when addrSection='Bahon' then '38'
	  when addrSection='Saint Raphaël' or addrSection='SAINT-RAPHAEL' then '39'
	  when addrSection='Dondon' then '40'
	  when addrSection='Ranquitte' then '41'
	  when addrSection='Pignon' then '42'
	  when addrSection='La Victoire' then '43'
	  when addrSection='Borgne' then '44'
	  when addrSection='PORT-MARGOT' or addrSection='Port Margot' then '45'
	  when addrSection='Limbe' then '46'
	  when addrSection='Bas Limbe' then '47'
	  when addrSection='Plaisance' then '48'
	  when addrSection='Pilate' then '49'
	  when addrSection='Fort Liberté' or addrSection='FORT-LIBERTE' then '50'
	  when addrSection='Perches' then '51'
	  when addrSection='Ferrier' then '52'
	  when addrSection='Ouanaminthe' then '53'
	  when addrSection='Capotille' then '54'
	  when addrSection='Mont Organisé' then '55'
	  when addrSection='TROU-DU-NORD' or addrSection='Trou du Nord' then '56'
	  when addrSection='Caracol' then '57'
	  when addrSection='Sainte Suzanne' then '58'
	  when addrSection='Terrier Rouge' or addrSection='TERRIER-ROUGE' then '59'
	  when addrSection='Vallières' then '60'
	  when addrSection='Carice' then '61'
	  when addrSection='Mombin Crochu' then '62'
	  when addrSection='Gonaïves' or addrSection='LES GONAIVES' then '63'
	  when addrSection='Ennery' then '64'
	  when addrSection='L’Estère' then '65'
	  when addrSection='Gros Morne' or addrSection='Gros-Morne' then '66'
	  when addrSection='Anse Rouge' then '67'
	  when addrSection='Terre-Neuve' then '68'
	  when addrSection='Saint Marc' or addrSection='SAINT-MARC' then '69'
	  when addrSection='Verrettes' or  addrSection='LES VERRETTES' then '70'
	  when addrSection='La Chapelle' then '71'
	  when addrSection='Dessalines' then '72'
	  when addrSection='Desdunes' then '73'
	  when addrSection='Grande Saline' then '74'
	  when addrSection='Petite-Rivière-de-l''Artibonite' then '75'
	  when addrSection='Marmelade' then '76'
	  when addrSection='Saint Michel de l’Attalaye' or addrSection='SAINT-MICHEL DE L''ATTALAYE' then '77'
	  when addrSection='Hinche' then '78'
	  when addrSection='Cerca Cavajal' then '79'
	  when addrSection='Maïssade' then '80'
	  when addrSection='Thomonde' then '81'
	  when addrSection='Mirebalais' then '82'
	  when addrSection='Saut d’Eau' then '83'
	  when addrSection='Boucan Carré' then '84'
	  when addrSection='Lascaobas' then '85'
	  when addrSection='Belladère' then '86'
	  when addrSection='Savanette' then '87'
	  when addrSection='Cerca La Source' then '88'
	  when addrSection='Thomassique' then '89'
	  when addrSection='Cayes' or addrSection='LES CAYES' then '90'
	  when addrSection='Camp-Perrin' then '91'
	  when addrSection='Chantal' then '92'
	  when addrSection='Maniche' then '93'
	  when addrSection='Ile a Vache' then '94'
	  when addrSection='Torbeck' then '95'
	  when addrSection='Port Salut' or addrSection='PORT-SALUT' then '96'
	  when addrSection='Arniquet' then '97'
	  when addrSection='Saint Jean du Sud' or addrSection='SAINT-JEAN DU SUD' then '98'
	  when addrSection='Aquin' then '99'
	  when addrSection='Cavaillon' then '100'
	  when addrSection='Saint Louis du Sud' or addrSection='SAINT-LOUIS DU SUD' then '101'
	  when addrSection='Coteaux' then '102'
	  when addrSection='Port à Piment' or addrSection='PORT-A-PIMENT' then '103'
	  when addrSection='Roche à Bateau' or addrSection='ROCHE-A-BATEAU' then '104'
	  when addrSection='Chardonnieres' then '105'
	  when addrSection='Les Anglais' then '106'
	  when addrSection='Tiburon' then '107'
	  when addrSection='Jérémie' then '108'
	  when addrSection='Abricot' then '109'
	  when addrSection='Bonbon' then '110'
	  when addrSection='Moron' then '111'
	  when addrSection='Chambellan' then '112'
	  when addrSection='Anse d’Hainault' or addrSection='ANSE-D''HAINAULT' or addrSection='Anse dâ€™Hainault' then '113'
	  when addrSection='Dame Marie' or addrSection='DAME-MARIE' then '114'
	  when addrSection='Irois' or addrSection='LES IROIS' then '115'
	  when addrSection='Corail' then '116'
	  when addrSection='Roseaux' or addrSection='LES ROSEAUX' then '117'
	  when addrSection='Beaumont' then '118'
	  when addrSection='Pestel' then '119'
	  when addrSection='Port de Paix' or addrSection='PORT-DE-PAIX' then '120'
	  when addrSection='Bassin Bleu' or addrSection='BASSIN-BLEU' then '121'
	  when addrSection='Chansolme' then '122'
	  when addrSection='LA TORTUE' then '123'
	  when addrSection='St Ls du Nord' or addrSection='SAINT-LOUIS DU NORD' then '124'
	  when addrSection='Anse à Foleur' or addrSection='ANSE-A-FOLEUR' then '125'
	  when addrSection='Môle St Nicolas' or addrSection='MOLE SAINT-NICOLAS' then '126'
	  when addrSection='Baie de Henne' or addrSection='BAIE-DE-HENNE' then '127'
	  when addrSection='Bombardopolis' then '128'
	  when addrSection='Jean Rabel' or addrSection='JEAN-RABEL' then '129'
	  when addrSection='Miragoane' then '130'
	  when addrSection='Petite Rivière de Nippes' or addrSection='PETITE-RIVIERE DE NIPPES' or addrSection='Petite RiviÃ¨re de Nippes' then '131'
	  when addrSection='Fonds-des-Nègres' then '132'
	  when addrSection='Paillant' then '133'
	  when addrSection='Anse à Veau' or addrSection='ANSE-A-VEAU' or addrSection='Anse Ã  Veau' then '134'
	  when addrSection='L’Asile' or addrSection='Lâ€™Asile' then '135'
	  when addrSection='Petite Trou de Nippes' then '136'
	  when addrSection='Plaisance du Sud' then '137'
	  when addrSection='Arnaud' then '138'
	  when addrSection='Baraderes' then '139'
	  when addrSection='Grand-Boucan' then '140'
	  else 'Autre' end as addrSection,
      addrTown, birthSection, birthTown, dobDd, dobMm, dobYy,
      ageYears, fnameMother, occupation, maritalStatus,
      p.clinicPatientID, nationalID, telephone
    FROM patient p, encValidAll e
    WHERE e.patientID = '$pid'
    AND e.encounter_id = '" . $patData["intakeEid"] . "'
    AND e.patientID = p.patientID");
  while ($row = psRowFetch ($result)) {
    $patData["lname"] = specialChars ($row[0]);
    $patData["fname"] = specialChars ($row[1]);
    $patData["sex"] = trim ($row[2]);
    $patData["addrDist"] = specialChars ($row[3]);
    $patData["addrSect"] = specialChars ($row[4]);
    $patData["addrTown"] = specialChars ($row[5]);
    $patData["birthSect"] = specialChars ($row[6]);
    $patData["birthTown"] = specialChars ($row[7]);
    $patData["dob"] = date("Ymd",strtotime(trim ($row[10]) . zpad (trim ($row[9]), 2) . zpad (trim ($row[8]), 2)));
    $patData["age"] = trim ($row[11]);
    $patData["motherName"] = specialChars ($row[12]);
    $patData["occupation"] = specialChars ($row[13]);
    $patData["marStat"] = trim ($row[14]);
    $patData["stID"] = specialChars ($row[15]);
    $patData["natID"] = specialChars ($row[16]);
    $patData["telephone"] = specialChars ($row[17]);
  }

  // If female, determine pregnancy
  if ($patData["sex"] == 1) {
    $patData["pregnant"] = caseNotifIsPregnant ($pid, $dt, $tbls);
  }

  // If pregnant, collect or estimate expected delivery date (LMP + 40 weeks)
  if ($patData["pregnant"] == 1) {
    $patData["lmpDate"] = "";
    $patData["dpa"] = "";

    // Get most recent LMP date
//    $result = dbQuery ("
//      SELECT
//        MAX(CASE WHEN ISNUMERIC(pregnantLmpDd) <> 1
//            AND ISDATE(dbo.ymdToDate(pregnantLmpYy,pregnantLmpMm,'15')) = 1
//            AND dbo.ymdToDate(pregnantLmpYy,pregnantLmpMm, '15') < '$dt'
//            THEN dbo.ymdToDate(pregnantLmpYy,pregnantLmpMm,'15')
//            WHEN ISNUMERIC(pregnantLmpDd) = 1
//            AND ISDATE(dbo.ymdToDate(pregnantLmpYy,pregnantLmpMm, pregnantLmpDd)) = 1
//            AND dbo.ymdToDate(pregnantLmpYy,pregnantLmpMm, pregnantLmpDd) < '$dt'
//            THEN dbo.ymdToDate(pregnantLmpYy,pregnantLmpMm, pregnantLmpDd) END
//        ) lmpDate
//      FROM v_vitals
//      WHERE patientID = '$pid'
//      AND ISNUMERIC(pregnantLmpMm) = 1
//      AND ISNUMERIC(pregnantLmpYy) = 1
//      AND ISDATE(dbo.ymdToDate(pregnantLmpYy,pregnantLmpMm,'1')) = 1
//      GROUP BY patientID");
    $result = dbQuery ("
      SELECT lmpDate
      FROM " . $tbls[2] . "
      WHERE patientID = '$pid'");
    while ($row = psRowFetch ($result)) {
      $patData["lmpDate"] = $row[0];
    }

    // See if we have any valid expected delivery dates entered on ob/gyn forms
    $dpa_enc = "";
    $result = dbQuery ("
      SELECT TOP 1 e.encounter_id
      FROM encValidAll e, obs o1, obs o2, obs o3
      WHERE o1.concept_id = '7057'
      AND o2.concept_id = '7058'
      AND o3.concept_id = '7059'
      AND e.siteCode = o1.location_id
      AND e.encounter_id = o1.encounter_id
      AND o1.location_id = LEFT('$pid', 5)
      AND o1.person_id = SUBSTRING('$pid', 6, LEN('$pid'))
      AND o1.encounter_id = o2.encounter_id
      AND o2.encounter_id = o3.encounter_id
      AND o1.value_text IS NOT NULL
      AND o2.value_text IS NOT NULL
      AND o3.value_text IS NOT NULL
      AND ISNUMERIC(CONVERT(varchar, o1.value_text)) = 1
      AND ISNUMERIC(CONVERT(varchar, o2.value_text)) = 1
      AND ISNUMERIC(CONVERT(varchar, o3.value_text)) = 1 
      AND ISDATE(dbo.ymdToDate(o1.value_text, o2.value_text, o3.value_text)) = 1
      AND e.visitDate <= '$dt'
      AND e.patientID = '$pid'
      ORDER BY e.patientID, e.visitDate DESC");
    while ($row = psRowFetch ($result)) {
      $dpa_enc = $row[0];
    }

    if (!empty ($dpa_enc)) {
      $result = dbQuery ("
        SELECT CONCAT(
                 SUM(
                   IF(concept_id = '7057', 2000 + value_text, '')
                 ),
                 '-',
                 IF(
                   LENGTH(
                     SUM(
                       IF(concept_id = '7058', value_text, '')
                     )
                   ) < 2,
                   CONCAT(
                     '0',
                     SUM(
                       IF(concept_id = '7058', value_text, '')
                     )
                   ),
                   SUM(
                     IF(concept_id = '7058', value_text, '')
                   )
                 ),
                 '-',
                 IF(
                   LENGTH(
                     SUM(
                       IF(concept_id = '7059', value_text, '')
                     )
                   ) < 2,
                   CONCAT(
                     '0',
                     SUM(
                       IF(concept_id = '7059', value_text, '')
                     )
                   ),
                   SUM(
                     IF(concept_id = '7059', value_text, '')
                   )
                 )
               ) AS dpa
        FROM obs
        WHERE CONCAT(location_id, person_id) = '$pid'
        AND encounter_id = '$dpa_enc'
        GROUP BY encounter_id");

      while ($row = psRowFetch ($result)) {
        $patData["dpa"] = substr ($row[0], 0, 4) . substr ($row[0], 5, 2) . "01";
      }
    } else {
      // If no DPA, add 40 weeks to LMP
      if (!empty ($patData["lmpDate"])) {
        $patData["dpa"] = date ("Ym", strtotime ($patData["lmpDate"] . " +40 week")) . "01"; 
      }
    }
  }

  // Probable mode of transmission info
  $keys = array (
    "", "sexMale", "sexWorker", "sexFemale", "sexWoutCondom", "sexDrugUser",
    "sexBisexual", "sexAnal", "sexOral", "drugUse", "drugUse", "histStd",
    "bloodExp", "sexAssault", "verticalTrans", "bloodTrans", "", "sexMale",
    "sexFemale", "sexWithInfected", "sexWorker", "", "", "", "", "otherRisk",
    "noRisk", "sexInt", "", "sexAnal", "", "sexBloodTrans", "histSyphilis",
    "sexTwoPlus", "sexStuff", "coinfection", "histTb", "otherRisk");

  $result = dbQuery ("
    SELECT riskID, riskAnswer, riskYy, riskComment
    FROM encValidAll e, riskAssessments r
    WHERE e.patientID = '$pid'
    AND e.encounter_id = '" . $patData["intakeEid"] . "'
    AND e.patientID = r.patientID
    AND e.siteCode = r.siteCode
    AND e.visitDateDd = r.visitDateDd
    AND e.visitDateMm = r.visitDateMm
    AND e.visitDateYy = r.visitDateYy
    AND e.seqNum = r.seqNum");
  while ($row = psRowFetch ($result)) {
    switch (trim ($row[0])) {
      case 12:
      case 15:
        $patData[$keys[$row[0]]] = trim ($row[1]);
        $patData[$keys[$row[0]] . "Yr"] = assumeYear (trim ($row[2])) == -1 ? "N/A" : assumeYear (trim ($row[2]));
        break;
      case 16:
        if ($patData["sex"] == 1) {
          $patData[$keys[1]] = trim ($row[1]);
        } else if ($patData["sex"] == 2) {
          $patData[$keys[3]] = trim ($row[1]);
        }
        break;
      case 17:
        if ($patData["sex"] == 2) {
          $patData[$keys[$row[0]]] = trim ($row[1]);
        }
        break;
      case 18:
        if ($patData["sex"] == 1) {
          $patData[$keys[$row[0]]] = trim ($row[1]);
        }
        break;
      case 25:
      case 26:
      case 35:
      case 37:
        $patData[$keys[$row[0]]] = trim ($row[1]);
        $patData[$keys[$row[0]] . "Text"] = specialChars ($row[3]);
        break;
      default:
        $patData[$keys[$row[0]]] = trim ($row[1]);
        break;
    }
  }

  $result = dbQuery ("
    SELECT r.lastQuarterSeroStatPart
    FROM riskAssessment r, encValidAll e
    WHERE e.patientID = '$pid'
    AND e.encounter_id = '" . $patData["intakeEid"] . "'
    AND e.patientID = r.patientID
    AND e.siteCode = r.siteCode
    AND e.visitDateDd = r.visitDateDd
    AND e.visitDateMm = r.visitDateMm
    AND e.visitDateYy = r.visitDateYy
    AND e.seqNum = r.seqNum");
  while ($row = psRowFetch ($result)) {
    $patData["partnerStatus"] = trim ($row[0]);
  }

  // First HIV+ test info
  // Check on intake form first
  if ($patData["adult"] == 1) {
    $result = dbQuery ("
      SELECT v.firstTestDd, v.firstTestMm, v.firstTestYy, v.firstTestThisFac,
             v.firstTestOtherFac, v.firstTestOtherFacText
      FROM vitals v, encValidAll e
      WHERE e.patientID = '$pid'
      AND e.encounter_id = '" . $patData["intakeEid"] . "'
      AND e.patientID = v.patientID
      AND e.siteCode = v.siteCode
      AND e.visitDateDd = v.visitDateDd
      AND e.visitDateMm = v.visitDateMm
      AND e.visitDateYy = v.visitDateYy
      AND e.seqNum = v.seqNum");
    while ($row = psRowFetch ($result)) {
      if (is_numeric (trim ($row[0])) &&
		  is_numeric (trim ($row[1])) &&
          is_numeric (trim ($row[2])) &&
          checkdate (trim ($row[1]), trim ($row[0]), assumeYear (trim ($row[2]))) &&
          strtotime (trim ($row[1]) . trim ($row[0]) . assumeYear (trim ($row[2]))) <= strtotime ($dt)) {
        $patData["firstTest"] = assumeYear (trim ($row[2])) . zpad (trim ($row[1]), 2) . zpad (trim ($row[0]), 2);
        if (is_numeric (trim ($row[3])) && trim ($row[3]) == 1) {
          $patData["firstTestFac"] = $site;
        } else if (is_numeric (trim ($row[4])) && trim ($row[4]) == 1) {
          $patData["firstTestFac"] = specialChars ($row[5]);
        } 
      }
    }
  } else {
    // Pediatric intake form
    $result = dbQuery ("
      SELECT p.pedLabsResult, p.pedLabsResultAge, p.pedLabsResultAgeUnits
      FROM pedLabs p, encValidAll e
      WHERE p.patientID = '$pid'
      AND e.encounter_id = '" . $patData["intakeEid"] . "'
      AND p.patientID = e.patientID
      AND p.siteCode = e.siteCode
      AND p.visitDateDd = e.visitDateDd
      AND p.visitDateMm = e.visitDateMm
      AND p.visitDateYy = e.visitDateYy
      AND p.seqNum = e.seqNum");
    while ($row = psRowFetch ($result)) {
      if (is_numeric (trim ($row[0])) && trim ($row[0]) == 1 &&
        is_numeric (trim ($row[1])) && trim ($row[2]) > 0) {
        $tmpDt = "";
        if (trim ($row[2]) == 1) {
          $tmpDt = caseNotifDayDiff (trim ($row[1]), strtotime ($patData["dob"]));
          if ($tmpDt > $dt) $tmpDt = "";
        } else if (trim ($row[2]) == 2) {
          $tmpDt = caseNotifMonthDiff (trim ($row[1]), strtotime ($patData["dob"]));
          if ($tmpDt > $dt) $tmpDt = "";
        }
  
        $patData["firstTest"] = $tmpDt;
        $patData["firstTestFac"] = $site;
      } 
    }
  }

  // If lab form has an earlier date, use that
  $result = dbQuery ("
    SELECT visitdate, resultDateMm, resultDateYy, result
    FROM a_labsCompleted
    WHERE patientID = '$pid'
    AND encounterType IN (6,19)
    AND labID IN (100,101,181,182)
    AND visitdate <= '$dt'");
  while ($row = psRowFetch ($result)) {
    if (is_numeric (specialChars ($row[3])) && specialChars ($row[3]) == 1) {
      $tmpDt = "";
      if (is_numeric (trim ($row[2])) &&
          is_numeric (trim ($row[1])) &&
          checkdate (trim ($row[1]), 1, assumeYear (trim ($row[2]))) &&
          strtotime (trim ($row[1]) . "/01/" . assumeYear (trim ($row[2]))) <= strtotime ($dt)) {
        $tmpDt = assumeYear (trim ($row[2])) . zpad (trim ($row[1]), 2) . "01";
      } else if (!is_numeric (trim ($row[1])) || !is_numeric (trim ($row[2]))) {
        $tmpDt = date ("Ymd", strtotime ($row[0]));
      }

      if (empty ($patData["firstTest"]) || (!empty ($patData["firstTest"]) && $tmpDt < $patData["firstTest"])) {
        $patData["firstTest"] = $tmpDt;
        $patData["firstTestFac"] = $site;
      }
    }
  }

  // Referred date
  // This data only exists on an adult intake form
  if ($patData["adult"] == 1) {
    $result = dbQuery ("
      SELECT v.firstCareOtherMm, v.firstCareOtherYy, v.transferIn,
             v.firstCareOtherFacText
      FROM vitals v, encValidAll e
      WHERE e.patientID = '$pid'
      AND e.encounter_id = '" . $patData["intakeEid"] . "'
      AND e.patientID = v.patientID
      AND e.siteCode = v.siteCode
      AND e.visitDateDd = v.visitDateDd
      AND e.visitDateMm = v.visitDateMm
      AND e.visitDateYy = v.visitDateYy
      AND e.seqNum = v.seqNum");
    while ($row = psRowFetch ($result)) {
      if (is_numeric (trim ($row[0])) &&
          is_numeric (trim ($row[1])) &&
          checkdate (trim ($row[0]), 1, assumeYear (trim ($row[1]))) &&
          strtotime (trim ($row[0]) . "/01/" . assumeYear (trim ($row[1]))) <= strtotime ($dt)) {
        $patData["firstCare"] = assumeYear (trim ($row[1])) . zpad (trim ($row[0]), 2) . "01";
        if (is_numeric (trim ($row[2])) && trim ($row[2]) == 1) {
          $patData["firstCareFac"] = specialChars ($row[3]);
        } else {
          $patData["firstCareFac"] = "N/A";
        }
      }
    }
  }

  // This used in multiple queries below
  $startRxClause = "
    CASE WHEN ISDATE(dbo.ymdToDate(p.dispDateYy, p.dispDateMm, p.dispDateDd)) = 1
     THEN dbo.ymdToDate(p.dispDateYy, p.dispDateMm, p.dispDateDd)
    WHEN ISDATE(dbo.ymdToDate(p.dispDateYy, p.dispDateMm, 1)) = 1
     THEN dbo.ymdToDate(p.dispDateYy, p.dispDateMm, 1)
    ELSE dbo.ymdToDate(p.visitDateYy, p.visitDateMm, p.visitDateDd)
    END";

  // First ARV
  /*$startDrugClause = "
    CASE WHEN ISDATE(dbo.ymdToDate(p.startYy, p.startMm, 1)) = 1 
     THEN dbo.ymdToDate(p.startYy, p.startMm, 1) 
    ELSE dbo.ymdToDate(p.visitDateYy, p.visitDateMm, p.visitDateDd)
    END";
  $result = dbQuery ("
    SELECT TOP 1 $startRxClause AS startDate
    FROM drugLookup l, prescriptions p, encounter e
    WHERE p.patientID = '$pid'
	AND e.encounterType in(5,18) and encStatus<255
	 AND p.patientID = e.patientID
     AND p.sitecode = e.sitecode 
	 AND p.visitdateyy = e.visitdateyy 
	 AND p.visitdatemm = e.visitdatemm 
	 AND p.visitdatedd = e.visitdatedd 
	 AND p.seqNum = e.seqNum 
     AND p.drugID = l.drugID
     AND l.drugGroup IN ('NRTIs', 'NNRTIs', 'Pls')
     AND $startRxClause <= '$dt'
     AND dbo.ymdToDate(p.visitDateYy, p.visitDateMm, p.visitDateDd) <= '$dt'
    UNION
    SELECT $startDrugClause AS startDate
    FROM drugLookup l, drugs p, encounter e
    WHERE p.patientID = '$pid'
	AND e.encounterType in(1,2,16,17) and encStatus<255
	 AND p.patientID = e.patientID
     AND p.sitecode = e.sitecode 
	 AND p.visitdateyy = e.visitdateyy 
	 AND p.visitdatemm = e.visitdatemm 
	 AND p.visitdatedd = e.visitdatedd 
	 AND p.seqNum = e.seqNum 
     AND p.drugID = l.drugID
     AND l.drugGroup IN ('NRTIs', 'NNRTIs', 'Pls')
     AND $startDrugClause <= '$dt'
     AND dbo.ymdToDate(p.visitDateYy, p.visitDateMm, p.visitDateDd) <= '$dt'
     AND (ISDATE(dbo.ymdToDate(p.startYy, p.startMm, 1)) = 1
          OR p.isContinued = 1)
    ORDER BY startDate ASC");
  while ($row = psRowFetch ($result)) {
    if (!empty ($row[0])) $patData["firstArv"] = date ("Ymd", strtotime ($row[0]));
  }*/

  // First AIDS
  $result = dbQuery ("SELECT visitdate, conditionMm, conditionYy, whoStage FROM a_conditions WHERE patientID = '$pid' AND whoStage IN ('Stage III', 'Stage IV') AND encounterType IN (1,2,16,17) AND visitdate <= '$dt'");
  while ($row = psRowFetch ($result)) {
    $tmpDt = "";
    if (is_numeric (trim ($row[2])) &&
        is_numeric (trim ($row[1])) &&
        checkdate (trim ($row[1]), 1, assumeYear (trim ($row[2]))) &&
        strtotime (trim ($row[1]) . "/01/" . assumeYear (trim ($row[2]))) <= strtotime ($dt)) {
      $tmpDt = zpad (trim ($row[1]), 2) . "/" . assumeYear (trim ($row[2]));
    } else if (!is_numeric (trim ($row[1])) || !is_numeric (trim ($row[2]))) {
      $tmpDt = date ("m/Y", strtotime ($row[0]));
    }

    if (empty ($patData["firstAidsDiag"]) || (!empty ($patData["firstAidsDiag"]) && $tmpDt < $patData["firstAidsDiag"])) {
      $patData["firstAidsDiag"] = $tmpDt;
    }
  }

  // Death
  $result = dbQuery ("SELECT visitdate, reasonDiscDeathDd, reasonDiscDeathMm, reasonDiscDeathYy FROM a_discEnrollment WHERE patientID = '$pid' AND reasonDiscDeath = 1 AND encounterType IN (12,21) AND visitdate <= '$dt'");
  while ($row = psRowFetch ($result)) {
    if (is_numeric (trim ($row[3])) &&
        is_numeric (trim ($row[2])) &&
		is_numeric (trim ($row[1])) &&
        checkdate (trim ($row[2]), trim ($row[1]), assumeYear (trim ($row[3]))) &&
        strtotime (trim ($row[2]) . trim ($row[1]) . assumeYear (trim ($row[3]))) <= strtotime ($dt)) {
      $patData["deathDate"] = assumeYear (trim ($row[3])).zpad (trim ($row[2]), 2).zpad (trim ($row[1]), 2);
    } else if (!is_numeric (trim ($row[1])) || !is_numeric (trim ($row[2])) || !is_numeric (trim ($row[3]))) {
      $patData["deathDate"] = date ("Ymd", strtotime ($row[0]));
    }
  }

  // CD4 Tests
  $result = dbQuery ("
    SELECT visitdate, cd4
    FROM cd4Table
    WHERE patientID = '$pid'
     AND visitdate <= '$dt'
    GROUP BY 1, 2
    ORDER BY visitdate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["cd4Date"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
    $patData["cd4Cnt"][] = !empty ($row[1]) || $row[1] == 0 ? trim ($row[1]) : "N/A";
  }
  
  // Test PCR
  $result = dbQuery ("
    SELECT visitdate, pedLabsResultAge, case when pedLabsResultAgeUnits=1 then 'jours' 
	when pedLabsResultAgeUnits=2 then 'mois' end as AgeUnits, case when pedLabsResult=1 then 'POSITIF'
	when pedLabsResult=2 then 'NEGATIF' 
	when pedLabsResult=4 then 'INDETERMINE' end as PCRResult
    FROM pedLabs p, encValidAll e
    WHERE p.patientID = '$pid'
     AND visitdate <= '$dt'
	 AND e.patientID=p.patientID
	 AND e.encounterType = 16
    GROUP BY 1, 2
    ORDER BY visitdate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["PCRDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
	$patData["PCRAge"][] = !empty ($row[1]) & !empty ($row[2]) ? $row[1]." ".$row[2] : "N/A";
    $patData["ResultPCR"][] = !empty ($row[3])  ? $row[3] : "N/A";
  }
  
  //Allaitement maternel, On laisse tomber cette variable (reunion Nastad)
  /*$result = dbQuery ("
    SELECT dbo.ymdToDate(v.visitdateYy,v.visitDateMm, v.visitDateDd) as visitdate, case 
	when pedFeedBreast=1 or pedFeedBreast=2 or pedFeedMixed=1 or pedFeedMixed=2 then 'oui' else 'non' end as allaitement
    FROM vitals v, encounter e
    WHERE v.patientID = '$pid'
	 AND e.encounterType in(16,17)
	 AND encStatus <255
	 AND v.patientID = e.patientID
	 AND v.sitecode = e.sitecode
	 AND v.visitdateyy = e.visitdateyy
	 AND v.visitdatemm = e.visitdatemm
	 AND v.visitdatedd = e.visitdatedd
	 AND v.seqNum = e.seqNum
     AND visitdate <= '$dt'
    GROUP BY 1, 2
    ORDER BY visitdate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["allaitementDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
    $patData["allaitement"][] = !empty ($row[1]) ? $row[1] : "N/A";
  }*/
  
  //WHO Stage
  $result = dbQuery ("
    SELECT visitdate, whoStage
    FROM a_conditions
    WHERE patientID = '$pid'
     AND visitdate <= '$dt'
    GROUP BY 1, 2
    ORDER BY visitdate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["whoDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
    $patData["whoStage"][] = !empty ($row[1]) || $row[1] == 0 ? trim ($row[1]) : "N/A";
  }
  
  // Date of prescription of Cotrimoxizol (CTX)
  $result = dbQuery ("
    SELECT distinct 
	CASE WHEN ISDATE(dbo.ymdToDate(dispDateYy, dispDateMm, dispDateDd))=1 
	THEN dbo.ymdToDate(dispDateYy, dispDateMm, dispDateDd) 
	WHEN ISDATE(dbo.ymdToDate(dispDateYy, dispDateMm, 1)) = 1
    THEN dbo.ymdToDate(dispDateYy, dispDateMm, 1)
	ELSE dbo.ymdToDate(p.visitDateYy, p.visitDateMm, p.visitDateDd) END as dispDate
    FROM prescriptions p, encounter e
    WHERE p.patientID = '$pid'
	AND e.encounterType in(5,18) and encStatus<255
	 AND p.patientID = e.patientID
     AND p.sitecode = e.sitecode 
	 AND p.visitdateyy = e.visitdateyy 
	 AND p.visitdatemm = e.visitdatemm 
	 AND p.visitdatedd = e.visitdatedd 
	 AND p.seqNum = e.seqNum 
	 AND drugId = 9
	 AND (dispensed = 1 or dispAltNumPills is not null or isdate(ymdtodate(dispdateyy,dispdatemm,dispdatedd)) = 1 or dispAltNumDays is not null or dispAltDosage is not null)
    ORDER BY dispDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["ctxDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  }
  
  // Date patient received ART
  /*$result = dbQuery ("
    SELECT visitdate
    FROM pepfarTable
    WHERE patientID = '$pid'
     AND visitdate <= '$dt'
    ORDER BY visitdate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["artDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  }*/
  
  // Date patient received ART
	$result = dbQuery ("
    SELECT distinct 
	CASE WHEN ISDATE(dbo.ymdToDate(dispDateYy, dispDateMm, dispDateDd))=1 
	THEN dbo.ymdToDate(dispDateYy, dispDateMm, dispDateDd) 
	WHEN ISDATE(dbo.ymdToDate(p.dispDateYy, p.dispDateMm, 1)) = 1
    THEN dbo.ymdToDate(p.dispDateYy, p.dispDateMm, 1)
	ELSE dbo.ymdToDate(p.visitDateYy, p.visitDateMm, p.visitDateDd) END as dispDate
    FROM prescriptions p, encounter e
    WHERE p.patientID = '$pid'
	 AND e.encounterType in(5,18) and encStatus<255
	 AND p.patientID = e.patientID
     AND p.sitecode = e.sitecode 
	 AND p.visitdateyy = e.visitdateyy 
	 AND p.visitdatemm = e.visitdatemm 
	 AND p.visitdatedd = e.visitdatedd 
	 AND p.seqNum = e.seqNum
	 AND dbo.ymdToDate(p.visitDateYy, p.visitDateMm, p.visitDateDd) <= '$dt'
	 AND drugid in ( 1, 3, 4, 5, 6, 7, 8, 10, 11, 12, 15, 16, 17, 20, 21, 22, 23, 26, 27, 28, 29, 31, 32, 33, 34 )  
	 AND (dispensed = 1 or dispAltNumPills is not null or isdate(ymdtodate(dispdateyy,dispdatemm,dispdatedd)) = 1 or dispAltNumDays is not null or dispAltDosage is not null)
    ORDER BY dispDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["artDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  }
  
  
  
  // Date première prescription ART
	$result = dbQuery ("
    SELECT min(dbo.ymdToDate(p.visitDateYy, p.visitDateMm, p.visitDateDd)) as presDate
    FROM prescriptions p, encounter e
    WHERE p.patientID = '$pid'
	 AND e.encounterType in(5,18) and encStatus<255
	 AND p.patientID = e.patientID
     AND p.sitecode = e.sitecode 
	 AND p.visitdateyy = e.visitdateyy 
	 AND p.visitdatemm = e.visitdatemm 
	 AND p.visitdatedd = e.visitdatedd 
	 AND p.seqNum = e.seqNum
	 AND dbo.ymdToDate(p.visitDateYy, p.visitDateMm, p.visitDateDd) <= '$dt'
	 AND drugid in ( 1, 3, 4, 5, 6, 7, 8, 10, 11, 12, 15, 16, 17, 20, 21, 22, 23, 26, 27, 28, 29, 31, 32, 33, 34 )  
	 ORDER BY presDate ASC");
  while ($row = psRowFetch ($result)) {
	   if (!empty ($row[0])) $patData["firstArv"] = date ("Ymd", strtotime ($row[0]));
  }
  
  
  // Date prescription ART
	$result = dbQuery ("
    SELECT distinct dbo.ymdToDate(p.visitDateYy, p.visitDateMm, p.visitDateDd) as presDate
    FROM prescriptions p, encounter e
    WHERE p.patientID = '$pid'
	 AND e.encounterType in(5,18) and encStatus<255
	 AND p.patientID = e.patientID
     AND p.sitecode = e.sitecode 
	 AND p.visitdateyy = e.visitdateyy 
	 AND p.visitdatemm = e.visitdatemm 
	 AND p.visitdatedd = e.visitdatedd 
	 AND p.seqNum = e.seqNum
	 AND dbo.ymdToDate(p.visitDateYy, p.visitDateMm, p.visitDateDd) <= '$dt'
	 AND drugid in ( 1, 3, 4, 5, 6, 7, 8, 10, 11, 12, 15, 16, 17, 20, 21, 22, 23, 26, 27, 28, 29, 31, 32, 33, 34 )  
	 ORDER BY presDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["prescartDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  }
  
  // Date d'approvisionnement de traitement TB
  $result = dbQuery ("
    SELECT startDate
    FROM drugSummary
    WHERE patientID = '$pid'
	 AND drugID in('13','18','24','25','30')
    ORDER BY startDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["tbDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  }
  
  // Date de test syphilis
  $result = dbQuery ("
    SELECT dbo.ymdToDate(resultDateYy, resultDateMm, resultDateDd) as dateResult
    FROM a_labs
    WHERE patientID = '$pid'
	 AND testNameFr='RPR'
    ORDER BY dateResult ASC");
  while ($row = psRowFetch ($result)) {
    $patData["syphilisDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  }
  
  // Resultat du test syphilis
  $result = dbQuery ("
    SELECT case when result= 1 then 'POSITIF'
	when result=2 then 'NEGATIF' end as syphilisResult,visitDate
    FROM a_labs
    WHERE patientID = '$pid'
	 AND testNameFr='RPR'
    ORDER BY visitDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["syphilisResult"][] = !empty ($row[0]) ?  $row[0] : "N/A";
  }
  
  
  
 // Date ARV prophylaxique prenatale
  $result = dbQuery ("
    SELECT distinct dbo.ymdToDate(startYy, startMm, 1)
    FROM drugs d, vitals v
    WHERE d.patientID = '$pid'
	 AND d.patientID=v.patientID
	 AND v.pregnant = 1
	 AND v.visitDateYy = d.visitDateYy
	 AND v.visitDateMm = d.visitdateMm
	 AND v.visitDateDd = d.visitDateDd
	 AND drugID in(1,5,6,8,10,11,12,16,17,20,21,23,29,31,33,34)
	UNION
	SELECT distinct o.value_datetime
    FROM encValidAll e, obs o
    WHERE e.encounter_id = o.encounter_id
	 AND patientID = '$pid'
	 AND SUBSTRING(e.patientID, 6, LENGTH(e.patientID)) = o.person_id
     AND e.encounterType IN (24, 25)
	 AND o.value_datetime is not null
	 AND e.sitecode = o.location_id
     AND o.concept_id in('71211','71406','71407','71408','71409','71410')
     AND visitDate <= '$dt'
    ORDER BY 1 ASC
	");
  while ($row = psRowFetch ($result)) {
    $patData["ARVProDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  }
  
  // Valeur de la charge virale
  $result = dbQuery ("
    SELECT result ,visitDate
    FROM a_labs
    WHERE patientID = '$pid'
	 AND testNameFr='Charge virale'
    ORDER BY visitDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["ViralLoad"][] = !empty ($row[0]) ?  $row[0] : "N/A";
  }
  
  // Date de discontinuation ARV
  $result = dbQuery ("
    SELECT visitDate
    FROM encValidAll
    WHERE patientID = '$pid'
	 AND encounterType in(12,21)
    ORDER BY visitDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["discontinuationDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  }
  
  // Date de transfert du patient
  $result = dbQuery ("
    SELECT visitDate
    FROM a_discEnrollment
    WHERE patientID = '$pid'
	 AND reasonDiscTransfer=1
    ORDER BY visitDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["transferDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  }
  
  // Code de l'institution vers laquelle le patient est transfere
  $result = dbQuery ("
    SELECT clinicName,visitDate
    FROM a_discEnrollment
    WHERE patientID = '$pid'
	 AND reasonDiscTransfer=1
    ORDER BY visitDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["InstTransfer"][] = !empty ($row[0]) ? $row[0] : "N/A";
  }
  
  // Taux d'hemoglobine(g/dl)
  $result = dbQuery ("
    SELECT result ,visitDate
    FROM a_labs
    WHERE patientID = '$pid'
	 AND testNameEn='Hemoglobin (Hb)'
    ORDER BY visitDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["THb"][] = !empty ($row[0]) ?  $row[0] : "N/A";
  }
  
  // Resultat de test Hepatite B
  $result = dbQuery ("
    SELECT case when result = 1 then 'AgHbS'
		when result = 2 then 'AcHbC'
		when result = 4 then 'AcHbE' end as TestH,visitDate
    FROM a_labs
    WHERE patientID = '$pid'
	 AND testNameEn='Hepatitis B, presence of:'
    ORDER BY visitDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["THepb"][] = !empty ($row[0]) ?  $row[0] : "N/A";
  } 

  // Date de test Hepatite B
  $result = dbQuery ("
    SELECT dbo.ymdToDate(resultDateYy, resultDateMm, resultDateDd) as dateResult
    FROM a_labs
    WHERE patientID = '$pid'
	 AND testNameEn='Hepatitis B, presence of:'
    ORDER BY dateResult ASC");
  while ($row = psRowFetch ($result)) {
    $patData["HepbDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  } 

  // Valeur BMI
  $result = dbQuery ("
	SELECT case when vitalWeightUnits=1 then vitalWeight
	when vitalWeightUnits=2 then vitalWeight * 0.45359 end as Poids,
	case when vitalHeightCm is not null then concat(vitalHeight,'.',vitalHeightCm)
	when vitalHeightCm is null then vitalHeight end as Taille, visitDate
    FROM a_vitals
    WHERE patientID = '$pid' and vitalWeight is not null and vitalHeight is not null and (vitalHeight + vitalHeightCm) !=0
    ORDER BY visitDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["BMI"][] = !empty ($row[0])&&!empty($row[1]) ?  $row[0]/($row[1]*$row[1]) : "N/A";
  }

  // ARV Meds
  $stopDrugClause = "
    CASE WHEN ISDATE(dbo.ymdToDate(stopYy, stopMm, 1)) = 1
     THEN dbo.ymdToDate(stopYy, stopMm, 1)
    ELSE dbo.ymdToDate(visitDateYy, visitDateMm, 1)
    END";
  $result = dbQuery ("
    SELECT l.drugID, l.drugLabelFr, l.stdDosageDescription, MIN($startRxClause),
     CASE WHEN p.stdDosage NOT IN (1, 2) THEN NULL ELSE p.stdDosage END,
     CASE WHEN LTRIM(RTRIM(p.altDosageSpecify)) = '' THEN NULL ELSE
      LTRIM(RTRIM(p.altDosageSpecify)) END,
     CASE WHEN LTRIM(RTRIM(p.dispAltDosageSpecify)) = '' THEN NULL ELSE
      LTRIM(RTRIM(p.dispAltDosageSpecify)) END,
     CASE WHEN LTRIM(RTRIM(p.pedDosageDesc)) = '' THEN NULL ELSE
      LTRIM(RTRIM(p.pedDosageDesc)) END,
     l.pedStdDosageFr1, l.pedStdDosageFr2, l.pedDosageLabel, p.encounterType
    FROM drugLookup l, a_prescriptions p
    WHERE p.patientID = '$pid'
     AND p.drugID = l.drugID
     AND l.drugGroup IN ('NRTIs', 'NNRTIs', 'Pls')
     AND (p.forPepPmtct IS NULL OR p.forPepPmtct <> 1)
     AND $startRxClause <= '$dt'
     AND dbo.ymdToDate(p.visitDateYy, p.visitDateMm, p.visitDateDd) <= '$dt'
     AND p.encounterType IN (5, 18)
    GROUP BY 1, 5, 6, 7, 8
    ORDER BY $startRxClause ASC");
  while ($row = psRowFetch ($result)) {
    $patData["artMedName"][] = !empty ($row[1]) ? specialChars ($row[1]) : "N/A";
    $patData["artMedStart"][] = !empty ($row[3]) ? date ("m/d/Y", strtotime ($row[3])) : "N/A";
    $stopDate = "N/A";
    $subResult = dbQuery ("
      SELECT MAX($stopDrugClause)
      FROM a_drugs
      WHERE patientID = '$pid'
       AND drugID = '" . $row[0] . "'
       AND $stopDrugClause BETWEEN
        (SELECT MAX($startRxClause)
         FROM a_prescriptions p
         WHERE p.patientID = '$pid'
          AND p.drugID = '" . $row[0] . "'
          AND (p.forPepPmtct IS NULL OR p.forPepPmtct <> 1)
          AND $startRxClause <= '$dt'
          AND dbo.ymdToDate(p.visitDateYy, p.visitDateMm, p.visitDateDd) <= '$dt'
          AND p.encounterType IN (5, 18)
         GROUP BY p.patientID
        ) AND '$dt'
       AND dbo.ymdToDate(visitDateYy, visitDateMm, visitDateDd) <= '$dt'
       AND (ISDATE(dbo.ymdToDate(stopYy, stopMm, 1)) = 1
            OR isContinued != 1
            OR (toxicity = 1 OR intolerance = 1 OR failureVir = 1
                OR failureImm = 1 OR failureClin = 1 OR stockOut = 1
                OR pregnancy = 1 OR patientHospitalized = 1 OR lackMoney = 1
                OR alternativeTreatments = 1 OR missedVisit = 1
                OR patientPreference = 1 OR prophDose = 1 OR failureProph = 1
                OR interUnk = 1 OR finPTME = 1
               )
           )
       AND encounterType IN (1, 2, 16, 17)
      GROUP BY patientID, drugID");
    while ($subRow = psRowFetch ($subResult)) {
      if (!empty ($subRow[0])) $stopDate = date ("m/Y", strtotime ($subRow[0]));
    }
    $patData["artMedStop"][] = $stopDate;
    if ($row[11] == 5) {
      if (!empty ($row[6])) {
        $patData["artMedDose"][] = specialChars ($row[6]);
      } else if (!empty ($row[5])) {
        $patData["artMedDose"][] = specialChars ($row[5]);
      } else if ($row[4] == 1) {
        $patData["artMedDose"][] = specialChars ($row[2]);
      } else {
        $patData["artMedDose"][] = "N/A";
      }
    } else if ($row[11] == 18) {
      $artMedDose = "N/A";
      if ($row[4] == 1 || $row[4] == 2) {
        $artMedDose = specialChars ($row[7 + $row[4]]);
      }
      if (!empty ($row[7])) {
        if ($artMedDose == "N/A") {
          $artMedDose = specialChars ($row[7]);
        } else {
          $artMedDose .= " " . specialChars ($row[7]);
        }
      }
      if ($artMedDose != "N/A") $artMedDose .= " " . specialChars ($row[10]);
      $patData["artMedDose"][] = $artMedDose;
    }
  }

  // First progression to WHO Stage III and IV
  $patData["firstStage3"] = "1970-01-01";
  $patData["firstStage4"] = "1970-01-01";
  // First check conditions
  $startCondClause = "
    CASE WHEN ISNUMERIC(conditionYy) = 1
     AND ISNUMERIC(conditionMm) <> 1
     AND ISDATE(dbo.ymdToDate(conditionYy, '06', '15')) = 1
     THEN dbo.ymdToDate(conditionYy, '06', '15')
    WHEN ISNUMERIC(conditionYy) = 1
     AND ISNUMERIC(conditionMm) = 1
     AND ISDATE(dbo.ymdToDate(conditionYy, conditionMm, '15')) = 1
     THEN dbo.ymdToDate(conditionYy, conditionMm, '15')
    ELSE visitDate END";
  $result = dbQuery ("
    SELECT MAX($startCondClause),
     CASE WHEN whoStage = 'Stage III' THEN 3
     WHEN whoStage = 'Stage IV' THEN 4
     ELSE NULL END
    FROM a_conditions
    WHERE whoStage in ('Stage III', 'Stage IV')
     AND patientID = '$pid'
     AND $startCondClause <= '$dt'
     AND visitDate <= '$dt'
    GROUP BY 2");
  while ($row = psRowFetch ($result)) {
    if ($row[1] == 3) $patData["firstStage3"] = $row[0];
    else if ($row[1] == 4) $patData["firstStage4"] = $row[0];
  }
  // Next, symptoms
  $result = dbQuery ("
    SELECT MAX(e.visitDate),
     CASE WHEN o.short_name IN ('weightLossPlusTenPercMo', 'diarrheaPlusMo', 'pedSympWhoWtLoss2') THEN 3
     WHEN o.short_name IN ('wtLossTenPercWithDiarrMo', 'pedSympWhoWtLoss3') THEN 4
     WHEN e.encounterType IN (1, 2)
      AND o.short_name = 'feverPlusMo' THEN 3
     ELSE NULL END
    FROM encValidAll e, v_obs o
    WHERE e.encounter_id = o.encounter_id
     AND e.patientID = '$pid'
     AND SUBSTRING(e.patientID, 6, LENGTH(e.patientID)) = o.person_id
     AND e.sitecode = o.location_id
     AND o.short_name IN ('weightLossPlusTenPercMo', 'diarrheaPlusMo', 'pedSympWhoWtLoss2', 'wtLossTenPercWithDiarrMo', 'pedSympWhoWtLoss3', 'feverPlusMo')
     AND visitdate <= '$dt'
    GROUP BY 2");
  while ($row = psRowFetch ($result)) {
    if ($row[1] == 3 && strtotime ($row[0]) < strtotime ($patData["firstStage3"])) $patData["firstStage3"] = $row[0];
    else if ($row[1] == 4 && strtotime ($row[0]) < strtotime ($patData["firstStage4"])) $patData["firstStage4"] = $row[0];
  }
  
  // Date du test TB
  $result = dbQuery ("
    SELECT e.visitDate
    FROM encValidAll e, obs o
    WHERE e.encounter_id = o.encounter_id
	 AND patientID = '$pid'
	 AND SUBSTRING(e.patientID, 6, LENGTH(e.patientID)) = o.person_id
     AND e.encounterType IN (27, 28, 29, 31)
	 AND e.sitecode = o.location_id
     AND o.concept_id = '71174'
     AND visitDate <= '$dt'
    ORDER BY visitDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["tbTestDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  }
  
  // Date de rechute de diagnostic TB
  $result = dbQuery ("
    SELECT e.visitDate
    FROM encValidAll e, obs o
    WHERE e.encounter_id = o.encounter_id
	 AND patientID = '$pid'
	 AND SUBSTRING(e.patientID, 6, LENGTH(e.patientID)) = o.person_id
     AND e.encounterType IN (27, 28, 29, 31)
	 AND e.sitecode = o.location_id
     AND o.concept_id = '71177'
     AND visitDate <= '$dt'
    ORDER BY visitDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["tbReTestDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  }
  
  // Date de fin de traitement TB
  $result = dbQuery ("
    SELECT o.value_datetime, e.visitDate
    FROM encValidAll e, obs o
    WHERE e.encounter_id = o.encounter_id
	 AND patientID = '$pid'
	 AND SUBSTRING(e.patientID, 6, LENGTH(e.patientID)) = o.person_id
     AND e.encounterType IN (27, 28, 29, 31)
	 AND e.sitecode = o.location_id
     AND o.concept_id = '71215'
     AND visitDate <= '$dt'
    ORDER BY visitDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["tbEndDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  }
  
  //Resultat du traitement TB
  $result = dbQuery ("
    SELECT CASE when o.value_numeric = 1 then 1
	when o.value_numeric = 2 then 2
	when o.value_numeric = 4 then 4
	when o.value_numeric = 8 then 5
	when o.value_numeric = 16 then 6
	when o.value_numeric = 32 then 3 end as result,
	e.visitDate
    FROM encValidAll e, obs o
    WHERE e.encounter_id = o.encounter_id
	 AND patientID = '$pid'
	 AND SUBSTRING(e.patientID, 6, LENGTH(e.patientID)) = o.person_id
     AND e.encounterType IN (27, 28, 29, 31)
	 AND e.sitecode = o.location_id
     AND o.concept_id = '71216'
	 AND visitDate <= '$dt'
	ORDER BY visitDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["tbEndResult"][] = !empty ($row[0]) ? $row[0] : "N/A";
  }
  
  // Date d'accouchement
  $result = dbQuery ("
    SELECT o.value_datetime, e.visitDate
    FROM encValidAll e, obs o
    WHERE e.encounter_id = o.encounter_id
	 AND patientID = '$pid'
	 AND SUBSTRING(e.patientID, 6, LENGTH(e.patientID)) = o.person_id
     AND e.encounterType = 26
	 AND e.sitecode = o.location_id
     AND o.concept_id = '7819'
     AND visitDate <= '$dt'
    ORDER BY visitDate ASC");
  while ($row = psRowFetch ($result)) {
    $patData["AccouchementDate"][] = !empty ($row[0]) ? date ("Ymd", strtotime ($row[0])) : "N/A";
  }
  
  // Last, current HIV Stage radio buttons
  $result = dbQuery ("
    SELECT MAX(visitDate),
     CASE WHEN encounterType IN (1, 2) AND formVersion = 0
      AND currentHivStage IN (2, 3) THEN 3
     WHEN encounterType IN (1, 2) AND formVersion = 0
      AND currentHivStage > 3 THEN 4
     WHEN encounterType IN (1, 2) AND formVersion = 1
      AND currentHivStage IN (4, 5, 6, 7) THEN 3
     WHEN encounterType IN (1, 2) AND formVersion = 1
      AND currentHivStage > 7 THEN 4
     WHEN encounterType IN (16, 17) AND currentHivStage IN (4, 5, 6, 7) THEN 3
     WHEN encounterType IN (16, 17) AND currentHivStage > 7 THEN 4
     ELSE NULL END
    FROM a_medicalEligARVs
    WHERE currentHivStage IS NOT NULL
     AND patientID = '$pid'
     AND currentHivStage BETWEEN 1 AND 15
     AND visitDate <= '$dt' 
    GROUP BY 2");
  while ($row = psRowFetch ($result)) {
    if ($row[1] == 3 && strtotime ($row[0]) < strtotime ($patData["firstStage3"])) $patData["firstStage3"] = $row[0];
    else if ($row[1] == 4 && strtotime ($row[0]) < strtotime ($patData["firstStage4"])) $patData["firstStage4"] = $row[0];
  }
  if ($patData["firstStage3"] == "1970-01-01") {
    unset ($patData["firstStage3"]);
  } else {
    $patData["firstStage3"] = date ("m/d/Y", strtotime ($patData["firstStage3"]));
  }
  if ($patData["firstStage4"] == "1970-01-01") {
    unset ($patData["firstStage4"]);
  } else {
    $patData["firstStage4"] = date ("m/d/Y", strtotime ($patData["firstStage4"]));
  }

  // Most recent service date (any form filed except discontinuation)
  $patData["lastService"] = "1970-01-01";

  $result = dbQuery ("
    SELECT TOP 1 visitDate
    FROM encValidAll
    WHERE patientID = '$pid'
     AND visitDate <= '$dt'
     AND encounterType IN (1,2,3,4,5,6,7,9,10,11,14,15,16,17,18,19,20,24,25,26,27,28,29,31)
    ORDER BY visitDate DESC");
  while ($row = psRowFetch ($result)) {
    if (strtotime ($row[0]) >= strtotime ($patData["lastService"])) $patData["lastService"] = $row[0];
  }
  if ($patData["lastService"] == "1970-01-01") {
    unset ($patData["lastService"]);
  } else {
    $patData["lastService"] = date ("m/d/Y", strtotime ($patData["lastService"]));
  }
}

// Build CDA doc from patient & site data
function caseNotifCDA ($dom, $pid, $dt, $sData, $pData) {

  $now = date ("YmdHisO");
  $cda = $dom->createElementNS ("urn:hl7-org:v3", "ClinicalDocument");
  $cda->setAttribute ("xmlns:voc", "urn:hl7-org:v3/voc");
  $cda->setAttribute ("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
  $cda->setAttribute ("xsi:schemaLocation", "urn:hl7-org:v3 CDA.xsd");

  // Header
  $typeId = $dom->createElement ("typeId");
  $typeId->setAttribute ("root", "2.16.840.1.113883.1.3");
  $typeId->setAttribute ("extension", "POCD_HD000040");
  $cda->appendChild ($typeId);
  $templateId = $dom->createElement ("templateId");
  $templateId->setAttribute ("extension", "IMPL_CDAR2_LEVEL1-2REF_US_I2_2005SEP");
  $templateId->setAttribute ("root", "2.16.840.1.113883.10");
  $cda->appendChild ($templateId);
  $templateId2 = $dom->createElement ("templateId");
  $templateId2->setAttribute ("extension", "Medical Summmary");
  $templateId2->setAttribute ("root", "1.3.6.1.4.1.19376.1.5.3.1.1.2");
  $cda->appendChild ($templateId2);
  $id = $dom->createElement ("id");
  $id->setAttribute ("root", "1.3.6.1.4.1.150.2474.1");
  $id->setAttribute ("extension", "1004");
  $cda->appendChild ($id);
  $code = $dom->createElement ("code");
  $code->setAttribute ("code", "34133-9");
  $code->setAttribute ("codeSystem", "2.16.840.1.113883.6.1");
  $code->setAttribute ("codeSystemName", "LOINC");
  $code->setAttribute ("displayName", "SUMMARIZATION OF EPISODE NOTE");
  $cda->appendChild ($code);
  $effTime = $dom->createElement ("effectiveTime");
  $effTime->setAttribute ("value", $now);
  $cda->appendChild ($effTime);
  $confCode = $dom->createElement ("confidentialityCode");
  $confCode->setAttribute ("code", "N");
  $confCode->setAttribute ("codeSystem", "2.16.840.1.113883.5.25");
  $cda->appendChild ($confCode);
  $recTarget = $dom->createElement ("recordTarget");
  $patRole = $dom->createElement ("patientRole");
  $id2 = $dom->createElement ("id");
  $id2->setAttribute ("root", "1.3.6.1.4.1.150.2474.2");
  $id2->setAttribute ("extension", anonymizePid ($pid));
  $pat = $dom->createElement ("patient");
  $nm = $dom->createElement ("name");
  $given = $dom->createElement ("given", $pData["fname"]);
  $family = $dom->createElement ("family", $pData["lname"]);
  $nm->appendChild ($given);
  $nm->appendChild ($family);
  $gender = $dom->createElement ("administrativeGenderCode");
  if ($pData["sex"] == 1) {
    $gender->setAttribute ("code", "F");
  } else if ($pData["sex"] == 2) {
    $gender->setAttribute ("code", "M");
  } else {
    $gender->setAttribute ("code", "N/A");
  }
  $gender->setAttribute ("codeSystem", "2.16.840.1.113883.5.1");
  $dob = $dom->createElement ("birthTime");
  if (!empty ($pData["dob"])) {
    $dob->setAttribute ("value", $pData["dob"]);
  } else {
    $dob->setAttribute ("value", "00000000");
  }
  $pat->appendChild ($nm);
  $pat->appendChild ($gender);
  $pat->appendChild ($dob);
  $patRole->appendChild ($id2);
  $patRole->appendChild ($pat);
  $recTarget->appendChild ($patRole);
  $cda->appendChild ($recTarget);
  $author = $dom->createElement ("author");
  $time = $dom->createElement ("time");
  $time->setAttribute ("value", $now);
  $assignAuth = $dom->createElement ("assignedAuthor");
  $id3 = $dom->createElement ("id");
  $id3->setAttribute ("root", "1.3.6.1.4.1.150.2474.3");
  $id3->setAttribute ("extension", DB_SITE . "-" . substr ($pid, 0, 5));
  $assignAuthDev = $dom->createElement ("assignedAuthoringDevice");
  $software = $dom->createElement ("softwareName", "iSant&#xe9;");
  $assignAuthDev->appendChild ($software);
  $assignAuth->appendChild ($id3);
  $assignAuth->appendChild ($assignAuthDev);
  $author->appendChild ($time);
  $author->appendChild ($assignAuth);
  $cda->appendChild ($author);
  $custodian = $dom->createElement ("custodian");
  $assignCust = $dom->createElement ("assignedCustodian");
  $repCust = $dom->createElement ("representedCustodianOrganization");
  $id4 = $dom->createElement ("id");
  $id4->setAttribute ("root", "1.3.6.1.4.1.150.2474.4");
  $repCust->appendChild ($id4);
  $assignCust->appendChild ($repCust);
  $custodian->appendChild ($assignCust);
  $cda->appendChild ($custodian);
  $docOf = $dom->createElement ("documentationOf");
  $serviceEvent = $dom->createElement ("serviceEvent");
  $code2 = $dom->createElement ("code");
  $code2->setAttribute ("code", "34133-9");
  $code2->setAttribute ("codeSystem", "2.16.840.1.113883.6.1");
  $effTime2 = $dom->createElement ("effectiveTime");
  $low = $dom->createElement ("low");
  if (!empty ($pData["intake"])) {
    $low->setAttribute ("value", $pData["intake"]);
  } else {
    $low->setAttribute ("value", "00000000");
  }
  $high = $dom->createElement ("high");
  $high->setAttribute ("value", substr ($now, 0, 8));
  $effTime2->appendChild ($low);
  $effTime2->appendChild ($high);
  $performer = $dom->createElement ("performer");
  $performer->setAttribute ("typeCode", "PRF");
  $function = $dom->createElement ("functionCode");
  $function->setAttribute ("code", "PCP");
  $function->setAttribute ("codeSystem", "2.16.840.1.113883.5.88");
  $time2 = $dom->createElement ("time");
  $time2->appendChild ($low->cloneNode());
  $time2->appendChild ($high->cloneNode());
  $assignEntity = $dom->createElement ("assignedEntity");
  $id5 = $dom->createElement ("id");
  $id5->setAttribute ("extension", "3001");
  $id5->setAttribute ("root", "1.3.6.1.4.1.150.2474.5");
  $code3 = $dom->createElement ("code");
  $code3->setAttribute ("code", "59058001");
  $code3->setAttribute ("codeSystem", "2.16.840.1.113883.6.96");
  $assignPerson = $dom->createElement ("assignedPerson");
  $nm2 = $dom->createElement ("name");
  $prefix = $dom->createElement ("prefix", "N/A");
  $given = $dom->createElement ("given", "N/A");
  $family = $dom->createElement ("family", "N/A");
  $suffix = $dom->createElement ("suffix", "N/A");
  $nm2->appendChild ($prefix);
  $nm2->appendChild ($given);
  $nm2->appendChild ($family);
  $nm2->appendChild ($suffix);
  $assignPerson->appendChild ($nm2);
  $assignEntity->appendChild ($id5);
  $assignEntity->appendChild ($code3);
  $assignEntity->appendChild ($assignPerson);
  $performer->appendChild ($function);
  $performer->appendChild ($time2);
  $performer->appendChild ($assignEntity);
  $serviceEvent->appendChild ($code2);
  $serviceEvent->appendChild ($effTime2);
  $serviceEvent->appendChild ($performer);
  $docOf->appendChild ($serviceEvent);
  $cda->appendChild ($docOf);
  // End of Header

  // Body
  $component = $dom->createElement ("component");
  $structBody = $dom->createElement ("structuredBody");

  // Body Section 1
  $component2 = $dom->createElement ("component");
  $section = $dom->createElement ("section");
  $title = $dom->createElement ("title", "1. Identification");
  $text = $dom->createElement ("text", "Identification du patient");
  $list = $dom->createElement ("list");
  if ($pData["adult"]) {
    $item = $dom->createElement ("item", "Adulte ou P&#xe9;diatrique: Adulte");
  } else {
    $item = $dom->createElement ("item", "Adulte ou P&#xe9;diatrique: P&#xe9;diatrique");
  }
  $list->appendChild ($item);
  $item = $dom->createElement ("item", "Nom: " . $pData["lname"]);
  $list->appendChild ($item);
  $item = $dom->createElement ("item", "Pr&#xe9;nom: " . $pData["fname"]);
  $list->appendChild ($item);
  if ($pData["adult"]) {
    $item = $dom->createElement ("item", "Num. de T&#xe9;l&#xe9;: " . $pData["telephone"]);
    $list->appendChild ($item);
    $item = $dom->createElement ("item", "AKA: N/A");
    $list->appendChild ($item);
    $item = $dom->createElement ("item", "Adresse: " . $pData["addrDist"]);
    $list->appendChild ($item);
  }
  $item = $dom->createElement ("item", "Pr&#xe9;nom de la m&#xe8;re: " . $pData["motherName"]);
  $list->appendChild ($item);
  $item = $dom->createElement ("item", "Lieu de r&#xe9;sidence, Commune: " . $pData["addrSect"]);
  $list->appendChild ($item);
  $item = $dom->createElement ("item", "Lieu de r&#xe9;sidence, Section communale / Ville: " . $pData["addrTown"]);
  $list->appendChild ($item);
  $item = $dom->createElement ("item", "Lieu de naissance, Commune: " . $pData["birthSect"]);
  $list->appendChild ($item);
  $item = $dom->createElement ("item", "Lieu de naissance, Section communale / Ville: " . $pData["birthTown"]);
  $list->appendChild ($item);
  $item = $dom->createElement ("item", "Code VCT: " . $pData["stID"]);
  $list->appendChild ($item);
  $item = $dom->createElement ("item", "Code d'identification du patient: " . $pData["natID"]);
  $list->appendChild ($item);
  if ($pData["sex"] == 1) {
    $item = $dom->createElement ("item", "Sexe: F");
  } else if ($pData["sex"] == 2) {
    $item = $dom->createElement ("item", "Sexe: M");
  } else {
    $item = $dom->createElement ("item", "Sexe: N/A");
  }
  $list->appendChild ($item);
  if ($pData["sex"] == 1) {
    if ($pData["pregnant"] == 1) {
      $item = $dom->createElement ("item", "Enceinte: Oui");
      if ($pData["adult"] == 1) {
        if (!empty ($pData["dpa"])) {
          $item2 = $dom->createElement ("item", "DPA: " . substr ($pData["dpa"], 0, 4). substr ($pData["dpa"], 4, 2) . substr ($pData["dpa"], 6, 2) );
        } else {
          $item2 = $dom->createElement ("item", "DPA: N/A");
        }
        $list->appendChild ($item2);
      }
    } else if ($pData["pregnant"] == 0) {
      $item = $dom->createElement ("item", "Enceinte: Non");
    }
    $list->appendChild ($item);
  }
  if (!empty ($pData["dob"])) {
    $item = $dom->createElement ("item", "Date de naissance-" . ($i + 1) . ": " . $pData["dob"]);
	/*$item = $dom->createElement ("item", "Date de naissance: " . substr ($pData["dob"], 6, 2) . "/" . substr ($pData["dob"], 4, 2) . "/" . substr ($pData["dob"], 0, 4));*/
  } else if (!empty ($pData["age"]) || $pData["age"] == 0) {
    $item = $dom->createElement ("item", "Age (en ann&#xe9;es): " . $pData["age"]);
  } else {
    $item = $dom->createElement ("item", "Date de naissance: N/A");
    $list->appendChild ($item);
    $item = $dom->createElement ("item", "Age (en ann&#xe9;es): N/A");
  } 
  $list->appendChild ($item);
  if ($pData["adult"] == 1) {
    if (!empty ($pData["occupation"])) {
      $item = $dom->createElement ("item", "Occupation: " . $pData["occupation"]);
    } else {
      $item = $dom->createElement ("item", "Occupation: N/A");
    }
    $list->appendChild ($item);

    switch ($pData["marStat"]) {
      case 1: $tmp = "3"; break;
      case 2: $tmp = "4"; break;
      case 4: $tmp = "2"; break;
      case 8: $tmp = "5"; break;
      case 16: $tmp = "1"; break;
      case 32: $tmp = "6"; break;
      default: $tmp = "N/A"; break;
    }
    $item = $dom->createElement ("item", "Statut Marital: $tmp");
    $list->appendChild ($item);

    switch ($pData["partnerStatus"]) {
      case 1: $tmp = "VIH+"; break;
      case 2: $tmp = "N&#xe9;gatif"; break;
      case 4: $tmp = "Inconnu"; break;
      default: $tmp = "N/A"; break;
    }
    $item = $dom->createElement ("item", "Statut VIH du conjoint/de la conjointe: $tmp");
    $list->appendChild ($item);
  }
  $text->appendChild ($list);
  $section->appendChild ($title);
  $section->appendChild ($text);
  $component2->appendChild ($section);
  $structBody->appendChild ($component2);

  // Body Section 2
  $component2 = $dom->createElement ("component");
  $section = $dom->createElement ("section");
  $title = $dom->createElement ("title", "2. Mode possible de transmission");
  $text = $dom->createElement ("text", "Mode possible de transmission");
  $list = $dom->createElement ("list");
  if ($pData["adult"] == 1) {
    genXmlItem ($dom, $list, $pData["sexMale"], "Rapports sexuels avec un male:");
    genXmlItem ($dom, $list, $pData["sexFemale"], "Rapports sexuels avec une femelle:");
    genXmlItem ($dom, $list, $pData["drugUse"], "Injection de drogues:");
    genXmlItem ($dom, $list, $pData["bloodTrans"], "B&#xe9;n&#xe9;ficier de sang/produits de sang:");
    if ($pData["bloodTrans"] == 1) {
      if (!empty ($pData["bloodTransYr"])) {
        $item = $dom->createElement ("item", "Ann&#xe9;e b&#xe9;n&#xe9;ficier de sang/produits de sang: " . $pData["bloodTransYr"]);
      } else {
        $item = $dom->createElement ("item", "Ann&#xe9;e b&#xe9;n&#xe9;ficier de sang/produits de sang: Inconnu");
      }
      $list->appendChild ($item);
    }
    genXmlItem ($dom, $list, $pData["verticalTrans"], "Transmission M&#xe8;re &#xe0; Enfant:");
    genXmlItem ($dom, $list, $pData["bloodExp"], "Accident d'exposition au sang:");
    if ($pData["bloodExp"] == 1) {
      if (!empty ($pData["bloodExpYr"])) {
        $item = $dom->createElement ("item", "Ann&#xe9;e accident d'exposition au sang: " . $pData["bloodExpYr"]);
      } else {
        $item = $dom->createElement ("item", "Ann&#xe9;e accident d'exposition au sang: Inconnu");
      }
      $list->appendChild ($item);
    }
    genXmlItem ($dom, $list, $pData["sexWithInfected"], "Rapports sexuels h&#xe9;t&#xe9;rosexuelles avec personne SIDA/VIH+:");
    genXmlItem ($dom, $list, $pData["sexDrugUser"], "Rapports sexuels h&#xe9;t&#xe9;rosexuelles avec personne qui s'injecte la drogue:");
    genXmlItem ($dom, $list, $pData["sexBisexual"], "Rapports sexuels h&#xe9;t&#xe9;rosexuelles avec male bisexuel:");
    genXmlItem ($dom, $list, $pData["sexBloodTrans"], "Rapports sexuels h&#xe9;t&#xe9;rosexuelles avec b&#xe9;n&#xe9;ficier de sang/produits sang:");
    genXmlItem ($dom, $list, $pData["noRisk"], "Aucun risque sp&#xe9;cifi&#xe9;:", array ($pData["noRiskText"], ""));
  } else { // pediatric
    if ($pData["sexMale"] == 1 || $pData["sexFemale"] == 1 ||
        $pData["sexWorker"] == 1 || $pData["sexWoutCondom"] == 1 ||
        $pData["sexDrugUser"] == 1 || $pData["sexBisexual"] == 1 ||
        $pData["sexAnal"] == 1 || $pData["sexOral"] == 1 ||
        $pData["sexWithInfected"] == 1 || $pData["sexInt"] == 1 ||
        $pData["sexBloodTrans"] == 1 || $pData["sexTwoPlus"] == 1 ||
        $pData["sexStuff"] == 1) {
      $pData["sexEarly"] = 1;
    } else if ($pData["sexInt"] == 2 ||
               ($pData["sexMale"] == 2 && $pData["sexFemale"] == 2 &&
                $pData["sexWorker"] == 2 && $pData["sexWoutCondom"] == 2 &&
                $pData["sexDrugUser"] == 2 && $pData["sexBisexual"] == 2 &&
                $pData["sexAnal"] == 2 && $pData["sexWithInfected"] == 2 &&
                $pData["sexInt"] == 2 && $pData["sexBloodTrans"] == 2 &&
                $pData["sexTwoPlus"] == 2 && $pData["sexStuff"] == 2)) {
      $pData["sexEarly"] = 2;
    } else if ($pData["sexInt"] == 4 ||
               ($pData["sexMale"] == 4 && $pData["sexFemale"] == 4 &&
                $pData["sexWorker"] == 4 && $pData["sexWoutCondom"] == 4 &&
                $pData["sexDrugUser"] == 4 && $pData["sexBisexual"] == 4 &&
                $pData["sexAnal"] == 4 && $pData["sexWithInfected"] == 4 &&
                $pData["sexInt"] == 4 && $pData["sexBloodTrans"] == 4 &&
                $pData["sexTwoPlus"] == 4 && $pData["sexStuff"] == 4)) {
      $pData["sexEarly"] = 4;
    } else {
      $pData["sexEarly"] = 8;
    }
    genXmlItem ($dom, $list, $pData["sexEarly"], "Rapports sexuels pr&#xe9;coce:");
    genXmlItem ($dom, $list, $pData["sexAssault"], "Victime d'agression sexuelle:");
    genXmlItem ($dom, $list, $pData["verticalTrans"], "Statut VIH (+) de la m&#xe8;re:");
    genXmlItem ($dom, $list, $pData["bloodExp"], "Accident d'exposition au sang:");
    if ($pData["bloodExp"] == 1) {
      if (!empty ($pData["bloodExpYr"])) {
        $item = $dom->createElement ("item", "Si oui, ann&#xe9;e de l'accident: " . $pData["bloodExpYr"]);
      } else {
        $item = $dom->createElement ("item", "Si oui, ann&#xe9;e de l'accident: Inconnu");
      }
      $list->appendChild ($item);
    }
    genXmlItem ($dom, $list, $pData["bloodTrans"], "Transfusion sanguine:");
    if ($pData["bloodTrans"] == 1) {
      if (!empty ($pData["bloodTransYr"])) {
        $item = $dom->createElement ("item", "Si oui, ann&#xe9;e de la transfusion: " . $pData["bloodTransYr"]);
      } else {
        $item = $dom->createElement ("item", "Si oui, ann&#xe9;e de la transfusion: Inconnu");
      }
      $list->appendChild ($item);
    }
  }
  if ($list->hasChildNodes()) {
    $text->appendChild ($list);
  }
  $section->appendChild ($title);
  $section->appendChild ($text);
  $component2->appendChild ($section);
  $structBody->appendChild ($component2);

  // Body Section 3 (adult)
  if ($pData["adult"] == 1) {
    $component3 = $dom->createElement ("component");
    $section = $dom->createElement ("section");
    $title = $dom->createElement ("title", "3. Autres facteurs de risque");
    $text = $dom->createElement ("text", "Autres facteurs de risque");
    $list = $dom->createElement ("list");
    genXmlItem ($dom, $list, $pData["histSyphilis"], "Histoire ou pr&#xe9;sence de Syphilis:");
    genXmlItem ($dom, $list, $pData["histStd"], "Histoire ou pr&#xe9;sence d'autre IST:");
    genXmlItem ($dom, $list, $pData["sexAssault"], "Victime d'agression sexuelle:");
    genXmlItem ($dom, $list, $pData["otherRisk"], "Autre risque sp&#xe9;cifier:", array ($pData["otherRiskText"], ""));
    genXmlItem ($dom, $list, $pData["coinfection"], "Coinfection:", array ($pData["coinfectionText"], ""));
    genXmlItem ($dom, $list, $pData["histTb"], "Histoire ou pr&#xe9;sence du TB:");
    genXmlItem ($dom, $list, $pData["sexTwoPlus"], "Rapports sexuels avec >= 2 personnes dans les 3 derni&#xe8;res mois:");
    genXmlItem ($dom, $list, $pData["sexWoutCondom"], "Rapports sexuels sans condom:");
    genXmlItem ($dom, $list, $pData["sexAnal"], "Rapports sexuels par voie anale:");
    genXmlItem ($dom, $list, $pData["sexWorker"], "Rapports sexuels avec travailleur/euse de sexe:");
    genXmlItem ($dom, $list, $pData["sexStuff"], "L'&#xe9;change de sexe pour argent/choses:");
    if ($list->hasChildNodes()) {
      $text->appendChild ($list);
    }
    $section->appendChild ($title);
    $section->appendChild ($text);
    $component3->appendChild ($section);
    $structBody->appendChild ($component3);
  }

  // Body Section 3 (ped.)/Section 4 (adult)
  $component4 = $dom->createElement ("component");
  $section = $dom->createElement ("section");
  if ($pData["adult"] == 1) $title = $dom->createElement ("title", "4. Diagnostic du VIH");
  else $title = $dom->createElement ("title", "3. Diagnostic du VIH");
  $section->appendChild ($title);
  $text = $dom->createElement ("text", "Diagnostic du VIH");
  $list = $dom->createElement ("list");
  if (!empty ($pData["firstTest"])) {
    $item = $dom->createElement ("item", "Date du diagnostic VIH+: " . substr ($pData["firstTest"], 0, 4) . substr ($pData["firstTest"], 4, 2) . substr ($pData["firstTest"], 6, 2));
    $list->appendChild ($item);
    if (!empty ($pData["firstTestFac"])) {
      $item = $dom->createElement ("item", "&#xc9;tablissement o&#xf9; le test a &#xe9;t&#xe9; r&#xe9;alis&#xe9;: " . $pData["firstTestFac"]);
    } else {
      $item = $dom->createElement ("item", "&#xc9;tablissement o&#xf9; le test a &#xe9;t&#xe9; r&#xe9;alis&#xe9;: N/A");
    }
    $list->appendChild ($item);
  } else {
    $item = $dom->createElement ("item", "Date du diagnostic VIH+: N/A");
    $list->appendChild ($item);
  }
  if (!empty ($pData["firstCare"])) {
    $item = $dom->createElement ("item", "Date de la r&#xe9;f&#xe9;rence au centre de prise en charge: " . substr ($pData["firstCare"], 4, 2) . "/" . substr ($pData["firstCare"], 0, 4));
    $list->appendChild ($item);
    if (!empty ($pData["firstCareFac"])) {
      $item = $dom->createElement ("item", "Nom de l'&#xe9;tablissement de la r&#xe9;f&#xe9;rence: " . $pData["firstCareFac"]);
    } else {
      $item = $dom->createElement ("item", "Nom de l'&#xe9;tablissement de la r&#xe9;f&#xe9;rence: N/A");
    }
    $list->appendChild ($item);
  } else {
    $item = $dom->createElement ("item", "Date de la r&#xe9;f&#xe9;rence au centre de prise en charge: N/A");
    $list->appendChild ($item);
  }
  if ($list->hasChildNodes()) {
    $text->appendChild ($list);
  }
  $section->appendChild ($text);
  $component4->appendChild ($section);
  $structBody->appendChild ($component4);

  // Body Section 4 (ped.)/Section 5 (adult)
  $component5 = $dom->createElement ("component");
  $section = $dom->createElement ("section");
  if ($pData["adult"] == 1) $title = $dom->createElement ("title", "5. Donn&#xe9;es suppl&#xe9;mentaire");
  else $title = $dom->createElement ("title", "4. Donn&#xe9;es suppl&#xe9;mentaire");
  $section->appendChild ($title);
  $text = $dom->createElement ("text", "Donn&#xe9;es suppl&#xe9;mentaire");
  $list = $dom->createElement ("list");
  if (!empty ($pData["firstArv"])) {
    $item = $dom->createElement ("item", "Date 1er ARV: " . $pData["firstArv"]);
    $list->appendChild ($item);
  }
  if (!empty ($pData["firstAidsDiag"])) {
    $item = $dom->createElement ("item", "Mois/Ann&#xe9;e 1er Diag. SIDA: " . $pData["firstAidsDiag"]);
    $list->appendChild ($item);
  }
  if (!empty ($pData["deathDate"])) {
    $item = $dom->createElement ("item", "Date D&#xe9;c&#xe8;s: " . $pData["deathDate"]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["cd4Date"]); $i++) {
    $item = $dom->createElement ("item", "Date Test CD4-" . ($i + 1) . ": " . $pData["cd4Date"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["cd4Cnt"]); $i++) {
    $item = $dom->createElement ("item", "Val. Test CD4-" . ($i + 1) . ": " . $pData["cd4Cnt"][$i]);
    $list->appendChild ($item);
  }
   for ($i = 0; $i < count ($pData["whoStage"]); $i++) {
    $item = $dom->createElement ("item", "Val. WhoStage-" . ($i + 1) . ": " . $pData["whoStage"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["whoDate"]); $i++) {
    $item = $dom->createElement ("item", "Date WhoStage-" . ($i + 1) . ": " . $pData["whoDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["ctxDate"]); $i++) {
    $item = $dom->createElement ("item", "Date Reception Cotrimoxazol-" . ($i + 1) . ": " . $pData["ctxDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["artDate"]); $i++) {
    $item = $dom->createElement ("item", "Date Reception ART-" . ($i + 1) . ": " . $pData["artDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["prescartDate"]); $i++) {
    $item = $dom->createElement ("item", "Date Prescription ART-" . ($i + 1) . ": " . $pData["prescartDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["tbTestDate"]); $i++) {
    $item = $dom->createElement ("item", "Date du test TB-" . ($i + 1) . ": " . $pData["tbTestDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["tbReTestDate"]); $i++) {
    $item = $dom->createElement ("item", "Date de rechute de diagnostic TB-" . ($i + 1) . ": " . $pData["tbReTestDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["tbDate"]); $i++) {
    $item = $dom->createElement ("item", "Date d'approvisionnement de traitement TB-" . ($i + 1) . ": " . $pData["tbDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["tbEndDate"]); $i++) {
    $item = $dom->createElement ("item", "Date de fin de traitement TB-" . ($i + 1) . ": " . $pData["tbEndDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["tbEndResult"]); $i++) {
    $item = $dom->createElement ("item", "Resultat du traitement TB-" . ($i + 1) . ": " . $pData["tbEndResult"][$i]);
    $list->appendChild ($item);
  }
   for ($i = 0; $i < count ($pData["syphilisDate"]); $i++) {
    $item = $dom->createElement ("item", "Date de test syphilis-" . ($i + 1) . ": " . $pData["syphilisDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["syphilisResult"]); $i++) {
    $item = $dom->createElement ("item", "Resultat du test syphilis-" . ($i + 1) . ": " . $pData["syphilisResult"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["ARVProDate"]); $i++) {
    $item = $dom->createElement ("item", "Date ARV prophylaxique prenatale-" . ($i + 1) . ": " . $pData["ARVProDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["AccouchementDate"]); $i++) {
    $item = $dom->createElement ("item", "Date de fin d'accouchement-" . ($i + 1) . ": " . $pData["AccouchementDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["PCRDate"]); $i++) {
    $item = $dom->createElement ("item", "Date de PCR-" . ($i + 1) . ": " . $pData["PCRDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["PCRAge"]); $i++) {
    $item = $dom->createElement ("item", "Age lors du test PCR-" . ($i + 1) . ": " . $pData["PCRAge"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["ResultPCR"]); $i++) {
    $item = $dom->createElement ("item", "Resultat du Test PCR-" . ($i + 1) . ": " . $pData["ResultPCR"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["allaitementDate"]); $i++) {
    $item = $dom->createElement ("item", "Date allaitement-" . ($i + 1) . ": " . $pData["allaitementDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["allaitement"]); $i++) {
    $item = $dom->createElement ("item", "allaitement maternel-" . ($i + 1) . ": " . $pData["allaitement"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["ViralLoad"]); $i++) {
    $item = $dom->createElement ("item", "Valeur de la charge virale-" . ($i + 1) . ": " . $pData["ViralLoad"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["discontinuationDate"]); $i++) {
    $item = $dom->createElement ("item", "Date de discontinuation ARV-" . ($i + 1) . ": " . $pData["discontinuationDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["transferDate"]); $i++) {
    $item = $dom->createElement ("item", "Date de transfert du patient-" . ($i + 1) . ": " . $pData["transferDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["InstTransfer"]); $i++) {
    $item = $dom->createElement ("item", "Code de l'institution de transfert du patient-" . ($i + 1) . ": " . $pData["InstTransfer"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["THb"]); $i++) {
    $item = $dom->createElement ("item", "Taux d'hemoglobine-" . ($i + 1) . ": " . $pData["THb"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["THepb"]); $i++) {
    $item = $dom->createElement ("item", "Resultat de test Hepatite B-" . ($i + 1) . ": " . $pData["THepb"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["HepbDate"]); $i++) {
    $item = $dom->createElement ("item", "Date de test Hepatite B-" . ($i + 1) . ": " . $pData["HepbDate"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["BMI"]); $i++) {
    $item = $dom->createElement ("item", "BMI-" . ($i + 1) . ": " . $pData["BMI"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["artMedName"]); $i++) {
    $item = $dom->createElement ("item", "ART Med. Name-" . ($i + 1) . ": " . $pData["artMedName"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["artMedStart"]); $i++) {
    $item = $dom->createElement ("item", "ART Med. Start-" . ($i + 1) . ": " . $pData["artMedStart"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["artMedStop"]); $i++) {
    $item = $dom->createElement ("item", "ART Med. End-" . ($i + 1) . ": " . $pData["artMedStop"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["artMedDose"]); $i++) {
    $item = $dom->createElement ("item", "ART Med. Dosage-" . ($i + 1) . ": " . $pData["artMedDose"][$i]);
    $list->appendChild ($item);
  }
  for ($i = 0; $i < count ($pData["artMedDose"]); $i++) {
    $item = $dom->createElement ("item", "ART Med. Dosage-" . ($i + 1) . ": " . $pData["artMedDose"][$i]);
    $list->appendChild ($item);
  }
  if (!empty ($pData["firstStage3"])) {
    $item = $dom->createElement ("item", "Date first progression to Stage III: " . $pData["firstStage3"]);
    $list->appendChild ($item);
  }
  if (!empty ($pData["firstStage4"])) {
    $item = $dom->createElement ("item", "Date first progression to Stage IV: " . $pData["firstStage4"]);
    $list->appendChild ($item);
  }
  if (!empty ($pData["lastService"])) {
    $item = $dom->createElement ("item", "Date of last service event: " . $pData["lastService"]);
    $list->appendChild ($item);
  }
  if ($list->hasChildNodes()) {
    $text->appendChild ($list);
  }
  $section->appendChild ($text);
  $component5->appendChild ($section);
  $structBody->appendChild ($component5);

  // End of Body
  $component->appendChild ($structBody);
  $cda->appendChild ($component);

  return ($cda);
}

// XML element creation helper
function genXmlItem ($dom, &$list, $val, $label, $other = "") {
  if (!empty ($val)  || $val == 0) {
    switch ($val) {
      case 1: $tmp = "Oui";
              if (!empty ($other) && !empty ($other[0])) {
                $tmp .= ", " . $other[1] . " " . $other[0];
              }
              break;
      case 2: $tmp = "Non"; break;
      case 4: $tmp = "Inconnu"; break;
      default: $tmp = "N/A";
               if (!empty ($other) && !empty ($other[0])) {
                 $tmp .= ", " . $other[1] . " " . $other[0];
               }
               break;
    }
    if (!empty ($tmp)) {
      $item = $dom->createElement ("item", "$label $tmp");
      $list->appendChild ($item);
    }
  }
}

// Anonymize patientID
function anonymizePid ($pid) {
  $anonID = "";

  $result = dbQuery ("SELECT patientLookup_id FROM caseNotification.patientLookup WHERE dbSite = '" . DB_SITE . "' AND patientID = '$pid' AND siteCode = '" . substr ($pid, 0, 5) . "'");

  while ($row = psRowFetch ($result)) {
    $anonID = $row[0];
  }

  if (strlen ($anonID) < 1) {
    $result = dbQuery ("INSERT caseNotification.patientLookup (dbSite, siteCode, patientID) VALUES ('" . DB_SITE . "', '" . substr ($pid, 0, 5) . "', '$pid')");
    $result = dbQuery ("SELECT patientLookup_id FROM caseNotification.patientLookup WHERE dbSite = '" . DB_SITE . "' AND patientID = '$pid' AND siteCode = '" . substr ($pid, 0, 5) . "'");

    while ($row = psRowFetch ($result)) {
      $anonID = $row[0];
    }
  }

  return ($anonID);
}

// Create a database for keeping case notification metadata
function createNotifDb () {
  if (DB_TYPE == "mssql") {
    $result = dbQuery ("if not exists (select * from master.dbo.sysdatabases where [name] = 'caseNotification') create database caseNotification");
    $result = dbQuery ("use caseNotification");
    $result = dbQuery ("if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[patientLookup]') and OBJECTPROPERTY(id, N'IsTable') = 1) create table patientLookup (patientLookup_id bigint identity not null, dbSite bigint not null, siteCode char(10), patientID varchar(11))");
  } else {
  }
}

// Determine if patient is pregnant at given date
function caseNotifIsPregnant ($pid, $dt, $tbls) {
  /* try to determine pregnancy using intake, f/u and lab forms */
  $isPreg = 0;
//  $result = dbQuery ("SELECT TOP 1 value FROM (SELECT patientID, pregnant AS value, visitdate FROM v_vitals WHERE patientID = '$pid' AND visitdate BETWEEN DATEADD(mm, -9, '$dt') AND '$dt' AND pregnant IN (1, 2) UNION SELECT patientID, CONVERT(int, result) AS value, dbo.ymdToDate(resultDateYy, resultDateMm, resultDateDd) AS visitdate FROM v_labsCompleted WHERE patientID = '$pid' AND labID IN (134) AND ISDATE(dbo.ymdToDate(resultDateYy, resultDateMm, resultDateDd)) = 1 AND dbo.ymdToDate(resultDateYy, resultDateMm, resultDateDd) BETWEEN DATEADD(mm, -9, '$dt') AND '$dt' AND result IN (1, 2)) t1 ORDER BY visitdate DESC");
//
//  while ($row = psRowFetch ($result)) {
//    if ($row[0] == 1) $isPreg = $row[0];
//  }
//
  $result = dbQuery ("
    SELECT patientID
    FROM " . $tbls[1] . " t1
    WHERE patientID = '$pid'");

  while ($row = psRowFetch ($result)) {
    if (!empty ($row[0])) {
      $isPreg = 1;
    }
  }

  return ($isPreg);
}

function caseNotifMonthDiff ($cnt, $timestamp = null) {
  /* add $cnt months to $timestamp ('now' if null), return date as "Ymd" */
  if (is_null ($timestamp)) $timestamp = time ();

  /* Fix $timestamp if needed */
  $timestamp = fixBadDates ($timestamp);
  if (strtotime ($timestamp) === false) return (date ("Ymd", strtotime ("2099-12-31")));

  $midmonth = mktime (12, 0, 0, date ('n', $timestamp) + $cnt, 15, date ('Y', $timestamp));
  return date ("Ymd", date ('t', $timestamp) > date ('t', $midmonth) ? strtotime (date ("t F Y", $midmonth)) : strtotime (date (date ('d', $timestamp) . " F Y", $midmonth)));
}

function caseNotifDayDiff ($cnt, $timestamp = null) {
  /* add $cnt days to $timestamp ('now' if null), return date as "Ymd" */
  if (is_null ($timestamp)) $timestamp = time ();

  /* Fix $timestamp if needed */
  $timestamp = fixBadDates ($timestamp);
  if (strtotime ($timestamp) === false) return (date ("Ymd", strtotime ("2099-12-31")));

  return date ("Ymd", $timestamp + ($cnt * 86400));
}

function fixBadDates ($in) {
  /* if bad date input, fix it if possible, otherwise return -1 */
  if (empty ($in)) $in = "BAD_DATE";
  if (strtotime ($in) === false) {
    if (strtotime (substr ($in, 0, 6) . "15") === false) {
      if (strtotime (substr ($in, 0, 4) . "0615") === false) {
        $in = "BAD_DATE";
      } else {
        $in = substr ($in, 0, 4) . "0615";
      }
    } else {
      $in = substr ($in, 0, 6) . "15";
    }
  }
  return ($in);
}

function specialChars ($in) {
  $out = "";

  /* remove/replace special characters so they don't mung up the XML */
  $out = str_replace ("<", "", str_replace ("&", "", trim($in)));

  /* also replace any HTML entities with their UTF-8 equivalents */
  $out = str_replace ("#xe9;", "é", $out);

  return ($out);
}

function cdaDigest ($in) {
  $copy = $in->cloneNode (true);

  // Convert cloned node into SimpleXML object
  $case = simplexml_import_dom ($copy);
 
  // Truncate any fields we don't care about that may be different, then
  // hash the XML and return digest.
  $case->effectiveTime["value"] = "";
  $case->author->time["value"] = "";
  $case->author->assignedAuthor->id["extension"] = "";
  $case->documentationOf->serviceEvent->effectiveTime->high["value"] = "";
  $case->documentationOf->serviceEvent->performer->time->high["value"] = "";

  return (hash ('sha256', $case->asXML()));
}
