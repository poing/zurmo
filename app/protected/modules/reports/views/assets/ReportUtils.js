$(window).ready(function(){
    $(".attribute-to-place").live("mousemove",function(){
        $(this).draggable({
            helper: "clone",
            revert: "invalid",
            snap: ".droppable-attribute-container",
            snapMode: "inner",
            cursor: "pointer",
            start: function(event,ui){
                $(ui.helper).attr("id", $(this).attr("id"));
                //$(ui.helper).css("height", "20px");
                //$(ui.helper).css("width", "260px");
            },
            stop: function(event, ui){
                document.body.style.cursor = "auto";
            }
        });
    });
    var isDragging = false;
    $( ".droppable-attributes-container").droppable({
        accept: ".attribute-to-place",
        hoverClass: "ui-state-active",
        cursor: "pointer",
        drop: function( event, ui ) {
            //todo: hide drop overlay
        },
        activate: function(event,ui){
            isDragging = true;
            $('.dynamic-droppable-area').addClass('activate-drop-zone');
        },
        deactivate: function(event,ui){
            isDragging = false;
            $('.dynamic-droppable-area').removeClass('activate-drop-zone');
        }
    });
    $(".hasTree").hover(
        function(){
            $('.dynamic-droppable-area').addClass('activate-drop-zone');
        },
        function(){
            if (isDragging == false){
                $('.dynamic-droppable-area').removeClass('activate-drop-zone');
            }
        }
    );
});

function rebuildReportFiltersAttributeRowNumbersAndStructureInput(divId){
    rowCount = 1;
    structure = '';
    $('#' + divId).find('.dynamic-attribute-row-number-label').each(function(){
        $(this).html(rowCount + '.');
        if(structure != ''){
            structure += ' AND ';
        }
        structure += rowCount;
        $(this).parent().find('.structure-position').val(rowCount);
        rowCount ++;
    });
    $('#' + divId).find('.filters-structure-input').val(structure);
    if(rowCount == 1){
        //hmm. not sure exactly how this will be named.
        $('#show-filters-structure-wrapper').hide();
    } else {
        $('#show-filters-structure-wrapper').show();
    }
}