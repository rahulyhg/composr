{$REQUIRE_JAVASCRIPT,realtime_rain}

<div class="webstandards-checker-off" data-tpl="realtimeRainBubble" data-tpl-params="{+START,PARAMS_JSON,TICKER_TEXT,RELATIVE_TIMESTAMP,GROUP_ID,SPECIAL_ICON,MULTIPLICITY}{_*}{+END}">
	{$SET,RAND_ID,bubble_id_{$RAND}}

	<div id="{$GET,RAND_ID}" class="bubble-wrap attitude-{$REPLACE%,_,-,{TYPE}}{$?,{IS_POSITIVE},-positive,}{$?,{IS_NEGATIVE},-negative,}">
		<div id="{$GET,RAND_ID}_main" class="bubble bubble-{$LCASE%,{$REPLACE,_,-,{TYPE}}}">
			<div class="float-surrounder">
				<div class="special-icon">
					{+START,IF_PASSED,SPECIAL_ICON}
						<img width="36" height="36" src="{$IMG*,icons/realtime_rain/{SPECIAL_ICON}}" alt="{SPECIAL_TOOLTIP*}" title="{SPECIAL_TOOLTIP*}" />
					{+END}
				</div>

				<div class="avatar-icon">
					{+START,IF_NON_EMPTY,{IMAGE}}
						<img src="{$ENSURE_PROTOCOL_SUITABILITY*,{IMAGE}}" alt="" />
					{+END}
				</div>
			</div>

			<h1>{TITLE*}</h1>

			<div class="linkage">
				{+START,IF_PASSED,URL}
					<a href="{URL*}">{!VIEW}</a>
				{+END}
			</div>
		</div>
	</div>
</div>
