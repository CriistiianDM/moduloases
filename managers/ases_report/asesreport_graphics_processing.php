<?php

require_once('asesreport_lib.php');

if(isset($_POST['type'])&&$_POST['type']=="sexo"&&isset($_POST['cohort'])){
    
    $cohorte =  $_POST['cohort'];
    $data = getGraficSex($cohorte);
    echo json_encode($data);
    
} 



if(isset($_POST['type'])&&$_POST['type']=="edad"&&isset($_POST['cohort'])){
    
    $cohorte =  $_POST['cohort'];
    $data = getGraficAge($cohorte);
    echo json_encode($data);
    
} 

if(isset($_POST['type'])&&$_POST['type']=="carrera"&&isset($_POST['cohort'])&&isset($_POST['ases_status'])){
    
    $cohorte =  $_POST['cohort'];
    $ases_status = $_POST['ases_status'];
    $data = getGraficPrograma($cohorte, $ases_status);
    echo json_encode($data);
    
} 

if(isset($_POST['type'])&&$_POST['type']=="facultad"&&isset($_POST['cohort'])){
    
    $cohorte =  $_POST['cohort'];
    $data = getGraficFacultad($cohorte);

    echo json_encode($data);
    
} 

if(isset($_POST['type'])&&$_POST['type']=="estado"&&isset($_POST['cohort'])){
    
    $cohorte =  $_POST['cohort'];
    $data = getGraficEstado($cohorte);

    echo json_encode($data);
    
} 
//user_info_field
//user_info_data
//tabla programa
//tabla facultad

// select * from prefix_talentospilos_programa  
// inner join prefix_talentospilos_facultad on 
// (prefix_talentospilos_programa.id_facultad=prefix_talentospilos_facultad.id);

?>