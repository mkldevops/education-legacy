$(function() {

    $('#documents .document').hover(
       function(){ $('.action', this).removeClass('hidden') },
       function(){ $('.action', this).addClass('hidden') }
   );

    // Upload file document
    $("#fileuploader").uploadFile({
        url: $('#fileuploader').data('urlupload'),
        fileName : 'file',
        formData: $('#fileuploader').data(),
        onSuccess:function(files, response) {
            console.log(response);
            $('#documents').addDocument(response.data);
        }
    });

    $('.document-unlink').click(function() {
        $(this).unlinkDocument();
    });

    $('#lastDocumentModal').on('show.bs.modal', function() {
        var modal = $(this);
        if($('#last-documents-list .items a', modal).length === 0) {
            $('.modal-body', modal).lastDocument();
        }

        $('.document-plus', modal).click(function() {
            $('.modal-body', modal).lastDocument();
        });
    });
});

// Get last documents to entity with url
$.fn.lastDocument = function() {
    var myModal = $('#lastDocumentModal');
    var list    = $('#last-documents-list', myModal);
    var ids     = [];

    $('#documents .document ').each(function() {
       ids.push($(this).data('id'));
    });

    $('.document-plus', myModal)
        .removeClass('document-plus')
        .addClass('document-loader');

    $('.fa-plus', myModal)
        .removeClass('fa-plus')
        .addClass('fa-spin fa-spinner');

    $.ajax({
        type: "POST",
        timeout : 30000,
        dataType : 'json',
        url : Routing.generate( 'app_document_last'),
        data: { firstResult : $(list).data('first-result'), exists : ids }
    })
    .done(function( response ) {
        if (!response.success) {
            return;
        }
        if ($('.items', list).length === 0) {
            $(list)
                .prepend($('<div />', {class: 'items list-group text-left'}));
        }
        $(list).data('first-result', parseInt($(list).data('first-result')) + response.data.length);
        for (key in response.data) {
            let data = response.data[key];

            $('.items', list)
                .append(
                    $('<a />', {
                        href: '#',
                        html: data.document.name,
                        'class': 'list-group-item',
                        'data-data': JSON.stringify(data)
                    }).click(function () {
                        $('#documents').addDocument($(this).data('data'), $(this));

                        return false;
                    }).prepend(
                        $('<div />', {class: 'img-thumb'}).append(
                            $('<img />', {src: '/' + data.document.pathThumb, title: data.document.name})
                        )
                    )
                );
        }
    });


    $('.document-loader', myModal)
        .removeClass('document-loader')
        .addClass('document-plus');

    $('.fa-spin', myModal)
        .removeClass('fa-spin fa-spinner')
        .addClass('fa-plus');
};


$.fn.unlinkDocument = function() {
    var parent = $(this).parents('.document');

    $.ajax({
        type: "POST",
        timeout : 30000,
        url : $(this).data('url'),
        data: { document : $(parent).data('id') }
    })
    .done(function( responses ) {
        if(!responses.success) {
            alert('The document haven\'t unattached !');
            return false;
        }

        $(parent).hide('slide', function() {
           $(parent).remove();
        });
    });
};

// Add document to entity with url
$.fn.addDocument = function(document, btnLink) {
    let that = $('#documents');
    let fileUploader = $('#fileuploader').data();

    console.log(document);

    $.ajax({
        type: "POST",
        timeout : 30000,
        url : fileUploader.url,
        data: { document : document.id }
    })
    .done(function( responses ) {

        if(!responses.success) {
            alert('The document haven\'t attached !');
            return false;
        }

        // If is not multiple upload
        if(!Boolean(fileUploader.multiple)) {
            $(that).html('');
            $('div[class^="ajax-file-upload"]').hide();
        }

        $(that).append(
            $('<div />', {
                class : "document " + fileUploader.class,
                id : 'document-' + document.id,
                title : document.name
            }).append(
                $('<a />', {
                    href : "#",//Routing.generate("document_show", { id : data.id }),
                    "data-toggle" : "lightbox",
                    class : 'image'
                }).append(
                    $('<img />', {
                        src : '/' + document.pathThumb,
                        alt : document.name,
                        class : 'img-thumbnail'
                    })
                )
            ).append(
                $('<div />', {
                    class : 'alert alert-info text'
                }).append(
                    $('<i />', {
                        class : 'pull-left fa fa-file-o'
                    })
                ).append(
                    $('<span />', {
                        html : document.name
                    })
                )
            )
        );

        $('#document-' + document.id).addClass('bg-success');

        if(responses.success) {
            $(btnLink).hide('slide', function() {
                $(this).remove();
            });
        }
    });
}
