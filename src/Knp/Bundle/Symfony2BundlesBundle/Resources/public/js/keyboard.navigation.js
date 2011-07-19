//code to control keyboard navigation
var curPosition = 0;

function onload() {

    $('.repo').first().css('border','1px solid blue');

    $(document).bind('keydown', 's', function() { $('#search-query').focus(); return false; });

    $(document).bind('keydown', 'down', function() {
        moveElement('repo', 'down');

        return false;
    });

    $(document).bind('keydown', 'j', function() {
        moveElement('repo', 'down');

        return false;
    });


    $(document).bind('keydown', 'up', function(){
        moveElement('repo', 'up');

        return false;
    });
    $(document).bind('keydown', 'k', function(){
        moveElement('repo', 'up');

        return false;
    });

    $(document).bind('keydown', 'return', function() {
        document.location = $('.repo').eq(curPosition).children('.generals').children('.repo-title').children('.name').attr('href');

        return false;
    });
}

function moveElement(className ,direction)
{
    var elem = $('.' + className);
    var listCount = elem.size();

    if (direction == 'down') {
        if (++curPosition < listCount) {
            elem.eq(curPosition-1).css('border','1px solid #DDDDDD');
            elem.eq(curPosition).css('border','1px solid blue');
        } else {
            curPosition--;
        }
    } else {
        if (--curPosition >= 0) {
                elem.eq(curPosition+1).css('border','1px solid #DDDDDD');
                elem.eq(curPosition).css('border','1px solid blue');
        } else{
            curPosition++;
        }
    }

    //check if the element is in view, if not move to it
    if (elem.eq(curPosition).viewportOffset().top > $(window).height() || elem.eq(curPosition).viewportOffset().top < 0) {
        $('html, body').animate({scrollTop: elem.eq(curPosition).offset().top}, 500);
    }
}
