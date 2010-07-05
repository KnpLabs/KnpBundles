$(function()
{
    $('a, input, label, div.lichess_server').tipsy({fade: false});

    $('.bundle-list').delegate('li', 'click', function() {
        location.href = $(this).find('a').attr('href');
    });

    $('#qsearch').one('click', function() {
        $(this).val('');
    });
});

//analytics
if(document.domain == 'symfony2bundles.org') {
    //var _gaq = _gaq || [];
    //_gaq.push(['_setAccount', 'UA-7935029-3']);
    //_gaq.push(['_trackPageview']);
    //(function() {
        //var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = 'http://www.google-analytics.com/ga.js';
        //var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    //})();
}
