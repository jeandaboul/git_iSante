<?php
if ($tabsOn) {
  echo $tabHeaders;
}
echo "
<div id=\"tab-panes\">";

if (!$tabsOn) {
  include ("include/nurseSection.php");
}

echo "
<div id=\"pane1\">
  <table class=\"header\">
   <tr>
    <td valign=\"top\" width=\"50%\">
     <table class=\"b_header_nb\">
      <tr>
       <td class=\"s_header\" colspan=\"4\">" . $vitalTemp[$lang][0] . "</td>
      </tr>
      <tr>
	    <td  id=\"vitalTempTitle\" width=\"5%\">" . $vitalTemp[$lang][1] . "</td>
	    <td  colspan=\"3\" width=\"45%\"><input tabindex=\"1101\" id=\"vitalTemp\" name=\"vitalTemp\" " . getData ("vitalTemp", "text") . " type=\"text\" size=\"10\" maxlength=\"64\"> <i>" . $vitalTempUnits[$lang][1] . " </i></td>
      </tr>
      <tr>
       <td id=\"vitalBp1Title\">" . $vitalBp1[$lang][1] . "</td>
       <td colspan=\"3\" width=\"40%\">
       <table><tr><td><input tabindex=\"1104\" id=\"vitalBp1\"  name=\"vitalBp1\" " . getData ("vitalBp1", "text") . " type=\"text\" size=\"10\" maxlength=\"64\">
       &nbsp;/</td><td  id=\"vitalBp2Title\"></td>
       <td><input tabindex=\"1105\" id=\"vitalBp2\" name=\"vitalBp2\" " . getData ("vitalBp2", "text") . " type=\"text\" size=\"10\" maxlength=\"64\"></td></tr></table></td>

      </tr>
      <tr>
      	<td>&nbsp;</td>
     	<td valign=\"top\" colspan=\"3\"><table><tr><td id=\"vitalBpUnitTitle\"><i>" . $vitalBPUnits[$lang][0] . "</td><td> <input tabindex=\"1106\" id=\"vitalBpUnit1\"  name=\"vitalBPUnits[]\" " . getData ("vitalBPUnits", "radio", 1) . " type=\"radio\" value=\"1\">" . $vitalBPUnits[$lang][1] . " <input tabindex=\"1107\" id=\"vitalBpUnit2\" name=\"vitalBPUnits[]\" " . getData ("vitalBPUnits", "radio", 2) . " type=\"radio\" value=\"2\">" . $vitalBPUnits[$lang][2] . "</i></td></tr></table></td>
      <tr>
       <td id=\"vitalHrTitle\">" . $vitalHr[$lang][1] . "</td>
       <td colspan=\"3\" width=\"45%\"><input tabindex=\"1108\" id=\"vitalHr\" name=\"vitalHr\" " . getData ("vitalHr", "text") . " type=\"text\" size=\"10\" maxlength=\"64\"></td>
      </tr>
      <tr>
       <td id=\"vitalWeightTitle\">" . $vitalWeight[$lang][1] . "</td>
       <td colspan=\"3\" width=\"45%\"><input tabindex=\"1109\" id=\"vitalWeight\" name=\"vitalWeight\" " . getData ("vitalWeight", "text") . " type=\"text\" size=\"10\" maxlength=\"64\">
       <table><tr><td><i>" . $vitalWeightUnits[$lang][0] . " </i><td  id=\"vitalWeightUnitTitle\"></td><td><input tabindex=\"1110\" id=\"vitalWeightUnit1\" name=\"vitalWeightUnits[]\" " . getData ("vitalWeightUnits", "radio", 1) . " type=\"radio\" value=\"1\">" . $vitalWeightUnits[$lang][1] . " <input tabindex=\"1111\" id=\"vitalWeightUnit2\" name=\"vitalWeightUnits[]\" " . getData ("vitalWeightUnits", "radio", 2) . " type=\"radio\" value=\"2\">" . $vitalWeightUnits[$lang][2] . "</i></td></tr></table></td>
      </tr>
      <tr>
       <td id=\"vitalRrTitle\">" . $vitalRr[$lang][1] . "</td>
       <td width=\"10%\" ><input tabindex=\"1112\" id=\"vitalRr\" name=\"vitalRr\" " . getData ("vitalRr", "text") . " type=\"text\" size=\"10\" maxlength=\"64\"></td>
       <td id=\"vitalHeightTitle\">&nbsp;" . $vitalHeight[$lang][0] . "</td>
       <td><table><tr><td><input tabindex=\"1113\" id=\"vitalHeight\" name=\"vitalHeight\" " . getData ("vitalHeight", "text") . " type=\"text\" size=\"3\" maxlength=\"64\"><i>" . $vitalHeight[$lang][1] . " </i></td>
       <td id=\"vitalHeightCmTitle\"></td><td><input tabindex=\"1114\"  id=\"vitalHeightCm\" name=\"vitalHeightCm\" " . getData ("vitalHeightCm", "text") . " type=\"text\" size=\"3\" maxlength=\"64\"><i>" . $vitalHeight[$lang][2] . "</i></td></tr></table></td>
      </tr>
     </table>

     <table class=\"b_header_nb\">
      <tr>
       <td class=\"s_header\">" . $functionalStatus[$lang][0] . "</td>
      </tr>
      <tr>
       <td><table><tr><td id=\"functionalStatusTitle\"></td><td><i>" . $functionalStatus[$lang][1] . "</i></td></tr></table></td>
      </tr>
      <tr>
       <td><input tabindex=\"1201\" id=\"functionalStatus1\" name=\"functionalStatus[]\" " . getData ("functionalStatus", "radio", 1) . " type=\"radio\" value=\"1\">" . $functionalStatus[$lang][2] . "</td>
      </tr>
      <tr>
       <td><input tabindex=\"1202\" id=\"functionalStatus2\" name=\"functionalStatus[]\" " . getData ("functionalStatus", "radio", 2) . " type=\"radio\" value=\"2\">" . $functionalStatus[$lang][3] . "</td>
      </tr>
      <tr>
       <td><input tabindex=\"1203\" id=\"functionalStatus4\" name=\"functionalStatus[]\" " . getData ("functionalStatus", "radio", 4) . " type=\"radio\" value=\"4\">" . $functionalStatus_1[$lang][0] . "</td>
      </tr>
     </table>

     <table class=\"b_header_nb\">
      <tr>
       <td class=\"s_header\" colspan=\"4\">" . $sexInt[$lang][0] . "</td>
      </tr>
      <tr>
       <td colspan=\"4\"><i>" . $sexInt[$lang][1] . "</i></td>
      </tr>
      <tr>
       <td>" . $sexInt[$lang][2] . "</td>
       <td><input tabindex=\"1301\" name=\"sexInt[]\" " . getData ("sexInt", "radio", 1) . " type=\"radio\" value=\"1\">" . $sexInt[$lang][3] . "</td>
       <td><input tabindex=\"1302\" name=\"sexInt[]\" " . getData ("sexInt", "radio", 2) . " type=\"radio\" value=\"2\">" . $sexInt[$lang][4] . "</td>
       <td><input tabindex=\"1303\" name=\"sexInt[]\" " . getData ("sexInt", "radio", 4) . " type=\"radio\" value=\"4\">" . $sexInt[$lang][5] . "</td>
      </tr>
      <tr>
       <td>" . $sexIntWOcondom[$lang][0] . "</td>
       <td><input tabindex=\"1304\" name=\"sexIntWOcondom[]\" " . getData ("sexIntWOcondom", "radio", 1) . " type=\"radio\" value=\"1\">" . $sexIntWOcondom[$lang][1] . "</td>
       <td><input tabindex=\"1305\" name=\"sexIntWOcondom[]\" " . getData ("sexIntWOcondom", "radio", 2) . " type=\"radio\" value=\"2\">" . $sexIntWOcondom[$lang][2] . "</td>
       <td><input tabindex=\"1306\" name=\"sexIntWOcondom[]\" " . getData ("sexIntWOcondom", "radio", 4) . " type=\"radio\" value=\"4\">" . $sexIntWOcondom[$lang][3] . "</td>
      </tr>
     </table>

     <table class=\"b_header_nb\">
      <tr>
       <td class=\"s_header\" colspan=\"2\">" . $genStat[$lang][0] . "</td>
      </tr>
      <tr>
       <td><input tabindex=\"1401\" name=\"genStat[]\" " . getData ("genStat", "radio", 1) . " type=\"radio\" value=\"1\">" . $genStat[$lang][1] . "</td>
       <td><input tabindex=\"1404\" name=\"genStat[]\" " . getData ("genStat", "radio", 2) . " type=\"radio\" value=\"2\">" . $genStat[$lang][2] . "</td>
      </tr>
      <tr>
       <td><input tabindex=\"1402\" name=\"genStat[]\" " . getData ("genStat", "radio", 4) . " type=\"radio\" value=\"4\">" . $genStat[$lang][3] . "</td>
       <td><input tabindex=\"1405\" name=\"genStat[]\" " . getData ("genStat", "radio", 8) . " type=\"radio\" value=\"8\">" . $genStat[$lang][4] . "</td>
      </tr>
      <tr>
       <td><input tabindex=\"1403\" name=\"genStat[]\" " . getData ("genStat", "radio", 16) . " type=\"radio\" value=\"16\">" . $genStat[$lang][5] . "</td>
       <td>&nbsp;</td>
      </tr>
      <tr>
       <td>" . $hospitalized[$lang][0] . " </td>
       <td><input tabindex=\"1406\" name=\"hospitalized[]\" " . getData ("hospitalized", "radio", 1) . " type=\"radio\" value=\"1\">" . $hospitalized[$lang][1] . " <input tabindex=\"1407\" name=\"hospitalized[]\" " . getData ("hospitalized", "radio", 2) . " type=\"radio\" value=\"2\">" . $hospitalized[$lang][2] . " <input tabindex=\"1408\" name=\"hospitalized[]\" " . getData ("hospitalized", "radio", 4) . " type=\"radio\" value=\"4\">" . $hospitalized[$lang][3] . "</td>
      </tr>
      <tr>
       <td colspan=\"2\">" . $hospitalizedText[$lang][1] . "</td>
      </tr>
      <tr>
       <td colspan=\"2\"><input tabindex=\"1409\" name=\"hospitalizedText\" " . getData ("hospitalizedText", "text") . " type=\"text\" size=\"60\" maxlength=\"255\"></td>
      </tr>
     </table>
    </td>
    <td valign=\"top\" class=\"vert_line\">&nbsp;</td>
    <td valign=\"top\" class=\"left_pad\" width=\"50%\">
     <table class=\"b_header_nb\">
      <tr>
       <td class=\"s_header\" colspan=\"3\">" . $pregnant[$lang][0] . "</td>
      </tr>
      <tr>
       <td colspan=\"3\"><span><input tabindex=\"1501\" class=\"preg\" id=\"pregY\" name=\"pregnant[]\" " . getData ("pregnant", "radio", 1) . " type=\"radio\" value=\"1\">" . $pregnant[$lang][1] . "
       <input tabindex=\"1502\" class=\"preg\" id=\"pregN\" name=\"pregnant[]\" " . getData ("pregnant", "radio", 2) . " type=\"radio\" value=\"2\">" . $pregnant[$lang][2] . "
       <input tabindex=\"1503\" class=\"preg\" id=\"pregU\" name=\"pregnant[]\" " . getData ("pregnant", "radio", 4) . " type=\"radio\" value=\"4\">" . $pregnant[$lang][3] . "</span></td>
      </tr>
      <tr>
       <td id=\"pregnantLmpDtTitle\"  width=\"40%\" >" . $pregnantLmpDd[$lang][0] . "</td>
       <td width=\"20%\"><input tabindex=\"1504\" id=\"pregnantLmpDt\" name=\"pregnantLmpDt\" value=\"" . getData ("pregnantLmpDd", "textarea") . "/". getData ("pregnantLmpMm", "textarea") ."/". getData ("pregnantLmpYy", "textarea") . "\" type=\"text\" size=\"8\" maxlength=\"8\"><input  id=\"pregnantLmpDd\" name=\"pregnantLmpDd\" " . getData ("pregnantLmpDd", "text") . " type=\"hidden\" ><input tabindex=\"1505\" id=\"pregnantLmpMm\" name=\"pregnantLmpMm\" " . getData ("pregnantLmpMm", "text") . " type=\"hidden\" ><input tabindex=\"1506\" id=\"pregnantLmpYy\" name=\"pregnantLmpYy\" " . getData ("pregnantLmpYy", "text") . " type=\"hidden\" ></td>
	   <td width=\"35%\">&nbsp;<i>" . $pregnantLmpYy[$lang][2] . "</i></td>
	  </tr>
      <tr>
       <td colspan=\"3\" >" . $pregnantPrenatal[$lang][0] . "</td>
      </tr>
      <tr>
       <td colspan=\"3\"><span><input tabindex=\"1507\" class=\"pregPrenat\" id=\"pregPrenatY\" name=\"pregnantPrenatal[]\" " . getData ("pregnantPrenatal", "radio", 1) . " type=\"radio\" value=\"1\">" . $pregnantPrenatal[$lang][1] . " <input class=\"pregPrenat\" id=\"pregPrenatN\" tabindex=\"1508\" name=\"pregnantPrenatal[]\" " . getData ("pregnantPrenatal", "radio", 2) . " type=\"radio\" value=\"2\">" . $pregnantPrenatal[$lang][2] . "</span></td>
      </tr>
 	  <tr>
        <td id=\"pregnantPrenatalFirstDtTitle\">" . $pregnantPrenatalFirstDd[$lang][0] . "</td>
		<td><input tabindex=\"1509\" class=\"pregPrenat\" id=\"pregnantPrenatalFirstDt\" name=\"pregnantPrenatalFirstDt\" value=\"" . getData ("pregnantPrenatalFirstDd", "textarea") . "/". getData ("pregnantPrenatalFirstMm", "textarea") ."/". getData ("pregnantPrenatalFirstYy", "textarea") . "\" type=\"text\" size=\"8\" maxlength=\"8\"><input id=\"pregnantPrenatalFirstDd\"  name=\"pregnantPrenatalFirstDd\" " . getData ("pregnantPrenatalFirstDd", "text") . " type=\"hidden\"><input tabindex=\"1510\" id=\"pregnantPrenatalFirstMm\" name=\"pregnantPrenatalFirstMm\" " . getData ("pregnantPrenatalFirstMm", "text") . " type=\"hidden\"><input tabindex=\"1511\" id=\"pregnantPrenatalFirstYy\" name=\"pregnantPrenatalFirstYy\" " . getData ("pregnantPrenatalFirstYy", "text") . " type=\"hidden\"></td>
	    <td><i>" . $pregnantPrenatalFirstYy[$lang][2] . "</i></td>
 	  </tr>
      <tr>
       <td id=\"pregnantPrenatalLastDtTitle\">" . $pregnantPrenatalLastDd[$lang][0] . "</td>
	   <td><input tabindex=\"1512\" class=\"pregPrenat\"  id=\"pregnantPrenatalLastDt\" name=\"pregnantPrenatalLastDt\" value=\"" . getData ("pregnantPrenatalLastDd", "textarea") . "/". getData ("pregnantPrenatalLastMm", "textarea") ."/". getData ("pregnantPrenatalLastYy", "textarea") . "\" type=\"text\" size=\"8\" maxlength=\"8\"> <input id=\"pregnantPrenatalLastDd\" name=\"pregnantPrenatalLastDd\" " . getData ("pregnantPrenatalLastDd", "text") . " type=\"hidden\"><input tabindex=\"1513\" id=\"pregnantPrenatalLastMm\" name=\"pregnantPrenatalLastMm\" " . getData ("pregnantPrenatalLastMm", "text") . " type=\"hidden\"><input tabindex=\"1514\" id=\"pregnantPrenatalLastYy\" name=\"pregnantPrenatalLastYy\" " . getData ("pregnantPrenatalLastYy", "text") . " type=\"hidden\"> </td>
	   <td><i>" . $pregnantPrenatalLastYy[$lang][2] . "</i></td>
	  </tr>
      <tr>
       <td colspan=\"3\"><i>" . $pregnantNoPrenatal[$lang][1] . "</i></td>
      </tr>
     </table>
";

$tabIndex = 1600;
include ("include/tbStatus_followup_0.php");

echo"
    </td>
   </tr>
   <tr>
    <td colspan=\"3\">
     <table class=\"b_header_nb\">
      <tr>
       <td class=\"s_header\">" . $followupNotes[$lang][1] . "</td>
      </tr>
      <tr>
       <td><textarea tabindex=\"1701\" name=\"followupNotes\" cols=\"80\" rows=\"4\">" . getData ("followupNotes", "textarea") . "</textarea></td>
      </tr>
     </table>
    </td>
   </tr>
  </table>
 </div>
";

if (!$tabsOn) {
  include ("include/doctorSection.php");
}

$tabIndex = 2000;
echo "<div id=\"pane2\">
  <table class=\"header\">";
include ("symptoms/adult.php");
echo "
	</table>
  </div>";

$tabIndex = 3000;
echo "<div id=\"pane3\">
  <table class=\"header\">";
include ("include/conditions.php");
echo "
	</table>
  </div>";

$tabIndex = 4000;
echo "<div id=\"pane4\">
  <table class=\"header\">";
include ("include/clinicalExam.php");
echo "
	</table>
  </div>";

echo "
<!-- ******************************************************************** -->
<!-- ******************************* ARV ******************************** -->
<!-- ********************* (tab indices 5001 - 6000) ******************** -->
<!-- ******************************************************************** -->
";

$arv_rows = arvRows (5101, 1);

echo "<div id=\"pane5\">
  <table class=\"header\">
   <tr>
    <td>
     <table class=\"b_header_nb\" width=\"100%\" cellspacing =\"0\" cellpadding=\"0\" border=\"0\">
      <tr>
       <td class=\"s_header\" colspan=\"17\" width=\"100%\">" . $followupTreatment_header[$lang][1] . "</td>
      </tr>
      <tr>
       <td colspan=\"17\">" . $arvEver[$lang][0] . " <span><input tabindex=\"5001\" id=\"arvY\" name=\"arvEver[]\" " . getData ("arvEver", "radio", 1) . " type=\"radio\" value=\"1\">" . $arvEver[$lang][1] . " <input tabindex=\"5002\" id=\"arvN\"  name=\"arvEver[]\" " . getData ("arvEver", "radio", 2) . " type=\"radio\" value=\"2\">" . $arvEver[$lang][3] . "</span></td>
      </tr>
      <tr>
       <td colspan=\"17\"><i>" . $arv_header[$lang][3] . "</i></td>
      </tr>
      <tr>
       <td rowspan=\"3\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
       <td class=\"sm_header_cnt_np\">" . $followupTreatment_subhead1[$lang][0] . "</td>
       <td width=\"3%\">&nbsp;</td>
       <td class=\"sm_header_lt_np\">" . $followupTreatment_subhead1[$lang][1] . "</td>
       <td colspan=\"5\" class=\"sm_header_lt_np\">" . $followupTreatment_subhead1[$lang][2] . "</td>
       <td width=\"3%\">&nbsp;</td>
       <td colspan=\"7\" class=\"sm_header_lt_np\">" . $followupTreatment_subhead1[$lang][3] . "</td>
      </tr>
      <tr>
       <td class=\"sm_header_cnt_np\">" . $followupTreatment_subhead2[$lang][0] . "</td>
       <td width=\"3%\">&nbsp;</td>
       <td class=\"sm_header_lt_np\">" . $followupTreatment_subhead2[$lang][1] . "</td>
       <td colspan=\"2\">&nbsp;</td>
       <td class=\"sm_header_cnt_np\">" . $followupTreatment_subhead2[$lang][2] . "</td>
       <td class=\"sm_header_cnt_np\">" . $followupTreatment_subhead2[$lang][3] . "</td>
       <td class=\"sm_header_cnt_np\">" . $followupTreatment_subhead2[$lang][4] . "</td>
       <td colspan=\"8\">&nbsp;</td>
      </tr>
      <tr>
       <td colspan=\"3\">&nbsp;</td>
       <td class=\"sm_header_cnt_np\">" . $followupTreatment_subhead3[$lang][0] . "</td>
       <td class=\"sm_header_cnt_np\">" . $followupTreatment_subhead3[$lang][1] . "</td>
       <td class=\"sm_header_cnt_np\">" . $followupTreatment_subhead3[$lang][2] . "</td>
       <td class=\"sm_header_cnt_np\">" . $followupTreatment_subhead3[$lang][3] . "</td>
       <td class=\"sm_header_cnt_np\">" . $followupTreatment_subhead3[$lang][4] . "</td>
       <td width=\"3%\">&nbsp;</td>
       <td class=\"sm_header_cnt_np\">" . $arv_subhead9[$lang][0] . "</td>
       <td class=\"sm_header_cnt_np\">" . $arv_subhead9[$lang][1] . "</td>
       <td class=\"sm_header_cnt_np\">" . $arv_subhead9[$lang][2] . "</td>
       <td class=\"sm_header_cnt_np\">" . $arv_subhead10[$lang][0] . "</td>
       <td class=\"sm_header_cnt_np\">" . $arv_subhead10[$lang][1] . "</td>
       <td class=\"sm_header_cnt_np\">" . $arv_subhead10[$lang][2] . "</td>
       <td class=\"sm_header_cnt_np\">" . $arv_subhead11[$lang][0] . "</td>
      </tr>
      <tr>
       <td class=\"top_line\" colspan=\"17\">&nbsp;</td>
      </tr>
      <tr>
       <td colspan=\"9\"><b>
         <a class=\"toggle_display\"
            onclick=\"toggleDisplay(0,$arvSubHeadElems[0]);\"
            title=\"Toggle display\">
            <span id=\"section0Y\" style=\"display:none\">(+)</span>
            <span id=\"section0N\">(-)&nbsp;</span>" .
              $arv_subhead3[$version][$lang][1] .
         "</a></b></td>
       <!--td rowspan=\"22\" class=\"vert_line\" width=\"5%\">&nbsp;</td-->
       <td>&nbsp;</td>
       <td colspan=\"7\">&nbsp;</td>
      </tr>" . $arv_rows . "
      <tr>
       <td class=\"sm_lt\" colspan=\"17\"><b>" . $arv_subhead14[$lang][0] . "</b> &nbsp;" . $arv_subhead12[$lang][1] . " &nbsp;" . $arv_subhead14[$lang][1] . " &nbsp;" . $arv_subhead18[$lang][0] . " &nbsp;" . $arv_subhead18[$lang][1] . " &nbsp;" . $arv_subhead18[$lang][2] . " &nbsp;" . $arv_subhead14[$lang][2] . " &nbsp;" . $arv_subhead13[$lang][0] . " &nbsp;" . $arv_subhead15[$lang][0] . " &nbsp;" . $arv_subhead13[$lang][1] . " &nbsp;" . $arv_subhead15[$lang][1] . " &nbsp;" . $arv_subhead13[$lang][2] . " &nbsp;" . $arv_subhead15[$lang][2] . "</td>
      </tr>
     </table>
    </td>
   </tr>

<!-- ******************************************************************** -->
<!-- ************************* Toxicity/Adherence *********************** -->
<!-- ********************* (tab indices 6001 - 7000) ******************** -->
<!-- ******************************************************************** -->

   <tr>
    <td>
     <table class=\"b_header_nb\">
      <tr>
       <td class=\"s_header\">" . $currToxicity_header[$lang][1] . "</td>
      </tr>
      <tr>
       <td><b>" . $currToxicityText[$lang][1] . "</b> <input tabindex=\"6001\" name=\"currToxicityText\" " . getData ("currToxicityText", "text") . " type=\"text\" size=\"80\" maxlength=\"255\"></td>
      </tr>
      <tr>
       <td>
        <span><input tabindex=\"6002\" name=\"currToxRash\" " . getData ("currToxRash", "checkbox") . " type=\"checkbox\" value=\"On\">" . $currToxRash[$lang][1] . "</span>
        <span><input tabindex=\"6003\" name=\"currToxAnemia\" " . getData ("currToxAnemia", "checkbox") . " type=\"checkbox\" value=\"On\">" . $currToxAnemia[$lang][1] . "</span>
        <span><input tabindex=\"6004\" name=\"currToxHep\" " . getData ("currToxHep", "checkbox") . " type=\"checkbox\" value=\"On\">" . $currToxHep[$lang][1] . "</span>
        <span><input tabindex=\"6005\" name=\"currToxCNS\" " . getData ("currToxCNS", "checkbox") . " type=\"checkbox\" value=\"On\">" . $currToxCNS[$lang][1] . "</span>
        <span><input tabindex=\"6006\" name=\"currToxHyper\" " . getData ("currToxHyper", "checkbox") . " type=\"checkbox\" value=\"On\">" . $currToxHyper[$lang][1] . "</span>
        <span><input tabindex=\"6007\" name=\"currToxOther\" " . getData ("currToxOther", "checkbox") . " type=\"checkbox\" value=\"On\">" . $currToxOther[$lang][1] . "
        <input tabindex=\"6008\" name=\"currToxText\" " . getData ("currToxText", "text") . " type=\"text\" size=\"40\" maxlength=\"20\"></span>
       </td>
      </tr>
      <tr>
       <td><b>" . $adherence[$lang][0] . "</b> " . $adherence[$lang][1] . "
        <span>
        <input tabindex=\"6101\" name=\"adherence[]\" " . getData ("adherence", "radio", 1) . " type=\"radio\" value=\"1\">" . $adherence[$lang][2] . "
        <input tabindex=\"6102\" name=\"adherence[]\" " . getData ("adherence", "radio", 2) . " type=\"radio\" value=\"2\">" . $adherence[$lang][3] . "
        <input tabindex=\"6103\" name=\"adherence[]\" " . getData ("adherence", "radio", 4) . " type=\"radio\" value=\"4\">" . $adherence[$lang][4] . "
        <input tabindex=\"6104\" name=\"adherence[]\" " . getData ("adherence", "radio", 8) . " type=\"radio\" value=\"8\">" . $adherence[$lang][5] . "
        <input tabindex=\"6105\" name=\"adherence[]\" " . getData ("adherence", "radio", 16) . " type=\"radio\" value=\"16\">" . $adherence[$lang][6] . "</span></td>
      </tr>
      <tr>
       <td>" . $doseProp[$lang][0] . "</td>
      </tr>
      <tr>
       <td><input tabindex=\"6201\" name=\"doseProp[]\" " . getData ("doseProp", "radio", 1) . " type=\"radio\" value=\"1\">" . $doseProp[$lang][1] . " &nbsp;&nbsp;&nbsp;&nbsp; <input tabindex=\"6202\" name=\"doseProp[]\" " . getData ("doseProp", "radio", 2) . " type=\"radio\" value=\"2\">" . $doseProp[$lang][2] . " &nbsp;&nbsp;&nbsp;&nbsp; <input tabindex=\"6203\" name=\"doseProp[]\" " . getData ("doseProp", "radio", 4) . " type=\"radio\" value=\"4\">" . $doseProp[$lang][3] . " &nbsp;&nbsp;&nbsp;&nbsp; <input tabindex=\"6204\" name=\"doseProp[]\" " . getData ("doseProp", "radio", 8) . " type=\"radio\" value=\"8\">" . $doseProp[$lang][4] . " &nbsp;&nbsp;&nbsp;&nbsp; <input tabindex=\"6205\" name=\"doseProp[]\" " . getData ("doseProp", "radio", 16) . " type=\"radio\" value=\"16\">" . $doseProp[$lang][5] . " &nbsp;&nbsp;&nbsp;&nbsp; <input tabindex=\"6206\" name=\"doseProp[]\" " . getData ("doseProp", "radio", 32) . " type=\"radio\" value=\"32\">" . $doseProp[$lang][6] . " &nbsp;&nbsp;&nbsp;&nbsp; <input tabindex=\"6207\" name=\"doseProp[]\" " . getData ("doseProp", "radio", 64) . " type=\"radio\" value=\"64\">" . $doseProp[$lang][7] . " &nbsp;&nbsp;&nbsp;&nbsp; <input tabindex=\"6208\" name=\"doseProp[]\" " . getData ("doseProp", "radio", 128) . " type=\"radio\" value=\"128\">" . $doseProp[$lang][8] . " &nbsp;&nbsp;&nbsp;&nbsp; <input tabindex=\"6209\" name=\"doseProp[]\" " . getData ("doseProp", "radio", 256) . " type=\"radio\" value=\"256\">" . $doseProp[$lang][9] . " &nbsp;&nbsp;&nbsp;&nbsp; <input tabindex=\"6210\" name=\"doseProp[]\" " . getData ("doseProp", "radio", 512) . " type=\"radio\" value=\"512\">" . $doseProp[$lang][10] . " &nbsp;&nbsp;&nbsp;&nbsp; <input tabindex=\"6211\" name=\"doseProp[]\" " . getData ("doseProp", "radio", 1024) . " type=\"radio\" value=\"1024\">" . $doseProp[$lang][11] . "</td>
      </tr>
      <tr>
       <td><i>" . $doseProp[$lang][12] . "</i></td>
      </tr>
      <tr>
       <td class=\"s_header\">" . $adherenceComments[$lang][1] . "</td>
      </tr>
      <tr>
       <td><textarea tabindex=\"6301\" name=\"adherenceComments\" cols=\"80\" rows=\"5\">" . getData ("adherenceComments", "textarea") . "</textarea></td>
      </tr>
     </table>
    </td>
   </tr>

<!-- ******************************************************************** -->
<!-- ********************** Other Current Treatments ******************** -->
<!-- ********************* (tab indices 7001 - 8000) ******************** -->
<!-- ******************************************************************** -->

   <tr>
    <td>
     <table class=\"b_header_nb\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
      <tr>
       <td class=\"s_header\" colspan=\"9\">" . $allergies_header[$lang][0] . "</td>
      </tr>
      <tr>
       <td colspan=\"9\"><i>" . $allergies_header[$lang][3] . "</i></td>
      </tr>
      <tr>
       <td class=\"bottom_line\" colspan=\"9\" width=\"100%\">&nbsp;</td>
      </tr>
      <tr>
       <td class=\"sm_header_lt\">&nbsp;</td>
       <td class=\"sm_header_cnt\">" . $allergies_subhead2[$lang][0] . "</td>
       <td class=\"sm_header_lt\">&nbsp;</td>
       <td class=\"sm_header_lt\">" . $allergies_subhead1[$lang][2] . "</td>
       <td class=\"sm_header_lt\">&nbsp;</td>
       <td class=\"sm_header_lt\">&nbsp;</td>
       <td class=\"sm_header_cnt\">" . $allergies_subhead2[$lang][0] . "</td>
       <td class=\"sm_header_lt\">&nbsp;</td>
       <td class=\"sm_header_lt\">" . $allergies_subhead1[$lang][2] . "</td>
      </tr>
      <tr>
       <td class=\"top_line\" colspan=\"9\" width=\"100%\">&nbsp;</td>
      </tr>
      <tr>
       <td colspan=\"5\"><b>" . $allergies_subhead11[$lang][0] . "</b></td>
       <td colspan=\"4\"><b>" . $allergies_subhead11[$lang][1] . "</b></td>
      </tr>
      <tr class=\"alt\">
       <td class=\"small_cnt\"  id=\"ethambutolContinuedTitle\">" . $ethambutolMM[$lang][0] . "</td>
       <td class=\"sm_header_cnt_np\"><input tabindex=\"7001\"  id=\"ethambutolContinued\" name=\"ethambutolContinued\" " .
             getData ("ethambutolContinued", "checkbox") . " type=\"checkbox\" value=\"On\"></td>
       <td id=\"ethambutolStopTitle\">&nbsp;</td>
       <td><input tabindex=\"7002\" id=\"ethambutolStopMm\" name=\"ethambutolStopMm\" " .
          getData ("ethambutolStopMm", "text") .
         " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\">/<input tabindex=\"7003\" id=\"ethambutolStopYy\" name=\"ethambutolStopYy\" " .
           getData ("ethambutolStopYy", "text") .
         " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\"></td>
       <td>&nbsp;</td>
       <td id=\"acyclovirContinuedTitle\" class=\"small_cnt\">" . $acyclovirMM[$lang][0] . "</td>
       <td  class=\"sm_header_cnt_np\">
         <input tabindex=\"7016\" id=\"acyclovirContinued\" name=\"acyclovirContinued\" " . getData ("acyclovirContinued", "checkbox") .
           " type=\"checkbox\" value=\"On\"></td>
       <td id=\"acyclovirStopTitle\">&nbsp;</td>
       <td>
         <input tabindex=\"7017\" id=\"acyclovirStopMm\" name=\"acyclovirStopMm\" " .
           getData ("acyclovirStopMm", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\">" .
           showValidationIcon ($type, "acyclovirStopMm") .
         "/<input tabindex=\"7018\" id=\"acyclovirStopYy\" name=\"acyclovirStopYy\" " .
            getData ("acyclovirStopYy", "text") . " type=\"text\" size=\"2\"
            maxlength=\"2\" class=\"small_cnt\"></td>
      </tr>
      <tr>
       <td class=\"small_cnt\" id=\"isoniazidContinuedTitle\">" . $isoniazidMM[$lang][0] . "</td>
       <td  class=\"sm_header_cnt_np\"><input tabindex=\"7004\" id=\"isoniazidContinued\"  name=\"isoniazidContinued\" " . getData ("isoniazidContinued", "checkbox") . " type=\"checkbox\" value=\"On\"></td>
       <td id=\"isoniazidStopTitle\">&nbsp;</td>
       <td><input tabindex=\"7005\" id=\"isoniazidStopMm\" name=\"isoniazidStopMm\" " .
           getData ("isoniazidStopMm", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\">/<input tabindex=\"7006\" id=\"isoniazidStopYy\" name=\"isoniazidStopYy\" " .
           getData ("isoniazidStopYy", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\"></td>
       <td>&nbsp;</td>
       <td class=\"small_cnt\" id=\"cotrimoxazoleContinuedTitle\">" . $cotrimoxazoleMM[$lang][0] . "</td>
       <td  class=\"sm_header_cnt_np\"><input tabindex=\"7019\" id=\"cotrimoxazoleContinued\" name=\"cotrimoxazoleContinued\" " . getData ("cotrimoxazoleContinued", "checkbox") . " type=\"checkbox\" value=\"On\"></td>
       <td id=\"cotrimoxazoleStopTitle\">&nbsp;</td>
       <td><input tabindex=\"7020\" id=\"cotrimoxazoleStopMm\" name=\"cotrimoxazoleStopMm\" " .
           getData ("cotrimoxazoleStopMm", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\">/<input tabindex=\"7021\" id=\"cotrimoxazoleStopYy\" name=\"cotrimoxazoleStopYy\" " .
             getData ("cotrimoxazoleStopYy", "text") .
             " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\"></td>
      </tr>
      <tr class=\"alt\">
       <td class=\"small_cnt\" id=\"pyrazinamideContinuedTitle\">" . $pyrazinamideMM[$lang][0] . "</td>
       <td  class=\"sm_header_cnt_np\"><input tabindex=\"7007\" id=\"pyrazinamideContinued\" name=\"pyrazinamideContinued\" " . getData ("pyrazinamideContinued", "checkbox") . " type=\"checkbox\" value=\"On\"></td>
       <td id=\"pyrazinamideStopTitle\">&nbsp;</td>
       <td><input tabindex=\"7008\"  id=\"pyrazinamideStopMm\" name=\"pyrazinamideStopMm\" " .
           getData ("pyrazinamideStopMm", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\">/<input tabindex=\"7009\" id=\"pyrazinamideStopYy\" name=\"pyrazinamideStopYy\" " .
           getData ("pyrazinamideStopYy", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\"></td>
       <td>&nbsp;</td>
       <td class=\"small_cnt\" id=\"fluconazoleContinuedTitle\">" . $fluconazoleMM[$lang][0] . "</td>
       <td  class=\"sm_header_cnt_np\"><input tabindex=\"7022\" id=\"fluconazoleContinued\" name=\"fluconazoleContinued\" " . getData ("fluconazoleContinued", "checkbox") . " type=\"checkbox\" value=\"On\"></td>
       <td id=\"fluconazoleStopTitle\">&nbsp;</td>
       <td><input tabindex=\"7023\"  id=\"fluconazoleStopMm\" name=\"fluconazoleStopMm\" " .
           getData ("fluconazoleStopMm", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\">/<input tabindex=\"7024\" id=\"fluconazoleStopYy\" name=\"fluconazoleStopYy\" " .
           getData ("fluconazoleStopYy", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\"></td>
      </tr>
      <tr>
       <td class=\"small_cnt\" id=\"rifampicineContinuedTitle\">" . $rifampicineMM[$lang][0] . "</td>
       <td  class=\"sm_header_cnt_np\"><input tabindex=\"7010\" id=\"rifampicineContinued\" name=\"rifampicineContinued\" " . getData ("rifampicineContinued", "checkbox") . " type=\"checkbox\" value=\"On\"></td>
       <td id=\"rifampicineStopTitle\">&nbsp;</td>
       <td><input tabindex=\"7011\" id=\"rifampicineStopMm\" name=\"rifampicineStopMm\" " .
           getData ("rifampicineStopMm", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\">/<input tabindex=\"7012\" id=\"rifampicineStopYy\" name=\"rifampicineStopYy\" " .
           getData ("rifampicineStopYy", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\"></td>
       <td>&nbsp;</td>
       <td class=\"small_cnt\" id=\"ketaconazoleContinuedTitle\">" . $ketaconazoleMM[$lang][0] . "</td>
       <td  class=\"sm_header_cnt_np\"><input tabindex=\"7025\" id=\"ketaconazoleContinued\" name=\"ketaconazoleContinued\" " . getData ("ketaconazoleContinued", "checkbox") . " type=\"checkbox\" value=\"On\"></td>
       <td id=\"ketaconazoleStopTitle\">&nbsp;</td>
       <td><input tabindex=\"7026\" id=\"ketaconazoleStopMm\" name=\"ketaconazoleStopMm\" " .
           getData ("ketaconazoleStopMm", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\">/<input tabindex=\"7027\" id=\"ketaconazoleStopYy\" name=\"ketaconazoleStopYy\" " .
           getData ("ketaconazoleStopYy", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\"></td>
      </tr>
      <tr class=\"alt\">
       <td class=\"small_cnt\" id=\"streptomycineContinuedTitle\">" . $streptomycineMM[$lang][0] . "</td>
       <td  class=\"sm_header_cnt_np\"><input tabindex=\"7013\" id=\"streptomycineContinued\" name=\"streptomycineContinued\" " . getData ("streptomycineContinued", "checkbox") . " type=\"checkbox\" value=\"On\"></td>
       <td id=\"streptomycineStopTitle\">&nbsp;</td>
       <td><input tabindex=\"7014\" id=\"streptomycineStopMm\" name=\"streptomycineStopMm\" " .
           getData ("streptomycineStopMm", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\">/<input tabindex=\"7015\" id=\"streptomycineStopYy\" name=\"streptomycineStopYy\" " .
           getData ("streptomycineStopYy", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\"></td>
       <td>&nbsp;</td>
       <td class=\"small_cnt\" id=\"traditionalContinuedTitle\">" . $traditionalStartMm[$lang][0] . "</td>
       <td class=\"sm_header_cnt_np\"><input tabindex=\"7028\" id=\"traditionalContinued\" name=\"traditionalContinued\" " . getData ("traditionalContinued", "checkbox") . " type=\"checkbox\" value=\"On\"></td>
       <td id=\"traditionalStopTitle\">&nbsp;</td>
       <td><input tabindex=\"7029\" id=\"traditionalStopMm\" name=\"traditionalStopMm\" " .
           getData ("traditionalStopMm", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\">/<input tabindex=\"7030\" id=\"traditionalStopYy\" name=\"traditionalStopYy\" " .
           getData ("traditionalStopYy", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\"></td>
      </tr>
      <tr>
       <td colspan=\"5\">&nbsp;</td>
       <td class=\"small_cnt\">" .
         $other1Text[$lang][1] .
         " <input tabindex=\"7031\" name=\"other3Text\" " .
           getData ("other3Text", "text") .
           " type=\"text\" size=\"20\" maxlength=\"255\" class=\"small_cnt\"></td>
       <td class=\"sm_header_cnt_np\"><table><tr><td><input tabindex=\"7032\" id=\"other3Continued\" name=\"other3Continued\" " . getData ("other3Continued", "checkbox") . " type=\"checkbox\" value=\"On\"></td><td id=\"other3ContinuedTitle\" align=\"right\">&nbsp;</td></tr></table></td>
       <td id=\"other3StopTitle\">&nbsp;</td>
       <td class=\"small_cnt\" ><input tabindex=\"7033\" id=\"other3StopMm\" name=\"other3SpMM\" " .
           getData ("other3SpMM", "text") .
           " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\">/<input tabindex=\"7034\" id=\"other3StopYy\" name=\"other3SpYY\" " .
             getData ("other3SpYY", "text") .
             " type=\"text\" size=\"2\" maxlength=\"2\" class=\"small_cnt\"></td>
      </tr>
     </table>
    </td>
   </tr>
  </table>
</div>";

$tabIndex = 8000;
echo "<div id=\"pane6\">
  <table class=\"header\">";
include ("include/medicalEligibility.php");
echo "
	</table>
 </div>
 <div id=\"pane7\">
  <table class=\"header\">
	   <tr>
		<td>
			 <table class=\"b_header_nb\" width=\"100%\" border=\"0\">
			  <tr>
			   <td class=\"s_header\">" . strtoupper($followupComments[$lang][1]) . "</td>
			  </tr>
			  <tr>
			   <td><textarea tabindex=\"9001\" name=\"followupComments\" cols=\"80\" rows=\"5\">" . getData ("followupComments", "textarea") . "</textarea></td>
			  </tr>
			 </table>
		</td>
	   </tr>
   </table>
   </div>
   ";
   include ("include/nextVisitDate.php");
echo"
 </div>
";

$formName = "followup";

$tabIndex = 10000;
echo "<script language=\"JavaScript\" type=\"text/javascript\" src=\"followup/0.js\"></script>";
?>
