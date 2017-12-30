{+START,SET,TOOLTIP}
	<div class="gallery-tooltip">
		<table class="map_table results-table">
			{+START,IF,{$DESKTOP}}
				<colgroup>
					<col class="gallery-entry-field-name-column" />
					<col class="gallery-entry-field-value-column" />
				</colgroup>
			{+END}

			<tbody>
				<tr>
					<th class="de-th metadata-title">{!ADDED}</th>
					<td>{$DATE_TIME*,{ADD_DATE_RAW}}</td>
				</tr>

				<tr>
					<th class="de-th metadata-title">{!BY}</th>
					<td><a rel="author" href="{$MEMBER_PROFILE_URL*,{SUBMITTER}}">{$USERNAME*,{SUBMITTER},1}</a></td>
				</tr>

				{+START,IF_NON_EMPTY,{EDIT_DATE_RAW}}
					<tr>
						<th class="de-th metadata-title">{!EDITED}</th>
						<td>{$DATE_TIME*,{EDIT_DATE_RAW}}</td>
					</tr>
				{+END}

				{+START,IF,{$INLINE_STATS}}
					<tr>
						<th class="de-th metadata-title">{!COUNT_VIEWS}</th>
						<td>{VIEWS*}</td>
					</tr>
				{+END}

				{$SET,rating,{$RATING,images,{ID},1,{SUBMITTER}}}
				{+START,IF_NON_EMPTY,{$TRIM,{$GET,rating}}}
					<tr>
						<th class="de-th metadata-title">{!RATING}</th>
						<td>{$GET,rating}</td>
					</tr>
				{+END}
			</tbody>
		</table>
	</div>
{+END}

{+START,IF,{$EQ,{_GUID},carousel}}
	<a data-mouseover-activate-tooltip="['{$GET;^*,TOOLTIP}','auto',null,null,false,true]" href="{VIEW_URL*}"><img alt="{TITLE}" height="140" src="{$THUMBNAIL*,{THUMB_URL},140x140,website_specific,,,height}" /></a>
{+END}

{+START,IF,{$NEQ,{_GUID},carousel}}
	<div class="gallery-regular-thumb">
		{+START,IF,{$HAS_DELETE_PERMISSION,mid,{SUBMITTER},{$MEMBER},cms_galleries}}
			{+START,INCLUDE,MASS_SELECT_MARKER}
				TYPE={MEDIA_TYPE}
				ID={ID}
			{+END}
		{+END}

		<div class="img-thumb-wrap" data-mouseover-activate-tooltip="['{$GET;^*,TOOLTIP}','auto',null,null,false,true]">
			<a href="{VIEW_URL*}">{$TRIM,{THUMB}}</a>
		</div>

		<p class="gallery-media-title-cropped">
			{+START,FRACTIONAL_EDITABLE,{TITLE},title,_SEARCH:cms_galleries:__edit:{ID},1,1,{$HAS_EDIT_PERMISSION,mid,{SUBMITTER},{$MEMBER},cms_galleries,galleries,{CAT}}}{$TRUNCATE_LEFT,{TITLE},23,0,0}{+END}
		</p>

		{+START,IF_PASSED,RATING_DETAILS}{+START,IF_NON_EMPTY,{RATING_DETAILS}}
			<div class="grating">{RATING_DETAILS}</div>
		{+END}{+END}
		<p class="gallery-regular-thumb-comments-count">
			<a href="{VIEW_URL*}">{$COMMENT_COUNT,images,{ID}}</a>
		</p>
	</div>
{+END}
