{capture assign='stylesheets'}
<link rel="stylesheet" href="/js/libs/iphone-style-checkboxes/style.css" />
{/capture}
{include file='common/adminHeader.tpl'}
<ol class="tabs">
	<li class="active"><a href="#cardContainer">Manage Designs</a></li>
	<li><a href="#newDesign">Add New Design</a></li>
	<li><a href="#categories">Manage Categories</a></li>
</ol>
<div>
	<ul id="cardContainer" data-allpgroups='{$allProductGroups|json_encode nofilter}'>
	{foreach from=$designs item=design}
		{$design->getCategories()}
		{$design->getProductGroups()}
		<li class="cardWrap{if $design->status == 0} inactive{/if}" data-id="{$design->id}" data-catids='{$design->categories|json_encode nofilter}' data-groupids='{$design->productGroups|json_encode nofilter}'>
			<div class="card" style="background-image: url({$design->smallSrc})">
					<div class="delete"></div>
				</div>
				<ul class="cardDetails">
					<li>
						<label>Alt Text:</label>
						<div class="edit"></div>
						<input type="text" name="altText" disabled value="{$design->alt}" class="altText" maxlength="20" />
					</li>
					<li>
						{* design now has it own property page, manage category and productgroup there
						<label>Categories:</label>
						<div class="categoryWrap" data-ids='{$design->categories|json_encode nofilter}'>
							{foreach $designCategories as $key => $category}
								<select class="designFilter" data-id="{$category.id}">
									<option>{$category.name nofilter}</option>
									<option>----------</option>
									{foreach $category.children as $child}
										<option value="{$child.id}"{if in_array( $child.id, $design->categories)} selected{/if}>{$child.name nofilter}</option>
									{/foreach}
								</select>
							{/foreach}
						</div>
						*}
						<input class="btnEditDesign" type="button" value="Edit Details" />
					</li>
					<li>
						<label>Status:</label>
						<input type="checkbox" name="active" {if $design->status == 1}checked {/if} />
					</li>
				</ul>
			</li>
		{/foreach}
	</ul>

	<div id="newDesign" class="hidden">
		<form action="" method="post">
			<ul>
				<li>
					<label for="newCard">File:</label>
					<input type="file" name="newCard" id="newCard" />
				</li>
				<li>
					<label for="alt">Alt Text:</label>
					<input type="text" name="alt" id="alt" maxlength="20" />
				</li>
				<li>{* design now has it own property page, manage category and productgroup there
					<label>Categories:</label>
					<div class="newDesignCatWrap">
						{foreach $designCategories as $key => $category}
							<select name="category[]" class="designFilter" data-id="{$category.id}">
								<option>{$category.name nofilter}</option>
								<option>----------</option>
								{foreach $category.children as $child}
									<option value="{$child.id}">{$child.name nofilter}</option>
								{/foreach}
							</select>
						{/foreach}
					</div>
					*}
				</li>
				<li style="display:none">
					<label class="checkbox">
						<input type="checkbox" name="round" /> Round image corners
					</label>
				</li>
				<li style="display:none">
					<label class="checkbox">
						<input type="checkbox" name="border" /> Add 1px border (good for images with white backgrounds)
					</label>
				</li>
				<li class="submit">
                    <input type="hidden" name="action" value="upload" />
					<input type="submit" value="Upload!" />
				</li>
			</ul>
			<p><strong>Note: </strong>Card design image must be 785 pixels wide by 500 pixels high in JPG or PNG format.</p>
		</form>
	</div>
	<div id="categories" class="hidden" data-allcategories='{$designCategories|json_encode nofilter}'>
		<ul>
			{foreach $designCategories as $key => $category}
				<li data-id="{$category.id}" class="parent">
					<div class="name" contenteditable="true">{$category.name}</div>
					<div class="action sortHook"></div>
					<div class="action deleteCategory"></div>
					<div class="action newChild"></div>
					<ul>
						{if $category.children}
							{foreach $category.children as $child}
								<li data-id="{$child.id}" class="child">
									<div class="name" contenteditable="true">{$child.name}</div>
									<div class="action sortHook"></div>
									<div class="action deleteCategory"></div>
								</li>
							{/foreach}
						{/if}
					</ul>
				</li>
			{/foreach}
		</ul>
		<div class="actions">
			<input type="submit" id="newParent" value="Add Parent Category" />
		</div>
	</div>
</div>

<script id="deleteTooltip" type="text/html">
Are you absolutely sure you want to delete this cover design?
<div class="options">
	<span>No, cancel</span>
	<input type="submit" class="deleteSubmit" value="Yes, delete it" />
	<input type="hidden" value="<id>" />
</div>
</script>

<script id="deleteCategoryTooltip" type="text/html">
{include file='lang:coverConfirmDelete'}
<% if ( isParent ) { %>
parent
<% } else { %>
child
<% } %>
category?
<% if ( isParent ) { %>
{include file='lang:coverConfirmDeleteChildren'}
<% } %>
<form class="options deleteCategoryForm">
	<span>No, cancel</span>
	<input type="submit" value="Yes, delete it" />
	<input type="hidden" name="id" value="<%=id%>" />
	<input type="hidden" name="isParent" value="<%=isParent%>" />
</form>
</script>

<script id="newCardTemplate" type="text/html">
	<li class="cardWrap" data-id="<%=design.id%>">
		<div class="card" style="background-image: url(<%=design.smallSrc%>)">
			<div class="delete"></div>
		</div>
		<ul class="cardDetails">
			<li>
				<label>Alt Text:</label>
				<div class="edit"></div>
				<input type="text" name="altText" disabled value="<%=design.alt%>" maxlength="20" class="altText" />
			</li>
			<li>
				<!--<label>Categories</label>
				<div class="categoryWrap" data-ids='<%=(window.JSON.stringify( design.categories ))%>'>
				</div>-->
				<input class="btnEditDesign" type="button" value="Edit Details" />
			</li>
			<li>
				<label>Status:</label>
				<input type="checkbox" name="active" checked />
			</li>
		</ul>
	</li>
</script>

<script id="newCategoryTemplate" type="text/html">
	<h4>What should the name of this category be? <div class="close"></div></h4>
	<form id="newCategoryForm">
		<ul>
			<li>
				<label for="categoryName">Category Name:</label>
				<input type="text" name="categoryName" />
			</li>
			<li class="buttons">

				<input type="hidden" name="parentId" value="<%=parentId%>" />
				<div class="buttonLabel cancel">
					<div class="cancel">Cancel</div>
				</div>
				<label class="buttonLabel">
					<input type="submit" value="Add Category" />
				</label>
			</li>
		</ul>
	</form>
</script>

<script id="editDesignTemplate" type="text/html">
	<h4>Manage Categories and Product Groups <div class="close"></div></h4>
	<form id="editDesignDetailsForm">
	<input type="hidden" name="designId" value="<%=designId%>"/>
	<input type="hidden" name="action" value="saveDesignEdit"/>
		<strong> Categories</strong>
		<ul>
			<% _.each( $("#categories").data("allcategories"), function( cat ) { %>
			<%  var pchecked = (categories && $.inArray(cat.id, categories)>-1) ? 'checked="checked"' : '';%>
			<li>
				<input type = "checkbox" name="designCategories[]" value="<%=cat.id%>" <%=pchecked%>/> : <%=cat.name%>
					 <ul>
					 <% _.each(cat.children, function( child ) { %>
					 <% var checked = (categories && $.inArray(child.id, categories)>-1) ? 'checked="checked"' : ''; %>
					 	<li>&lfloor;<input type = "checkbox" name="designCategories[]" value="<%=child.id%>"  <%=checked%>/> : <%=child.name%></li>
					 <% }); %>
					 </ul>
			</li>
			<% }); %>

		</ul>
		<strong> Product Groups</strong>
		<ul>
			<% _.each($("#cardContainer").data("allpgroups"), function( g ) { %>
			<li><strong><%=g.currency%> : </strong>
				<ul>
				<li><input type = "radio" name="designPG_<%=g.currency%>" value="0" /> N/A </li>
				<% _.each( g.groups, function( group ) { %>
					 <% var checked = (groups && $.inArray(group.id+'', groups)>-1) ? 'checked="checked"' : ''; %>
					<li><input type = "radio" name="designPG_<%=g.currency%>" value="<%=group.id%>" <%=checked%>/> <%=group.title%></li>
				<% }); %>
				</ul>
			</li>
			<% }); %>
			<li class="buttons">

				<div class="buttonLabel cancel">
					<div class="cancel">Cancel</div>
				</div>
				<label class="buttonLabel">
					<input type="submit" value="Save" />
				</label>
			</li>
		</ul>
	</form>
</script>

<script id="categoryTemplate" type="text/html">
	<li data-id="<%=id%>" class="child">
		<div class="name" contenteditable="true"><%=name%></div>
		<div class="action sortHook"></div>
		<div class="action deleteCategory"></div>
	</li>
</script>

<script id="parentCategoryTemplate" type="text/html">
	<li data-id="<%=id%>" class="parent">
		<div class="name" contenteditable="true"><%=name%></div>
		<div class="action sortHook"></div>
		<div class="action deleteCategory"></div>
		<div class="action newChild"></div>
		<ul>
		</ul>
	</li>
</script>

<script id="selectsUpdate" type="text/html">
	<% _.each( structure, function( parent ) { %>
		<% if ( parent.children ) { %>
			<select name="category[]" class="designFilter" data-id="<%=parent.id%>">
				<option><%=parent.name%></option>
				<option>----------</option>
				<% _.each( parent.children, function( child ) { %>
					<option value="<%=child.id%>"><%=child.name%></option>
				<% }); %>
			</select>
		<% } %>
	<% }); %>
</script>

{capture assign='includedScripts'}
<script src="/js/admin/jquery-modal.js"></script>
<script src="/js/admin/iphone-style-checkboxes.js"></script>
{/capture}
{include file='common/adminFooter.tpl'}
