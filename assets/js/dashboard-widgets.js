jQuery(document).ready( function(){
    jQuery('#enable_roles_limit').on('change', function(){
        if (this.checked) {
            console.log( "checked");
            jQuery( "#enabled_roles_list input[type=checkbox]" ).prop( { "disabled": false } );
        } else {
            console.log( "unchecked");
            jQuery( "#enabled_roles_list input[type=checkbox]" ).prop( { "disabled": true, "checked": false } );
        }
    });
});
