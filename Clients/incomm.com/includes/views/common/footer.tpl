		<div class="column" id="footer">
			<ul>
				<li>
					<a id="openfaq" href="#" data-title="{$lang->footerFAQExpanded}">{$lang->footerFAQ}</a>
				</li>
				<li>
					{if (!is_null($lang->contactUsLink) && ($lang->contactUsLink != ''))}
						<a id="opencontactform1" href="{$lang->contactUsLink}" target="_blank" rel="external">{$lang->footerContactUs}</a>
					{else}
						<a id="opencontactform" href="#" data-title="{$lang->footerContactUs}" data-okbtntext="{$lang->send}" rel="external">{$lang->footerContactUs}</a>
					{/if}
				</li>
				<li>
					<a id="openterm" href="#" data-title="{$lang->footerTermsOfUse}">{$lang->footerTermsOfUse}</a>
				</li>
				<li>
					<a id="openprivacy" href="#" data-title="{$lang->footerPrivacyPolicy}">{$lang->footerPrivacyPolicy}</a>
				</li>
			</ul>
		</div>

		{if isset($scriptTags)}
			{$scriptTags nofilter}
		{/if}
		{* Definition for php array that will later be encoded to json object window.PF.page *}
		{if isset($jsLang)} {$_PAGE['lang'] = $jsLang} {/if}
		{if isset($message)} {$_PAGE['message'] = $message} {/if}
		{if isset($gift)} {$_PAGE['gift'] = $gift} {/if}
		{if isset($fbSettings)} {$_PAGE['FB'] = $fbSettings} {/if}
		{if isset($geoWhitelist)} {$_PAGE['geoWhitelist'] = $geoWhitelist} {/if}
		{if isset($geoBlacklist)} {$_PAGE['geoBlacklist'] = $geoBlacklist} {/if}
		{if $settings->ui->hasPromoPop} {$_PAGE['promo'] = $settings->ui->hasPromoPop} {/if}
		{if $settings->ui->hasCufon} {$_PAGE['cufon'] = $lang->cufonFont} {/if}
		{if $settings->ui->defaultToPhysicalDelivery} {$_PAGE['defaultToPhysicalDelivery'] = 1} {/if}
		<script>
			//page level settings
			window.PF = $.extend( true, window.PF || {}, {
				page: {$_PAGE|json_encode nofilter},
				langs: {
					help: "{$lang->help}"
				}
			});

			// Embedded JavaScript
			{if isset($inlineScripts)}
				{$inlineScripts nofilter}
			{/if}

			//This should be the last javascript piece.
			window.PF = $.extend( true, window.PF || {}, {
				init : function() {
					if ( ! window.console ) { window.console = { log : function(){} }; }
					$.each( window.PF, function( key, value ) {
						if ( key !== 'init' ) {
							if ( $.isFunction( value ) ) {
								value.call( window.PF );
							} else {
								(value.init || $.noop).call( window.PF );
							}
							$.each( PF[ key ], function( method ) {
								$.subscribe('/' + key + '/' + method, $.proxy( PF[key][method], PF ));
							});
						}
					});
				}
			});

			// Start it!
			$(window.PF.init);

			//Page specific JavaScript that runs after main initialization
			{if isset($lastMinuteScripts)}
				{$lastMinuteScripts nofilter}
			{/if}

			// Google analytics
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-32112118-1']);
			_gaq.push(['_trackPageview']);
			_gaq.push(['_setAllowLinker', true]);
			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = 'https://ssl.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script>
		</div>
	</body>
</html>
