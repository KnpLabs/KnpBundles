$(function()
{
    $('a, input, label').tipsy({fade: false});

    $('.clickable-list').delegate('li.item', 'click', function() {
        location.href = $(this).find('a').attr('href');
    });

    $('input.hint').one('click', function() {
        $(this).val('');
    });

    var addBundleDefaultValue = $('form.add_bundle input').val();
    $('form.add_bundle').submit(function() {
        var regexp = /^http:\/\/github\.com\/[\w\d]+\/[\w\d]+Bundle$/;
        var url = $(this).find('input').val();
        if(url == addBundleDefaultValue || !url.match(regexp)) {
            alert(url+' is not a valid GitHub Bundle repository url!');
            return false;
        }
        var $form = $(this);
        setTimeout(function() {
            $form.replaceWith('<p>Importing the Bundle fro GitHub.<br />This can take a few seconds, be patient!</p>');
        }, 500);
    });

});

if(document.domain == 'symfony2bundles.org') {
    //analytics
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
      background_color:'#000', 
      text_color: 'white',
      hover_color: '#06C',
      lang: 'en'
    };

    function _loadUserVoice() {
      var s = document.createElement('script');
      s.setAttribute('type', 'text/javascript');
      s.setAttribute('src', "http://cdn.uservoice.com/javascripts/widgets/tab.js");
      document.getElementsByTagName('head')[0].appendChild(s);
    }
    _loadSuper = window.onload;
    window.onload = (typeof window.onload != 'function') ? _loadUserVoice : function() { _loadSuper(); _loadUserVoice(); };

    var _sf_async_config={uid:2506,domain:"symfony2bundles.org"};
    (function(){
      function loadChartbeat() {
        window._sf_endpt=(new Date()).getTime();
        var e = document.createElement('script');
        e.setAttribute('language', 'javascript');
        e.setAttribute('type', 'text/javascript');
        e.setAttribute('src', "http://static.chartbeat.com/js/chartbeat.js");
        document.body.appendChild(e);
      }
      var oldonload = window.onload;
      window.onload = (typeof window.onload != 'function') ?
      loadChartbeat : function() { oldonload(); loadChartbeat(); };
    })();
}
