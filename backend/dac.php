<?php
require_once ("backend.php");

require_once 'backend/config.php';
require_once 'backend/database.php';
require_once 'backend/materializedViews.php';
require_once "include/standardHeaderExt.php";

function generateDac($startdate, $enddate,$site, $lang) {
  $dateTime = $lang == "fr" ? date ("d/m/y H:i:s") : date ("m/d/y H:i:s");
  $siteName = getSiteName ($site, $lang);
  $period=date("d-M-Y", strtotime($startdate)).' To '.date("d-M-Y", strtotime($enddate));
 
  $queryArray = array(
"darck" => "
select 
concat('<a href=\"dacList.php?disp=1&site=".$site."&endDate=".$enddate."&startDate=".$startdate."&lang=".$lang."\">',dispensation,'</a>') as Communautaire,
concat('<a href=\"dacList.php?disp=0&site=".$site."&endDate=".$enddate."&startDate=".$startdate."&lang=".$lang."\">',institution,'</a>') as Institution,
concat(round((dispensation/(dispensation+institution))*100,2),'%') as Pourcentage,uniquePatient as 'Patient Unique'
from (
select  e.siteCode,
count(distinct case when o.value_boolean=1 then e.patientID else null end) as dispensation,
count(distinct case when ifnull(o.value_boolean,0)=0 then e.patientID else null end) as institution,
count(distinct e.patientID) as uniquePatient
from 
(select max(visitDate) as visitDate,patientID from v_prescriptions  e 
where drugid IN ( 1, 3, 4, 5, 6, 7, 8, 10, 11, 12, 15, 16, 17, 20, 21, 22, 23, 26, 27, 28, 29, 31, 32, 33, 34, 87, 88,89,90,91,92)
and e.dispensed=1
and e.visitDate between '".$startdate."' AND '".$enddate."' and  LEFT(e.patientid,5)=".$site." 
group by patientID
)p,v_prescriptions e 
 left outer join obs o  on (e.encounter_id=o.encounter_id and o.concept_id='71642' and o.value_boolean=1)
where  e.visitDate between '".$startdate."' AND '".$enddate."' and  LEFT(e.patientid,5)=".$site."
and p.visitDate=e.visitDate and p.patientID=e.patientID
and e.drugid IN ( 1, 3, 4, 5, 6, 7, 8, 10, 11, 12, 15, 16, 17, 20, 21, 22, 23, 26, 27, 28, 29, 31, 32, 33, 34, 87, 88,89,90,91,92)
and e.dispensed=1
 group by 1) A" ); 
  
  $darck = outputQueryRows($queryArray["darck"]); 
 
  $summary = <<<EOF
  
 <script type="text/javascript">
</script>
  
<html>
<head>
  <title></title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <style type="text/css">
    a {text-decoration: none}
	input { padding:3px; border:1px solid #F5C5C5; border-radius:2px; width:142px; }
  </style>  
</head>


<body text="#000000" link="#000000" alink="#000000" vlink="#000000" align="center">
<table width="90%" cellpadding="0" cellspacing="0" border="0">
<tr valign="top" >
  <td style="width: 30%;text-align: right; padding:15px;">
  <div><span style="font-family: Lucida Console; font-size: 12.0px;"><strong>Periode :</strong>   $period </span></div>
  </td>  
</tr>
</table>

<div>&nbsp;</div>
<div>&nbsp;</div>


<form >
<table width="100%" >
  <tr>
    <td width="70%">
	<p>&nbsp;</p>
	<div ><strong> Distribution des ARVs en communauté </strong></div>
	<div>&nbsp;</div>
	<div>$darck</div>
	<p>&nbsp;</p>	
	</td>
  </tr>
</table>


</form>
<!-- ********************************************************************* -->
<!-- ****************** Ped. imm. data goes here, if applicable ********** -->
<!-- ********************************************************************* -->
$relatives

</body>
</html>
EOF;

  return ($summary);
}



function outputQueryRows($qry) {
        $output = '';
        // execute the query 
        $arr = databaseSelect()->query($qry)->fetchAll(PDO::FETCH_ASSOC); 
        if (count($arr) == 0) return '<p><center><font color="red"><bold>Aucuns résultats trouvés</bold></font></center><p>';
        // set up the table
        $output = '<table class="" width="90%" border="1" cellpadding="0" cellspacing="0">';
        // loop on the results 
        $i = 0;
        foreach($arr as $row) {
               if ($i == 0) { 
                       // output the column header 
                       $output .= '<tr>';
                       foreach($row as $key => $value) $output .= '<th style="float:center;">' . $key . '</th>';
                       $output .= '</tr>'; 
                       $i++;
               } 
               $output .= '<tr>';
               foreach($row as $key => $value) $output .= '<td style="font-family: Lucida Console; font-size: 12.0px; padding:3px; float:center;">' . $value . '</td>';
               $output .= '</tr>';
        }
        // close the table 
        $output .= '</table>';
        return $output;
}

?>
