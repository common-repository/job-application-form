jQuery(document).ready(function($) {
    jQuery("#formcv").on('submit', function(e)
    {
        e.preventDefault();
        jQuery.ajax({
                url:sg_custom_script_object.ajaxurl,
                type:"POST",
                processData: false,
                contentType: false,
                data:  new FormData(this),
                success : function( response ){
                    if(response == "done"){
                        //alert('Inserted uploaded!');
                        jQuery( ".dispmsg-sg" ).append( "We will get in touch soon" );
                    }else{
                        jQuery( ".dispmsg-sg" ).append( "Issue in adding record" );
                    }
                    jQuery(".dispmsg-sg").css( "display", "block");
                    jQuery(".job-form").css( "display", "none");
                },
            });

    });
});