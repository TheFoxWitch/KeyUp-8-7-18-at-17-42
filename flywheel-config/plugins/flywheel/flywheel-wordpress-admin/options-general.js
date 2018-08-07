window.onload = function () {
	jQuery( "input#siteurl" ).attr('readonly', true);
	jQuery( "input#siteurl" ).parent().append( "<p class='description'>This field is managed by setting the default domain on your Flywheel dashboard.</p>" );
	jQuery( "input#home" ).attr('readonly', true);
	jQuery( "input#home" ).parent().children("p").text("This field is managed by setting the default domain on your Flywheel dashboard.")
};