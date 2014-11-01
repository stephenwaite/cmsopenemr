<?php /* Smarty version 2.3.1, created on 2014-04-09 08:41:25
         compiled from default/views/week_print/outlook_ajax_template.html */ ?>
<?php $this->_load_plugins(array(
array('function', 'assign', 'default/views/week_print/outlook_ajax_template.html', 245, false),
array('function', 'pc_sort_events', 'default/views/week_print/outlook_ajax_template.html', 250, false),
array('modifier', 'date_format', 'default/views/week_print/outlook_ajax_template.html', 245, false),
array('modifier', 'string_format', 'default/views/week_print/outlook_ajax_template.html', 246, false),)); ?>








<?php $this->_config_load("default.conf", null, 'local'); ?>

<?php $this->_config_load("lang.$USER_LANG", null, 'local'); ?>


<html>
<head>


<?php $timeslotHeightVal=20; $timeslotHeightUnit="px"; ?>

<style>
body {
    font-size: 1em;
}
a {
 text-decoration:none;
}
td {
 font-family: Arial, Helvetica, sans-serif;
}
div.tiny { width:1px; height:1px; font-size:1px; }

#bigCalHeader {
    height: 20%;
    font-family: Arial, Helvetica, sans-serif;
    float: left;
}
#bigCalText {
    float: left;
}
#provname {
    font-size: 2em;
}
#daterange {
    font-size: 1.8em;
    font-weight: bold;
}
.pagebreak {
    page-break-after: always;    
}

/* these are for the small datepicker DIV */
#datePicker {
    float: right;
    padding: 5px;
    text-align: center;
    margin: 5px;
}
#datePicker td {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 0.7em;
}
#datePicker table {
    border-collapse: collapse;
}
#datePicker .tdDOW-small {
    font-family: Arial, Helvetica, sans-serif;
    vertical-align: top; 
    text-align: center;
    border-bottom: 1px solid black;
    padding: 2px 3px 2px 3px;
}
#datePicker .tdWeekend-small {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
    vertical-align: top;
    border: none;
    padding: 2px 3px 2px 3px;
}

#datePicker .tdOtherMonthDay-small {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
    vertical-align: top;
    border: none;
    padding: 2px 3px 2px 3px;
    color: #fff;
}

#datePicker .tdMonthName-small {
    text-align: center;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
    font-style: normal 
}

#datePicker .tdMonthDay-small {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
    vertical-align: top;
    border: none;
    padding: 2px 3px 2px 3px;
    background-color: #ffffff;
}
#datePicker .currentWeek {
    border-top: 1px solid blue;
    border-bottom: 1px solid blue;
}
#datePicker .currentDate {
    border: 1px solid blue;
    background-color: blue;
    color: lightblue;
}

/* the DIV of times */
#times {
    border-right: 1px solid #999;
}
#times table {
    border-collapse: collapse;
    width: 100%;
    margin: 0px;
    padding: 0px;
}
#times table td {
    border: 0px;
    border-top: 1px solid #999;
    margin: 0px;
    padding: 0px;
    font-size: 10pt;
}
.timeslot {
    height: <?php echo $timeslotHeightVal.$timeslotHeightUnit; ?>;
    margin: 0px; 
    padding: 0px;
}
.schedule {
    background-color: pink;
    vertical-align: top;
    padding: 0px;
    margin: 0px;
    border-right: 1px solid black;
}
/* types of events */
.event_in {
    width: 98%; 
    font-size: 0.8em;
    padding: 2px;
}
.event_out {
    width: 98%;
    font-size: 0.8em;
    padding: 2px;
}
.event_appointment {
    overflow: hidden;
    width: 98%;
    font-size: 0.8em;
    padding: 2px;
}
.event_noshow {
    overflow: hidden;
    width: 98%;
    font-size: 0.8em;
    padding: 2px;
}
.event_reserved {
    overflow: hidden;
    width: 98%;
    font-size: 0.8em;
    padding: 2px;
}
/* these hold the day groupings */
#weekcal {
    width: 100%;
    height: 80%;
    border-collapse: collapse;
    float: left;
}
#weekcal td {
    vertical-align:top;
    text-align:left;
    border: 1px solid #333;
    width: 50%;
    height: 20%;
}
#dowheader {
    font-family: helvetica, arial;
    font-weight: bold;
    border-bottom: 1px solid #333;
    font-align: left;
    font-size: 1em;
}
</style>

<script type="text/javascript" src="../../../library/textformat.js"></script>

</head>
<body>

<?php 

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

 $A_CATEGORY  =& $this->_tpl_vars['A_CATEGORY'];

 $A_EVENTS  =& $this->_tpl_vars['A_EVENTS'];
 // $S_EVENTS  =& $this->_tpl_vars['S_EVENTS']; // Deleted by Rod
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

 $MULTIDAY = count($A_EVENTS) > 1;

 ?>

<?php $this->_plugins['function']['assign'][0](array('var' => "dayname",'value' => $this->_run_mod_handler('date_format', true, $this->_tpl_vars['DATE'], "%w")), $this); if($this->_extract) { extract($this->_tpl_vars); $this->_extract=false; } ?>
<?php $this->_plugins['function']['assign'][0](array('var' => "day",'value' => $this->_run_mod_handler('string_format', true, $this->_run_mod_handler('date_format', true, $this->_tpl_vars['DATE'], "%d"), "%1d")), $this); if($this->_extract) { extract($this->_tpl_vars); $this->_extract=false; } ?>
<?php $this->_plugins['function']['assign'][0](array('var' => "month",'value' => $this->_run_mod_handler('string_format', true, $this->_run_mod_handler('date_format', true, $this->_tpl_vars['DATE'], "%m"), "%1d")), $this); if($this->_extract) { extract($this->_tpl_vars); $this->_extract=false; } ?>
<?php $this->_plugins['function']['assign'][0](array('var' => "year",'value' => $this->_run_mod_handler('string_format', true, $this->_run_mod_handler('date_format', true, $this->_tpl_vars['DATE'], "%Y"), "%4d")), $this); if($this->_extract) { extract($this->_tpl_vars); $this->_extract=false; } ?>

<?php $this->_plugins['function']['pc_sort_events'][0](array('var' => "S_EVENTS",'sort' => "time",'order' => "asc",'value' => $this->_tpl_vars['A_EVENTS']), $this); if($this->_extract) { extract($this->_tpl_vars); $this->_extract=false; } ?>

<div id="bigCal">
<?php 
    
// start out without adding a pagebreak
$addPagebreak = false;

// This loops once for each provider to be displayed.
//
foreach ($providers as $provider) {
    // output a pagebreak, if needed
    if ($addPagebreak) { echo "<div class='pagebreak'></div>\n"; }
    $addPagebreak = true;

    echo "<div id='bigCalHeader'>";

    echo "<div id='bigCalText'>";
    // output the provider name
    echo "<span id='provname'>".$provider['fname'] . " " . $provider['lname']."</span>";
    echo "<br>";
    // output the date range
    echo "<span id='daterange'>";
    $atmp = array_keys($A_EVENTS);
    echo xl(date('F', strtotime($atmp[0]))) . " "  . date('d', strtotime($atmp[0]));
    echo " - <br>";
    echo xl(date('F', strtotime($atmp[count($atmp)-1]))) . " "  . date('d', strtotime($atmp[count($atmp)-1]));
    echo "</span>";
    echo "</div>";

    // output a calendar for the subsequent month
    list($nyear, $nmonth, $nday) = explode(" ", date("Y m d", strtotime($atmp[0])));
    $nmonth++;
    if ($nmonth > 12) { $nyear++; $nmonth=1; }
    echo "<div id='datePicker'>";
    PrintDatePicker(strtotime($nyear."-".$nmonth."-1"), $DOWlist, $this->_tpl_vars['A_SHORT_DAY_NAMES']);
    echo "</div>";
    
    // output a small calendar for the chosen month
    echo "<div id='datePicker'>";
    PrintDatePicker(strtotime($atmp[0]), $DOWlist, $this->_tpl_vars['A_SHORT_DAY_NAMES']);
    echo "</div>";

    echo "</div>"; // end the bigCalHeader
    
    $providerid = $provider['id'];

    // to specially handle the IN/OUT events I'm doing something new here
    // for each IN event it will have a duration lasting until the next
    // OUT event or until the end of the day
    $tmpTime = $times[0];
    $calStartMin = ($tmpTime['hour'] * 60) + $tmpTime['minute'];
    $tmpTime = $times[count($times)-1];
    $calEndMin = ($tmpTime['hour'] * 60) + $tmpTime['minute'];

    // For each day...
    // output a TD with an inner containing DIV positioned 'relative'
    echo "<table id='weekcal' >";
    $loopcount = 0;
    foreach ($A_EVENTS as $date => $events) {
        echo "<tr>";
        $eventdate = substr($date, 0, 4) . substr($date, 5, 2) . substr($date, 8, 2);

        // each date gets it's own header
        echo "<td><div id='dowheader'>". xl(date("D",strtotime($eventdate))) . " " . date("m/d", strtotime($eventdate))."</div>";

        // output the events for this date
        PrintEvents($events, $providerid, $TPL_IMAGE_PATH);

        // now output the events for this date+4, if they exist
        echo "</td><td>";
        $dateplusfour = strtotime($eventdate) + (4 * 24 * 60 * 60);
        if (isset($A_EVENTS[date("Y-m-d", $dateplusfour)])) {
            echo "<div id='dowheader'>". xl(date("D",$dateplusfour)) . " " . date("m/d", $dateplusfour)."</div>";
            $tmpEvents = $A_EVENTS[date("Y-m-d", $dateplusfour)];
            PrintEvents($tmpEvents, $providerid, $TPL_IMAGE_PATH);
        }
        echo "</tr>";

        // limit the times through this loop by the limit of four TRs
        if ($loopcount++ >= 3) break;
    } // end date

    echo "</table>\n";

} // end provider



/* print out the array of events */
function PrintEvents($events, $providerid, $TPL_IMAGE_PATH) {
    global $times, $calStartMin, $calEndMin;

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
                $event['duration'] = ($calEndMin - $calStartMin) * 60;  // measured in seconds
            }

            // figure the start time and minutes (from midnight)
            $starth = substr($event['startTime'], 0, 2);
            $startm = substr($event['startTime'], 3, 2);
            $eStartMin = $starth * 60 + $startm;

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

            $eventdatetime = strtotime($date." ".$starth.":".$startm);

            if ($catid == 2 || $catid == 3 || $catid == 4 || $catid == 8 || $catid == 11) {
                if      ($catid ==  2) $catname = xl("IN");
                else if ($catid ==  3) $catname = xl("OUT");
                else if ($catid ==  4) $catname = xl("VACATION");
                else if ($catid ==  8) $catname = xl("LUNCH");
                else if ($catid == 11) $catname = xl("RESERVED");

                $content .= date("h:i", $eventdatetime);
                if ($event['recurrtype'] == 1) $content .= "<img src='$TPL_IMAGE_PATH/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='Repeating event' alt='Repeating event'>";
                $content .= " ".$catname;
                if ($comment) $content .= " $comment";
            }
            else {
                // some sort of patient appointment
                $content .= "<span class='appointment".$apptToggle."'>";
                $content .= date("h:i", $eventdatetime). " ";
                if ($event['recurrtype'] == 1) $content .= "<img src='$TPL_IMAGE_PATH/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='Repeating event' alt='Repeating event'>";
                if ($patientid) {
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
                }
                else {
                    // no patient id, just output the category name
                    $content .= $catname;
                }

                $content .= "</span>";
            }

            $divTitle .= "\n(double click to edit)";

            // output the DIV and content
            echo "<div class='".$evtClass." event' style='background-color:".$event["catcolor"].
                    "' ".
                    " id='".$eventdate."-".$eventid."'".
                    ">";
            echo $content;
            echo "</div>";
        } // end EVENT loop

    return;
}

/* output a small calendar, based on the date-picker code from the normal calendar */
function PrintDatePicker($caldate, $DOWlist, $daynames) {

    $cMonth = date("m", $caldate);
    $cYear = date("Y", $caldate);
    $cDay = date("d", $caldate);

    echo '<table>';
    echo '<tr>';
    echo '<td colspan="7" class="tdMonthName-small">';
    echo date('F Y', $caldate);
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    foreach ($DOWlist as $dow) {
        echo "<td class='tdDOW-small'>".$daynames[$dow]."</td>";
    }
    echo '</tr>';
    
    // to make a complete week row we need to compute the real
    // start and end dates for the view
    list ($year, $month, $day) = explode(" ", date('Y m d', $caldate));
    $startdate = strtotime($year.$month."01");
    while (date('w', $startdate) != $DOWlist[0]) { $startdate -= 60*60*24; }

    $enddate = strtotime($year.$month.date("t", $month));
    while (date('w', $enddate) != $DOWlist[6]) { $enddate += 60*60*24; }

    $currdate = $startdate;
    while ($currdate <= $enddate) {
        if (date('w', $currdate) == $DOWlist[0]) {
            echo "<tr>";
        }

        // we skip outputting some days
        $skipit = false;

        // set the TD class
        $tdClass = "tdMonthDay-small";
        if (date('m', $currdate) != $month) {
            $tdClass = "tdOtherMonthDay-small";
            $skipit = true;
        }
        if ((date('w', $currdate) == 0) || (date('w', $currdate) == 6)) {
            $tdClass = "tdWeekend-small";
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
        $td .= "> ".date('d', $currdate)."</td>\n";
        if ($skipit == true) { echo "<td></td>"; }
        else { echo $td; }
   
        // end of week row
        if (date('w', $currdate) == $DOWlist[6]) echo "</tr>\n";

        // time correction = plus 1000 seconds, for some unknown reason
        $currdate += (60*60*24)+1000;
    }
    echo "</table>";
}
 ?>
</table>

</body>

<script type="text/javascript" src="<?php  echo $GLOBALS['webroot']  ?>/library/js/jquery-1.2.2.min.js"></script>
<script>
$(document).ready(function(){
    // a poor-man's attempt to scale down the text to make sure
    // the calendar fits onto a single piece of paper
    if (($("#bigCal").height() > 900) && ($("#bigCal").height() < 1200)) {
        $("#weekcal *").css("font-size", "90%");
    }
    else if ($("#bigCal").height() > 1200) {
        $("#weekcal *").css("font-size", "80%");
    }
    window.print();
    window.close();
});
</script>

</html>