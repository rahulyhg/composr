<form title="{!MAKE_PAYMENT}" class="ecommerce_button" action="{FORM_URL*}" method="post" autocomplete="off">
	<input type="hidden" name="x_fp_sequence" value="{SEQUENCE*}" />
	<input type="hidden" name="x_fp_timestamp" value="{TIMESTAMP*}" />
	<input type="hidden" name="x_fp_hash" value="{FINGERPRINT*}" />
	<input type="hidden" name="x_description" value="{TRANS_EXPECTING_ID*} - {!SUBSCRIPTION_FOR,{$USERNAME*}} ({ITEM_NAME*})" />
	<input type="hidden" name="x_login" value="{LOGIN_ID*}" />
	<input type="hidden" name="x_amount" value="{AMOUNT*}" />
	<input type="hidden" name="x_tax" value="{TAX*}" />
	<input type="hidden" name="x_tax_exempt" value="N" />
	<input type="hidden" name="x_show_form" value="PAYMENT_FORM" />
	<input type="hidden" name="x_test_request" value="{$?,{IS_TEST},TRUE,FALSE}" />
	<input type="hidden" name="x_cust_id" value="{CUST_ID*}" />
	<input type="hidden" name="x_currency_code" value="{CURRENCY*}" />
	<input type="hidden" name="x_relay_response" value="TRUE" />
	<input type="hidden" name="x_relay_url" value="{$PAGE_LINK*,_SEARCH:purchase:finish:type_code={TYPE_CODE}:from=authorize}" />
	{+START,IF_NON_EMPTY,{MEMBER_ADDRESS}}
		<input type="hidden" name="address_override" value="1" />
		{+START,LOOP,MEMBER_ADDRESS}
			{+START,IF_NON_EMPTY,{_loop_key*}}{+START,IF_NON_EMPTY,{_loop_var*}}
				<input type="hidden" name="{_loop_key*}" value="{_loop_var*}" />
			{+END}{+END}
		{+END}
	{+END}

	<input type="hidden" name="x_recurring_billing" value="TRUE" />

	<div class="purchase_button">
		<input id="purchase_button" data-disable-on-click="1" class="button_screen menu__rich_content__ecommerce__purchase" type="submit" value="{!MAKE_PAYMENT}" alt="Authorize.net - Simple Checkout" />
	</div>
</form>
