
function loading(){
	showLoading();
	var timer = null;
	timer = setInterval(function(){
		if($('body').is('.pace-done')){
			hideLoading();
			clearInterval(timer);
		}
	},20);
}

function showLoading()
{
    var divs = $('<div class="loading-show"><div class="loading-show-shade"></div><div class="loading-show-content"></div></div>');
	var loadding = $('<div class="sk-spinner sk-spinner-wave"><div class="sk-rect1"></div><div class="sk-rect2"></div><div class="sk-rect3"></div><div class="sk-rect4"></div><div class="sk-rect5"></div></div>');
    $('body').append(divs);
    $('.loading-show-content').append(loadding);
	$('.loading-show').css({'position': 'fixed','top': '0','left':'0','width':'100%','height':'100%','z-index':'99999999999'});
	$('.loading-show-shade').css({'position':'absolute','top':'0','left':'0','height':'100%','width':'100%','background':'#000','opacity':'0.8','margin-left':'-25px','margin-top':'-15px'});
	$('.loading-show-content').css({'position':'absolute','top':'50%','left':'50%','width':'50px','height':'30px','margin-left':'-25px','margin-top':'-15px'});
	$('.sk-spinner-wave').css({'width':'100px','height':'60px'});
	$('.sk-spinner-wave div').css({'margin':'0 2px'});
}

function hideLoading()
{
    $('.loading-show').remove();
}