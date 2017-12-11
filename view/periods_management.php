<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * General Reports
 *
 * @author     Juan Pablo Moreno Muñoz
 * @package    block_ases
 * @copyright  2017 Juan Pablo Moreno Muñoz <moreno.juan@correounivalle.edu.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Standard GPL and phpdocs
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('../managers/instance_management/instance_lib.php');
require_once('../managers/periods_management/periods_lib.php');
require_once ('../managers/permissions_management/permissions_lib.php');
require_once ('../managers/validate_profile_action.php'); 
require_once ('../managers/menu_options.php');

global $PAGE;

// Variables for setup the page.
$title = "Gestionar Períodos";
$pagetitle = $title;
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('instanceid', PARAM_INT);

require_login($courseid, false);

$contextcourse = context_course::instance($courseid);
$contextblock =  context_block::instance($blockid);
$url = new moodle_url("/blocks/ases/view/periods_management.php", array('courseid' => $courseid, 'instanceid' => $blockid));

//se culta si la instancia ya está registrada
if(!consult_instance($blockid)){
    header("Location: instanceconfiguration.php?courseid=$courseid&instanceid=$blockid");
}

//se crean los elementos del menu
$menu_option = create_menu_options($USER->id, $blockid, $courseid);

//Se obtienen todos los períodos (semestres)
$semesters = get_all_semesters(); 

$table_semesters = '';
$table_semesters .= '<option value=""> --------------------------------- </option>';

foreach ($semesters as $semester) {
	$table_semesters.= '<option value="' .$semester->id .'">'.$semester->nombre .'</option>'; 	
}

//Crea una clase con la información que se llevará al template.   
$data = new stdClass;

// Evalua si el rol del usuario tiene permisos en esta view.
$actions = authenticate_user_view($USER->id, $blockid);
$data = $actions;
$data->table = $table_semesters;
$data->menu = $menu_option;

//configuracion de la navegación
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$node = $coursenode->add('Gestion de períodos del bloque',$url);
$node->make_active();

// Setup page
$PAGE->set_context($contextcourse);
$PAGE->set_context($contextblock);
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$PAGE->requires->css('/blocks/ases/style/styles_pilos.css', true);
$PAGE->requires->css('/blocks/ases/style/bootstrap_pilos.css', true);
$PAGE->requires->css('/blocks/ases/style/bootstrap_pilos.min.css', true);
$PAGE->requires->css('/blocks/ases/style/round-about_pilos.css', true);
$PAGE->requires->css('/blocks/ases/style/sweetalert.css', true);
$PAGE->requires->css('/blocks/ases/style/forms_pilos.css', true);
$PAGE->requires->css('/blocks/ases/style/add_fields.css', true);
$PAGE->requires->css('/blocks/ases/style/jqueryui.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/dataTables.foundation.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/dataTables.foundation.min.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/dataTables.jqueryui.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/dataTables.jqueryui.min.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/jquery.dataTables.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/jquery.dataTables.min.css', true);
$PAGE->requires->css('/blocks/ases/js/DataTables-1.10.12/css/jquery.dataTables_themeroller.css', true);
$PAGE->requires->css('/blocks/ases/js/select2/css/select2.css', true);
$PAGE->requires->css('/blocks/ases/style/side_menu_style.css', true);

$PAGE->requires->js_call_amd('block_ases/periods_management_main', 'init');

$output = $PAGE->get_renderer('block_ases');
$index_page = new \block_ases\output\periods_management_page($data);

echo $output->header();

echo $output->render($index_page);
echo $output->footer();

