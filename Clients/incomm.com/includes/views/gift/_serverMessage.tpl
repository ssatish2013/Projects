{if isset(Env::main()->validationErrors)}
	<div class="errorMessage">
		<span class="noty_text">
			{foreach from=Env::main()->validationErrors key=k item=error}
				{$error nofilter}<br />
			{/foreach}
		</span>
	</div>
{/if}
{if isset($smarty.session.flashMessage)}
	<div class="flashMessage">
		<span class="noty_text">
			{$smarty.session.flashMessage nofilter}
		</span>
	</div>
{/if}