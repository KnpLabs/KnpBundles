function onload() {
    //code to control keyboard navigation
    var listCount = $('.repo').size();
    var curPosition = 0;

    $('.repo').first().css('border','1px solid blue');

    $(document).bind('keydown', 's', function() { $('#search-query').focus(); return false; });

    $(document).bind('keydown', 'down', function() {
        if (++curPosition < listCount) {
            $('.repo').eq(curPosition-1).css('border','1px solid #DDDDDD');
            $('.repo').eq(curPosition).css('border','1px solid blue');
            moveToElement($('.repo').eq(curPosition));
        } else {
            curPosition--;
        }

        return false;
    });


    $(document).bind('keydown', 'up', function(){
        if (--curPosition >= 0) {
                $('.repo').eq(curPosition+1).css('border','1px solid #DDDDDD');
                $('.repo').eq(curPosition).css('border','1px solid blue');
                moveToElement($('.repo').eq(curPosition));
        } else{
            curPosition++;
        }

        return false;
    });

    $(document).bind('keydown', 'return', function() {
        document.location = $('.repo').eq(curPosition).children('.generals').children('.repo-title').children('.name').attr('href');

        return false;
    });
}

function moveToElement(elem)
{
    //check if the element is in view, if not move to it
    if (elem.viewportOffset().top > $(window).height() || elem.viewportOffset().top < 0) {
        $('html, body').animate({scrollTop: elem.offset().top}, 500);
    }
}
