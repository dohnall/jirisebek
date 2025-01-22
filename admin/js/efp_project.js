$(document).ready(function() {

    $.each($(".rating"), function(k1, v1) {
        var na = false;
        var ckd = $(v1).find(':checked').val();
        $(v1).addClass('use-checked-status');
        $(v1).attr('title', '1 star = very low<br />2 stars = low<br />3 stars = moderate<br />4 stars = high<br />5 stars = very high');
        $.each($(v1).children("input:radio"), function(k2, v2) {
            var cs = '';
            var ckd2 = na == true ? k2 : k2 + 1;
            if(ckd != 'undefined' && ckd2 <= ckd) {
                cs = ' checked-star';
            }
            if($(v2).val() == 0) {
                var cs2 = $(v2).attr('checked') == 'checked' ? ' checked-star' : '';
                $(v2).addClass("not-visible").after('<div class="rating-star-wrapper no-star'+cs2+'"><span class="rating-star star-na"></span></div>');
                $(v1).attr('title', 'N/A - Not Applicable<br />1 star = very low<br />2 stars = low<br />3 stars = moderate<br />4 stars = high<br />5 stars = very high');
                na = true;
            } else {
                var k = $(v2).val() - 1;
                $(v2).addClass("not-visible").after('<div class="rating-star-wrapper'+cs+'"><span class="rating-star star-'+k+'"></span></div>');
            }
        });
    });

    $(".rating-star-wrapper").live("mouseover", function() {
        $(this).prevAll('.rating-star-wrapper').andSelf().not('.no-star').addClass('active-star');
    });

    $(".rating-star-wrapper").live("mouseout", function() {
        $(this).prevUntil(':checked').andSelf().not('.no-star').removeClass('active-star');
    });

    $(".rating-star-wrapper.no-star").live("mouseover", function() {
        $(this).addClass('active-star');
    });

    $(".rating-star-wrapper.no-star").live("mouseout", function() {
        $(this).removeClass('active-star');
    });

    $(".rating-star-wrapper").live("click", function() {
        $(this).prevAll('.rating-star-wrapper').andSelf().not('.no-star').addClass('checked-star');
        $(this).prevAll('.rating-star-wrapper.no-star').removeClass('checked-star');
        $(this).nextAll('.rating-star-wrapper').removeClass('checked-star').removeClass('active-star');
        $(this).siblings(':checked').attr('checked', false);
        $(this).prev('input').attr('checked', true);
    });
    $(".rating-star-wrapper.no-star").live("click", function() {
        $(this).addClass('checked-star');
        $(this).nextAll('.rating-star-wrapper').removeClass('checked-star').removeClass('active-star').attr('checked', false);
        $(this).prev('input').attr('checked', true);
    });

    $.each($("tr.collapse"), function(k, v) {
        if($(v).hasClass('collapsed')) {
            var id = $(v).attr('id');
            $('tr.'+id).css('display', 'none');
        }
    });

    $("tr.collapse .rating-star-wrapper").live("click", function() {
        var id = $(this).parents('tr.collapse').attr('id');
        if($(this).hasClass('no-star')) {
            $('tr.'+id).css('display', 'none');
        } else {
            $('tr.'+id).css('display', 'table-row');
        }
    });

    var dates = $("#startdate, #enddate").datepicker({
    	changeMonth: true,
    	changeYear: true,
    	showAnim: "slide",
    	dateFormat: "yy-mm-dd",
		onSelect: function( selectedDate ) {
			var option = this.id == "startdate" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date );
		}
    });

    $("a.delete").click(function() {
        return window.confirm('Are you sure you want to delete this file?');
    });

    $(".comment-open").click(function() {
        if($(this).hasClass('open')) {
            $(this).removeClass('open');
            $(this).parent().next().hide();
        } else {
            $(this).addClass('open');
            $(this).parent().next().show();
        }
    });

    $(".addrows-open").click(function() {
        if($(this).hasClass('open')) {
            $(this).removeClass('open');
            $(this).parent().nextAll().hide();
        } else {
            $(this).addClass('open');
            $(this).parent().nextAll().show();
        }
    });

    $("input.additional, textarea.additional").keyup(function() {
        var v = $(this).val();
        $(this).next('.counter').text(v.length);
    });

    $("input.additional2").keyup(function() {
        var v = $(this).val();
        if($(this).hasClass('counter1')) {
            $(this).closest('tr').prev().find('.counter1').text(v.length);
        } else {
            $(this).closest('tr').prev().find('.counter2').text(v.length);
        }
    });

    $(".number-select:not(.frascati) span").click(function() {
        var arr = $(this).text().split(' ');
        $(this).parents('.relative').find('input').val(arr[0]);
    });

    $(".number-select.frascati span span").click(function() {
        var arr = $(this).text().split(' ');
        $(this).parents('.relative').find('input').val(arr[0]);
    });

    $(".methods input:checkbox").click(function() {
        var c = $(this).prop('checked');
        if(c == true) {
            $(this).val(1);
            $(this).parents('td').next().find('b').text('1');
        } else {
            $(this).val(0);
            $(this).parents('td').next().find('b').text('0');
        }
    });

    $("a.xbutton").click(function() {
        var i = parseInt($(this).siblings('b').text());

        if($(this).hasClass('plus')) {
            i++;
        } else {
            i--;
        }
        if(i <= 0) {
            i = 0;
            $(this).parent().prev().find('input:checkbox').prop('checked', false);
        } else {
            $(this).parent().prev().find('input:checkbox').prop('checked', true);
        }

        $(this).siblings('b').text(i);
        $(this).parent().prev().find('input:checkbox, input:hidden').val(i);
    });

	$(".showDesc").click(function() {
		var tr = $(this).parents("tr").next();
		if(tr.css('display') == 'table-row') {
			tr.css('display', 'none');
		} else {
			tr.css('display', 'table-row');
		}
	});

});
