window.onload = function () {
	jQuery( ".wrap ul.core-updates li" ).replaceWith('<p>Your service provider automatically updates sites to the latest version of the WordPress core. Maintaining a current and up-to-date core is one of the most important things one can do to keep their site free from malicious activity and malware.</p>');
	jQuery( ".wrap h3:first" ).text('WordPress Core');
};
