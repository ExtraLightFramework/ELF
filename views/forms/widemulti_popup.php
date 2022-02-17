<select class="elf-advs-input elf-clever-selector" name="<% field_name %>" id="widemulti-popup-<% field_name %>"
		data-step="1" data-getter="<% getter %>" data-selected="<% selected %>" onchange="_get_rec_by_field_name()"
		data-link-field="<% link_field %>" data-search-fields="<% search_fields %>"
		data-display-field="<% display_field %>" data-params="<?=base64_decode(Elf::get_data('params'))?>;getter_variable_name=<% getter_variable_name %>;slave_field=<% slave_field %>;caption=<% caption %>;is_related_form=<% slave_id %>">
	<option value=""><% lang:forms:set.value %></option>
</select>
