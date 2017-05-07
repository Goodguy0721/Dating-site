<?php

$module['module'] = 'field_editor';
$module['install_name'] = 'Field editor';
$module['install_descr'] = 'Create and manage extra fields in user\'s profile, in search forms ';
$module['version'] = '3.03';
$module['files'] = array(
    array('file', 'read', "application/config/field_editor.php"),
    array('file', 'read', "application/modules/field_editor/js/admin-field-editor-select.js"),
    array('file', 'read', "application/modules/field_editor/js/admin-form-fields.js"),
    array('file', 'read', "application/modules/field_editor/controllers/admin_field_editor.php"),
    array('file', 'read', "application/modules/field_editor/install/module.php"),
    array('file', 'read', "application/modules/field_editor/install/permissions.php"),
    array('file', 'read', "application/modules/field_editor/install/settings.php"),
    array('file', 'read', "application/modules/field_editor/install/structure_deinstall.sql"),
    array('file', 'read', "application/modules/field_editor/install/structure_install.sql"),
    array('file', 'read', "application/modules/field_editor/models/fields/checkbox_field_model.php"),
    array('file', 'read', "application/modules/field_editor/models/fields/field_type_model.php"),
    array('file', 'read', "application/modules/field_editor/models/fields/like_field_model.php"),
    array('file', 'read', "application/modules/field_editor/models/fields/multiselect_field_model.php"),
    array('file', 'read', "application/modules/field_editor/models/fields/range_field_model.php"),
    array('file', 'read', "application/modules/field_editor/models/fields/select_field_model.php"),
    array('file', 'read', "application/modules/field_editor/models/fields/text_field_model.php"),
    array('file', 'read', "application/modules/field_editor/models/fields/textarea_field_model.php"),
    array('file', 'read', "application/modules/field_editor/models/field_editor_install_model.php"),
    array('file', 'read', "application/modules/field_editor/models/field_editor_model.php"),
    array('file', 'read', "application/modules/field_editor/models/field_editor_forms_model.php"),
    array('file', 'read', "application/modules/field_editor/models/field_editor_searches_model.php"),
    array('file', 'read', "application/modules/field_editor/models/field_types_loader_model.php"),
    array('dir', 'read', 'application/modules/field_editor/langs'),
);

$module['dependencies'] = array(
    'moderation'    => array('version' => '1.01'),
    'start'         => array('version' => '1.03'),
    'menu'          => array('version' => '2.03'),
);
$module['linked_modules'] = array(
    'install' => array(
        'moderation'    => 'install_moderation',
        'menu'          => 'install_menu',
    ),
    'deinstall' => array(
        'moderation'    => 'deinstall_moderation',
        'menu'          => 'deinstall_menu',
    ),
);
