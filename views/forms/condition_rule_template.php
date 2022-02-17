let _condition_ctrl;
let _condition_ctrl_type = '';
if ($("#elf-form-<% master_id %> input[name=<% field %>]").attr('name')) {
	_condition_ctrl = $("#elf-form-<% master_id %> input[name=<% field %>]").first();
	_condition_ctrl_type = _condition_ctrl.attr('type');
}
else if ($("#elf-form-<% master_id %> textarea[name=<% field %>]").attr('name')) {
	_condition_ctrl = $("#elf-form-<% master_id %> textarea[name=<% field %>]").first();
	_condition_ctrl_type = 'textarea';
}
else if ($("#elf-form-<% master_id %> select[name=<% field %>]").attr('name')) {
	_condition_ctrl = $("#elf-form-<% master_id %> select[name=<% field %>]").first();
	_condition_ctrl_type = 'select';
}
switch (_condition_ctrl_type) {
	case 'select':
		_condition_ctrl.on("change",function() {
			if($(this).val()<% condition_oper %>"<% value %>")
				_sw_related_form(<% slave_id %>,"show");
			else
				_sw_related_form(<% slave_id %>,"hide");
		});
		break;
	case 'checkbox':
		_condition_ctrl.on("change",function() {
			if ($(this).prop('checked')) {
				if (''=='<% value %>')
					_sw_related_form(<% slave_id %>,"hide");
				else
					_sw_related_form(<% slave_id %>,"show");
			}
			else {
				if (''=='<% value %>')
					_sw_related_form(<% slave_id %>,"show");
				else
					_sw_related_form(<% slave_id %>,"hide");
			}
		});
		break;
	default:
		_condition_ctrl.on("input", function() {
			if ($(this).val()<% condition_oper %>"<% value %>")
				_sw_related_form(<% slave_id %>,"show"); 
			else
				_sw_related_form(<% slave_id %>,"hide");
		});
		break;
}
_sw_related_form(<% slave_id %>,"hide");
if ('<% value %>'<% condition_oper %>_condition_ctrl.val())
	_sw_related_form(<% slave_id %>,"show");
