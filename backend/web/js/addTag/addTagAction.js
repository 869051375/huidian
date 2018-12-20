
//添加标签功能动态生成标签组
function createTags(rs) {
    rs.data.map(function (item) {
        var input = $('<input class="tagInput" data-color=' + item.color + ' data-id=' + item.id + ' type="input" name=tag-name' + item.id + ' value=' + item.name + '>');
        var helpBlock = $('<div class="help-block help-block-error"></div>');
        var tagConItem = $('<div class="tag-content-item"></div>');
        tagConItem.append(input);
        tagConItem.append(helpBlock);
        var label = $('<label></label>');
        var formGroup = $('<div class="form-group field-full_name"></div>');
        formGroup.append(label);
        formGroup.append(tagConItem);
        var colorCon = $('<div class="show-color-content" style="background-color: #' + item.color + ';"></div>');
        var setItem = $('<div class="constomer-set-item"></div>');
        setItem.append(formGroup);
        setItem.append(colorCon);
        $('.group-container').append(setItem).insertBefore($('#addBtnText'));
    });
}
//客户列表，商机列表全局提示框
function setGlobalTip(message, isError) {
    if(isError) {
        $('#self-danger-tip span').html(message);
        $('#self-danger-tip').removeClass('hide').addClass('show alert-danger');
    }else{
        $('#self-danger-tip span').html(message);
        $('#self-danger-tip').removeClass('hide alert-danger').addClass('show alert-success');
        setTimeout(function(){
            $('#self-danger-tip').removeClass('show').addClass('hide');
        },1500);
    }
}
