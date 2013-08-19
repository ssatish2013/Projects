<% if(designs.length == 0) { %>
<div class="card_section current empty">
<h1><%=noFilterText%></h1>
</div>
<% } else { %>
	<% _.each( designs, function( design, index) { %>
	
		<% if ( index % PF.page.cardsPerSection == 0 ) { %>
			<div class="card_section<% if ( index == 0 ) { %> current<% } %>">
		<% } %>
			<div class="card" did="<%=design.id%>" data-thirdparty="<%=design.thirdparty%>" data-product-id="<%=design.productId%>"
				src="<%=design.mediumSrc%>"><img class="img" src="<%=design.smallSrc%>" width="150" height="95"></div>
		<% if ( index % PF.page.cardsPerSection ==  PF.page.cardsPerSection - 1 || index == designs.length - 1 ) { %>
			</div>
		<% } %>
	<% }); %>
<% } %>