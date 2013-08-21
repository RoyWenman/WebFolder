<script src="media/js/jquery.tablesorter.min.js"></script>
<script src="media/js/jquery.ui.labeledslider.js"></script>
<script src="media/js/jsDate.js"></script>
<script src="media/js/jquery.validate.min.js"></script>
<script src="media/js/jquery-impromptu.js"></script>

<script type="text/javascript">
	$(document).ready(function() 
	{
	    // Prevent enter from submitting the form
	    $(window).keypress(function(event){
		    var keycode = (event.keyCode ? event.keyCode : event.which);
		    if(keycode == '13') {
			    event.preventDefault();
		    }
	    });		
	    
	    // Prevent double clicking because it seems to mess up database connection
	    /*$(window).dblclick(function(event) {
		$.prompt("Please don't double click");
		event.preventDefault;
		event.clearQueue();
		event.stop();
		event.stopPropagation();
	    });*/
	    /*
	     * Initialisation
	     */ 
	    
	    $("button").button(); // What is this for?
	    $("#tabs").tabs();
	    $('#MEWSTrig').hide();
	    $( "form" ).sisyphus({
		locationBased: true
	    });
	    var activeMedicationIndex = 0;
	    $( ".medicationAccordion" ).accordion({
		active: 0,
		autoHeight: true,
		heightStyle: 'content',
		activate: function (event, ui) {
		    var activeIndex = $(".medicationAccordion").accordion("option", "active");
		    window.activeMedicationIndex = activeIndex;
		}
	    });
	    var clicks = 0;
	    updateDOB();
	    changeConsultant($('#adm-consultant'),$('#adm-speciality'),$('#adm-consultant').val());
	    
	    /*
	     * End initialisation
	     */ 
	    
	    /*
	     * Begin functions and other generic code
	     */
	    
	    /*
	     * Form Validation rules and stuff
	     */
		
	    /*
	     * NHS Number Validation
	     */
	    $.validator.addMethod("validateNHSNumber", function(value, element) {
		// Taken from https://github.com/pfwd/NHSNumber-Validation
		var multipliers = {1:10, 2:9, 3:8, 4:7, 5:6, 6:5, 7:4, 8:3, 9:2};
		var currentSum, currentNumber, currentMultiplier;
		currentSum =  currentNumber = currentMultiplier = 0;
		
		//Get submitted NHS Number and remove whitespace
		var givenNumber = value.replace(/ /g,'');
		
		// Get length
		var numberLength = givenNumber.length;
		
		// Number must be 10 digits in length
		if (numberLength !== 10) {
			return false;
		}
		
		// Check number
		var checkNumber = givenNumber.substring(9);
		
		// Loop over each number in the string and calculate the current sum
		for (var i = 0; i <= 8; i++) {
			var minus = i-1;
			var plus = i+1;
			currentNumber = givenNumber.charAt(i);
			currentMultiplier = multipliers[plus];
			currentSum += currentNumber * currentMultiplier;
		}
		
		var remainder = currentSum % 11;
		var total = 11 - remainder;

		if (total == 11) {
		    total = 0;
		}
		
		if (total == 10) {
		    return false;
		}
		
		if (total == checkNumber) {
		    return true;
		} else return false;			
		
		return false;
		}, "NHS Number provided failed validation");
		
	    $.validator.addMethod("canBePregnant", function(value, element) {
		// Men have no womb and therefore cannot be pregnant
		var gender = $('#dmg-sex').val();
		
		// Are they claiming to be pregnant? 'undefined' = no, 'dmg-pregnant' = yes
		if (typeof value !== 'undefined') {
			if (gender === 'Female') {
				return true;
			} else return false;
		} else return true;
		}, "Males cannot be pregnant");
	    
	    $.validator.addMethod("bornBeforeToday", function(value, element) {
		// For DOB, check that value given is before the current date
		var myDate = value;
		return Date.parse(myDate) < new Date();
		}, "Date must be less than than current date");
	    
	    $.validator.addMethod("noFutureDates", function(value, element) {
		// Check that the date given is not in the future
		var myDate = value;
		return Date.parse(myDate) >= new Date();
		}, "Date cannot be in the future");
	    
	    $.validator.addMethod("greaterThan", 
	       function(value, element, params) {
	       
		   if (!/Invalid|NaN/.test(new Date(value))) {
		       return new Date(value) > new Date($(params).val());
		   }
	       
		   return isNaN(value) && isNaN($(params).val()) 
		       || (Number(value) > Number($(params).val())); 
	       },'Must be greater than {0}.');
	    
	    /*
	     * These are all physiology1A range values
	     */
	    $.validator.addMethod("temperature", function(value, element) {
		return value >= 22.5 && value <= 42;
	    }, "Temperature must be between 22.5 and 42");
	    
	    $.validator.addMethod("systolicBP", function(value, element) {
		return value >= 24 && value <= 286;
	    }, "Systolic BP must be between 24 and 286");
	    
	    $.validator.addMethod("diastolicBP", function(value, element) {
		return value >= 15 && value <= 158;
	    }, "Diastolic BP must be between 15 and 158");
	    
	    $.validator.addMethod("meanBP", function(value, element) {
		return value >= 1 && value <= 286;
	    }, "Mean BP must be between 1 and 286");
	    
	    $.validator.addMethod("heartRate", function(value, element) {
		return value >= 14 && value <= 220;
	    }, "Heart Rate must be between 14 and 220");
	    
	    $.validator.addMethod("respRate", function(value, element) {
		return value >= 2 && value <= 68;
	    }, "Resp Rate must be between 2 and 68");
	    
	    $.validator.addMethod("SpO2", function(value, element) {
		return value >= 94 && value <= 100;
	    }, "SpO2 must be between 94 and 100");
	    
	    $.validator.addMethod("O2", function(value, element) {
		return value >= 85 && value <= 100;
	    }, "O2 must be between 85 and 100");
	    
	    $.validator.addMethod("urine", function(value, element) {
		return value >= 0 && value <= 10804;
	    }, "Urine must be between 0 and 10804");
	    
	    $.validator.addMethod("PaO2", function(value, element) {
		return value >= 85 && value <= 100;
	    }, "PaO2 must be between 85 and 100");
	    
	    $.validator.addMethod("FIO", function(value, element) {
		return value >= 0.21 && value <= 1;
	    }, "FIO must be between 0.21 and 1");
	    
	    $.validator.setDefaults({
		ignore: ""
	    });
	    
	    var submitted = false;
	    
	    $("#save_form").validate({
		errorLabelContainer: ".validationErrorBox",
		wrapper: "li",
		showErrors: function(errorMap, errorList) {
		    if (submitted) {
			if (errorList) {
			    var summary = "Form errors: \n";
			    //$.each(errorList, function() { summary += " * " + this.message + "\n"; });
			    //$(".validationErrorBox").show().text(summary);
			    //$.each(errorList, function() { summary +="\n"; });
			    //$(".validationErrorBox").show().text(summary);
			    submitted = false;	
			} else {
			    $(".validationErrorBox").hide().val('');    
			}
			
		    }
		    this.defaultShowErrors();
		},          
		invalidHandler: function(form, validator) {
		    var submitted = true;
		},
		rules: {
		    "dmg-NHSNumber": "validateNHSNumber",	
		    "dmg-pregnant" : "canBePregnant",
		    "dmg-DOB": "bornBeforeToday",
		    "adm-outreachNumber": "required",
		    "newassDate": "noFutureDates"
		    /*"phys-temperature": "temperature",
		    "phys-systolicBP": "systolicBP",
		    "phys-diastolicBP": "diastolicBP",
		    "phys-meanBP": "meanBP",
		    "phys-heartRate1": "heartRate",
		    "phys-respRate": "respRate",
		    "phys-SpO2": "SpO2",
		    "phys-O2": "O2",
		    "phys-UrineQuant": "urine",
		    "phys-heartRate": "PaO2",
		    "phys-AssociatedFIO2": "FIO"*/
		},
		 messages: {
		    "dmg-NHSNumber": "Demographics - Invalid NHS Number",
		    "dmg-pregnant" : "Demographics - Males cannot be pregnant",
		    "dmg-DOB": "Demographics - Invalid DOB value given",
		    "adm-outreachNumber": "Admission - Missing ID/Outreach Number",
		    "newassDate": "New Assessment - Date cannot be in the future",
		    "phys-temperature": "Physiology 1A - Temperature is outside of acceptable value range",
		    "phys-systolicBP": "Physiology 1A - Systolic BP is outside of acceptable value range",
		    "phys-diastolicBP": "Physiology 1A - Diastolic BP is outside of acceptable value range",
		    "phys-meanBP": "Physiology 1A - Mean BP is outside of acceptable value range",
		    "phys-heartRate1": "Physiology 1A - Heart Rate is outside of acceptable value range",
		    "phys-respRate": "Physiology 1A - Respiratory Rate is outside of acceptable value range",
		    "phys-SpO2": "Physiology 1A - SpO2 is outside of acceptable value range",
		    "phys-O2": "Physiology 1A - O2 is outside of acceptable value range",
		    "phys-UrineQuant": "Physiology 1A - Urine is outside of acceptable value range",
		    "phys-heartRate": "Physiology 1A - PaO2 is outside of acceptable value range",
		    "phys-AssociatedFIO2": "Physiology 1A - FIO is outside of acceptable value range"
		},
		highlight: function(element) {
		    $(element).closest('.control-group').removeClass('success').addClass('error');
		},
		success: function(element) {
		    $(element).removeClass('error');
		    $(element).remove();
		}
	    });
	    
	    /*
	    * End form validation
	    */ 
	    
	    function resetForm(id) {
		$('#'+id).each(function(){
			this.reset();
		});
	    }
	    
	    $('tbody tr[data-href] td:not(:last-child)').click( function(e) { 
			window.location = $(this).attr('data-href');
	    });
	    
	    function popitup(url,windowName, data, height, width) {
			// Height and width are optional parameters, if not defined default values are used
			var windowHeight = 450;
			var windowWidth = 575;
			if (arguments.length === 5) {
			    windowHeight = height;
			    windowWidth = width;
			}
			
			// Data can contain additional info to get tacked on to the end of the URL
			if (typeof data === "undefined") {
			    newwindow=window.open(url,windowName,'height='+windowHeight+',width='+windowWidth);
			} else {
			    var fullURL = url + data;
			    newwindow=window.open(fullURL,windowName,'height='+windowHeight+',width='+windowWidth);
			}			
			if (window.focus) {newwindow.focus()}
			return false;
	    }
	    
	    function changeConsultant(selector,target,consultant) {
		var parentField = $(selector);
		var targetField = $(target);
		var speciality = targetField.load("changeConsultantSpeciality.php?consultant=" + consultant);
		targetField.val(speciality);
	    }
	    
	    function resetField(field) {
		// This is mainly intended for resetting the dropdown
		// lists in medicalstaff.php
		$(field).val('');
	    }
	    
	    function changeTab(tabIndex) {
		$("#tabs").tabs( "option", "active", tabIndex );
	    }
	    
	    function getBMI(height,weight) {
		if ((height > 0) && (weight > 0)) {
		    var BMI = Math.round(weight/(height*height));
		    return BMI;
		} else return 0;
	    }
	    
	    function getAge(dateString, dateString2) {
		var now = new Date(dateString2);
		var today = new Date(now.getYear(),now.getMonth(),now.getDate());
	      
		var yearNow = now.getYear();
		var monthNow = now.getMonth();
		var dateNow = now.getDate();
	      
		var dob = new Date(dateString);		
	      
		var yearDob = dob.getYear();
		var monthDob = dob.getMonth();
		var dateDob = dob.getDate();
		var age = {};	      
	      
		yearAge = yearNow - yearDob;
	      
		if (monthNow >= monthDob)
		  var monthAge = monthNow - monthDob;
		else {
		  yearAge--;
		  var monthAge = 12 + monthNow -monthDob;
		}
	      
		if (dateNow >= dateDob)
		  var dateAge = dateNow - dateDob;
		else {
		  monthAge--;
		  var dateAge = 31 + dateNow - dateDob;
	      
		  if (monthAge < 0) {
		    monthAge = 11;
		    yearAge--;
		  }
		}
	      
		age = {
		    years: yearAge,
		    months: monthAge,
		    days: dateAge
		    };
	      
		return [age.years,age.months,age.days];
	    }
	    
	    /*
	     * Physiology value checking
	     */
	    function checkPhysiologyValue(dlkID, fieldCode, fieldType, physiologyValue, fieldLabel) {
		/*
		 * @param {int} dlkID - Daily Link ID
		 * @param {string} fieldCode - Identifies which field is being checked (look at form structure in 4D. Method pat_Validate_Value is called with 3 args, this is 3rd arg)
		 * @param {string} fieldType - 3 possibilites: 'T' for Physiology1A, 'L' or 'H' for physiology 2 referring to highest/lowest
		 * @param {float} physiologyValue - The actual value of the field being checked
		 * @param {string} fieldLabel - The name of the field being checked
		 * @returns {string} String is regexp checked for a question mark. If so, present yes/no option else just display warning
		 */
		$.ajax({
		    type: "POST",
		    url: "checkPhysiology.php",
		    data: "dlkid=" + dlkID + "&code=" + fieldCode + "&type=" + fieldType + "&value=" + physiologyValue + "&label=" + fieldLabel,
		    async: false,
		    success: function(msg){
		    	if (msg.length > 0) {
			    // Simplest way to check if it's returning a question is to check
			    // if the last character is a question mark, like so: str.slice(-1);
			    if (msg.slice(-1) === '?') {
				// Need to provide yes/no option
				$.prompt(msg, {
				    buttons: { "OK": true, "Cancel": false }
				});		
			    } else {
				$.prompt(msg);
			    }
			}			
		    },
		    error: function(XMLHttpRequest, textStatus, errorThrown) {
			 rowID = 'Invalid';
			 alert(" Status: " + textStatus + "\n Error message: "+ errorThrown); 
		    } 
		});
	    
	    }
	    
	    /*
	     * End physiology value checking
	     */ 
	    
	    /*
	     * End functions and generic code
	     */
	    
	    /*
	     * Physiology value checking
	     */
	    
	    $('.checkPhysiology').change(function() {
		var dlkID = $('#patDLKID').val();
		var data = $(this).data();
		var code = data['code'];
		var type = data['type'];
		var label = data['label'];
		var that = $(this).val();
		checkPhysiologyValue(dlkID, code, type, that, label);
	    });
	    
	    /*
	     * End physiology value checking
	     */ 
	    
	    $('#patientSeen').click(function () {
		var data = $(this).data();
		var lnkid = data['lnkid'];
		var user = data['user'];
		$.ajax({
		    type: "POST",
		    url: "SQL_TriggSetSeen.php",
		    data: "lnkid=" + lnkid + "&user=" + user,
		    async: false,
		    success: function(msg){
			$('#patientSeen').hide();
		    },
		    error: function(XMLHttpRequest, textStatus, errorThrown) {
			 rowID = 'Invalid';
			 alert(" Status: " + textStatus + "\n Error message: "+ errorThrown); 
		    } 
		});	
	    });
	    
	    
	    /*
	     * Begin page/element specific code
	     */
	    
	    /*
	     * SOFA/NEWS/EWSS button popups
	     */
	    $('#SOFAScore').click(function() {
		var data = $(this).data();
		var dlkpatid = data['dlkpatid'];
		var dlkid = data['dlkid'];
		var query = "?dlkpatid="+dlkpatid+"&dlkid="+dlkid;
		popitup('SOFA.php','SOFA',query,625,725);
	    });
	    
	    $('#EWSScore').click(function() {
		var data = $(this).data();
		var dlkpatid = data['dlkpatid'];
		var lnkid = data['lnkid'];
		var query = "?dlk_patID="+dlkpatid+"&dlk_MEWSID=1&lnkid=" + lnkid;
		popitup('MEWSPopup.php','MEWS',query,550,1025);
	    });
	    
	    $('#NEWSScore, #admNEWSScore').click(function() {
		var data = $(this).data();
		var dlkid = data['dlkid'];
		var dlkpatid = data['dlkpatid'];
		var lnkid = data['lnkid'];
		var query = "?dlkid="+dlkid+"&dlk_patID="+dlkpatid+"&lnkid=" + lnkid;
		popitup('NEWSPopup.php','NEWS',query,550,1025);
	    });
	    
	    $('#firstTrigger').click(function() {
		var data = $(this).data();
		var lnkid = data['lnkid'];
		var query = "?lnkid="+lnkid;
		popitup('firstTrigger.php','First Trigger',query,565,1025);
	    });
	    
	    $('.calculateNEWS').change(function() {
		var ID = $('#patDLKID').val();
		var lnkid = $('#hiddenLNKID').val();
		var username = $('#hiddenUsername').val();
		
		var query = "?dlkid="+ID+"&dlk_patID="+ID+"&lnkid=" + lnkid;
		$.ajax({
		    type: "POST",
		    url: "calculateNEWS.php",
		    data: "page=PHYS&id=" + ID +"&user=" + username,
		    async: false,
		    success: function(msg){
			var ValsArr = msg.split('	');
			console.debug(ValsArr);
			console.debug("Score is " + ValsArr[0]);
			if (ValsArr[0] >= '6') { // NEWS Total
			    popitup('NEWSPopup.php','NEWS',query,550,1025);    
			}
		    },
		    error: function(XMLHttpRequest, textStatus, errorThrown) {
			 rowID = 'Invalid';
			 alert(" Status: " + textStatus + "\n Error message: "+ errorThrown); 
		    } 
		});
	    });
	    
	    /*
	     * End SOFA/NEWS/EWSS
	     */
	    
	    /*
	    * Respite Care
	    */ 
	    
	    $('#respiteCare').tablesorter();
	    $('#respiteCare').hover( function() { 
		$(this).css('cursor','pointer');
	    });
	    $('#respiteCare tbody tr td:not(:last-child)').click(function() { 
		var data = $(this).data();
		var query = "?lnkID=" + data['lnkid'] + "&respCareID=" + data['respcareid'];
		popitup('editrespiteCare.php','Respite Care',query);
	    });
	    
	    $(document).on('click', '#addRespCare', function () {
		var data = $(this).data();
		var query = "?lnkID=" + data['lnkid'];
		popitup('addRespiteCare.php','Respite Care',query);
	    });
	    
	    /*
	     * End respite care
	     */
	    
	    /*
	     * Delete patient
	     */
	    
	    $('#deletePatient').click(function() {
		if (confirm("Are you sure you wish to delete the patient record?")) {
		    var data = $(this).data();
		    var lnk_ID = data['lnkid']
		    $.ajax({
			type: "POST",
			url: "deletePatient.php",
			data: "lnkID=" + lnk_ID,
			async: false,
			success: function(msg){
			    if (isNaN(msg)) {
				alert( "Error: " + msg );
			    } else {
				alert("Patient was successfully deleted. You will now be returned to patient listing.");
				window.location.assign("patListing.php");
			    }
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
			    rowID = 'Invalid';
			    alert(" Status: " + textStatus + "\n Error message: "+ errorThrown); 
			} 
		    });
		}
	    });
	    
	    /*
	     * End delete patient
	     */
	    
	    /*
	     * CSS menu stuff
	     */
	    $('.cssmenu ul li a').click(function() {
		$('.cssmenu ul li a.active_pat_tab').removeClass('active_pat_tab');
		$(this).closest('.cssmenu ul li a').addClass('active_pat_tab');
	    });
    
	    $('.cssmenu ul li ul li.tabsub a').click(function() {
		$('.cssmenu ul li a.active_pat_tab').removeClass('active_pat_tab');
		$(this).parents('ul').parents('li').find('a').addClass('active_pat_tab');
	    });
	    /*
	     * End CSS menu stuff
	     */
	    
	    if ($('#ass-timeliness-Delayed:checked')) {
		//alert("Hi1 " + $('#ass-timeliness-Delayed').val());
		$('#ass-delayed').show();
	    } else {
		//alert("Hi2");
		$('#ass-delayed').hide();
	    }
	    
	    $('#ass-timeliness-Delayed').change(function() {
		if ($(this).val() === 'Delayed') {
		    $('#ass-delayed').show();
		} 
	    });
	    
	    $('#ass-timeliness-Timely').change(function() {
		if ($(this).val() === 'Timely') {
		    $('#ass-delayed').hide();
		} 
	    });
	    
	    
	    $("#accordian h3").click(function(){
			//slide up all the link lists
			$("#accordian ul ul").slideUp();
			//slide down the link list below the h3 clicked - only if its closed
			if(!$(this).next().is(":visible"))
			{
			    $(this).next().slideDown();
			}
	    });     
	    
	    $('#adm-consultant').change( function() {
		var target = $('select#adm-speciality');
		var consultantID = $(this).val();
		changeConsultant(this,target,consultantID);
	    });
	    
	    $('.resetButton').click(function() {
		var data = $(this).data();
		var target = data['target'];
		resetField(target);
	    });
	    
	    $('#pmh_EvidenceAvailable, #pmh-pmhRadio').hide();
	    
	    var pmhPrecheckedVal = $('#pmh-evidenceAvailableToAssess-Yes:checked').val();
	    var pmhPastMedicalHistory = $('#pmh-pastMedicalHistory-No:checked').val();
	    if (pmhPrecheckedVal == 'Yes' && pmhPastMedicalHistory != 'Yes') {
		$('#pmh_EvidenceAvailable, #pmh-pmhRadio').show();
		//alert("PMH is " + pmhPastMedicalHistory);
	    }
	    
	    $('#pmh-pastMedicalHistory-No').click(function() {
		$('#pmh_EvidenceAvailable, #pmh-pmhRadio').hide();
		$('#pmh-pastMedicalHistory-No').prop('checked', false);
	    });
	    
	    $('#pmh-evidenceAvailableToAssess-Yes').click(function() {
		$('#pmh_EvidenceAvailable, #pmh-pmhRadio').show();
	    });
	    
	    $('#pmh-evidenceAvailableToAssess-No').click(function() {
		$('#pmh_EvidenceAvailable, #pmh-pmhRadio').hide();
	    });
	    
	    $('.adm-priorSurgeryHide').hide();
	    
	    $('#adm-priorSurgeryUndertaken-Yes').click(function() {
		$('.adm-priorSurgeryHide').show();
	    });
	    
	    $('#adm-priorSurgeryUndertaken-No').click(function() {
		$('.adm-priorSurgeryHide').hide();
	    });
	    
	    $('#adm-ASAScoreDD').change(function() {
		var scoreVal = $(this).find(":selected").text();
		$("#adm-ASAScoreTextBox").load("ASAScore.php?score=" + scoreVal);
	    });
	    
	    /*
	     * Tabbed nagivation jQuery stuff
	     *  If you add a tab, you need to add some code here to make it switch to the
	     *  correct page. the changeTab ID is zero-indexed, so take the #page-# of the tab and -1 eg #page-13 would be changeTab ID 12
	     */
	    
	    // Demographics
	    
	    $('#tabDemographic').click(function() {
		changeTab(0);
	    });
	    
	    $('#tabNextOfKin').click(function() {
		changeTab(0);
	    });
	    
	    $('#tabAdmission').click(function() {
		changeTab(1);
	    });
	    
	    $('#secondTabAdmission').click(function() {
		changeTab(1);
	    });
	    
	    $('#tabDiagnosis').click(function() {
		changeTab(2);
	    });
	    
	    $('#tabOtherDiagnosis').click(function() {
		changeTab(3);
	    });
	    
	    $('#tabSurgery2').click(function() {
		changeTab(4);
	    });
	    
	    $('#tabComorbidity').click(function() {
		changeTab(5);
	    });
	    
	    $('#tabPMH').click(function() {
		changeTab(6);
	    });
	    
	    $('#tabAssessments').click(function() {
		changeTab(7);
	    });
	    
	    $('#tabPainAssessmentTool').click(function() {
		changeTab(8);
	    });
	    
	    $('#tabModalities').click(function() {
		changeTab(9);
	    });
	    
	    $('#tabDischarge').click(function() {
		changeTab(10);
	    });
	    
	    $('#tabMedicalStaff').click(function() {
		changeTab(11);
	    });
	    
	    $('#tabICD10Diagnosis').click(function() {
		changeTab(12);
	    });
	    
	    $('#tabCCMDS').click(function() {
		changeTab(13);
	    });
	    
	    $('#tabRespiteCare').click(function() {
		changeTab(14);
	    });
	    
	    // Assessments
	    
	    $('#tabAssessDetails').click(function() {
		changeTab(0);
	    });
	    
	    $('#tabPainAssessment').click(function() {
		changeTab(1);
	    });
	    
	    $('#tabPhysicalExamination').click(function() {
		changeTab(2);
	    });
	    
	    $('#tabPhysiology1A').click(function() {
		changeTab(3);
	    });
	    
	    $('#tabPhysiology1B').click(function() {
		changeTab(4);
	    });
	    
	    $('#tabPhysiology2').click(function() {
		changeTab(5);
	    });
	    
	    $('#tabSepsis').click(function() {
		changeTab(6);
	    });
	    
	    $('#tabMedications').click(function() {
		changeTab(7);
	    });
	    
	    $('#tabInterventions').click(function() {
		changeTab(8);
	    });
	    
	    $('#tabCriticalIncidents').click(function() {
		changeTab(9);
	    });
	    
	    $('#tabSurgery').click(function() {
		changeTab(10);
	    });
	    
	    $('#tabVisitOutcome').click(function() {
		changeTab(11);
	    });
	    
	    $('#tabTasks').click(function() {
		changeTab(12);
	    });
	    
	    $('#tabCareLevel').click(function() {
		changeTab(16);
	    });
	    
	    /*
	     * End tabbed nagivation
	     */ 
	    
	    /*
	     * Surgery jQuery stuff
	     */
	    
	    // Prevent enter key from submitting form on surgery search
	    $('#search').bind("keypress", function(e) {
		var code = e.keyCode || e.which; 
		if (code  == 13) {               
		  e.preventDefault();
		  return false;
		}
	    });
	    
	    $('.surgeryIframe').css('height', $(window).height()+'px');
	    
	    $('#surgerySearch').tablesorter({ sortList: [0,0] });
	    
	    function showLoader(){
		$(".searchBtn").fadeOut(200).button("disable");
		$('.search-background').fadeIn(200);
	    }  
	      
	    //hide loading bar			      
	    function hideLoader() {			      
		$('#sub_cont').fadeIn(1500);			      
		$('.search-background').fadeOut(200);
		$(".searchBtn").fadeIn(1500).button("enable");
	    };  
	      
	    /*$('#search').keyup(function(e) {
		e.preventDefault();
		var surgeryData = $(this).data();
		//console.debug(surgeryData);
		surgerylnkid = surgeryData['lnkid'];
		surgerydlkid = surgeryData['dlkid'];
	      
		if(e.keyCode == 13) {
		    e.preventDefault();
		    $("#content #sub_cont").html('');
		    //show the loading bar			      
		    showLoader();  
		    $('#preresults').fadeOut(500);  
		    $('#sub_cont').fadeIn(1500);  
		    $("#content #sub_cont").load("search.php?lnkid=" + surgerylnkid + "&dlkid=" + surgerydlkid + "&search=" + $("#search").val(), hideLoader());  
		    return false;
		}
		return false;
	    }); */
	      
	    $(".searchBtn").click(function(){
		var surgerySearching = 0;
		var surgeryData = $(this).data();
		surgerylnkid = surgeryData['lnkid'];
		surgerydlkid = surgeryData['dlkid'];
		//$(this).button("disable");
		    
		$("#content #sub_cont").html('');
		//show the loading bar			      
		showLoader();  
		$('#preresults').fadeOut(500);  
		$('#sub_cont').fadeIn(1500);  
		$("#content #sub_cont").load("search.php?lnkid=" + surgerylnkid + "&dlkid=" + surgerydlkid + "&search=" + $("#search").val(), hideLoader());
	    });
	    
	    /*
	     * End surgery jQuery stuff
	     */ 
	     
	    /*
	     * Groups/items add, delete and edit row functions
	     */
	    
	    $('.addRow').click(function() {
		CLRow = 0;
		var data = $(this).closest(':radio').data();
		var abbr = data['abbr'];
		var item_ID = data['item_id'];
		var group = data['group'];
		var dlk_ID = data['dlk_id'];
		var lnk_ID = data['lnk_id'];
		var destination = data['destination'];
		var edit = data['edit'];
		var med = data['med'];
		//console.debug(data);
		
		if (med === 1) {
		    // Determine which accordion panel is open to determine which table to place the new row in
		    if (window.activeMedicationIndex === null) {
			var activeMedicationIndex = 0;
		    }
		    
		    switch (window.activeMedicationIndex) {
			case 0:
			    var destination = "medications";
			    var abbr = "ME";
			break;
		    
			case 1:
			    var destination = "pre-medications";
			    var abbr = "PRMED";
			break;
		    
			case 2:
			    var destination = "rec-medications";
			    var abbr = "REMED";
			break;
		    }
		}
		
		$.ajax({
		   type: "POST",
		   url: "addRow.php",
		   data: "destination="+ destination + "&itemID=" + item_ID + "&group=" + group + "&dlk_ID=" + dlk_ID + "&lnk_ID=" + lnk_ID,
		   async: false,
		   success: function(msg){
			rowID = msg;
			if (isNaN(rowID)) {
			    alert( "Error: " + msg );
			} else {
			    CLRow = 1;
			}
		    },
		   error: function(XMLHttpRequest, textStatus, errorThrown) {
			rowID = 'Invalid';
			alert(" Status: " + textStatus + "\n Error message: "+ errorThrown); 
		    } 
		});
		
		
		if (CLRow == 1) {
		    /*Extra columns for the medications area*/
		    if (med == 1) {
			// Need to get extra data to fill out the dropdown menus
			var DDHTML = "";
			$.ajax({
			    type: "POST",
			    url: "getMedDDs.php",
			    data: "id=" + rowID,
			    async: false,
			    success: function(msg){
				DDHTML = msg;
			    },
			    error: function(XMLHttpRequest, textStatus, errorThrown) {
				DDHTML = 'Invalid';
				alert(" Status: " + textStatus + "\n Error message: "+ errorThrown); 
			    } 
			});
			var medDDs = DDHTML.split('|');			
			
			var tr = $(
				'<tr>'
				+ '<td class="cat ' + abbr + '"></td>'
				+ '<td class="sel ' + abbr + '"></td>' 
				+ '<td><input type="text" value="0" name="med-Dose[' + rowID + ']" id="med-Dose[' + rowID + ']"></td>'
				+ '<td>' + medDDs[0] + '</td>'
				+ '<td>' + medDDs[1] + '</td>'
				+ '<td>' + medDDs[2] + '</td>'
				+ '<td>' + medDDs[3] + '</td>'
				+ '<td><input type="date" value="" name="med-Discontinued[' + rowID + ']" id="med-Discontinued[' + rowID + ']"></td>'
				+ '<td id="textArea_cell"><textarea class="FormBlock gi_text" name="mednotes[' + rowID + ']"></textarea><input type="hidden" name="catText[' + rowID + ']" class="hiddenCat"><input type="hidden" name="selText[' + rowID + ']" class="hiddenSel"></td>'
				+ '<td id="Button_cell"><button id="' + rowID + '" type="button" class="editRow ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" data-page="' + destination + '"><img src="Media/img/pencil.gif" alt="Edit"/></button></td>'
				+ '<td id="Button_cell"><button id="' + rowID + '" type="button" class="deleteRow ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" data-page="' + destination + '"><img src="Media/img/bin.gif" alt="Delete"/></button></td></tr>');
			$( ".medicationAccordion" ).accordion( "refresh" );
		    } else if (med == 2) {
			var tr = $(
				'<tr>'
				+ '<td class="cat ' + abbr + '"></td>'
				+ '<td class="sel ' + abbr + '"></td>'
				+ '<td id="textArea_cell"><textarea class="FormBlock gi_text" name="' + abbr + 'notes[' + rowID + ']"></textarea><input type="hidden" name="catText[' + rowID + ']" class="hiddenCat"><input type="hidden" name="selText[' + rowID + ']" class="hiddenSel"></td>'
				+ '<td id="Button_cell"><button id="' + rowID + '" type="button" class="editRow ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" data-page="' + destination + '"><img src="Media/img/pencil.gif" alt="Edit"/></button></td>'
				+ '<td id="Button_cell"><button id="' + rowID + '" type="button" class="deleteRow ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" data-page="' + destination + '"><img src="Media/img/bin.gif" alt="Delete"/></button></td></tr>');
		    }
		    else if (abbr == 'DO') {
			// Daily outcome adds a date and time field too
			var d = new Date();
			var curr_date = d.getDate();
			var curr_month = d.getMonth() + 1; //Months are zero based
			var curr_year = d.getFullYear();
			
			if (curr_date < 10) {
			    curr_date = '0'+curr_date;
			}
			
			if (curr_month < 10) {
			    curr_month = '0'+curr_month;
			}
			var today = (curr_year + "-" + curr_month + "-" + curr_date);
			//console.debug("Today is " + today);
			var tr = $(
				'<tr>'
				+ '<td class="cat ' + abbr + '"></td>'
				+ '<td class="sel ' + abbr + '"></td>'
				+ '<td><input type="date" class="FormDate valid FormDODate" name="' + abbr + 'Date[' + rowID + ']" id="' + abbr + 'Date_' + rowID + '" value="' + today + '"></td>'
				+ '<td><input type="time" class="FormTime valid FormDOTime" name="' + abbr + 'Time[' + rowID + ']" id="' + abbr + 'Time_' + rowID + '" value="00:00"></td>'
				+ '<td id="textArea_cell"><textarea class="FormBlock gi_text" name="' + abbr + 'notes[' + rowID + ']"></textarea><input type="hidden" name="catText[' + rowID + ']" class="hiddenCat"><input type="hidden" name="selText[' + rowID + ']" class="hiddenSel"></td>'
				+ '<td id="Button_cell"><button id="' + rowID + '" type="button" class="deleteRow ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" data-page="' + destination + '"><img src="Media/img/bin.gif" alt="Delete"/></button></td></tr>');
		    }
		    /*Append item in G&I with with the remove option and Edit option buttons available*/
		    else if (edit == 'y') {
			var tr = $(
			    '<tr>'
			    + '<td class="cat ' + abbr + '"></td>' 
			    + '<td class="sel ' + abbr + '"></td>'
			    + '<td id="textArea_cell"><textarea class="FormBlock gi_text" name="' + abbr + 'notes[' + rowID + ']"></textarea><input type="hidden" name="catText[' + rowID + ']" class="hiddenCat"><input type="hidden" name="selText[' + rowID + ']" class="hiddenSel"></td>'
			    + '<td id="Button_cell"><button id="' + rowID + '" type="button" class="editRow ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" data-page="' + destination + '"><img src="Media/img/pencil.gif" alt="Edit"/></button></td>'
			    + '<td id="Button_cell"><button id="' + rowID + '" type="button" class="deleteRow ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" data-page="' + destination + '"><img src="Media/img/bin.gif" alt="Delete"/></button></td>'
			    + '</tr>');	
		    } else {

		    /*Append item in G&I with with just the remove option button*/	
			var tr = $(
			    '<tr>'
			    + '<td class="cat ' + abbr + '"></td>' 
			    + '<td class="sel ' + abbr + '"></td>'
			    + '<td id="textArea_cell"><textarea class="FormBlock gi_text" name="' + abbr + 'notes[' + rowID + ']"></textarea><input type="hidden" name="catText[' + rowID + ']" class="hiddenCat"><input type="hidden" name="selText[' + rowID + ']" class="hiddenSel"></td>'
			    + '<td id="Button_cell"><button id="' + rowID + '" type="button" class="deleteRow ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" data-page="' + destination + '" role="button" aria-disabled="false"><span class="ui-button-text"><img src="Media/img/bin.gif" alt="Delete"/></span></button></td>'
			    + '</tr>');	
		    }
		    
			$('.' + abbr + 'Table > tbody:last').one().append(tr);
			tr.find(".cat").text($(this).closest("li.category").attr("title"));
			tr.find(".hiddenCat").val($(this).closest("li."+abbr).attr("title"));
			tr.find(".sel").text($(this).closest("li.select").attr("title"));
			tr.find(".hiddenSel").val($(this).closest("li."+abbr).attr("title"));

		    CLRow = 0;	
		}
	    }); 
	    
	    $(document).on('click', '.editRow', function() { 
		var id = $(this).attr('id');
		var data = $(this).data();
		page = data['page'];
		var url = "edit" + page + ".php?dlk=<?php echo (isset($patient['DLK_ID'])) ? $patient['DLK_ID'] : 0; ?>&lnk=<?php echo (isset($patient['LNK_ID'])) ? $patient['LNK_ID'] : 0; ?>&row="+ id;
		popitup(url,page,'',375,1075);
	    });
	    
	    $(document).on('click', '.deleteRow', function() {
		var id = $(this).attr('id');
		var data = $(this).data();
		page = data['page'];
		$.ajax({
		   type: "POST",
		   url: "DeleteRow.php",
		   data: "page=" + page + "&itemID="+id,
		   success: function(msg){
		   },
		   error: function(XMLHttpRequest, textStatus, errorThrown) { 
			alert("Status: " + textStatus); alert("Error: " + errorThrown); 
		    } 
		});
	    
		var whichtr = $(this).closest("tr");
		whichtr.remove();

	    });
	    
	    /*
	     * End groups/items functions
	     */
	    
	    /*
	     * Daily Outcome sublist stuff
	     */
	    $('#doActionTakenSublist, #do-actiontaken2').hide();
	    
	    $('#do-actiontaken').change(function() {
		var list = $(this).val();
		switch (list) {
		    case "Refer for specialist assistance":
			changeDropDown('do-actiontaken2','do-specialist','specialist');
			$('#doActionTakenSublist').text('Referred to');
			$('#doActionTakenSublist, #do-actiontaken2').show();
		    break;
		    
		    case "Transfer":
			changeDropDown('do-actiontaken2','do-transfer','transfer');
			$('#doActionTakenSublist').text('Transferred to');
			$('#doActionTakenSublist, #do-actiontaken2').show();
		    break;
		
		    default:
			$('#doActionTakenSublist, #do-actiontaken2').hide();
			$('#doActionTakenSublist').text('Action detail');
		    break;
		}
	    });
	    
	    /*
	     * End daily outcome sublist stuff
	     */
	    
	    /*
	     * Begin Physiology 1A
	     */
	    
	    // Limb calculation
	    
	    function getLimbScore(limb) {
		var limbScore = 0;
		switch (limb) {
		    case "None":
			limbScore = 0;
		    break;
		
		    case "Flicker":
			limbScore = 1;
		    break;
		
		    case "Movement":
			limbScore = 2;
		    break;
		
		    case "Movement against gravity":
			limbScore = 3;
		    break;
		
		    case "Movement against resistance":
			limbScore = 4;
		    break;
		
		    case "Full power":
			limbScore = 5;
		    break;
		}
		
		return limbScore;
	    }
	    
	    function calculateLimbScore(that) {
		var thisVal = $(that).val();
		var newLimbScore = getLimbScore(thisVal);
		var scoreSelectorID = $(that).attr('id');
		var scoreSelector = scoreSelectorID + "Score";
		$('#' + scoreSelector).val(newLimbScore);
		updateLimbTotal();
	    };
	    
	    function updateLimbTotal() {
		// Get all 4 number totals
		var leftArm = ($('#phys-LimbLeftArmScore').val().length > 0) ? parseInt($('#phys-LimbLeftArmScore').val(),10) : 0;
		var leftLeg = ($('#phys-LimbLeftLegScore').val().length > 0) ? parseInt($('#phys-LimbLeftLegScore').val(),10) : 0;
		var rightArm = ($('#phys-LimbRightArmScore').val().length > 0) ? parseInt($('#phys-LimbRightArmScore').val(),10) : 0;
		var rightLeg = ($('#phys-LimbRightLegScore').val().length > 0) ? parseInt($('#phys-LimbRightLegScore').val(),10) : 0;
		
		var newLimbTotal = leftArm+leftLeg+rightArm+rightLeg;
		
		// Update
		$('#phys-LimbTotal').val(newLimbTotal);
	    }
	    
	    // Initially calculate the score on page load if they exist
	    if ($('#phys-LimbLeftArm').length > 0) calculateLimbScore($('#phys-LimbLeftArm'));
	    if ($('#phys-LimbLeftLeg').length > 0) calculateLimbScore($('#phys-LimbLeftLeg'));
	    if ($('#phys-LimbRightArm').length > 0) calculateLimbScore($('#phys-LimbRightArm'));
	    if ($('#phys-LimbRightLeg').length > 0) calculateLimbScore($('#phys-LimbRightLeg'));
	    
	    $('#phys-LimbLeftArm, #phys-LimbLeftLeg, #phys-LimbRightArm, #phys-LimbRightLeg').change(function() {
		calculateLimbScore(this);
	    });
	    
	    /*
	     * End physiology 1A
	     */ 
	    
	    /*
	     * Begin auto-fill of dropdowns, mainly for primary/secondary diagnosis
	     * Select = ID of dropdown list to be changed and also the switch case to use in changeDropdown.php
	     * options = Specific option selected from dropdown
	     * specifier = Added later. In very few instances like daily outcome which dropdown list to change can depend on option select, so use this to specify which
	     */ 
	    
	    function changeDropDown(select, options, specifier) {
		var dropdown = $("select#" + select + "");
		/*var optionData = $('select#' + options +'').val().data();
		var optionID = optionData['id'];*/
		var val = $('#' + options + '').val();
		var optionID = $('#' + options + ' option').filter(function() {
		    return this.value == val;
		}).data('id');
		//console.debug("Option is: " + options + " and ID is " + optionID + " and val is " + val);
		dropdown.empty();    
		dropdown.load("changeDropdown.php?dd=" + select + "&id=" + $('#' + options + '').val() + "&specifier=" + specifier);
		//dropdown.load("changeDropdown.php?dd=" + select + "&id=" + optionID);
	    }
	    
	    function getDiagnosisCode(destination, procID, description) {				
		if ($(destination).text().trim().charAt(0) == "#") {
		    $(destination).text($(header).text().replace("#", ""));
		}

		$.ajax({
		   type: "POST",
		   url: "getDiagnosisCode.php",
		   data: "procID=" + procID + "&description=" + description,
		   success: function(msg){
		    $('#' + destination + '').val(msg);
		   },
		   error: function(XMLHttpRequest, textStatus, errorThrown) { 
			alert("Status: " + textStatus); alert("Error: " + errorThrown); 
		    } 
		 });
	    }
	    
	    if (($('#ass-seenByName')).length <= 1) {
		$('#ass-seenByName option[value=""]').text('No staff listed');
	    }
	    
	    if (($('#ass-seenByName1')).length <= 1) {
		$('#ass-seenByName1 option[value=""]').text('No staff listed');
	    }
	    
	    if (($('#ass-seenByName2')).length <= 1) {
		$('#ass-seenByName2 option[value=""]').text('No staff listed');
	    }
	    
	    $('#ass-seenByName').one("click", function() {
		changeDropDown('ass-seenByName','ass-seenByRole');
		if (($('#ass-seenByName')).length <= 1) {
		    $('#ass-seenByName option[value=""]').text('No staff listed');
		}
	    });
	    
	    $('#ass-seenByRole').change(function() {
		$('#ass-seenByName').val('');
		changeDropDown('ass-seenByName','ass-seenByRole');
	    });
	    
	    $('#ass-seenByName1').one("click", function() {
		changeDropDown('ass-seenByName1','ass-seenByRole1');
		if (($('#ass-seenByName1')).length <= 1) {
		    $('#ass-seenByName1 option[value=""]').text('No staff listed');
		}
	    });
	    
	    $('#ass-seenByRole1').change(function() {
		$('#ass-seenByName1').val('');
		changeDropDown('ass-seenByName1','ass-seenByRole1');
	    });
	    
	    $('#ass-seenByName2').one("click", function() {
		changeDropDown('ass-seenByName2','ass-seenByRole2');
		if (($('#ass-seenByName2')).length <= 1) {
		    $('#ass-seenByName2 option[value=""]').text('No staff listed');
		}
	    });
	    
	    $('#ass-seenByRole2').change(function() {
		$('#ass-seenByName2').val('');
		changeDropDown('ass-seenByName2','ass-seenByRole2');
	    });
	    
	    $('#pdi-Type').change(function() {
		$('#pdi-System').val(0);
		$('#pdi-Site').empty();
		$('#pdi-Process').empty();
		$('#pdi-Condition').empty();
		$('#pdi-Code').val('');
	    });
	
	    $('#pdi-System').change(function() {
		$('#pdi-Site').empty();
		$('#pdi-Process').empty();
		$('#pdi-Condition').empty();
		$('#pdi-Code').val('');
		changeDropDown('pdi-Site','pdi-System');
	    });
	    
	    $('#pdi-Site').change(function() {
		$('#pdi-Condition').empty();
		$('#pdi-Code').val('');
		changeDropDown('pdi-Process','pdi-Site');
	    });
	    
	    $('#pdi-Process').change(function() {
		$('#pdi-Code').val('');
		changeDropDown('pdi-Condition','pdi-Process');
	    });
	    
	    $('#pdi-Condition').change(function() {
		var proc = $(this).val();
		var description = $('#pdi-Condition option:selected').text();
		getDiagnosisCode('pdi-Code', '' + proc + '', '' + description + '');
		changeDropDown('hiddenDiag','pdi-Condition');
	    });
	    
	    $('#sdi-Type').change(function() {
		$('#sdi-System').val(0);
		$('#sdi-Site').empty();
		$('#sdi-Process').empty();
		$('#sdi-Condition').empty();
		$('#sdi-Code').val('');
	    });
	    
	    $('#sdi-System').change(function() {
		$('#sdi-Site').empty();
		$('#sdi-Process').empty();
		$('#sdi-Condition').empty();
		$('#sdi-Code').val('');
		changeDropDown('sdi-Site','sdi-System');
	    });
	    
	    $('#sdi-Site').change(function() {
		$('#sdi-Condition').empty();
		$('#sdi-Code').val('');
		changeDropDown('sdi-Process','sdi-Site');
	    });
	    
	    $('#sdi-Process').change(function() {
		$('#sdi-Code').val('');
		changeDropDown('sdi-Condition','sdi-Process');
	    });
	    
	    $('#sdi-Condition').change(function() {
		var proc = $(this).val();
		var description = $('#sdi-Condition option:selected').text();
		getDiagnosisCode('sdi-Code', '' + proc + '', '' + description + '');
	    });
	    
	    $('#ass-assessmentReason').change(function() {
		var selectedOption = $(this).find(":selected").text().replace(/ /g,"_"); // Replace whitespace with _ for URL transportation
		console.debug(selectedOption);
		$.ajax({
		    type: "GET",
		    url: "otrFollowUp.php",
		    data: { "followup": selectedOption },
		    async: false,
		    success: function(msg){
			console.debug(msg);
			$('#ass-detail').empty('');
			changeDropDown('ass-detail','0',msg);
		    },
		    error: function(XMLHttpRequest, textStatus, errorThrown) {
			rowID = 'Invalid';
			alert(" Status: " + textStatus + "\n Error message: "+ errorThrown); 
		    } 
		});				
	    });
	    
	    /*
	     * End primary/secondary diagnosis dropdown auto-fills
	     */ 
	    
	    /*
	     * Begin diagnosis search
	     */
	    
	    /*
	     * Begin primary diagnosis
	     */
	    
	    $(function() {
		var searchDiag = $( "#searchDiag" ),
		  allFields = $( [] ).add( searchDiag ),
		  tips = $( ".validateTips" );
	     
		function updateTips( t ) {
		  tips
		    .text( t )
		    .addClass( "ui-state-highlight" );
		  setTimeout(function() {
		    tips.removeClass( "ui-state-highlight", 1500 );
		  }, 500 );
		}	     
	     
		$( "#diagnosis-search-form" ).dialog({
		  autoOpen: false,
		  height: 400,
		  width: 800,
		  modal: true,
		  buttons: {
		    "Search diagnosis": function() { 
		      var bValid = true;
		      var data = $(this).data();
		      var page = data['page'];
		      allFields.removeClass( "ui-state-error" );
		      var submitButton = $(".ui-dialog-buttonpane button:contains('Search diagnosis')");
		      submitButton.button("disable");
		      
	     
		      if ( bValid ) {
			$.ajax({
			    type: "POST",
			    url: "diagnosisSearch.php",
			    data: "page=" + page +"&search="+ searchDiag.val(),
			    async: true,
			    success: function(msg){					      
			      $('#diagnosisResults').css('display','block');
			      $('#diagnosisResults').html(msg);
			      submitButton.button("enable");
			    },
			    error: function(XMLHttpRequest, textStatus, errorThrown) {
				 rowID = 'Invalid';
				 alert(" Status: " + textStatus + "\n Error message: "+ errorThrown);
				 submitButton.button("enable");
			     } 
			  });
			//$( this ).dialog( "close" );
		      }
		    },
		    Cancel: function() {
		      $('#diagnosisResults').css('display','none');
		      $('#diagnosisResults').html('');
		      $( this ).dialog( "close" );
		    }
		  },
		  close: function() {
		    allFields.val( "" ).removeClass( "ui-state-error" );
		  }
		});
	     
		$( "#search-diagnosis" )
		  .button()
		  .click(function() {
		    $( "#diagnosis-search-form" ).dialog( "open" );
		  });
	      });
	    
	    /*
	     * End primary diagnosis
	     */
	    
	    /*
	     * Begin secondary diagnosis
	     * I'd like to cut down on code reuse but I
	     * can't figure out how to do this as everything
	     * I have tried so far results in it stopping working
	     */
	    
	    $(function() {
		var searchDiag = $( "#sdi-searchDiag" ),
		  allFields = $( [] ).add( searchDiag ),
		  tips = $( ".validateTips" );
	     
		function updateTips( t ) {
		  tips
		    .text( t )
		    .addClass( "ui-state-highlight" );
		  setTimeout(function() {
		    tips.removeClass( "ui-state-highlight", 1500 );
		  }, 500 );
		}	     
	     
		$( "#sdi-diagnosis-search-form" ).dialog({
		  autoOpen: false,
		  height: 400,
		  width: 800,
		  modal: true,
		  buttons: {
		    "Search diagnosis": function() {
		      var bValid = true;
		      var data = $(this).data();
		      var page = data['page'];
		      allFields.removeClass( "ui-state-error" );
		      var submitButton = $(".ui-dialog-buttonpane button:contains('Search diagnosis')");
		      submitButton.button("disable");
	     
		      if ( bValid ) {
			$.ajax({
			    type: "POST",
			    url: "diagnosisSearchSecondary.php",
			    data: "page=" + page +"&search="+ searchDiag.val(),
			    async: true,
			    success: function(msg){					      
			      $('#sdi-diagnosisResults').css('display','block');
			      $('#sdi-diagnosisResults').html(msg);
			      submitButton.button("enable");
			    },
			    error: function(XMLHttpRequest, textStatus, errorThrown) {
				 rowID = 'Invalid';
				 alert(" Status: " + textStatus + "\n Error message: "+ errorThrown);
				 submitButton.button("enable");
			     } 
			  });
			//$( this ).dialog( "close" );
		      }
		    },
		    Cancel: function() {
		      $('#sdi-diagnosisResults').css('display','none');
		      $('#sdi-diagnosisResults').html('');
		      $( this ).dialog( "close" );
		    }
		  },
		  close: function() {
		    allFields.val( "" ).removeClass( "ui-state-error" );
		  }
		});
	     
		$( "#sdi-search-diagnosis" )
		  .button()
		  .click(function() {
		    $( "#sdi-diagnosis-search-form" ).dialog( "open" );
		  });
	      });
	    
	    /*
	     * End secondary diagnosis
	     */ 
	    
	    $(".diagnosisSearchBtn").click(function(){			      
		$("#diagnosisResults").load("diagnosisSearch.php?search=" + $("#search").val());  
	    });
	    
	    /*
	     * End  diagnosis search
	     */ 
	    
	    $('#intensePainNowSlider').labeledslider({
		value:5,
		min: 0,
		max: 10,
		step: 1,
		tickInterval: 1,
		slide: function( event, ui ) {
		  $( "#amount" ).val( ui.value );
		},
		change: function(event, ui) {
		  $('input#intensePainNowSliderval').val(ui.value);
		}
	    });
	    $( "#amount" ).val( $( "#intensePainNowSlider" ).labeledslider( "value" ) );
	    $('input#intensePainNowSliderval').val($( "#intensePainNowSlider" ).labeledslider( "value" ));
	      
	    $('#intensePainLastWeekSlider').labeledslider({
		value:5,
		min: 0,
		max: 10,
		step: 1,
		tickInterval: 1,
		slide: function( event, ui ) {
		  $( "#amount" ).val( ui.value );
		},
		change: function(event, ui) {
		  $('input#intensePainLastWeekSliderval').val(ui.value);
		}
	      });
	      $( "#amount" ).val( $( "#intensePainLastWeekSlider" ).labeledslider( "value" ) );
	      $('input#intensePainLastWeekSliderval').val($( "#intensePainLastWeekSlider" ).labeledslider( "value" ));
	      
	      $('#distressingPainNowSlider').labeledslider({
		value:5,
		min: 0,
		max: 10,
		step: 1,
		tickInterval: 1,
		slide: function( event, ui ) {
		  $( "#amount" ).val( ui.value );
		},
		change: function(event, ui) {
		  $('input#distressingPainNowSliderval').val(ui.value);
		}
	    });
	    $( "#amount" ).val( $( "#distressingPainNowSlider" ).labeledslider( "value" ) );
	    $('input#distressingPainNowSliderval').val($( "#distressingPainNowSlider" ).labeledslider( "value" ));
	      
	    $('#distressingPainAverageSlider').labeledslider({
		value:5,
		min: 0,
		max: 10,
		step: 1,
		tickInterval: 1,
		slide: function( event, ui ) {
		  $( "#amount" ).val( ui.value );
		},
		change: function(event, ui) {
		  $('input#distressingPainAverageSliderval').val(ui.value);
		}
	    });
	    $( "#amount" ).val( $( "#distressingPainAverageSlider" ).labeledslider( "value" ) );
	    $('input#distressingPainAverageSliderval').val($( "#distressingPainAverageSlider" ).labeledslider( "value" ));
	      
	    $('#painAverageLastWeekSlider').labeledslider({
		value:5,
		min: 0,
		max: 10,
		step: 1,
		tickInterval: 1,
		slide: function( event, ui ) {
		  $( "#amount" ).val( ui.value );
		},
		change: function(event, ui) {
		  $('input#painAverageLastWeekSliderval').val(ui.value);
		}
	    });
	    $( "#amount" ).val( $( "#painAverageLastWeekSlider" ).labeledslider( "value" ) );
	    $('input#painAverageLastWeekSliderval').val($( "#painAverageLastWeekSlider" ).labeledslider( "value" ));
	      
	    $('#painEverydayActivitiesSlider').labeledslider({
		value:5,
		min: 0,
		max: 10,
		step: 1,
		tickInterval: 1,
		slide: function( event, ui ) {
		  $( "#amount" ).val( ui.value );
		},
		change: function(event, ui) {
		  $('input#painEverydayActivitiesSliderval').val(ui.value);
		}
	    });
	    $( "#amount" ).val( $( "#painEverydayActivitiesSlider" ).labeledslider( "value" ) );
	    $('input#painEverydayActivitiesSliderval').val($( "#painEverydayActivitiesSlider" ).labeledslider( "value" ));
	      
	    $('#painTreatmentReliefSlider').labeledslider({
		value:50,
		min: 0,
		max: 100,
		step: 10,
		tickInterval: 1,
		slide: function( event, ui ) {
		  $( "#amount" ).val( ui.value );
		},
		change: function(event, ui) {
		  $('input#painTreatmentReliefSliderval').val(ui.value);
		}
	    });
	    $( "#amount" ).val( $( "#painTreatmentReliefSlider" ).labeledslider( "value" ) );
	    $('input#painTreatmentReliefSliderval').val($( "#painTreatmentReliefSlider" ).labeledslider( "value" ));
	    
	    /*
	     * Calculate and set BMI
	     */
	    
	    $('#dmg-height').change(function() {
		var weight = $('#dmg-weight').val();
		var height = $(this).val();
		var BMI = getBMI(height,weight);
		$('#dmg-bodyMassIndex').val(BMI);
		$('#dmg-BMIHidden').val(BMI);
		console.debug(weight,height, BMI);
	    });
	    
	    $('#dmg-weight').change(function() {
		var weight = $(this).val();
		var height = $('#dmg-height').val();
		var BMI = getBMI(height,weight);
		$('#dmg-bodyMassIndex').val(BMI);
		$('#dmg-BMIHidden').val(BMI);
		console.debug(weight,height, BMI);
	    });
	    
	    /*
	     * End BMI
	     */
	    
	    /*
	     * Calculate Age Days/Months/Years for demographics tab
	     */
	    
	    function updateDOB() {
		var dob = $('#dmg-DOB').val();
		var age = getAge(dob, new Date());
		$('#dmg-demAgeYears').text(age[0]);
		$('#dmg-demAgeMonths').text(age[1]);
		$('#dmg-demAgeDays').text(age[2]);
	    }
	    
	    $('#dmg-DOB').change(function() {
		updateDOB();
	    });
	    
	    /*
	     * End calculate age
	     */ 
	    
	    /*
	     * Discharge Alive/Dead form element stuff
	     */
	    
	    $('#otr-outreachDischargeDateTR').hide();
	    $('#otr-outreachDischargeTime').hide();
	    $('#dis-hospitalDischargeStatusAlive').hide();
	    $('#dis-hospitalDischargeStatusDead').hide();
	    $('#dis-hospitalDischarge').hide();
	    
	    if ($('#otr-outreachDischargeStatusOutcome').val() == 'Alive') {
		$('#dis-hospitalDischarge').show();
	    }
	    
	    $('#otr-outreachDischargeStatusOutcome').change(function() {
		var patientStatus = $(this).val();
		
		switch (patientStatus) {
		    case 'Alive':
			$('#otr-outreachDischargeDateTR').show();
			$('#otr-outreachDischargeTime').show();
			$('#dis-hospitalDischarge').show();
		    break;
		
		    case 'Dead':
			$('#otr-outreachDischargeDateTR').hide();
			$('#otr-outreachDischargeTime').hide();
			$('#dis-hospitalDischarge').show();
		    break;
		
		    default:
			$('#otr-outreachDischargeDateTR').hide();
			$('#otr-outreachDischargeTime').hide();
			$('#dis-hospitalDischarge').hide();
		    break;
		}
	    });
	    
	    $('#otr-hospitalDischargeStatus').change(function() {
		var patientStatus = $(this).val();
		
		switch (patientStatus) {
		    case 'Alive':
			$('#dis-hospitalDischargeStatusAlive').show();
			$('#dis-hospitalDischargeStatusDead').hide();
		    break;
		
		    case 'Dead':
			$('#dis-hospitalDischargeStatusAlive').hide();
			$('#dis-hospitalDischargeStatusDead').show();
		    break;
		
		    default:
			$('#dis-hospitalDischargeStatusAlive').hide();
			$('#dis-hospitalDischargeStatusDead').hide();
		    break;
		}
	    });
	    
	    /*
	     * End discharge stuff
	     */
	    
	    /*
	     * MEWS/EWS total score stuff
	     */
	    
	    function fetchEWSSScore(input,category) {
		/*$.ajax({
		    type: "POST",
		    url: "EWSSTriggerLevel.php",
		    data: "input=" + input + "&category=" + category,
		    async: true,
		    success: function(msg){					      
			EWSS_Score = parseInt(msg, 10);
	    		if (isNaN(EWSS_Score)) {
			EWSS_Score = 'Invalid';
			alert("An invalid response was given while attempting to fetch EWSS Score.");
			return false;
			} else {
			return EWSS_Score;
			}   
		    },
		    error: function(XMLHttpRequest, textStatus, errorThrown) {
			 EWSS_Score = 'Invalid';
			 alert(" Status: " + textStatus + "\n Error message: "+ errorThrown); 
		     }
		});*/
		return $.ajax({
		    type: "POST",
		    url: "EWSSTriggerLevel.php",
		    data: "input=" + input + "&category=" + category
		    //data: {"input": " " + input + " ", "category": " " + category + " "}
		}).then(function(EWSS_Score) {
		    if (isNaN(EWSS_Score)) {
			alert("An invalid response was given while attempting to fetch EWSS Score.");
			return 'Invalid';
		    }
		    return EWSS_Score;
		});
	    }
	    
	    function calculateEWSSTotal() {
		var HR = Number($('#heartRateWeighted').val());
		var resp = Number($('#respRateWeighted').val());
		var temp = Number($('#temperatureWeighted').val());
		var sysBP = Number($('#sysBPWeighted').val());
		var urine = Number($('#urineWeighted').val());
		var pain = Number($('#painWeighted').val());
		var O2sat = Number($('#O2SatWeighted').val());
		var GCS = Number($('#GCSWeighted').val());
		var baseExcess = Number($('#baseExcessWeighted').val());
		var ph = Number($('#pHWeighted').val());
		var pao2 = Number($('#pAO2Weighted').val());
		
		var total = (HR+resp+temp+sysBP+urine+pain+O2sat+GCS+baseExcess+ph+pao2);
		
		$('#MEWSTotal').val(total);
	    }
	    
	    function calculateEWSSWeightedScore(input,category,weighted) {
		var EWSSScore = fetchEWSSScore(input,category).done(function(EWSSScore) {
		    $(weighted).val(EWSSScore);
		    calculateEWSSTotal();
		});
		/*alert("It is:" + EWSSScore);
		var EWSSScoreVal = EWSSScore.html();
		$(weighted).val(EWSSScoreVal);
		//$(weighted).text(EWSSScore);
		calculateEWSSTotal();*/
	    }
	    
	    $('#heartRate').change(function() {
		var category = "HR";
		var input = $(this).val();
		calculateEWSSWeightedScore(input,category,'#heartRateWeighted');
	    });
	    
	    $('#respRate').change(function() {
		var category = "Resp";
		var input = $(this).val();		
		calculateEWSSWeightedScore(input,category,'#respRateWeighted');
	    });
	    
	    $('#temperature').change(function() {
		var category = "Temp";
		var input = $(this).val();		
		calculateEWSSWeightedScore(input,category,'#temperatureWeighted');	
	    });
	    
	    $('#sysBP').change(function() {
		var category = "BP";
		var input = $(this).val();		
		calculateEWSSWeightedScore(input,category,'#sysBPWeighted');
	    });
	    
	    $('#AVPU').change(function() {
		var category = "CNS";
		var input = $(this).val();		
		calculateEWSSWeightedScore(input,category,'#AVPUWeighted');
	    });
	    
	    $('#Urine').change(function() {
		var category = "Urine";
		var input = $(this).val();		
		calculateEWSSWeightedScore(input,category,'#urineWeighted');
	    });
	    
	    $('#Pain').change(function() {
		var category = "Pain";
		var input = $(this).val();		
		calculateEWSSWeightedScore(input,category,'#painWeighted');
	    });
	    
	    $('#O2Sat').change(function() {
		var category = "O2";
		var input = $(this).val();		
		calculateEWSSWeightedScore(input,category,'#O2SatWeighted');
	    });
	    
	    $('#Resp').change(function() {
		var category = "Resp";
		var input = $(this).val();		
		calculateEWSSWeightedScore(input,category,'#respSupportWeighted');
	    });
	    
	    $('#GCS').change(function() {
		var category = "GCS";
		var input = $(this).val();		
		calculateEWSSWeightedScore(input,category,'#GCSWeighted');
	    });
	    
	    $('#BaseExcess').change(function() {
		var category = "BE";
		var input = $(this).val();		
		calculateEWSSWeightedScore(input,category,'#baseExcessWeighted');
	    });
	    
	    $('#pH').change(function() {
		var category = "PH";
		var input = $(this).val();		
		calculateEWSSWeightedScore(input,category,'#pHWeighted');
	    });
	    
	    $('#pAO2').change(function() {
		var category = "PAO2";
		var input = $(this).val();		
		calculateEWSSWeightedScore(input,category,'#pAO2Weighted');
	    });
	    
	    /*
	     * End MEWS/EWS stuff
	     */
	    
	    /*
	     * Add new assessment
	     */    
	     
	    $(function() {
		var newassDate = $('#newassDate'),
		    newassTime = $('#newassTime'),
		    newassDaysRef = $('#newassDaysRef'),
		    lnkID = $('#newAssessment_lnkID');
	     
		$( "#addNewAssessmentForm" ).dialog({
		  autoOpen: false,
		  height: 295,
		  width: 280,
		  modal: true,
		  buttons: {
		    "Add new assessment": function() {
		      var bValid = true;
		      
		      // Check that the assessment date given is not greater than the current date
		      var newAssessmentDate = $('#newassDate').val();		      	
		      var d = new Date();
		      var curr_date = d.getDate();
			if(curr_date <= 9)
			    curr_date = '0'+curr_date;
		      var curr_month = d.getMonth() + 1; //Months are zero based
			if(curr_month <= 9)
			    curr_month = '0'+curr_month;
		      var curr_year = d.getFullYear();
		      var currentDate = (curr_year + "-" + curr_month + "-" + curr_date);
		      
		      bValid = bValid && newAssessmentDate <= currentDate;
	     
		      if ( bValid ) {
			$.ajax({
			    type: "POST",
			    url: "addAssessment.php",
			    data: "date=" + newassDate.val() + "&time=" + newassTime.val() + "&daysref=" + newassDaysRef.val() + "&lnkID=" + lnkID.val(),
			    async: false,
			    success: function(msg){
				var bits = msg.split('	');
				if (bits[1].length > 0) {
				 $.prompt(bits[1]);
				} else {
				 //$.prompt("Everything's fine");
				 window.location.assign("assessment.php?lnkID=" + lnkID.val() + "&assessment=" + bits[0]);
				}
				/*rowID = msg;
				if (!msg || isNaN(rowID)) {
				   alert( "Error: " + msg );
				} else {
				  //alert("DLK ID="+rowID);
				  window.location.assign("assessment.php?lnkID=" + lnkID.val() + "&assessment=" + rowID);			 
				}*/	
			    },
			    error: function(XMLHttpRequest, textStatus, errorThrown) {
				 rowID = 'Invalid';
				 alert(" Status: " + textStatus + "\n Error message: "+ errorThrown); 
			     } 
			  });
			$( this ).dialog( "close" );
		      } else {
			$.prompt("Error - The date you have entered is in the future");
		      }
		    },
		    Cancel: function() {
		      $( this ).dialog( "close" );
		    }
		  },
		  close: function() {
		    //allFields.val( "" ).removeClass( "ui-state-error" );
		  }
		});
	     
		$( "#addNewAssessment" )
		  .button()
		  .click(function() {
		    $( "#addNewAssessmentForm" ).dialog( "open" );
		  });
	    });
	     
	    /*
	     * End add new assessment
	     */
	    
	    /*
	     * Copying stuff from list to textbox - physical examination etc
	     */
	    
	    $(".tag").click(function(){
		var txt = $.trim($(this).text());
		var data = $(this).data();
		var type = data['type'];
		var box = $("#" + type + "TextArea");
		box.val(box.val() + txt + ':\n\n');
	    });
	    
	    /*
	     * End phys exam copying stuff
	     */
	    
	    /*
	     * Clear Discharge Summary
	     */
	    
	    $('#clearDischargeSummary').click(function() {
		$('#dischargeTextArea').val('');
	    });
	    
	    /*
	     * Show hide sepsis based on likeliness
	     */
	    $('#sepsis').hide();
	    
	    $('#sps-sepsisOnArrival').change(function() {
		var sepsisArrival = $(this).val();
		
		if ((sepsisArrival === 'Likely') || (sepsisArrival === 'Very likely')) {
		    $('#sepsis').show();    
		}
		
		if ((sepsisArrival === 'Unlikely') || (sepsisArrival === 'Very unlikely')) {
		    $('#sepsis').hide();
		    $('#sps-sepsisSource').val('');
		}
	    });
	    
	    /*
	     * End show/hide sepsis
	     */
	    
	    /*
	     * Calculate difference between two times for delay in Caller Detail on Admissions tab
	     */	    
	    $('#adm-timeOfCall, #adm-timeOfResponse').change(function() {
		var timeOfCall = $('#adm-timeOfCall').val();
		var timeOfResponse = $('#adm-timeOfResponse').val();
		
		if ((timeOfCall !== '--:--') && (timeOfResponse !== '--:--')) {		    
		    hours = timeOfResponse.split(':')[0] - timeOfCall.split(':')[0],
		    minutes = timeOfResponse.split(':')[1] - timeOfCall.split(':')[1];
		    
		    minutes = minutes.toString().length<2?'0'+minutes:minutes;
		    if(minutes<0){ 
			hours--;
			minutes = 60 + minutes;
		    }
		    hours = hours.toString().length<2?'0'+hours:hours;
		    
		    $('#adm-delay').val(hours + ':' + minutes);
		    $('#adm-hiddenDelay').val(hours + ':' + minutes);
		    
		}
	    });
	    
	    function timeDifference(field1, field2) {
		var field1val = $(field1).val();
		var field2val = $(field2).val();
		
		if ((field1val !== '--:--') && (field2val !== '--:--')) {		    
		    var hours = field1val.split(':')[0] - field2val.split(':')[0];
		    var minutes = field1val.split(':')[1] - field2val.split(':')[1];
		    
		    minutes = minutes.toString().length<2?'0'+minutes:minutes;
		    if(minutes<0){ 
			hours--;
			minutes = 60 + minutes;
		    }
		    hours = hours.toString().length<2?'0'+hours:hours;
		    
		    var difference = hours + ':' + minutes;
		    return difference;		    
		} else return false;
	    };
	    
	    $('#assessmentHeaderStartTime, #assessmentHeaderEndTime').change(function() {
		var duration = timeDifference('#assessmentHeaderEndTime','#assessmentHeaderStartTime');
		$('#assessmentHeaderDuration').val(duration);
	    });
	    
	    /*
	     * Form focus - testing
	     * Breaks inputting minutes into time fields atm
	     */
	    /*
	    $("input").change(function() {
		var inputs = $(this).closest('form').find(':input');
		inputs.eq( inputs.index(this)+ 1 ).focus();
	    });*/
	    
	    /*
	     * NOK copy address details
	     */
	    $('#NOKSame').click(function() {
		var patAddress = $('#dmg-address').val();
		var patTown = $('#dmg-town').val();
		var patCounty = $('#dmg-county').val();
		var patPostcode = $('#dmg-postCode').val();
		var patCountry = $('#dmg-country').val();
		
		$('#dmg-NOKAddress').val(patAddress);
		$('#dmg-NOKTown').val(patTown);
		$('#dmg-NOKCounty').val(patCounty);
		$('#dmg-NOKPostCode').val(patPostcode);
		$('#dmg-NOKCountry').val(patCountry);
	    });
	    
	    /*
	     * Admission ICU show/hide stuff
	     */
	    
	    function checkICUVisible() {
		var referrer = $('#adm-referrer').val().toString().substring(0,3);
		    var isRefICU = (referrer === 'ICU') ? true : false;
		    
		    if (isRefICU === true) {
		       $('#ICUAdmission, #ICUDischarge').show();		   
		    } else {
		       $('#ICUAdmission, #ICUDischarge').hide();
		    }
	    }
	    
	    if ($('#adm-ICUAdmission').length > 0) {
		checkICUVisible();
		$('#adm-referrer').change(function() {
		    // Show/hide ICU details depends on 'refferer' starting with ICU
		    checkICUVisible();
		});
	    }
	    
	    /*
	     * Add admission research tags
	     */
	    $.fn.serializeObject = function()
	    {
		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
		    if (o[this.name] !== undefined) {
			if (!o[this.name].push) {
			    o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || '');
		    } else {
			o[this.name] = this.value || '';
		    }
		});
		return o;
	    };
	     
	    $(function() {
		  var searchDiag = $( "#searchDiag" ),
		  allFields = $( [] ).add( searchDiag ),
		  tips = $( ".validateTips" );
		  
		$( "#adm-ResearchTagsForm" ).dialog({
		  autoOpen: false,
		  height: 400,
		  width: 800,
		  modal: true,
		  buttons: {
		    "Set Tags": function() {
		      var bValid = true;
		      var data = $(this).data();
		      var page = data['page'];
		      var checkboxes = [];
		      var result = JSON.stringify($('form#admResearchTagsForm :input').serializeObject());
	     
		      if ( bValid ) {
			$.ajax({
			    type: "POST",
			    url: "admResearchTags.php",
			    data: "admResearchTags=" + result,
			    async: true,
			    success: function(msg){					      
			      $('#admResearchTagsResults').css('display','block');
			      $('#admResearchTagsResults').html(msg);
			      setTimeout(function() {
				$('#admResearchTagsResults').css('display','none');
				$('#admResearchTagsResults').html('');
			      }, 5000);
			    },
			    error: function(XMLHttpRequest, textStatus, errorThrown) {
				 rowID = 'Invalid';
				 alert(" Status: " + textStatus + "\n Error message: "+ errorThrown); 
			     } 
			  });
			//$( this ).dialog( "close" );
		      }
		    },
		    Cancel: function() {
		      $('#admResearchTagsResults').css('display','none');
		      $('#admResearchTagsResults').html('');
		      $( this ).dialog( "close" );
		    }
		  },
		  close: function() {
		    allFields.val( "" ).removeClass( "ui-state-error" );
		  }
		});
	     
		$( "#adm-researchTags" )
		  .button()
		  .click(function() {
		    $( "#adm-ResearchTagsForm" ).dialog( "open" );
		  });
	      });
	     
	    /*
	     * End admission research tags
	     */
	    
	    /*
	     * Add outreach assessment details tags
	     */
	    $.fn.serializeObject = function()
	    {
		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
		    if (o[this.name] !== undefined) {
			if (!o[this.name].push) {
			    o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || '');
		    } else {
			o[this.name] = this.value || '';
		    }
		});
		return o;
	    };
	     
	    $(function() {
		  var searchDiag = $( "#searchDiag" ),
		  allFields = $( [] ).add( searchDiag ),
		  tips = $( ".validateTips" );
		  
		$( "#ass-TagsForm" ).dialog({
		    autoOpen: false,
		    height: 400,
		    width: 800,
		    modal: true,
		    buttons: {
		      "Set Tags": function() {
			var bValid = true;
			var data = $(this).data();
			var page = data['page'];
			var checkboxes = [];
			var result = JSON.stringify($('form#ass-TagsForm :input').serializeObject());
	       
			if ( bValid ) {
			  $.ajax({
			      type: "POST",
			      url: "assTags.php",
			      data: "assTags=" + result,
			      async: true,
			      success: function(msg){					      
				$('#assTagsResults').css('display','block');
				$('#assTagsResults').html(msg);
				setTimeout(function() {
				  $('#assTagsResults').css('display','none');
				  $('#assTagsResults').html('');
				}, 5000);
			      },
			      error: function(XMLHttpRequest, textStatus, errorThrown) {
				   rowID = 'Invalid';
				   alert(" Status: " + textStatus + "\n Error message: "+ errorThrown); 
			       } 
			    });
			  //$( this ).dialog( "close" );
			}
		      },
		      Cancel: function() {
			$('#assTagsResults').css('display','none');
			$('#assTagsResults').html('');
			$( this ).dialog( "close" );
		      }
		    },
		    close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
		    }
		});
	     
		$( "#ass-TagsButton" )
		  .button()
		  .click(function() {
		    $( "#ass-TagsForm" ).dialog( "open" );
		  });
	      });
	     
	    /*
	     * End outreach assessment details tags
	     */
	    
	    /*
	     * Delete assessment from assessments.php
	     */
	    $('.deleteAssessment').click(function() {
		if(confirm('Are you sure you wish to delete this record?')) {
		    var data = $(this).data();
		    var dlkID = data['dlkid'];
		    var whichtr = $(this).closest("tr");
		    //alert("You clicked yes");
		    
		    return $.ajax({
			type: "POST",
			url: "deleteAssessment.php",
			data: "dlkID=" + dlkID
		    }).then(function(success) {
			console.debug(success);
			/*if (success) {
			    whichtr.remove();
			    return success;
			} else {
			    alert("An invalid response was given.");
			    return 'Invalid';    
			}*/ whichtr.remove();
		    });	    		    
		} else return false;	
	    });
	    
	    /*
	     * Calculate length fo care from hospital admission date to discharge data
	     */
	    
	    $('#adm-originalhospitalAdmission, #otr-outreachDischargeDate').change(function() {
		var hospitalAdmission = $('#adm-originalhospitalAdmission').val();
		var dischargeDate = $('#otr-outreachDischargeDate').val();
		var hosplen = hospitalAdmission.length;
		//console.debug("Hosp len is " + hosplen);
		//alert("Hi hosp length is " + hosplen + " and disch length is " + dischargeDate.length);
		
		// Date format is YYYY-MM-DD
		// Will always be 10 characters (well, until the year 10,000) so just use length to check dates have been filled in
		if ((hospitalAdmission.length === 10) && (dischargeDate.length === 10)) {
		    //alert("Hi2");
		    // Calculate difference in days between the two dates
		    var stay = getAge(hospitalAdmission,dischargeDate);
		    // stay[2] is day
		    $('#otr-lengthOfCare, #otr-lengthOfCareHidden').val(stay[2]);
		    
		}
	    });
	    
	    $('#cancelButton').click(function() {
		var data = $(this).data();
		var lnkid = data['lnkid'];
		var strMsg = confirm("Are you sure you want to cancel this patient?");
		if (strMsg) {
		    $.ajax({
			type: "POST",
			url: "SQLLock_Unlock.php",
			data: "lnkID=" + lnkid,
			async: false,
			success: function(msg) {
			    window.location = "patListing.php";
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
			    rowID = 'Invalid';
			    alert(" Status: " + textStatus + "\n Error message: "+ errorThrown); 
			} 
		    });
		}
	    });
	
	}); // End jQuery

	/****************************/
	function OnSave(inForm)
	{
	//if (inForm.vsHospNum){} 
	//alert("Your patient will be saved now..."); //###

	return true;
	}
	/****************************/
	
	
	function toDemographics(patientID)
	{
	var strMsg = confirm("Are you sure you want to return to demographics? Any unsaved progress will be lost.");
	if (strMsg) {
	    window.location = "patDmg.php?lnkID=" + patientID +"#page-8";
	}
	}
	
	function logOutConfirm() {
	    var strMsg = confirm("Are you sure you wish to log out? Any unsaved progress will be lost.");
	    if (strMsg) {
		window.location = "MelaClass/logoutAction.php";
	    }	
	}
 </script>	