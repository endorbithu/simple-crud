<?php

return [

    'admin_guard' => 'admin',

    'middleware' => [],

    'activity_log_keep_days' => 90,

    //ha van / jel az elemben, az azt jelentoi, hogy a base_path($elem) ben található mappák nevei lesznek hozzáadva namespacenek
    'namespaces' => [
        'App', 'app_modules/'
    ],

    'own_error_handling' => true,

    'multilang_all_text' => false, //ha ez false, akkor a multilang direktívát kell beleírni az adott mezőhöz

    'multilang_languages' => ['hu', 'en'], //az első elem lesz az alapértelmezett, ha nincs semmilyen langugae elválsasztás

    'blades' => [
        'in_head_tag' => [

        ],

        'before_content' => [

        ],

        'after_content' => [

        ],

        //itt egyenként felül lehet írni, ha üresen marad, akkor a fenti beállítás lesz érvényben
        'index' => [
            'in_head_tag' => '',
            'before_content' => '',
            'after_content' => ''
        ],
        'show' => [
            'in_head_tag' => '',
            'before_content' => '',
            'after_content' => ''
        ],
        'create' => [
            'in_head_tag' => '',
            'before_content' => '',
            'after_content' => ''
        ],
        'update' => [
            'in_head_tag' => '',
            'before_content' => '',
            'after_content' => ''
        ],


    ],

    'html_class' => [
        'success-msg' => ' alert alert-success ',
        'error-msg' => ' alert alert-danger  ',

        'simplecrud-container' => ' container ',
        'simplecrud-description-container' => 'width50pc',
        'simplecrud-content-container' => 'width50pc',
        'simplecrud-datatable-container' => '',
        'h2_tag' => '',

        'image_container_in_show' => '',
        'image_in_show' => '',


        'div_show_field_container' => '',
        'div_show_field_name' => 'bold',
        'div_show_value' => '',

        'form_tag' => '',
        'label_tag' => '',
        'input_tag' => 'form-control',
        'input_tag_checkbox' => 'form-control width30px',
        'input_submit' => 'btn btn-md btn-default',
        'textarea_tag' => 'form-control',
        'texteditor' => '',
    ],

    'wysiwyg_in_head' => '<script src="https://cdn.tiny.cloud/1/_________TOKEN_______/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>',

    'wysiwyg_in_body' => <<<JS
    tinymce.init({
      selector: '.texteditor',
      plugins: 'a11ychecker advcode casechange export formatpainter image editimage linkchecker autolink lists checklist media mediaembed pageembed permanentpen powerpaste table advtable tableofcontents tinycomments tinymcespellchecker',
      toolbar: 'a11ycheck addcomment showcomments casechange checklist code export formatpainter image editimage pageembed permanentpen table tableofcontents',
      toolbar_mode: 'floating',
      tinycomments_mode: 'embedded',
      tinycomments_author: 'Author name',
    });
JS,

];
