jQuery(document).ready( function() {

    jQuery("#ea_purge_cache").click( function(e) {
       e.preventDefault(); 
       post_id = jQuery(this).attr("data-post_id")
       nonce = jQuery(this).attr("data-nonce")
 
       jQuery.ajax({
          type : "post",
          dataType : "json",
          url : myAjax.ajaxurl,
          data : {action: "publish_purge_ajax", post_id : post_id, nonce: nonce},
          success: function(response) {
               var result = JSON.stringify(response);
               jQuery("#purge_result").html("Sections, tags and types purged!");
               console.log('Success: '+ result);
           
            },
          error: function(response) {
            var result = JSON.stringify(response);
               jQuery("#purge_result").html("error " + result);
               console.log('Error occurred: '+ response.statusText + ' ' + response.status);
               console.log(response.responseJSON);
         }
          
       })   
 
    })
 
 })