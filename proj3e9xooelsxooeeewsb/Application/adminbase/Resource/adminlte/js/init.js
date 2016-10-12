/**
 * Get a prestored setting
 *
 * @param String name Name of of the setting
 * @returns String The value of the setting | null
 */
function get(name) {
    if (typeof (Storage) !== "undefined") {
        return localStorage.getItem(name);
    } else {
        window.alert('Please use a modern browser to properly view this template!');
    }
}

function store(name, val) {
    if (typeof (Storage) !== "undefined") {
        localStorage.setItem(name, val);
    } else {
        window.alert('Please use a modern browser to properly view this template!');
    }
}

var my_skins = [
    "skin-blue",
    "skin-black",
    "skin-red",
    "skin-yellow",
    "skin-purple",
    "skin-green",
    "skin-blue-light",
    "skin-black-light",
    "skin-red-light",
    "skin-yellow-light",
    "skin-purple-light",
    "skin-green-light"
];

function change_skin(cls) {
    $.each(my_skins, function (i) {
        $("body").removeClass(my_skins[i]);
    });

    $("body").addClass(cls);
    store('skin', cls);
    return false;
}

require.config(require_config);

require(['jquery', 'bootstrap', 'fastclick', 'app', 'cml'], function($) {
    $(function() {
        $("[data-skin]").on('click', function (e) {
            if($(this).hasClass('knob'))
                return;
            e.preventDefault();
            change_skin($(this).data('skin'));
        });
        var tmp = get('skin');
        if (tmp && $.inArray(tmp, my_skins))
            change_skin(tmp);

        if (typeof (onRequireJsReady) != 'undefined') {
            onRequireJsReady(cml);
        }
    });
});
