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
 * Estrategia ASES
 *
 * @author     Isabella Serna Ramírez
 * @package    block_ases
 * @copyright  2017 Isabella Serna Ramírez <isabella.serna@correounivalle.edu.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ('../validate_profile_action.php');
require_once('tracking_functions.php');
require_once('../student_profile/studentprofile_lib.php');
require_once('../periods_management/periods_lib.php');
require_once ('../dphpforms/dphpforms_forms_core.php');
require_once ('../dphpforms/dphpforms_records_finder.php');
require_once ('../dphpforms/dphpforms_get_record.php');
require_once '../user_management/user_lib.php';

global $USER;

if(isset($_POST['type'])&&$_POST['type']=="getInfo"&&isset($_POST['instance'])) 
 {
    $datos=[];
    $datos["id"]=$USER->id;
    $datos["username"]=$USER->username;
    $datos["email"]=$USER->email;
    $datos["rol"]=get_id_rol_($USER->id,$_POST['instance']);
    $datos["name_rol"]=get_name_rol($datos["rol"]);

    echo json_encode($datos);
}

if(isset($_POST['type'])&&isset($_POST['instance'])&&$_POST['type']=="get_student_trackings"&&isset($_POST['student_code'])) 
 {
    // Student trackings (Seguimientos)

      $html_tracking_peer = "";



    $student_code = explode("-", $_POST['student_code']);

    $ases_student = get_ases_user_by_code($student_code[0]);
    $student_id = $ases_student->id;
    //$array_peer_trackings_dphpforms = dphpforms_find_records('seguimiento_pares', 'seguimiento_pares_id_estudiante', $student_code[0], 'DESC');
    $array_peer_trackings_dphpforms=get_tracking_peer_student_current_semester($student_code[0], '21');

    $array =render_student_trackings($array_peer_trackings_dphpforms);
    echo json_encode($array);
 
    


}



?>
