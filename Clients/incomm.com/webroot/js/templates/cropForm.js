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
						<td id="previewCell">
							<h5><%=PF.langs.preview%></h5>
							<div id="cropPreviewWrap">
								<div class="corners"></div>	
								<img class="previewImage" id="previewImage">
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
