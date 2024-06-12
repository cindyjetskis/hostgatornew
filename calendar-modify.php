<?php require_once('../Connections/readycat.php'); ?>
<?php
session_start();
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";
$msg_updt=$_REQUEST['updt'];

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) {
  // For security, start by assuming the visitor is NOT authorized.
  $isValid = False;

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username.
  // Therefore, we know that a user is NOT logged in if that Session variable is blank.
  if (!empty($UserName)) {
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login.
    // Parse the strings into arrays.
    $arrUsers = Explode(",", $strUsers);
    $arrGroups = Explode(",", $strGroups);
    if (in_array($UserName, $arrUsers)) {
      $isValid = true;
    }
    // Or, you may restrict access to only certain users based on their username.
    if (in_array($UserGroup, $arrGroups)) {
      $isValid = true;
    }
    if (($strUsers == "") && true) {
      $isValid = true;
    }
  }
  return $isValid;
}

$MM_restrictGoTo = "login.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0)
  $MM_referrer .= "?" . $QUERY_STRING;
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo);
  exit;
}
?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE calendarevents SET eventTitle=%s, eventFromDate=%s, eventTime=%s, calendarType=%s, eventDescription=%s WHERE eventID=%s",
                       GetSQLValueString($_POST['eventTitle'], "text"),
                       GetSQLValueString($_POST['eventFromDate'], "date"),
                       GetSQLValueString($_POST['eventTime'], "text"),
                       GetSQLValueString($_POST['calendarType'], "text"),
                       GetSQLValueString($_POST['eventDescription'], "text"),
                       GetSQLValueString($_POST['eventID'], "int"));

  mysql_select_db($database_readycat, $readycat);
  $Result1 = mysql_query($updateSQL, $readycat) or die(mysql_error());

  $updateGoTo = "calendar-display.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?edit=1";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$colname_calendarModify = "-1";
if (isset($_POST['eventID'])) {
  $colname_calendarModify = $_POST['eventID'];
}
mysql_select_db($database_readycat, $readycat);
$query_calendarModify = sprintf("SELECT eventID, eventTitle, eventDescription, eventFromDate, eventTime, calendarType FROM calendarevents WHERE eventID = %s", GetSQLValueString($colname_calendarModify, "int"));
$calendarModify = mysql_query($query_calendarModify, $readycat) or die(mysql_error());
$row_calendarModify = mysql_fetch_assoc($calendarModify);
$totalRows_calendarModify = mysql_num_rows($calendarModify);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3c.org/TR/1999/REC-html401-19991224/loose.dtd">
<HTML><!-- InstanceBegin template="/Templates/admin.dwt.php" codeOutsideHTMLIsLocked="false" --><HEAD>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Modify an Event on your Calendar</title>
<link rel="StyleSheet" href="styles.css" type="text/css">
<script type="text/javascript" src="calendarDateInput.js" language="javascript"></script>
<script language="javascript">

function IsValidTime(timeStr)
{
var CurrTime=new Date();
		var curr_hours=CurrTime.getHours()
		if (curr_hours > 12)
		curr_hours = curr_hours - 12; 	// hours

		var curr_min=CurrTime.getMinutes();	// Minutes
		var curr_sec=CurrTime.getSeconds();	// seconds

		var Curr_ampm="AM";
		if (CurrTime.getHours() > 12)
		Curr_ampm="PM";

	/*--- Code END For Current Time----------*/

	var timePat = /^(\d{1,2}):(\d{2})(:(\d{2}))?(\s?(AM|am|PM|pm))?$/;

	var matchArray = timeStr.toUpperCase().match(timePat);

	if (matchArray == null)
	{
		alert("Please enter event time in HH:MM AM/PM format");
		return false;
	}
	hour = matchArray[1];
	minute = matchArray[2];
	second = matchArray[4];
	ampm = matchArray[6];
	if(hour<curr_hours && ampm==Curr_ampm)
	{
		alert("You can't specify past time.");
		return false;
	}
	if(hour==curr_hours && minute<curr_min && ampm==Curr_ampm)
	{
		alert("You can't specify past time.");
		return false;
	}

	if (second=="") { second = null; }
	if (ampm=="") { ampm = null }

	if (hour < 0  || hour > 23)
	{
		alert("Hour must be between 1 and 12. (or 0 and 23 for military time)");
		return false;
	}
	if (hour <= 12 && ampm == null)
	{
		if (confirm("Please indicate which time format you are using.  OK = Standard Time, CANCEL = Military Time"))
		{
			alert("You must specify AM or PM.");
			return false;
		}
	}
	if  (hour > 12 && ampm != null)
	{
		alert("You can't specify AM or PM for military time.");
		return false;
	}
	if (minute<0 || minute > 59)
	{
		alert ("Minute must be between 0 and 59.");
		return false;
	}
	if (second != null && (second < 0 || second > 59))
	{
		alert ("Second must be between 0 and 59.");
		return false;
	}
return true;
}









function trim(str)
	{
		return str.replace(/^\s+|\s+$/g,'');
	}

	function check_regForm()
	{
	if(trim(document.form1.eventTitle.value)== "" )
		   {
			alert("Please enter event title.");
			document.form1.eventTitle.focus();
			return false;
		   }

		   if(IsValidTime(document.form1.eventTime.value)==false)
			{
				document.form1.eventTime.focus();
				return false;
			}



		   /*if(document.form1.eventDescription.value==""){
		   alert("Please enter event description .");
		   document.form1.eventDescription.focus();
		   return false;
		   }*/
		   if(document.form1.calendarType.value==""){
		   alert("Please select atleast one calender type .");
		   document.form1.calendarType.focus();
		   return false;
		   }



		   return true;
	}
/***********************************************
* Jason's Date Input Calendar- By Jason Moon http://calendar.moonscript.com/dateinput.cfm
* Script featured on and available at http://www.dynamicdrive.com
* Keep this notice intact for use.
***********************************************/

</script>
<!-- InstanceEndEditable --><META http-equiv=Content-Type content="text/html; charset=utf-8">
<META content="" name=description>
<META content="" name=keywords>
<LINK href="../main_styles.css" type=text/css 
rel=StyleSheet>

<style type="text/css">
<!--
.style3 {color: #F2BD6D}
-->
</style>

<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
<link href="../css/default_booth.css" rel="stylesheet" type="text/css">
<script src="../SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css">
</HEAD>
<body>
<TABLE cellSpacing=0 cellPadding=0 width=796 align=center border=0>
  <TBODY>
  <TR>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=19 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=96 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=4 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=9 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=67 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=60 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=62 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=18 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=118 
      border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=22 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=23 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=11 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=110 
      border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=63 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=13 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=5 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=30 
    border=0></TD>
    <TD><IMG height=1 alt="" src="../images/clearpixel.gif" width=1 
    border=0></TD></TR>
  <TR>
    <TD rowSpan=13 valign="top" background="../images/lef-bgr.gif">&nbsp;</TD>
    <TD colSpan=15>&nbsp;</TD>
    <TD rowSpan=13 background="../images/right-bgr.gif">&nbsp;</TD>
    <TD><IMG height=13 alt="" src="../images/clearpixel.gif" width=1 
    border=0></TD></TR>
  <TR>
    <TD colSpan=15 bgcolor="#184677">
        <img src="../images/banner-idbooth.jpg" width="777" height="118"></TD>
    <TD><IMG height=59 alt="" src="../images/clearpixel.gif" width=1 
    border=0></TD></TR>
  
    <TR>
    <TD colspan="15" bgcolor="#FFFFFF">
      <ul id="MenuBar1" class="MenuBarHorizontal">
        <li><a href="welcome.php">Home</a>            </li>
        <li><a href="index.php" class="MenuBarItemSubmenu">Pages</a>
          <ul>
            <li><a href="index.php">View  Pages</a></li>
            <li><a href="form-page-new.php">Add New Page</a></li>
          </ul>
          </li>
        <li><a class="MenuBarItemSubmenu" href="add_calender_type.php">Calendars</a>
            <ul>
              <li><a href="add_calender_type.php">View Calendars</a>                  </li>
              <li><a href="add_calender_type.php">New Calendar</a></li>
              <li><a href="calendar-display.php">View Events</a></li>
              <li><a href="calendar-input.php">New Event</a></li>
            </ul>
        </li>
        <li><a href="show_manufacturer.php" class="MenuBarItemSubmenu">Manufacturers</a>
          <ul>
            <li><a href="product.php">Manufacturing Types</a></li>
            <li><a href="show_manufacturer.php">Manufacturers</a></li>
            <li><a href="manufacturer.php">Add Manufacturer</a></li>
            <li><a href="show_product.php">View Products</a></li>
            <li><a href="add_product.php">New Product</a></li>
            <li><a href="show_product_manufactuere.php"> Products of Manfacturers</a></li>
          </ul>
          </li>
        <li><a href="banners.php">Banners</a>          </li>
        <li><a href="users.php" class="MenuBarItemSubmenu">Users</a>
          <ul>
            <li><a href="users-display.php">View Users</a></li>
            <li><a href="user-new.php">New User</a></li>
          </ul>
          </li>
        <li><a href="support.php">Support</a></li>
      </ul></TD>
    <TD><IMG height=39 alt="" src="../images/clearpixel.gif" width=1 border=0></TD></TR>
  <TR>
    <TD colSpan=15 rowspan="11" valign="top" bgcolor="#FFFFFF">	
      <div align="left"></div>        <table width="100%"  border="0" cellspacing="0" cellpadding="25">
          <tr>
            <td valign="top"><!-- InstanceBeginEditable name="body" -->
          <h1>Modify this Event</h1>
          <p>&nbsp;</p>


          <form method="post" name="form1" action="<?php echo $editFormAction; ?>" onSubmit="return check_regForm();">
            <table width="650" align="center" cellpadding="5">
              <tr valign="baseline">
                <td nowrap align="right">Event ID:</td>
                <td><?php echo $row_calendarModify['eventID'];
				 ?>

				</td>
              </tr>
              <tr valign="baseline">
                <td nowrap align="right">Event Title<font color="#CC0000">*:</font></td>
                <td><input type="text" name="eventTitle" value="<?php echo $row_calendarModify['eventTitle']; ?>" size="32"></td>
              </tr>
              <tr valign="baseline">
                <td nowrap align="right">Date<font color="#CC0000">*:</font></td>
                <td><!--<input type="text" name="eventFromDate" value="<?php //echo $row_calendarModify['eventFromDate']; ?>" size="32">-->
				<script>DateInput('eventFromDate', true, 'YYYY-MM-DD','<?=$row_calendarModify['eventFromDate'];?>')</script>
               </td>
              </tr>
              <tr valign="baseline">
                <td nowrap align="right">Time<font color="#CC0000">*:</font></td>
                <td><input type="text" name="eventTime" value="<?php echo $row_calendarModify['eventTime']; ?>" size="32"></td>
              </tr>

              <tr valign="baseline">
                <td nowrap align="right" valign="top">Event Description:</td>
                <td><textarea name="eventDescription" cols="50" rows="5"><?php echo $row_calendarModify['eventDescription']; ?></textarea>                </td>
              </tr>
              <tr valign="baseline">
                <td nowrap align="right">Calendar Type<font color="#CC0000">*:</font></td>
                <td><!--<input name="calendarType" type="text" id="calendarType" value="<?php //echo $row_calendarModify['calendarType']; ?>" size="32">-->
				<label>
                  <select name="calendarType" id="calendarType">
				  <option value="">Please Select</option>
				  <?
				//$qr="select * from tblcalender";
				//$sql=mysql_query($qr);
				$qr="select * from tblcalender";
				$sql=mysql_query($qr);
				while($row=mysql_fetch_array($sql))
				{
				?>
                  <option value="<? echo $row['id']?>" <? if($row['id']==$row_calendarModify['calendarType']) { echo "selected";} ?>>

				  <? echo $row['calender_type']?>
				  </option>

				<?
				}
				?>
				<!--<option value="public" selected>public</option>
                    <option value="training">training</option>
                    <option value="executives">executives</option>-->
                  </select>
                </label>
				</td>
              </tr>
              <tr valign="baseline">
                <td nowrap align="right">&nbsp;</td>
                <td><input type="submit" value="Update event"></td>
              </tr>
            </table>
            <input type="hidden" name="MM_update" value="form1">
			<input type="hidden" name="location" value="Tanglewood Nature Center">
            <input type="hidden" name="eventID" value="<?php echo $row_calendarModify['eventID']; ?>">
          </form>
          <p>&nbsp;</p>
        <!-- InstanceEndEditable --></td>
          </tr>
      </table></TD>
    <TD><IMG height=39 alt="" src="../images/clearpixel.gif" width=1 
    border=0></TD></TR>
  <TR>
    <TD><IMG height=59 alt="" src="../images/clearpixel.gif" width=1 
    border=0></TD></TR>
  <TR>
    <TD><IMG height=11 alt="" src="../images/clearpixel.gif" width=1 
    border=0></TD></TR>
  <TR>
    <TD><IMG height=63 alt="" src="../images/clearpixel.gif" width=1 
    border=0></TD></TR>
  <TR>
    <TD><IMG height=43 alt="" src="../images/clearpixel.gif" width=1 
    border=0></TD></TR>
  <TR>
    <TD><IMG height=65 alt="" src="../images/clearpixel.gif" width=1 
    border=0></TD></TR>
  <TR>
    <TD><IMG height=88 alt="" src="../images/clearpixel.gif" width=1 
    border=0></TD></TR>
  <TR>
    <TD><IMG height=14 alt="" src="../images/clearpixel.gif" width=1 
    border=0></TD></TR>
  <TR>
    <TD><IMG height=7 alt="" src="../images/clearpixel.gif" width=1 
    border=0></TD></TR>
  <TR>
    <TD><IMG height=138 alt="" src="../images/clearpixel.gif" width=1 
      border=0></TD></TR>
  <TR>
    <TD><IMG height=79 alt="" src="../images/clearpixel.gif" width=1 
    border=0></TD></TR>
  <TR>
    <TD valign="top" background="../images/lef-bgr.gif">&nbsp;</TD>
    <TD colSpan=8 bgcolor="#F6B251"><span class="bottom_menu_text">&nbsp;&nbsp;
ID Booth, Elmira NY</span></TD>
    <TD colSpan=5 bgcolor="#F6B251"><div align="right"><span class="bottom_text_right"><A class=brownlink 
      href="support.php">Admin Support </A> <FONT color=#c29c3a>|&nbsp; </FONT>&nbsp; </span></div></TD>
    <TD bgcolor="#F6B251">&nbsp;</TD>
    <TD bgcolor="#F6B251">&nbsp;</TD>
    <TD background="../images/right-bgr.gif">&nbsp;</TD>
    <TD>&nbsp;</TD>
  </TR>
  <TR>
    <TD valign="top">&nbsp;</TD>
    <TD colSpan=15 background="../images/bottom_bar.gif">&nbsp;</TD>
    <TD>&nbsp;</TD>
    <TD></TD>
  </TR>
  </TBODY></TABLE>
<div align="center"></div>
<P align="center" class=bottom_text_centered>Web development by <A class=style3 
href="http://www.lionsites.com/" target=_blank>Lionsites Web Design, Elmira NY </A> 
<BR>
<BR></P>
<script type="text/javascript">
<!--
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"../SpryAssets/SpryMenuBarDownHover.gif", imgRight:"../SpryAssets/SpryMenuBarRightHover.gif"});
//-->
</script>
</BODY><!-- InstanceEnd --></HTML>
<?php
mysql_free_result($calendarModify);
?>
