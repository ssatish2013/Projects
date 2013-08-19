{capture assign='stylesheets'}
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/themes/base/jquery-ui.css" />
{/capture}
{include file='common/adminHeader.tpl'}
<ol class="tabs">
{foreach from=$events item=event}
	<li data-graph="{$event->name}"><a href="#{$event->name}">{ucfirst($event->name)}</a></li>
{/foreach}
</ol>
<div>
	<form id="dashboardRangeForm" >
		<div id="ranges">
			<label>
				<input type="radio" name="range" value="today" checked="checked" />
				Today
			</label>
			<label>
				<input type="radio" name="range" value="yesterday" />
				Yesterday
			</label>
			<label>
				<input type="radio" name="range" value="week" />
				Last 7 Days
			</label>
			<label>
				<input type="radio" name="range" value="month" />
				Last 30 days
			</label>
			{*
			<label>
				<input type="radio" name="range" value="custom" />	
				Select Range:
			</label>
			*}
			<input class="downloadGraph" type="submit" value="Download" />
			|
			<input id="customRangeButton" type="submit" value="Custom Range" />
		</div>
		<div id="customRanges" class="hidden">
				<label for="customFromDate">From:</label>
				<input type="text" name="customFromDate" id="customFromDate" title="From Date" value="Now" />            
				<label for="customUntilDate">Until:</label>
				<input type="text" name="customUntilDate" id="customUntilDate" title="Until Date" value="Now" />    
				<input id="createCustomGraph" type="submit" value="Create" />
				&nbsp;
				<input class="downloadGraph" type="submit" value="Download" />
				|
				<input id="standardRangesButton" type="submit" value="Standard Ranges" />
		</div>
	</form>
	<form id="downloadForm" method="post" target="_blank">
		<input type="hidden" name="from" value="" />
		<input type="hidden" name="tzOffset" value="" />
		<input type="hidden" name="until" value="" />
		<input type="hidden" name="spread" value="" />
		<input type="hidden" name="action" value="" />
		<input type="hidden" name="download" value="1" />
	</form>
	<div id="graph">
		<div class="no-data">No data available. ಠ_ಠ</div>
		<div class="screen"></div>
	</div>
	<div>
		All dates are represented as Eastern time
	</div>
</div>
<script type="text/template" id="legendTemplate">
	<% var newKey; %>
	<ul id="legend">
	<% for ( var key in colors ) { %>
		<%
			newKey = key.charAt(0).toUpperCase();
			newKey = newKey + key.substr(1);
			color = colors[key];
			if(filter[key]) {
				color = "transparent";
			}
		%>
		<li>
			<label><%=newKey%></label>
			<b class="legendLabel" data-color=<%=color%> data-series-name="<%=key%>" style="background-color: <%=color%>;"></b>
		</li>
	<% } %>
	</ul>
</script>
{include file='common/adminFooter.tpl'}
