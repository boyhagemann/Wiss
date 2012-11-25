

$(document).ready(function(e) {
	
    $('.sortable').sortable({
        axis: 'y',
        connectWith: '.sortable',
        delay: 150,
        opacity: 0.5
    });
    
    $('.draggable').draggable({
        connectToSortable: '.sortable',
        helper: 'clone',
        opacity: 0.5,
        revert: true,
        revertDuration: 200
    });
    
    
    
})