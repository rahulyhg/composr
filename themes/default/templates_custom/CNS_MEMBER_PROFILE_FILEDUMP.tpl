{$BLOCK,block=myfiles,member_id={MEMBER_ID}}

{+START,IF,{$HAS_ACTUAL_PAGE_ACCESS,filedump}}
	<p class="associated-link associated-links-block-group"><a href="{$PAGE_LINK*,_SEARCH:filedump:browse:place=/{$USERNAME&}/}">{!MORE}</a></p>
{+END}