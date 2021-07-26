/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function strip_tags(input, allowed) {
    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
            commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return input.replace(commentsAndPhpTags, '').replace(tags, function($0, $1) {
        return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
    });
}

if(jQuery.fn.dataTableExt !== undefined) {
    $.extend(jQuery.fn.dataTableExt.oSort, {
        "date-eu-pre": function(date) {
            // clean the sring
            let aDate = strip_tags(date).trim().replace(/:/g, "").split(" ");
            date = aDate[0];
            let hour = 0;

            if(date.length === 0) {
                return false;
            }

            if (aDate[1] !== undefined) {
                hour = aDate[1];
            }

            if (date.indexOf('.') > 0) {
                /*date a, format dd.mn.(yyyy) ; (year is optional)*/
                var eu_date = date.split('.');
            } else {
                /*date a, format dd/mn/(yyyy) ; (year is optional)*/
                var eu_date = date.split('/');
            }

            /*year (optional)*/
            if (eu_date[2]) {
                var year = eu_date[2];
            } else {
                var year = 0;
            }

            /*month*/
            let month = eu_date[1];
            if (month.length === 1) {
                month = 0 + month;
            }

            /*day*/
            let day = eu_date[0];
            if (day.length === 1) {
                day = 0 + day;
            }

            return (year + month + day + hour) * 1;
        },
        "date-eu-asc": function(a, b) {
            return ((a < b) ? -1 : ((a > b) ? 1 : 0));
        },
        "date-eu-desc": function(a, b) {
            return ((a < b) ? 1 : ((a > b) ? -1 : 0));
        },
        "formatted-num-pre": function(a) {
            a = strip_tags(a);
            a = (a === "-") ? 0 : a.replace(/[^\d\-\.]/g, "");
            return parseFloat(a);
        },
        "formatted-num-asc": function(a, b) {
            return a - b;
        },
        "formatted-num-desc": function(a, b) {
            return b - a;
        }
    });
}

var dataTrlCountry = {
    "en": {
        "search": "Search",
        "lang": ""
    },
    "fr": {
        "search": "Recherche",
        "lang": "fr"
    }
};

var currentDataTrl = dataTrlCountry[document.documentElement.lang];

function array_keys(input) {
    var output = new Array();
    var counter = 0;
    for (i in input) {
        output[counter++] = i;
    }
    return output;
}

function removeAccent(query) {
    query = query.toLowerCase()
            .replace(/[èéêë]/g, 'e')
            .replace(/[ç]/g, 'c')
            .replace(/[àâä]/g, 'a')
            .replace(/[ïî]/g, 'i')
            .replace(/[ûùü]/g, 'u')
            .replace(/[ôöó]/g, 'o');

    return query;
}

$(document).bind("ajaxSend", function() {
    $('.ajax-loader').show();
}).bind("ajaxComplete", function() {
    $('.ajax-loader').hide();
});

$.fn.serializeObject = function() {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};
