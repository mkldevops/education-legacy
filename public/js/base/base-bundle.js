$(function() {
    // Switch period
    $('.dropdown-period a').click(function(){
        $.ajax({
            url: Routing.generate('period_switch'),
            type: "POST",
            data: { id: $(this).data('id') }
        }).done(function(msg) {
            if(msg.success) {
                location.reload();
            }
        });
    });
});
