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
 * Talentos Pilos
 *
 * @author     Iader E. García Gómez
 * @author     Camilo José Cruz Rivera
 * @author     Jeison Cardona Gómez
 * @package    block_ases
 * @copyright  2017 Iader E. García <iadergg@gmail.com>
 * @copyright  2017 Camilo José Cruz Rivera <cruz.camilo@correounivalle.edu.co>
 * @copyright  2018 Jeison Cardona Gómez <jeison.cardona@correounivalle.edu.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once dirname(__FILE__) . '/../../../../config.php';
require_once dirname(__FILE__).'/../../core/module_loader.php';

require_once (__DIR__.'/lib.php');
require_once (__DIR__.'/../dphpforms/v2/dphpforms_lib.php');
require_once (__DIR__. '/../../classes/TrackingStatus.php');

module_loader("periods");
/**
 * Obtains an user object given user id from {talentospilos_usuario} table
 *
 * @see get_user_moodle($id)
 * @param $id --> moodle user id
 * @return object
 * @throws dml_exception A DML specific exception is thrown for any errors.
 */
function get_user_moodle($id)
{

    global $DB;

    $sql_query = "SELECT * from {user} where id= (SELECT id_moodle_user
                                                  FROM {talentospilos_user_extended} extended
                                                  WHERE id_ases_user = $id and tracking_status=1)";
    $user = $DB->get_record_sql($sql_query);

    return $user;
}
/**
 * Retorna los usuarios ases homonimos a un usuario de moodle
 * @param string $mdl_username Username completo de moodle
 * @return array Nombres y apellidos de los usuarios ases [array('firstname', 'lastname')...] Array vacio si no hay homonimos
 */

function get_ases_users_by_mdl_username_prefix($mdl_username) {
    global $DB;
    $sql = "
    select mdl_user.username ,mdl_user.firstname, mdl_user.lastname from {user}, {talentospilos_user_extended} tp_user_ext, {talentospilos_usuario} tp_user  
    where  mdl_user.id = tp_user_ext.id_moodle_user and tp_user.id = tp_user_ext.id_ases_user
    
    and mdl_user.firstname in (select  mdl_user_.firstname
    from {user} mdl_user_ where mdl_user_.username = '$mdl_username')
    
    and mdl_user.lastname in (select  mdl_user_.lastname
    from  {user} mdl_user_ where mdl_user_.username = '$mdl_username')";
    $ases_users = $DB->get_records_sql($sql);
    return $ases_users;
   
}


/**
 * Función que recupera los campos de usuario de la tabla {talentospilos_usuario}
 * Gets all fields from user on {talentospilos_usuario} table
 *
 * @see get_ases_user($id)
 * @param $id_student --> student id on {talentospilos_usuario} table
 * @return array --> with every field
 */
function get_ases_user($id)
{

    global $DB;

    $sql_query = "SELECT num_doc, tipo_doc, (now() - fecha_nac)/365 AS age, estado, estado_ases, direccion_res, tel_ini, tel_res, celular, emailpilos, acudiente, tel_acudiente, estado_ases, observacion  FROM {talentospilos_usuario} WHERE id = $id";
    $user = $DB->get_record_sql($sql_query);

    return $user;
}
/**
 * //THIS GOTTA CHANGE TO THE NEW MODEL

 * Gets moodle user id (moodle table) given user id from {talentospilos_usuario}
 *
 * @see get_id_user_moodle($id_student)
 * @param $id_student --> user id from {talentospilos_usuario}
 * @return string with the moodle id
 */
function get_id_user_moodle($id_student)
{

    global $DB;

    $sql_query = "SELECT id_moodle_user FROM {talentospilos_user_extended} WHERE id_ases_user = $id_student AND tracking_status = 1";
    $id_user_moodle = $DB->get_record_sql($sql_query)->id_moodle_user;
    //USANDO MODELO ANTIGUO
    // $sql_query = "SELECT id FROM {user_info_field} WHERE shortname = 'idtalentos'";
    // $id_field = $DB->get_record_sql($sql_query)->id;

    // $sql_query = "SELECT MAX(userid) AS userid FROM {user_info_data} WHERE fieldid = $id_field AND data = '$id_student'";

    // $id_user_moodle = $DB->get_record_sql($sql_query)->userid;

    return $id_user_moodle;
}




/**
 * Gets ASES user given student id associated to moodle user name
 *
 * @see get_ases_user_by_code($code)
 * @param $username --> student id associated to moodle user
 * @return object containing the ASES student information
 */
function get_ases_user_by_code($code)
{

    global $DB;

    $sql_query = "SELECT MAX(id) as id_moodle FROM {user} WHERE username LIKE '" . $code . "%';";

    $id_moodle_result = $DB->get_record_sql($sql_query);

    if ($id_moodle_result) {
        $id_moodle = $id_moodle_result->id_moodle;
    } else {
        return false;
    }

    if( !$id_moodle ){
        return false;
    };

    $add_fields = get_adds_fields_mi($id_moodle);
    
    if($add_fields){
        $id_ases = $add_fields->idtalentos;
    }else{
        return false;
    }

    $sql_query = "SELECT *, (now() - fecha_nac)/365 AS age FROM {talentospilos_usuario} WHERE id =" . $id_ases;

    $ases_user = $DB->get_record_sql($sql_query);

    return $ases_user;
}

/**
 * Gets ASES student status
 *
 * @see get_student_ases_status($id)
 * @param $id --> user id on talentospilos_usuario table
 * @return object --> with ASES student information
 */

function get_student_ases_status($id_student)
{
    global $DB;

    $sql_query = "SELECT MAX(id) FROM {talentospilos_est_estadoases} WHERE id_estudiante = $id_student";
    $id_ases_status = $DB->get_record_sql($sql_query)->max;

    $sql_query = "SELECT * FROM {talentospilos_est_estadoases} WHERE id = $id_ases_status";
    $id_status = $DB->get_record_sql($sql_query)->id_estado_ases;

    if ($id_ases_status) {
        $sql_query = "SELECT * FROM {talentospilos_estados_ases} WHERE  id = $id_status";
        $status_ases = $DB->get_record_sql($sql_query);
    } else {
        $status_ases = "NO REGISTRA";
    }

    return $status_ases;
}

/**
 * Gets student ICETEX status
 *
 * @see get_student_icetex_status($id_student)
 * @param $id_student --> student id on talentospilos_usuario table
 * @return array --> with ICETEX student information
 */

function get_student_icetex_status($id_student)
{
    global $DB;

    $sql_query = "SELECT MAX(id) FROM {talentospilos_est_est_icetex} WHERE id_estudiante = $id_student";
    $id_icetex_status = $DB->get_record_sql($sql_query)->max;

    $sql_query = "SELECT * FROM {talentospilos_est_est_icetex} WHERE id = $id_icetex_status";
    $id_status = $DB->get_record_sql($sql_query)->id_estado_icetex;

    if ($id_icetex_status) {
        $sql_query = "SELECT * FROM {talentospilos_estados_icetex} WHERE  id = $id_status";
        $status_icetex = $DB->get_record_sql($sql_query);
    } else {
        $status_icetex = "NO REGISTRA";
    }

    return $status_icetex;
}

/**
 * Gets student information from {talentospilos_user_extended} table given his id
 *
 * @see get_adds_fields_mi($id_student)
 * @param $id_student --> student id
 * @return object --> object representing moodle user
 */

function get_adds_fields_mi($id_student)
{

    global $DB;

    $sql_query = "SELECT * FROM {talentospilos_user_extended} WHERE id_moodle_user = $id_student";

    $result = $DB->get_record_sql($sql_query);
    if ($result) {
        $array_result = new stdClass();
        $array_result->idtalentos = $result->id_ases_user;
        $array_result->idprograma = $result->id_academic_program;
        $array_result->estado = $result->program_status;
    } else {
        $array_result = false;
    }

    return $array_result;
}

/**
 * Obtains academic program data given the program id
 *
 * @see get_program($id_program)
 * @param $id --> program id
 * @return object representing all the academic program information
 */
function get_program($id)
{

    global $DB;

    $program = $DB->get_record_sql("SELECT * FROM  {talentospilos_programa} WHERE id=" . $id . ";");

    return $program;
}

/**
 * Obtains faculty information given its id
 *
 * @see get_faculty($id)
 * @param $$id --> faculty id
 * @return object representing all faculty information
 */
function get_faculty($id)
{

    global $DB;

    $sql_query = "SELECT * FROM {talentospilos_facultad} WHERE id=" . $id;
    $result = $DB->get_record_sql($sql_query);

    return $result;
}

/**
 * Gets student cohort
 *
 * @see get_cohort_by_student($id_student)
 * @param $id_student --> student id
 * @return object Representing the cohort
 */
function get_cohort_student($id_student)
{

    global $DB;

    $sql_query = "SELECT MAX(id) AS id FROM {cohort_members} WHERE userid = $id_student;";
    $id_cohort_member = $DB->get_record_sql($sql_query)->id;

    $sql_query = "SELECT cohortid FROM {cohort_members} WHERE id = $id_cohort_member";
    $id_cohort = $DB->get_record_sql($sql_query)->cohortid;

    $sql_query = "SELECT name, idnumber FROM {cohort} WHERE id = $id_cohort;";
    $cohort = $DB->get_record_sql($sql_query);

    return $cohort;
}

/**
 * Gets student cohorts.
 *
 * If the student does not have cohorts return false
 *
 * @see get_cohorts_by_student($id_student)
 * @param $id_moodle_student --> ID moodle 
 * @return array|false
 */
function get_cohorts_by_student($id_moodle_student){

    global $DB;


    $sql_query = <<<SQL
                  SELECT cohorts.*
                  FROM {cohort_members} AS members 
                    INNER JOIN {cohort} AS cohorts ON members.cohortid = cohorts.id
                  WHERE userid = $id_moodle_student
SQL;
    
    $result_query = $DB->get_records_sql($sql_query);
    $result_to_return = array_values($result_query);
    if(count($result_query)<=0) {
        return false;
    }
    return $result_to_return;
}

/**
 * Obtains name, lastname and email from a monitor assigned to a student, given the student id
 *
 * @see get_assigned_monitor($id_student)
 * @param $id_student --> student id on {talentospilos_usuario} table
 * @param $instance_id --> instance id on {talentospilos_semestre} table
 * @return array Containing the information
 */
function get_assigned_monitor($id_student, $instance_id)
{

    global $DB;

    if (is_numeric($instance_id) && is_numeric($id_student)) {
         
        $object_current_semester = core_periods_get_current_period($instance_id);

        $sql_query = "SELECT id_monitor 
                      FROM {talentospilos_monitor_estud} 
                      WHERE id_estudiante = ".$id_student." AND id_semestre = ".$object_current_semester->id.";";

        $result = $DB->get_record_sql($sql_query);    
        
        $monitor = $DB->get_record_sql($sql_query);
        $id_monitor = -1;
        if($monitor){
            $id_monitor = $monitor->id_monitor;
        }else{
            return array();
        }

        if ($id_monitor) {

            $sql_query = "SELECT id, firstname, lastname, email 
                          FROM {user} 
                          WHERE id = ".$id_monitor;

            $monitor_object = $DB->get_record_sql($sql_query);

        } else {
            $monitor_object = array();
        }
        return $monitor_object;
    } else {
        Throw new Exception(
            'Invalid argument(s) instance:'. $instance_id . ' or id_student:' . $id_student
        );
    }


}

/**
 * Obtains name, lastname and email from a practicant (practicante) assigned to a student, given the student id
 *
 * @see get_assigned_pract($id_student)
 * @param $id_student --> student id on {talentospilos_usuario} table
 * @param $instance_id --> instance id
 * @return array Containing the information
 */
function get_assigned_pract($id_student, $instance_id)
{

    global $DB;

    if (is_numeric($id_student) && is_numeric($instance_id)) {
            
        $object_current_semester = core_periods_get_current_period($instance_id);

        $sql_query = "SELECT id_monitor FROM {talentospilos_monitor_estud} WHERE id_estudiante =" . $id_student . " AND id_semestre = " . $object_current_semester->id . ";";
        $monitor = $DB->get_record_sql($sql_query);

        if ($monitor) {
            $sql_query = "SELECT id_jefe FROM {talentospilos_user_rol} WHERE id_usuario = " . $monitor->id_monitor . " AND id_semestre = " . $object_current_semester->id . ";";
            $trainee = $DB->get_record_sql($sql_query);

            if ($trainee) {
                $sql_query = "SELECT id, firstname, lastname, email FROM {user} WHERE id = " . $trainee->id_jefe;
                $trainee_object = $DB->get_record_sql($sql_query);
            } else {
                $trainee_object = array();
            }
        } else {
            $trainee_object = array();
        }

        return $trainee_object;
    } else {
        Throw new exception('Invalid id_student and/or instance_id');
    }
}

/**
 * Return the student programs by ases user id
 * @param $id_ases_user
 * @return array
 * @throws dml_exception
 */
function get_student_programs_by_ases_user_id($id_ases_user) {
    global $DB;
    $sql = <<<SQL
      SELECT DISTINCT  mdl_talentospilos_programa.* 
      FROM {talentospilos_programa} mdl_talentospilos_programa
      INNER JOIN {talentospilos_user_extended} mdl_talentospilos_user_extended
      on mdl_talentospilos_user_extended.id_academic_program = mdl_talentospilos_programa.id
      INNER JOIN {talentospilos_usuario} mdl_talentospilos_usuario
      ON mdl_talentospilos_user_extended.id_ases_user = mdl_talentospilos_usuario.id
      WHERE mdl_talentospilos_usuario.id = ?
SQL;
    return array_values($DB->get_records_sql($sql, array($id_ases_user)));
}
/**
 * Return the student programs by ases user id.
 * Only return the programs where tracking status are 1
 * @param $id_ases_user
 * @return array
 * @throws dml_exception
 */
function get_student_active_programs_by_ases_user_id($id_ases_user) {
    global $DB;
    $tracking_status_active = TrackingStatus::ACTIVE;
    $sql = <<<SQL
      SELECT DISTINCT  mdl_talentospilos_programa.* 
      FROM {talentospilos_programa} mdl_talentospilos_programa
      INNER JOIN {talentospilos_user_extended} mdl_talentospilos_user_extended
      on mdl_talentospilos_user_extended.id_academic_program = mdl_talentospilos_programa.id
      INNER JOIN {talentospilos_usuario} mdl_talentospilos_usuario
      ON mdl_talentospilos_user_extended.id_ases_user = mdl_talentospilos_usuario.id
      WHERE mdl_talentospilos_usuario.id = ?
      AND mdl_talentospilos_user_extended.tracking_status = ?
SQL;
    return array_values($DB->get_records_sql($sql, array($id_ases_user, $tracking_status_active)));
}


/**
 * Obtains name, lastname and email from a professional (profesional) assigned to a student, given the student id
 *
 * @see get_assigned_professional($id_student)
 * @param $id_student --> student id on {talentospilos_usuario} table
 * @param $instance_id --> instance id on {talentospilos_semestre} table
 * @return array Containing the information
 */
function get_assigned_professional($id_student, $instance_id)
{

    global $DB;

    $object_current_semester = core_periods_get_current_period($instance_id);

    $sql_query = "SELECT id_monitor FROM {talentospilos_monitor_estud} WHERE id_estudiante =" . $id_student . " AND id_semestre = " . $object_current_semester->id . ";";
    $id_monitor = $DB->get_record_sql($sql_query);

    if ($id_monitor) {

        $sql_query = "SELECT id_jefe
                      FROM {talentospilos_user_rol}
                      WHERE id_usuario = $id_monitor->id_monitor AND id_semestre = $object_current_semester->id";

        $id_trainee = $DB->get_record_sql($sql_query)->id_jefe;

        if ($id_trainee) {

            $sql_query = "SELECT id_jefe
                          FROM {talentospilos_user_rol}
                          WHERE id_usuario = $id_trainee AND id_semestre = $object_current_semester->id;";

            $id_professional = $DB->get_record_sql($sql_query)->id_jefe;

            if ($id_professional) {
                $sql_query = "SELECT id, firstname, lastname, email
                              FROM {user} WHERE id = $id_professional ;";
                $professional_object = $DB->get_record_sql($sql_query);
                //$tmp = (array) $professional_object;
                //print_r($professional_object);
                if (!isset($professional_object->firstname)) {
                    $professional_object = array();
                }
            } else {
                $professional_object = array();
            }
        } else {
            $professional_object = array();
        }
    } else {
        $professional_object = array();
    }

    return $professional_object;
}

/**
 * Gets an array with all students risks given user id on {talentospilos_usuario} table
 *
 * @see get_risk_by_student($id_student)
 * @param $id_student --> student id on {talentospilos_usuario} table
 * @return array Containing the information
 */

function get_risk_by_student($id_student)
{

    global $DB;

    $sql_query = "SELECT riesgo.nombre, r_usuario.calificacion_riesgo
                  FROM {talentospilos_riesg_usuario} AS r_usuario INNER JOIN {talentospilos_riesgos_ases} AS riesgo ON r_usuario.id_riesgo = riesgo.id
                  WHERE r_usuario.id_usuario = $id_student AND riesgo.nombre <> 'geografico'";

    $array_risk = $DB->get_records_sql($sql_query);

    return $array_risk;
}
/**
 * Gets a moodle user object given his code
 *
 * @see get_user($code)
 * @param $code --> student username on {user} table
 * @return object representing the user
 */
function get_user($code){
    global $DB;
    $sql_query = "SELECT * FROM {user} WHERE username LIKE '" . $code . "%';";
    $user = $DB->get_record_sql($sql_query);

    return $user;
}
/**
 * Gets a moodle user object given his id
 *
 * @see get_full_user($id)
 * @param $id --> student id on {user} table
 * @return object representing the user
 */

function get_full_user($id)
{
    global $DB;

    //TO DO: $id sometimes reaches this point as empty
    if($id != null) {
        $sql_query = "SELECT * FROM {user} WHERE id= " . $id;
        $user = $DB->get_record_sql($sql_query);
    }else return 1;

    return $user;
}

/**
 * Returns the academic programs associated with a student
 *
 * @see get_academic_program($id_ases_user)
 * @param $id_ases_user --> student id on {talentospilos_usuario} table 
 * @return array 
 */
function get_academic_programs_by_student($id_ases_user){

    global $DB;

    $result_to_return = new stdClass();
    $array_result = array();

    $sql_query = "SELECT user_extended.id_moodle_user, 
                         academic_program.id AS academic_program_id, 
                         academic_program.cod_univalle, 
                         academic_program.nombre AS nombre_programa, 
                         academic_program.jornada, 
                         faculty.nombre AS nombre_facultad,
                         user_extended.program_status, 
                         user_extended.tracking_status
                  FROM {talentospilos_user_extended} AS user_extended
                       INNER JOIN {talentospilos_programa} AS academic_program ON user_extended.id_academic_program = academic_program.id
                       INNER JOIN {talentospilos_facultad} AS faculty ON academic_program.id_facultad = faculty.id
                  WHERE id_ases_user = $id_ases_user";
    
    $array_result_query = $DB->get_records_sql($sql_query);

    foreach($array_result_query as $result){
        array_push($array_result, $result);
        
    }

    return $array_result;
}

/**
 * Update the academic program status of a student
 *
 * @see update_status_program($program_id, $status, $student_id)
 * @param $program_id --> Academic program id
 * @param $status --> New status for an academic program
 * @param $student_id --> Student id in Moodle table
 * @return array 
 */

function update_status_program($program_id, $status, $student_id){

    global $DB;

    $sql_query = "SELECT id 
                  FROM {talentospilos_user_extended} 
                  WHERE id_academic_program = $program_id AND id_moodle_user = $student_id";

    $id_register = $DB->get_record_sql($sql_query)->id;

    $object_updatable = new stdClass();
    $object_updatable->id = $id_register;
    $object_updatable->program_status = $status;
    if($object_updatable->id == 0){
        trigger_error('ASES Notificacion: actualizar academic program status en la BD con id 0');
        $result = false;
    }else{
    $result = $DB->update_record('talentospilos_user_extended', $object_updatable);
    }

    if($result){
        return array(
            "status_code" => 0,
            "title" => 'Éxito',
            "type" => 'success',
            "message" => 'Estado del programa actualizado con éxito.'
        );
    }else{
        return array(
            "status_code" => 0,
            "title" => 'Error',
            "type" => 'error',
            "message" => 'Error al guardar estado en la base de datos.'
        );
    }
}

/* Obtains name, lastname and email from a monitor assigned to a student, given the student id
 *
 * @see get_student_monitor($id_ases_user,$id_semester,$id_instance)
 * @param $id_student --> student id on {talentospilos_usuario} table
 * @return array Containing the information
 */
function get_student_monitor($id_ases_user, $id_semester, $id_instance)
{

    global $DB;

    if (is_null($id_ases_user)) {
        Throw New Exception('Empty id supplied as student id in function get_student_monitor');
    }

    $sql_query = "SELECT id_monitor FROM {talentospilos_monitor_estud} WHERE id_estudiante =$id_ases_user AND  id_instancia=$id_instance AND id_semestre =$id_semester";
    $id_monitor = $DB->get_record_sql($sql_query)->id_monitor;

    return $id_monitor;
}

/**
 * Function that return the history of risk levels of a student.
 * @param int Student ases code
 * @return array list of stdClass with the end risk level of a set of semesters.
 */
function student_lib_get_full_risk_status( $ases_id ){

    $NUMBER_OF_DIMENSIONS = 5;

    $xQuery = new stdClass();
    $xQuery->form = "seguimiento_pares";
    $xQuery->filterFields = [ 
                                ["id_estudiante",[ [ $ases_id,"=" ] ], false],
                                ["fecha",[ ["%%","LIKE"] ] , false],
                                ["puntuacion_riesgo_individual",[ ["%%","LIKE"] ] , false],
                                ["puntuacion_riesgo_familiar",[ ["%%","LIKE"] ] , false],
                                ["puntuacion_riesgo_academico",[ ["%%","LIKE"] ] , false],
                                ["puntuacion_riesgo_economico",[ ["%%","LIKE"] ] , false],
                                ["puntuacion_vida_uni",[ ["%%","LIKE"] ] , false]
                            ];
    $xQuery->orderFields = [ ["fecha","DESC"] ];
    $xQuery->orderByDatabaseRecordDate = false;
    $xQuery->recordStatus = [ "!deleted" ];
    $xQuery->selectedFields = [ ]; 

    //Trackings of a student
    $records = dphpformsV2_find_records( $xQuery );
    
    $first_full_status_risk = [
        "individual" => -1,
        "familiar" => -1,
        "academico" => -1,
        "economico" => -1,
        "vida_uni" => -1
    ];

    /**
     * Function that returns the semester that in its interval contains a given time.
     * @author Jeison Cardona Gomez <jeison.cardona@correounivalle.edu.co>
     * @param time
     * @return array Semester information
     */
    $get_semester = function( $_date ){

        $semesters = core_periods_get_all_periods(); 

        foreach ($semesters as $key => $semester) {

            $start_date_semester  = strtotime( $semester->fecha_inicio );
            $end_date_semester  = strtotime( $semester->fecha_fin );
            
            if( ( $_date >= $start_date_semester ) && ( $_date <= $end_date_semester ) ){
                return [
                    "id" => $semester->id,
                    "name" => $semester->nombre,
                    "start_time" => $start_date_semester,
                    "end_time" => $end_date_semester,
                    "start_date" => $semester->fecha_inicio,
                    "end_date" => $semester->fecha_fin
                ];
            }
        }
    };

    /**
     * Function that return the next semesters to the semester that in its interval contains
     * a given time.
     * @author Jeison Cardona Gomez <jeison.cardona@correounivalle.edu.co>
     * @param time
     * @return array Array of next semesters information
     */
    $get_next_semesters = function( $_date ){

        $semesters = core_periods_get_all_periods();
        $to_return = [];

        foreach ($semesters as $key => $semester) {
            $start_date_semester  = strtotime( $semester->fecha_inicio );
            $end_date_semester  = strtotime( $semester->fecha_fin );
            
            if( $_date < $start_date_semester ){
                array_push( 
                    $to_return,
                    [
                        "id" => $semester->id,
                        "name" => $semester->nombre,
                        "start_time" => $start_date_semester,
                        "end_time" => $end_date_semester,
                        "start_date" => $semester->fecha_inicio,
                        "end_date" => $semester->fecha_fin
                    ]
                );
            }
        }

        return $to_return;
    };

    $get_risk_value = function( $risk_value ){
        if( $risk_value !== "-#$%-" ){
            return $risk_value;
        }else{
            return -1;
        }
    };

    if( $records ){

        /* count( $records ) - 1 
         * It is used because the records are ordered in a DESC way, then the last record is the
         * first one respect to the date.
         * */
        $first_semester = $get_semester( strtotime( $records[count( $records ) - 1]["fecha"] ) );

        $first_full_status_risk["individual"] = $get_risk_value( $records[count( $records ) - 1]["puntuacion_riesgo_individual"] );
        $first_full_status_risk["familiar"] = $get_risk_value( $records[count( $records ) - 1]["puntuacion_riesgo_familiar"] );
        $first_full_status_risk["academico"] = $get_risk_value( $records[count( $records ) - 1]["puntuacion_riesgo_academico"] );
        $first_full_status_risk["economico"] = $get_risk_value( $records[count( $records ) - 1]["puntuacion_riesgo_economico"] );
        $first_full_status_risk["vida_uni"] = $get_risk_value( $records[count( $records ) - 1]["puntuacion_vida_uni"] );
        $first_full_status_risk["semester_info"] = $first_semester;

        $next_semesters = $get_next_semesters( strtotime( $records[count( $records ) - 1]["fecha"] ) );
        array_push( $next_semesters, $first_semester );
        
        $other_semesters = [];
        $items = null;
        foreach ($next_semesters as $key => $row) {
            $items[$key]  = $row["id"];
        }
        
        array_multisort($items, SORT_DESC, $next_semesters);

        foreach( $next_semesters as $semester_key => $semester ){
            
            $checked = 0;
            $full_status_risk = [
                "individual" => -1,
                "familiar" => -1,
                "academico" => -1,
                "economico" => -1,
                "vida_uni" => -1
            ];
            $full_status_risk["semester_info"] = $semester;

            foreach( $records as $record_key => $record ){
                
                
                if( ( strtotime( $record["fecha"] ) >= $semester["start_time"] ) && 
                    ( strtotime( $record["fecha"] ) <= $semester["end_time"] )
                ){  
                    
                    if( ( $full_status_risk["individual"] === -1 ) && ( $record["puntuacion_riesgo_individual"] !== "-#$%-" ) ){
                        $full_status_risk["individual"] = $record["puntuacion_riesgo_individual"];
                        $checked++;
                    }
                    if( ( $full_status_risk["familiar"] === -1 ) && ( $record["puntuacion_riesgo_familiar"] !== "-#$%-" ) ){
                        $full_status_risk["familiar"] = $record["puntuacion_riesgo_familiar"];
                        $checked++;
                    }
                    if( ( $full_status_risk["academico"] === -1 ) && ( $record["puntuacion_riesgo_academico"] !== "-#$%-" ) ){
                        $full_status_risk["academico"] = $record["puntuacion_riesgo_academico"];
                        $checked++;
                    }
                    if( ( $full_status_risk["economico"] === -1 ) && ( $record["puntuacion_riesgo_economico"] !== "-#$%-" ) ){
                        $full_status_risk["economico"] = $record["puntuacion_riesgo_economico"];
                        $checked++;
                    }
                    if( ( $full_status_risk["vida_uni"] === -1 ) && ( $record["puntuacion_vida_uni"] !== "-#$%-" ) ){
                        $full_status_risk["vida_uni"] = $record["puntuacion_vida_uni"];
                        $checked++;
                    }
            
                    if( $checked == $NUMBER_OF_DIMENSIONS ){
                        break;
                    }
                }

            }

            array_push( $other_semesters, $full_status_risk );

        }

        $other_semesters = array_reverse( $other_semesters );

        return [
            "start_risk_lvl_fist_semester" => $first_full_status_risk,
            "end_risk_lvl_semesters" => $other_semesters
        ];

    }else{
        return null;
    }

};
