

$(function() {
	
    $('.sortable').sortable({
        axis: 'y',
        connectWith: '.sortable',
        delay: 150,
        opacity: 0.5,
        update: function(event, ui) {
            sortBlocks(event, ui);
        }
    });
    
    $('.draggable').draggable({
        connectToSortable: '.sortable',
        helper: 'clone',
        opacity: 0.5,
        revert: true,
        revertDuration: 200
    });
        
    $('.blocks-available .draggable').on('dragstop', function(event, ui) {
        sortBlocks(event, ui);
    });
    
    sortBlocks = function(event, ui)
    {              
        var blockId;
        if(ui.helper) {
            blockId = ui.helper.data('block-id');
        }
        else {
            blockId = ui.item.data('block-id');
        }
        
        var blocks = $('.blocks-used li');
        var data = [];
        
        blocks.each(function(i) {
            data.push({
                blockId: blockId,
                zoneId: $(this).parent('ul').data('zone-id'),
                contentId: $(this).data('content-id'),
                position: i
            });
        })
        
        $.ajax({
            url: $('.blocks-used').data('sort-url'),
            type:'get',
            dataType:'json',
            data: {items: data},
            success: function(data){
//                console.log(data);
            }
        })
    }
        
})