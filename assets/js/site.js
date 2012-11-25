

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
    
    console.log('tss');
    $('.blocks-available .draggable').on('dragstop', function(event, ui) {

        var blockId = ui.helper.data('block-id');
        
        var blocks = $('.blocks-used li');
        var data = [];
        
        blocks.each(function(e) {
            data.push({
                contentId: $(this).data('content-id'),
                position: '1'
            });
        })
        
        
        $.ajax({
            url: $('.blocks-used').data('sort-url'),
            type:'get',
            dataType:'json',
            data: {test: data},
            success: function(data){
                console.log(data);
            }
        })
        
    });
    
})