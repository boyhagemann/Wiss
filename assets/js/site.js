

$(document).ready(function(e) {
	
	$('.dropdown-toggle').dropdown();
	$('.alert').alert();
//	$('.datepicker').datepicker();


    $('.element-config-trigger').click(function() {
        var url = $(this).data('remote');
        var formClass = $(this).parent().find('select.form-class').val();
        if(!formClass) {
            return false;
        }
        $('#myModal').modal({
            remote: url + '?form-class=' + formClass
        });
    })

  $('.treeview').jstree({
    core : { 
		animation: 250,
		load_open: true
	},
    plugins : [ "themes", "html_data", "dnd" ]
  });
	
})