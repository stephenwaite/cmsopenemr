<?php /* Smarty version 2.3.1, created on 2014-04-09 08:41:21
         compiled from default/views/week/ajax_template.html */ ?>
<?php $this->_load_plugins(array(
array('function', 'assign', 'default/views/week/ajax_template.html', 356, false),
array('modifier', 'date_format', 'default/views/week/ajax_template.html', 356, false),
array('modifier', 'string_format', 'default/views/week/ajax_template.html', 357, false),)); ?>








<?php $this->_config_load("default.conf", null, 'local'); ?>

<?php $this->_config_load("lang.$USER_LANG", null, 'local'); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include("$TPL_NAME/views/header.html", array());
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>


<?php 
/* if you change these be sure to change their matching values in
 * the CSS for the calendar, found in interface/themes/ajax_calendar.css
 */
$timeslotHeightVal=20;
$timeslotHeightUnit="px";
 ?>

<script language='JavaScript'>

 var mypcc = '<?php  echo $GLOBALS['phone_country_code']  ?>';

 // This is called from the event editor popup.
 function refreshme() {
  top.restoreSession();
  document.forms[0].submit();
 }

 function newEvt(startampm, starttimeh, starttimem, eventdate, providerid, catid) {
  dlgopen('add_edit_event.php?startampm=' + startampm +
   '&starttimeh=' + starttimeh + '&starttimem=' + starttimem +
   '&date=' + eventdate + '&userid=' + providerid + '&catid=' + catid<?php 
    if(isset($_SESSION[pid]))
        {
            if($_SESSION[pid]>0)
                {
                    echo "+'&patientid=$_SESSION[pid]'";
                }
        }
     ?>
   ,'_blank', 575, 375);
 }

 function oldEvt(eventdate, eventid, pccattype) {
  dlgopen('add_edit_event.php?date='+eventdate+'&eid=' + eventid+'&prov=' + pccattype, '_blank', 575, 375);
 }

 function goPid(pid) {
  top.restoreSession();
<?php 
 if ($GLOBALS['concurrent_layout'])
 {

		 echo "  top.RTop.location = '../../patient_file/summary/demographics.php' " .
		   "+ '?set_pid=' + pid;\n";
	
 } else {
  echo "  top.location = '../../patient_file/patient_file.php' " .
   "+ '?set_pid=' + pid + '&pid=' + pid;\n";
 }
 ?>
 }

 function GoToToday(theForm){
  var todays_date = new Date();
  var theMonth = todays_date.getMonth() + 1;
  theMonth = theMonth < 10 ? "0" + theMonth : theMonth;
  theForm.jumpdate.value = todays_date.getFullYear() + "-" + theMonth + "-" + todays_date.getDate();
  top.restoreSession();
  theForm.submit();
 }

</script>

<?php 

 // this is my proposed setting in the globals config file so we don't
 // need to mess with altering the pn database AND the config file
 //pnModSetVar(__POSTCALENDAR__, 'pcFirstDayOfWeek', $GLOBALS['schedule_dow_start']);
 
 // build a day-of-week (DOW) list so we may properly build the calendars later in this code
 $DOWlist = array();
 $tmpDOW = pnModGetVar(__POSTCALENDAR__, 'pcFirstDayOfWeek');
 // bound check and auto-correction
 if ($tmpDOW <0 || $tmpDOW >6) { 
    pnModSetVar(__POSTCALENDAR__, 'pcFirstDayOfWeek', '0');
    $tmpDOW = 0;
 }
 while (count($DOWlist) < 7) {
    array_push($DOWlist, $tmpDOW);
    $tmpDOW++;
    if ($tmpDOW > 6) $tmpDOW = 0;
 }

 // A_CATEGORY is an ordered array of associative-array categories.
 // Keys of interest are: id, name, color, desc, event_duration.
 //
 // echo "<!-- A_CATEGORY = "; print_r($this->_tpl_vars['A_CATEGORY']); echo " -->\n"; // debugging
 // echo "<!-- A_EVENTS = "; print_r($this->_tpl_vars['A_EVENTS']); echo " -->\n"; // debugging

 $A_CATEGORY  =& $this->_tpl_vars['A_CATEGORY'];

 // [-if $PRINT_VIEW != 1-]
 // [-**-]
 // [-include file="$TPL_NAME/views/global/navigation.html"-]
 // [-/if-]

 $A_EVENTS  =& $this->_tpl_vars['A_EVENTS'];
 $providers =& $this->_tpl_vars['providers'];
 $times     =& $this->_tpl_vars['times'];
 $interval  =  $this->_tpl_vars['interval'];
 $viewtype  =  $this->_tpl_vars['VIEW_TYPE'];
 $PREV_WEEK_URL = $this->_tpl_vars['PREV_WEEK_URL'];
 $NEXT_WEEK_URL = $this->_tpl_vars['NEXT_WEEK_URL'];
 $PREV_DAY_URL  = $this->_tpl_vars['PREV_DAY_URL'];
 $NEXT_DAY_URL  = $this->_tpl_vars['NEXT_DAY_URL'];

 $Date =  postcalendar_getDate();
 if (!isset($y)) $y = substr($Date, 0, 4);
 if (!isset($m)) $m = substr($Date, 4, 2);
 if (!isset($d)) $d = substr($Date, 6, 2);

 // echo "<!-- There are " . count($A_EVENTS) . " A_EVENTS days -->\n";

 //==================================
 //FACILITY FILTERING (CHEMED)
 $facilities = getUserFacilities($_SESSION['authId']); // from users_facility
 if ( $_SESSION['pc_facility'] ) {
    $provinfo = getProviderInfo('%', true, $_SESSION['pc_facility']);
 } else {
    $provinfo = getProviderInfo();
 }
 //EOS FACILITY FILTERING (CHEMED)
 //==================================

 ?>
<div id="topToolbarRight">  <!-- this wraps some of the top toolbar items -->
<div id="functions">
<!-- stuff form element here to avoid the margin padding it introduces into the page in some browsers -->
<form name='theform' id='theform' action='index.php?module=PostCalendar&func=view&tplview=default&pc_category=&pc_topic=' method='post' onsubmit='return top.restoreSession()'>
<input type="hidden" name="jumpdate" id="jumpdate" value="">
<input type="hidden" name="viewtype" id="viewtype" value="<?php echo $viewtype; ?>">
<?php 
echo "   <a href='#' value='" .xl ("Add"). "' onclick='newEvt(1, 9, 00, $Date, 0, 0)'  class='css_button'/><span>".xl("Add")."</span></a>\n";
echo "   <a href='#' value='" . xl ("Search") .
    "' onclick='top.restoreSession();location=\"index.php?module=PostCalendar&func=search\"'  class='css_button'/><span>".xl("Search")."</span></a>\n";
 ?>
</div>


<div id="dateNAV"">
<a href='#' name='bnsubmit' value='<?php echo xl ("Today") ?>' onClick='GoToToday(theform);'  class='css_button'/><span><?php  echo xl("Today") ?></span></a>
<a href='<?php echo $PREV_WEEK_URL ?>' onclick='top.restoreSession()'>
<img id="prevweek" src="<?php echo $this->_tpl_vars['TPL_IMAGE_PATH']; ?>
/leftbtn.gif" border="0" title="<?php echo xl("Previous Week") ?>" alt="<?php echo xl ("Previous Week") ?>" style="padding-top:5px"/></a>
<a href='<?php echo $NEXT_WEEK_URL ?>' onclick='top.restoreSession()'>
<img id="nextweek" src="<?php echo $this->_tpl_vars['TPL_IMAGE_PATH']; ?>
/rightbtn.gif" border="0" title="<?php echo xl("Next Week") ?>" alt="<?php echo xl ("Next Week") ?>" /></a>
&nbsp;
<?php 
$atmp = array_keys($A_EVENTS);
echo date('d M Y', strtotime($atmp[0]));
echo " - ";
echo date('d M Y', strtotime($atmp[count($atmp)-1]));
 ?>
</div>

<div id="viewPicker">
<?php 
echo "   <a href='#' type='button' id='printview' title='" .xl ("Print View"). "' class='css_button'/><span>".xl("Print")."</span></a>\n";
echo "   <a href='#' type='button' value='" .xl ("Refresh"). "' onclick='javascript:refreshme()' class='css_button'/><span>".xl("Refresh")."</span></a>\n";
echo "   <a href='#' type='button' id='dayview' title='" .htmlspecialchars( xl('Day View'), ENT_QUOTES). "' class='css_button'/><span>".htmlspecialchars( xl('Day'), ENT_QUOTES) ."</span></a>\n";
echo "   <a href='#' type='button' id='weekview' title='" .htmlspecialchars( xl('Week View'), ENT_QUOTES). "' class='css_button'/><span>".htmlspecialchars( xl('Week'), ENT_QUOTES)."</span></a>\n";
echo "   <a href='#' type='button' id='monthview' title='" .htmlspecialchars( xl('Month View'), ENT_QUOTES). "' class='css_button'/><span>".htmlspecialchars( xl('Month'), ENT_QUOTES)."</span></a>\n";
 ?>
</div>
</div> <!-- end topToolbarRight -->
<div id="bottom">
<div id="bottomLeft">
<div id="datePicker">
<?php 
$atmp = array_keys($A_EVENTS);
$caldate = strtotime($atmp[0]);
$cMonth = date("m", $caldate);
$cYear = date("Y", $caldate);
$cDay = date("d", $caldate);

    include_once($GLOBALS['webserver_root'].'/interface/main/calendar/modules/PostCalendar/pntemplates/default/views/monthSelector.php');
 ?>
    
<table border="0" cellpadding="0" cellspacing="0">
<tr>
<?php 

// compute the previous month date
// stay on the same day if possible
$pDay = $cDay;
$pMonth = $cMonth - 1;
$pYear = $cYear;
if ($pMonth < 1) { $pMonth = 12; $pYear = $cYear - 1; }
while (! checkdate($pMonth, $pDay, $pYear)) { $pDay = $pDay - 1; }
$prevMonth = sprintf("%d%02d%02d",$pYear,$pMonth,$pDay);

// compute the next month
// stay on the same day if possible
$nDay = $cDay;
$nMonth = $cMonth + 1;
$nYear = $cYear;
if ($nMonth > 12) { $nMonth = 1; $nYear = $cYear + 1; }
while (! checkdate($nMonth, $nDay, $nYear)) { $nDay = $nDay - 1; }
$nextMonth = sprintf("%d%02d%02d",$nYear,$nMonth,$nDay);
 ?>
<td class="tdDOW-small tdDatePicker tdNav" id="<?php echo $prevMonth ?>" title="<?php echo xl(date("F", strtotime($prevMonth))); ?>">&lt;</td>
<td colspan="5" class="tdMonthName-small">
<?php 
echo xl(date('F', $caldate));
 ?>
</td>
<td class="tdDOW-small tdDatePicker tdNav" id="<?php echo $nextMonth ?>" title="<?php echo xl(date("F", strtotime($nextMonth))); ?>">&gt;</td>
<tr>
<?php 
foreach ($DOWlist as $dow) {
    echo "<td class='tdDOW-small'>".$this->_tpl_vars['A_SHORT_DAY_NAMES'][$dow]."</td>";
}
 ?>
</tr>
<?php 
$atmp = array_keys($A_EVENTS);
$caldate = strtotime($atmp[0]);
$caldateEnd = strtotime($atmp[6]);

// to make a complete week row we need to compute the real
// start and end dates for the view
list ($year, $month, $day) = explode(" ", date('Y m d', $caldate));
$startdate = strtotime($year.$month."01");
$enddate = strtotime($year.$month.date("t", $startdate)." 23:59");
while (date('w', $startdate) != $DOWlist[0]) { $startdate -= 60*60*24; }
while (date('w', $enddate) != $DOWlist[6]) { $enddate += 60*60*24; }

$currdate = $startdate;
while ($currdate <= $enddate) {
    if (date('w', $currdate) == $DOWlist[0]) {
        // start of week row
        $tr = "<tr class='trDateRow'>";
        echo $tr;
    }

    // set the TD class
    $tdClass = "tdMonthDay-small";
    if (date('m', $currdate) != $month) {
        $tdClass = "tdOtherMonthDay-small";
    }
    if ((date('w', $currdate) == 0) || (date('w', $currdate) == 6)) {
        $tdClass = "tdWeekend-small";
    }

    if ((date('Ymd',$currdate) >= date('Ymd', $caldate)) &&
        (date('Ymd',$currdate) <= date('Ymd', $caldateEnd)))
    {
        // add a class that highlights the 'current date'
        $tdClass .= " currentWeek";
    }
    
    if (date('Ymd',$currdate) == $Date) {
        // $Date is defined near the top of this file
        // and is equal to whatever date the user has clicked
        $tdClass .= " currentDate";
    }

    // add a class so that jQuery can grab these days for the 'click' event
    $tdClass .= " tdDatePicker";

    // output the TD
    $td = "<td ";
    $td .= "class=\"".$tdClass."\" ";
    //$td .= "id=\"".date("Ymd", $currdate)."\" ";
    $td .= "id=\"".date("Ymd", $currdate)."\" ";
    $td .= "title=\"" . xl('Go to week of') . " ".date('M d, Y', $currdate)."\" ";
    $td .= "> ".date('d', $currdate)."</td>\n";
    echo $td;
   
    // end of week row
    if (date('w', $currdate) == $DOWlist[6]) echo "</tr>\n";

    // time correction = plus 1000 seconds, for some unknown reason
    $currdate += (60*60*24)+1000;
}
 ?>
</table>
</div>

<div id="bigCalHeader">
</div>

<div id="providerPicker">
<?php  xl('Providers','e');  ?>
<div>
<?php 
// ==============================
// FACILITY FILTERING (lemonsoftware)
// $facilities = getFacilities();
if ($_SESSION['authorizeduser'] == 1) {
  $facilities = getFacilities();
} else {
  $facilities = getUserFacilities($_SESSION['authId']); // from users_facility
 	if (count($facilities) == 1)
    $_SESSION['pc_facility'] = key($facilities);
}
if (count($facilities) > 1) {
    echo "   <select name='pc_facility' id='pc_facility'  class='view1' >\n";
    if ( !$_SESSION['pc_facility'] ) $selected = "selected='selected'";
    // echo "    <option value='0' $selected>"  .xl('All Facilities'). "</option>\n";
    if (!$GLOBALS['restrict_user_facility']) echo "    <option value='0' $selected>" . xl('All Facilities') . "</option>\n";
    foreach ($facilities as $fa) {
        $selected = ( $_SESSION['pc_facility'] == $fa['id']) ? "selected='selected'" : "" ;
        echo "    <option style=background-color:".htmlspecialchars($fa['color'],ENT_QUOTES)." value='" .htmlspecialchars($fa['id'],ENT_QUOTES). "' $selected>"  .htmlspecialchars($fa['name'],ENT_QUOTES). "</option>\n";
    }
    echo "   </select>\n";
}
// EOS FF
// ==============================
 echo "</div>";
 echo "   <select multiple size='5' name='pc_username[]' id='pc_username' class='view2'>\n";
 echo "    <option value='__PC_ALL__'>"  .xl ("All Users"). "</option>\n";
 foreach ($provinfo as $doc) {
  $username = $doc['username'];
  echo "    <option value='$username'";
  foreach ($providers as $provider)
   if ($provider['username'] == $username) echo " selected";
  echo ">" . htmlspecialchars($doc['lname'],ENT_QUOTES) . ", " . htmlspecialchars($doc['fname'],ENT_QUOTES) . "</option>\n";
 }
 echo "   </select>\n";

 ?>
</div>
<?php 
if($_SESSION['pc_facility'] == 0){
 ?>
<div id="facilityColor">
 <table>
<?php 
foreach ($facilities as $f){
echo "   <tr><td><div class='view1' style=background-color:".$f['color'].";font-weight:bold>".htmlspecialchars($f['name'],ENT_QUOTES)."</div></td></tr>";
}
 ?>
 </table>
</div>
<?php 
}
 ?>
</form>

<?php $this->_plugins['function']['assign'][0](array('var' => "dayname",'value' => $this->_run_mod_handler('date_format', true, $this->_tpl_vars['DATE'], "%w")), $this); if($this->_extract) { extract($this->_tpl_vars); $this->_extract=false; } ?>
<?php $this->_plugins['function']['assign'][0](array('var' => "day",'value' => $this->_run_mod_handler('string_format', true, $this->_run_mod_handler('date_format', true, $this->_tpl_vars['DATE'], "%d"), "%1d")), $this); if($this->_extract) { extract($this->_tpl_vars); $this->_extract=false; } ?>
<?php $this->_plugins['function']['assign'][0](array('var' => "month",'value' => $this->_run_mod_handler('string_format', true, $this->_run_mod_handler('date_format', true, $this->_tpl_vars['DATE'], "%m"), "%1d")), $this); if($this->_extract) { extract($this->_tpl_vars); $this->_extract=false; } ?>
<?php $this->_plugins['function']['assign'][0](array('var' => "year",'value' => $this->_run_mod_handler('string_format', true, $this->_run_mod_handler('date_format', true, $this->_tpl_vars['DATE'], "%Y"), "%4d")), $this); if($this->_extract) { extract($this->_tpl_vars); $this->_extract=false; } ?>

</div> <!-- end bottomLeft -->
<div id="bigCal">
<?php 
/* used in debugging
foreach ($A_EVENTS as $date => $events) {
    echo $date." = ";
    foreach ($events as $oneE) {
        print_r($oneE);
        echo "<br><br>";
    }
    echo "<hr width=100%>";
}
*/
    

// This loops once for each provider to be displayed.
//
foreach ($providers as $provider) {
    $providerid = $provider['id'];

    // to specially handle the IN/OUT events I'm doing something new here
    // for each IN event it will have a duration lasting until the next
    // OUT event or until the end of the day
    $tmpTime = $times[0];
    $calStartMin = ($tmpTime['hour'] * 60) + $tmpTime['minute'];
    $tmpTime = $times[count($times)-1];
    $calEndMin = ($tmpTime['hour'] * 60) + $tmpTime['minute'];

    echo "<table border='0' cellpadding='1' cellspacing='0' width='100%'>\n";
    echo " <tr>\n";
    echo "  <td colspan='8' class='providerheader'>";
    echo htmlspecialchars($provider['fname'],ENT_QUOTES) . " " . htmlspecialchars($provider['lname'],ENT_QUOTES);
    echo "</td>\n";
    echo " </tr>\n";

    // output column (date) headers
    $colWidth = 100/7; // intentionally '7' and not '8'
    echo " <tr>\n";
    echo " <td>&nbsp;</td>"; // blank TD for the header above the Times column
    $defaultDate = ""; // used when creating link for a 'new' event
    $in_cat_id = 0; // used when creating link for a 'new' event
    foreach ($A_EVENTS as $date => $events) {
        $dateFmt = date("Ymd", strtotime($date));
        $gotoURL = pnModURL(__POSTCALENDAR__,'user','view',
                        array('tplview'=>$template_view,
                        'viewtype'=>'day',
                        'Date'=> $dateFmt,
                        'pc_username'=>$pc_username,
                        'pc_category'=>$category,
                        'pc_topic'=>$topic));
        if ($defaultDate == "") $defaultDate = $dateFmt;
        $currclass = "";
        if ($Date == $dateFmt) { $currclass= "week_currday"; }
        echo "<td align='center' class='week_dateheader $currclass' style='width:".$colWidth."%;' >";
        echo "<a href='$gotoURL'>";
        echo xl(date("D", strtotime($date))) . " " . date("m/d", strtotime($date));
        echo "</a></td>";
    }
    echo " </tr>\n";

    // output the TD with the times DIV
    echo "<tr>";
    echo "<td id='times'><div><table>\n";
//==================================================================================================================
    foreach ($times as $slottime) {
        $startampm = ($slottime['mer']) == "pm" ? 2 : 1;
        $starttimeh = $slottime['hour'];
        $disptimeh = ($starttimeh > 12) ? ($starttimeh - 12) : $starttimeh;
        $starttimem = $slottime['minute'];
        $slotendmins = $starttimeh * 60 + $starttimem + $interval;

        echo "<tr><td class='timeslot'>";
//         echo "<a href='javascript:newEvt($startampm,$starttimeh,$starttimem,$Date,$providerid,$in_cat_id)' title='New Appointment' alt='New Appointment'>";
        echo "<a href='javascript:newEvt($startampm,$starttimeh,$starttimem,$defaultDate,$providerid,$in_cat_id)' title='" . htmlspecialchars(xl("New Appointment"),ENT_QUOTES) . "' alt='".htmlspecialchars(xl("New Appointment"),ENT_QUOTES)."'>";

        //echo $slottime['hour'] * 60 + $slottime['minute'];
        echo "$disptimeh:$starttimem</a>";
        echo "</td></tr>\n";
    }
    echo "</table></div></td>";

    // For each day...
    // output a TD with an inner containing DIV positioned 'relative'
    // within that DIV we place our event DIVs using 'absolute' positioning
    foreach ($A_EVENTS as $date => $events) {
        $eventdate = substr($date, 0, 4) . substr($date, 5, 2) . substr($date, 8, 2);

        // having a 'title' for the TD makes the date appear by the mouse pointer
        // this is nice when all you see are times on the left side and no head
        // row with the dates or day-of-week (DOW)
        $headDate=strtotime($date);
        echo "<td class='schedule' title='".date("l, d M Y",$headDate )."' date='".date("Ymd",$headDate )."' provider='$providerid'>";
        echo "<div class='calendar_day'>";

        // determine if events overlap and adjust their width and left position as needed
        // 26 Feb 2008 - This needs fine tuning or total replacement
        //             - it doesn't work as well as I'd like - JRM
        $eventPositions = array();
        foreach ($times as $slottime) {
            $starttimeh = $slottime['hour'];
            $starttimem = $slottime['minute'];

            $slotstartmins = $starttimeh * 60 + $starttimem;
            $slotendmins = $starttimeh * 60 + $starttimem + $interval;

            $events_in_timeslot = array();
            foreach ($events as $e1) {
                // ignore IN and OUT events
                if (($e1['catid'] == 2) || ($e1['catid'] == 3)) { continue; }
                // skip events without an ID (why they are in the loop, I have no idea)
                if ($e1['eid'] == "") { continue; }
                // skip events for other providers
                if ($providerid != $e1['aid']) { continue; }

                // specially handle all-day events
                if ($e1['alldayevent'] == 1) {
                    $tmpTime = $times[0];
                    if (strlen($tmpTime['hour']) < 2) { $tmpTime['hour'] = "0".$tmpTime['hour']; }
                    if (strlen($tmpTime['minute']) < 2) { $tmpTime['minute'] = "0".$tmpTime['minute']; }
                    $e1['startTime'] = $tmpTime['hour'].":".$tmpTime['minute'].":00";
                    $e1['duration'] = ($calEndMin - $calStartMin) * 60;  // measured in seconds
                }
            
                // create a numeric start and end for comparison
                $starth = substr($e1['startTime'], 0, 2);
                $startm = substr($e1['startTime'], 3, 2);
                $e1Start = ($starth * 60) + $startm;
                $e1End = $e1Start + $e1['duration']/60;

                // three ways to overlap:
                // start-in, end-in, span
                if ((($e1Start >= $slotstartmins) && ($e1Start < $slotendmins)) // start-in
                   || (($e1End > $slotstartmins) && ($e1End <= $slotendmins)) // end-in
                   || (($e1Start < $slotstartmins) && ($e1End > $slotendmins))) // span
                {
                    array_push($events_in_timeslot, $e1['eid']);
                }
            }

            $leftpos = 0;
            $width = 100 / count($events_in_timeslot);

            // loop over the events in this timeslot and adjust their width
            foreach ($events_in_timeslot as $eid) {
                // set the width if not already set or if the current width is smaller
                // than was was previously set
                if (! isset($eventPositions[$eid]->width)) { $eventPositions[$eid]->width = $width; }
                else if ($eventPositions[$eid]->width > $width) { $eventPositions[$eid]->width = $width; }
               
                // set the left position if not already set or if the current left is
                // greater than what was previously set
                if (! isset($eventPositions[$eid]->leftpos)) { $eventPositions[$eid]->leftpos = $leftpos; }
                else if ($eventPositions[$eid]->leftpos < $leftpos) { $eventPositions[$eid]->leftpos = $leftpos; }

                // increment the leftpos by the width
                $leftpos += $width;
            }
        } // end overlap detection

        // now loop over the events for the day and output their DIVs
        foreach ($events as $event) {
            // skip events for other providers
            // yeah, we've got that sort of overhead here... it ain't perfect
            if ($providerid != $event['aid']) { continue; }

            // skip events without an ID (why they are in the loop, I have no idea)
            if ($event['eid'] == "") { continue; }
                
            // specially handle all-day events
            if ($event['alldayevent'] == 1) {
                $tmpTime = $times[0];
                if (strlen($tmpTime['hour']) < 2) { $tmpTime['hour'] = "0".$tmpTime['hour']; }
                if (strlen($tmpTime['minute']) < 2) { $tmpTime['minute'] = "0".$tmpTime['minute']; }
                $event['startTime'] = $tmpTime['hour'].":".$tmpTime['minute'].":00";
                $event['duration'] = ($calEndMin - $calStartMin) * 60; // measured in seconds
            }

            // figure the start time and minutes (from midnight)
            $starth = substr($event['startTime'], 0, 2);
            $startm = substr($event['startTime'], 3, 2);
            $eStartMin = $starth * 60 + $startm;
            $dispstarth = ($starth > 12) ? ($starth - 12) : $starth;

            // determine the class for the event DIV based on the event category
            $evtClass = "event_appointment";
            switch ($event['catid']) {
                case 1:  // NO-SHOW appt
                    $evtClass = "event_noshow";
                    break;
                case 2:  // IN office
                    $evtClass = "event_in";
                    break;
                case 3:  // OUT of office
                    $evtClass = "event_out";
                    break;
                case 4:  // VACATION
                case 8:  // LUNCH
                case 11: // RESERVED
                    $evtClass = "event_reserved";
                    break;
                default: // some appointment
                    $evtClass = "event_appointment";
                    break;
            }
            
            // if this is an IN or OUT event then we have some extra special
            // processing to be done
            // the IN event creates a DIV until the OUT event
            // or, without an OUT DIV matching the IN event
            // then the IN event runs until the end of the day
            if ($event['catid'] == 2) {
                // locate a matching OUT for this specific IN
                $found = false;
                $outMins = 0;
                foreach ($events as $outevent) {
                    // skip events for other providers
                    if ($providerid != $outevent['aid']) { continue; }
                    // skip events with blank IDs
                    if ($outevent['eid'] == "") { continue; }

                    if ($outevent['eid'] == $event['eid']) { $found = true; continue; }
                    if (($found == true) && ($outevent['catid'] == 3)) {
                        // calculate the duration from this event to the outevent
                        $outH = substr($outevent['startTime'], 0, 2);
                        $outM = substr($outevent['startTime'], 3, 2);
                        $outMins = ($outH * 60) + $outM;
                        $event['duration'] = ($outMins - $eStartMin) * 60; // duration is in seconds
                        $found = 2;
                        break;
                    }
                }
                if ($outMins == 0) {
                    // no OUT was found so this event's duration goes
                    // until the end of the day
                    $event['duration'] = ($calEndMin - $eStartMin) * 60; // duration is in seconds
                }
            }

            // calculate the TOP value for the event DIV
            // diff between event start and schedule start
            $eMinDiff = $eStartMin - $calStartMin;
            // diff divided by the time interval of the schedule
            $eStartInterval = $eMinDiff / $interval;
            // times the interval height
            $eStartPos = $eStartInterval * $timeslotHeightVal;
            $evtTop = $eStartPos.$timeslotHeightUnit;
            
            // calculate the HEIGHT value for the event DIV
            // diff between end and start of event
            $eEndMin = $eStartMin + ($event['duration']/60);
            // prevent the overall height of the event from going beyond the bounds
            // of the time table
            if ($eEndMin > $calEndMin) { $eEndMin = $calEndMin + $interval; }
            $eMinDiff = $eEndMin - $eStartMin;
            // diff divided by the time interval of the schedule
            $eEndInterval = $eMinDiff / $interval;
            // times the interval height
            $eHeight = $eEndInterval * $timeslotHeightVal;
            $evtHeight = $eHeight.$timeslotHeightUnit;

            // determine the DIV width based on any overlapping events
            // see further above for the overlapping calculation code
            $divWidth = "";
            $divLeft = "";
            if (isset($eventPositions[$event['eid']])) {
                $divWidth = "width: ".$eventPositions[$event['eid']]->width."%";
                $divLeft = "left: ".$eventPositions[$event['eid']]->leftpos."%";
            }

            $eventid = $event['eid'];
	    $eventtype = sqlQuery("SELECT pc_cattype FROM openemr_postcalendar_categories as oc LEFT OUTER JOIN openemr_postcalendar_events as oe ON oe.pc_catid=oc.pc_catid WHERE oe.pc_eid='".$eventid."'");
	    $pccattype = '';
	    if($eventtype['pc_cattype']==1)
	    $pccattype = 'true';
            $patientid = $event['pid'];
            $commapos = strpos($event['patient_name'], ",");
            $lname = substr($event['patient_name'], 0, $commapos);
	    $fname = substr($event['patient_name'], $commapos + 2);
            $patient_dob = $event['patient_dob'];
            $patient_age = $event['patient_age'];
            $catid = $event['catid'];
            $comment = addslashes($event['hometext']);
            $catname = $event['catname'];
            $title = "Age $patient_age ($patient_dob)";

            $content = "";

            if ($comment && $GLOBALS['calendar_appt_style'] < 4) $title .= " " . $comment;

            // the divTitle is what appears when the user hovers the mouse over the DIV
            $divTitle = date("D, d M Y", strtotime($date));
	    $result = sqlStatement("SELECT name,id,color FROM facility WHERE id=(SELECT pc_facility FROM openemr_postcalendar_events WHERE pc_eid=".$eventid.")");
	    $row = sqlFetchArray($result);
	    $color=$event["catcolor"];
	    if($GLOBALS['event_color']==2)
	    $color=$row['color'];
	      $divTitle .= "\n" .htmlspecialchars($row['name'],ENT_QUOTES);
            if ($catid == 2 || $catid == 3 || $catid == 4 || $catid == 8 || $catid == 11) {
                if      ($catid ==  2) $catname = xl("IN");
                else if ($catid ==  3) $catname = xl("OUT");
                else if ($catid ==  4) $catname = xl("VACATION");
                else if ($catid ==  8) $catname = xl("LUNCH");
                else if ($catid == 11) $catname = xl("RESERVED");

                $atitle = $catname;
                if ($comment) $atitle .= " $comment";
                //$divTitle .= "\n[".$atitle ."] ".$divTitle;
                $divTitle .= "\n[".$atitle ."]";
                //$content .= "<a href='javascript:oldEvt($eventdate,$eventid)' title='$atitle'>";
                $content .= $catname;
                if ($event['recurrtype'] == 1) $content .= "<img src='$TPL_IMAGE_PATH/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='Repeating event' alt='Repeating event'>";
                if ($comment) $content .= " $comment";
                //$content .= "</a>";
            }
            else {
                // some sort of patient appointment
                $divTitle .= "\r\n[".$catname.' '.htmlspecialchars($comment, ENT_QUOTES) ."] ".htmlspecialchars($fname,ENT_QUOTES)." ".htmlspecialchars($lname,ENT_QUOTES);
                $content .= "<span class='appointment".$apptToggle."'>";
                $content .= create_event_time_anchor($dispstarth . ':' . $startm);
                if ($event['recurrtype'] == 1) $content .= "<img src='$TPL_IMAGE_PATH/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='Repeating event' alt='Repeating event'>";
                $content .= htmlspecialchars($event['apptstatus']);
                if ($patientid) {
                    // include patient name and link to their details
                    $link_title = htmlspecialchars($fname,ENT_QUOTES)." ".htmlspecialchars($lname,ENT_QUOTES). " \n";
                    $link_title .= xl('Age') . ": ".$patient_age."\n" . xl('DOB') . ": ".$patient_dob.htmlspecialchars($comment, ENT_QUOTES)."\n";
                    $link_title .= "(" . xl('Click to view') . ")";
                    $content .= "<a href='javascript:goPid($patientid)' title='$link_title'>";
                    $content .= "<img src='$TPL_IMAGE_PATH/user-green.gif' border='0' title='$link_title' alt='View Patient' />";

                    if ($catid == 1) $content .= "<strike>";
                    $content .= htmlspecialchars($lname);
                    if ($GLOBALS['calendar_appt_style'] != 1) {
                        $content .= "," . htmlspecialchars($fname);
                        if ($event['title'] && $GLOBALS['calendar_appt_style'] >= 3) {
                            $content .= "(" . $event['title'];
                            if ($event['hometext'] && $GLOBALS['calendar_appt_style'] >= 4)
                            $content .= ": <font color='green'>" . htmlspecialchars(trim($event['hometext'])) . "</font>";
                            $content .= ")";
                        }
                    }
                    if ($catid == 1) $content .= "</strike>";
                    
                    $content .= "</a>";
                }
                else {
                    // no patient id, just output the category name
                    $content .= $catname;
                }
                $content .= "</span>";
            }

            $divTitle .= "\n(" . xl('double click to edit') . ")";

            // output the DIV and content
			if($_SESSION['pc_facility'] == 0){
				// output the DIV and content
				echo "<div class='".$evtClass." event' style='top:".$evtTop."; height:".$evtHeight.
						"; background-color:".$color.
						"; $divWidth".
						"; $divLeft".
						"' title='".$divTitle."'".
						" id='".$eventdate."-".$eventid."-".$pccattype."'".
						">";
				echo $content;
				echo "</div>\n";
			}
			elseif($_SESSION['pc_facility'] == $row['id']){
				echo "<div class='".$evtClass." event' style='top:".$evtTop."; height:".$evtHeight.
						"; background-color:".$event["catcolor"].
						"; $divWidth".
						"; $divLeft".
						"' title='".$divTitle."'".
						" id='".$eventdate."-".$eventid."-".$pccattype."'".
						">";
				echo $content;
				echo "</div>\n";
			}
			else{
				echo "<div class='".$evtClass." event' style='top:".$evtTop."; height:".$evtHeight.
						"; background-color:#DDDDDD".
						"; $divWidth".
						"; $divLeft".
						"' title='".$divTitle."'".
						" id='".$eventdate."-".$eventid."-".$pccattype."'".
						">";
				echo "<span style='color:red;text-align:center;font-weight:bold'>".htmlspecialchars($row['name'],ENT_QUOTES)."</span>";
				echo "</div>\n";
			}
        } // end EVENT loop

        echo "</div>";
        echo "</td>\n";
        
    } // end date
//==================================================================================================================
    echo " </tr>\n";

    echo "</table>\n";
} // end provider

 // [-**-]
 // [-include file="$TPL_NAME/views/global/footer.html"-]
 // [-include file="$TPL_NAME/views/footer.html"-]

 ?>
</div>  <!-- end bigCal DIV -->
</div> <!-- end bottom DIV -->
</body>

<script language='JavaScript'>
    var tsHeight='<?php  echo $timeslotHeightVal.$timeslotHeightUnit;  ?>';
    var tsHeightNum=<?php  echo $timeslotHeightVal;  ?>;

    $(document).ready(function(){
        setupDirectTime();
        $("#pc_username").change(function() { ChangeProviders(this); });
        $("#pc_facility").change(function() { ChangeProviders(this); });
        $("#dayview").click(function() { ChangeView(this); });
        //$("#weekview").click(function() { ChangeView(this); });
        $("#monthview").click(function() { ChangeView(this); });
        //$("#yearview").click(function() { ChangeView(this); });
        $(".tdDatePicker").click(function() { ChangeDate(this); });
        $("#datePicker .trDateRow").mouseover(function() { $(this).children().toggleClass("tdDatePickerHighlight"); });
        $("#datePicker .trDateRow").mouseout(function() { $(this).children().toggleClass("tdDatePickerHighlight"); });
        $("#datePicker .tdNav").mouseover(function() { $(this).toggleClass("tdDatePickerHighlight"); });
        $("#datePicker .tdNav").mouseout(function() { $(this).toggleClass("tdDatePickerHighlight"); });
        $("#printview").click(function() { PrintView(this); });
        $(".event").dblclick(function() { EditEvent(this); });
        $(".event").mouseover(function() { $(this).toggleClass("event_highlight"); });
        $(".event").mouseout(function() { $(this).toggleClass("event_highlight"); });

        
        $(".tdMonthName-small").click(function() {
            
            dpCal=$("#datePicker>table"); 
            mp = $("#monthPicker"); mp.width(dpCal.width()); mp.toggle();});    
    });

    /* edit an existing event */
    var EditEvent = function(eObj) {
        //alert ('editing '+eObj.id);
        // split the object ID into date and event ID
        objID = eObj.id;
        var parts = new Array();
        parts = objID.split("-");
        // call the oldEvt function to bring up the event editor
        oldEvt(parts[0], parts[1], parts[2]);
        return true;
    }

    /* change the current date based upon what the user clicked in 
     * the datepicker DIV
     */
    var ChangeDate = function(eObj) {
        baseURL = "<?php echo pnModURL(__POSTCALENDAR__,'user','view',
                        array('tplview'=>$template_view,
                        'viewtype'=>$viewtype,
                        'Date'=> '~REPLACEME~',
                        'pc_username'=>$pc_username,
                        'pc_category'=>$category,
                        'pc_topic'=>$topic)); ?>";
        newURL = baseURL.replace(/~REPLACEME~/, eObj.id);
        document.location.href=newURL;
    }

    /* pop up a window to print the current view
     */
    var PrintView = function (eventObject) {
        printURL = "<?php echo pnModURL(__POSTCALENDAR__,'user','view',
                        array('tplview'=>$template_view,
                        'viewtype'=>$viewtype,
                        'Date'=> $Date,
                        'print'=> 1,
                        'pc_username'=>$pc_username,
                        'pc_category'=>$category,
                        'pc_topic'=>$topic)); ?>";
        window.open(printURL,'printwindow','width=740,height=480,toolbar=no,location=no,directories=no,status=no,menubar=yes,scrollbars=yes,copyhistory=no,resizable=yes');
        return false;
    }

    /* change the provider(s)
     */
    var ChangeProviders = function (eventObject) {
        $('#theform').submit();
    }

    /* change the calendar view
     */
    var ChangeView = function (eventObject) {
        if (eventObject.id == "dayview") {
            $("#viewtype").val('day');
        }
        else if (eventObject.id == "weekview") {
            $("#viewtype").val('week');
        }
        else if (eventObject.id == "monthview") {
            $("#viewtype").val('month');
        }
        else if (eventObject.id == "yearview") {
            $("#viewtype").val('year');
        }
        $('#theform').submit();
    }

</script>


</html>