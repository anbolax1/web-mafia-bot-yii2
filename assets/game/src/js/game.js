$( document ).ready(function() {
    $(document).on("click", "#testButton", function (e){
        jQuery.ajax({
            url: 'send',
            method: 'get',
            success: function(response) {
            }
        });
    })
});
