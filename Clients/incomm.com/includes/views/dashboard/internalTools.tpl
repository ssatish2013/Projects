{capture assign=stylesheets}
	<style>
		#content.column div {
			padding: 20px;
			margin-bottom: 30px;
			border: 1px solid #ccc;
			width: 710px;
		}
		table {
			width: 100%;
		}
		td {
			padding: 2px 8px;
			vertical-align: middle;
		}
		tr:nth-child(2n) {
			background-color: #eee;
		}
		input[type=submit] {
			font-size: 13px;
			line-height: 18px;
			padding: 4px 12px;
		}
		form {
			float: left;
		}
		form.submitting {
			background: transparent url(//gc-fgs.s3.amazonaws.com/common/lang_ajax.gif) no-repeat scroll center;
		}
		form.submitting input {
			visibility: hidden;	
		}
	</style>
{/capture}
{include file='common/header.tpl' ribbonBar='status' labelText='Dashboard > Internal Tools'}
<div class="column" id="content">
	<div>
		<h1>Batch Scripts</h1>
		<table>
			<tr>
				<td></td>
				<td></td>
			</tr>
			{foreach from=$batchInfo item=batch}
				<tr>
					<td>{$batch.name}</td>
					<td>
						<form action="/dashboard/startBatch" method="POST">
							<input type="hidden" name="batchName" value="{$batch.name}"/>
							<input type="submit" name="kickoff" value="kickoff" />
						</form>
					</td>
				</tr>
			{/foreach}
		</table>

		<h1>Workers</h1>
		<h3>Base Workers</h3>
		<table>
			<tr>
				<td>Worker name</td>
				<td>Number of workers</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			{foreach from=$workerInfo item=worker}
				<tr>
					<td>{$worker.name}</td>
					<td>{$worker.count}</td>
					<td>
						<form action="/dashboard/restartWorker" method="POST">
							<input type="hidden" name="workerName" value="{$worker.name}"/>
							<input type="submit" name="restart" value="restart" />
						</form>
					</td>
					<td>
						<form action="/dashboard/startWorker" method="POST">
							<input type="hidden" name="workerName" value="{$worker.name}"/>
							<input type="submit" name="start" value="start" />
						</form>
					</td>
					<td>
						<form action="/dashboard/stopWorker" method="POST">
							<input type="hidden" name="workerName" value="{$worker.name}"/>
							<input type="submit" name="stop" value="Stop" />
						</form>
					</td>
				</tr>
			{/foreach}
		</table>

		<h3>Email Workers</h3>
		<table>
			<tr>
				<td>Worker name</td>
				<td>Number of workers</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			{foreach from=$emailWorkerInfo item=worker}
				<tr>
					<td>{$worker.name}</td>
					<td>{$worker.count}</td>
					<td>
						<form action="/dashboard/restartWorker" method="POST">
							<input type="hidden" name="workerName" value="{$worker.name}"/>
							<input type="submit" name="restart" value="restart" />
						</form>
					</td>
					<td>
						<form action="/dashboard/startWorker" method="POST">
							<input type="hidden" name="workerName" value="{$worker.name}"/>
							<input type="submit" name="start" value="start" />
						</form>
					</td>
					<td>
						<form action="/dashboard/stopWorker" method="POST">
							<input type="hidden" name="workerName" value="{$worker.name}"/>
							<input type="submit" name="stop" value="Stop" />
						</form>
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
</div>
{capture assign=inlineScripts}
	$("<img/>")[0].src = "//gc-fgs.s3.amazonaws.com/common/lang_ajax.gif";
	$("table").delegate("form", "submit", function() {
		var $form = $(this);
		$.ajax({
			type: "post",
			url: $form.attr("action"),
			beforeSend: function() {
				$form.addClass("submitting");
			},
			dataType: "json",
			data: $form.serialize(),
			success: function(json) {
				$form.removeClass("submitting");
				if ("count" in json) {
					$form.closest("tr").children().eq(1).text(json.count)
				}
			}
		});
		return false;
	});
{/capture}
{include file='common/footer.tpl'}
