<?php

/**
 * Grader report for ases
 *
 * @author     Luis Gerardo Manrique Cardona
 * @package    grades
 * @copyright  2018 Luis Gerardo Manrique Cardona <luis.manrique@correounivalle.edu.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Class ases_grade_report_grader Grader report class extended for ASES
 *
 * @property  int $course_id Id of the course when the report will be generated
 * @property  int $course_caller_id Id of the course where the report is generated
 * @property int $instance_id Id of the instance where the block is instanciated and the report is
 *  generated
 *
 * @see grade_report_grader
 *
 */
defined('MOODLE_INTERNAL') || die;
require_once (__DIR__ . '/../../classes/AsesUser.php');
require_once( __DIR__ . '/../../managers/student_profile/studentprofile_lib.php');
require_once ($CFG->dirroot . '/grade/report/grader/lib.php');
require_once ($CFG->dirroot . '/grade/lib.php');
require_once ($CFG->dirroot . '/user/lib.php');
class ases_grade_report_grader extends grade_report_grader {


    public $course_id;
    public $course_caller_id;
    public $instance_id;

    /**
     * Overwrited method for load users and final grades before return the grade table
     *
     * @param bool $displayaverages
     *
     * @return string
     */
    public function get_grade_table($displayaverages = false)
    {
        $this->load_users();
        $this->load_final_grades();
        return parent::get_grade_table($displayaverages); // TODO: Change the autogenerated stub
    }
    /**
     * Constructor. Sets local copies of user preferences and initialises grade_tree.
     * @param int $courseid
     * @param int $course_caller_id Id of the course where the report should be was generated
     * @param int $instance_id Id of the ASES instance
     * @param object $gpr grade plugin return tracking object
     * @param string $context
     * @param int $page The current page being viewed (when report is paged)
     * @param int $sortitemid The id of the grade_item by which to sort the table
     */
    function __construct(int $courseid, $course_caller_id,  $instance_id, $context,  $page = null, ?int $sortitemid = null)
    {
        $gpr = new grade_plugin_return(
            array(
                'type' => 'report',
                'plugin' => 'grader',
                'courseid' => $courseid));
        $context = context_course::instance($courseid);
        parent::__construct($courseid, $gpr, $context, $page, $sortitemid);
        $this->instance_id = $instance_id;
        $this->course_id = $courseid;
        $this->course_caller_id = $course_caller_id;

    }

    /**
     * Se ha sobrecargado este metodo para que los links de los nombres de usuario y sus respectivas imagenes
     * no redirijan a el perfil de moodle si no al perfil de la ficha general de ASES en la vista student_profile.php
     * @param bool $displayaverages
     *
     * @return array
     */
    function get_left_rows($displayaverages) {
        $rows = parent::get_left_rows($displayaverages);
        $doc = new DOMDocument();
        /* Se editan los href de cada nombre de usuario en la tabla para que redirija a sudent_profile.php*/
        /* @var html_table_row $row */
        foreach($rows as $row) {
            /* @var html_table_cell $cell */
            foreach($row->cells as &$cell) {
                if(strpos($cell->text, 'username') && strpos($cell->text, 'href')  ) {
                    /* A element */
                    /* @var DOMDocument $document */

                    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $cell->text);
                    /* @var string $cell_user_profile_link Example: http://localhost/moodle/user/profile.php?id=122098 */

                    /**
                     * The cell->text have two ´a´ elements, first is a link of the user image
                     * second ´a´ is a link of username
                     */
                    $cell_user_image_link_str = $doc->getElementsByTagName('a')->item(0)->getAttribute('href');
                    $cell_user_profile_url = parse_url($cell_user_image_link_str);
                    $url_query = array();
                    parse_str($cell_user_profile_url['query'], $url_query);
                    $cell_user_id = $url_query['id'];
                    /* $this->users only have the fields than return ´user_picture::fields functions´*/
                    $user_complete = user_get_users_by_id([$cell_user_id])[$cell_user_id];
                    $link_general_report = get_student_profile_url($this->course_caller_id, $this->instance_id, $user_complete->username);
                    $link_general_report_string =  $link_general_report->out(false);
                    /* Change the link for ´a´ of user image*/
                    $user_image_a = $doc
                        ->getElementsByTagName('a')
                        ->item(0);
                    $user_image_a->setAttribute('href', $link_general_report_string);
                    /* Change the link for ´a´ of user name*/
                    $user_name_a = $doc
                        ->getElementsByTagName('a')
                        ->item(1);
                    $user_name_a->setAttribute('href', $link_general_report_string);

                    /* Set the target of links from users */
                    $user_name_a->setAttribute('target', '_blank');
                    $user_image_a->setAttribute('target', '_blank');

                    $new_text= $doc->saveXML();
                    $cell->text = $new_text;

                }
            }
        }
        return $rows;
    }

    /**
     * Se reescribe la función para que solamente los usuarios ASES se carguen en
     */
    function setup_users()
    {
        parent::setup_users(); // TODO: Change the autogenerated stub

        $query = AsesUser::_select_active_ases_users()
            ->columns('user_extended.'.AsesUserExtended::ID_MOODLE_USER)
            ->compile();
        $ids_ases_users_active = $query->sql();
        $this->userwheresql .= "AND u.id in ($ids_ases_users_active)";


    }
}
