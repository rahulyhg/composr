{$, Template uses auto-complete}
{$REQUIRE_JAVASCRIPT,jquery}
{$REQUIRE_JAVASCRIPT,jquery_autocomplete}
{$REQUIRE_CSS,autocomplete}

{$SET,id,{$RAND}}
{$SET,init_drag_drop,0}

<tr class="form_table_field_spacer" id="field-{$GET*,id}-label">
	{+START,SET,posting_field}
		{+START,IF_PASSED,POST_COMMENT}
			{+START,IF_NON_EMPTY,{POST_COMMENT}}
				<p class="faux_h2"><label for="{NAME*}">{POST_COMMENT*}</label></p>

				<input type="hidden" name="label_for__{NAME*}" value="{$STRIP_TAGS,{POST_COMMENT*}}" />
			{+END}
		{+END}
		{+START,IF_NON_PASSED,POST_COMMENT}
			<span class="field_name">
				<label class="accessibility_hidden" for="{NAME*}">{!TEXT}</label>
			</span>

			<span id="required_readable_marker__{$?,{$IS_EMPTY,{NAME*}},{$RAND},{NAME*}}" style="display: {$?,{REQUIRED*},inline,none}"><span class="required_star">*</span> <span class="accessibility_hidden">{!REQUIRED}</span></span>
		{+END}

		{+START,INCLUDE,FORM_SCREEN_FIELD_DESCRIPTION}{+END}

		<input type="hidden" name="comcode__{NAME*}" value="1" />
		{HIDDEN_FIELDS}

		{+START,IF,{$OR,{$IN_STR,{CLASS},wysiwyg},{$AND,{$MATCH_KEY_MATCH,_WILD:cms_comcode_pages},{$SHOW_DOCS}}}}
			<div class="comcode_supported posting_form_main_comcode_button">
				<ul class="horizontal_links horiz_field_sep associated_links_block_group">
					{+START,IF,{$SHOW_DOCS}}{+START,IF_PASSED,COMCODE_URL}
						{+START,IF,{$NOT,{$MATCH_KEY_MATCH,_WILD:cms_comcode_pages}}}
							<li><a data-open-as-overlay="1" class="link_exempt" title="{!COMCODE_MESSAGE,Comcode} {!LINK_NEW_WINDOW}" target="_blank" href="{COMCODE_URL*}"><img src="{$IMG*,icons/16x16/editor/comcode}" srcset="{$IMG*,icons/32x32/editor/comcode} 2x" class="vertical_alignment" alt="{!COMCODE_MESSAGE,Comcode}" /></a></li>
						{+END}
						{+START,IF,{$MATCH_KEY_MATCH,_WILD:cms_comcode_pages}}
							<li><a class="link_exempt" title="{!FULL_COMCODE_TUTORIAL} {!LINK_NEW_WINDOW}" target="_blank" href="{$TUTORIAL_URL*,tut_comcode}">{!FULL_COMCODE_TUTORIAL}</a></li>
							<li><a class="link_exempt" title="{!FULL_BLOCK_TUTORIAL} {!LINK_NEW_WINDOW}" target="_blank" href="{$TUTORIAL_URL*,tut_adv_comcode_pages}">{!FULL_BLOCK_TUTORIAL}</a></li>
						{+END}
						<li><a rel="nofollow" class="link_exempt js-link-click-open-field-emoticon-chooser-window" title="{!EMOTICONS_POPUP} {!LINK_NEW_WINDOW}" target="_blank" href="{$FIND_SCRIPT*,emoticons}?field_name={NAME*}{$KEEP*,0,1}" data-click-pd="1"><img src="{$IMG*,icons/16x16/editor/insert_emoticons}" srcset="{$IMG*,icons/32x32/editor/insert_emoticons} 2x" alt="{!EMOTICONS_POPUP}" class="vertical_alignment" /></a></li>
					{+END}{+END}
					{+START,IF,{$IN_STR,{CLASS},wysiwyg}}
						<li><a id="toggle_wysiwyg_{NAME*}" href="#!" class="js-click-toggle-wysiwyg"><abbr title="{!TOGGLE_WYSIWYG_2}"><img src="{$IMG*,icons/16x16/editor/wysiwyg_on}" srcset="{$IMG*,icons/32x32/editor/wysiwyg_on} 2x" alt="{!comcode:ENABLE_WYSIWYG}" title="{!comcode:ENABLE_WYSIWYG}" /></abbr></a></li>
					{+END}
				</ul>
			</div>
		{+END}
	{+END}
	{+START,IF_NON_EMPTY,{$TRIM,{$GET,posting_field}}}
		<th colspan="2" class="table_heading_cell{+START,IF,{REQUIRED}} required{+END}">
			{$GET,posting_field}
		</th>
	{+END}
</tr>
<tr class="field_input" id="field-{$GET*,id}-input">
	<td class="{+START,IF,{REQUIRED}} required{+END} form_table_huge_field" colspan="2">
		{+START,IF_PASSED,DEFAULT_PARSED}
			<textarea cols="1" rows="1" style="display: none" readonly="readonly" disabled="disabled" name="{NAME*}_parsed">{DEFAULT_PARSED*}</textarea>
		{+END}

		<div class="float_surrounder">
			<div role="toolbar" class="float_surrounder post_options_wrap">
				<div id="post_special_options2" style="display: none">
					{COMCODE_EDITOR_SMALL}
				</div>
				<div id="post_special_options">
					{COMCODE_EDITOR}
				</div>
			</div>

			<div id="container_for_{NAME*}" class="container_for_wysiwyg">
				<textarea data-textarea-auto-height="" accesskey="x" class="{CLASS*}{+START,IF,{REQUIRED}} posting_required{+END} wide_field posting_field_textarea" tabindex="{TABINDEX_PF*}" id="{NAME*}" name="{NAME*}" cols="70" rows="17">{POST*}</textarea>

				{+START,IF_PASSED,WORD_COUNTER}
					{$SET,word_count_id,{$RAND}}
					<div class="word_count" id="word_count_{$GET*,word_count_id}"></div>
				{+END}
			</div>
		</div>

		{+START,IF_NON_EMPTY,{$TRIM,{EMOTICON_CHOOSER}}}
			{+START,IF,{$NOT,{$MATCH_KEY_MATCH,_WILD:cms_news}}}
				{+START,IF,{$DESKTOP}}{+START,IF,{$OR,{$CONFIG_OPTION,is_on_emoticon_choosers},{$CNS}}}
					<div{+START,IF,{$CONFIG_OPTION,is_on_emoticon_choosers}} class="emoticon_chooser box block_desktop"{+END}>
						{+START,IF,{$CNS}}
							<span class="right horiz_field_sep associated_link"><a rel="nofollow" target="_blank" class="js-link-click-open-site-emoticon-chooser-window" href="{$FIND_SCRIPT*,emoticons}?field_name={NAME*}{$KEEP*,0,1}" data-click-pd="1" title="{!EMOTICONS_POPUP} {!LINK_NEW_WINDOW}">{$?,{$CONFIG_OPTION,is_on_emoticon_choosers},{!VIEW_ARCHIVE},{!EMOTICONS_POPUP}}</a></span>
						{+END}

						{+START,IF,{$CONFIG_OPTION,is_on_emoticon_choosers}}
							{EMOTICON_CHOOSER}
						{+END}
					</div>
				{+END}{+END}
			{+END}
		{+END}

		{+START,IF,{$NOT,{$MATCH_KEY_MATCH,cms}}}
			{+START,IF_PASSED,POST_COMMENT}
				<p class="posting_rules">{!USE_WEBSITE_RULES,{$PAGE_LINK*,:rules},{$PAGE_LINK*,:privacy}}</p>
			{+END}
		{+END}

		{+START,IF,{$MATCH_KEY_MATCH,cms}}
			{+START,IF,{$VALUE_OPTION,download_associated_media}}
				<p class="vertical_alignment">
					<label for="{NAME*}_download_associated_media">{!comcode:DOWNLOAD_ASSOCIATED_MEDIA}</label>
					<input title="{!comcode:DESCRIPTION_DOWNLOAD_ASSOCIATED_MEDIA}" checked="checked" type="checkbox" name="{NAME*}_download_associated_media" id="{NAME*}_download_associated_media" value="1" />
				</p>
			{+END}
		{+END}

		{+START,IF,{$AND,{$BROWSER_MATCHES,simplified_attachments_ui},{$IS_NON_EMPTY,{ATTACHMENTS}}}}
			{$SET,init_drag_drop,1}
			{ATTACHMENTS}
			<input type="hidden" name="posting_ref_id" value="{$RAND%}" />
		{+END}

		<div class="tpl_placeholder" style="display: none;" data-tpl="postingField" data-tpl-params="{+START,PARAMS_JSON,id,NAME,CLASS,WORD_COUNTER,word_count_id,init_drag_drop}{_*}{+END}"></div>
	</td>
</tr>

{+START,IF,{$AND,{$NOT,{$BROWSER_MATCHES,simplified_attachments_ui}},{$IS_NON_EMPTY,{ATTACHMENTS}}}}
	{$SET,init_drag_drop,1}
	<tr class="form_table_field_spacer" id="field-{$GET*,id}-attachments-ui">
		<th colspan="2" class="table_heading_cell">
			<a class="toggleable_tray_button js-click-pf-toggle-subord-fields" id="fes_attachments" href="#!"><img alt="{!EXPAND}: {!ATTACHMENTS}" title="{!EXPAND}" src="{$IMG*,1x/trays/expand}" srcset="{$IMG*,2x/trays/expand} 2x" /></a>

			<span class="faux_h2 toggleable_tray_button js-click-pf-toggle-subord-fields">
				{!ATTACHMENTS}

				{+START,IF,{$DESKTOP}}
					<img class="help_icon inline_desktop" data-cms-rich-tooltip="{}" title="{$STRIP_TAGS,{!ATTACHMENT_HELP}}" alt="{!HELP}" src="{$IMG*,icons/16x16/help}" srcset="{$IMG*,icons/32x32/help} 2x" />
				{+END}
			</span>

			{+START,IF_PASSED,HELP}
				<p style="display: none" id="fes_attachments_help">
					{HELP*}
				</p>
			{+END}
		</th>
	</tr>
	<tr style="display: none" class="field_input" id="field-{$GET*,id}-attachments-ui-input">
		<td class="form_table_huge_field" colspan="2">
			{ATTACHMENTS}
			<input type="hidden" name="posting_ref_id" value="{$RAND%}" />
		</td>
	</tr>
{+END}
