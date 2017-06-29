<form title="{!MAKE_PAYMENT}" class="ecommerce_button" action="{FORM_URL*}" method="post" autocomplete="off">
	<input type="hidden" name="clientAccnum" value="{ACCOUNT_NUM*}" /> {$,An integer value representing the 6-digit client account number.}
	<input type="hidden" name="clientSubacc" value="{SUBACCOUNT_NUM*}" /> {$,An integer value representing the 4-digit client subaccount number.}
	<input type="hidden" name="formName" value="{FORM_NAME*}" /> {$,The name of the form.}
	<input type="hidden" name="formPrice" value="{AMOUNT*}" /> {$,A decimal value representing the initial price.}
	<input type="hidden" name="formPeriod" value="{FORM_PERIOD*}" /> {$,An integer representing the length, in days, of the initial billing period.}
	<input type="hidden" name="currencyCode" value="{CURRENCY*}" /> {$,An integer representing the 3-digit currency code that will be used for the transaction.}
	<input type="hidden" name="formDigest" value="{DIGEST*}" /> {$,An MD5 Hex Digest based on the above values}
	<input type="hidden" name="productDesc" value="{!SUBSCRIPTION_FOR,{$USERNAME*}} ({ITEM_NAME*})" /> {$,Hopefully shown to the customer when paying}
	<input type="hidden" name="customPurchaseId" value="{TRANS_EXPECTING_ID*}" /> {$,Custom variable for tracking purchase ID}
	<input type="hidden" name="customItemName" value="{ITEM_NAME*}" /> {$,Custom variable for tracking item name}
	<input type="hidden" name="customIsSubscription" value="1" /> {$,Custom variable for subscription status}
	{+START,IF_NON_EMPTY,{MEMBER_ADDRESS}}
		{+START,LOOP,MEMBER_ADDRESS}
			{+START,IF_NON_EMPTY,{_loop_key*}}
				{+START,IF_NON_EMPTY,{_loop_var*}}
					<input type="hidden" name="{_loop_key*}" value="{_loop_var*}" />
				{+END}
			{+END}
		{+END}
	{+END}

	<input type="hidden" name="formRecurringPrice" value="{AMOUNT*}" /> {$,A decimal value representing the recurring billing price}
	<input type="hidden" name="formRecurringPeriod" value="{FORM_PERIOD*}" /> {$,An integer representing the number of days between each rebill.}
	<input type="hidden" name="formRebills" value="99" /> {$,An integer representing the total times the subscription will rebill. Passing a value of 99 will cause the subscription to rebill indefinitely}

	<div class="purchase_button">
		<input id="purchase_button" data-disable-on-click="1" class="button_screen menu__rich_content__ecommerce__purchase" type="submit" value="{!MAKE_PAYMENT}" />
	</div>
</form>
