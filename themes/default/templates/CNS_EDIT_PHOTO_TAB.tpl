<div class="float-surrounder">
	<div class="cns-avatar-page-old-avatar">
		{+START,IF_NON_EMPTY,{PHOTO}}
			<img class="cns-topic-post-avatar" alt="{!PHOTO}" src="{$ENSURE_PROTOCOL_SUITABILITY*,{PHOTO}}" />
		{+END}
		{+START,IF_EMPTY,{PHOTO}}
			{!NONE_EM}
		{+END}
	</div>

	<div class="cns_avatar_page_text">
		<p>{!PHOTO_CHANGE,{$DISPLAYED_USERNAME*,{USERNAME}}}</p>

		{TEXT}

		{+START,IF_NON_EMPTY,{PHOTO}}
			<form title="{$WCASE,{!DELETE_PHOTO}}" action="{$SELF_URL*}#tab__edit__photo" method="post" class="inline" autocomplete="off">
				{$INSERT_SPAMMER_BLACKHOLE}

				<p>
					<input type="hidden" name="delete_photo" value="1" />
					{!YOU_CAN_DELETE_PHOTO,<input class="button-hyperlink" type="submit" value="{!DELETE_PHOTO}" />}
				</p>
			</form>
		{+END}
	</div>
</div>
