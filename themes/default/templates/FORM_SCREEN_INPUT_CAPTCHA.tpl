{+START,IF_EMPTY,{$CONFIG_OPTION,recaptcha_site_key}}
	{+START,SET,CAPTCHA}
		<div class="captcha">
			{+START,IF,{$CONFIG_OPTION,audio_captcha}}
				<a class="js-click-play-self-audio-link" title="{!captcha:PLAY_AUDIO_VERSION}" href="{$FIND_SCRIPT*,captcha,1}?mode=audio&amp;cache_break={$RAND}{$KEEP*,0,1}">{!captcha:PLAY_AUDIO_VERSION}</a>
			{+END}
			{+START,IF,{$CONFIG_OPTION,css_captcha}}
				<iframe{$?,{$BROWSER_MATCHES,ie}, frameBorder="0" scrolling="no"} id="captcha_readable" class="captcha_frame" title="{!CONTACT_STAFF_TO_JOIN_IF_IMPAIRED}" src="{$FIND_SCRIPT*,captcha}?cache_break={$RAND&*}{$KEEP*,0,1}">{!CONTACT_STAFF_TO_JOIN_IF_IMPAIRED}</iframe>
			{+END}
			{+START,IF,{$NOT,{$CONFIG_OPTION,css_captcha}}}
				<img id="captcha_readable" title="{!CONTACT_STAFF_TO_JOIN_IF_IMPAIRED}" alt="{!CONTACT_STAFF_TO_JOIN_IF_IMPAIRED}" src="{$FIND_SCRIPT*,captcha}?cache_break={$RAND&*}{$KEEP*,0,1}" />
			{+END}
		</div>
		<div class="accessibility_hidden"><label for="captcha">{!captcha:AUDIO_CAPTCHA_HELP}</label></div>
		<input {+START,IF_PASSED,TABINDEX} tabindex="{TABINDEX*}"{+END} maxlength="6" size="8" class="input_text_required" type="text" id="captcha" name="captcha" />
	{+END}

	<div data-tpl="formScreenInputCaptcha" data-tpl-params="{+START,PARAMS_JSON,CAPTCHA}{_*}{+END}">
		{+START,IF,{$CONFIG_OPTION,js_captcha}}
			<div id="captcha_spot"></div>
		{+END}
		{+START,IF,{$NOT,{$CONFIG_OPTION,js_captcha}}}
			{$GET,CAPTCHA}
		{+END}
	</div>
{+END}

{+START,IF_NON_EMPTY,{$CONFIG_OPTION,recaptcha_site_key}}
	<div data-recaptcha-captcha id="captcha"{+START,IF_PASSED,TABINDEX} data-tabindex="{TABINDEX*}"{+END} ></div>
{+END}
