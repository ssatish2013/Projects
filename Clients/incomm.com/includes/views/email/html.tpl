{include file='email/templates/'|cat:$template|cat:'.tpl'}
{include file='email/common/header.tpl'}
	{$smarty.capture.html nofilter}
{include file='email/common/footer.tpl'}
