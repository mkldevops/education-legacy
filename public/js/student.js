$(function() {

    // Add comment
    $('#add-comment').click(function(event) {
        event.preventDefault();
        $('.hidden', '#panel-comments').removeClass('hidden');
    });

    $('#modal-classperiod').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var data = $(button).parents('tr').data(); // Extract info from data-* attributes

        $('form', this).data(data);
        $('form select', this).val(data.classPeriod);
    });

    // Append student to class period
    $('#classperiod-change').change(function(event) {
        var select = $(this);
        var student = $(this).parents('form').data('id');
        var tdClassPeriod = $('.class-period', '#student-' + student);
        var classPeriod = $(this).val();
        console.log(classPeriod);

        $.ajax({
             url: Routing.generate( 'app_api_class_period_update_student', { id : classPeriod }),
             type: "POST",
             data: { students : [student] }
        }).done(function(msg) {
             $('#modal-classperiod').modal('hide');

             $(select).val("0");

             console.log(msg);
             if(msg.success) {
                 $('.class-period-name', tdClassPeriod)
                     .replaceWith( $('<a/>', {
                         href : Routing.generate('app_class_period_show', { id : classPeriod }),
                         text : ' Classe ' + msg.name,
                         "class" : "class-period fa fa-folder-open class-period-" + classPeriod
                     }));

                 if(classPeriod === "0") {
                     $('.icon', tdClassPeriod).removeClass('sign-out text-warning').addClass('sign-in text-success');
                 } else {
                     $('.icon', tdClassPeriod).removeClass('sign-in text-success').addClass('sign-out text-warning');
                 }
             } else {
                 $(tdClassPeriod).html('').append( content.old );
             }
        });
    });

    // Désactive student
    $( 'a.student-disable' ).click(function(event) {
        event.preventDefault();

        var trStudent = $(this).parents('tr');

        $('.student-setting', trStudent)
                .hide()
                .append($('<i/>', { class : 'fa fa-spinner fa-spin' }));

        $.ajax({
             url: Routing.generate( 'app_student_edit_status', { id : $(trStudent).data('id')}),
             type: "POST",
             data: { enable : 0, id : $(trStudent).data('id') }
         }).done(function(msg) {
             if(msg.success) {
                 $(trStudent).hide('fade', function() {
                     $(this).remove();
                });
             } else {
                 $(tdClassPeriod).html('').append( content.old );
             }
         });
    });

    // After show popover
    $('[data-toggle="popover"]').on('shown.bs.popover', function() {
        var myPopover = $(this);

        // add phone with form
        $('form.student-phone-action').submit(function() {
            $('[type=submit]', this).attr('disabled', true);

            $(this).setPhoneStudent($(this).serializeObject());

            $(myPopover).popover('hide');

            return false;
        });

        // edit phone
        $('i.student-phone-action').click(function() {
            var result = $(this).setPhoneStudent({ key : $(this).parents('li').data('key') });

            return false;
        });
    });

    $('#submit-comment').click(function() {
        $('form#student_addcomment').submit();
    });

    $('form#student_addcomment').submit(function() {
        $.post( $(this).attr('action'), $(this).serialize())
          .done(function( data ) {
            alert( "Data Loaded: " + data );
          });

        return null;
    });
});

$.fn.setPhoneStudent = function(param) {
    var student     = $(this).parents('.set-student').data('id');
    var dataStudent = $.extend({ action : $(this).data('action').length > 0 ? $(this).data('action') : 'add' }, param);
    var result      = false;

    $.ajax({
         url: Routing.generate( 'student_set_phone', { id : student }),
         type: "POST",
         data: dataStudent
     }).done(function(msg) {

         if(msg.success) {
             content = $.parseHTML($('#set-phone-' + student).data('content'));
             $(content).children('ul').html('');

             for(keyPhone in msg.data.listPhones) {
                 $(content).children('ul').append('<li class="row student-phone-key-' + student + '-' + keyPhone + '" data-key="' + keyPhone + '"><a href="' + msg.data.listPhones[keyPhone] + '" class="col-md-9"><i class="fa fa-phone"></i> ' + msg.data.listPhones[keyPhone] + '</a><i class="fa fa-trash pointer col-md-3 student-phone-action" data-action="delete"></i></li>');
             }

             $('#set-phone-' + student).attr('data-content', $(content).html());

             if(dataStudent.action === 'delete') {
                 console.log($('.student-phone-key-' + student + '-' + dataStudent.key ));
                 $('.student-phone-key-' + student + '-' + dataStudent.key ).remove();
             }
         }

         result = msg.success;
     }).fail(function(a, b, c) {
         return false;
     });

     return result;
};
