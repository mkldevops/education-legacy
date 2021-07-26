$(function() {
    // Grid Table
    var globalParams = {
        "bJQueryUI": false,
        "bPaginate": false,
        "bLengthChange": false,
        "bFilter": true,
        "bSort": true,
        "bInfo": false,
        "bAutoWidth": true,
        "bScrollCollapse": true,
        "oLanguage": {
            "sSearch": 'Recherche : '
        },
        "aaSorting": [[0, 'asc']],
        "dom": 'T<"clear">lfrtip',
        "tableTools": {
            "sSwfPath": "/swf/copy_csv_xls_pdf.swf"
        }
    };

    $('#form_q').parent().removeClass('col-sm-9');
    $('button:not(.btn)').addClass('btn btn-default');

    if($.fn.dataTable !== undefined) {
        $.fn.dataTable.moment( 'L' );
        //$.fn.dataTable.moment( 'dddd, MMMM Do, YYYY' );
    }

    $("table[data-toggle='datatable']").each(function() {
        var params      = globalParams;
        var aoColumns   = [];

        $("thead th", this).each(function() {
            var column = {};

            if ( $(this).hasClass( 'no_sort' )) {
                column["bSortable"] = false;
            }

            // If is date
            if ($(this).hasClass('date')) {
                column["sType"] = "date-eu";

            // Else if number formated
            } else if ($(this).hasClass('numeric num')) {
                column["sType"] = "formatted-num";
            }

            aoColumns.push(column);

        });

        params["aoColumns"] = aoColumns;

        dataTable = $(this).dataTable(params);
    });

    //$("[data-toggle='tooltip']").tooltip();
    $('#wrapper').tooltip({
        selector: "[data-toggle=tooltip]",
        container: "#wrapper"
    });
    $("[data-toggle='popover']").popover({container: 'body'});

    if($("[data-toggle='multiselect']").length > 0) {
        $("[data-toggle='multiselect']").multiselect({
            includeSelectAllOption: true
        });
    }

    $('.datepicker').datetimepicker({
        format: 'DD/MM/YYYY'
    });

    $('.monthpicker').datetimepicker({
        viewMode: 'months',
        format: '01/MM/YYYY'
    });

    $('.timepicker').datetimepicker({
        format: 'HH:mm'
    });

    // delegate calls to data-toggle="lightbox"
    $(document).delegate('*[data-toggle="lightbox"]:not([data-gallery="navigateTo"])', 'click', function(event) {
        event.preventDefault();
        return $(this).ekkoLightbox({
            onShown: function() {
                if (window.console) {
                    return console.log('Checking our the events huh?');
                }
            },
            onNavigate: function(direction, itemIndex) {
                if (window.console) {
                    return console.log('Navigating '+direction+'. Current item: '+itemIndex);
                }
            }
        });
    });
});
