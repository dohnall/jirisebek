/***************************/
//@Author: Adrian "yEnS" Mato Gondelle & Ivan Guardado Castro
//@website: www.yensdesign.com
//@email: yensamg@gmail.com
//@license: Feel free to use it, but keep this credits please!
// adapded for DIAMOND FRAME CMS
// @version 20.07.2012 22:07:16
/***************************/
jQuery.fn.tipbox = function (content, className) {

	jQuery.fn.tipbox.created.id = "tipBox";
	$("body").append(jQuery.fn.tipbox.created);

	//set some properties for the tipBox division
	var tipBox = $(jQuery.fn.tipbox.created);
	tipBox.css({"position":"absolute","display":"none"});
	var w = $(document).width();

	//functions
	function tipBoxShow(e) {
		var l = w - e.pageX < 216 ? e.pageX-200 : e.pageX;
		tipBox.css({/*"display":"block", */"top":e.pageY+25, "left":l}).stop().fadeTo(190,1);
	}
	function tipBoxHide(){
		tipBox.stop().fadeTo(1, 0, function () {
            $(this).hide();
        });//css({"display":"none"});
	}

	//events for each element
	this.each(function () {
		$(this).mousemove(function (e) {
            var t = $(this);
            if (!content) {
                return;
            }
            // make it economical
            if (tipBox.is(':visible')) {
                return;
            }

            // show tipbox
			tipBoxShow(e);

			//update the content
			tipBox.html(content);

			//remove all classes for the tipBox before add a new one and to avoid the "append class"
			tipBox.removeClass();
			if (className) {
                tipBox.addClass(className);
            }

		});

		$(this).mouseout(function () {
            var t = $(this);
			tipBoxHide();
		});

	});	
};

//create the element (avoiding create multiple divisions for the tipBox)
jQuery.fn.tipbox.created = document.createElement("div");

