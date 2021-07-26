$('.ckeditor').each(function() {
    CKEDITOR.replaceClass = 'ckeditor'
    CKEDITOR.config.toolbarLocation = 'bottom';
    CKEDITOR.config.language = 'fr';

    CKEDITOR.config.removePlugins = 'elementspath,save,font';
});

$('.ckeditor.basic').each(function() {
    CKEDITOR.config.toolbar = 'Basic';
    CKEDITOR.config.toolbar_Basic =
    [
        ['Source', 'Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink','-','About']
    ];
});