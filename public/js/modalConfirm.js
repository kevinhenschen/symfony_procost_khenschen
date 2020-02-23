(function () {
    "use strict";

    $(document).on('click','.confirm-open', function(){
        let title   = $(this).attr('title');
        let message = $(this).attr('message');
        let path_y  = $(this).attr('path_yes');
        let path_n  = $(this).attr('path_no');

        $('modal-confirm-title').html(title);
        $('modal-confirm-message').html(message);
        $('modal-confirm-path-y').attr('href',path_y);
        $('modal-confirm-path-n').attr('href',path_n);

        $('#modal-confirm').modal({
            backdrop: 'static',
            keyboard: false
        });

    })


})();