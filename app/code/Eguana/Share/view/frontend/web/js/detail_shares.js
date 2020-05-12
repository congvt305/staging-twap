require(['jquery'], function($){
    $(function(){
        var $target, $on, boo;

        $target = $('.info_shares');
        $on = $target.find('.toggle');

        $target.find('.toggle_btn').click(function(e){

            boo = $on.hasClass('on');

            if ( boo ) {
                $on.removeClass('on');
                return;
            }
            $on.addClass('on');
        })

        $('.toggle_shares a').click(function(e){
            $on.removeClass('on');
        })
    })
});