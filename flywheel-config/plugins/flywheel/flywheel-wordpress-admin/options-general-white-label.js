window.onload = function () {
	jQuery( "input#siteurl" ).attr('readonly', true);
	jQuery( "input#siteurl" ).parent().append( "<p class='description'>This field is managed by your service provider.</p>" );
	jQuery( "input#home" ).attr('readonly', true);
	jQuery( "input#home" ).parent().children("p").text("This field is managed by your service provider.")
};
