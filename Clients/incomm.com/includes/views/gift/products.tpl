{capture assign='stylesheets'}
	<link rel="stylesheet" href="/css/home.css" type="text/css" />
	<link rel="stylesheet" href="/css/jquery.Jcrop.css" type="text/css" />
	<link rel="stylesheet" href="/css/jquery.dd.css" type="text/css" />
{/capture}
{include file='common/header.tpl'
	showPreview=true showCartCount=true
	ribbonBar='progress' progressAt=1}
<div id="next-error" class="mdialog" style="display:none">
   <p>{$lang->selectCardDesign}</p>
</div>
<form method="GET" action="">
			<div class="column" id="products">
				<div style="overflow: visible; position: relative; height: 40px;">
					<div class="select narrowouter leftfloat{if count($_PAGE['products'])<=1} hidden{/if}">
						<span id="curdisplay" class="{$_PAGE['defaultCurrency']|lower}"></span>
						<span></span>
						<select id="curselector" class="narrowinner currency">
						</select>
					</div>
					<div class="select narrowouter{if count($_PAGE['designCategories'])<1} hidden{/if}">
						<span></span>
						<select class="narrowinner" id="catselector">
							<option>{$lang->selectCategory}</option>
						</select>
					</div>
					<a id="btnBrowseAll" class="button"{if count($_PAGE['designCategories'])<1} style="display:none"{/if}>
					<span>{$lang->browseAll}</span>
					</a>			
				</div>
				<div>
					<img id="loading-designs" src="/images/loading.gif"/>
					<ul class="designlist">
					</ul>
				</div>
				<div>
					<span class="legend">
						<span class="custom" title="{$lang->giftProductsIconTooltipCustom}">
							<img src="https://gca-common.s3.amazonaws.com/assets/card.custom.png" />
							<span class="text">{$lang->customImage}</span>
						</span>
						<span class="postal" title="{$lang->giftProductsIconTooltipPostal}">
							<img src="https://gca-common.s3.amazonaws.com/assets/card.mail.png" />
							<span class="text">{$lang->postal}</span>
						</span>
					</span>
					<span class="pagingControls">
						<span id="btnPrev">
							<img class="pointer" src="/images/prevpage.png"/>
						</span>
						<strong> <span id="currentpage"></span> </strong>
						<span id="totalpage"></span>
						<span id= "btnNext"> 
							<img class="pointer" src="/images/nextpage.png"/>
						</span>
					</span>
				</div>
			</div>
			<div class="column" id="navigation">
				<div>
					<a href="/"><span>{$lang->cropCancel}</span></a>
					<input type="submit" id="next" value="{$lang->nextStep}" />
				</div>
			</div>
			<input type="hidden" name="giftingmode" id="giftingmode" value="{$giftingMode}" />
			<input type="hidden" name="messageGuid" id="messageGuid" value="{if isset($messageGuid)}{$messageGuid}{/if}" />
			<input type="hidden" name="did" id="did" value="{if isset($did)}{$did}{/if}" />
			{if isset($smarty.get.search)}
				<input type="hidden" name="search" id="search" value="{$smarty.get.search}" />
			{/if}
		</form>
<div class="uploadImageForm" style="display:none" data-okbtntext="OK" data-title="{include file='lang:coverUploadYourOwn'}">
	<div class="loader" style="display: none;text-align:center;padding-top:50px;">
		<h2>{include file="lang:uploading"}...</h2>
	</div>
	<div class="customUpload" style="padding:60px 0 0 20px; text-align:center">
		<form class="uploadForm"  method="POST">
		<span for="customImage" style="text-decoration: none;">{include file="lang:chooseImage"}:</span>
		<input type="file" class="customImage" name="newCard" style="display: inline;">
		</form>
	</div>	
</div>

<div class="cropImageForm" style="display:none" data-okbtntext="{include file="lang:crop"}" data-title="{include file="lang:cropYourImage"}">
	{include file="gift/_cropForm.tpl"}
</div>
{include file="common/footer.tpl"}
