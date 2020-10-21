<?php

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_once '../managers/instance_management/instance_lib.php';
require_once('../managers/menu_options.php');
require_once '../managers/lib/lib.php';
require_once('../managers/validate_profile_action.php');

include '../lib.php';
include "../classes/output/monitorias_page.php";
include "../classes/output/renderer.php";

global $PAGE;

$page_title = 'Monitorias';
$course_id = required_param('courseid', PARAM_INT);
$block_id = required_param('instanceid', PARAM_INT);

require_login($course_id, false);

if (!consult_instance($block_id)) {
    header("Location: instanceconfiguration.php?courseid=$course_id&instanceid=$block_id");
}

$contextcourse = context_course::instance($course_id);
$contextblock = context_block::instance($block_id);
$id_current_user = $USER->id;

$url = new moodle_url("/blocks/ases/view/monitorias.php", array('courseid' => $course_id, 'instanceid' => $block_id));

$data = new stdClass();

$coursenode = $PAGE->navigation->find($course_id, navigation_node::TYPE_COURSE);
$blocknode = navigation_node::create('Monitorias',$url, null, 'block', $block_id);
$coursenode->add_node($blocknode);

$menu_option = create_menu_options($id_current_user, $block_id, $course_id);
$data->menu = $menu_option;

$PAGE->set_url($url);
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

$PAGE->requires->css('/blocks/ases/style/aaspect.min.css', true);
$PAGE->requires->css('/blocks/ases/style/side_menu_style.css', true);
$PAGE->requires->css('/blocks/ases/js/select2/css/select2.css', true);

//$PAGE->requires->js_call_amd('block_ases/communications', 'init');

$output = $PAGE->get_renderer('block_ases');
$monitorias_page = new \block_ases\output\monitorias_page($data);

echo $output->header();
echo $output->render($monitorias_page);
echo $output->footer();