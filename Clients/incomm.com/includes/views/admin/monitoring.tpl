{include file='common/adminHeader.tpl'}
<h1>Monitoring</h1>
<table>
	<tr class="category">
		<th>Name</th>
		<th></th>
		<th>enabled</th>
		<th>Minimum Percent</th>
		<th>Maximum Percent</th>
		<th>Minimum Hard Limit</th>
		<th>Maximum Hard Limit</th>
		<th>Compare Start Time</th>
		<th>Compare End Time</th>
		<th>Current Start Time</th>
		<th>Current End Time</th>
	</tr>
	{foreach $events as $event}
		<tr class="values">
			<td data-id="{$event->id}" class="key">
				{$event->name}
			</td>
			
			<td><div class="save"></div></td>
			<td class="enabled value">{$event->enabled}</td>
			<td class="minimumPercent value">{$event->minimumPercent}</td>
			<td class="maximumPercent value">{$event->maximumPercent}</td>
			<td class="minimumHardLimit value">{$event->minimumHardLimit}</td>
			<td class="maximumHardLimit value">{$event->maximumHardLimit}</td>
			<td class="compareStartTime value">{$event->compareStartTime}</td>
			<td class="compareEndTime value">{$event->compareEndTime}</td>
			<td class="currentStartTime value">{$event->currentStartTime}</td>
			<td class="currentEndTime value">{$event->currentEndTime}</td>
		</tr>
{/foreach}
</table>

<div class="buttons">
	<input type="submit" value="New Monitor" id="newMonitor" />
</div>

<form id="newMonitoringForm" class="hidden validate" method="POST">
	<h2>Create a new event Monitor</h2>
	<ul>
		
		<li>
			<label for="eventType">Event Type</label>
			<select name="eventTypeId" id="eventType">
				<option value=""></option>
				{foreach $eventTypeDropDown as $eventType}
					<option value="{$eventType->id}">{$eventType->name}</option>
				{/foreach}
			</select>
		</li>
		
		<li>
			<label for="eventType">Event</label>
			<select name="eventId" id="event">
				<option value=""></option>
				{foreach $eventDropDown as $event}
					<option value="{$event->id}">{$event->eventType->name}+{$event->name}</option>
				{/foreach}
			</select>
		</li>
		
		<li>
			<label>Minimum Percent</label>
			<input name="minimumPercent" type="text" data-validate=["required"] />
		</li>

		<li>
			<label>Maximum Percent</label>
			<input name="maximumPercent" type="text" data-validate=["required"] />
		</li>

		<li>
			<label>Minimum Hard Limit</label>
			<input name="minimumHardLimit" type="text" data-validate=["required"] />
		</li>

		<li>
			<label>Maximum Hard Limit</label>
			<input name="maximumHardLimit" type="text" data-validate=["required"] />
		</li>
		
		<li>
			<label>Compare Start Time (strtotime format)</label>
			<input name="compareStartTime" type="text" value="-25 hours" data-validate=["required"] />
		</li>
		
		<li>
			<label>Compare End Time (strtotime format)</label>
			<input name="compareEndTime" type="text" value="-1 hours" data-validate=["required"] />
		</li>
		
		<li>
			<label>Current Start Time (strtotime format)</label>
			<input name="currentStartTime" type="text" value="-1 hours" data-validate=["required"] />
		</li>
		
		<li>
			<label>Current End Time (strtotime format)</label>
			<input name="currentEndTime" type="text" value="now" data-validate=["required"] />
		</li>
		
		<li class="buttons">
			<span class="clickable cancel">Cancel</span>
			<input type="hidden" name="enabled" value="1" />
			<input type="submit" value="Submit" />
		</li>
	</ul>
</form>
{include file='common/adminFooter.tpl'}
