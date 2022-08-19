 /**
 * Management - Tracks (seguimiento de pilos)
 * @module amd/src/pilos_tracking_main 
 * @author Isabella Serna Ramírez <isabella.serna@correounivalle.edu.co>
 * @author Jeison Cardona Gomez <jeison.cardona@correounivalle.edu.co>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
    ['jquery',
    'block_ases/Modernizr-v282' ,
    'block_ases/bootstrap', 
    'block_ases/jquery.dataTables',  
    'block_ases/sweetalert', 
    'block_ases/select2',
    'block_ases/loading_indicator'
], function($,Modernizr,bootstrap, datatables, sweetalert, select2, loading_indicator) {

    return {
        init: function() {

            

            var collapse_loaded = [];

            var rol = 0;
            var id = 0;
            var name = "";
            var email = "";
            var namerol = "";
            var current_semester = parseInt($("#current_ases_semester").data("info"));
            
            $("#msg-cache").click(function(){
                
                swal({
                    title: "¡Información!",
                    text: "Está observando una versión reciente del conteo, más no una versión en vivo. El conteo se actualizará en un intervalo de tiempo no mayor a 30 minutos.",
                    type: 'info'
                });                
                
            });


             /**
             *** Rules associated with the handling of new forms
             ***
             **/

            function put_tracking_count( username, semester_id, instance, is_student ){
                
                let fun = "get_tracking_count";
                if( is_student ){
                    fun = "get_tracking_count_student";
                }

                loading_indicator.show();

                $.ajax({
                    type: "POST",
                    data: JSON.stringify( { function:fun, params:[ username, semester_id, instance ] } ),
                    url: "../managers/pilos_tracking/v2/pilos_tracking_api.php",
                    dataType: "json",
                    cache: "false",
                    success: function( data ) {

                        loading_indicator.hide();

                        let counters = data.data_response;
                        
                        let fichas_totales = 0;
                        let fichas_PP = 0;
                        let fichas_Pp = 0;
                        let inasistencias_totales = 0;
                        let inasistencias_PP = 0;
                        let inasistencias_Pp = 0;

                        for( let i = 0; i < counters.length; i++){

                            fichas_totales = counters[i].count.revisado_profesional + counters[i].count.not_revisado_profesional;
                            fichas_PP = counters[i].count.not_revisado_profesional;
                            fichas_Pp = counters[i].count.not_revisado_practicante;
                            inasistencias_totales = counters[i].count.in_revisado_profesional + counters[i].count.in_not_revisado_profesional;
                            inasistencias_PP = counters[i].count.in_not_revisado_profesional;
                            inasistencias_Pp = counters[i].count.in_not_revisado_practicante;

                            let base = '\
                                <div class="conteo">\
                                    <div class="row"> \
                                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4"> \
                                            Fichas: <strong>'+fichas_totales+'</strong>\
                                        </div>\
                                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4"> \
                                            <div class="pend-prof">Pendientes Prof: </div>'+fichas_PP+'\
                                        </div>\
                                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4"> \
                                            <div class="pend-pract">Pendientes pract: </div>'+fichas_Pp+'\
                                        </div>\
                                    </div>\
                                    <div class="row"> \
                                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4"> \
                                            Inasistencias: <strong>'+inasistencias_totales+'</strong>\
                                        </div>\
                                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4"> \
                                            <div class="pend-prof">Pendientes Prof: </div>'+inasistencias_PP+'\
                                        </div>\
                                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4"> \
                                            <div class="pend-pract">Pendientes pract: </div>'+inasistencias_Pp+'\
                                        </div>\
                                    </div>\
                                </div>\
                            ';
                            
                            $("#counting_" + counters[i].username).find(".loader").html(
                                base
                            );

                        }

                    },
                    error: function( data ) {
                        loading_indicator.hide();
                        console.log( data );
                        swal({
                            title: "Error!",
                            text: "",
                            html: true,
                            type: 'error',
                            confirmButtonColor: "#d51b23"
                        });
                    },
                });
            }


            $(document).on( "click", ".btn-dphpforms-close", function() {
                $(this).closest('div[class="mymodal"]').fadeOut(300);
            });

            $('.outside').click(function(){
                var outside = $(this);
                swal({
                    title: 'Confirmación de salida',
                    text: "",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Salir'
                  }, function(isConfirm) {
                    if (isConfirm) {
                        $(outside).parent('.mymodal').fadeOut(300);
                    }
                  });
                
            });

            function custom_actions( form, action ){

                if( (form == 'primer_acercamiento' ) && ( action == 'insert' )){ 

                }else if( (form == 'primer_acercamiento' ) && ( action == 'update' )){ 

                }else if( (form == 'inasistencia' )&&( action == 'insert' )){

                }else if( (form == 'inasistencia')&&( action == 'update' ) ){

                    var rev_prof = $('.dphpforms-record').find('.in_revisado_profesional').find('.checkbox').find('input[type=checkbox]').prop('checked');
                    var rev_prac = $('.dphpforms-record').find('.in_revisado_practicante').find('.checkbox').find('input[type=checkbox]').prop('checked');
                    var role_support = $('#dphpforms_role_support').attr('data-info');
                    if( ( rev_prof ) && ( role_support != "sistemas" ) ){
                        $('.btn-dphpforms-delete-record').remove();
                        $('.btn-dphpforms-update').remove();
                    }
                    if( role_support == "dir_socioeducativo" ){
                        $('.btn-dphpforms-delete-record').remove();
                        $('.btn-dphpforms-update').remove();
                    };    

                }else if( (form == 'seguimiento_pares' )&&( action == 'insert' )){

                }else if( (form == 'seguimiento_pares')&&( action == 'update' ) ){

                    var rev_prof = $('.dphpforms-record').find('.revisado_profesional').find('.checkbox').find('input[type=checkbox]').prop('checked');
                    var rev_prac = $('.dphpforms-record').find('.revisado_practicante').find('.checkbox').find('input[type=checkbox]').prop('checked');
                                            
                    if( rev_prof ){
                        $('.btn-dphpforms-delete-record').remove();
                        $('.btn-dphpforms-update').remove();
                    };

                    if( rev_prac ){
                        $('.btn-dphpforms-delete-record').remove();
                    };

                }else if( (form == 'seguimiento_geografico_')&&( action == 'insert' ) ){
                
                }else if( (form == 'seguimiento_geografico_')&&( action == 'update' ) ){

                }else if( (form=='seguimiento_grupal_')&&( action == 'insert' ) ){

                }else if( (form=='seguimiento_grupal_')&&( action == 'update' ) ){

                }

            }

            $(document).ready(function() {
                
                //$(".loader").html("Cargando conteo...");

                ///////////////////////////////////////////////////////////7

                $(".se-pre-con").fadeOut('slow');
                $("#reemplazarToogle").fadeIn("slow");
                let username = "";
                //Getting information of the logged user such as name, id, email and role
                loading_indicator.show();
                $.ajax({
                    type: "POST",
                    data: {
                        type: "getInfo",
                        instance: get_instance()
                    },
                    url: "../../../blocks/ases/managers/pilos_tracking/pilos_tracking_report.php",
                    async: false,
                    success: function(msg) {
                        loading_indicator.hide();
                        $data = $.parseJSON(msg);
                        name = $data.username;
                        username = $data.username;
                        id = $data.id;
                        email = $data.email;
                        rol = $data.rol;
                        namerol = $data.name_rol;
                        
                    },
                    dataType: "text",
                    cache: "false",
                    error: function(msg) {
                        loading_indicator.hide();
                        swal({
                            title: "error al obtener información del usuario, getInfo.",
                            html: true,
                            type: "error",
                            confirmButtonColor: "#d51b23"
                        });
                    },
                });

                name = "";
                var usuario = [];
                usuario["id"] = id;
                usuario["name"] = name;
                usuario["namerol"] = namerol;
                
                create_specific_counting( usuario );

                // when user is 'practicante' then has permissions
                if (namerol == "practicante_ps") {
                    put_tracking_count( username, current_semester, parseInt( get_instance() ), false );
                    consultar_seguimientos_persona(get_instance(), usuario, username);
                    send_email_new_form(get_instance()); 

                   // when user is 'profesional' then has permissions
                } else if (namerol == "profesional_ps") {
                    //Starts adding event
                    put_tracking_count( username, current_semester, parseInt( get_instance() ), false );
                    consultar_seguimientos_persona(get_instance(), usuario, username);
                    send_email_new_form(get_instance());

                    // when user is 'monitor' then has permissions
                } else if (namerol == "monitor_ps") {
                    put_tracking_count( username, current_semester, parseInt( get_instance() ), true );
                    consultar_seguimientos_persona(get_instance(), usuario, username);
                    send_email_new_form(get_instance());

                    // when user is 'sistemas' then has permissions
                } else if (namerol == "sistemas") {
                    anadirEvento(get_instance());
                    send_email_new_form(get_instance());
                }

            });


            /*function edit_tracking_new_form(){
            // Controles para editar formulario de pares
            $('.dphpforms-peer-record').on('click', function(){
                var id_tracking = $(this).attr('data-record-id');
                load_record_updater('seguimiento_pares', id_tracking);
                $('#modal_v2_edit_peer_tracking').fadeIn(300);
                  
            });}


            function edit_groupal_tracking_new_form(){
            // Controles para editar formulario grupal
            $('.dphpforms-groupal-record').on('click', function(){
                var id_tracking = $(this).attr('data-record-id');
                load_record_updater('seguimiento_grupal', id_tracking);
               $('#modal_v2_edit_groupal_tracking').fadeIn(300);

            });}*/


            function check_risks_tracking( flag, student_code ){
                   

                        var individual_risk = get_checked_risk_value_tracking('.puntuacion_riesgo_individual');
                        var idv_observation = $('.comentarios_individual').find('textarea').val();;
                        var familiar_risk = get_checked_risk_value_tracking('.puntuacion_riesgo_familiar');
                        var fam_observation = $('.comentarios_familiar').find('textarea').val();
                        var academico_risk = get_checked_risk_value_tracking('.puntuacion_riesgo_academico');
                        var aca_observation = $('.comentarios_academico').find('textarea').val();
                        var economico_risk = get_checked_risk_value_tracking('.puntuacion_riesgo_economico');
                        var eco_observation = $('.comentarios_economico').find('textarea').val();
                        var vida_univer_risk = get_checked_risk_value_tracking('.puntuacion_vida_uni');
                        var vid_observation = $('.comentarios_vida_uni').find('textarea').val();

                        if( 
                            ( individual_risk == '3' ) || ( familiar_risk == '3' ) || 
                            ( academico_risk == '3' ) || ( economico_risk == '3' ) || 
                            ( vida_univer_risk == '3' ) 
                        ){

                            var json_risks = {
                                "function": "send_email_dphpforms",
                                "student_code": student_code,
                                "risks": [
                                    {
                                        "name":"Individual",
                                        "risk_lvl": individual_risk,
                                        "observation":idv_observation
                                    },
                                    {
                                        "name":"Familiar",
                                        "risk_lvl": familiar_risk,
                                        "observation":fam_observation
                                    },
                                    {
                                        "name":"Académico",
                                        "risk_lvl": academico_risk,
                                        "observation":aca_observation
                                    },
                                    {
                                        "name":"Económico",
                                        "risk_lvl": economico_risk,
                                        "observation":eco_observation
                                    },
                                    {
                                        "name":"Vida Universitaria",
                                        "risk_lvl": vida_univer_risk,
                                        "observation":vid_observation
                                    }
                                ],
                                "date": $('.fecha').find('input').val(),
                                "url": window.location.href
                            };

                            loading_indicator.show();
                            $.ajax({
                                type: "POST",
                                data: JSON.stringify(json_risks),
                                url: "../managers/pilos_tracking/send_risk_email.php",
                                success: function(msg) {
                                    console.log(msg);
                                    loading_indicator.hide();
                                },
                                dataType: "text",
                                cache: "false",
                                error: function(msg) {
                                    loading_indicator.hide();
                                    console.log(msg)
                                }
                            });

                        }

                    
                };

            function get_checked_risk_value_tracking( class_id ){
                    var value = 0;
                    $( class_id ).find('.opcionesRadio').find('div').each(function(){
                        if($(this).find('label').find('input').is(':checked')){
                            value = $(this).find('label').find('input').val();
                        }
                    });
                    return value;
                }; 

          //OBSOLETO
           /*$(document).on('click', '.dphpforms > #button' , function(evt) {
           
                    evt.preventDefault();
                    $( ':disabled' ).prop( 'disabled', false);
                    var formData = new FormData();
                    var formulario = $(this).parent();
                    var url_processor = formulario.attr('action');
                    if(formulario.attr('action') == 'procesador.php'){
                        url_processor = '../managers/dphpforms/procesador.php';
                    };
                    var student_code = formulario.find('.id_estudiante').find('input').val();
                    loading_indicator.show();
                    $.ajax({
                        type: 'POST',
                        url: url_processor,
                        data:  $('form.dphpforms').serialize(),
                                dataType: 'json',

                        success: function(data) {
                                loading_indicator.hide();
                                //var response = JSON.parse(data);
                                var response = data;
                                
                                if(response['status'] == 0){
                                    $.get( "../managers/pilos_tracking/api_pilos_tracking.php?function=update_last_user_risk&arg=" + student_code + "&rid=-1", function( data ) {
                                        console.log( data );
                                    });
                                    var mensaje = '';
                                    if(response['message'] == 'Stored'){
                                        mensaje = 'Almacenado';
                                    }else if(response['message'] == 'Updated'){
                                        mensaje = 'Actualizado';
                                    }
                                    check_risks_tracking( false, student_code );
                                    swal(
                                        {title:'Información',
                                        text: mensaje,
                                        type: 'success'},
                                        function(){
                                            if(response['message'] == 'Updated'){
                                                $('#dphpforms-peer-record-' + $('#dphpforms_record_id').val()).stop().animate({backgroundColor:'rgb(175, 255, 173)'}, 400).animate({backgroundColor:'#f5f5f5'}, 4000);
                                                let asesid = $('#dphpforms-peer-record-' + $('#dphpforms_record_id').val()).parent().parent().data("asesid");
                                                console.log(asesid);
                                                for( let i = 0; i < collapse_loaded.length; i++){ 
                                                    if ( collapse_loaded[i] === asesid) {
                                                        collapse_loaded.splice(i, 1); 
                                                    }
                                                 }
                                                $("a[data-username='"+ asesid +"']").trigger("click");
                                            }
                                        }
                                    );
                                    $('.dphpforms-response').trigger("reset");
                                    $('#modal_v2_edit_peer_tracking').fadeOut(300);
                                    $('#modal_v2_peer_tracking').fadeOut(300);

                                    $(formulario).find('button').prop( "disabled", false);
                                    $(formulario).find('a').attr( "disabled", false);
                                    
                                }else if(response['status'] == -5){
                                    $(formulario).find('button').prop( "disabled", false);
                                    $(formulario).find('a').attr( "disabled", false);
                                    var mensaje = '';
                                    if(response['message'] == 'The field is static and can not be changed'){

                                        var id_form_pregunta = response['data'];
                                        $('div').removeClass('regla_incumplida');
                                        $('.div-' + id_form_pregunta).addClass('regla_incumplida');
                                        
                                        mensaje  = 'Ups!, el campo marcado en rojo está definido como estático y por lo tanto debe mantener el mismo valor, si no logra ver el campo marcado en rojo informe de este incidente.';
                                    }
                                    swal(
                                        'Alerta',
                                        mensaje,
                                        'warning'
                                    );
                                }else if(response['status'] == -4){
                                    $(formulario).find('button').prop( "disabled", false);
                                    $(formulario).find('a').attr( "disabled", false);
                                    var mensaje = '';
                                    if(response['message'] == 'Field does not match with the regular expression'){

                                        var id_form_pregunta = response['data']['id'];
                                        $('div').removeClass('regla_incumplida');
                                        $('.div-' + id_form_pregunta).addClass('regla_incumplida');
                                        
                                        mensaje  = 'Ups!, el campo marcado en rojo no cumple con el patrón esperado('+ response['data']['human_readable'] +'). Ejemplo: ' + response['data']['example'];
                                    }
                                    swal(
                                        'Alerta',
                                        mensaje,
                                        'warning'
                                    );
                                }else if(response['status'] == -3){
                                    $(formulario).find('button').prop( "disabled", false);
                                    $(formulario).find('a').attr( "disabled", false);
                                    var mensaje = '';
                                    if(response['message'] == 'Field cannot be null'){

                                        var id_form_pregunta = response['data'];
                                        $('div').removeClass('regla_incumplida');
                                        $('.div-' + id_form_pregunta).addClass('regla_incumplida');
                                        
                                        mensaje  = 'Ups!, los campos que se acaban de colorear en rojo no pueden estar vacíos, si no logra ver ningún campo, informe de este incidente.';
                                    }
                                    swal(
                                        'Alerta',
                                        mensaje,
                                        'warning'
                                    );
                                }else if(response['status'] == -2){
                                    $(formulario).find('button').prop( "disabled", false);
                                    $(formulario).find('a').attr( "disabled", false);
                                    var mensaje = '';
                                    if(response['message'] == 'Without changes'){
                                        mensaje = 'No hay cambios que registrar';
                                        $('#modal_v2_edit_peer_tracking').fadeOut(300);
                                        $('#modal_v2_peer_tracking').fadeOut(300);
                                        $('#modal_primer_acercamiento').fadeOut(300);
                                        $('#modal_seguimiento_geografico').fadeOut(300);
                                    }else if(response['message'] == 'Unfulfilled rules'){
                                        var id_form_pregunta_a = response['data']['id_form_pregunta_a'];
                                        var id_form_pregunta_b = response['data']['id_form_pregunta_b'];
                                        $('div').removeClass('regla_incumplida');
                                        $('.div-' + id_form_pregunta_a).addClass('regla_incumplida');
                                        $('.div-' + id_form_pregunta_b).addClass('regla_incumplida');
                                        
                                        mensaje  = 'Ups!, revise los campos que se acaban de colorear en rojo.';
                                    }
                                    swal(
                                        'Alerta',
                                        mensaje,
                                        'warning'
                                    );
                                }else if(response['status'] == -1){
                                    console.log(data);
                                    swal(
                                        'ERROR!',
                                        'Ups!, informe de este error',
                                        'error'
                                    );
                                };
                            },
                            error: function(data) {
                                loading_indicator.hide();
                                swal(
                                    'Error!',
                                    'Ups!, informe de este error',
                                    'error'
                                );
                            }
                            
                     });
                
                });
            */



                $('.mymodal-close').click(function(){
                    $(this).parent().parent().parent().parent().fadeOut(300);
                });


            function create_specific_counting(user){
                
                $("#general_rev_pro").html( "*" );
                $("#general_rev_prac").html( "*" );
                $("#general_not_rev_pro").html( "*" );
                $("#general_not_rev_prac").html( "*" );
                $("#general_pro_t").html( "*" );
                $("#general_prac_t").html( "*" );

                $("#general_in_rev_pro").html( "*" );
                $("#general_in_rev_prac").html( "*" );
                $("#general_in_not_rev_pro").html( "*" );
                $("#general_in_not_rev_prac").html( "*" );
                $("#general_in_pro_t").html( "*" );
                $("#general_in_prac_t").html( "*" );

                loading_indicator.show();
                $.ajax({
                    type: "POST",
                    data: {
                        type: "user_specific_counting",
                        user: user,
                        instance:get_instance(),
                    },
                    url: "../managers/pilos_tracking/pilos_tracking_report.php",
                    async: true,
                    dataType: "json",
                    cache: "false",
                    success: function( data ) {
                        loading_indicator.hide();
                        $("#general_rev_pro").html( data.revisado_profesional );
                        $("#general_rev_prac").html( data.revisado_practicante );
                        $("#general_not_rev_pro").html( data.not_revisado_profesional );
                        $("#general_not_rev_prac").html( data.not_revisado_practicante );
                        $("#general_pro_t").html( data.total_profesional );
                        $("#general_prac_t").html( data.total_practicante );

                        $("#general_in_rev_pro").html( data.in_revisado_profesional );
                        $("#general_in_rev_prac").html( data.in_revisado_practicante );
                        $("#general_in_not_rev_pro").html( data.in_not_revisado_profesional );
                        $("#general_in_not_rev_prac").html( data.in_not_revisado_practicante );
                        $("#general_in_pro_t").html( data.in_total_profesional );
                        $("#general_in_prac_t").html( data.in_total_practicante );
                        
                    },
                    error: function( data ) {
                        loading_indicator.hide();
                    },
                });

            }


                /*function generate_attendance_table(students){
                    loading_indicator.show();
                     $.ajax({
                            type: "POST",
                            data: {
                                students: students,
                                type: "consult_students_name"
                            },
                            url: "../../../blocks/ases/managers/pilos_tracking/pilos_tracking_report.php",
                            async: false,
                            success: function(msg) {

                                loading_indicator.hide();
                                if (msg != "") {
                                   var table ='<hr style="border-color:red"><div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 estudiantes" id="students"><h3>Estudiantes asistentes:</h3><br>'+msg+'<br>';
                                   $('#modal_v2_edit_groupal_tracking').find('#students').remove(); 
                                   $('#modal_v2_edit_groupal_tracking').find('form').find('h1').after(table);
                                }
                                
                            },
                            dataType: "text",
                            cache: "false",
                            error: function(msg) {
                                alert("Error al consultar nombres de los estudiantes pertenecientes a un seguimiento grupal");
                                loading_indicator.hide();
                            },
                        });
                }*/


                /*function load_record_updater(form_id, record_id){
                    $('.div').removeClass('regla_incumplida');
                    $("#body_editor").html("");
                    loading_indicator.show();
                    $.get( "../managers/dphpforms/dphpforms_forms_core.php?form_id=&record_id="+record_id, function( data ) {
                         loading_indicator.hide();
                         if(form_id =='seguimiento_grupal'){
                      
                            $("#modal_v2_edit_groupal_tracking").find("#body_editor").append(data);
                            $("#modal_v2_edit_groupal_tracking").find(".btn-dphpforms-univalle").remove();
                            var students = $("#modal_v2_edit_groupal_tracking").find('form').find('.oculto.id_estudiante').find('input').val();

                            generate_attendance_table(students);


                         }else{
                                                     
                            $('#body_editor').append( data );
                            $(".dphpforms.dphpforms-record.dphpforms-updater").append('<br><br><div class="div-observation col-xs-12 col-sm-12 col-md-12 col-lg-12 comentarios_vida_uni">Observaciones de Practicante/profesional:<br> <textarea id="observation_text" class="form-control " name="observation_text" maxlength="5000"></textarea><br><a id="send_observation" class="btn btn-sm btn-danger btn-dphpforms-univalle btn-dphpforms-send-observation">Enviar observación</a></div>');
                            $('button.btn.btn-sm.btn-danger.btn-dphpforms-univalle').attr('id', 'button');
                            var is_seguimiento_pares = data.indexOf('seguimiento_de_pares_');
                            if( is_seguimiento_pares != -1 ){
                                custom_actions( 'seguimiento_pares', 'update' );
                            };
                            var is_inasistencia = data.indexOf('inasistencia');
                            if( is_inasistencia != -1 ){
                                custom_actions( 'inasistencia', 'update' );
                            };
                         }
                            
                           
                            $("#permissions_informationr").html("");

                            var rev_prof = $('.dphpforms-record').find('.revisado_profesional').find('.checkbox').find('input[type=checkbox]').prop('checked');
                            var rev_prac = $('.dphpforms-record').find('.revisado_practicante').find('.checkbox').find('input[type=checkbox]').prop('checked');
                            
                            if(rev_prof){ 
                                $('.dphpforms-record').find('.btn-dphpforms-delete-record').remove();
                            }

                            var behaviors = JSON.parse($('#permissions_information').text());
                            
                            for(var x = 0; x < behaviors['behaviors_permissions'].length; x++){
                             
                                var current_behaviors =  behaviors['behaviors_permissions'][x]['behaviors'][0];
                                var behaviors_accessibility = current_behaviors.behaviors_accessibility;
                                
                                for( var z = 0; z <  behaviors_accessibility.length; z++){
                                    var disabled = behaviors_accessibility[z]['disabled'];
                                    if(disabled == 'true'){
                                        disabled = true;
                                    }else if(disabled == 'false'){
                                        disabled = false;
                                    }
                                    $('.dphpforms-record').find('#' + behaviors_accessibility[z]['id']).prop( 'disabled', disabled );
                                    $('.dphpforms-record').find('.' + behaviors_accessibility[z]['class']).prop( 'disabled', disabled );

                                }
                                var behaviors_fields_to_remove = current_behaviors['behaviors_fields_to_remove'];
                                for( var z = 0; z < behaviors_fields_to_remove.length; z++){
                                    $('.dphpforms-record').find('#' + behaviors_fields_to_remove[z]['id']).remove();
                                    $('.dphpforms-record').find('.' + behaviors_fields_to_remove[z]['class']).remove();
                                }
                                var limpiar_to_eliminate = current_behaviors['limpiar_to_eliminate'];
                                for( var z = 0; z <  limpiar_to_eliminate.length; z++){
                                    $('.dphpforms-record').find('.' + limpiar_to_eliminate[z]['class'] + '.limpiar ').remove();
                                }
                                
                            }

                            $("#permissions_informationr").html("");

                    });
                }*/




            student_load();
            monitor_load();
            professional_load();
            groupal_tracking_load();

 



            //-------- Page elements --> Listener


            function professional_load(){

            /*When click on the practicant's name, open the container with the information of 
            the assigned monitors*/

            $('a[class*="practicant"]').click(function() {

                let username = $(this).data("username");
                if( collapse_loaded.indexOf( username ) == -1){
                    collapse_loaded.push( username );
                }else{
                    return;
                }

                var practicant_code = $(this).attr('href').split("#practicant")[1];
                var practicant_id = $(this).attr('href');
                //Fill container with the information corresponding to the monitor 
                loading_indicator.show();
                $.ajax({
                    type: "POST",
                    data: {
                        type: "get_practicants_of_professional",
                        practicant_code: practicant_code,
                        instance:get_instance(),
                    },
                    url: "../managers/pilos_tracking/pilos_tracking_report.php",
                    async: true,
                    dataType: "json",
                    cache: "false",
                    success: function(msg) {

                        loading_indicator.hide();
                        $(practicant_id + " > div").empty();
                        $(practicant_id + " > div").append(msg.render);
                        var html = msg.counting;

                        put_tracking_count( practicant_code, current_semester, parseInt( get_instance() ), false );
                        monitor_load();
                        groupal_tracking_load();

                        

                    },
                    error: function(msg) {
                       loading_indicator.hide();
                       swal({
                            title: "Oops !",
                            text: "Se presentó un inconveniente con el practicante seleccionado.",
                            html: true,
                            type: 'warning',
                            confirmButtonColor: "#d51b23"
                        });
                    },
                });
            
            });
            }






            function monitor_load(){

            /*When click on the student's name, open the container with the information of 
            the follow-ups of that date*/

            $('a[class*="monitor"]').click(function() {

                let username = $(this).data("username");
                if( collapse_loaded.indexOf( username ) == -1){
                    collapse_loaded.push( username );
                }else{
                    return;
                }

                var monitor_code = $(this).attr('href').split("#monitor")[1];
                var monitor_id = $(this).attr('href');
                //Fill container with the information corresponding to the monitor 
                loading_indicator.show();
                $.ajax({
                    type: "POST",
                    data: {
                        type: "get_monitors_of_practicant",
                        monitor_code: monitor_code,
                        instance:get_instance(),
                    },
                    url: "../managers/pilos_tracking/pilos_tracking_report.php",
                    async: true,
                    dataType: "json",
                    cache: "false",
                    success: function(msg ) {

                        loading_indicator.hide();
                        $(monitor_id + " > div").empty();
                        $(monitor_id + " > div").append(msg);
                        put_tracking_count( monitor_code, current_semester, parseInt( get_instance() ), true );
                        student_load();
                        groupal_tracking_load();
                        
                    },
                    error: function(msg) {
                        loading_indicator.hide();
                       swal({
                            title: "Oops !",
                            text: "Se presentó un inconveniente con el monitor seleccionado.",
                            html: true,
                            type: 'warning',
                            confirmButtonColor: "#d51b23"
                        });
                    },
                });
            
            });
            }


            /*When click on the "SEGUIMIENTOS GRUPALES", open the container with the information of 
            the follow-ups of that date*/

            function groupal_tracking_load(){

            $('a[class*="groupal"]').click(function() {
                var student_code = $(this).attr('href').split("#groupal")[1];
                var student_id = $(this).attr('href');
                //Fill container with the information corresponding to the trackings of the selected student
                loading_indicator.show();
                $.ajax({
                    type: "POST",
                    data: {
                        type: "get_groupal_trackings",
                        student_code: student_code,
                        instance:get_instance()
                    },
                    url: "../managers/pilos_tracking/pilos_tracking_report.php",
                    async: false,
                    success: function(msg) {

                        loading_indicator.hide();
                        $(student_id + " > div").empty();
                        $(student_id + " > div").append(msg);
                        //edit_groupal_tracking_new_form();
                        
                    },
                    dataType: "json",
                    cache: "false",
                    error: function(msg) {
                        loading_indicator.hide();
                        swal({
                            title: "Oops !",
                            text: "Se presentó un inconveniente con los seguimientos grupales seleccionados.",
                            html: true,
                            type: 'warning',
                            confirmButtonColor: "#d51b23"
                        });
                    },
                });
            
            });}




            function student_load(){

            /*When click on the student's name, open the container with the information of 
            the follow-ups of that date*/

            $('a[class*="student"]').click(function() {

                let username = $(this).data("username");
                if( collapse_loaded.indexOf( username ) == -1){
                    collapse_loaded.push( username );
                }else{
                    return;
                }

                var student_code = $(this).attr('href').split("#student")[1];
                var student_id = $(this).attr('href');
                //Fill container with the information corresponding to the trackings of the selected student
                loading_indicator.show();
                $.ajax({
                    type: "POST",
                    data: {
                        type: "get_student_trackings",
                        student_code: student_code,
                        instance:get_instance()
                    },
                    url: "../managers/pilos_tracking/pilos_tracking_report.php",
                    async: false,
                    success: function(msg) {

                        loading_indicator.hide();
                        $(student_id + " > div").empty();
                        $(student_id + " > div").append(msg);
                        //edit_tracking_new_form();
                        //edit_groupal_tracking_new_form();
                        
                    },
                    dataType: "json",
                    cache: "false",
                    error: function(msg) {
                        loading_indicator.hide();
                        swal({
                            title: "Oops !",
                            text: "Se presentó un inconveniente con el estudiante seleccionado.",
                            html: true,
                            type: 'warning',
                            confirmButtonColor: "#d51b23"
                        });
                    },
                });
            
            });

            /*When click on the button "Ver horas", open a new tab with information of report time control about a
            determinated monitor*/

            $('.see_history').unbind().click(function(e) {


             var element =  $(this).parents().eq(3).attr('href').split("#monitor")[1];
             loading_indicator.show();
            $.ajax({
                    type: "POST",
                    data: {
                        type: "redirect_tracking_time_control",
                        monitor: element,
                    },
                    url: "../managers/pilos_tracking/pilos_tracking_report.php",
                    async: false,
                    success: function(msg) {
                        loading_indicator.hide();
                        var current_url = window.location.href;
                        var next_url = current_url.replace("report_trackings", "tracking_time_control");
                        
                        try{
                        var win = window.open(next_url+"&&monitorid="+msg, '_blank');
                        
                        if (win) {
                            //Browser has allowed it to be opened
                            win.focus();
                        }
                        }catch(ex){
                            alert("Se ha producido un error al abrir la ventana : "+ex);
                        } 
                        
                    },
                    dataType: "json",
                    cache: "false",
                    error: function(msg) {
                        loading_indicator.hide();
                        swal({
                            title: "Oops !",
                            text: "Se presentó un inconveniente al reedirecionar al reporte de horas.",
                            html: true,
                            type: 'warning',
                            confirmButtonColor: "#d51b23"
                        });
                    },
                });

            });

        }




            /**
             * @method consultar_seguimientos_persona
             * @desc Obtain track information of a certain user
             * @param {instance} instance current instance
             * @param {object} usuario current user to obtain information
             * @param {String} username
             * @return {void}
             */
            function consultar_seguimientos_persona(instance, usuario, username) {
                $("#periodos").change(function() {
                    if (namerol != 'sistemas') {
                        var semestre = $("#periodos").val();
                        var id_persona = id;
                        loading_indicator.show();
                        $.ajax({
                            type: "POST",
                            data: {
                                id_persona: id_persona,
                                id_semestre: semestre,
                                instance: instance,
                                otro: true,
                                type: "consulta_sistemas"
                            },
                            url: "../../../blocks/ases/managers/pilos_tracking/pilos_tracking_report.php",
                            dataType: "text",
                            cache: "false",
                            success: function(msg) {

                                loading_indicator.hide();
                                if (msg == "") {
                                    $('#reemplazarToogle').html('<label> No se encontraron registros </label>');
                                } else {
                                    $('#reemplazarToogle').html(msg);
                                    student_load();
                                    monitor_load();
                                    professional_load();
                                    groupal_tracking_load();
                                }
                                $(".well.col-md-10.col-md-offset-1.reporte-seguimiento.oculto").slideDown("slow");
                                put_tracking_count( username, semestre, parseInt( get_instance() ), false );
                                
                            },
                            error: function(msg) {
                                loading_indicator.hide();
                                swal(
                                 'ERROR!',
                                 'Oops!, Se presentó un error al consultar seguimientos de personas',
                                 'error'
                                );
                            },
                        });
                        //edit_tracking_new_form();
                        //edit_groupal_tracking_new_form();
                    }
                });
            }

            /**
             * @method anadirEvento
             * @desc Function for 'sistemas' role. Adding an event
             * @param {instance} instance current instance
             * @return {string} message according if there's a period or person to look for
             */
            function anadirEvento(instance) {
                $("#personas").val('').change();

                //Select2 is able when user role is 'sistemas'
                $("#personas").select2({
                    placeholder: "Seleccionar persona",

                    language: {
                        noResults: function() {
                            return "No hay resultado";
                        },
                        searching: function() {
                            return "Buscando..";
                        }
                    }
                });
                $("#periodos").select2({
                    language: {
                        noResults: function() {
                            return "No hay resultado";
                        },
                        searching: function() {
                            return "Buscando..";
                        }
                    }
                });

                period_consult(instance, namerol);




                $('#consultar_persona').on('click', function() {

                    var id_persona = $("#personas").children(":selected").attr("value");
                    var id_semestre = $("#periodos").children(":selected").attr("value");
                    let username = $("#personas").children(":selected").data("username");
                    collapse_loaded = [];

                    if (id_persona == undefined) {

                        swal({
                            title: "Debe escoger una persona para realizar la consulta",
                            html: true,
                            type: "warning",
                            confirmButtonColor: "#d51b23"
                        });

                    } else {
                        
                        $(".well.col-md-10.col-md-offset-1.reporte-seguimiento.oculto").show();

                        $(".se-pre-con").show();
                        $("#reemplazarToogle").hide();

                        //Processing in pilos_tracking_report.php
                        let user_id = id_persona;
                        loading_indicator.show();
                        $.ajax({
                            type: "POST",
                            data: {
                                id_persona: id_persona,
                                id_semestre: id_semestre,
                                instance: get_instance(),
                                type: "consulta_sistemas"
                            },
                            url: "../../../blocks/ases/managers/pilos_tracking/pilos_tracking_report.php",
                            dataType: "text",
                            cache: "false",
                            success: function(msg) {
                                loading_indicator.hide();

                                //In case there are not records
                                if (msg == "") {
                                    $('#reemplazarToogle').html('<label> No se encontraron registros </label>');

                                } else {
                                    $('#reemplazarToogle').html(msg);
                                    $("input[name=practicante]").prop('disabled', true);
                                    $("input[name=profesional]").prop('disabled', true);
                                }
                                create_specific_counting( user_id );
                                student_load();
                                monitor_load();
                                professional_load();
                                groupal_tracking_load();
                                $(".well.col-md-10.col-md-offset-1.reporte-seguimiento.oculto").slideDown("slow");
                                put_tracking_count( username, id_semestre, parseInt( get_instance() ), false );
                                
                            },
                            error: function(msg) {
                             loading_indicator.hide();
                             swal(
                                 'ERROR!',
                                 'Oops!, Se presentó un error al cargar seguimientos de personas',
                                 'error'
                                );
                            },
                            complete: function(){
                               $(".se-pre-con").hide();
                               $("#reemplazarToogle").fadeIn();
                            }
                        });
                        //edit_tracking_new_form();
                        //edit_groupal_tracking_new_form();

                    }

                });
            }

 

            /**
             * @method send_email_new_form
             * @desc Sends an email to a monitor, given his id, text message, date, name.
             * @param {instance} instance current instance 
             * @return {void}
             */
            function send_email_new_form(instance){

                $('body').on('click', '#send_observation', function() {
                   
                    var form = $("form").serializeArray(),dataObj = {};


                    $(form).each(function(i, field){
                        dataObj[field.name] = field.value;
                    });


                    var id_register = dataObj['id_registro'];
                    var text = $("#observation_text");


                    if (text.val() == "") {
                        swal({
                            title: "Para enviar una observación debe llenar el campo correspondiente.",
                            html: true,
                            type: "error",
                            confirmButtonColor: "#d51b23"
                        });
                    } else {
                        // Gets text message and monitor id to send the email
                        var tracking_type = 'individual';
                        var monitor_code = $('.id_creado_por').find('input').val();
                        if(monitor_code == undefined){
                            monitor_code = $('.in_id_creado_por').find('input').val();
                            tracking_type = 'individual_inasistencia';
                        }
                        var date = $('.fecha').find('input').val();
                        if(date == undefined){
                            date = $('.in_fecha').find('input').val();
                        }
                        var message_to_send = text.val();
                        var semester=$("#periodos").val();
                        var place = $('.lugar').find('input').val();
                        if(place == undefined){
                            place = $('.in_lugar').find('input').val();
                        }

                        let courseid =  $('#dphpforms_block_courseid').data('info');

                        //Text area is clear again
                        var answer = "";

                        //Ajax function to send message
                        loading_indicator.show();
                        $.ajax({
                            type: "POST",
                            data: {
                                id_tracking: id_register,
                                type: "send_email_to_user",
                                form: "new_form",
                                tracking_type: tracking_type,
                                monitor_code: monitor_code,
                                date: date,
                                message_to_send: message_to_send,
                                semester:semester,
                                instance:instance,
                                place:place,
                                courseid:courseid
                            },
                            url: "../../../blocks/ases/managers/pilos_tracking/pilos_tracking_report.php",
                            async: false,
                            success: function(msg) {
                                //If it was successful...
                                loading_indicator.hide();
                                msg = JSON.parse(msg)
                                if (msg.status_code === 0) {
                                    swal({
                                        title: "Correo enviado",
                                        html: true,
                                        type: "success",
                                        confirmButtonColor: "#d51b23"
                                    });
                                    text.val("");

                                } else {
                                    console.log(`mensaje error : ${msg.error_message}`);
                                    swal({
                                        title: "Error al enviar correos",
                                        html: true,
                                        type: "error",
                                        confirmButtonColor: "#d51b23"
                                    });
                                }
                            },
                            dataType: "text",
                            cache: "false",
                            error: function(msg) {
                                loading_indicator.hide();
                                console.log( "mensaje error : " );
                                console.log( msg );
                            swal(
                                 'ERROR!',
                                 'Oops!, Se presentó un error al enviar el correo',
                                 'error'
                                );
                            },
                        });
                    }
                });


            }




 


            /**   Auxiliary functions !!
             */



            /**
             * @method period_consult(instance, namerol)
             * @desc Functionality that sets the select2 corresponding to "PERSONA" with the same ones that are associated with the selected semester
             * @param {*} instance 
             * @param {*} namerol 
             * @return {void}
             */
            function period_consult(instance, namerol) {
                $("#periodos").change(function() {
                    var chosen_period = $("#periodos").val();
                    loading_indicator.show();
                    $.ajax({
                        type: "POST",
                        data: {
                            id: chosen_period,
                            instance: instance,
                            type: "update_people"
                        },
                        url: "../../../blocks/ases/managers/pilos_tracking/pilos_tracking_report.php",
                        async: false,
                        success: function(msg) {

                            loading_indicator.hide();

                            $('#personas').empty();
                            $("#personas").select2({
                                placeholder: "Seleccionar persona",
                                language: {
                                    noResults: function() {
                                        return "No hay resultado";
                                    },
                                    searching: function() {
                                        return "Buscando..";
                                    }
                                }
                            });
                            if (namerol == 'sistemas') {
                                var index = '<option value="">Seleccionar persona</option>';

                                $("#personas").attr('selectedIndex', '-1').find("option:selected").removeAttr("selected");
                                $('#personas').append(index + msg);

                            }

                        },
                        dataType: "text",
                        cache: "false",
                        error: function(msg) {
                            loading_indicator.hide();
                            swal(
                                 'ERROR!',
                                 'Oops!, Se presentó un error al cargar personas',
                                 'error'
                                );
                        },
                    });
                });

            }

            /**
             * @method get_instance()
             * @desc Functionality to obtain the id of current instance.
             * @return {integer}
             */

            function get_instance(){
                //We get the current instance id

                var informacionUrl = window.location.search.split("&");
                for (var i = 0; i < informacionUrl.length; i++) {
                    var elemento = informacionUrl[i].split("=");
                    if (elemento[0] == "?instanceid" || elemento[0] == "instanceid") {
                        var instance = elemento[1];
                    }
                }
                return instance;
            }


        }
    };
});
