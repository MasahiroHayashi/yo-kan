window.addEventListener("load",appearHeader,false);  

// ヘッダーを出したり隠したりするためのスクリプト
function appearHeader(){
	var _window = $(window),
		_header = $('.site-header'),
		heroBottom,
		startPos,
		winScrollTop;
	_window.on('scroll',function(){
		winScrollTop = $(this).scrollTop();
		heroBottom = $('.hero').height();
		heroBottom = 50;
		if (winScrollTop >= startPos) {
			if(winScrollTop >= heroBottom){
				_header.addClass('hide');
			}
		} else {
			_header.removeClass('hide');
		}
		startPos = winScrollTop;
	});
	_window.trigger('scroll');
}

// ハンバーガーメニューのためのスクリプト
$(function(){
    $(".btn-gnavi").on("click", function(){
        // ハンバーガーメニューの位置を設定
        var rightVal = 0;
        if($(this).hasClass("open")) {
            // 位置を移動させメニューを開いた状態にする
            rightVal = -240;
            // メニューを開いたら次回クリック時は閉じた状態になるよう設定
            $(this).removeClass("open");
			document.getElementById("menu_close").style.display ="none";
        } else {
            // メニューを開いたら次回クリック時は閉じた状態になるよう設定
            $(this).addClass("open");
			// サイドクローズメニューの幅を取得
			document.getElementById('menu_close').style.width = (document.body.clientWidth - 240)+'px';
			document.getElementById("menu_close").style.display ="block";
        }
        $("#global-navi").stop().animate({
            right: rightVal
        }, 200);
    });
});
// ハンバーガーメニューをクローズするサイドバーのスクリプト
$(function(){
    $("#menu_close").on("click", function(){
		var rightVal = 0;
        if($(".btn-gnavi").hasClass("open")) {
            // 位置を移動させメニューを開いた状態にする
            rightVal = -240;
            // メニューを開いたら次回クリック時は閉じた状態になるよう設定
            $(".btn-gnavi").removeClass("open");
        } else {
            // メニューを開いたら次回クリック時は閉じた状態になるよう設定
            $(".btn-gnavi").addClass("open");
        }
        $("#global-navi").stop().animate({
            right: rightVal
        }, 200);
		document.getElementById("menu_close").style.display ="none";
    });
});