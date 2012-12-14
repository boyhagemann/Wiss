

$(document).ready(function(e) {
	
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
    
    
    $('.tree').on('sortstart', function(event, ui) {
       var children = getTreeChildren(ui.item);
       children.css('opacity', 0.5);
    });
    
    $('.tree').on('sortstop', function(event, ui) {
       var children = getTreeChildren(ui.item);
       console.log(children)
       children.css('opacity', 1);
    });
        
    getTreeChildren = function(element)
    {
       var lft = $(element).data('lft');
       var rgt = $(element).data('rgt');
       var children = $('.tree li').filter(function(index) {
           return ( $(this).data('lft') > lft && $(this).data('rgt') < rgt);
       });
       
       return children;
    }
    
    
    $('#tree').dynatree({
        minExpandLevel: 2,
        clickFolderMode: 0,
        onDblClick: function(node, event) {
            url = $(node.li).find('a').attr('href');
            if(!url || url == '#') {
                return false;
            }
            console.log(url);
            window.location =  url;
        },
        dnd: {
            autoExpandMS: 1000,
            preventVoidMoves: true,
            onDragStart: function(node) {
                return true;
            },      
            onDragEnter: function(node, sourceNode) {
                return true;
            },
            onDragOver: function(node, sourceNode, hitMode) {
                
                // Prevent dropping a parent below it's own child
                if(node.isDescendantOf(sourceNode)){
                  return false;
                }
                
                // Prohibit creating childs in non-folders (only sorting allowed)
                if( !node.data.isFolder && hitMode === "over" ){
                  return "after";
                }
            },
            onDrop: function(node, sourceNode, hitMode, ui, draggable) {
              
                var copynode;
                if(sourceNode) {
                    sourceNode.move(node, hitMode);
                    return true;
                }
                else{
                    copynode = {
                        title: "This node was dropped here (" + ui.helper + ")."
                        };
                }                
                
                if(hitMode == "over"){
                    // Append as child node
                    node.addChild(copynode);
                    // expand the drop target
                    node.expand(true);
                }else if(hitMode == "before"){
                    // Add before this, i.e. as child of current parent
                    node.parent.addChild(copynode, node);
                }else if(hitMode == "after"){
                    // Add after this, i.e. as child of current parent
                    node.parent.addChild(copynode, node.getNextSibling());
                }
            },
         
            onDragLeave: function(node, sourceNode) {
            }

        }

    });

    $("#new-page").draggable({
        revert: true,
        connectToDynatree: true,
        cursorAt: { top: -5, left:-5 },
        helper: "clone"
    });

    
})