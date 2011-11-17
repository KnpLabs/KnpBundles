(function($) {
    //code to control keyboard navigation
    var curPosition = 0;

    $(function() {
        // Show/hide contributors
        $('.contributor-others-switch').click(function() {
            $('.user-list').removeClass('hide-contributors');
            return false;
        });

        // keyboard navigation
        if ($('.repo').length > 0) {
            var className = 'repo';
        } else if ($('.developer').length > 0) {
            var className = 'developer';
        }

        $('.' + className).first().addClass('active');

        $(document).bind('keydown', 's', focusSearch);
        $(document).bind('keydown', '/', focusSearch);
        
        $('#search-query').bind('keydown', 'esc', function() {
            $('#search-query').blur();
            return false;
        });

        $(document).bind('keydown', 'down', function() {
            moveElement(className, 'down');
            return false;
        });

        $(document).bind('keydown', 'j', function() {
            moveElement(className, 'down');
            return false;
        });


        $(document).bind('keydown', 'up', function(){
            moveElement(className, 'up');
            return false;
        });
        $(document).bind('keydown', 'k', function(){
            moveElement(className, 'up');
            return false;
        });

        $(document).bind('keydown', 'right', gotoNextPage);
        $(document).bind('keydown', 'l', gotoNextPage);

        $(document).bind('keydown', 'left', gotoPreviousPage);
        $(document).bind('keydown', 'h', gotoPreviousPage);

        $(document).bind('keydown', 'return', function() {
            if (className == 'repo') {
                document.location = $('.' + className).eq(curPosition).children('.generals').children('.repo-title').children('.name').attr('href');
            } else if (className == 'developer') {
                document.location = $('.' + className).eq(curPosition).children('.generals').children('.name').attr('href');
            }

            return false;
        });
    });

    function moveElement(className ,direction)
    {
        var elem = $('.' + className);
        var listCount = elem.size();
        if (direction == 'down') {
            if (++curPosition < listCount) {
                console.log(curPosition);
                elem.eq(curPosition-1).removeClass('active');
                elem.eq(curPosition).addClass('active');
            } else {
                curPosition--;
            }
        } else {
            if (--curPosition >= 0) {
                elem.eq(curPosition+1).removeClass('active');
                elem.eq(curPosition).addClass('active');
            } else{
                curPosition++;
            }
        }

        //check if the element is in view, if not move to it
        if ((elem.eq(curPosition).viewportOffset().top + elem.eq(curPosition).height()) > $(window).height() || elem.eq(curPosition).viewportOffset().top < 0) {
            $('html, body').animate({scrollTop: elem.eq(curPosition).offset().top}, {duration: 500, queue: false});
        }
    }
    
    // Focus on the search field
    function focusSearch()
    {
        $('#search-query').focus();
        return false;
    }
    
    function gotoNextPage()
    {
        var url;
        if (url = $('div.pagination span.next a').attr('href')) {
            document.location = url;
        }
        return false;
    }

    function gotoPreviousPage()
    {
        var url;
        if (url = $('div.pagination span.previous a').attr('href')) {
            document.location = url;
        }
        return false;
    }
})(jQuery)
