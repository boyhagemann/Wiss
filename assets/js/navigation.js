

$(function() {
	      
    $('#tree').dynatree({
        minExpandLevel: 2,
        clickFolderMode: 0,
        onDblClick: function(node, event) {
            console.log(node)
            console.log(node.data.href);
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
//                console.log(hitMode)
              
                var copynode;
                if(sourceNode) {
                    
                    sourceNode.move(node, hitMode);
                    
                    if(hitMode == "over"){
                        node.expand(true);
                    }else if(hitMode == "before"){
                        
                    }else if(hitMode == "after"){
                        
                    }
                    
                    
                    return true;
                }
                else {
                    
                    var url = ui.helper.attr('href');
                    url += '/' + node.data.key;
                    url += '/' + hitMode;
                    
                    copynode = {
                        title: "New page (click to save)",
                        href: url
                     };
                     
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