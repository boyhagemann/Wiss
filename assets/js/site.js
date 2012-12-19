

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
        
        // Collect the relevant data from the blocks and zones.
        // Put it in an object that is to be send thru ajax
        blocks.each(function(i) {
            data.push({
                blockId: blockId,
                zoneId: $(this).parent('ul').data('zone-id'),
                contentId: $(this).data('content-id'),
                position: i
            });
        })
        
        // Save the current block positions 
        $.ajax({
            url: $('.blocks-used').data('sort-url'),
            type:'get',
            dataType:'json',
            data: {items: data},
            success: function(response){
                        
                data = response[0];
                
                if(data.html) {
                    var newBlock = $('.blocks-used').find('li[data-block-id]');
                    newBlock.append(data.html);
                    newBlock.removeAttr('data-block-id').attr('data-content-id', data.content.id)
                }
            }
        })
    }
        
})