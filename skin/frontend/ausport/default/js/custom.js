jQuery(window).load(function() {
	jQuery("#tabcontent").children('div').hide(); // Initially hide all content
	jQuery("#tabs li:first").attr("id","current"); // Activate first tab
	jQuery("#tabcontent div:first").fadeIn(); // Show first tab content
    
    jQuery('#tabs a').click(function(e) {
        e.preventDefault();        
        jQuery("#tabcontent").children('div').hide(); //Hide all content
        jQuery("#tabs li").attr("id",""); //Reset id's
        jQuery(this).parent().attr("id","current"); // Activate this
        jQuery('#' + jQuery(this).attr('title')).fadeIn(); // Show content for current tab
    });
});