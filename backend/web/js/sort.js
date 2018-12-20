$.fn.clickSort = function(opts){
    var defaults = {
        speed:200
    };
    defaults.moveCallback = function(this_id,other_id, dataType){
    };
    var option = $.extend(defaults,opts);
    this.each(function(){
        var _this = $(this);
        _this.on('click','.move-up',function(){
            var parent = $(this).parents('.sortableitem');
            var thisId = parent.attr('data-id');
            var prevItem =  parent.prev('.sortableitem');
            var arrowId = prevItem.attr('data-id');
            if(prevItem.length==0)return;
            var parentTop = parent.position().top;
            var prevItemTop = prevItem.position().top;
            parent.css('visibility','hidden');
            prevItem.css('visibility','hidden');
            var so_width= $('.so1').width()+32;console.log(so_width);
            parent.clone().insertAfter(prevItem).css({position:'absolute',visibility:'visible',top:parentTop,width:so_width}).animate({top:prevItemTop,width:so_width},option.speed,function(){
                $(this).remove();
                parent.insertBefore(prevItem).css('visibility','visible');
                option.callback();
            });
            prevItem.clone().insertAfter(parent).css({position:'absolute',visibility:'visible',top:prevItemTop,width:so_width}).animate({top:parentTop,width:so_width},option.speed,function(){
                $(this).remove();
                prevItem.insertAfter(parent).css('visibility','visible');
            });
            if(typeof option.moveCallback == 'function')
            {
                option.moveCallback(thisId, arrowId, _this.attr('data-type'));
            }

        });
        _this.on('click','.move-down',function(){
            var parent = $(this).parents('.sortableitem');
            var thisId = parent.attr('data-id');
            var nextItem = parent.next('.sortableitem');
            var arrowId = nextItem.attr('data-id');
            if(nextItem.length==0)return;
            var parentTop = parent.position().top;
            var nextItemTop = nextItem.position().top;
            parent.css('visibility','hidden');
            nextItem.css('visibility','hidden');
            var so_width= $('.so1').width()+32;console.log(so_width);
            parent.clone().insertAfter(nextItem).css({position:'absolute',visibility:'visible',top:parentTop,width:so_width}).animate({top:nextItemTop,width:so_width},option.speed,function(){
                $(this).remove();
                parent.insertAfter(nextItem).css('visibility','visible');
                option.callback();
            });
            nextItem.clone().insertAfter(nextItem).css({position:'absolute',visibility:'visible',top:nextItemTop,width:so_width}).animate({top:parentTop,width:so_width},option.speed,function(){
                $(this).remove();
                nextItem.insertBefore(parent).css('visibility','visible');
            });
            if(typeof option.moveCallback == 'function')
            {
                option.moveCallback(thisId, arrowId,  _this.attr('data-type'));
            }
        });
    });
};

