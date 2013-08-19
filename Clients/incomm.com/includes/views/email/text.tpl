{include file='email/templates/'|cat:$template|cat:'.tpl'}
{$smarty.capture.text nofilter}
