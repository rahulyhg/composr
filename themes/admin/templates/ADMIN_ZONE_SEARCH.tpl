{$REQUIRE_JAVASCRIPT,admin}

{+START,IF,{$HAS_ACTUAL_PAGE_ACCESS,admin,adminzone}}
	<div class="adminzone_search" data-require-javascript="core" data-tpl="adminZoneSearch">
		<form title="{!SEARCH}" action="{$URL_FOR_GET_FORM*,{$PAGE_LINK,adminzone:admin:search}}" method="get" class="inline" autocomplete="off">
			<div id="adminzone_search_hidden" class="js-adminzone-search-hiddens">
				{$HIDDENS_FOR_GET_FORM,{$PAGE_LINK,adminzone:admin:search}}
			</div>

			<div>
				<label for="search_content" class="accessibility_hidden">{!SEARCH}</label>
				<input size="25" type="search" id="search_content" name="content" placeholder="{!SEARCH*}" />
				<div class="accessibility_hidden"><label for="new_window">{!NEW_WINDOW}</label></div>
				<input title="{!NEW_WINDOW}" type="checkbox" value="1" id="new_window" name="new_window" />
				<button type="submit" class="button_screen_item buttons__search js-click-btn-admin-search" data-tp-hiddens="{$HIDDENS_FOR_GET_FORM*,{$PAGE_LINK,adminzone:admin:search}}" data-tp-action-url="{$URL_FOR_GET_FORM*,{$PAGE_LINK,adminzone:admin:search}}">{+START,IF,{$DESKTOP}}<span class="inline_desktop">{!SEARCH_ADMIN}</span>{+END}<span class="inline_mobile">{!SEARCH}</span></button>
				<input type="submit" value="{!SEARCH_TUTORIALS}" class="button_screen_item buttons__menu__pages__help js-click-btn-admin-search-tutorials" data-tp-hiddens="{$HIDDENS_FOR_GET_FORM*,{$BRAND_BASE_URL}/index.php?page=search&type=results}" data-tp-action-url="{$URL_FOR_GET_FORM*,{$BRAND_BASE_URL}/index.php?page=search&type=results}" />
			</div>
		</form>
	</div>
{+END}
