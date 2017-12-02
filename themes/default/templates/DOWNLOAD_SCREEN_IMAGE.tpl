{$SET,FIRST_IMAGE_ID,{ID}}

<figure>
	<div>
		<a rel="lightbox" href="{VIEW_URL*}">{$TRIM,{THUMB}}</a>
	</div>

	{+START,IF_NON_EMPTY,{DESCRIPTION}}
		<figcaption class="associated-details">
			{DESCRIPTION}
		</figcaption>
	{+END}

	{+START,IF_NON_EMPTY,{EDIT_URL}}
		<p class="associated_link associated-links-block-group">
			<a href="{EDIT_URL*}" title="{!EDIT_IMAGE}, #{ID*}">{!EDIT_LINK}</a>
		</p>
	{+END}
</figure>
