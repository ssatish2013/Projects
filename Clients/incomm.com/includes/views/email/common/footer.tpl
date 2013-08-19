{include file="lang:emailFooter" assign='emailFooter'}
{if $emailFooter}
	{$emailFooter nofilter}
{else}
			</td>
		</tr>

		<tr class="tfooter">
			<td style="border-top: 1px solid #e3e3e3;height: 20px;padding: 5px 0 10px 20px;background: #191919;">
				<span style="clear: right;display: block;float: left;margin-right: 20px;padding-right: 20px;text-transform: uppercase;">
					<a style="cursor: pointer;text-decoration: none;color: #EAEAEA;font-size: 12px;" href="{url controller='help' method='_faq' full='true'}">{$lang->footerFAQ}</a>
				</span>
				<span style="clear: right;display: block;float: left;margin-right: 20px;padding-right: 20px;text-transform: uppercase;">
					{if (!is_null($lang->contactUsLink) && ($lang->contactUsLink != ''))}
						<a rel="external" style="cursor: pointer;text-decoration: none;color: #EAEAEA;font-size: 12px;" href="{$lang->contactUsLink}">{$lang->footerContactUs}</a>
					{else}
						<a style="cursor: pointer;text-decoration: none;color: #EAEAEA;font-size: 12px;" href="{url controller='help' method='_contact' full='true'}">{$lang->footerContactUs}</a>
					{/if}
				</span>
				<span style="clear: right;display: block;float: left;margin-right: 20px;padding-right: 20px;text-transform: uppercase;">
					<a style="cursor: pointer;text-decoration: none;color: #EAEAEA;font-size: 12px;" href="{url controller='help' method='_terms' full='true'}">{$lang->footerTermsOfUse}</a>
				</span>
				<span style="clear: right;display: block;float: left;margin-right: 20px;padding-right: 20px;text-transform: uppercase;">
					<a style="cursor: pointer;text-decoration: none;color: #EAEAEA;font-size: 12px;" href="{url controller='help' method='_privacy' full='true'}">{$lang->footerPrivacyPolicy}</a>
				</span>
			</td>
		</tr>
		
		<tr style="border:0;border-collapse:collapse;font-family:Arial,Helvetica,'Times New Roman','Courier New';">
			<td style="font-size:x-small;color:#d3d3d3;text-align:center">
				{include file='lang:emailDisclaimerCAN-SPAM'}
			</td>
		</tr>
	</table>
	</body>
</html>			
{/if}
