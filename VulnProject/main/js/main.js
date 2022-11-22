function get_language() {
    var cookie = document.cookie.trim();
    var tmp = cookie.match(/(^|;\s*)language=(\w+)/);
    if (tmp) {
        return tmp[2];
    }
}

function password_confirm() {
	if ($("#password").val() != $("#password-confirm").val()) {
        var language = get_language();
        alert(language === 'ja_JP'
              ? "パスワードが一致しません。"
              : "Password mismatch!");
        return false;
    }
	return true;
}

function show_email_suggestion(email) {
    var language = get_language();
    var re = /(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/;
    var match = re.exec(email);
    if (match) {
        alert(language === 'ja_JP'
              ? `"${match[0]}"を試してください。`
              : `Try "${match[0]}" instead!`);
    }
    else {
        alert(language === 'ja_JP'
              ? 'サジェスチョンは見つかりませんでした。'
              : 'No suggestion found.');
    }
}

$(document).ready(function () {
    $(".ts-sidebar-menu li a").each(function () {
        if ($(this).next().length > 0) {
            $(this).addClass("parent");
        };
    })
    var menux = $('.ts-sidebar-menu li a.parent');
    $('<div class="more"><i class="fa fa-angle-down"></i></div>').insertBefore(menux);

    $('.more').click(function () {
        $(this).parent('li').toggleClass('open');
    });

	$('.parent').click(function (e) {
		e.preventDefault();
        $(this).parent('li').toggleClass('open');
    });

    $('.menu-btn').click(function () {
    $('nav.ts-sidebar').toggleClass('menu-open');
    });
	
    if ($("#avatar").length > 0) {
        $("#avatar").fileinput({
            overwriteInitial: true,
            maxFileSize: 1500,
            showClose: false,
            showCaption: false,
            showBrowse: false,
            browseOnZoneClick: true,
            removeLabel: '',
            removeIcon: '<i class="glyphicon glyphicon-remove"></i>',
            removeTitle: 'Cancel or reset changes',
            elErrorContainer: '#avatar-errors-2',
            msgErrorClass: 'alert alert-block alert-danger',
            layoutTemplates: {main: '{preview} {remove} {browse}'},
            allowedFileExtensions: ["jpg", "png", "jpeg"]
        });
    }

    $("#password").on("input", function (e) {
        if ($(this).val() != $("#password-confirm").val()) {
            $("#password-confirm").removeClass("valid").addClass("invalid");
        } else {
            $("#password-confirm").removeClass("invalid").addClass("valid");
        }
    });

    $("#password-confirm").on("input", function(e) {
        if ($("#password").val() != $(this).val()) {
            $(this).removeClass("valid").addClass("invalid");
        } else {
            $(this).removeClass("invalid").addClass("valid");
        }
    })

    if ($(".alert-success strong").length == 1) {
        setInterval(function() {
            var count = Number($(".alert-success strong")[0].innerHTML);
            if (count > 0) {
                count = count - 1;
            } else {
                document.location = "/login.php";
            }
            $(".alert-success strong")[0].innerHTML = count;
        }, 1000);
    }

    $("#secret-toggle").on("click", function(e) {
        var input = $("#secret");
        if (input.attr("type") === "password") {
            input.attr("type", "text");
        } else {
            input.attr("type", "password");
        }
    })

    $("#file-box").fileinput({
        showUpload: false,
        browseOnZoneClick: true,
        maxFileSize: 2000,
    });

    $("#file-box").on("change", function(e) {
        var file = $("#file-box")[0].files[0];
        
        $("#file-name")[0].value = file.name;
        $("#file-size")[0].innerHTML = file.size + " Bytes";
        $("#file-type")[0].innerHTML = file.type;

        $("#file-info").collapse("show");
    })

    $(".fileinput-remove").on("click", function(e) {
        $("#file-info").collapse("hide");
    })

    if ($("#file-status").length == 1) {
        $("#file-status")[0].value = $("#file-status")[0].getAttribute("value");
    }

    $(".cell.clickable").on("click", function(e) {
        var href = $(e.currentTarget).data("href");
        window.location = href;
    })

    $("#update-download-code").on("click.UPDATE", function(e) {
        var language = get_language();
        e.preventDefault();
        var download_url = $("#download-url span")[0];
        var download_code = /code=(.+)/.exec(download_url.innerHTML)[1];
        download_url.innerHTML = download_url.innerHTML.replace(download_code, "");
        $("#download-url").append('<input style="width: 30%" id="download-code" type="text" class="form-control" value=' + download_code + ' name="download_code"></input>');
        $("#download-code").focus();
        $("#update-download-code")[0].innerHTML =
            language === "ja_JP" ? "URLを保存する" : "Submit URL change";
        $("#update-download-code").off("click.UPDATE");
        $("#update-download-code").on("click.SUBMIT", function(e) {
            $("form")[0].submit();
        })
    })

    $.each($(".comment-content p"), function(key, comment) {
        $(comment).data("original-value", comment.innerHTML);
        comment.innerHTML = parse_image_block(comment.innerHTML);
    })

});

function parse_image_block(s) {
    var matches = s.match(/\[image\](.+?)\[\/image\]/);
    while (matches != null) {
        s = s.replace(matches[0], '<img src="' + matches[1] + '" style="max-width: 200px;max-height:200px;padding:10px 10px 10px 10px">');
        var matches = s.match(/\[image\](.+?)\[\/image\]/);
    }

    return s;
}

function edit_comment(comment_id) {
    var comment_form = $("#comment-form")[0];
    comment_form.action.value = "edit-comment";

    comment_form.content.value = $($("#comment-" + comment_id + " p")[0]).data("original-value");
    

    $('<input>').attr({
        type: 'hidden',
        name: 'comment_id',
        value: comment_id
    }).appendTo(comment_form);
    $("#comment").focus();
}