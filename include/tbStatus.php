<?php

echo "
<!-- ******************************************************************** -->
<!-- **************************** TB Status ***************************** -->
<!--  (tab indices " . ($tabIndex + 1) . " - " . ($tabIndex + 100) . ") * -->
<!-- ******************************************************************** -->
";

echo "
   <tr>
    <td colspan=\"3\">
     <table class=\"b_header_nb\">
      <tr>
       <td class=\"s_header\" colspan=\"2\">" . $tbstatus_header[$lang][1] . "</td>
      </tr>
      <tr>
       <td><table><tr><td id=\"tbStatusTitle\"></td><td><i>" . $tbstatus_header[$lang][2] . "</i></td></tr></table></td>
       <td>
        <table><tr><td id=\"completeTreatTitle\"></td><td>
         <input tabindex=\"" . ($tabIndex + 6) . "\" id=\"completeTreat[]\" name=\"tbStatus[]\" " .
           getData ("completeTreat", "radio","1") .
           " type=\"radio\"><input type=\"hidden\" id =\"completeTreat\"  name =\"completeTreat\" " .  getData ("completeTreat", "text") ." />" .
           $completeTreat[$lang][1] . "</td></tr></table>
       </td>
      </tr>
      <tr>
       <td>
         <input tabindex=\"" . ($tabIndex + 1) . "\" id=\"asymptomaticTb[]\" name=\"tbStatus[]\" " .
           getData ("asymptomaticTb", "radio","1") .
           " type=\"radio\">" .
           $asymptomaticTb[$lang][1] . "<input type=\"hidden\" id =\"asymptomaticTb\"  name =\"asymptomaticTb\" " .  getData ("asymptomaticTb", "text") ." />
       </td>
";
echo "
       <td><table><tr><td id=\"completeTreatDtTitle\">" . $completeTreatMm[$lang][0] . "</td><td><input tabindex=\"" . ($tabIndex + 7) . "\" id=\"completeTreatDt\" name=\"completeTreatDt\"  value=\"" . getData ("completeTreatDd", "textarea") . "/". getData ("completeTreatMm", "textarea") ."/". getData ("completeTreatYy", "textarea") . "\"  type=\"text\" size=\"8\" maxlength=\"8\"><input id=\"completeTreatDd\" name=\"completeTreatDd\"  type=\"hidden\"><input tabindex=\"" . ($tabIndex + 8) . "\" id=\"completeTreatMm\" name=\"completeTreatMm\" type=\"hidden\"><input tabindex=\"" . ($tabIndex + 9) . "\" id=\"completeTreatYy\" name=\"completeTreatYy\"  type=\"hidden\"></td><td><i>" . $completeTreatYy[$lang][2] . "</i></td></tr></table></td>
";
echo "
      </tr>
      <tr>
       <td>
         <input tabindex=\"" . ($tabIndex + 2) . "\" id=\"suspectedTb[]\" name=\"tbStatus[]\" " .
           getData ("suspectedTb", "radio","1") .
           " type=\"radio\">" .
           $suspectedTb[$lang][1] . "<input type=\"hidden\" id =\"suspectedTb\"  name =\"suspectedTb\" " .  getData ("suspectedTb", "text") ." />
       </td>
       <td>&nbsp;</td>
      </tr>
      <tr>
       <td><table><tr><td id=\"currentTreatTitle\">&nbsp;</td><td>
         <input tabindex=\"" . ($tabIndex + 3) . "\" id=\"currentTreat[]\" name=\"tbStatus[]\" " .
           getData ("currentTreat", "radio","1") .
           " type=\"radio\">" .
           $currentTreat[$lang][1] . "<input type=\"hidden\" id =\"currentTreat\"  name =\"currentTreat\" " .  getData ("currentTreat", "text") ." />
       </td></tr></table></td>
       <td><input tabindex=\"" . ($tabIndex + 10) . "\" id=\"currentProp[]\" name=\"tbStatus[]\" " .
         getData ("currentProp", "radio","1") .
         " type=\"radio\">" .
         $currentProp[$lang][1] . "<input type=\"hidden\" id =\"currentProp\"  name =\"currentProp\" " .  getData ("currentProp", "text") ." />
       </td>
      </tr>
      <tr>
       <td id=\"currentTreatNoTitle\">" . $currentTreatNo[$lang][1] . "
         <input tabindex=\"" . ($tabIndex + 4) . "\" id=\"currentTreatNo\" name=\"currentTreatNo\" " . getData ("currentTreatNo", "text") . " type=\"text\" size=\"30\" maxlength=\"64\"></td>
       <td><table><tr><td id=\"completeTreatFacTitle\">&nbsp;</td><td>" . $completeTreatFac[$lang][1] . "
         <input tabindex=\"" . ($tabIndex + 11) . "\" id=\"completeTreatFac\" name=\"completeTreatFac\" " . getData ("completeTreatFac", "text") . " type=\"text\" size=\"30\" maxlength=\"255\"></td></tr></table></td>
      </tr>
      <tr>
       <td><table><tr><td id=\"currentTreatFacTitle\">&nbsp;</td><td>" . $currentTreatFac[$lang][1] . "
         <input tabindex=\"" . ($tabIndex + 5) . "\" id=\"currentTreatFac\" name=\"currentTreatFac\" " . getData ("currentTreatFac", "text") . " type=\"text\" size=\"30\" maxlength=\"255\"></td></tr></table></td>
      </tr>
     </table>
    </td>
   </tr>
";
?>
