<?php
/**
 * Gerador de Plano de estudos
 *
 * @version 1.0.0
 * @copyright 2016 Guilherme Diniz  http://guilhermediniz.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package studyguide
 */

function local_studyguide_extend_navigation(global_navigation $navigation){
    global $PAGE, $COURSE;

    //TODAS AS GLOBAIS REGISTRADAS
    //var_dump(array_keys($GLOBALS));

    if($PAGE->context->contextlevel == CONTEXT_COURSE){
        $url = new moodle_url('/local/studyguide/index.php?id='.$COURSE->id);
        /*
         * ADD LINK PARA O GUIA DE ESTUDO NO NAV BLACK DO CURSO
         */
        $curso = $navigation->find($COURSE->id, global_navigation::TYPE_COURSE);
        $curso->add('Plano de Estudo', $url, global_navigation::TYPE_CUSTOM, 'guia','guiaestudo');
    }
}