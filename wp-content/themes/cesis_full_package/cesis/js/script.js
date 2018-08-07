/*jslint browser: true*/ /*global  $*/
jQuery(document).ready(function ($) {

  $("a[href$='http://keyup.flywheelsites.com/#contact']").click(function () {
    $("header").toggleClass("overlay_menu_on", false, 10);
    $(".cesis_menu_button").toggleClass("open", false, 10);
    $(".header_overlay").toggleClass("overlay_on", false, 10);
    $(".header_mobile").toggle("display:none;");
    $(".cesis_mobile_menu_switch").toggleClass("open", false, 10);
  });
  $("a[href$='http://keyup.flywheelsites.com/#about']").click(function () {
    $("header").toggleClass("overlay_menu_on", false, 10);
    $(".cesis_menu_button").toggleClass("open", false, 10);
    $(".header_overlay").toggleClass("overlay_on", false, 10);
    $(".header_mobile").toggle("display:none;");
    $(".cesis_mobile_menu_switch").toggleClass("open", false, 10);
  });

});

// $('header, .cesis_menu_button, .header_overlay').click(function () {
//   if (!$(this).next().is(':visible')) {
//     $('.toggleOff').slideToggle();
//   }
// });