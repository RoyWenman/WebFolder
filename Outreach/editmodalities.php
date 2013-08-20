<head>
	<?php include('header.php'); ?>
	<script src="media/js/jquery-1.10.0.min.js"></script>
	<script src="media/js/date.js"></script>
	<script src="media/js/datesDifference.js"></script>
	<script src="media/js/closeEditPages.js"></script>
	<script language="JavaScript" type="text/javascript">	
	function calculateDuration(startDate, startTime, endDate, endTime) {	    
	    var differenceDays = getDaysDifference(startDate, endDate);
	    var differenceHoursMins = getHoursMinsDifference(startTime, endTime);	    
	    var duration = differenceDays + differenceHoursMins;
	    
	    $('#duration').val(duration);	    
	}	
	
	$(document).ready(function() {
	    $('#startDate, #startTime, #endDate, #endTime').change(function() {
		var startDate = $('#startDate').val();
		var startTime = $('#startTime').val();
		var endDate = $('#endDate').val();
		var endTime = $('#endTime').val();
		
		if ((startDate !== '') && (endDate !== '')) {
		    calculateDuration(startDate,startTime,endDate,endTime);
		}
	    });
	});
	</script>
	<div id="top_of_page"></div>
</head>
<?php
error_reporting(E_ALL ^ E_NOTICE);
include './MelaClass/functions.php';
include './MelaClass/Mela_Forms.php';
include './MelaClass/db.php';
include './MelaClass/authInitScript.php';

$Form = new Mela_Forms('editModalitiess','','POST','modalities_form');

if (!$_REQUEST['dlk'] || !$_REQUEST['lnk'] || !$_REQUEST['row']) die("Necessary data is missing");

$row = filter_var($_REQUEST['row'], FILTER_SANITIZE_NUMBER_INT);
$dlkID = filter_var($_REQUEST['dlk'], FILTER_SANITIZE_NUMBER_INT);
$lnkID = filter_var($_REQUEST['lnk'], FILTER_SANITIZE_NUMBER_INT);

// Get relevant data to fill out form
$query = "SELECT m.mod_ID, m.mod_AssessedDate, m.mod_Time, m.mod_Comments, m.mod_PerformedBy, m.mod_Frequency,
	  m.End_Date, m.End_Time, m.Discon_as_planned, m.NoOfTries, m.NoOfGoods, m.mod_Dose, m.PumpNo, m.Background,
	  m.mod_SkinEpiDistance, m.mod_NumOfBoluses, m.Catheter_in_space, m.Attempts_to_site, m.Failure_to_site,
	  m.SkinPrep, m.EpiTestDose, m.EpiInitDose, m.EpiMaintainDose, m.Epi2Hr_Limit, m.Epi2Hr_Dose, m.mod_Rate,
	  m.mod_Background_Val, m.mod_Infusion, m.mod_ChoiceOfLA, m.mod_ChoiceOfLA2, m.mod_ChoiceOfOpioid, m.mod_ChoiceOfOpioid2,
	  m.mod_Type, m.mod_InsertionLevel, m.mod_BlockLevelLeft1, m.mod_BlockLevelLeft2, m.mod_BlockLevelRight1, m.mod_BlockLevelRight2,
	  m.mod_Position, m.mod_DensityOfBlock, m.mod_BromageScore, m.ReasonForFailToSite, m.AsepticPrecautions, m.LOR, m.`Duration`,
	  m.mod_SiteCheck, m.mod_Frequency, m.mod_Background_Unit, m.Epi2Hr_Unit, m.Dose_String, m.PerfBy_PersonID, m.DisconBy_PersonID,
	  itm.GrpID,
	  dmg.dmg_FirstName, dmg.dmg_MiddleName, dmg.dmg_Surname, dmg.dmg_DateOfBirth, dmg.dmg_Sex, dmg.dmg_NHSNumber, dmg.dmg_HospitalNumber,
	  adm.adm_Number,
	  lnk.lnk_ID
	  FROM Modality m
	  LEFT OUTER JOIN Items_Modality itm ON m.mod_ID=itm.ID
	  LEFT OUTER JOIN LINK lnk ON m.mod_lnkID = lnk.lnk_ID
	  LEFT OUTER JOIN Demographic dmg ON dmg.dmg_ID = lnk.lnk_dmgID
	  LEFT OUTER JOIN Admission adm ON adm.lnk_ID = lnk.lnk_ID
	  WHERE m.mod_ID=$row AND m.mod_dlkID=$dlkID AND m.mod_lnkID=$lnkID AND itm.ID=m.mod_ID";
try { 
    $result = odbc_exec($connect,$query); 
    if($result){ 
	$modalityData = odbc_fetch_array($result);
    } 
    else{ 
    throw new RuntimeException("Failed to connect."); 
    } 
	} 
    catch (RuntimeException $e) { 
	    print("Exception caught: $e");
    }






$query_PAT = "SELECT dmg.dmg_FirstName, dmg.dmg_Surname, dmg.dmg_DateOfBirth, dmg.dmg_Sex, dmg.dmg_NHSNumber, dmg.dmg_HospitalNumber, adm.adm_Number
			FROM Demographic dmg
			LEFT OUTER JOIN LINK lnk ON lnk.lnk_dmgID = dmg.dmg_ID
			LEFT OUTER JOIN Admission adm ON adm.adm_ID = lnk.lnk_admID
			WHERE lnk.lnk_ID = $lnkID";
try { 
    $result_PAT = odbc_exec($connect,$query_PAT); 
    if($result_PAT){ 
	$HeaderData = odbc_fetch_array($result_PAT);
    } 
    else{ 
    throw new RuntimeException("Failed to connect."); 
    } 
	} 
    catch (RuntimeException $e) { 
	    print("Exception caught: $e");
    }






$hiddenRow = $Form->hiddenField('row',$row);
$hiddenDLK = $Form->hiddenField('dlk',$dlkID);
$hiddenLNK = $Form->hiddenField('lnk',$lnkID);

echo $hiddenRow;
echo $hiddenDLK;
echo $hiddenLNK;

if ($_POST) {    
    if (!$_POST['modID']) die("No ID set for selected row.");
    $modID = filter_var($_REQUEST['modID'], FILTER_SANITIZE_NUMBER_INT);
    
    foreach($_POST as $k => $v) {
	$formKey[$k] = checkValues($k);
	$formVal[$k] = checkValues($v);
	echo "<b>". $formKey[$k] ."</b> - ". $formVal[$k] ."<br />";
    }    
    //echo "<h1>".$formKey['noOfTries']." is ".$formVal['noOfTries']."</h1>";
    
    /*
     * INTs: noOfTries, noOfGoods, dose, firstBolusDose, testDose, initialDose, maintenanceDose, hourlyLimit
     * hourlyDose, totalInfusion
     */
    
    $assessedDateSQL = "";
    if (strlen($formVal['startDate']) > 0) {
	$assessedDateSQL = "mod_AssessedDate='".$formVal['startDate']."', ";
    }
    
    $endDateSQL = "";
    if (strlen($formVal['endDate']) > 0) {
	$endDateSQL = "End_Date='".$formVal['endDate']."', ";
    }
    
    // Some fields like radio buttons don't get any POST data sent if left blank which 4D will kick up a fuss about so account for those here
    if (!isset($formVal['discontAsPlanned'])) $formVal['discontAsPlanned'] = "";
    if (!isset($formVal['discontBy'])) $formVal['discontBy'] = "";
    if (!isset($formVal['performedBy'])) $formVal['performedBy'] = "";
    if (!isset($formVal['failureToSite'])) $formVal['failureToSite'] = "";
    if (!isset($formVal['skinPreparation'])) $formVal['skinPreparation'] = "";
    
    $hiddenComments = $Form->hiddenField('hiddenComments',$formVal['comments']);
    echo $hiddenComments;
    
    // Need to check that the record is properly locked first of all
    if ($Mela_SQL->Exec4DSQL("SQLLock_IsLocked", $lnkID) == 1) { 
	$query = "UPDATE Modality SET $assessedDateSQL mod_Time='".$formVal['startTime']."', $endDateSQL End_Time='".$formVal['endTime']."',
	`Duration`='".$formVal['duration']."', Discon_as_planned=".$formVal['discontAsPlanned'].", DisconBy_PersonID=".$formVal['discontBy'].", mod_Comments='".$formVal['comments']."',
	NoOfTries=".$formVal['noOfTries'].", NoOfGoods=".$formVal['noOfGoods'].", PerfBy_PersonID=".$formVal['performedBy'].", Dose_String='".$formVal['doseString']."', PumpNo='".$formVal['pumpNo']."',
	mod_ChoiceOfLA='".$formVal['choiceOfLA']."', mod_ChoiceOfOpioid='".$formVal['choiceOfOpioid']."', mod_ChoiceOfLA2='".$formVal['choiceOfLA2']."', mod_ChoiceOfOpioid2='".$formVal['choiceOfOpioid2']."',
	mod_Type='".$formVal['EpiType']."', Background='".$formVal['lockout']."', mod_InsertionLevel='".$formVal['insertionLevel']."', mod_SkinEpiDistance='".$formVal['positionAtSkin']."',
	mod_NumOfBoluses=".$formVal['firstBolusDose'].", Catheter_in_space='".$formVal['catheterInSpace']."', mod_BlockLevelLeft1='".$formVal['blockLevelLeft1']."', mod_BlockLevelRight1='".$formVal['blockLevelRight1']."',
	mod_Position='".$formVal['patientPosition']."', mod_BlockLevelLeft2='".$formVal['blockLevelLeft2']."', mod_BlockLevelRight2='".$formVal['blockLevelRight2']."', Attempts_to_site=".$formVal['attemptsToSite'].",
	mod_DensityOfBlock='".$formVal['densityOfBlock']."', Failure_to_site=".$formVal['failureToSite'].", mod_BromageScore='".$formVal['bromageScore']."', ReasonForFailToSite='".$formVal['reasonForFailToSite']."',
	AsepticPrecautions='".$formVal['asepticPrecaut']."', SkinPrep=".$formVal['skinPreparation'].", LOR='".$formVal['LOR']."', EpiTestDose=".$formVal['testDose'].", EpiInitDose=".$formVal['initialDose'].",
	EpiMaintainDose=".$formVal['maintenanceDose'].", Epi2Hr_Limit=".$formVal['hourlyLimit'].", Epi2Hr_Dose=".$formVal['hourlyDose'].", Epi2Hr_Unit='".$formVal['epiUnit']."', mod_Rate='".$formVal['rate']."',
	mod_SiteCheck='".$formVal['siteCheck']."', mod_Frequency='".$formVal['frequency']."', mod_Background_Val='".$formVal['backgInfusion']."', mod_Background_Unit='".$formVal['backgroundUnit']."',
	mod_Infusion=".$formVal['totalInfusion']."
	WHERE mod_ID=$modID";
	try { 
	    $result = odbc_exec($connect,$query); 
	    if($result){ 
		    /*while ($systems = odbc_fetch_array($result)) {
			array_push($discontByDDArray,$systems['MDS_NAME']);
			array_push($discontByValueDD,$systems['MDS_ID']);
		    }*/
		?>
		<script type="text/javascript">
		    CloseAndRefresh('row','hiddenComments','MOnotes');
		</script>	    
		<?php
	    } 
	    else{ 
	    throw new RuntimeException("Failed to connect."); 
	    } 
	} 
	catch (RuntimeException $e) { 
	    print("Exception caught: $e");
	}
      } else {
        echo '<div style="height:100%; width:100%;">
		<div class="failurebox" style="vertical-align: middle; text-align: center; margin-left: auto; margin-right: auto; border: 3px solid #A52A2A; background-color: #CD5C5C; color: #330000; height:50%; width:50%;">
		    <span style="vertical-align: middle; height: 100%;">
			<h2>
			    Record Locking Error
			</h2>
			The selected record was not locked and therefore data were not safe to save. 
			<br />
			<button type="button" style="font-size:small;color:red" onclick="failedRecordLock()">Please click here to return to patient listing</button>
		    </span>
		</div>
	    </div>';
    }	

} else {
    $modID = $Form->hiddenField('modID',$modalityData['MOD_ID']);
    echo $modID;
?>



<div class="container clearfix">

			<div class="Header_List">
				<ul id="Head_Left" class="grid_3 alpha">
					<li><?php echo $HeaderData['DMG_FIRSTNAME']; ?></li> 
					<li><?php echo $HeaderData['DMG_SURNAME']; ?></li>
				</ul>
				<ul id="Head_Mid" class="grid_3">
					<li>
						<table>
							<tr><td id="Table_Mid">Sex&nbsp;</td><td id="Table_Mid"><?php echo $HeaderData['DMG_SEX']; ?></td></tr>
							<tr><td id="Table_Mid">DOB&nbsp;</td><td id="Table_Mid"><?php $splitDOB = explode(' ',$HeaderData['DMG_DATEOFBIRTH']); echo $splitDOB[0]; ?></td></tr>
						</table>
					</li>
				</ul>
				<ul id="Head_Right" class="grid_3 omega">
					<li>
						<table>
							<tr><td id="Table_Right">NHS No&nbsp;</td><td id="Table_Right"><?php echo $HeaderData['DMG_NHSNUMBER']; ?></td></tr>
							<tr><td id="Table_Right">Hospital No&nbsp;</td><td id="Table_Right"><?php echo $HeaderData['DMG_HOSPITALNUMBER']; ?></td></tr>
							<tr><td id="Table_Right">Referral No&nbsp;</td><td id="Table_Right"><?php echo $HeaderData['ADM_NUMBER']; ?></td></tr>
						</table>
					</li>
				</ul>
			</div>


				<div id="tabs2" class="btn_bar">
					<button style="font-size:small;color:red" type="button"  value="Cancel" onclick="CloseAndRefresh()" href=>Cancel</button>
					<button style="font-size:small;color:green" type="submit" value="Save">Save</button>
					<!-- Form submission success -->
					<div id="success" style="display: none;">
						Success
					</div>
				</div>





<div class='mod_Form'>

    <form action="" method="POST">

<div class='div_modDetail'>

    <table class="temp modDetail">
    	<tr>
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
    		<td colspan='3'>Start Date/time</td>
    		<td colspan='4'>
				<?php
		    		$startDate = $Form->dateField('startDate',stringToDateTime($modalityData['MOD_ASSESSEDDATE'],2));
				$startTime = $Form->timeField('startTime',convert4DTime($modalityData['MOD_TIME']));
				
				echo $startDate;
				echo $startTime;
				?>
			</td>
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
    		<td colspan='3'>End Date/time</td>
    		<td colspan='4'>
    			<?php
		   	    $endDate = $Form->dateField('endDate',stringToDateTime($modalityData['END_DATE'],2));
			    $endTime = $Form->timeField('endTime',convert4DTime($modalityData['END_TIME']));
			    echo $endDate;
			    echo $endTime;
			?>
			</td>
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
    		<td colspan='2'>Duration</td>
    		<td colspan='2'>
				<?php
		    		// $duration = $Form->textBox('duration',$modalityData['DURATION']);
		    		$duration = $Form->textBox('duration','00:00','','','FormDTTime');
				print $duration;
				?>
    		</td>
    	</tr>
    	<!--    #    #    #    #     #     #     #     #     #     #    -->
    	<tr>
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
			<td colspan='2'>Discontinued as planned</td>
			<td colspan='6'>
				<?php
		            $discontAsPlannedOptions = array(1 => ' Yes ', 2 => ' No ');
		            $discontAsPlanned = $Form->radioBox('discontAsPlanned',$discontAsPlannedOptions,''.$modalityData['DISCON_AS_PLANNED'].'','');
		            print $discontAsPlanned;
		        ?>
			</td>
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
			<td colspan='2'>Discontinued by</td>
			<td colspan='8'>
				<?php
				    $discontByDDArray = array();
				    $discontByValueDD = array();
				    $query = "SELECT mds_ID, mds_Name FROM MedStaff WHERE Active=True AND Anaesthetist=True AND Outreach_Team=True AND".$Mela_SQL->sqlHUMinMax("mds_ID");
				    try { 
					$result = odbc_exec($connect,$query); 
					if($result){ 
						while ($systems = odbc_fetch_array($result)) {
						    array_push($discontByDDArray,$systems['MDS_NAME']);
						    array_push($discontByValueDD,$systems['MDS_ID']);
						}
					} 
					else{ 
					throw new RuntimeException("Failed to connect."); 
					} 
					    } 
					catch (RuntimeException $e) { 
						print("Exception caught: $e");
						//exit;
					}
			
				    $discontByDD = $Form->dropDown('discontBy',$discontByDDArray,$discontByValueDD,$modalityData['DISCONBY_PERSONID']);
				    echo $discontByDD;
				?>
			</td>
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
    	</tr>
    	<!--    #    #    #    #     #     #     #     #     #     #    -->
		<tr>
	    	<td colspan='17'>Comments &nbsp;
				<?php
                    $comments = $Form->textArea('comments',$modalityData['MOD_COMMENTS'],0,0,'','FormText_Mod');
                    echo $comments;
                ?>
	    	</td>
		</tr>
    	<!--    #    #    #    #     #     #     #     #     #     #    -->
		<tr>
	    	<td colspan='3'>Self medication</td>
	    	<td colspan='14'>
				<?php
				    $noOfTries = $Form->textBox('noOfTries',$modalityData['NOOFTRIES'],2,'','goodsTries');
				    $noOfGoods = $Form->textBox('noOfGoods',$modalityData['NOOFGOODS'],2,'','goodsTries');
		                    
				    print "No of Tries ".$noOfTries;
				    print '&nbsp'.'&nbsp'.'&nbsp';
				    print "No of Goods ".$noOfGoods;
				?>
	    	</td>
		</tr>
    	<!--    #    #    #    #     #     #     #     #     #     #    -->
    	<tr>
		    <td colspan='2'>Performed by</td>
		    <td colspan='6'>
				<?php
				    $performedByDDArray = array();
				    $performedByValueDD = array();
				    $query = "SELECT mds_ID, mds_Name FROM MedStaff WHERE Active=True AND Anaesthetist=True AND Outreach_Team=True AND".$Mela_SQL->sqlHUMinMax("mds_ID");
				    try { 
					$result = odbc_exec($connect,$query); 
					if($result){ 
						while ($systems = odbc_fetch_array($result)) {
						    array_push($performedByDDArray,$systems['MDS_NAME']);
						    array_push($performedByValueDD,$systems['MDS_ID']);
						}
					} 
					else{ 
					throw new RuntimeException("Failed to connect."); 
					} 
					    } 
					catch (RuntimeException $e) { 
						print("Exception caught: $e");
						//exit;
					}
			
				    $performedByDD = $Form->dropDown('performedBy',$performedByDDArray,$performedByValueDD,$modalityData['PERFBY_PERSONID']);
				    echo $performedByDD;
				?>
		    </td>
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~ -->
		    <td colspan='2'>Dose</td>
		    <td colspan='8'>
				<?php
				    $dose = $Form->textBox('dose',$modalityData['MOD_DOSE'],2,'','doseField');
				    
				    $doseStringDDSQL = $Mela_SQL->tbl_LoadItems('Dose Units');
				    $doseStringDDArray = array();
				    for ($i = 1; $i < (count($doseStringDDSQL)+1); $i++) {
					array_push($doseStringDDArray,$doseStringDDSQL[$i]['Long_Name']);
				    }

				    $doseStringDD = $Form->dropDown('doseString',$doseStringDDArray,$doseStringDDArray,$modalityData['DOSE_STRING'],'doseFieldUnit');
				    
				    echo $dose;
				    echo $doseStringDD;
				?>
	    	</td>
		</tr>
    	<!--    #    #    #    #     #     #     #     #     #     #    -->
    	<tr>
		    <td colspan='2'>Pump No.</td>
		    <td colspan='6'>
		    	<?php
			    	$pumpNo = $Form->textBox('pumpNo',$modalityData['PUMPNO'],3);
	                print $pumpNo;
				?>
		    </td>
		    <td colspan='10'></td>
		</tr>
	</table>


</div>

    <hr />
    <br/>

    <table class="temp modDetail">
		<tbody>
		    <tr>
		    	<td colspan='4'>Choice of LA</td>
				<td colspan='5'>
				    <?php
                        $choiceOfLADDSQL = $Mela_SQL->tbl_LoadItems('Choice of LA');
                        $choiceOfLADDArray = array();
                        for ($i = 1; $i < (count($choiceOfLADDSQL)+1); $i++) {
                            array_push($choiceOfLADDArray,$choiceOfLADDSQL[$i]['Long_Name']);
                        }

                        $choiceOfLADD = $Form->dropDown('choiceOfLA',$choiceOfLADDArray,$choiceOfLADDArray,$modalityData['MOD_CHOICEOFLA']);
                        echo $choiceOfLADD;
                    ?>
				</td>
			
				<td colspan='4'>Choice of Opiod</td>
				<td colspan='5'>
			    	<?php
	                    $choiceOfOpioidDDSQL = $Mela_SQL->tbl_LoadItems('Choice of Opioid');
	                    $choiceOfOpioidDDArray = array();
	                    for ($i = 1; $i < (count($choiceOfOpioidDDSQL)+1); $i++) {
	                        array_push($choiceOfOpioidDDArray,$choiceOfOpioidDDSQL[$i]['Long_Name']);
	                    }

	                    $choiceOfOpioidDD = $Form->dropDown('choiceOfOpioid',$choiceOfOpioidDDArray,$choiceOfOpioidDDArray,$modalityData['MOD_CHOICEOFOPIOID']);
	                    echo $choiceOfOpioidDD;
	                ?>
				</td>
			</tr>



		    <tr>
				<td colspan='4'>Choice of LA (2)</td>
				<td colspan='5'>
			    	<?php
	                    $choiceOfLA2DDSQL = $Mela_SQL->tbl_LoadItems('Choice of LA');
	                    $choiceOfLA2DDArray = array();
	                    for ($i = 1; $i < (count($choiceOfLA2DDSQL)+1); $i++) {
	                        array_push($choiceOfLA2DDArray,$choiceOfLA2DDSQL[$i]['Long_Name']);
	                    }

	                    $choiceOfLA2DD = $Form->dropDown('choiceOfLA2',$choiceOfLA2DDArray,$choiceOfLA2DDArray,$modalityData['MOD_CHOICEOFLA2']);
	                    echo $choiceOfLA2DD;
	                ?>
				</td>
			
				<td colspan='4'>Choice of Opioid (2)</td>
				<td colspan='5'>
				    <?php
	                    $choiceOfOpioid2DDSQL = $Mela_SQL->tbl_LoadItems('Choice of Opioid');
	                    $choiceOfOpioid2DDArray = array();
	                    for ($i = 1; $i < (count($choiceOfOpioid2DDSQL)+1); $i++) {
	                        array_push($choiceOfOpioid2DDArray,$choiceOfOpioid2DDSQL[$i]['Long_Name']);
	                    }

	                    $choiceOfOpioid2DD = $Form->dropDown('choiceOfOpioid2',$choiceOfOpioid2DDArray,$choiceOfOpioid2DDArray,$modalityData['MOD_CHOICEOFOPIOID2']);
	                    echo $choiceOfOpioid2DD;
		            ?>
				</td>
		    </tr>


		    <tr>
				<td colspan='4'>Type</td>
				<td colspan='5'>
				    <?php
	                    $EpiTypeDDSQL = $Mela_SQL->tbl_LoadItems('Epidural Type');
	                    $EpiTypeDDArray = array();
	                    for ($i = 1; $i < (count($EpiTypeDDSQL)+1); $i++) {
	                        array_push($EpiTypeDDArray,$EpiTypeDDSQL[$i]['Long_Name']);
	                    }

	                    $EpiTypeDD = $Form->dropDown('EpiType',$EpiTypeDDArray,$EpiTypeDDArray,$modalityData['MOD_TYPE']);
	                    echo $EpiTypeDD;
		            ?>
				</td>
			
				<td colspan='4'>Lockout</td>
				<td colspan='5'>
				    <?php
					$lockout = $Form->timeField('lockout',convert4DTime($modalityData['BACKGROUND']));
					print $lockout;
				    ?>    
				</td>
		    </tr>


		    <tr>
				<td colspan='4'>Insertion Level</td>
				<td colspan='5'>
				    <?php
	                    $insertionLevelDDSQL = $Mela_SQL->tbl_LoadItems('Insertion Level');
	                    $insertionLevelDDArray = array();
	                    for ($i = 1; $i < (count($insertionLevelDDSQL)+1); $i++) {
	                        array_push($insertionLevelDDArray,$insertionLevelDDSQL[$i]['Long_Name']);
	                    }

	                    $insertionLevelDD = $Form->dropDown('insertionLevel',$insertionLevelDDArray,$insertionLevelDDArray,$modalityData['MOD_INSERTIONLEVEL']);
	                    echo $insertionLevelDD;
	                ?>
				</td>
			
				<td colspan='4'>Position at skin</td>
				<td colspan='5'>
				    <?php
						$positionAtSkin = $Form->textBox('positionAtSkin',$modalityData['MOD_SKINEPIDISTANCE'],3);
						print $positionAtSkin." cm";
				    ?>
				</td>
		    </tr>

		    <tr>
				<td colspan='4'>First bolus dose</td>
				<td colspan='5'>
				    <?php
						$firstBolusDose = $Form->textBox('firstBolusDose',$modalityData['MOD_NUMOFBOLUSES'],3);
						print $firstBolusDose." ml";
				    ?>    
				</td>
			
				<td colspan='4'>Catheter in space</td>
				<td colspan='5'>
				    <?php
						$catheterInSpace = $Form->textBox('catheterInSpace',$modalityData['CATHETER_IN_SPACE'],3);
						print $catheterInSpace." cm";
				    ?>    
				</td>
		    </tr>

		    <tr>
				<td colspan='4'>Block level - upper</td>
				<td colspan='5'>
					L:
					    <?php
				            $blockLevelLeft1DDSQL = $Mela_SQL->tbl_LoadItems('Block Level Left');
				            $blockLevelLeft1DDArray = array();
				            for ($i = 1; $i < (count($blockLevelLeft1DDSQL)+1); $i++) {
				                array_push($blockLevelLeft1DDArray,$blockLevelLeft1DDSQL[$i]['Long_Name']);
				            }

				            $blockLevelLeft1DD = $Form->dropDown('blockLevelLeft1',$blockLevelLeft1DDArray,$blockLevelLeft1DDArray,$modalityData['MOD_BLOCKLEVELLEFT1'],'modBlockLev');
				            echo $blockLevelLeft1DD;
			            ?>
				    
				    R: 
				    	<?php
		                    $blockLevelRight1DDSQL = $Mela_SQL->tbl_LoadItems('Block Level right');
		                    $blockLevelRight1DDArray = array();
		                    for ($i = 1; $i < (count($blockLevelRight1DDSQL)+1); $i++) {
		                        array_push($blockLevelRight1DDArray,$blockLevelRight1DDSQL[$i]['Long_Name']);
		                    }

		                    $blockLevelRight1DD = $Form->dropDown('blockLevelRight1',$blockLevelRight1DDArray,$blockLevelRight1DDArray,$modalityData['MOD_BLOCKLEVELRIGHT1'],'modBlockLev');
		                    echo $blockLevelRight1DD;
		                ?>
				</td>
				
				<td colspan='4'>Patient position</td>
				<td colspan='5'>
				    <?php
		                $patientPositionDDSQL = $Mela_SQL->tbl_LoadItems('Patient Position');
		                $patientPositionDDArray = array();
		                for ($i = 1; $i < (count($patientPositionDDSQL)+1); $i++) {
		                    array_push($patientPositionDDArray,$patientPositionDDSQL[$i]['Long_Name']);
		                }

		                $patientPositionDD = $Form->dropDown('patientPosition',$patientPositionDDArray,$patientPositionDDArray,$modalityData['MOD_POSITION']);
		                echo $patientPositionDD;
		            ?>
				</td>
		    </tr>

		    <tr>
				<td colspan='4'>Block level - lower</td>
				<td colspan='5'>
			    L: 
			    	<?php
	                    $blockLevelLeft2DDSQL = $Mela_SQL->tbl_LoadItems('Block Level Left');
	                    $blockLevelLeft2DDArray = array();
	                    for ($i = 1; $i < (count($blockLevelLeft2DDSQL)+1); $i++) {
	                        array_push($blockLevelLeft2DDArray,$blockLevelLeft2DDSQL[$i]['Long_Name']);
	                    }

	                    $blockLevelLeft2DD = $Form->dropDown('blockLevelLeft2',$blockLevelLeft2DDArray,$blockLevelLeft2DDArray,$modalityData['MOD_BLOCKLEVELLEFT2'],'modBlockLev');
	                    echo $blockLevelLeft2DD;
	                   ?>
			    R: 
			    	<?php
	                    $blockLevelRight2DDSQL = $Mela_SQL->tbl_LoadItems('Block Level Right');
	                    $blockLevelRight2DDArray = array();
	                    for ($i = 1; $i < (count($blockLevelRight2DDSQL)+1); $i++) {
	                        array_push($blockLevelRight2DDArray,$blockLevelRight2DDSQL[$i]['Long_Name']);
	                    }

	                    $blockLevelRight2DD = $Form->dropDown('blockLevelRight2',$blockLevelRight2DDArray,$blockLevelRight2DDArray,$modalityData['MOD_BLOCKLEVELRIGHT2'],'modBlockLev');
	                    echo $blockLevelRight2DD;
	                ?>
				</td>
			
				<td colspan='4'>No of attempts to site</td>
				<td colspan='5'>
				    <?php
						$attemptsToSite = $Form->textBox('attemptsToSite',$modalityData['ATTEMPTS_TO_SITE']);
						print $attemptsToSite;
				    ?>    
				</td>
		    </tr>

		    <tr>
				<td colspan='4'>Density of block</td>
				<td colspan='5'>
				    <?php
	                    $densityOfBlockDDSQL = $Mela_SQL->tbl_LoadItems('Density of Block');
	                    $densityOfBlockDDArray = array();
	                    for ($i = 1; $i < (count($densityOfBlockDDSQL)+1); $i++) {
	                        array_push($densityOfBlockDDArray,$densityOfBlockDDSQL[$i]['Long_Name']);
	                    }

	                    $densityOfBlockDD = $Form->dropDown('densityOfBlock',$densityOfBlockDDArray,$densityOfBlockDDArray,$modalityData['MOD_DENSITYOFBLOCK']);
	                    echo $densityOfBlockDD;
	                ?>
				</td>
			
				<td colspan='4'>Failure to site</td>
				<td colspan='5'>
				    <?php
					$failureToSiteOptions = array(1 => ' Yes ', 2 => ' No ');
					$failureToSite = $Form->radioBox('failureToSite',$failureToSiteOptions,''.$modalityData['FAILURE_TO_SITE'].'','');
					print $failureToSite;
				    ?>    
				</td>
		    </tr>

		    <tr>
				<td colspan='4'>Bromage score</td>
				<td colspan='5'>
				    <?php
	                    $bromageScoreDDSQL = $Mela_SQL->tbl_LoadItems('Bromage Score');
	                    $bromageScoreDDArray = array();
	                    for ($i = 1; $i < (count($bromageScoreDDSQL)+1); $i++) {
	                        array_push($bromageScoreDDArray,$bromageScoreDDSQL[$i]['Long_Name']);
	                    }

	                    $bromageScoreDD = $Form->dropDown('bromageScore',$bromageScoreDDArray,$bromageScoreDDArray,$modalityData['MOD_BROMAGESCORE']);
	                    echo $bromageScoreDD;
	                ?>
				</td>
			
				<td colspan='4'>Fail to site - reason</td>
				<td colspan='5'>
				    <?php
		                        /*
					 * 'Epidural - Reasons for failure to site' Table_List_Items entry not found
					 * 
					 $reasonForFailToSiteDDSQL = $Mela_SQL->tbl_LoadItems('Epidural - Reasons for failure to site');
		                        $reasonForFailToSiteDDArray = array();
		                        for ($i = 1; $i < (count($reasonForFailToSiteDDSQL)+1); $i++) {
		                            array_push($reasonForFailToSiteDDArray,$reasonForFailToSiteDDSQL[$i]['Long_Name']);
		                        }

		                        $reasonForFailToSiteDD = $Form->dropDown('reasonForFailToSite',$reasonForFailToSiteDDArray,$reasonForFailToSiteDDArray,$modalityData['REASONFORFAILTOSITE']);
		                        echo $reasonForFailToSiteDD;*/
		                    ?>
				</td>
		    </tr>


		    <tr>
				<td colspan='4'>Aseptic precaut.</td>
				<td colspan='5'>
				    <?php
	                    $asepticPrecautDDSQL = $Mela_SQL->tbl_LoadItems('Epidural - Aseptic precaution');
	                    $asepticPrecautDDArray = array();
	                    for ($i = 1; $i < (count($asepticPrecautDDSQL)+1); $i++) {
	                        array_push($asepticPrecautDDArray,$asepticPrecautDDSQL[$i]['Long_Name']);
	                    }

	                    $asepticPrecautDD = $Form->dropDown('asepticPrecaut',$asepticPrecautDDArray,$asepticPrecautDDArray,$modalityData['ASEPTICPRECAUTIONS']);
	                    echo $asepticPrecautDD;
	                ?>
				</td>
			
				<td colspan='4'>Skin preparation</td>
				<td colspan='5'>
				    <?php
						$skinPreparationOptions = array(1 => ' Yes ', 2 => ' No ');
						$skinPreparation = $Form->radioBox('skinPreparation',$skinPreparationOptions,''.$modalityData['SKINPREP'].'','');
						print $skinPreparation;
				    ?>    
				</td>
		    </tr>


		    <tr>
				<td colspan='4'>LOR</td>
				<td colspan='5'>
				    <?php
	                    $LORDDSQL = $Mela_SQL->tbl_LoadItems('Epidural - LOR');
	                    $LORDDArray = array();
	                    for ($i = 1; $i < (count($LORDDSQL)+1); $i++) {
	                        array_push($LORDDArray,$LORDDSQL[$i]['Long_Name']);
	                    }

	                    $LORDD = $Form->dropDown('LOR',$LORDDArray,$LORDDArray,$modalityData['LOR']);
	                    echo $LORDD;
	                ?>
				</td>
			
				<td colspan='4'>Test dose</td>
				<td colspan='5'>
				    <?php
						$testDose = $Form->textBox('testDose',$modalityData['EPITESTDOSE']);
						print $testDose;
				    ?>    
				</td>
		    </tr>

		    <tr>
				<td colspan='4'>Initial dose</td>
				<td colspan='5'>
				    <?php
						$initialDose = $Form->textBox('initialDose',$modalityData['EPIINITDOSE']);
						print $initialDose;
				    ?>    
				</td>
			
				<td colspan='4'>Maintenance dose</td>
				<td colspan='5'>
				    <?php
						$maintenanceDose = $Form->textBox('maintenanceDose',$modalityData['EPIMAINTAINDOSE']);
						print $maintenanceDose;
				    ?>
				</td>
		    </tr>


		    <tr>
				<td colspan='4'>Hourly limit</td>
				<td colspan='5'>
				    <?php
						$hourlyLimit = $Form->textBox('hourlyLimit',$modalityData['EPI2HR_LIMIT'],2);
						print $hourlyLimit;
				    ?>    
				</td>
			
				<td colspan='4'>Dose & unit</td>
				<td colspan='5'>
				    <?php
					$hourlyDose = $Form->textBox('hourlyDose',$modalityData['EPI2HR_DOSE'],2,'','doseField');
					
					$epiUnitDDSQL = $Mela_SQL->tbl_LoadItems('Dose Units');
		                        $epiUnitDDArray = array();
		                        for ($i = 1; $i < (count($epiUnitDDSQL)+1); $i++) {
		                            array_push($epiUnitDDArray,$epiUnitDDSQL[$i]['Long_Name']);
		                        }

		                        $epiUnitDD = $Form->dropDown('epiUnit',$epiUnitDDArray,$epiUnitDDArray,$modalityData['EPI2HR_UNIT'],'doseFieldUnit');
					
					print $hourlyDose;
					echo $epiUnitDD;
					
				    ?>    
				</td>
		    </tr>


		    <tr>
				<td colspan='4'>Rate</td>
				<td colspan='5'>
				    <?php
						$rate = $Form->textBox('rate',$modalityData['MOD_RATE'],2);
						print $rate;
				    ?>
				</td>
			
				<td colspan='4'>Site check</td>
				<td colspan='5'>
				    <?php
		                $siteCheckDDSQL = $Mela_SQL->tbl_LoadItems('Site Check');
		                $siteCheckDDArray = array();
		                for ($i = 1; $i < (count($siteCheckDDSQL)+1); $i++) {
		                    array_push($siteCheckDDArray,$siteCheckDDSQL[$i]['Long_Name']);
		                }

		                $siteCheckDD = $Form->dropDown('siteCheck',$siteCheckDDArray,$siteCheckDDArray,$modalityData['MOD_SITECHECK']);
		                echo $siteCheckDD;
		            ?>
				</td>
		    </tr>



		    <tr>
				<td colspan='4'>Frequency</td>
				<td colspan='5'>
				    <?php
		                $frequencyDDSQL = $Mela_SQL->tbl_LoadItems('Frequency');
		                $frequencyDDArray = array();
		                for ($i = 1; $i < (count($frequencyDDSQL)+1); $i++) {
		                    array_push($frequencyDDArray,$frequencyDDSQL[$i]['Long_Name']);
		                }

		                $frequencyDD = $Form->dropDown('frequency',$frequencyDDArray,$frequencyDDArray,$modalityData['MOD_FREQUENCY']);
		                echo $frequencyDD;
		            ?>
				</td>
			
				<td colspan='4'>Backg. infusion</td>
				<td colspan='5'>
				    <?php
						$backgInfusion = $Form->textBox('backgInfusion',$modalityData['MOD_BACKGROUND_VAL'],2,'','doseField');

		                $backgroundUnitDDSQL = $Mela_SQL->tbl_LoadItems('Dose Units');
		                $backgroundUnitDDArray = array();
		                for ($i = 1; $i < (count($backgroundUnitDDSQL)+1); $i++) {
		                    array_push($backgroundUnitDDArray,$backgroundUnitDDSQL[$i]['Long_Name']);
		                }

		                $backgroundUnitDD = $Form->dropDown('backgroundUnit',$backgroundUnitDDArray,$backgroundUnitDDArray,$modalityData['MOD_BACKGROUND_UNIT'],'doseFieldUnit');
			
						print $backgInfusion;
		                echo $backgroundUnitDD;
				    ?>    
				</td>
		    </tr>



		    <tr>
				<td colspan='4'>Total infusion</td>
				<td colspan='5'>
				    <?php
						$totalInfusion = $Form->textBox('totalInfusion',$modalityData['MOD_INFUSION']);
						print $totalInfusion;
				    ?>    
				</td>
			
				<td colspan='9'>&nbsp;</td>
		    </tr>


		</tbody>
	</table>

    <br/>


    
    <?php
    // Now begins specific form fields for certain items
    switch ($modalityData['MOD_ID']) {
    }?>  





    </form>
</div>



</div>

<?php
}
?>

