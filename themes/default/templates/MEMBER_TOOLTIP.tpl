{+START,IF_PASSED,SUBMITTER}{+START,IF_NON_EMPTY,{SUBMITTER}}{+START,IF,{$NOT,{$IS_GUEST,{SUBMITTER}}}}
	{+START,IF,{$CNS}}
		{+START,IF,{$OR,{$ADDON_INSTALLED,cns_avatars},{$IS_NON_EMPTY,{$AVATAR,{SUBMITTER}}}}}
						<span data-tpl="memberTooltip" data-tpl-params="{+START,PARAMS_JSON,SUBMITTER}{_*}{+END}">
				<img class="embedded-mini-avatar js-mouseover-activate-member-tooltip js-mouseout-deactivate-member-tooltip" src="{$THUMBNAIL*,{$?,{$IS_EMPTY,{$AVATAR,{SUBMITTER}}},{$IMG,cns_default_avatars/default},{$AVATAR,{SUBMITTER}}},50,,,{$IMG,cns_default_avatars/default}}" alt="" />
			</span>
		{+END}
	{+END}
{+END}{+END}{+END}
