<?php
/**
 * Created by PhpStorm.
 * User: luis
 * Date: 8/02/19
 * Time: 04:49 PM
 */
namespace mass_management\endpoints;

const UPLOAD_ASES_USERS = 'ases_user';
const UPDATE_COND_EXEPCION= 'cond_excepcion';
const UPDATE_ACADEMIC_HISTORY = 'academic_history';

function get_options() {
    return [
        array(
            'display_name'=>'Usuarios ASES',
            'route' => UPLOAD_ASES_USERS
        ),
        array(
            'display_name' => 'Actualizar condición excepcion',
            'route' => UPDATE_COND_EXEPCION
        ),
        array(
            'display_name' => 'Actualizar historial academico de estudiantes',
            'route' => UPDATE_ACADEMIC_HISTORY
        )
    ];
}