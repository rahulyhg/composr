{$REQUIRE_JAVASCRIPT,core_feedback_features}

{$,Template uses auto-complete}
{$REQUIRE_JAVASCRIPT,jquery}
{$REQUIRE_JAVASCRIPT,jquery_autocomplete}
{$REQUIRE_CSS,autocomplete}

{$SET,GET_NAME,{$AND,{$IS_GUEST},{$CNS}}}

<div data-view="CommentsPostingForm" data-view-params="{+START,PARAMS_JSON,MORE_URL,GET_EMAIL,GET_NAME,GET_TITLE,EMAIL_OPTIONAL,TITLE_OPTIONAL,WYSIWYG,CAPTCHA,ANALYTIC_EVENT_CATEGORY}{_*}{+END}">
	{+START,IF_NON_EMPTY,{COMMENT_URL}}
	<form role="form" title="{TITLE*}" class="comments-form js-form-comments" id="comments-form" action="{COMMENT_URL*}{+START,IF_NON_EMPTY,{$GET,current_anchor}}#{$GET,current_anchor}{+END}{+START,IF_EMPTY,{$GET,current_anchor}}{+START,IF_PASSED_AND_TRUE,COMMENTS}#last_comment{+END}{+END}" method="post" enctype="multipart/form-data" autocomplete="off">
		{$INSERT_SPAMMER_BLACKHOLE}
		<input type="hidden" name="_comment_form_post" value="1" />
	{+END}

		{+START,IF_PASSED,HIDDEN}{HIDDEN}{+END}
		<input type="hidden" name="_validated" value="1" />
		<input type="hidden" name="stub" value="" />

		{+START,IF,{$NOT,{$GET,GET_NAME}}}
			<input type="hidden" name="poster_name_if_guest" value="" />
		{+END}
		{+START,IF,{$NOT,{GET_EMAIL}}}
			<input type="hidden" name="email" value="" />
		{+END}
		{+START,IF,{$NOT,{GET_TITLE}}}
			<input type="hidden" name="title" value="" />
		{+END}

		<div class="box box---comments-posting-form"{+START,IF_PASSED,EXPAND_TYPE} data-toggleable-tray="{}"{+END}>
			{+START,IF_NON_EMPTY,{TITLE}}
				<h3 class="toggleable-tray-title js-tray-header">
					{+START,IF_NON_PASSED,EXPAND_TYPE}
						{TITLE*}
					{+END}
					{+START,IF_PASSED,EXPAND_TYPE}
						<a class="toggleable-tray-button js-tray-onclick-toggle-tray" href="#!"><img alt="{$?,{$EQ,{EXPAND_TYPE},contract},{!CONTRACT},{!EXPAND}}" title="{$?,{$EQ,{EXPAND_TYPE},contract},{!CONTRACT},{!EXPAND}}" width="24" height="24" src="{$IMG*,icons/trays/{EXPAND_TYPE}2}" /></a>
						<a class="toggleable-tray-button js-tray-onclick-toggle-tray" href="#!">{TITLE*}</a>
					{+END}
				</h3>
			{+END}
			<div class="comments-posting-form-outer {+START,IF_PASSED,EXPAND_TYPE} toggleable-tray js-tray-content{+END}"{+START,IF_PASSED,EXPAND_TYPE} aria-expanded="false"{+END} id="comments_posting_form_outer" style="display: {DISPLAY*}">
				<div class="comments-posting-form-inner">
					<div class="wide-table-wrap"><table class="map-table wide-table">
						{+START,IF,{$DESKTOP}}
							<colgroup>
								<col class="comments-field-name-column" />
								<col class="comments-field-input-column" />
							</colgroup>
						{+END}

						<tbody>
							{$GET,EXTRA_COMMENTS_FIELDS_1}

							{+START,IF,{$GET,GET_NAME}}
								<tr>
									<th class="de-th vertical-alignment">
										<label for="poster_name_if_guest">{!YOUR_NAME}:</label>
										{$,Never optional; may not be requested if logged in as we already know}
									</th>

									<td>
										<input id="poster_name_if_guest" name="poster_name_if_guest" type="text" tabindex="1" maxlength="255" size="24" />
										{+START,IF_PASSED,JOIN_BITS}{+START,IF_NON_EMPTY,{JOIN_BITS}}
											<span class="horiz-field-sep">{JOIN_BITS}</span>
										{+END}{+END}
									</td>
								</tr>
							{+END}

							{+START,IF,{GET_EMAIL}}
								<tr>
									<th class="de-th vertical-alignment">
										<label for="email">{!YOUR_EMAIL_ADDRESS}:</label>
										{+START,IF,{EMAIL_OPTIONAL}}<br /><span class="associated-details">({!OPTIONAL})</span>{+END}
									</th>

									<td>
										<div>
											<input id="email" name="email" value="{$MEMBER_EMAIL*}" type="text" tabindex="2" maxlength="255" class="wide-field{+START,IF,{$NOT,{EMAIL_OPTIONAL}}} input-text-required{+END}" />
										</div>

										<div id="error_email" style="display: none" class="input-error-here"></div>
									</td>
								</tr>
							{+END}

							{+START,IF,{GET_TITLE}}
								<tr>
									<th class="de-th vertical-alignment">
										<label for="title">{!SUBJECT}:</label>
										{+START,IF,{TITLE_OPTIONAL}}<br /><span class="associated-details">({!OPTIONAL})</span>{+END}
									</th>

									<td>
										<div>
											<input id="title" name="title" value="{DEFAULT_TITLE*}" type="text" tabindex="3" maxlength="255" class="wide-field" />
										</div>

										<div id="error_title" style="display: none" class="input-error-here"></div>
									</td>
								</tr>
							{+END}

							{+START,IF_PASSED,REVIEW_RATING_CRITERIA}{+START,IF_PASSED,TYPE}{+START,IF_PASSED,ID}
								{+START,LOOP,REVIEW_RATING_CRITERIA}
									<tr class="js-container-review-rating">
										<th class="de-th vertical-alignment">
											{+START,IF_EMPTY,{REVIEW_TITLE}}{!RATING}:{+END}{+START,IF_NON_EMPTY,{REVIEW_TITLE}}{REVIEW_TITLE*}:{+END}
										</th>

										<td>
											<img id="review_bar_1__{TYPE|*}__{REVIEW_TITLE|*}__{ID|*}" class="rating-star js-img-review-bar" data-vw-rating="2" alt="" width="14" height="14" src="{$IMG*,icons/feedback/rating}" />
											<img id="review_bar_2__{TYPE|*}__{REVIEW_TITLE|*}__{ID|*}" class="rating-star js-img-review-bar" data-vw-rating="4" alt="" width="14" height="14" src="{$IMG*,icons/feedback/rating}" />
											<img id="review_bar_3__{TYPE|*}__{REVIEW_TITLE|*}__{ID|*}" class="rating-star js-img-review-bar" data-vw-rating="6" alt="" width="14" height="14" src="{$IMG*,icons/feedback/rating}" />
											<img id="review_bar_4__{TYPE|*}__{REVIEW_TITLE|*}__{ID|*}" class="rating-star js-img-review-bar" data-vw-rating="8" alt="" width="14" height="14" src="{$IMG*,icons/feedback/rating}" />
											<img id="review_bar_5__{TYPE|*}__{REVIEW_TITLE|*}__{ID|*}" class="rating-star js-img-review-bar" data-vw-rating="10" alt="" width="14" height="14" src="{$IMG*,icons/feedback/rating}" />
											<input id="review_rating__{TYPE|*}__{REVIEW_TITLE|*}__{ID|*}" class="js-inp-review-rating" type="hidden" name="review_rating__{REVIEW_TITLE|*}" value="" />
										</td>
									</tr>
								{+END}
							{+END}{+END}{+END}

							<tr>
								<th class="de-th">
									{$SET,needs_msg_label,{$OR,{$GET,GET_NAME},{GET_EMAIL},{GET_TITLE}}}
									{+START,IF,{$GET,needs_msg_label}}
										<div class="vertical-alignment">
											<a data-open-as-overlay="{}" class="link-exempt" title="{!COMCODE_MESSAGE,Comcode} {!LINK_NEW_WINDOW}" target="_blank" href="{$PAGE_LINK*,_SEARCH:userguide_comcode}"><img alt="{!COMCODE_MESSAGE,Comcode}" width="16" height="16" src="{$IMG*,icons/editor/comcode}" /></a>
											<label for="post">{!POST_COMMENT}:</label>
										</div>
									{+END}

									{+START,IF_NON_EMPTY,{FIRST_POST}{RULES_TEXT}}
										<ul class="associated-links-block-group">
											{+START,IF_NON_EMPTY,{FIRST_POST}}
												<li><a class="non-link" title="{!cns:FIRST_POST} {!LINK_NEW_WINDOW}" target="_blank" href="{FIRST_POST_URL*}" data-blur-deactivate-tooltip="" data-focus-activate-tooltip="['{FIRST_POST*~;^}','320px',null,null,false,true]" data-mouseover-activate-tooltip="['{FIRST_POST*~;^}','320px',null,null,false,true]">{!cns:FIRST_POST}</a></li>
											{+END}

											{+START,IF_NON_EMPTY,{RULES_TEXT}}
												<li><a class="non-link" href="{$PAGE_LINK*,:rules}" data-blur-deactivate-tooltip="" data-focus-activate-tooltip="['{$TRUNCATE_LEFT,{RULES_TEXT*~;^},1000,0,1}','320px',null,null,false,true]" data-mouseover-activate-tooltip="['{$TRUNCATE_LEFT,{RULES_TEXT*~;^},1000,0,1}','320px',null,null,false,true]">{+START,IF,{$DESKTOP}}<span class="inline-desktop">{!HOVER_MOUSE_IMPORTANT}</span>{+END}<span class="inline-mobile">{!TAP_MOUSE_IMPORTANT}</span></a></li>
											{+END}
										</ul>
									{+END}

									{+START,IF,{$NOT,{$GET,needs_msg_label}}}
										<div>
											<a data-open-as-overlay="{}" class="link-exempt" title="{!COMCODE_MESSAGE,Comcode} {!LINK_NEW_WINDOW}" target="_blank" href="{$PAGE_LINK*,_SEARCH:userguide_comcode}"><img alt="{!COMCODE_MESSAGE,Comcode}" width="16" height="16" src="{$IMG*,icons/editor/comcode}" class="vertical-alignment" /></a>
											<label for="post" class="vertical-alignment">{!POST_COMMENT}:</label>
										</div>
									{+END}

									{+START,IF,{$DESKTOP}}
										{+START,IF_NON_EMPTY,{EMOTICONS}}
											<div class="comments-posting-form-emoticons block-desktop">
												<div class="box box---comments-posting-form"><div class="box-inner">
													{EMOTICONS}

													{+START,IF,{$CNS}}
														<p class="associated-link associated-links-block-group"><a rel="nofollow" href="#!" class="js-click-open-site-emoticon-chooser-window">{!EMOTICONS_POPUP}</a></p>
													{+END}
												</div>
											</div></div>
										{+END}
									{+END}
								</th>

								<td>
									<div>
										<textarea name="post" id="post" data-textarea-auto-height="" tabindex="6" accesskey="x" class="{$?,{TRUE_ATTACHMENT_UI},true-attachment-ui,faux-attachment-ui} wide-field js-focus-textarea-post" cols="42" rows="{$?,{$IS_NON_EMPTY,{$GET,COMMENT_POSTING_ROWS}},{$GET,COMMENT_POSTING_ROWS},11}">{POST_WARNING*}{+START,IF_PASSED,DEFAULT_POST}{DEFAULT_POST*}{+END}</textarea>
										<input type="hidden" name="comcode__post" value="1" />
									</div>

									<div id="error_post" style="display: none" class="input-error-here"></div>

									{+START,IF_PASSED,ATTACHMENTS}
										<div class="attachments">
											{+START,IF_PASSED,ATTACH_SIZE_FIELD}
												{ATTACH_SIZE_FIELD}
											{+END}
											<input type="hidden" name="posting_ref_id" value="{$RAND%}" />
											{ATTACHMENTS}
										</div>
									{+END}
								</td>
							</tr>

							{$GET,EXTRA_COMMENTS_FIELDS_2}
						</tbody>
					</table></div>

					<div class="comments-posting-form-end">
						{+START,IF_PASSED_AND_TRUE,USE_CAPTCHA}
							{+START,INCLUDE,COMMENTS_POSTING_FORM_CAPTCHA}
								TABINDEX=7
							{+END}
						{+END}

						{$SET,has_preview_button,{$AND,{$DESKTOP},{$JS_ON},{$CONFIG_OPTION,enable_previews},{$NOT,{$VALUE_OPTION,xhtml_strict}}}}
						{+START,IF_PASSED,SKIP_PREVIEW}{$SET,has_preview_button,0}{+END}

						<div class="proceed-button buttons-group {$?,{$GET,has_preview_button},contains-preview-button,contains-no-preview-button}">
							{+START,IF,{$DESKTOP}}
								{+START,IF,{$GET,has_preview_button}}
									<input id="preview_button" accesskey="p" tabindex="250" class="buttons--preview js-click-do-form-preview {$?,{$IS_EMPTY,{COMMENT_URL}},button-screen,button-screen-item} desktop-inline" type="button" value="{!PREVIEW}" />
								{+END}
							{+END}

							{+START,IF_PASSED,MORE_URL}
								<button tabindex="6" accesskey="y" class="buttons--new-post-full {$?,{$IS_EMPTY,{COMMENT_URL}},button-screen,button-screen-item} js-btn-full-editor" type="button">{+START,IF,{$DESKTOP}}<span class="inline-desktop">{!FULL_EDITOR}</span>{+END}<span class="inline-mobile">{!MORE}</span></button>
							{+END}

							{+START,IF_PASSED,ATTACHMENTS}
								{+START,IF,{$AND,{TRUE_ATTACHMENT_UI},{$BROWSER_MATCHES,simplified_attachments_ui}}}
									<input tabindex="7" id="js-attachment-upload-button" class="for-field-post buttons--thumbnail {$?,{$IS_EMPTY,{COMMENT_URL}},button-screen,button-screen-item}" type="button" value="{!comcode:ADD_IMAGES}" />
								{+END}
							{+END}

							{+START,SET,button_title}{+START,IF_PASSED,SUBMIT_NAME}{SUBMIT_NAME*}{+END}{+START,IF_NON_PASSED,SUBMIT_NAME}{+START,IF_NON_EMPTY,{TITLE}}{TITLE*}{+END}{+START,IF_EMPTY,{TITLE}}{!SEND}{+END}{+END}{+END}
							{+START,SET,button_icon}{+START,IF_PASSED,SUBMIT_ICON}{SUBMIT_ICON*}{+END}{+START,IF_NON_PASSED,SUBMIT_ICON}{+START,IF_NON_PASSED,MORE_URL}buttons--new-comment{+END}{+START,IF_PASSED,MORE_URL}buttons--new-reply{+END}{+END}{+END}
							<button tabindex="8" accesskey="u" id="submit-button" class="{$GET,button_icon} {$?,{$GET,has_preview_button},near-preview-button,not-near-preview-button} {$?,{$IS_EMPTY,{COMMENT_URL}},button-screen,button-screen-item} js-btn-submit-comments" type="button">{+START,IF,{$DESKTOP}}<span class="inline-desktop">{$GET,button_title}</span>{+END}<span class="inline-mobile">{$REPLACE,{!cns:REPLY},{!_REPLY},{$GET,button_title}}</span></button>
						</div>
					</div>
				</div>
			</div>
		</div>

	{+START,IF_NON_EMPTY,{COMMENT_URL}}
	</form>
	{+END}

	{+START,IF,{$CONFIG_OPTION,enable_previews}}
		<iframe {$?,{$BROWSER_MATCHES,ie}, frameBorder="0" scrolling="no"} title="{!PREVIEW}" name="preview_iframe" id="preview_iframe" src="{$BASE_URL*}/uploads/index.html" class="hidden-preview-frame">{!PREVIEW}</iframe>
	{+END}
</div>
