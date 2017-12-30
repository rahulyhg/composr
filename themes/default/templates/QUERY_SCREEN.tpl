{TITLE}

<p>
	{!QUERIES_WITHOUT_CACHE}
</p>

<div class="wide-table-wrap-framed"><table class="columned_table wide-table results-table autosized-table">
	<thead>
		<tr>
			<th>
				{!QUERY}
			</th>
			<th axis="time">
				{!EXECUTE_TIME}
			</th>
			<th>
				{!RESULTS}
			</th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<td>
				<strong>{!COUNT_TOTAL}</strong>: {TOTAL}
			</td>
			<td>
				<strong>{TOTAL_TIME}</strong>
			</td>
			<td>
			</td>
		</tr>
	</tfoot>

	<tbody>
		{QUERIES}
	</tbody>
</table></div>
