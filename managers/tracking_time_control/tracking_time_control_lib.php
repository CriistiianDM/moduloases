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

require_once dirname(__FILE__) . '/../../../../config.php';
require_once $CFG->dirroot . '/grade/querylib.php';
require_once $CFG->dirroot . '/grade/report/user/lib.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once('tracking_time_control_functions.php');


/**
 * Function that gets object of {user} with id_moodle
 * @see get_info_monitor($id_moodle)
 * @return object 
 */

function get_info_monitor($id_moodle){
      global $DB;

    $sql_query = "select * from {user} where id='$id_moodle'";
    $info_monitor = $DB->get_record_sql($sql_query);
    return $info_monitor;
}

/**
 * Function that obtains the initial and final hours in which a monitor was monitored in a * time interval
 * @see get_report_by_date()
 * @return array 
 */
function get_report_by_date($initial_date, $final_date){

    global $DB;

        $sql_query = "SELECT seg.id,id_monitor,fecha,hora_ini,hora_fin
    FROM {user}  usuario INNER JOIN {talentospilos_seguimiento}  seg ON usuario.id = seg.id_monitor where fecha<=$final_date and fecha>=$initial_date order by fecha asc";
    return $DB->get_records_sql($sql_query);
}


/**
 * Function that adds a button to see details of hours worked
 * @see get_hours_per_days($init,$final)
 * @return array 
 */
function get_hours_per_days($init,$final)
{
    global $DB;
    //
    $register =new stdClass();
	$register->hours=0;
	$register->minutes=0;
	$register->total_minutes=0;

	$final_array=[];
    $initial_hours_array = get_report_by_date($init,$final);
    $first_date;
    date_default_timezone_set("America/Bogota");


    foreach ($initial_hours_array as $date) {

    	 //Check if it is the first cycle
         //*if it is the first cycle it assigns the first date of the array to the variable *first_date

    	$first=$date->fecha;

    	//Get the start and end of the day in unix format
    	$init_day = date("Y-m-d", $first);
		$init_day=strtotime($init_day);

    	$final_day = date_create(date('Y-m-d',$first));
		$final_day=strtotime(date_time_set($final_day, 23, 59,59)->format('Y-m-d H:i:s'));


        if ($date === reset($initial_hours_array)) {
            $first_date=$date->fecha;
        }

        //if $first_date is different than $first, all the time calculation variables are reset.
        if(!($first_date>=$init_day&&$first_date<=$final_day)){
       // if($first_date!=$first){
        $register->fecha=date('d-m-Y', $first_date);

        $register->total_minutes+=$register->minutes;
        $register->minutes=0;
        if($register->hours>0){
           $register->total="".$register->hours." Horas y ".$register->total_minutes." Minutos.";
        }else{
           $register->total=$register->total_minutes." Minutos."; 
        }
        array_push($final_array,$register);        
        $register =new stdClass();
        $register->hours=0;
		$register->minutes=0;
		$register->total_minutes=0;
		$register->fecha=$first;
		$first_date=$first;
    	}

        $calculated_time=calculate_hours($date);
        if(isset($calculated_time->hours)){
           $register->hours+=$calculated_time->hours;
          if(isset($calculated_time->minutes)){
          	 $register->minutes+=$calculated_time->minutes;
          }
        }else{
          $register->total_minutes+=$calculated_time->total_minutes;
        }

        if ($date === end($initial_hours_array)) {
        $register->fecha=date('d-m-Y', $first_date);

        $register->total_minutes+=$register->minutes;
        $register->minutes=0;
        if($register->hours>0){
           $register->total="".$register->hours." Horas y ".$register->total_minutes." Minutos.";
        }else{
           $register->total=$register->total_minutes." Minutos."; 
        }

        array_push($final_array,$register);
    }


    }
    

    return $final_array;
}