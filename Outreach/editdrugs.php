<script src="media/js/jquery-1.10.0.min.js"></script>
<script src="media/js/closeEditPages.js"></script>
<?php

include './MelaClass/Mela_Forms.php';
include './MelaClass/db.php';
include './MelaClass/authInitScript.php';

$Form = new Mela_Forms('editDrugs','','POST','drugs_form');

if (!$_REQUEST['dlk'] || !$_REQUEST['lnk'] || !$_REQUEST['row']) die("Vital information missing");

$dlk = filter_var($_REQUEST['dlk'], FILTER_SANITIZE_NUMBER_INT);
$lnk = filter_var($_REQUEST['lnk'], FILTER_SANITIZE_NUMBER_INT);
$row = filter_var($_REQUEST['row'], FILTER_SANITIZE_NUMBER_INT);

$query = "SELECT ia.Antibiotics_Dose, itm.Description
          FROM Infection_Antibiotics ia
          LEFT OUTER JOIN Items_antibiotics itm ON ia.ID = itm.ID
          WHERE ia.ID=$row AND ia.antibiotics_dlkID=$dlk AND ia.antibiotics_lnkID=$lnk AND ia.ID=itm.ID";
    try { 
        $result = odbc_exec($connect,$query); 
        if($result){ 
               $drug = odbc_fetch_array($result);
        } 
        else{ 
        throw new RuntimeException("Failed to connect."); 
        } 
            } 
    catch (RuntimeException $e) { 
        print("Exception caught: $e");
    }
        
$hiddenRow = $Form->hiddenField('row',$row);
$hiddenDLK = $Form->hiddenField('dlk',$dlk);
$hiddenLNK = $Form->hiddenField('lnk',$lnk);

echo $hiddenRow;
echo $hiddenDLK;
echo $hiddenLNK;

if (!$_POST) {
    
    print "<table class='temp'>
           <tbody>
            <tr>
                <td>
                    Description
                </td>
                <td>";
                    $description = $Form->textBox('drugDescription',$drug['DESCRIPTION'],'',1);
                    print $description;
                print "</td>
            </tr>
            <tr>
                <td>
                Dose
                </td>
                <td>";
                    $dose = $Form->textBox('drugDose',$drug['ANTIBIOTICS_DOSE']);
                    print $dose;
                print "</td>
            </tr>
            <tr>
                <td colspan='2'>";
                $submit = $Form->submitButton('Submit');
                echo $submit;
                print "</td>
            </tr>
           </tbody>
           </table>";
} else {
    $dose = (is_numeric($_POST['drugDose'])) ? filter_var($_POST['drugDose'], FILTER_SANITIZE_NUMBER_INT) : filter_var($_POST['drugDose'], FILTER_SANITIZE_STRING);
    $rowID = $_POST['row'];
    $dlkID = $_POST['dlk'];
    $lnkID = $_POST['lnk'];
    
    // Need to check that the record is properly locked first of all
    if ($Mela_SQL->Exec4DSQL("SQLLock_IsLocked", $lnkID) == 1) {    
        $query = "UPDATE Infection_Antibiotics SET Antibiotics_Dose='$dose' WHERE ID=$rowID AND antibiotics_dlkID=$dlkID AND antibiotics_lnkID=$lnkID";
        try { 
            $result = odbc_exec($connect,$query); 
            if($result){ 
                ?>
                <script type="text/javascript">
                    CloseAndRefresh();
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
}