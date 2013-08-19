{if ! $isAjax}
			</section>
{*Common JS Libs*}
<script src="/js/jquery-1.6.4.min.js"></script>
<script src="/js/admin/jquery.ui.core.js"></script>
<script src="/js/admin/jquery.effects.core.js"></script>
<script src="/js/admin/jquery.effects.explode.js"></script>
<script src="/js/admin/jquery.ui.widget.js"></script>
<script src="/js/admin/jquery.ui.mouse.js"></script>
<script src="/js/admin/jquery.ui.position.js"></script>
<script src="/js/admin/jquery.ui.sortable.js"></script>
<script src="/js/admin/jquery.ui.autocomplete.js"></script>
<script src="/js/admin/jquery.ui.datepicker.js"></script>
<script src="/js/admin/jquery.ui.menu.js"></script>
<script src="/js/admin/chosen.jquery.js"></script>
<script src="/js/admin/jquery.mustard.js"></script>
<script src="/js/admin/jquery-modal.js"></script>
<script src="/js/admin/help.js"></script>
<script src="/js/jquery-pubsub-1.0.js"></script>
<script src="/js/modernizr-2.5.3.js"></script>
<script src="/js/admin/jquery.form-2.87.js"></script>
<script src="/js/underscore.js"></script>
<script src="/js/admin/ico.js"></script>
<script src="/js/admin/raphael-min.js"></script>
<script src="/js/admin/admin.js"></script>
<script src="/js/admin/tabs.js"></script>
<script src="/js/admin/template.js"></script>
<script src="/js/admin/preload.js"></script>
<script src="/js/admin/validate.js"></script>
<script src="/js/admin/inputFocus.js"></script>
<script src="/js/admin/charCounter.js"></script>
<script src="/js/admin/insetButtons.js"></script>
{*Page Specific Libs*}
{if isset($includedScripts)}
{$includedScripts nofilter}
{/if}
{*Page Specific JavaScript*}
<script src="/js/views/{$templateName}.js"></script>

<script>
	window.PF = $.extend( true, window.PF || {}, {
				langs: {
					help: "{$lang->help}"
				}
	});
	
	//Embedded JavaScript
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
</script>
</body>
</html>
{/if}
