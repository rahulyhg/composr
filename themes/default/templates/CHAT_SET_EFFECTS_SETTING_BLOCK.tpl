{$REQUIRE_JAVASCRIPT,chat}

<div data-tpl="chatSetEffectsSettingBlock" data-tpl-params="{+START,PARAMS_JSON,EFFECTS}{_*}{+END}"{+START,IF_PASSED,MEMBER_ID} data-toggleable-tray="{}"{+END}>
	{+START,IF_PASSED,USERNAME}{+START,IF_PASSED,MEMBER_ID}
		<div class="toggleable-tray-title js-tray-header">
			{!OVERRIDES_FOR_FRIEND,{USERNAME*}}
			<a class="toggleable-tray-button js-tray-onclick-toggle-tray" href="#!"><img alt="{$?,{HAS_SOME},{!CONTRACT},{!EXPAND}}" title="{$?,{HAS_SOME},{!CONTRACT},{!EXPAND}}" src="{$IMG*,1x/trays/{$?,{HAS_SOME},contract,expand}}" /></a>
		</div>
	{+END}{+END}

	<div class="toggleable-tray js-tray-content"{+START,IF_PASSED,MEMBER_ID} id="user_{MEMBER_ID*}"{+END}{+START,IF,{$NOT,{HAS_SOME}}} style="display: none"{+END} aria-expanded="false">
		<div class="wide-table-wrap"><table class="map_table form-table wide-table scrollable-inside">
			{+START,IF,{$DESKTOP}}
				<colgroup>
					<col class="field-name-column" />
					<col class="field-input-column" />
				</colgroup>
			{+END}

			<tbody>
				{+START,LOOP,EFFECTS}
					<tr class="form-table-field-spacer">
						<th colspan="2" class="table_heading_cell">
							<span class="faux-h2">{EFFECT_TITLE*}</span>
						</th>
					</tr>

					<tr class="field-input">
						<th id="form_table_field_name__select_{KEY*}{+START,IF_PASSED,MEMBER_ID}_{MEMBER_ID*}{+END}" class="form-table-field-name">
							<label for="select_{KEY*}{+START,IF_PASSED,MEMBER_ID}_{MEMBER_ID*}{+END}"><span class="form-field-name field-name">{!BROWSE}</span></label>
						</th>

						<td id="form_table_field_input__select_{KEY*}{+START,IF_PASSED,MEMBER_ID}_{MEMBER_ID*}{+END}" class="form-table-field-input">
							<select name="select_{KEY*}{+START,IF_PASSED,MEMBER_ID}_{MEMBER_ID*}{+END}" id="select_{KEY*}{+START,IF_PASSED,MEMBER_ID}_{MEMBER_ID*}{+END}">
								{+START,IF_PASSED,USERNAME}
									<option {+START,IF,{$EQ,-1,{VALUE}}} selected="selected"{+END} value="-1">{$STRIP_TAGS,{!_UNSET}}</option>
								{+END}
								<option {+START,IF,{$EQ,,{VALUE}}} selected="selected"{+END} value="">{!NONE_EM}</option>
								{+START,LOOP,LIBRARY}
									<option {+START,IF,{$EQ,{EFFECT},{VALUE}}} selected="selected"{+END} value="{EFFECT*}">{EFFECT_SHORT*}</option>
								{+END}
								{+START,IF,{$EQ,{$SUBSTR,{VALUE},0,8},uploads/}}
									<option selected="selected" value="{VALUE*}">{!CUSTOM_UPLOAD}</option>
								{+END}
							</select>

							<input class="button_screen_item menu--social--chat--sound js-click-require-sound-selection" data-tp-select-id="select_{KEY*}{+START,IF_PASSED,MEMBER_ID}_{MEMBER_ID*}{+END}" type="button" title="{EFFECT_TITLE*}" value="{!TEST_SOUND}" />
						</td>
					</tr>

					<tr class="field-input">
						<th id="form_table_field_name__upload_{KEY*}{+START,IF_PASSED,MEMBER_ID}_{MEMBER_ID*}{+END}" class="form-table-field-name">
							<span class="form-field-name field-name">{!ALT_FIELD,{!UPLOAD}}</span>
						</th>

						<td id="form_table_field_input__upload_{KEY*}{+START,IF_PASSED,MEMBER_ID}_{MEMBER_ID*}{+END}" class="form-table-field-input">
							<div class="upload-field">
								<label class="accessibility_hidden" for="upload_{KEY*}{+START,IF_PASSED,MEMBER_ID}_{MEMBER_ID*}{+END}">{!ALT_FIELD,{!UPLOAD}}</label>
								<input name="upload_{KEY*}{+START,IF_PASSED,MEMBER_ID}_{MEMBER_ID*}{+END}" id="upload_{KEY*}{+START,IF_PASSED,MEMBER_ID}_{MEMBER_ID*}{+END}" type="file" />

								<input type="hidden" name="clear_button_upload_{KEY*}{+START,IF_PASSED,MEMBER_ID}_{MEMBER_ID*}{+END}" id="clear_button_upload_{KEY*}{+START,IF_PASSED,MEMBER_ID}_{MEMBER_ID*}{+END}" />
							</div>
						</td>
					</tr>
				{+END}
			</tbody>
		</table></div>
	</div>
</div>
