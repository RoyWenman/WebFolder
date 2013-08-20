<script src="media/js/jquery-1.10.0.min.js"></script>
<script src="media/js/closeEditPages.js"></script>
<script type="text/javascript">    
    $(document).ready(function() {
        function resetField(field) {
		$(field).val('');
	}
            
        $('.resetButton').click(function() {
                    var data = $(this).data();
                    var target = data['target'];
                    resetField(target);
        });
    });
</script>
<?php
/*
 * Edit page for OPCS surgery
 */ 




error_reporting(E_ALL ^ E_NOTICE);
include './MelaClass/functions.php';
include './MelaClass/Mela_Forms.php';
include './MelaClass/db.php';
include './MelaClass/authInitScript.php';

// Need post data for this
//if (!$_POST || !$_REQUEST) die("No operation data specified");

if (!$_POST) {
$Form = new Mela_Forms('surgeryEdit','','POST','surgeryEdit_form');

        $operationID = filter_var($_REQUEST['row'], FILTER_SANITIZE_NUMBER_INT);
        $lnkID = filter_var($_REQUEST['lnk'], FILTER_SANITIZE_NUMBER_INT);
        $opr_query="SELECT Oper_Code, OPER_Date, OPER_Comments, OPER_Classification,
		    Anea1, Anea2, Type, IncisionType, Technique, Outcome
		    FROM OPER_PatOperations
		    WHERE OPER_ID=$operationID AND OPER_lnkID=$lnkID";
                    try { 
                          $result = odbc_exec($connect,$opr_query); 
                    if($result){ 
                            $operationData = odbc_fetch_array($result);
                    } 
                    else{ 
                    throw new RuntimeException("Failed to connect."); 
                    } 
                        } 
                    catch (RuntimeException $e) { 
                            print("Exception caught: $e");
                            //exit;
                    }
        
        ?>
        <form name="editSurgery" action="?action=edit" method="POST">
        <input type="hidden" name="lnkID" value="<?php echo $lnkID; ?>">
        <input type="hidden" name="operationID" value="<?php echo $operationID; ?>">
            <fieldset style="width:95%;">
                        <legend>
                            Selected record
                        </legend>
                        
                        <table class="temp" cellpadding="5">
                            <tr>
                                <td width="25%">
                                    Anaesthetist 1
                                </td>
                                <td width="25%">
                                    <?php
                                        $anaesthetist1 = $Mela_SQL->getAnaesthetistDropdown('anaesthetist1','',$operationData['ANEA1']);
                                        echo $anaesthetist1;
                                    ?>
                                    <button type='button' class='resetButton' data-target='#anaesthetist1'>Reset</button>
                                </td>
                                <td width="25%">
                                    Anaesthetist 2
                                </td>
                                <td width="20%">
                                    <?php
                                        $anaesthetist2 = $Mela_SQL->getAnaesthetistDropdown('anaesthetist2','',$operationData['ANEA2']);
                                        echo $anaesthetist2;
                                    ?>
                                    <button type='button' class='resetButton' data-target='#anaesthetist2'>Reset</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Date
                                </td>
                                <td>
                                    <input type="date" class="FormField" name="date" size="6" value="<?php echo stringToDateTime($operationData['OPER_DATE'],2); ?>">
                                </td>
                                <td>
                                    Classification
                                </td>
                                <td>
                                    <?php
                                        $srgClassificationDDSQL = $Mela_SQL->tbl_LoadItems('Surgery Classification');
                                        $srgClassificationDDArray = array();
                                        for ($i = 1; $i < (count($srgClassificationDDSQL)+1); $i++) {
                                            array_push($srgClassificationDDArray,$srgClassificationDDSQL[$i]['Long_Name']);
                                        }
                            
                                        $srgClassificationDD = $Form->dropDown('classification',$srgClassificationDDArray,$srgClassificationDDArray,$operationData['OPER_CLASSIFICATION']);
                                        echo $srgClassificationDD;                           
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Incision site
                                </td>
                                <td>
                                    <?php
                                        $srgIncisionDDSQL = $Mela_SQL->tbl_LoadItems('Incision Site');
                                        $srgIncisionDDArray = array();
                                        for ($i = 1; $i < (count($srgIncisionDDSQL)+1); $i++) {
                                            array_push($srgIncisionDDArray,$srgIncisionDDSQL[$i]['Long_Name']);
                                        }
                            
                                        $srgIncisionDD = $Form->dropDown('incisionSite',$srgIncisionDDArray,$srgIncisionDDArray,$operationData['INCISIONTYPE']);
                                        echo $srgIncisionDD;                         
                                    ?>
                                </td>
                                <td>
                                    Type of Surgery
                                </td>
                                <td>
                                    <?php
                                        $srgIncisionDDSQL = $Mela_SQL->tbl_LoadItems('Type Of Surgery');
                                        $srgIncisionDDArray = array();
                                        for ($i = 1; $i < (count($srgIncisionDDSQL)+1); $i++) {
                                            array_push($srgIncisionDDArray,$srgIncisionDDSQL[$i]['Long_Name']);
                                        }
                            
                                        $srgIncisionDD = $Form->dropDown('typeOfSurgery',$srgIncisionDDArray,$srgIncisionDDArray,$operationData['TYPE']);
                                        echo $srgIncisionDD;                         
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Outcome
                                </td>
                                <td>
                                    <?php
                                        $srgOutcomeDDSQL = $Mela_SQL->tbl_LoadItems('Surgery Outcome');
                                        $srgOutcomeDDArray = array();
                                        for ($i = 1; $i < (count($srgOutcomeDDSQL)+1); $i++) {
                                            array_push($srgOutcomeDDArray,$srgOutcomeDDSQL[$i]['Long_Name']);
                                        }
                            
                                        $srgOutcomeDD = $Form->dropDown('outcome',$srgOutcomeDDArray,$srgOutcomeDDArray,$operationData['OUTCOME']);
                                        echo $srgOutcomeDD;                             
                                    ?>
                                </td>
                                <td>
                                    Technique
                                </td>
                                <td>
                                    <?php
                                        $srgTechniqueDDSQL = $Mela_SQL->tbl_LoadItems('Technique');
                                        $srgTechniqueDDArray = array();
                                        for ($i = 1; $i < (count($srgTechniqueDDSQL)+1); $i++) {
                                            array_push($srgTechniqueDDArray,$srgTechniqueDDSQL[$i]['Long_Name']);
                                        }
                            
                                        $srgTechniqueDD = $Form->dropDown('technique',$srgTechniqueDDArray,$srgTechniqueDDArray,$operationData['TECHNIQUE']);
                                        echo $srgTechniqueDD;                             
                                    ?>
                                </td>
                            </tr>
                        </table>
                        
                    <b>Surgery notes</b><br />
                    <textarea class="FormBlock" name="surgeryNotes" rows="10" cols="50"><?php echo $operationData['OPER_COMMENTS']; ?></textarea>
                    <br />
                    <input type="submit" value="Save" onClick="return CloseAndRefresh()">
                    <a href="surgeryframe.php?lnkID=<?php echo $lnkID; ?>"><button type="button">Go back</button></a>
            </fieldset>
        </form>
        <?php
    } else {
    
    $ana1 = $_POST['anaesthetist1'];
    $ana2 = $_POST['anaesthetist2'];
    $date = dateToString($_POST['date']);
    $classification = $_POST['classification'];
    $incision = $_POST['incisionSite'];
    $surgerytype = $_POST['typeOfSurgery'];
    $outcome = $_POST['outcome'];
    $technique = $_POST['technique'];
    $notes = $_POST['surgeryNotes'];
    $operationID = $_POST['operationID'];
    $lnkID = $_POST['lnkID'];
    
    //print_r($_POST);        
    
    $sql = "UPDATE OPER_PatOperations SET Anea1=$ana1, Anea2=$ana2, OPER_Date='$date', OPER_Classification='$classification', IncisionType='$incision', Type='$surgerytype', Outcome='$outcome', Technique='$technique', OPER_Comments='$notes' WHERE OPER_ID=$operationID AND OPER_lnkID=$lnkID";
    //$sql = "UPDATE OPER_PatOperations SET IncisionType='1' WHERE OPER_ID=$operationID";
    try { 
        $result = odbc_exec($connect,$sql);
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
    }
?>