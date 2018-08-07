window.onload = function () {
	jQuery( ".wrap ul.core-updates li" ).replaceWith('<p>Flywheel automatically updates sites to the latest version of the WordPress core. Maintaining a current and up-to-date core is one of the most important things one can do to keep their site free from malicious activity and malware, and Flywheel has you covered!</p><p>You can check to see if you’re running the most recent version of WordPress by going to the “Advanced” tab for your site in the Flywheel control panel.</p>');
	jQuery( ".wrap h3:first" ).text('WordPress Core');
};