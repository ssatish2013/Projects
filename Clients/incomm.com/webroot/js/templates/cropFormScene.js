<h4><%=PF.langs.cropYourImage%><div class="close"></div></h4>
<form id="cropForm">
	<ul>
		<li id="cropImageLi">
			<table>
				<tbody>
					<tr>
						<td id="cropTd">
							<h5><%=PF.langs.cropYourImageDesc%></h5>
							<img id="cropImage">
						</td>
						<!--<td id="previewCell">
							<h5><%=PF.langs.preview%></h5>
							<div id="cropPreviewWrap">
								<div class="corners"></div>	
								<img id="previewImage">
							</div>
						</td>-->
					</tr>
					<tr>
						<td>
						
							<div id="scene_outer_wrap" class="arrowImages">
								<div class="arrow left no-pointer"><span>&larr;</span></div>
								<div id="scene_inner_wrap" data-mustard-position="top">
									<div class="cards round">
										<div class="cards_wrap">
											<div class="card_section current">
												<% _.each( customScenes, function( v, k ) { %>
													<div class="card" did="<%=v.id%>" src="<%=v.mediumSrc%>">
														<div class="glow"></div>
														<div class="cropPreviewWrap">
															<img class="previewImage" src="<%=v.smallSrc%>" width="150" height="95">
															<img class="img" src="<%=v.smallSrc%>" width="150" height="95">
														</div>
													</div>
												<% }); %>

												<!--{foreach $designChunks as $index => $chunk}
													<div class="card_section{if $index == 0} current{/if}">
														{foreach $chunk as $design}
															<div class="card" did="{$design->id}" src="{$design->mediumSrc}">
																<div class="glow"></div>
																<img class="img" src="{$design->smallSrc}" width="150" height="95">
															</div>
														{/foreach}
													</div>
												{/foreach}-->
											</div>
										</div>
									</div>
								</div>
								<div class="arrow right"><span>&rarr;</span></div>
							</div>

						</td>
					</tr>
				</tbody>
			</table>
			

			
		</li>
		<li class="buttons">
			<label class="buttonLabel cancel">
				<span class="cancel"><%=PF.langs.cropCancel%></span>
			</label>
			<label class="buttonLabel">
				<input type="submit" id="cropSubmit" value="<%=PF.langs.crop%>" />
				<input type="hidden" id="imageUrl" value="<%=url%>" />
			</label>
		</li>
	</ul>
</form>
