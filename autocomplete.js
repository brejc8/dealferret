var MIN_LENGTH = 2;

$( document ).ready(function() {
	$("#keyword").keyup(function() {
		var keyword = $("#keyword").val();
		if (keyword.length >= MIN_LENGTH) {

			$.get( "autocomplete.php", { keyword: keyword } )
			.done(function( data ) {
				$('#results').html('');
				var results = jQuery.parseJSON(data);
				$(results).each(function(key, value) {
					$('#results').append('<div class="item">' + value + '</div>');
				})

			    $('.item').click(function() {
			    	var text = $(this).html();
			    	$('#keyword').val(text);
			    })

			});
		} else {
			$('#results').html('');
		}
	});

    $("#keyword").blur(function(){
    		$("#results").fadeOut(500);
    	})
        .focus(function() {		
    	    $("#results").show();
    	});

});

$( document ).ready(function() {
        $(".wine_price").keyup(function() {
           var price = $(this).closest('form').find('.wine_price').val();
           var group = $(this).closest('form').find('.wine_group').val();
           $.get( "wineupdate.php", { price: price, group: group});
           
        });
});

