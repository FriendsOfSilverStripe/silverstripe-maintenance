<a href="https://addons.silverstripe.org/add-ons/$Name.ATT"
   title="<%t BringYourOwnIdeas\\Maintenance\\Reports\\SiteSummary.AddonsLinkTitle "View {package} on addons.silverstripe.org" package=$Title.ATT %>"
   class="package-summary__anchor" target="_blank" rel="noopener">
    <strong class="package-summary__title">$Title.XML</strong>
    
    <% loop $Badges %>
        <span class="package-summary__badge badge<% if $Type %> badge-$Type.ATT<% end_if %>">$Title.XML</span>
    <% end_loop %>

    <% if $Description %>
        <span class="package-summary__description">$Description.XML</span>
    <% end_if %>
</a>
