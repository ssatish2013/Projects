{include file='email/templates/'|cat:$template|cat:'.tpl'}
{$smarty.capture.subject nofilter}
