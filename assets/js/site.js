

$(document).ready(function(e) {
	
	$('.dropdown-toggle').dropdown();
	$('.alert').alert();
	$('.datepicker').datepicker();

  $('.treeview').jstree({
    core : { 
		animation: 250,
		load_open: true
	},
    plugins : [ "themes", "html_data", "dnd" ]
  });
	
})