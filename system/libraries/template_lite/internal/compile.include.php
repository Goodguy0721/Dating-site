<?php

/**
 * Template Lite
 *
 * Type:	 compile
 * Name:	 section_start
 */
function compile_include($arguments, &$object)
{
    $_args = $object->_parse_arguments($arguments);

    if (empty($_args['load_type'])) {
        $_args['load_type'] = false;
    }

    $arg_list = array();
    if (empty($_args['file'])) {
        $object->trigger_error("missing 'file' attribute in include tag", E_USER_ERROR, __FILE__, __LINE__);
    }

    foreach ($_args as $arg_name => $arg_value) {
        if ($arg_name == 'file') {
            $include_file = $arg_value;
            continue;
        } elseif ($arg_name == 'assign') {
            $assign_var = $arg_value;
            continue;
        }
        /**
         * added for PG_Core
         */
        elseif ($arg_name == 'module') {
            $module = $arg_value;
            continue;
        } elseif ($arg_name == 'theme') {
            $theme = $arg_value;
            continue;
        } elseif ($arg_name == 'menu') {
            $menu_type = $arg_value;
            continue;
        }

        /**
         * /added for PG_Core
         */
        if (is_bool($arg_value)) {
            $arg_value = $arg_value ? 'true' : 'false';
        }
        $arg_list[] = "'$arg_name' => $arg_value";
    }

    /**
     * added for PG_Core
     */
    $output_add = (isset($module)) ? " \$this->module_path. " . $module . ". \$this->module_templates. " : " \$this->general_path. ";
    $output_add .= " \$this->get_current_theme_gid('" . (isset($theme) ? $theme : '') . "', '" . (isset($module) ? $module : '') . "'). ";

    /**
     * /added for PG_Core
     */
    if (isset($assign_var)) {
        /*$output = '<?php $_templatelite_tpl_vars = $this->_vars;' .
            "\n\$this->assign(" . $assign_var . ", \$this->_fetch_compile_include(" . $output_add . $include_file . ", array(".implode(',', (array)$arg_list).")));\n" .
            "\$this->_vars = \$_templatelite_tpl_vars;\n" .
            "unset(\$_templatelite_tpl_vars);\n" .
            ' ?>';*/
        $output = '<?php' .
            "\n\$this->assign(" . $assign_var . ", \$this->_fetch_compile_include(" . $output_add . $include_file . ", array(" . implode(',', (array) $arg_list) . ")));\n" .
            ' ?>';
    } else {
        $output = '<?php $_templatelite_tpl_vars = $this->_vars;' .
            "\necho \$this->_fetch_compile_include(" . $output_add . $include_file . ", array(" . implode(',', (array) $arg_list) . "));\n" .
            "\$this->_vars = \$_templatelite_tpl_vars;\n" .
            "unset(\$_templatelite_tpl_vars);\n" .
            ' ?>';
    }

    return $output;
}
