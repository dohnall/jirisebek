/**
 * DIAMOND FRAME CMS Admin Login JS file
 * @version 13.07.2012 15:27:36
 */
/*global jQuery */
/*jslint browser: true, devel: true, nomen: true, plusplus: true, regexp: true, sloppy: true, vars: true, white: true */
typeof jQuery === 'function' && jQuery('#main') && jQuery(document).ready(function ($) {
	$('#rezervace-table div.days').hover( /* aktivni radek */
		function(){$(this).css('z-index','1');},
		function(){$(this).css('z-index','0');}
	);
	$('#rezervace-table div.days div.actions').hover( /* aktivni radek */
		function(){$(this).css('z-index','1');},
		function(){$(this).css('z-index','0');}
	);
	$('#rezervace-table div.days div.actions div.action').hover( /* aktivni akce */
		function(){$(this).css('z-index','3');
             $(this).find('div.actionmore:hidden').fadeIn(250);},
		function(){$(this).css('z-index','2');
             $(this).find('div.actionmore:visible').fadeOut(150);}
	);
	$('#rezervace-table div.actionmore a.actionbt').click(function() {
	  $('#rezervace-form').fadeIn(350);	
	});	
	/* zavreni formulare */
	$('#rezervace-form a.close').click(function() {
	  $('#rezervace-form').fadeOut(350);	
	});
});

// end of file