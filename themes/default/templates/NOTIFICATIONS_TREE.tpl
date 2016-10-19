{+START,LOOP,NOTIFICATION_CATEGORIES}
	{$SET,zebra,{$?,{$EQ,{DEPTH},0},{$CYCLE*,zebra,zebra_0,zebra_1},{$GET,zebra}}}
	<tr class="notification_code {$GET*,zebra}" data-tpl="notificationsTree">
		<th class="de_th" style="padding-left: {$ADD*,5,{$MULT,{DEPTH},20}}px">
			<input type="hidden" id="notification_{NOTIFICATION_CODE*}_category_{NOTIFICATION_CATEGORY*}" name="notification_{NOTIFICATION_CODE*}_category_{NOTIFICATION_CATEGORY*}" value="1" />
			{CATEGORY_TITLE*}

			{+START,IF_NON_EMPTY,{$TRIM,{CHILDREN}}}
				<span class="horiz_field_sep associated_link">
					<a class="js-click-copy-advanced-notifications" href="#!">{!NOTIFICATIONS_COPY_UNDER}</a>
				</span>
			{+END}
		</th>

		{+START,INCLUDE,NOTIFICATION_TYPES}{+END}
	</tr>

	{CHILDREN}
{+END}
