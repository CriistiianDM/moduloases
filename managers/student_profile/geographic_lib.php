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
 * Ases block
 *
 * @author     Iader E. García Gómez
 * @package    block_ases
 * @copyright  2018 Iader E. García <iadergg@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 
/**
 * Gets geographic information of a student, given his ID
 *
 * @see get_geographic_info($id_ases)
 * @param $id_ases --> user id from {talentospilos_ases} table
 * @return array containing residence coordinates and geographic risk qualification
 */
 
function get_geographic_info($id_ases){
    global $DB;
    $sql_query = "SELECT id_usuario AS id_user, latitud AS latitude, longitud AS longitude, barrio AS neighborhood,
                  vive_lejos AS live_far_away , vive_zona_riesgo AS live_risk_zone, nativo AS native,
                  nivel_riesgo AS risk_level, observaciones AS observations
                  FROM {talentospilos_demografia} AS demographic_t
                  WHERE demographic_t.id_usuario=".$id_ases;
                  
    $result = $DB->get_record_sql($sql_query);
    
    if(!$result) return false;
    
    $sql_query = "SELECT calificacion_riesgo AS risk FROM {talentospilos_riesgos_ases} AS ases_risk 
                                                     INNER JOIN {talentospilos_riesg_usuario} AS user_risk ON user_risk.id_riesgo = ases_risk.id 
                  WHERE ases_risk.nombre = 'geografico' AND user_risk.id_usuario =".$id_ases;
                  
    $risk_grade_object =  $DB->get_record_sql($sql_query);
    
    if($risk_grade_object){
        $result->risk = $risk_grade_object->risk;
    }
    else{
        $result->risk = 0;
    }
    
    return $result;
}

/**
 * Obtains all neighborhoods from {talentospilos_barrios} table
 *
 * @see get_neighborhoods()
 * @return array
 */

function get_neighborhoods(){
    
    global $DB;

    $sql_query = "SELECT * FROM {talentospilos_barrios}";

    $array_neighborhoods = $DB->get_records_sql($sql_query);

    return $array_neighborhoods;
    
}

/**
 * Función que carga la información geográfica de un estudiante ASES 
 * @desc Load geographic information of an ASES student
 * @see student_profile_load_geographic_info($id_ases)
 * @param $id_ases --> ASES student id
 * @return object representing the user
 */

function student_profile_load_geographic_info($id_ases){
    
    global $DB;

    $sql_query = "SELECT * FROM {talentospilos_demografia} WHERE id_usuario = $id_ases";
    $result = $DB->get_record_sql($sql_query);

    return $result;
}

/**
 * Saves geographic information of an ASES student 
 *
 * @see student_profile_save_geographic_info($id_ases, $latitude, $longitude, $neighborhood, $duration, $distance, $address, $city, $observaciones, $vive_lejos, $vive_zona_riesgo, $nativo, $nivel_riesgo)
 * @param $id_ases --> ASES student id
 * @param $latitude --> Latitude
 * @param $longitude --> longitude
 * @param $neighborhood --> neighborhood id
 * @param $duration --> duration of the route from the student's residence to Univalle
 * @param $distance --> distance of the route from the student's residence to Univalle
 * @param $address --> student's residence address
 * @param $city --> student's residence city
 * @param $observaciones --> Geographic tracing observations
 * @param $vive_lejos --> longitude
 * @param $vive_zona_riesgo --> neighborhood id
 * @param $nativo --> Student's origin (-1 if is not defined)
 * @param $nivel_riesgo --> geographic risk level (-1 if is not defined)
 * @return integer --> 1 if everything were saved, 0 otherwise
 */

function student_profile_save_geographic_info($id_ases, $latitude, $longitude, $neighborhood, $duration, $distance, $address, $city, $observaciones, $vive_lejos, $vive_zona_riesgo, $nativo, $nivel_riesgo){

    global $DB;

    $sql_query = "SELECT * FROM {talentospilos_demografia} WHERE id_usuario = $id_ases";
    $geographic_info = $DB->get_record_sql($sql_query)->id;

    $sql_query = "SELECT id FROM {talentospilos_riesgos_ases} WHERE nombre = 'geografico'";
    $id_risk = $DB->get_record_sql($sql_query)->id;

    $sql_query = "SELECT id FROM {talentospilos_riesg_usuario} WHERE id_usuario = $id_ases AND id_riesgo = $id_risk";
    $id_register_risk = $DB->get_record_sql($sql_query)->id;


    if($id_register_risk){
        $data_object_risk = new stdClass();
        $data_object_risk->id = (int)$id_register_risk;
        $data_object_risk->id_usuario = (int)$id_ases;
        $data_object_risk->id_riesgo = (int)$id_risk;
        $data_object_risk->calificacion_riesgo = (int)$nivel_riesgo;
        $data_object_risk->recorder = "other";

        $result_geographic_risk = $DB->update_record('talentospilos_riesg_usuario', $data_object_risk);
    }
    else{
        $data_object_risk = new stdClass();
        $data_object_risk->id = (int)$id_register_risk;
        $data_object_risk->id_usuario = (int)$id_ases;
        $data_object_risk->id_riesgo = (int)$id_risk;
        $data_object_risk->calificacion_riesgo = (int)$nivel_riesgo;
        $data_object_risk->recorder = "other";

        $result_geographic_risk = $DB->insert_record('talentospilos_riesg_usuario', $data_object_risk, true);
    }

    if($geographic_info){
        $data_object = new stdClass();
        $data_object->id = $geographic_info->id;
        $data_object->id_usuario = (isset($id_ases)?$id_ases:$geographic_info->id_usuario);
        $data_object->latitud = (isset($latitude)?$latitude:$geographic_info->latitud);
        $data_object->longitud = (isset($longitude)?$longitude:$geographic_info->longitud);
        $data_object->barrio = (isset($neighborhood)?$neighborhood:$geographic_info->barrio);
        $data_object->duracion = (isset($duration)?$duration:$geographic_info->duracion);
        $data_object->distancia = (isset($distance)?$distance:$geographic_info->distancia);
        $data_object->direccion = (isset($address)?$address:$geographic_info->direccion);
        $data_object->id_ciudad = (isset($city)?$city:$geographic_info->id_ciudad);
        $data_object->observaciones = (isset($observaciones)?$observaciones:$geographic_info->observaciones);
        $data_object->vive_lejos = (isset($vive_lejos)?$vive_lejos:$geographic_info->vive_lejos);
        $data_object->vive_zona_riesgo = (isset($vive_zona_riesgo)?$vive_zona_riesgo:$geographic_info->vive_zona_riesgo);
        $data_object->nativo = (isset($nativo)?$nativo:$geographic_info->nativo);
        $data_object->nivel_riesgo = (isset($nivel_riesgo)?$nivel_riesgo:$geographic_info->nivel_riesgo);
    
        $result_geographic_info = $DB->update_record('talentospilos_demografia', $data_object);
    }
    else{
        $data_object = new stdClass();
        $data_object->id_usuario = $id_ases;
        $data_object->latitud = $latitude;
        $data_object->longitud = $longitude;
        $data_object->barrio = $neighborhood;
        $data_object->duracion = $duration;
        $data_object->distancia = $distance;
        $data_object->direccion = $address;
        $data_object->id_ciudad = $city;
        $data_object->observaciones = $observaciones;
        $data_object->vive_lejos = $vive_lejos;
        $data_object->vive_zona_riesgo = $vive_zona_riesgo;
        $data_object->nativo = $nativo;
        $data_object->nivel_riesgo = $nivel_riesgo;

        $result_geographic_info = $DB->insert_record('talentospilos_demografia', $data_object, true);
    }

    if($result_geographic_info && $result_geographic_risk){
        return 1;
    }
    else{
        return 0;
    }    
}