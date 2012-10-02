

$(document).ready(function(e) {
	
	$('.dropdown-toggle').dropdown();
	$('.alert').alert();

	/*
	 * 
	 * Modal window for configuration form element
	 * 
	 */
    $('.element-config-trigger').click(function() {
		var trigger = $(this);
        var formClass = $(this).parent().find('select.form-class').val();
        if(!formClass) {
            return false;
        }
        
        var target = $(this).data('target');
        var url = $(this).data('remote') + '?form-class=' + formClass;
        
        // load the url and show modal on success
        $(target + " .modal-body").load(url, function() { 
             $(target).modal("show"); 
        });
		
		// Save the config		
		$('.btn-save-config').click(function(e) {
			var config = $(target).find('form').serialize();
			var element = trigger.parents('li').find('input.element-config');
			element.val(config);
		});
    });
	
	
	

  $('.treeview').jstree({
    core : { 
		animation: 250,
		load_open: true
	},
    plugins : [ "themes", "html_data", "dnd" ]
  });
	
})