{$SET,randomised_id,{$?,{$IS_EMPTY,{NAME*}},{$RAND},{NAME*}}}

<tr>
	<th id="form_table_field_name__{$GET,randomised_id}" colspan="2" class="form-table-description-above-cell{+START,IF,{REQUIRED}} required{+END}">
		<input type="hidden" name="label_for__{NAME*}" value="{$STRIP_TAGS,{PRETTY_NAME*}}" />

		<p class="field-name lonely-label">
			<label for="{NAME*}">{PRETTY_NAME*}<span class="inline-desktop">:</span></label>
		</p>

		<span id="required_readable_marker__{$?,{$IS_EMPTY,{NAME*}},{$RAND},{NAME*}}" style="display: {$?,{REQUIRED*},inline,none}"><span class="required-star">*</span> <span class="accessibility_hidden">{!REQUIRED}</span></span>

		{+START,INCLUDE,FORM_SCREEN_FIELD_DESCRIPTION}{+END}
	</th>
</tr>

<tr class="field-input" data-tpl="formScreenInputHuge_input" data-tpl-params="{+START,PARAMS_JSON,randomised_id,NAME}{_*}{+END}">
	<td id="form_table_field_input__{$GET,randomised_id}" colspan="2" class="form-table-huge-field {+START,IF,{REQUIRED}} required{+END}">
		<div id="container_for_{NAME*}">
			<textarea tabindex="{TABINDEX*}" class="input-text{_REQUIRED} wide-field{+START,IF,{SCROLLS}} textarea_scroll{+END}" cols="70" rows="{ROWS*}" id="{NAME*}" name="{NAME*}" data-textarea-auto-height="">{DEFAULT*}</textarea>

			{+START,IF_PASSED_AND_TRUE,RAW}<input type="hidden" name="pre_f_{NAME*}" value="1" />{+END}
		</div>
	</td>
</tr>
