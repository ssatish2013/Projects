<h2><%=PF.langs.birthdayWidgetTitle%></h2>
<% _.each( data.friends, function( chunk, i ) { %>
	<ul<% if ( ! i ) { %> class="first"<% } %>>
		<% _.each( chunk, function( friend ) { %>
			<li>
				<a target="_top" href="/_/gift?search=<%=friend.first_name%>%20<%=friend.last_name%>">
					<img data-src="/facebook/securePic/uid/<%=friend.uid%>" width="50" height="50" alt="<%=friend.first_name%> <%=friend.last_name%>" />
					<div>
						<cite><%=friend.first_name%> <%=friend.last_name%></cite>
						<span><%=friend.formatted_birthday%></span>
					</div>
				</a>
			</li>
		<% }); %>
	</ul>
<% }); %>
<% if ( data.settings.button ) { %><div class="buttons"><% } %>
<div id="moreBirthdays" <% if ( data.settings.button ) { %>class='button'<%}else{%>class='notButton'<%}%>><%=data.language.more%></div>
<% if ( data.settings.button ) { %></div><% } %>