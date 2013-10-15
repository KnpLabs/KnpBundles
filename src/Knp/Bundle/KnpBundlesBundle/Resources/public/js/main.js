(function($) {
    var cache = {},
        lastXhr;
    if (window.location.hash.length > 0) {
        $('ul.nav-tabs li > a[data-target="' + window.location.hash + '"]').tab('show');
    }
    // collapse all bundle versions dependencies
    $('.collapse').collapse();

    $(document).bind('keydown.s keydown.f keydown./', function() {
        $('#search-query').focus();
        return false;
    });

    $('#search-query').bind('keydown.esc', function() {
        $('#search-query').blur();
        return false;
    }).autocomplete({
        minLength: 4,
        position: {
            my : "left top",
            at: "left bottom",
            offset: "0 12",
            collision: "none"
        },
        source: function(request, response) {
            var term = request.term;
            if (term in cache) {
                response(cache[term]);
                return;
            }

            lastXhr = $.ajax({
                type: "GET",
                url: $('#search-box').attr('action')+'.json?limit=5',
                dataType: 'json',
                data: {
                    q: term
                },
                success: function(data, status, xhr) {
                    cache[term] = data;
                    if (xhr === lastXhr) {
                        response(data);
                    }
                }
            });
        },
        select: function(event, ui) {
            $('#search-query').val(ui.item.name).parent().trigger('submit');
            return false;
        }
    }).data('autocomplete')._renderItem = function(ul, item) {
        return $('<li></li>')
            .data('item.autocomplete', item)
            .append('<a><img src="'+item.avatarUrl+'" width="40" height="40"><strong>'+(item.name.length > 26 ? item.name.slice(0, 24)+'...' : item.name)+'</strong><span>'+(item.description || '<em>No description</em>')+'</span></a>')
            .appendTo(ul);
    };

    $('.sidebar-developers-list img,abbr').tooltip();
    $('.symfony-versions').popover({trigger: 'hover'});
    $('.badge').popover({trigger: 'click', html: true});
    // select all code inside textarea by click
    $(document).on('click', '.badge-code', function() {
        $(this).select();
    });

    $('#add-bundle-btn').bind('click', function(event) {
        var ul = $(this).parent().parent().parent(),
            input = ul.find('#bundle');

        ul.find('.unknown').removeClass('hide');

        if (!ul.find('.alert-error').hasClass('hide') || ul.find('.alert-success').hasClass('hide')) {
            ul.find('.alert-error').addClass('hide');
            ul.find('.alert-success').addClass('hide');
        }

        if (input.attr('value') == 'http://github.com/' || input.attr('value') == 'https://github.com/') {
            ul.find('li.unknown').addClass('hide');
            ul.find('.alert-error').removeClass('hide').text('Please enter proper GitHub repository URL, and try again.');

            return event.preventDefault();
        }

        $.ajax({
                type: "POST",
                url: $(this).parent().attr('action'),
                dataType: 'json',
                data: {
                    bundle: input.attr('value')
                },
                success: function(data) {
                    ul.find('li.unknown').addClass('hide');
                    ul.find('.alert-success').html(data.message).removeClass('hide');
                }
            })
            .fail(function(xhr) {
                var data = jQuery.parseJSON(xhr.responseText);
                ul.find('li.unknown').addClass('hide');
                ul.find('.alert-error').html(data.message).removeClass('hide');
            })
            .always(function() {
                ul.attr('disabled', 'disabled');
            });

        return event.preventDefault();
    });

    var titleHeight = 0;
    $('.content-box article h3 a,.content-half article .bundle-info h2 a').each(function() {
        var code = $(this);
        var codeWidthReal = code.css('display', 'inline-block').width();
        var codeWidthStyle = code.css('display', 'block').parent().width();
        if (code.height() > 0) {
            titleHeight = code.height();
        }
        code.parent().after($("<div></div>").css('height', titleHeight));
        code.parent().css({
            width: codeWidthStyle,
            position: 'absolute'
        });

        if (codeWidthReal > codeWidthStyle) {
            code.mouseenter(function() {
                $(this).parent().stop().animate({
                    width: codeWidthReal + 8,
                    borderRadius: '6px',
                    zIndex: 6666
                }, 'fast');
            });

            code.mouseleave(function() {
                $(this).parent().stop().animate({
                    width: codeWidthStyle,
                    borderRadius: '0'
                }, 'fast');
            });
        }
    });

    $('#fav-bundle-btn').bind('click', function(event) {

        $.ajax({
            type: "POST",
            url: $(this).attr('href'),
            success: function(data, state, xhr) {
                if (data instanceof Object) {
                    $(event.currentTarget).html(' ' + data.result.label);
                    $(event.currentTarget).toggleClass('favorited', data.result.favorited);
                } else {
                    alert('You must be a logged in user to favorite this bundle.');
                    window.location.href = '/login';
                }
            }
        }).fail(function(xhr){
            alert(xhr.responseText);
        });

        return event.preventDefault();

    });

})(jQuery);
