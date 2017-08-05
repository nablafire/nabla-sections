(function($){
    $('body').on('click', '.nabla-front-page-admin-toggle', function(e){
		$(this).toggleClass('open');
		$('.nabla-front-page-admin-field').toggle();
    });
})(jQuery);
