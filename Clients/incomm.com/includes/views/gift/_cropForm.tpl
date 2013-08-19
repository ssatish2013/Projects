<form class="cropForm">
	<ul>
		<li class="cropImageLi">
			<table>
				<tbody>
					<tr>
						<td class="cropTd">
							<h5>{include file="lang:cropYourImageDesc"}</h5>
							<img class="cropImage">
						</td>
						{if (((!isset($_PAGE['customScenes']) || count($_PAGE['customScenes'])==0) && (intval($_PAGE['forceLogoUpload']) == 0)) || ((intval($_PAGE['forceLogoUpload']) == 1) && (count($_PAGE['customScenes'])<=1)))}
						<td id="previewCell">
							<h5>{include file="lang:preview"}</h5>
							<div class="cropPreviewWrap">
								<div class="corners"></div>	
								<img class="previewImage">
							</div>
						</td>
						{/if}
					</tr>
					{if ((isset($_PAGE['customScenes']) && (count($_PAGE['customScenes'])>0) && (intval($_PAGE['forceLogoUpload']) == 0)) || ((intval($_PAGE['forceLogoUpload']) == 1) && (count($_PAGE['customScenes'])>1)))}
					<tr>
						<td>
						
							<div class="scene_outer_wrap arrowImages">
								<div class="arrow left no-pointer"><span>&larr;</span></div>
								<div class="scene_inner_wrap" data-mustard-position="top">
									<div class="cards round">
										<div class="cards_wrap">
											<div class="card_section current">
												{foreach $_PAGE['customScenes'] as $key=>$scene}
													<div class="card {if ( ($key==0) && (intval($_PAGE['forceLogoUpload']) == 1) )}selected{/if}" did="{$scene->id}" src="{$scene->mediumSrc}">
														<div class="glow"></div>
														<div class="cropPreviewWrap">
															<img class="previewImage" src="{$scene->smallSrc}" width="150" height="95">
															<img class="img" src="{$scene->smallSrc}" width="150" height="95">
														</div>
													</div>
												{/foreach}
											</div>
										</div>
									</div>
								</div>
								<div class="arrow right"><span>&rarr;</span></div>
							</div>

						</td>
					</tr>
					{/if}
				</tbody>
			</table>
		</li>
	</ul>
</form>
