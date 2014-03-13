<?php
/**
 * Render Template Module
 *
 * A small template loader module that loads a template from a prefedined 
 * location (usually the themes TEMPLATES_PATH) and sets up variables to be 
 * include in the templates scope. Using the render_template action the path 
 * and variables (data) can be changed/overridden. 
 *
 * @package Render_Template
 * @uses    Location_Helpers
 * @author  Simon Holloway <holloway.sy@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @version 1.0.0
 */

/**
 * Render a template from the templates directory
 * 
 * Pass a path and a dataset to this function and render_template hooks will 
 * attempt to interpret the path into a real file path, then the file is 
 * included and the data var is added to the templates scope, arrays are 
 * extracted by default, but the original is still available at $original_data
 * 
 * @param  string   $path 
 * @param  array    $data
 * @return void
 */
function render_template($path, array $data = array()) {
    $original_path = $path;
    $original_data = $data;
    $data = apply_filters('render_template_data', $data, $original_data, $path);
    $data = apply_filters('render_template_data_' . $original_path, $data, $original_data, $path);
    $path = apply_filters('render_template_path', $path, $original_path, $data);
    $path = apply_filters('render_template_path_' . $original_path, $path, $original_path, $data);
    if(is_array($data)) extract($data);
    require($path);
}

/**
 * Set the default path in the theme templates directory
 * 
 * Set the path to {TEMPLATES_PATH}/{$path}.php by default
 *  
 * @param string $path
 * @param string $original_path path passed into render_template
 * @param string $data
 * @return void
 */
function set_default_template_path($path, $original_path, $data) {
    $child_template = get_child_template_path($original_path . '.php');
    $parent_template = get_template_path($original_path . '.php');
    if (is_file($child_template)) {
        $path = $child_template;
    } elseif (is_file($parent_template)) {
        $path = $parent_template;
    }
    return $path;
}
add_filter('render_template_path', 'set_default_template_path', 5, 3);

/**
 * Set the path to module templates when the ":" operator is used
 * 
 * if a module template is called the path will be set to 
 * {MODULES_PATH}/{$module}/templates/{$path}.php  
 * 
 * @example render_template('mymodule:mydirectory/mytemplate');
 * 
 * @param string $path
 * @param string $original_path path passed into render_template
 * @param string $data
 * @return void
 */
function set_module_template_path($path, $original_path, $data) {
    if (str_contains($original_path, ':')) {
        list($module, $new_path) = explode(':', $original_path);
        $module_template = get_module_path($module . '/templates/' . $new_path . '.php');
        if (is_file($module_template)) {
            $path = $module_template;
        }
    }
    return $path;
}
add_filter('render_template_path', 'set_module_template_path', 6, 3);