{$SET,rowspan_detected,0}

{+START,IF_NON_EMPTY,{$TRIM,{HOURS}}}
	<table class="map_table calendar-day-table autosized-table">
		<tbody>
			{HOURS}
		</tbody>
	</table>
{+END}
