{$REQUIRE_JAVASCRIPT,core_cns}

<section id="tray_{!MEMBER|}" data-tpl="cnsGuestBar" data-toggleable-tray="{ save: true }" class="box cns_information_bar_outer">
	<h2 class="toggleable_tray_title js-tray-header">
		<a class="toggleable_tray_button js-tray-onclick-toggle-tray inline_desktop" href="#!"><img alt="{!CONTRACT}: {$STRIP_TAGS,{!_LOGIN}}" title="{!CONTRACT}" src="{$IMG*,1x/trays/contract2}" /></a>

		<a class="toggleable_tray_button js-tray-onclick-toggle-tray" href="#!">{!_LOGIN}{+START,IF,{$HAS_ACTUAL_PAGE_ACCESS,search}} / {!SEARCH}{+END}</a>
	</h2>

	<div class="toggleable_tray js-tray-content">
		<div class="cns_information_bar float-surrounder">
			<div class="cns_guest_column cns_guest_column_a">
				<form title="{!_LOGIN}" class="inline js-submit-check-field-login-username" action="{LOGIN_URL*}" method="post" autocomplete="on">
					{$INSERT_SPAMMER_BLACKHOLE}

					<div>
						<div class="accessibility_hidden"><label for="member_bar_login_username">{$LOGIN_LABEL}</label></div>
						<div class="accessibility_hidden"><label for="member_bar_s_password">{!PASSWORD}</label></div>
						<input size="15" type="text" placeholder="{!USERNAME}" id="member_bar_login_username" name="login_username" />
						<input size="15" type="password" placeholder="{!PASSWORD}" name="password" id="member_bar_s_password" />
						{+START,IF,{$CONFIG_OPTION,password_cookies}}
							<label for="remember">{!REMEMBER_ME}:</label>
							<input class="{+START,IF,{$NOT,{$CONFIG_OPTION,remember_me_by_default}}}js-click-checkbox-remember-me-confirm{+END}"{+START,IF,{$CONFIG_OPTION,remember_me_by_default}} checked="checked"{+END} type="checkbox" value="1" id="remember" name="remember" />
						{+END}
						<input class="button_screen_item menu__site_meta__user_actions__login" type="submit" value="{!_LOGIN}" />

						<ul class="horizontal_links associated-links-block-group">
							<li><a href="{JOIN_URL*}">{!_JOIN}</a></li>
							<li><a data-open-as-overlay="{}" rel="nofollow" href="{FULL_LOGIN_URL*}" title="{!MORE}: {!_LOGIN}">{!MORE}</a></li>
						</ul>
					</div>
				</form>
			</div>
			{+START,IF,{$ADDON_INSTALLED,search}}{+START,IF,{$HAS_ACTUAL_PAGE_ACCESS,search}}
				<div class="cns_guest_column cns_guest_column_c">
					{+START,INCLUDE,MEMBER_BAR_SEARCH}{+END}
				</div>
			{+END}{+END}

			<nav class="cns_guest_column cns_member_column_d">
				{$,<p class="cns_member_column_title">{!VIEW}:</p>}
				<ul class="actions_list">
					<li><a data-open-as-overlay="{}" href="{NEW_POSTS_URL*}">{!POSTS_SINCE}</a></li>
					<li><a data-open-as-overlay="{}" href="{UNANSWERED_TOPICS_URL*}">{!UNANSWERED_TOPICS}</a></li>
				</ul>
			</nav>
		</div>
	</div>
</section>
