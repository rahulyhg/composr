<section id="tray_{TITLE|}" data-toggleable-tray="{ save: true }" class="box box___block_main_forum_topics">
	<h3 class="toggleable-tray-title js-tray-header">
		<a class="toggleable-tray-button" data-click-tray-toggle="#tray_{TITLE|}" href="#!"><img alt="{!CONTRACT}: {$STRIP_TAGS,{TITLE}}" title="{!CONTRACT}" src="{$IMG*,1x/trays/contract2}" /></a>

		{+START,IF_NON_EMPTY,{TITLE}}
			<a class="toggleable-tray-button" data-click-tray-toggle="#tray_{TITLE|}" href="#!">{TITLE}</a>
		{+END}
	</h3>

	<div class="toggleable-tray js-tray-content">
		{+START,LOOP,TOPICS}
			<div class="box box___block_main_forum_topics_topic"><div class="box-inner">
				<p class="tiny-paragraph">
					<a title="{$STRIP_TAGS,{TITLE}}" href="{TOPIC_URL*}">{$TRUNCATE_LEFT,{TITLE},30,0,1}</a>
				</p>

				<div role="note">
					<ul class="tiny-paragraph associated-details horizontal-meta-details">
						<li>{!BY_SIMPLE,{$DISPLAYED_USERNAME*,{USERNAME}}}</li>
						<li>{!POST_PLU,{NUM_POSTS*}}</li>
					</ul>
				</div>

				<p class="tiny-paragraph associated-details">
					<span class="field-name">{!LAST_POST}:</span> {DATE*}
				</p>
			</div></div>
		{+END}

		{+START,IF_NON_EMPTY,{SUBMIT_URL}}
			<ul class="horizontal-links associated-links-block-group force_margin">
				<li><a href="{SUBMIT_URL*}">{!ADD_TOPIC}</a></li>
			</ul>
		{+END}
	</div>
</section>
