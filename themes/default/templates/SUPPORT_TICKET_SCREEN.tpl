{$REQUIRE_JAVASCRIPT,tickets}
<div data-tpl="supportTicketScreen" data-tpl-params="{+START,PARAMS_JSON,SERIALIZED_OPTIONS,HASH}{_*}{+END}">
	{TITLE}

	{$SET,ticket_merge_into,{ID}}

	{+START,INCLUDE,HANDLE_CONFLICT_RESOLUTION}{+END}
	{+START,IF_PASSED,WARNING_DETAILS}
		{WARNING_DETAILS}
	{+END}

	{+START,IF,{NEW}}
		<div class="ticket_page_text">
			{TICKET_PAGE_TEXT}
		</div>
	{+END}
	{+START,IF,{$NOT,{NEW}}}
		{+START,IF_NON_EMPTY,{$GET,ticket_page_existing_text}}
			<div class="ticket_page_text">
				{$GET,ticket_page_existing_text}
			</div>
		{+END}

		<p class="lonely-label">{!_ASSIGNED_TO}</p>
		<ul class="spaced_list">
			{+START,IF_NON_EMPTY,{ASSIGNED}}
				{+START,LOOP,ASSIGNED}
					<li>
						<span>{_loop_var*}</span>

						{+START,IF,{$HAS_PRIVILEGE,support_operator}}
							<form title="{!_ASSIGNED_TO}" action="{$PAGE_LINK*,_SEARCH:tickets:unassign:ticket_id={ID}:member_id={_loop_key}}" method="post" class="inline vertical_alignment" autocomplete="off">
								{$INSERT_SPAMMER_BLACKHOLE}

								<input class="button-micro menu___generic_admin__delete" type="submit" value="{!REMOVE}" />
							</form>
						{+END}
					</li>
				{+END}
			{+END}

			{+START,IF_EMPTY,{ASSIGNED}}
				<li><em>{!UNASSIGNED}</em></li>
			{+END}

			{+START,IF,{$HAS_PRIVILEGE,support_operator}}
				{$REQUIRE_JAVASCRIPT,ajax_people_lists}
				<li>
					<form title="{!ASSIGN_TO}" action="{$PAGE_LINK*,_SEARCH:tickets:assign:ticket_id={ID}}" method="post" class="inline vertical_alignment" autocomplete="off">
						{$INSERT_SPAMMER_BLACKHOLE}

						<label for="assign_username" class="accessibility_hidden">{!USERNAME}</label>
						<input {+START,IF,{$MOBILE}} autocorrect="off"{+END} autocomplete="off" maxlength="255" class="input-username js-focus-update-ajax-member-list js-keyup-update-ajax-member-list" type="text" id="assign_username" name="username" value="{$USERNAME*}" />
						<input class="button-micro buttons--proceed" type="submit" value="{!ASSIGN_TO}" />
					</form>
				</li>
			{+END}
		</ul>
	{+END}

	<div>
		{+START,SET,EXTRA_COMMENTS_FIELDS_1}
			{+START,IF,{NEW}}
				<tr>
					<th class="de-th">
						<span class="field-name"><label for="ticket_type_id">{!TICKET_TYPE}:</label></span>
					</th>
					<td>
						<select id="ticket_type_id" name="ticket_type_id" class="input-list-required wide-field">
							<option value="">---</option>
							{+START,LOOP,TYPES}
								<option value="{TICKET_TYPE_ID*}"{+START,IF,{SELECTED}} selected="selected"{+END}>{NAME*}</option>{$,You can also use {LEAD_TIME} to get the ticket type's lead time}
							{+END}
						</select>
						<div id="error_ticket_type_id" style="display: none" class="input-error-here"></div>
					</td>
				</tr>
			{+END}

			{+START,IF_NON_EMPTY,{POST_TEMPLATES}}
				<tr>
					<th class="de-th">
						<span class="field-name"><label for="post_template">{!cns_post_templates:POST_TEMPLATE}:</label></span>
					</th>
					<td>
						{POST_TEMPLATES}
					</td>
				</tr>
			{+END}
		{+END}

		<div id="comments_wrapper">
			{COMMENTS}
		</div>

		{+START,IF_PASSED,PAGINATION}
			<div class="float-surrounder">
				{PAGINATION}
			</div>
		{+END}

		{+START,IF_NON_EMPTY,{STAFF_DETAILS}}
			{$,Load up the staff actions template to display staff actions uniformly (we relay our parameters to it)...}
			{+START,INCLUDE,STAFF_ACTIONS}
				1_URL={STAFF_DETAILS*}
				1_TITLE={!VIEW_COMMENT_TOPIC}
				1_ICON=feedback/comments_topic
				{+START,IF_PASSED,TICKET_TYPE_ID}
					{+START,IF,{$NEQ,{USERNAME},{$USERNAME}}}
						2_URL={$PAGE_LINK*,_SEARCH:tickets:ticket:default={TICKET_TYPE_ID}:post={!TICKET_SPLIT_POST&,{USERNAME}}:post_as={USERNAME}}
						2_TITLE={!STAFF_NEW_TICKET_AS,{USERNAME}}
						2_ICON=buttons/add_ticket
					{+END}
				{+END}
			{+END}
		{+END}
	</div>

	{+START,IF_NON_EMPTY,{EXTRA_DETAILS}}
		<br />
		{EXTRA_DETAILS}
	{+END}

	{+START,SET,EXTRA_COMMENTS_FIELDS_2}
		{+START,IF,{STAFF_ONLY}}
			<tr>
				<th class="de-th">
					<span class="field-name">{!TICKET_STAFF_ONLY}:</span>
				</th>
				<td class="one_line">
					<label for="staff_only"><input type="checkbox" id="staff_only" name="staff_only" value="1" /> {!TICKET_STAFF_ONLY_DESCRIPTION}</label>
				</td>
			</tr>
		{+END}

		{+START,IF,{$NOT,{CLOSED}}}{+START,IF,{$NOT,{NEW}}}{+START,IF,{$CNS}}
			<tr>
				<th class="de-th">
					<span class="field-name">{!CLOSE_TICKET}:</span>
				</th>
				<td class="one_line">
					<label for="close"><input type="checkbox" id="close" name="close" value="1" /> {!DESCRIPTION_CLOSE_TICKET}</label>
				</td>
			</tr>
		{+END}{+END}{+END}
	{+END}

	{$SET,COMMENT_POSTING_ROWS,20}

	{+START,IF_NON_EMPTY,{COMMENT_FORM}}
		<form title="{!PRIMARY_PAGE_FORM}" id="comments_form" class="js-submit-check-post-and-ticket-type-id-fields" action="{URL*}" method="post" enctype="multipart/form-data" itemscope="itemscope" itemtype="http://schema.org/ContactPage" autocomplete="off">
			{$INSERT_SPAMMER_BLACKHOLE}

			{COMMENT_FORM}
		</form>

		<hr class="spaced-rule" />
	{+END}

	<div class="buttons-group buttons-group-faded">
		{+START,IF,{$NEQ,{$_GET,type},ticket}}
			{+START,INCLUDE,BUTTON_SCREEN}
				TITLE={!CREATE_SUPPORT_TICKET}
				IMG=add_ticket
				URL={ADD_TICKET_URL}
				IMMEDIATE=0
			{+END}
		{+END}

		{+START,IF_PASSED,SET_TICKET_EXTRA_ACCESS_URL}
			{+START,INCLUDE,BUTTON_SCREEN}
				TITLE={!_SET_TICKET_EXTRA_ACCESS}
				IMG=menu__adminzone__security__permissions__privileges
				URL={SET_TICKET_EXTRA_ACCESS_URL}
				IMMEDIATE=0
			{+END}
		{+END}

		{+START,IF_PASSED,EDIT_URL}
			{+START,INCLUDE,BUTTON_SCREEN}
				TITLE={!EDIT_TICKET}
				IMG=buttons--save
				URL={EDIT_URL}
				IMMEDIATE=0
			{+END}
		{+END}

		{+START,IF_PASSED,TOGGLE_TICKET_CLOSED_URL}
			{+START,INCLUDE,BUTTON_SCREEN}
				TITLE={$?,{CLOSED},{!OPEN_TICKET},{!CLOSE_TICKET}}
				IMG={$?,{CLOSED},buttons--closed,buttons--clear}
				IMMEDIATE=1
				URL={TOGGLE_TICKET_CLOSED_URL}
			{+END}
		{+END}
	</div>

	{+START,IF,{$HAS_PRIVILEGE,support_operator}}
		{+START,IF_NON_EMPTY,{WHOS_READ}}
			<h2>{!THIS_HAS_BEEN_READ_BY}</h2>

			<ul class="nl">
				{+START,LOOP,WHOS_READ}
					<li><a title="{USERNAME*}" href="{MEMBER_URL*}">{$DISPLAYED_USERNAME*,{USERNAME}}</a> {+START,INCLUDE,MEMBER_TOOLTIP}SUBMITTER={MEMBER_ID}{+END} &ndash; <span class="associated-details">{DATE*}</span></li>
				{+END}
			</ul>
		{+END}
	{+END}

	<h2>{!OTHER_TICKETS_BY_MEMBER,{$DISPLAYED_USERNAME*,{USERNAME}}}</h2>

	{+START,IF_EMPTY,{OTHER_TICKETS}}
		<p class="nothing_here">{!NONE}</p>
	{+END}
	{+START,IF_NON_EMPTY,{OTHER_TICKETS}}
		<div class="wide-table-wrap"><table class="columned_table results-table wide-table support_tickets autosized-table responsive-table">
			<thead>
				<tr>
					<th>
						{!SUPPORT_TICKET}
					</th>
					<th>
						{!TICKET_TYPE}
					</th>
					{+START,IF,{$DESKTOP}}
						<th class="cell-desktop">
							{!COUNT_POSTS}
						</th>
					{+END}
					<th>
						{!LAST_POST_BY}
					</th>
					<th>
						{!DATE}
					</th>
					<th>
						{!ASSIGNED_TO}
					</th>
					{+START,IF,{$HAS_PRIVILEGE,support_operator}}
						<th>
							{!ACTIONS}
						</th>
					{+END}
				</tr>
			</thead>
			<tbody>
				{OTHER_TICKETS}
			</tbody>
		</table></div>
	{+END}

	{$SET,ticket_merge_into,}
</div>
