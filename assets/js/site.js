

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
    
    $('.blocks-available .draggable').on('dragstop', function(event, ui) {
        var blockId = ui.helper.data('block-id');
        console.log(blockId);
        console.log(ui.helper);
        console.log(event);
        console.log(ui);
    });
    
})