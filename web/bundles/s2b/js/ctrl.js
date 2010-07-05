$(function()
{
    $('a, input, label').tipsy({fade: false});

    $('.bundle-list').delegate('li', 'click', function() {
        location.href = $(this).find('a').attr('href');
    });

    $('#qsearch').one('click', function() {
        $(this).val('');
    });
});

//analytics
if(document.domain == 'symfony2bundles.org') {
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-7062980-9']);
    _gaq.push(['_trackPageview']);
    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = 'http://www.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
    
    var uservoiceOptions = {
      /* required */
      key: 'symfony2bundles',
      host: 'symfony2bundles.uservoice.com', 
      forum: '65259',
      showTab: true,  
      /* optional */
      alignment: 'left',
      background_color:'#f00', 
      text_color: 'white',
      hover_color: '#06C',
      lang: 'en'
    };

    function _loadUserVoice() {
      var s = document.createElement('script');
      s.setAttribute('type', 'text/javascript');
      s.setAttribute('src', ("https:" == document.location.protocol ? "https://" : "http://") + "cdn.uservoice.com/javascripts/widgets/tab.js");
      document.getElementsByTagName('head')[0].appendChild(s);
    }
    _loadSuper = window.onload;
    window.onload = (typeof window.onload != 'function') ? _loadUserVoice : function() { _loadSuper(); _loadUserVoice(); };
}
