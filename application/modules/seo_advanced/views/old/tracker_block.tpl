{if $ga_default_account_id}
{literal}<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '{/literal}{$ga_default_account_id}{literal}', 'auto');
  ga('send', 'pageview');
</script>{/literal}
{/if}
{if $tracker_code}{$tracker_code}{/if}
