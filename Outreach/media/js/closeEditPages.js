function CloseAndRefresh(rowID, notesID, destinationNotesID) 
     {
          if (CloseAndRefresh.arguments.length < 3) {
               // Just close the window
               self.close();
          } else {
               var row = $('#'+ rowID +'').val();
               var notes = $('#'+ notesID +'').val();
                 
               window.opener.$('#' + destinationNotesID + '\\[' + row + '\\]').val(notes);            
               //alert(('#' + destinationNotesID + '\\[' + row + '\\]'));
               //console.debug(row);
               //console.debug(notes);
               //console.debug(destinationNotesID);
               self.close();
          }
     }
     
function failedRecordLock() {
        window.opener.location.href='patListing.php';
        self.close();
    }