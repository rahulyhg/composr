<div itemscope="itemscope" itemtype="http://schema.org/ImageGallery">
	{TITLE}

	{WARNING_DETAILS}

	{+START,IF_NON_EMPTY,{DESCRIPTION}}
		<div itemprop="descriptions">
			{$PARAGRAPH,{DESCRIPTION}}
		</div>
	{+END}

	{$SET,bound_catalogue_entry,{$CATALOGUE_ENTRY_FOR,gallery,{CAT}}}
	{+START,IF_NON_EMPTY,{$GET,bound_catalogue_entry}}{$CATALOGUE_ENTRY_ALL_FIELD_VALUES,{$GET,bound_catalogue_entry}}{+END}

	{CHILDREN}

	{CURRENT_ENTRY}

	{$SET,support_mass_select,cms_galleries}

	{+START,IF_NON_EMPTY,{ENTRIES}}
		<div class="box box___gallery_flow_mode_screen__other"><div class="box-inner">
			<h2>{!OTHER_IMAGES_IN_GALLERY}</h2>

			{$REQUIRE_JAVASCRIPT,core_rich_media}
			{$REQUIRE_CSS,carousels}

			{$SET,carousel_id,{$RAND}}

			<div id="carousel_{$GET*,carousel_id}" class="carousel" data-view="Carousel" data-view-params="{+START,PARAMS_JSON,carousel_id}{_*}{+END}">
				<div class="move-left js-btn-car-move" data-move-amount="-100"></div>
				<div class="move-right js-btn-car-move" data-move-amount="+100"></div>

				<div class="main" itemprop="significantLinks">
				</div>
			</div>

			<div class="carousel-temp" id="carousel_ns_{$GET*,carousel_id}">
				{ENTRIES}
			</div>

			<hr />

			<ul class="horizontal-links associated-links-block-group">
				<li>{SORTING}</li>
				<li><img src="{$IMG*,icons/24x24/buttons/proceed}" srcset="{$IMG*,icons/48x48/buttons/slideshow} 2x" alt="" /> <a {+START,IF,{$NOT,{$MOBILE}}} target="_blank" title="{!_SLIDESHOW} {!LINK_NEW_WINDOW}"{+END} href="{$PAGE_LINK*,_SELF:galleries:{FIRST_ENTRY_ID*}:slideshow=1:wide_high=1}">{!_SLIDESHOW}</a></li>
			</ul>

			{+START,INCLUDE,MASS_SELECT_DELETE_FORM}
			{+END}
		</div></div>
	{+END}

	{$SET,support_mass_select,}

	{+START,IF_EMPTY,{ENTRIES}{CURRENT_ENTRY}{CHILDREN}}
		<p class="nothing_here">
			{!NO_ENTRIES}
		</p>
	{+END}

	{+START,INCLUDE,NOTIFICATION_BUTTONS}
		NOTIFICATIONS_TYPE=gallery_entry
		NOTIFICATIONS_ID={CAT}
		BREAK=1
		RIGHT=1
	{+END}

	<div class="float-surrounder lined_up_boxes flow-mode-details">
		{+START,IF_NON_EMPTY,{MEMBER_DETAILS}}
			<div class="right">
				<div class="box box___gallery_flow_mode_screen__member"><div class="box-inner">
					<h2>{_TITLE*}</h2>

					{MEMBER_DETAILS}
				</div></div>
			</div>

			{+START,IF_NON_EMPTY,{ENTRIES}{CURRENT_ENTRY}}
				<div class="ratings right">
					{RATING_DETAILS}
				</div>
			{+END}
		{+END}
	</div>

	{$REVIEW_STATUS,gallery,{CAT}}

	{+START,IF,{$THEME_OPTION,show_content_tagging}}{TAGS}{+END}

	{$,Load up the staff actions template to display staff actions uniformly (we relay our parameters to it)...}
	{+START,INCLUDE,STAFF_ACTIONS}
		1_URL={IMAGE_URL*}
		1_TITLE={!ADD_IMAGE}
		1_REL=add
		1_ICON=menu/cms/galleries/add_one_image
		2_URL={VIDEO_URL*}
		2_TITLE={!ADD_VIDEO}
		2_REL=add
		2_ICON=menu/cms/galleries/add_one_video
		3_URL={$?,{$OR,{$NOT,{$HAS_PRIVILEGE,may_download_gallery}},{$IS_EMPTY,{ENTRIES}}},,{$FIND_SCRIPT*,download_gallery}?cat={CAT*}{$KEEP*,0,1}}
		3_TITLE={!DOWNLOAD_GALLERY_CONTENTS}
		3_ICON=links/download_as_archive
		4_URL={ADD_GALLERY_URL*}
		4_TITLE={!ADD_GALLERY}
		4_REL=edit
		4_ICON=menu/_generic_admin/add_one_category
		5_ACCESSKEY=q
		5_URL={EDIT_URL*}
		5_TITLE={!EDIT_GALLERY}
		5_REL=edit
		5_ICON=menu/_generic_admin/edit_this_category
		{+START,IF,{$ADDON_INSTALLED,tickets}}
			6_URL={$PAGE_LINK*,_SEARCH:report_content:content_type=gallery:content_id={CAT}:redirect={$SELF_URL&}}
			6_TITLE={!report_content:REPORT_THIS}
			6_ICON=buttons/report
			6_REL=report
		{+END}
	{+END}

	{+START,IF_NON_EMPTY,{ENTRIES}{CURRENT_ENTRY}}
		<div class="content_screen_comments">
			{COMMENT_DETAILS}
		</div>
	{+END}

	{+START,IF,{$THEME_OPTION,show_screen_actions}}{$BLOCK,failsafe=1,block=main_screen_actions,title={$METADATA,title}}{+END}

	{$,Uncomment the below if you want the root gallery to show recent and top content, then customise the GALLERY_POPULAR.tpl template to control specifics}
	{$,\{+START,INCLUDE,GALLERY_POPULAR\}\{+END\}}
</div>
