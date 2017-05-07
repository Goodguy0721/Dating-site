{include file="header.tpl"}
<div class="content-block">
	<h1>{l i='header_blog' gid='blogs'}</h1>
	{include file="blog_menu.tpl" module="blogs" theme="default"}
	<div style="margin-top:15px">
		{foreach from=$categories item=item}
			<div class="fleft" style="width:45%; padding:5px 30px 5px 0px">{if $item.blogs_count > 0}<a href="{$site_url}blogs/view_category/{$item.gid}">{$item.name}</a>{else}{$item.name}{/if}<span class="fright">{$item.blogs_count} {l i='blogs' gid='blogs'}</span></div>
		{/foreach}
	</div>
	<div class="clr"></div>
	{if $tags_count > 0}
	<h2 class="line top bottom linked">
		{l i='tags_cloud' gid='blogs'}
	</h2>
	{foreach from=$tags item=item}
		<a style="padding-right:5px" href="javascript:void(0)" onclick="TagsSearch('{$item}')">{$item}</a>
	{/foreach}
	{/if}
	<div id="by_tags_search"></div>
</div>
<div class="clr"></div>
{literal}<script>
	function TagsSearch(tag){
		$('#by_tags_search').html('');
		$.ajax({
			url: '{/literal}{$site_url}{literal}blogs/searh_by_tag/',
			data: {tag: tag},
			success: function(resp) {
				$('#by_tags_search').html(resp);
			},
			type: 'POST',
			async: false
		});
	}
</script>{/literal}
{include file="footer.tpl"}
