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
 * @author     Jorge Eduardo Mayor
 * @package    block_ases
 * @copyright  2018 Iader E. García <iadergg@gmail.com>
 * @copyright  2019 Jorge Eduardo Mayor <mayor.jorge@correounivalle.edu.co>
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
                  id_ciudad AS id_city, direccion AS res_address, vive_lejos AS live_far_away , vive_zona_riesgo AS live_risk_zone, nativo AS native,
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
 * @desc Gets the latitude and longitude from {talentospilos_demografia} table
 * @see student_profile_get_coordenates()
 * @param $id_ases --> ASES student id
 * @return array
 */
function student_profile_get_coordenates($id_ases){

    global $DB;

    $sql_query = "SELECT longitud AS longitude, latitud AS latitude FROM {talentospilos_demografia} WHERE id_usuario = ".$id_ases;

    $array_coordenates = $DB->get_record_sql($sql_query);

    return $array_coordenates;

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

function student_profile_save_geographic_info($id_ases, $latitude, $longitude, $neighborhood, $duration, $distance, $address, $city, $observaciones = null, $vive_lejos = null, $vive_zona_riesgo = null, $nativo = null, $nivel_riesgo = null){

    global $DB;

    $sql_query = "SELECT * FROM {talentospilos_demografia} WHERE id_usuario = $id_ases";
    $geographic_info = $DB->get_record_sql($sql_query);

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
        $geographic_info->id_usuario = (isset($id_ases)?$id_ases:$geographic_info->id_usuario);
        $geographic_info->latitud = (isset($latitude)?$latitude:$geographic_info->latitud);
        $geographic_info->longitud = (isset($longitude)?$longitude:$geographic_info->longitud);
        $geographic_info->barrio = (isset($neighborhood)?$neighborhood:$geographic_info->barrio);
        $geographic_info->duracion = (isset($duration)?$duration:$geographic_info->duracion);
        $geographic_info->distancia = (isset($distance)?$distance:$geographic_info->distancia);
        $geographic_info->direccion = (isset($address)?$address:$geographic_info->direccion);
        $geographic_info->id_ciudad = (isset($city)?$city:$geographic_info->id_ciudad);
        $geographic_info->observaciones = (isset($observaciones)?$observaciones:$geographic_info->observaciones);
        $geographic_info->vive_lejos = (isset($vive_lejos)?$vive_lejos:$geographic_info->vive_lejos);
        $geographic_info->vive_zona_riesgo = (isset($vive_zona_riesgo)?$vive_zona_riesgo:$geographic_info->vive_zona_riesgo);
        $geographic_info->nativo = (isset($nativo)?$nativo:$geographic_info->nativo);
        $geographic_info->nivel_riesgo = (isset($nivel_riesgo)?$nivel_riesgo:$geographic_info->nivel_riesgo);

        $result_geographic_info = $DB->update_record('talentospilos_demografia', $geographic_info);
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

/**
 * Get municipios registrados
 *
 * @see student_profile_get_municipios()
 * @return object --> with MUNICIPIOS information
 */
function student_profile_get_municipios()
{
    global $DB;
    $array_departamentos = array ();
    $sql_query_dpto = "SELECT id, nombre FROM {talentospilos_departamento}";
    $departamentos  = $DB->get_records_sql($sql_query_dpto);
    foreach($departamentos as $departamento){
        $sql_query = "SELECT  id, nombre   FROM {talentospilos_municipio} WHERE cod_depto = $departamento->id";
        $municipios = $DB->get_records_sql($sql_query);
        $array_departamentos[$departamento->nombre] =  $municipios;
    }

    return $array_departamentos;
}

/**
 * @see student_profile_load_geographic_tab($id_ases)
 * @desc Gets all the geographic information of an student
 * @param $id_ases --> ASES student id
 * @return Object
 */
function student_profile_load_geographic_tab($id_ases){

    $record = new stdClass();

    $geographic_object = get_geographic_info($id_ases);
    $neighborhoods_array = get_neighborhoods();
    $municipios= student_profile_get_municipios();

    $municipio_student = $geographic_object->id_city;

    $options_municipios = '';

    $options_municipios .= "<optgroup label='Populares'> <option value='1'>NO DEFINIDO</option> </optgroup>" ;

    foreach($municipios as $municipio){
        $key = key($municipios);
        $options_municipios .= "<optgroup label = '$key'>";

        foreach($municipio as $mun){
            if($municipio_student == $mun->id){
                $options_municipios .= "<option value='$mun->id' selected='selected'>$mun->nombre</option>";
            }else{
                $options_municipios .= "<option value='$mun->id'>$mun->nombre</option>";
            }
        }

        next($municipios);

        $options_municipios .= "</optgroup>";
    }

    $record->options_municipio_act = $options_municipios;

    $record->muni = $municipios;
    $neighborhoods = "<option>No registra</option>";

    for ($i = 1; $i <= count($neighborhoods_array); $i++) {
        if(isset($geographic_object->neighborhood)){
            if ($neighborhoods_array[$i]->id == (int) $geographic_object->neighborhood) {
                $neighborhoods .= "<option value='" . $neighborhoods_array[$i]->id . "' selected>" . $neighborhoods_array[$i]->nombre . "</option>";
            } else {
                $neighborhoods .= "<option value='" . $neighborhoods_array[$i]->id . "'>" . $neighborhoods_array[$i]->nombre . "</option>";
            }
        }else{
            $neighborhoods .= "<option value='" . $neighborhoods_array[$i]->id . "'>" . $neighborhoods_array[$i]->nombre . "</option>";
        }
    }

    $record->view_geographic_config_sp = true;
    $record->update_geographic_tab_sp = true;

    $native = $geographic_object->native;
    $live_far_away = $geographic_object->live_far_away;
    $live_risk_zone = $geographic_object->live_risk_zone;
    $geographic_risk_level = $geographic_object->risk_level;

    $record->select_neighborhoods = $neighborhoods;
    $record->live_far_away = ($live_far_away)?true:false;
    $record->live_risk_zone = ($live_risk_zone)?true:false;
    $record->res_address = $geographic_object->res_address;
    $record->observations = $geographic_object->observations;
    $record->geographic_risk_level  = $geographic_risk_level;

    if($live_risk_zone && $native == 1)
        $record->native_origin = true;

    switch ($geographic_risk_level) {
        case 1:
            $record->low_level = true;
            $record->mid_level = $record->high_level = false;
            break;
        case 2:
            $record->mid_level = true;
            $record->low_level = $record->high_level = false;
            break;
        case 3:
            $record->high_level = true;
            $record->low_level = $record->mid_level = false;
            break;
        default:
            $record->low_level = $record->mid_level = $record->high_level = false;
            break;
    }

    return $record;

}