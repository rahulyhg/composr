{+START,IF_PASSED,QUICK_VERSION}{+START,IF_PASSED,QUICK_FILESIZE}{+START,IF_PASSED,QUICK_URL}
	<div class="dlHolder">
		<div class="dlHead grn">
			Automatic extractor <span>Recommended</span>
		</div>

		<div class="dlBody">
			<p>This package ("quick installer") will self-extract on your server and automatically set all permissions.</p>

			<p>Works on most servers (needs PHP FTP support or suEXEC on your server).</p>

			<div class="sept"></div>

			<p><a class="alLft niceLink" href="{QUICK_URL*}">Download &dtrif;</a> <a class="alRht" href="#">{QUICK_VERSION*} | {QUICK_FILESIZE*}</a></p>
		</div>
	</div>
{+END}{+END}{+END}

{+START,IF_PASSED,MANUAL_VERSION}{+START,IF_PASSED,MANUAL_FILESIZE}{+START,IF_PASSED,MANUAL_URL}
	<div class="dlHolder">
		<div class="dlHead blu">
			Manual extractor <span>Slower; requires chmodding</span>
		</div>

		<div class="dlBody">
			<p>This is a ZIP containing all Composr files (several thousand). It is much slower, and only recommended if you cannot use the quick installer. Some chmodding is required.</p>

			<p><strong>Do not use this for upgrading.</strong></p>

			<div class="sept"></div>

			<p><a class="alLft niceLink" href="{MANUAL_URL*}">Download &dtrif;</a> <a class="alRht" href="{MANUAL_URL*}">{MANUAL_VERSION*} | {MANUAL_FILESIZE*}</a></p>
		</div>
	</div>
{+END}{+END}{+END}

{+START,IF_PASSED,BLEEDINGMANUAL_VERSION}{+START,IF_PASSED,BLEEDINGMANUAL_FILESIZE}{+START,IF_PASSED,BLEEDINGMANUAL_URL}
{+START,IF_PASSED,BLEEDINGQUICK_VERSION}{+START,IF_PASSED,BLEEDINGQUICK_FILESIZE}{+START,IF_PASSED,BLEEDINGQUICK_URL}
	<div class="dlHolder">
		<div class="dlHead">
			{+START,IF,{$IN_STR,{BLEEDINGQUICK_VERSION},RC}}
				Future track <span>Fairly polished</span>
			{+END}
			{+START,IF,{$NOT,{$IN_STR,{BLEEDINGQUICK_VERSION},RC}}}
				Bleeding edge <span>Unstable</span>
			{+END}
		</div>

		<div class="dlBody">
			<p>Are you able to {$?,{$IN_STR,{BLEEDINGQUICK_VERSION},alpha},alpha,{$?,{$IN_STR,{BLEEDINGQUICK_VERSION},RC},release,beta}}-test the new version: {BLEEDINGQUICK_VERSION*}?<br />
			It {$?,{$IN_STR,{BLEEDINGQUICK_VERSION},alpha},<strong>will not be stable</strong> like,<strong>may not be as stable</strong> as} our main version{+START,IF_PASSED,QUICK_VERSION} ({QUICK_VERSION*}){+END}.</p>

			<div class="sept"></div>

			<p><a class="alLft niceLink" href="{BLEEDINGQUICK_URL*}">Download automatic extractor &dtrif;</a> <a class="alRht niceLink" href="{BLEEDINGMANUAL_URL*}">Download manual extractor &dtrif;</a></p>
		</div>
	</div>
{+END}{+END}{+END}
{+END}{+END}{+END}
