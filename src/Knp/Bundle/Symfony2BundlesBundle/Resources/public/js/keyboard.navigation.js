//code to control keyboard navigation
var curPosition = 0;

function onload() {

    if ($('.repo').length > 0) {
        var className = 'repo';
    } else if ($('.developer').length > 0) {
        var className = 'developer';
    }

    $('.' + className).first().addClass('active');

    $(document).bind('keydown', 's', function() { $('#search-query').focus(); return false; });

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

    $(document).bind('keydown', 'return', function() {
        if (className == 'repo') {
            document.location = $('.' + className).eq(curPosition).children('.generals').children('.repo-title').children('.name').attr('href');
        } else if (className == 'developer') {
            document.location = $('.' + className).eq(curPosition).children('.generals').children('.name').attr('href');
        }

        return false;
    });
}

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
    if (elem.eq(curPosition).viewportOffset().top > $(window).height() || elem.eq(curPosition).viewportOffset().top < 0) {
        $('html, body').animate({scrollTop: elem.eq(curPosition).offset().top}, 500);
    }
}
