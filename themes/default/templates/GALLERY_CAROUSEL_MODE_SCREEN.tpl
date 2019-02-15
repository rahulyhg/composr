{$REQUIRE_JAVASCRIPT,galleries}
<div class="gallery-mode-screen gallery-carousel-mode-screen" itemscope="itemscope" itemtype="http://schema.org/ImageGallery" data-tpl="galleryCarouselModeScreen">
	{TITLE}

	{WARNING_DETAILS}

	{+START,IF_NON_EMPTY,{DESCRIPTION}}
		<div itemprop="descriptions">
			{$PARAGRAPH,{DESCRIPTION}}
		</div>
	{+END}

	{$SET,bound_catalogue_entry,{$CATALOGUE_ENTRY_FOR,gallery,{CAT}}}
	{+START,IF_NON_EMPTY,{$GET,bound_catalogue_entry}}{$CATALOGUE_ENTRY_ALL_FIELD_VALUES,{$GET,bound_catalogue_entry}}{+END}

	{+START,IF_NON_EMPTY,{CHILDREN}}
		<h2 class="heading-subgalleries">{!SUBGALLERIES}</h2>
	
		{CHILDREN}

		<h2 class="heading-images-and-videos">{!IMAGES_AND_VIDEOS_IN,{_TITLE}}</h2>
	{+END}

	{CURRENT_ENTRY}

	{$SET,support_mass_select,cms_galleries}

	{+START,IF_NON_EMPTY,{ENTRIES}}
		<div class="carousel-mode-other-gallery-images">
			<div class="head">
				<h2 class="heading">{!OTHER_IMAGES_IN_GALLERY}</h2>

				<div class="sorting-and-slideshow-btn">
					<ul class="horizontal-links with-icons">
						<li>
							<a {+START,IF,{$NOT,{$MOBILE}}} target="_blank" title="{!_SLIDESHOW} {!LINK_NEW_WINDOW}"{+END} href="{$PAGE_LINK*,_SELF:galleries:{FIRST_ENTRY_ID*}:slideshow=1:wide_high=1}">
								{+START,INCLUDE,ICON}
								NAME=buttons/proceed
								ICON_SIZE=24
								{+END}{!_SLIDESHOW}
							</a>
						</li>
						<li>{SORTING}</li>
					</ul>
				</div>
			</div>

			{$REQUIRE_CSS,widget_glide}
			{$REQUIRE_JAVASCRIPT,glide}

			<div class="glide glide-other-gallery-images" data-focus-class="focus-within">
				<div class="glide__track" data-glide-el="track">
					<div class="glide__slides">
						{ENTRIES}
					</div>
				</div>
				<div class="glide__arrows">
					<button class="btn btn-secondary btn-glide-go btn-glide-prev"><i class="chevron chevron-left"></i><span class="sr-only">{!PREVIOUS*}</span></button>
					<button class="btn btn-secondary btn-glide-go btn-glide-next"><i class="chevron chevron-right"></i><span class="sr-only">{!NEXT*}</span></button>
				</div>
			</div>

			{+START,INCLUDE,MASS_SELECT_DELETE_FORM}{+END}
		</div>
	{+END}

	{$SET,support_mass_select,}

	{+START,IF_EMPTY,{ENTRIES}{CURRENT_ENTRY}{CHILDREN}}
		<p class="nothing-here">
			{!NO_ENTRIES}
		</p>
	{+END}

	{+START,INCLUDE,NOTIFICATION_BUTTONS}
		NOTIFICATIONS_TYPE=gallery_entry
		NOTIFICATIONS_ID={CAT}
		BREAK=1
		RIGHT=1
	{+END}

	<div class="clearfix lined-up-boxes carousel-mode-details">
		{+START,IF_NON_EMPTY,{MEMBER_DETAILS}}
			<div class="right">
				<div class="box box---gallery-carousel-mode-screen--member"><div class="box-inner">
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
		4_ICON=admin/add_one_category
		5_ACCESSKEY=q
		5_URL={EDIT_URL*}
		5_TITLE={!EDIT_THIS_GALLERY}
		5_REL=edit
		5_ICON=admin/edit_this_category
		{+START,IF,{$ADDON_INSTALLED,tickets}}
			6_URL={$PAGE_LINK*,_SEARCH:report_content:content_type=gallery:content_id={CAT}:redirect={$SELF_URL&}}
			6_TITLE={!report_content:REPORT_THIS}
			6_ICON=buttons/report
			6_REL=report
		{+END}
	{+END}

	{+START,IF_NON_EMPTY,{ENTRIES}{CURRENT_ENTRY}}
		<div class="content-screen-comments">
			{COMMENT_DETAILS}
		</div>
	{+END}

	{+START,IF,{$THEME_OPTION,show_screen_actions}}{$BLOCK,failsafe=1,block=main_screen_actions,title={$METADATA,title}}{+END}

	{$,Uncomment the below if you want the root gallery to show recent and top content, then customise the GALLERY_POPULAR.tpl template to control specifics}
	{$,\{+START,INCLUDE,GALLERY_POPULAR\}\{+END\}}
</div>