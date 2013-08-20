<?php 
$surgeryLNKID = filter_var($_REQUEST['lnkID'], FILTER_SANITIZE_NUMBER_INT);
?>
<td>
    <table width="960px">
        <tr><td colspan='100' class='linebreak_top'>Search for procedure type</td></tr>
        <tr>
            <td>

    <!--<input type="text" name="search"> <input type="submit" value="Search">-->
    <div class="textBox">  
        <!-- <input type="search" value="" maxlength="500" name="searchBox" id="search" data-lnkid="<?php echo $lnkID; ?>"> -->
        <input type="search" value="" name="searchBox" id="search" data-lnkid="<?php echo $lnkID; ?>">
            
        <button type="button" class="searchBtn" data-lnkid='<?php echo $lnkID; ?>' data-dlkid=''>  
            Search  
        </button>  
    </div> 
    
    
    <div class="top" style="float:left; width:100%; display: inline-block; text-align: center;">

            <div id="content">
                <div id="preresults">
                    Please use the search panel above to add a new procedure
                </div>
                <div class="search-background">  
                    Loading...
                    <label>
                        <img src="media/images/loader.gif" alt="" />
                    </label>  
                </div>	
                <div id="sub_cont" class='OPCStop'></div>  
            </div>   
    </div>
                </td>
        </tr>
        <?php //echo "<iframe id='surgeryIframe' name='surgeryIframe' width='100%' src='surgeryframe.php?lnkID=".$lnkID."' frameborder='0' style='min-height:180px;'></iframe>"; ?>
        <tr style='line-height:8px;'><td>&nbsp;</td></tr>
        <tr><td colspan='100' class='linebreak_top'>Recorded procedures</td></tr>
        <tr>
            <td>
                <table class="OPERTable temp" id="OPER">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Description</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>            
                        <?php

                            $opr_query="SELECT po.OPER_lnkID, po.OPER_ID, oc.Oper_Code as OPERCODE, oc.Oper_Title, oc.Oper_Name
                            FROM OPER_PatOperations po
                            LEFT OUTER JOIN OPER_Codes oc ON po.OPER_ID = oc.Oper_ID
                            LEFT OUTER JOIN LINK l ON po.OPER_lnkID = l.lnk_ID
                            WHERE po.OPER_lnkID = $surgeryLNKID";


                            try { 
                                $result = odbc_exec($connect,$opr_query); 
                                if($result){ 
                                    while ($existingRows = odbc_fetch_array($result)) {
                                    print "<tr>
                                        <td class='OPERCode'>".$existingRows['OPERCODE']."</td>
                                        <td class='OPERName'>".$existingRows['OPER_NAME']."</td>
                                        <td id='Button_cell'><button id='".$existingRows['OPER_ID']."' type='button' class='editRow' data-page='OPCSSurgery'><img src='Media/img/pencil.gif' alt='Edit'/></button></td>
                                        <td id='Button_cell'><button id='".$existingRows['OPER_ID']."' type='button' class='deleteRow' data-page='OPCS'><img src='Media/img/bin.gif' alt='Delete'/></button></td>
                                    </tr>";
                                    }
                                }     
                            else { 
                                throw new RuntimeException("Failed to connect."); 
                            } 
                                } 
                                catch (RuntimeException $e) { 
                                    print("Exception caught: $e");
                                }   
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

</td>

