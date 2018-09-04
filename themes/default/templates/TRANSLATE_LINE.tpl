{$REQUIRE_JAVASCRIPT,core_language_editing}

<tr data-tpl="translateLine">
	<th class="translate-line-first">
		<a id="jmp-{NAME*}"></a>

		<kbd>{NAME*}</kbd>

		{+START,IF_NON_EMPTY,{DESCRIPTION}}
			<p>
				{DESCRIPTION*}
			</p>
		{+END}
	</th>
	<td class="translate-line-second js-mouseover-enable-textarea-translate-field">
		<div class="accessibility-hidden"><label for="old--trans-{NAME*}">{!OLD} {NAME*}</label></div>
		<div>
			<textarea disabled="disabled" readonly="readonly" class="translate-original-text form-control" cols="60" rows="{$ADD,{$DIV,{$LENGTH,{OLD}},80},1}" id="old--trans-{NAME*}" name="old__{NAME*}">{OLD*}</textarea>
		</div>

		<div class="arrow-ruler"><img alt="" width="36" height="36" src="{$IMG*,arrow_ruler}" /></div>

		<div class="accessibility-hidden"><label for="trans-{NAME*}">{NAME*}</label></div>
		<div>
			<textarea {+START,IF,{$EQ,{OLD},{CURRENT}}} disabled="disabled"{+END} class="form-control translate-field js-textarea-translate-field" cols="60" rows="{+START,IF,{$EQ,{CURRENT},}}{$ADD,{$DIV,{$LENGTH,{OLD}},80},1}{+END}{+START,IF,{$NEQ,{CURRENT},}}{$ADD,{$DIV,{$LENGTH,{CURRENT}},80},1}{+END}" id="trans-{NAME*}" name="trans_{NAME*}">{CURRENT*}</textarea>
		</div>
	</td>
	{+START,IF_NON_EMPTY,{ACTIONS}}
		<td>
			{ACTIONS}
		</td>
	{+END}
</tr>
<tr id="rexp-{NAME*}" style="display: none">
	<td colspan="{$?,{$IS_EMPTY,{ACTIONS}},3,4}">
		<div id="exp-{NAME*}"></div>
	</td>
</tr>
