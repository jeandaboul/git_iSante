<?php
$encType = 20;

echo "
<!-- ******************************************************************** -->
<!-- ********************** Adherence Counseling ************************ -->
<!-- *(tab indices " . ($tabIndex + 1) . " - " . ($tabIndex + 1000) . ")* -->
<!-- ******************************************************************** -->
";
echo "
<div class=\"formArea\">
  <hr/>
  <table class=\"header\">
   <tr>
   	<td colspan=\"2\" width=\"100%\">"
   		. $adhEvaBy_0[$lang][0] . "
    </td>
   </tr>
   <tr>
   	<td width=\"50%\">
   	  <span><input tabindex=\"1001\" name=\"evaluationDoctor\" " . getData ("evaluationDoctor", "checkbox") . " type=\"checkbox\" value=\"On\"> ". $adhEvaBy_0[$lang][1] . "
      </span>
    </td>
    <td width=\"50%\">
	  <span><input tabindex=\"1002\" name=\"evaluationSocialWorker\" " . getData ("evaluationSocialWorker", "checkbox") . " type=\"checkbox\" value=\"On\"> ". $adhEvaBy_0[$lang][4] . "
      </span>
    </td>
   </tr>
   <tr>
	<td width=\"50%\">
	 <span><input tabindex=\"1003\" name=\"evaluationPharmacien\" " . getData ("evaluationPharmacien", "checkbox") . " type=\"checkbox\" value=\"On\"> ". $adhEvaBy_0[$lang][2] . "
	 </span>
	</td>
	<td width=\"50%\">
	 <span><input tabindex=\"1004\" name=\"evaluationAgent\" " . getData ("evaluationAgent", "checkbox") . " type=\"checkbox\" value=\"On\"> ". $adhEvaBy_0[$lang][5] . "
	 </span>
	</td>
   </tr>
   <tr>
   	<td width=\"50%\">
   	 <span><input tabindex=\"1005\" name=\"evaluationNurse\" " . getData ("evaluationNurse", "checkbox") . " type=\"checkbox\" value=\"On\"> ". $adhEvaBy_0[$lang][3] . "
   	 </span>
   	</td>
   	<td width=\"50%\">
   	 <span><input tabindex=\"1006\" name=\"evaluationOther\" " . getData ("evaluationOther", "checkbox") . " type=\"checkbox\" value=\"On\"> ". $adhEvaBy_0[$lang][6] . "</span>&nbsp;<span><input type=\"text\"  size=\"40\" name=\"evaluationOtherText\" " . getData ("evaluationOtherText", "text") . ">
   	 </span>
   	</td>
   </tr>
  </table>
";

echo "
  <table class=\"header\">
   <tr>
    <td class=\"under_header\">
    </td>
   </tr>
   <tr>
    <td class=\"s_header\">" . $adherenceCounseling_header_0[$lang][1] . "</td>
   </tr>
   <tr>
    <td>" . $missedDoses_0[$lang][0] . " &nbsp; <span ><input tabindex=\"" . ($tabIndex + 1) . "\" name=\"missedDoses[]\" " . getData ("missedDoses", "checkbox", 1) . " type=\"radio\" value=\"1\"> " . $missedDoses_0[$lang][1] . " &nbsp;&nbsp; <input tabindex=\"" . ($tabIndex + 2) . "\" name=\"missedDoses[]\" " . getData ("missedDoses", "checkbox", 2) . " type=\"radio\" value=\"2\"> " . $missedDoses_0[$lang][2] . " &nbsp;&nbsp; <input tabindex=\"" . ($tabIndex + 3) . "\" name=\"missedDoses[]\" " . getData ("missedDoses", "checkbox", 4) . " type=\"radio\" value=\"4\"> " . $missedDoses_0[$lang][3] . " &nbsp;&nbsp; <input tabindex=\"" . ($tabIndex + 4) . "\" name=\"missedDoses[]\" " . getData ("missedDoses", "checkbox", 8) . " type=\"radio\" value=\"8\"> " . $missedDoses_0[$lang][4] . " &nbsp;&nbsp; <input tabindex=\"" . ($tabIndex + 5) . "\" name=\"missedDoses[]\" " . getData ("missedDoses", "checkbox", 16) . " type=\"radio\" value=\"16\"> " . $missedDoses[$lang][5] . "</span></td>
   </tr>
   <tr>
    <td>" . $doseProp[$lang][0] . "</td>
   </tr>
   <tr>
   	<td>
   		<IMG SRC=\"./images/arrow.jpg\" ALT=\"pecentage\" ALIGN=LEFT width=\"100%\">
   	</td>
   </tr>
   <tr>
    <td>&nbsp;&nbsp;<table><tr><td width=\"10%\"><input tabindex=\"" . ($tabIndex + 6) . "\" name=\"doseProp[]\" " . getData ("doseProp", "checkbox", 1) . " type=\"radio\" value=\"1\">" . $doseProp[$lang][1] . "</td><td width=\"10%\"><input tabindex=\"" . ($tabIndex + 7) . "\" name=\"doseProp[]\" " . getData ("doseProp", "checkbox", 2) . " type=\"radio\" value=\"2\">" . $doseProp[$lang][2] . " </td><td width=\"10%\"><input tabindex=\"" . ($tabIndex + 8) . "\" name=\"doseProp[]\" " . getData ("doseProp", "checkbox", 4) . " type=\"radio\" value=\"4\">" . $doseProp[$lang][3] . " </td><td width=\"10%\"><input tabindex=\"" . ($tabIndex + 9) . "\" name=\"doseProp[]\" " . getData ("doseProp", "checkbox", 8) . " type=\"radio\" value=\"8\">" . $doseProp[$lang][4] . " </td><td width=\"10%\"><input tabindex=\"" . ($tabIndex + 10) . "\" name=\"doseProp[]\" " . getData ("doseProp", "checkbox", 16) . " type=\"radio\" value=\"16\">" . $doseProp[$lang][5] . " </td><td width=\"10%\"><input tabindex=\"" . ($tabIndex + 11) . "\" name=\"doseProp[]\" " . getData ("doseProp", "checkbox", 32) . " type=\"radio\" value=\"32\">" . $doseProp[$lang][6] . " </td><td width=\"10%\"><input tabindex=\"" . ($tabIndex + 12) . "\" name=\"doseProp[]\" " . getData ("doseProp", "checkbox", 64) . " type=\"radio\" value=\"64\"> " . $doseProp[$lang][7] . " </td><td width=\"10%\"><input tabindex=\"" . ($tabIndex + 13) . "\" name=\"doseProp[]\" " . getData ("doseProp", "checkbox", 128) . " type=\"radio\" value=\"128\"> " . $doseProp[$lang][8] . " </td><td width=\"10%\"><input tabindex=\"" . ($tabIndex + 14) . "\" name=\"doseProp[]\" " . getData ("doseProp", "checkbox", 256) . " type=\"radio\" value=\"256\"> " . $doseProp[$lang][9] . " </td><td width=\"10%\"><input tabindex=\"" . ($tabIndex + 15) . "\" name=\"doseProp[]\" " . getData ("doseProp", "checkbox", 512) . " type=\"radio\" value=\"512\"> " . $doseProp[$lang][10] . " </td><td width=\"10%\"><input tabindex=\"" . ($tabIndex + 16) . "\" name=\"doseProp[]\" " . getData ("doseProp", "checkbox", 1024) . " type=\"radio\" value=\"1024\"> " . $doseProp[$lang][11] . "</td></tr></table></td>
   </tr>
   <tr>
   	 <td width=\"100%\"><i>" . $doseProp[$lang][12]. "</i></td>
   </tr>

  </table>
  <!--the table of reasons for missing ANY dose of medication-->
  <br/>
  <br/>
<table class=\"header\">
   <tr>
       <td class=\"under_header\">
   </td>

   <tr>
	<td colspan=\"3\">" . $adherenceCounseling_subhead_1[$lang][0] . $adherenceCounseling_subhead_1[$lang][1] . "</td>
   </tr>
   <tr>
    <td width=\"50%\"><input tabindex=\"2001\" name=\"reasonNotAvail\" " . getData ("reasonNotAvail", "checkbox") . " type=\"checkbox\" value=\"On\">" . $reasonNotAvail[$lang][1] . "</td>
    <td colspan=\"2\" width=\"50%\"><input tabindex=\"2010\" name=\"reasonNotComf\" " . getData ("reasonNotComf", "checkbox") . " type=\"checkbox\" value=\"On\">" . $reasonNotComf_1[$lang][0] . "</td>
   </tr>
   <tr>
    <td width=\"50%\"><input tabindex=\"2002\" name=\"reasonForgot\" " . getData ("reasonForgot", "checkbox") . " type=\"checkbox\" value=\"On\">" . $reasonForgot[$lang][1] . "</td>
    <td colspan=\"2\" width=\"50%\"><input tabindex=\"2011\" name=\"reasonNoSwallow\" " . getData ("reasonNoSwallow", "checkbox") . " type=\"checkbox\" value=\"On\">" . $reasonNoSwallow_1[$lang][1] . "</td>
   </tr>
   <tr>
    <td width=\"50%\"><input tabindex=\"2003\" name=\"reasonSideEff\" " . getData ("reasonSideEff", "checkbox") . " type=\"checkbox\" value=\"On\">" .  $reasonSideEff_1[$lang][0] . "</td>
	<td colspan=\"2\" width=\"50%\"><input tabindex=\"2012\" name=\"reasonTravel\" " . getData ("reasonTravel", "checkbox") . " type=\"checkbox\" value=\"On\">" . $reasonTravel_1[$lang][0] . "</td>
   </tr>
   <tr>
    <td width=\"50%\"><input tabindex=\"2004\" name=\"reasonTooSick\" " . getData ("reasonTooSick", "checkbox") . " type=\"checkbox\" value=\"On\">" . $reasonTooSick_1[$lang][1] . "</td>

    <td colspan=\"2\" width=\"50%\"><input tabindex=\"2013\" name=\"reasonNoFood\" " . getData ("reasonNoFood", "checkbox") . " type=\"checkbox\" value=\"On\">" . $reasonNoFood[$lang][1] . "</td>
   </tr>
   <tr>
    <td width=\"50%\"><input tabindex=\"2005\" name=\"reasonPrison\" " . getData ("reasonPrison", "checkbox") . " type=\"checkbox\" value=\"On\">" . $reasonPrison_1[$lang][1] . "</td>
    <td width=\"15%\" valign=\"top\" align=\"left\" rowspan=\"5\"><span><input tabindex=\"2014\" name=\"reasonOther\" " . getData ("reasonOther", "checkbox") . " type=\"checkbox\" value=\"On\">" . $reasonOther[$lang][1] . " </span>
    </td>
    <td width=\"35%\" valign=\"top\" align=\"left\" rowspan=\"5\"><span><textarea tabindex=\"2015\" name=\"reasonOtherText\" " . getData ("reasonOtherText", "text") . "  rows=\"5\" cols=\"46\">" . getData ("reasonOtherText", "textarea") .  "</textarea></span></td>
   </tr>
   <tr>
    <td colspan=\"3\" width=\"100%\"><input tabindex=\"2006\" name=\"reasonFinished\" " . getData ("reasonFinished", "checkbox") . " type=\"checkbox\" value=\"On\">" . $reasonFinished[$lang][1] . "</td>
    </tr>
   <tr>
    <td colspan=\"3\" width=\"100%\"><input tabindex=\"2007\" name=\"reasonFeelWell\" " . getData ("reasonFeelWell", "checkbox") . " type=\"checkbox\" value=\"On\">" . $reasonFeelWell[$lang][1] . "</td>
   </tr>
   <tr>
    <td colspan=\"3\" width=\"100%\"><input tabindex=\"2008\" name=\"reasonLost\" " . getData ("reasonLost", "checkbox") . " type=\"checkbox\" value=\"On\">" . $reasonLost[$lang][1] . "</td>
   </tr>
   <tr>
    <td colspan=\"3\" width=\"100%\"><input tabindex=\"2009\" name=\"reasonDidNotWant\" " . getData ("reasonDidNotWant", "checkbox") . " type=\"checkbox\" value=\"On\">" . $reasonDidNotWant_1[$lang][1] . "</td>
   </tr>
  </table>
";

echo "

  <table class=\"header\">
   <tr>
    <td class=\"s_header\" colspan=\"5\" width=\"100%\">"  . $adherence_subhead2_0[$lang][1] . "</td>
   </tr>
   <tr>
    <td class=\"under_header\">
   </td>
         <tr>
          <td>&nbsp;</td>
          <td width=\"7%\" align=\"center\"><b>" . $adherenceCounseling_subhead3[$lang][0] . "</b></td>
          <td width=\"7%\" align=\"center\"><b>" . $adherenceCounseling_subhead3[$lang][1] . "</b></td>
          <td width=\"7%\" align=\"center\"><b>" . $adherenceCounseling_subhead3[$lang][2] . "</b></td>
          <td width=\"50%\">&nbsp;</td>
         </tr>
         <tr>
          <td>" . $sideNausea[$lang][0] . "</td>
          <td align=\"center\"><input tabindex=\"6101\" name=\"sideNausea[]\" " . getData ("sideNausea", "checkbox", 1) . " type=\"radio\" value=\"1\"></td>
          <td align=\"center\"><input tabindex=\"6102\" name=\"sideNausea[]\" " . getData ("sideNausea", "checkbox", 2) . " type=\"radio\" value=\"2\"></td>
          <td align=\"center\"><input tabindex=\"6103\" name=\"sideNausea[]\" " . getData ("sideNausea", "checkbox", 4) . " type=\"radio\" value=\"4\"></td>
          <td width=\"50%\"> " . $reasonOther[$lang][1] . "</td>
         </tr>
         <tr>
          <td>" . $sideDiarrhea[$lang][0] . "</td>
          <td align=\"center\"><input tabindex=\"6104\" name=\"sideDiarrhea[]\" " . getData ("sideDiarrhea", "checkbox", 1) . " type=\"radio\" value=\"1\"></td>
          <td align=\"center\"><input tabindex=\"6105\" name=\"sideDiarrhea[]\" " . getData ("sideDiarrhea", "checkbox", 2) . " type=\"radio\" value=\"2\"></td>
          <td align=\"center\"><input tabindex=\"6106\" name=\"sideDiarrhea[]\" " . getData ("sideDiarrhea", "checkbox", 4) . " type=\"radio\" value=\"4\"></td>
		  <td  rowspan=\"6\" width=\"50%\"><span><textarea tabindex=\"6115\" name=\"sideOtherText\" " . getData ("sideOtherText", "text") . "  rows=\"6\" cols=\"70\">" . getData ("sideOtherText", "textarea") . "</textarea></span></td>
         </tr>
         <tr>
          <td>" . $sideRash[$lang][0] . "</td>
          <td align=\"center\"><input tabindex=\"6107\" name=\"sideRash[]\" " . getData ("sideRash", "checkbox", 1) . " type=\"radio\" value=\"1\"></td>
          <td align=\"center\"><input tabindex=\"6108\" name=\"sideRash[]\" " . getData ("sideRash", "checkbox", 2) . " type=\"radio\" value=\"2\"></td>
          <td align=\"center\"><input tabindex=\"6109\" name=\"sideRash[]\" " . getData ("sideRash", "checkbox", 4) . " type=\"radio\" value=\"4\"></td>
         </tr>
         <tr>
          <td>" . $sideHeadache[$lang][0] . "</td>
          <td align=\"center\"><input tabindex=\"6110\" name=\"sideHeadache[]\" " . getData ("sideHeadache", "checkbox", 1) . " type=\"radio\" value=\"1\"></td>
          <td align=\"center\"><input tabindex=\"6111\" name=\"sideHeadache[]\" " . getData ("sideHeadache", "checkbox", 2) . " type=\"radio\" value=\"2\"></td>
          <td align=\"center\"><input tabindex=\"6112\" name=\"sideHeadache[]\" " . getData ("sideHeadache", "checkbox", 4) . " type=\"radio\" value=\"4\"></td>
         </tr>
         <tr>
          <td>" . $sideAbPain[$lang][0] . "</td>
          <td align=\"center\"><input tabindex=\"6113\" name=\"sideAbPain[]\" " . getData ("sideAbPain", "checkbox", 1) . " type=\"radio\" value=\"1\"></td>
          <td align=\"center\"><input tabindex=\"6114\" name=\"sideAbPain[]\" " . getData ("sideAbPain", "checkbox", 2) . " type=\"radio\" value=\"2\"></td>
          <td align=\"center\"><input tabindex=\"6115\" name=\"sideAbPain[]\" " . getData ("sideAbPain", "checkbox", 4) . " type=\"radio\" value=\"4\"></td>
         </tr>
         <tr>
          <td>" . $jaundice[$lang][0] . "</td>
          <td align=\"center\"><input tabindex=\"6116\" name=\"jaundice[]\" " . getData ("jaundice", "checkbox", 1) . " type=\"radio\" value=\"1\"></td>
          <td align=\"center\"><input tabindex=\"6117\" name=\"jaundice[]\" " . getData ("jaundice", "checkbox", 2) . " type=\"radio\" value=\"2\"></td>
          <td align=\"center\"><input tabindex=\"6118\" name=\"jaundice[]\" " . getData ("jaundice", "checkbox", 4) . " type=\"radio\" value=\"4\"></td>
         </tr>
         <tr>
          <td>" . $severeAllergicReactions[$lang][0] . "</td>
          <td align=\"center\"><input tabindex=\"6119\" name=\"severeAllergicReactions[]\" " . getData ("severeAllergicReactions", "checkbox", 1) . " type=\"radio\" value=\"1\"></td>
          <td align=\"center\"><input tabindex=\"6120\" name=\"severeAllergicReactions[]\" " . getData ("severeAllergicReactions", "checkbox", 2) . " type=\"radio\" value=\"2\"></td>
          <td align=\"center\"><input tabindex=\"6121\" name=\"severeAllergicReactions[]\" " . getData ("severeAllergicReactions", "checkbox", 4) . " type=\"radio\" value=\"4\"></td>
         </tr>
		 <tr>
          <td>" . $behavProblem[$lang][0] . "</td>
          <td align=\"center\"><input tabindex=\"6122\" name=\"behaviorProblem[]\" " . getData ("behaviorProblem", "checkbox", 1) . " type=\"radio\" value=\"1\"></td>
          <td align=\"center\"><input tabindex=\"6123\" name=\"behaviorProblem[]\" " . getData ("behaviorProblem", "checkbox", 2) . " type=\"radio\" value=\"2\"></td>
          <td align=\"center\"><input tabindex=\"6124\" name=\"behaviorProblem[]\" " . getData ("behaviorProblem", "checkbox", 4) . " type=\"radio\" value=\"4\"></td>
          <td rowspan=\"2\" width=\"50%\" valign=\"top\"><i>" . $adherenceCounseling_subhead4[$lang][1] . "</i></td>
         </tr>
		 <tr>
          <td>" . $neuMusDisorder[$lang][0] . "</td>
          <td align=\"center\"><input tabindex=\"6125\" name=\"neuroMuscularDisorder[]\" " . getData ("neuroMuscularDisorder", "checkbox", 1) . " type=\"radio\" value=\"1\"></td>
          <td align=\"center\"><input tabindex=\"6126\" name=\"neuroMuscularDisorder[]\" " . getData ("neuroMuscularDisorder", "checkbox", 2) . " type=\"radio\" value=\"2\"></td>
          <td align=\"center\"><input tabindex=\"6127\" name=\"neuroMuscularDisorder[]\" " . getData ("neuroMuscularDisorder", "checkbox", 4) . " type=\"radio\" value=\"4\"></td>
         </tr>
  </table>
";
$remark_textarea = "";


echo "
  <table class=\"header\">
  <tr>
 	<td class=\"s_header\">" . $adherenceRemark_0[$lang][0] . "
   	</td>
  </tr>
  <tr>
   	<td>" . $adherenceRemark_1[$lang][1] . "
     	</td>
  </tr>
  <tr>
	<td>
	 <textarea tabindex=\"7001\" name=\"adherenceRemark\" cols=\"149\" rows=\"5\">" . getData ("adherenceRemark", "textarea") . "</textarea>
    </td>
   </tr>
  </table>
  <input type=\"hidden\" name=\"encounterType\" value=\"20\"></div>
 ";

$tabIndex = 5000;
$formName = "pedadherence";
echo "<script language=\"JavaScript\" type=\"text/javascript\" src=\"adherence/pedadherence.js\"></script>";
?>
