/**
 * DIAMOND FRAME CMS Admin Login JS file
 * @version 15.07.2012 17:21:59
 */
/*global jQuery, ROOT, AJAX */
/*jslint browser: true, devel: true, nomen: true,  eqeq: true,plusplus: true, regexp: true, sloppy: true, vars: true, white: true, sub: true */

;jQuery(document).ready(function ($) {

    // vars
    var swfu;

    $('.select2').select2({
        dropdownAutoWidth : true,
        width: '100%',
        placeholder: ''
    });

    // tipbox
    // to prevent weird opera behavior
    $("*[title]").each(function () {
        var t = $(this),
            tt = $.trim(t.attr('title'));
        if (t.is('.notification') || t.is('#item-new') || t.is('#item-export') || t.is('#item-import')) {
            return true;
        }
        if (tt) {
            t.data('tipboxTitle', tt);
            t.attr('data-tipbox-title', tt);
            t.removeAttr('title');
        }
    });
    // register tipbox
    $("*[title],*[data-tipbox-title]").live("mouseover", function() {
        var t = $(this),
            tt = $.trim(t.attr('title'));
        if (t.is('.notification') || t.is('#item-new')) {
            return;
        }
        if (tt) {
            t.data('tipboxTitle', tt);
            t.removeAttr('title');

        }
        t.tipbox($(this).data("tipboxTitle"), "tipbox");
    });


    $("a").live("click", function() {
        var url = $(this).attr("href");
        if(url.search(ROOT) < 0 && url != 'javascript:;' && url.substr(0, 1) != "#") {
            return !window.open(this.href);
        }
    });


    var oldIndex, newIndex;
    $(".sortable_template").sortable({
        items: "tr",
        axis: "y",
        cursor: "move",
        start: function(e, ui) {
            oldIndex = $(ui.item).parent().children().index(ui.item);
            ui.placeholder.html('<td colspan="' + $(ui.item).closest('table').find('thead th').length + '">&nbsp;</td>');
        },
        update: function(e, ui) {
            newIndex = $(ui.item).parent().children().index(ui.item);
            var from = $(ui.item).find(':hidden').val();
            if(oldIndex < newIndex) {
                var i = -1;
            } else {
                var i = 1;
            }
            var to = $(ui.item).parent().find("tr:eq(" + (newIndex + i) + ") :hidden").val();
            $.get(AJAX + "settings.php", {"action":"changeTabRank","from":from,"to":to});
        },
        forcePlaceholderSize: true
    }).closest('table').find('tbody td').each(function () {
        var t = $(this);
        t.width(t.width());
    });

    $(".sortable_user").sortable({
        items: "tr",
        axis: "y",
        cursor: "move",
        start: function(e, ui) {
            oldIndex = $(ui.item).parent().children().index(ui.item);
            ui.placeholder.html('<td colspan="' + $(ui.item).closest('table').find('thead th').length + '">&nbsp;</td>');
        },
        update: function(e, ui) {
            newIndex = $(ui.item).parent().children().index(ui.item);
            var from = $(ui.item).find(':hidden').val();
            if(oldIndex < newIndex) {
                var i = -1;
            } else {
                var i = 1;
            }
            var to = $(ui.item).parent().find("tr:eq(" + (newIndex + i) + ") :hidden").val();
            $.get(AJAX + "settings.php", {"action":"changeUserRank","from":from,"to":to});
        },
        forcePlaceholderSize: true
    }).closest('table').find('tbody td').each(function () {
        var t = $(this);
        t.width(t.width());
    });

    $(".sortable_codelist").sortable({
        items: "tr",
        axis: "y",
        cursor: "move",
        start: function(e, ui) {
            oldIndex = $(ui.item).parent().children().index(ui.item);
            ui.placeholder.html('<td colspan="' + $(ui.item).closest('table').find('thead th').length + '">&nbsp;</td>');
        },
        update: function(e, ui) {
            newIndex = $(ui.item).parent().children().index(ui.item);
            var from = $(ui.item).find(':hidden').val();
            if(oldIndex < newIndex) {
                var i = -1;
            } else {
                var i = 1;
            }
            var to = $(ui.item).parent().find("tr:eq(" + (newIndex + i) + ") :hidden").val();
            $.get(AJAX + "settings.php", {"action":"changeCodelistRank","from":from,"to":to});
        },
        forcePlaceholderSize: true
    }).closest('table').find('tbody td').each(function () {
        var t = $(this);
        t.width(t.width());
    });

    $(".sortable_codelist_record").sortable({
        items: "tr",
        axis: "y",
        cursor: "move",
        start: function(e, ui) {
            oldIndex = $(ui.item).parent().children().index(ui.item);
            ui.placeholder.html('<td colspan="' + $(ui.item).closest('table').find('thead th').length + '">&nbsp;</td>');
        },
        update: function(e, ui) {
            newIndex = $(ui.item).parent().children().index(ui.item);
            var from = $(ui.item).find(':hidden').val();
            if(oldIndex < newIndex) {
                var i = -1;
            } else {
                var i = 1;
            }
            var to = $(ui.item).parent().find("tr:eq(" + (newIndex + i) + ") :hidden").val();
			$.get(AJAX + "codelist.php", {"action":"changeCodelistRecordRank","from":from,"to":to});
        },
        forcePlaceholderSize: true
    }).closest('table').find('tbody td').each(function () {
        var t = $(this);
        t.width(t.width());
    });

	$(".confirmation").click(function() {
		var THIS = $(this);
		$.get(AJAX + "reservation.php", {"action":"confirmation","rid":THIS.val()});
	});

	$(".ngroup_cbx").click(function() {
		var THIS = $(this);
		var checked = THIS.prop('checked');
		if(THIS.hasClass('ngroup_cbx_first')) {
			$(".ngroup_cbx").not('.ngroup_cbx_first').prop('checked', !checked);
		} else {
			$(".ngroup_cbx.ngroup_cbx_first").prop('checked', false);
		}
		if($(".ngroup_cbx:checked").size() == 0) {
			$(".ngroup_cbx.ngroup_cbx_first").prop('checked', true);
		}
	});

	$(".nuser_ngroup").click(function() {
		var THIS = $(this);
		$.post(AJAX + "newsletter.php", {"action":"setUserGroup","str":THIS.val()});
	});

    // tabs
    $("#content-tabs").tabs({
        fx: {
            opacity: 'toggle',
            duration: 80
        },
        cache: true,
        cookie: {expires: 30},
        select: function (e, ui) {
            // to prevent collapsing of empty wrapper between hiding an old and showing new content
            var w = $(e.target).closest('.ui-tabs');
            w.css('min-height', w.height());
        },
        show: function (e, ui) {
            // remove style preventing collapsing of empty wrapper between hiding an old and showing new content
            var w = $(e.target).closest('.ui-tabs');
            w.css('min-height', '0');
        },
        load: function(e, ui) {
            $('.select2').select2({
                dropdownAutoWidth : true,
                width: '100%',
                placeholder: ''
            });

            $("#relations :radio").click(function() {
                $.get(AJAX + "content.php",
                    {
	                    action:"mainRelation",
	                    id:$("#parent_id").val(),
	                    relation:$(this).val()
                    },
                    function() {
                        location.reload();
                    }
                );
            });

            // new relation dialog (not system message)
            $("#relation_new").click(function() {
                $("#relation-new").dialog({
                    width: 'auto',
                    modal: true,
                    buttons: {
                        OK: function() {
                            $("#relation-new form").submit();
                        },
                        Cancel: function() {
                            $(this).dialog("close");
                        }
                    },
                    resizable: false
                });
            });

            // sortables
            $(".sortable_content").sortable({
                items: "tr",
                axis: "y",
                cursor: "move",
                start: function(e, ui) {
                    oldIndex = $(ui.item).parent().children().index(ui.item);
                    ui.placeholder.html('<td colspan="' + $(ui.item).closest('table').find('thead th').length + '">&nbsp;</td>');
                },
                update: function(e, ui) {
                    newIndex = $(ui.item).parent().children().index(ui.item);
                    var id = $(".sortable tr:eq(" + newIndex + ") :checkbox").val();
                    if(oldIndex < newIndex) {
                        var i = -1;
                    } else {
                        var i = 1;
                    }
                    var to = $(".sortable tr:eq(" + (newIndex + i) + ") :checkbox").val();
                    var parent = $("#parent_id").val();
                    $.get(AJAX + "content.php", {"action":"changeRank","id":id,"parent":parent,"to":to});
                },
                forcePlaceholderSize: true
            }).closest('table').find('tbody td').each(function () {
                var t = $(this);
                t.width(t.width());
            });

			$(".visibility1").die().live("click", function() {
				if($(".visibility1:checked").size() == 0) {
					$(".visibility2:first").attr("checked", true);
				} else {
					$(".visibility2").attr("checked", false);
				}
			});

			$(".visibility2").die().live("click", function() {
				if($(".visibility2:checked").size() == 0) {
					$(".visibility1").attr("checked", true);
				} else {
					$(".visibility1").attr("checked", false);
				}
			});

			loadContent();
        }
    });

	loadContent();

    // new menu item dialog (not system message)
    $(".newmenuitem").click(function() {
        $("#parent_id").val($(this).siblings('.menuitemid').val());
        $("#item-new").dialog({
            autoOpen: true,
            width: 540,
            modal: true,
            buttons: {
                OK: function() {
                    $("#item-new form").submit();
                },
                Cancel: function() {
                    $(this).dialog("close");
                }
            },
            resizable: false
        });
    });

    // new item dialog (not system message)
    $("#item_new").click(function() {
        $("#item-new").dialog({
            autoOpen: true,
            width: 540,
            modal: true,
            buttons: {
                OK: function() {
                    $("#item-new form").submit();
                },
                Cancel: function() {
                    $(this).dialog("close");
                }
            },
            resizable: false
        });
    });

    $("#item_export").click(function() {
        $("#item-export").dialog({
            autoOpen: true,
            width: 540,
            modal: true,
            buttons: {
                OK: function() {
                    $("#item-export form").submit();
                    $(this).dialog("close");
                },
                Cancel: function() {
                    $(this).dialog("close");
                }
            },
            resizable: false
        });
    });

    $("#item_import").click(function() {
        $("#item-import").dialog({
            autoOpen: true,
            width: 540,
            modal: true,
            buttons: {
                OK: function() {
                    $("#item-import form").submit();
                },
                Cancel: function() {
                    $(this).dialog("close");
                }
            },
            resizable: false
        });
    });

    $("#tree a.ui-icon-circlesmall-plus").live("click", function() {
        var THIS = this;
        if($(THIS).siblings("ul").size() == 0) {
            var id = $(THIS).attr("id").substr(1);
            $.ajax({
                url: AJAX + "content.php",
                data: {"action":"tree", "id":id}, 
                dataType: "html",
                async: false,
                success: function(html){
                    $(THIS).parents('li:first').append(html);
                }
            });
        }
        $(THIS).siblings("ul").slideDown();
        $(THIS).removeClass("ui-icon-circlesmall-plus").addClass("ui-icon-circlesmall-minus");
    });

    $("#tree a.ui-icon-circlesmall-minus").live("click", function() {
        $(this).removeClass("ui-icon-circlesmall-minus").addClass("ui-icon-circlesmall-plus");
        $(this).siblings("ul").slideUp();
    });

    $(".user_sections a:has(.ui-icon-circlesmall-plus)").live("click", function() {
        var THIS = this;
        if($(THIS).siblings("ul").size() == 0) {
            var arr = $(THIS).attr("id").split("_");
            var id = arr[1];
            var domain = arr[2];
            var lang = arr[3];
            $.ajax({
                url: AJAX + "content.php",
                data: {"action":"user_sections", "id":id, "domain":domain, "lang":lang}, 
                dataType: "html",
                async: false,
                success: function(html){
                    $(THIS).parents('li:first').append(html);
                }
            });
        }
        $(THIS).siblings("ul").slideDown();
        $(THIS).children(".ui-icon-circlesmall-plus").
            removeClass("ui-icon-circlesmall-plus").
            addClass("ui-icon-circlesmall-minus");
    });

    $(".user_sections a:has(.ui-icon-circlesmall-minus)").live("click", function() {
        $(this).children(".ui-icon-circlesmall-minus").
            removeClass("ui-icon-circlesmall-minus").
            addClass("ui-icon-circlesmall-plus");
        $(this).siblings("ul").slideUp();
    });

    $(".user_sections input:checkbox").live("click", function() {
        $(this).parents('ul').siblings('input:checkbox').attr('checked', false);
        $(this).siblings('ul').find('input:checkbox').attr('checked', false);
    });

    $("#content_search").click(function() {
        $("#content-search").slideToggle();
    });


    $(".delete").live("click", function() {
        var url = $(this).attr("href");

        // destroy existing dialog...
        $(".ui-dialog").dialog("destroy").remove();
        $("#dialog-confirm, #dialog-message").remove();

        // prepare new dialog text
        $("body").append('<div id="dialog-confirm" class="alert left-icon" data-message-class="alert left-icon" title="'
            + dictionary['delete'] + '"><p>'
            + dictionary['delete_item']
            + '</p></div>');

        // ... and create new
        // delete confirm dialog
        $("#dialog-confirm").dialog({
            modal: true,
            resizable: false,
            buttons: {
                OK: function() {
                    window.location = url;
                    return true;
                },
                Cancel: function() {
                    $("#dialog-confirm").remove();
                }
            },
            dialogClass: $('#dialog-confirm').data('messageClass')
        });
        return false;
    });

    $(".cancel").live("click", function() {
        var url = $(this).attr("href");

        // destroy existing dialog...
        $(".ui-dialog").dialog("destroy").remove();
        $("#dialog-confirm, #dialog-message").remove();

        // prepare new dialog text
        $("body").append('<div id="dialog-confirm" class="alert left-icon" data-message-class="alert left-icon" title="'
            + dictionary['cancel'] + '"><p>'
            + dictionary['cancel_item']
            + '</p></div>');

        // ... and create new
        // delete confirm dialog
        $("#dialog-confirm").dialog({
            modal: true,
            resizable: false,
            buttons: {
                OK: function() {
                    window.location = url;
                    return true;
                },
                Cancel: function() {
                    $("#dialog-confirm").remove();
                }
            },
            dialogClass: $('#dialog-confirm').data('messageClass')
        });
        return false;
    });

    // dialog contained in loaded tab HTML
    if(document.getElementById('dialog-message')) {
        var dialogTimeout;
        $("#dialog:ui-dialog").dialog("destroy");
        $("#dialog-message").dialog({
            modal: true,
            resizable: false,
            buttons: {
                OK: function() {
                    $(this).dialog("close");
                    clearTimeout(dialogTimeout);
                }
            },
            dialogClass: $('#dialog-message').data('messageClass'),
            open: function () {
                dialogTimeout = setTimeout(function () {
                    $('.ui-dialog, .ui-widget-overlay').fadeOut(230, function () {
                       $('.ui-icon-closethick').click();
                    });
                }, 3300);
                $('.ui-widget-overlay').on('click', function () {
                    $('.ui-dialog, .ui-widget-overlay').fadeOut(120, function () {
                       $('.ui-icon-closethick').click();
                    });
                })
            },
            dragStart: function () {
                clearTimeout(dialogTimeout);
            }
        });
    }


    if(document.getElementById('accordion')) {
        $("#accordion").accordion({
            autoHeight: false,
            navigation: true,
            animated: false,
            collapsible: false
        });
    }

    $(".enable_domain").click(function() {
        var id = $(this).attr('id');
        if(this.checked) {
            $("."+id).attr('disabled', false);
        } else {
            $("."+id).attr('disabled', true);
        }
    });

    $("#checkall").live("click", function() {
        var id = $(this).attr('id');
        if(this.checked) {
            $("."+id).attr('checked', true);
        } else {
            $("."+id).attr('checked', false);
        }
    });

    // file import dialog (not system message)
    $("#dictionary_import").click(function() {
        $("#dictionary-import").dialog({
            width: 440,
            modal: true,
            buttons: {
                Import: function() {
                    $("#dictionary-import form").submit();
                },
                Cancel: function() {
                    $(this).dialog("close");
                }
            },
            close: function() {
                allFields.val("").removeClass("ui-state-error");
            }
        });
    });


    $("#change_domain, #content_lang, #common_action").live("change", function() {
        $(this).parents("form").submit();
    });

    $(".content_list_rank").live("change", function() {
        var THIS = this;
        var pid = $("#parent_id").val();
        var id = $(this).attr("id").substr(5);
        var to = $(this).val();
        $.get(AJAX + "content.php", {"action":"changeRank2","id":id,"parent":pid,"to":to}, function() {
            location.reload();
        });
    });

    $("#content table.list tr").live("mouseover", function() {
        $(this).addClass("over");
    });

    $("#content table.list tr").live("mouseout", function() {
        $(this).removeClass("over");
    });

    $("table.list td:not(.action):has(a)").live("mouseover", function() {
        $(this).css('cursor', 'pointer');
    });

    $("table.list td:not(.action):has(a)").live("click", function() {
        document.location = $(this).find("a:first").attr('href');
    });

	$(".post p").click(function() {
		var id = $(this).parent().siblings("input:hidden").val();

		if($(this).parent().hasClass("subject")) {
            $(this).after('<input class="blur" type="text" name="subject" value="'+$(this).html()+'" />').css("display", "none");
            $(".blur").focus();
		}
		if($(this).parent().hasClass("message")) {
			var val = $(this).html();
            val = val.replace(/<br>/g, String.fromCharCode(13));
            $(this).after('<textarea class="blur" name="message" rows="3" cols="50">'+val+'</textarea>').css("display", "none");
            $(".blur").focus();
		}

        $(".post .blur").bind("blur", function() {
            var val = $(this).val();
            val = val.replace(/\n/g, '<br>');

            $.post(AJAX + "forum.php", {"col":$(this).attr('name'),"val":val,"id":id,"action":"update"});

            $(this).prev().html(val).css("display", "block");
            $(this).remove();
        });
	});

	$(".post select").change(function() {
		var THIS = this;
		var id = $(THIS).parent().siblings("input:hidden").val();
		$.post(AJAX + "forum.php", {"col":$(THIS).attr('name'),"val":$(THIS).val(),"id":id,"action":"updateStatus"}, function() {
			$(THIS).parents(".post.new").remove();
		});
	});

	$(".post .deletePost").click(function() {
		var THIS = this;
		var id = $(THIS).parent().siblings("input:hidden").val();

        // destroy existing dialog...
        $(".ui-dialog").dialog("destroy").remove();
        $("#dialog-confirm, #dialog-message").remove();

        // prepare new dialog text
        $("body").append('<div id="dialog-confirm" class="alert left-icon" data-message-class="alert left-icon" title="'
            + dictionary['delete'] + '"><p>'
            + dictionary['delete_item']
            + '</p></div>');

        // ... and create new
        // delete confirm dialog
        $("#dialog-confirm").dialog({
            modal: true,
            resizable: false,
            buttons: {
                OK: function() {
					$.post(AJAX + "forum.php", {"id":id,"action":"delete"}, function() {
						$(THIS).parents(".post").remove();
						$("#dialog-confirm").remove();
					});
                    return true;
                },
                Cancel: function() {
                    $("#dialog-confirm").remove();
                }
            },
            dialogClass: $('#dialog-confirm').data('messageClass')
        });

	});

	$(document).on('click', '.ngroup', function() {
		var THIS = $(this);
		$('#ngroup_id').val(THIS.attr('id').substr(1));
		$('#ngroupname').val(THIS.text());
		$('#item_new').click();
	});

	$("#cid_colour").die().live("change", function() {
		var THIS = $(this);
		if(THIS.val() != 0) {
			if(THIS.next('span').size() > 0) {
				THIS.next('span').css('background-color', '#'+THIS.val());
			} else {
				THIS.after('<span style="display:block; margin:3px 0 0 10px; width:20px; height:20px; border:solid 1px #000; float:left; background-color:#'+THIS.val()+';"></span>');
			}
		} else {
			THIS.next('span').remove();
		}
	});

    // header clock
    setInterval(function() {
        var date = new Date();
        var year = date.getFullYear();
        var month = date.getMonth() + 1 < 10 ? "0" + parseInt(date.getMonth() + 1) : parseInt(date.getMonth() + 1);
        var day = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();
        var hour = date.getHours() < 10 ? "0" + date.getHours() : date.getHours();
        var minute = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();
        var second = date.getSeconds() < 10 ? "0" + date.getSeconds() : date.getSeconds();
        
        var time = day + "." + month + "." + year + " " + hour + ":" + minute + ":" + second;
        $("#current_time").html(time);
    }, 1000);

    function imageManagerLoadFiles() {
        $("#gallery ul").replaceWith('<ul><li class="loader"></li></ul>');
        $.ajax({
            url: AJAX + "content.php",
            data: {"action":"imageManagerLoadFiles"}, 
            dataType: "html",
            async: true,
            success: function(html){
                $("#gallery ul").replaceWith(html);
            }
        });
    }

    function fileManagerLoadFiles() {
        $("#listFiles table").replaceWith('<ul><li class="loader"></li></ul>');
        $.ajax({
            url: AJAX + "content.php",
            data: {"action":"fileManagerLoadFiles"}, 
            dataType: "html",
            async: true,
            success: function(html){
                $("#listFiles ul").replaceWith(html);
            }
        });
    }

	function loadContent() {
		$('textarea.html').tinymce({
			script_url : JS + 'wysiwyg/tiny_mce_src.js',
			language : LANG == 'cz' ? 'cs' : LANG,
			entity_encoding : 'raw'
		});
	    $(".datetype").datepicker({
	        dateFormat: "yy-mm-dd",
	        showAnim: "fadeIn",
	        duration: 90,
	        changeMonth: true,
	        changeYear: true,
	        showWeek: true,
	        firstDay: 1,
	        yearRange: "c-100:c+10"
	    });
	    $(".datetimetype").datetimepicker({
	        dateFormat: "yy-mm-dd",
	        showAnim: "fadeIn",
	        duration: 90,
	        changeMonth: true,
	        changeYear: true,
	        showWeek: true,
	        firstDay: 1,
	        yearRange: "c-100:c+10"
	    });
	
	    $(".gallery.sortable, .listFiles.sortable").sortable({
	        items: "li",
	        axis: "y",
	        cursor: "move",
	        forcePlaceholderSize: true
	    });
	
	    $(".gallery .file a").die().live("click", function() {
	        var THIS = $(this);
	        $("body").append('<div id="imageBox" title="'+dictionary['image_manager']+'"></div>');
	        $("#imageManagerReload").die().live("click", function() {
	            imageManagerLoadFiles();
	        });
	        $.ajax({
	            url: AJAX + "content.php",
	            data: {"action":"imageManager"},
	            dataType: "html",
	            async: false,
	            success: function(html){
	                $("#imageBox").html(html);
			var settings = {
				file_types : "*.jpg;*.jpeg;*.png;*.gif",
				file_types_description : dictionary['images'],
				upload_complete_handler : uploadCompleteImage,
			};
	                swfu = new SWFUpload(settings);
	                imageManagerLoadFiles();
	            }
	        });
	
	        // image box dialog (not system message)
	        $("#imageBox").dialog({
	            width: 1000,
	            height: 560,
	            modal: true,
	            buttons: {
	                OK: function() {
	                    var file = $("#gallery [name='file']").val();
	                    THIS.find('img').attr('src', FILES + '120/' + file);
	                    THIS.parents('li').find('.fileFile').val(file);
	                    $(this).dialog("close");
	                },
	                Cancel: function() {
	                    $(this).dialog("close");
	                }
	            },
	            resizable: true,
	            minWidth: 500,
	            minHeight: 280,
	            maxWidth: 1500,
	            maxHeight: 1120
	        });
	    });
	
	
	    $(".listFiles .file a").die().live("click", function() {
	        var THIS = $(this);
	        $("body").append('<div id="fileBox" title="'+dictionary['file_manager']+'"></div>');
	        $("#fileManagerReload").die().live("click", function() {
	            fileManagerLoadFiles();
	        });
	        $.ajax({
	            url: AJAX + "content.php",
	            data: {"action":"fileManager"},
	            dataType: "html",
	            async: false,
	            success: function(html){
	                $("#fileBox").html(html);
			var settings = {
				file_types_description : dictionary['files'],
				upload_complete_handler : uploadCompleteFile,
			};
	                swfu = new SWFUpload(settings);
	                fileManagerLoadFiles();
	            }
	        });
	
	        // file box dialog (not system message)
	        $("#fileBox").dialog({
	            width: 770,
	            height: 460,
	            modal: true,
	            buttons: {
	                OK: function() {
	                    var file = $("#listFiles [name='file']").val();
	                    THIS.text(file);
	                    THIS.parents('li').attr('data-file', file).find('.fileFile').val(file);
	                    $(this).dialog("close");
	                },
	                Cancel: function() {
	                    $(this).dialog("close");
	                }
	            },
	            resizable: true,
	            minWidth: 350,
	            minHeight: 230,
	            maxWidth: 1050,
	            maxHeight: 990
	        });
	
	
	    });
	
	    $("#gallery ul li a").die().live("click", function() {
	        $("#gallery ul li a").removeClass('active');
	        $(this).addClass('active');
	        var file = $(this).find('img').attr('alt');
	        $("#gallery [name='file']").val(file);
	    }).live('dblclick', function () {
	        $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane button').first().trigger('click');
	    });
	
	    $("#listFiles tbody tr").die().live("click", function() {
	        $("#listFiles tbody tr").removeClass('active');
	        $(this).addClass('active');
	        var file = $(this).find('a').text();
	        $("#listFiles [name='file']").val(file);
	    }).live('dblclick', function () {
	        $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane button').first().trigger('click');
	    });
	
	    $(".gallery .cleanFile").die().live("click", function() {
	        $(this).parents('li').find('.file img').attr('src', DESIGN + "noimage.png");
	        $(this).parents('li').find('.fileFile').val('');
	        $(this).parents('li').find('.fileAlt').val('');
	        $(this).parents('li').find('.fileDescription').val('');
	    });
	
	    $(".listFiles .cleanFile").die().live("click", function() {
	        $(this).parents('li').find('.file').text(dictionary['nofile']);
	        $(this).parents('li').find('.fileFile').val('');
	        $(this).parents('li').find('.fileDescription').val('');
	    });
	
	    $(".gallery .deleteFile").die().live("click", function() {
	        if($(this).parents('li').siblings('li').size() > 0) {
				$(this).parents('li').remove();
			} else {
		        $(this).parents('li').find('.file img').attr('src', DESIGN + "noimage.png");
		        $(this).parents('li').find('.fileFile').val('');
		        $(this).parents('li').find('.fileAlt').val('');
		        $(this).parents('li').find('.fileDescription').val('');
			}
	    });

	    $(".listFiles .deleteFile").die().live("click", function() {
	        if($(this).parents('li').siblings('li').size() > 0) {
				$(this).parents('li').remove();
			} else {
		        $(this).parents('li').find('.file').text(dictionary['nofile']);
		        $(this).parents('li').find('.fileFile').val('');
		        $(this).parents('li').find('.fileDescription').val('');
			}
	    });

	    $(".nextImage").die().live("click", function() {
	        var code = $(this).siblings('[name="code"]').val();
	        var html = '';
	        html+= '<li>';
	        html+= '<div class="file"><a href="javascript:;"><img src="'+DESIGN+'noimage.png" alt="" /></a></div>';
	        html+= '<div class="fileInfo">';
	        html+= '<input type="hidden" class="fileFile" name="item[file]['+code+'][file][]" value="" />';
	        html+= '<input type="hidden" name="item[file]['+code+'][download][]" value="0" />';
	        html+= '<input type="file" name="file['+code+'][]" multiple />';
	        html+= '<label><input type="text" class="fileAlt" name="item[file]['+code+'][alt][]" value="" /> '+dictionary['alt']+'</label>';
	        html+= '<label><input type="text" class="fileDescription" name="item[file]['+code+'][description][]" value="" /> '+dictionary['description']+'</label>';
	        html+= '</div>';
	        html+= '<div class="fileControl">';
	        html+= '<a href="javascript:;" class="deleteFile">'+dictionary['delete']+'</a>';
	        html+= '</div>';
	        html+= '<div class="clr"><hr /></div>';
	        html+= '</li>';
	        $(this).siblings('ol').append(html);
	    });
	
	    $(".nextFile").die().live("click", function() {
	        var code = $(this).siblings('[name="code"]').val();
	        var html = '';
	        html+= '<li>';
	        html+= '<div class="file"><a href="javascript:;">'+dictionary['nofile']+'</a></div>';
	        html+= '<div class="fileInfo">';
	        html+= '<input type="hidden" class="fileFile" name="item[file]['+code+'][file][]" value="" />';
	        html+= '<input type="hidden" name="item[file]['+code+'][alt][]" value="" />';
	        html+= '<input type="hidden" name="item[file]['+code+'][download][]" value="" />';
	        html+= '<input type="file" name="file['+code+'][]" multiple />';
	        html+= '<label><input type="text" class="fileDescription" name="item[file]['+code+'][description][]" value="" /> '+dictionary['description']+'</label>';
	        html+= '</div>';
	        html+= '<div class="fileControl">';
	        html+= '<a href="javascript:;" class="deleteFile">'+dictionary['delete']+'</a>';
	        html+= '</div>';
	        html+= '<div class="clr"><hr /></div>';
	        html+= '</li>';
	        $(this).siblings('ol').append(html);
	    });
	}

});



// end of file