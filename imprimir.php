<?php

/**
 * Gerador de Plano de estudos
 *
 * @version 1.0.0
 * @copyright 2016 Guilherme Diniz  http://guilhermediniz.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package studyguide
 */
require_once("../../config.php");
require_once($CFG->dirroot. '/course/lib.php');

global $DB,$USER;

$param_id = optional_param('c', 0, PARAM_INT);

if($param_id === 0){
    print_error('unspecifycourseid', 'error');
}

//COURSE SELECTED
$course = $DB->get_record('course', array('id'=> $param_id), '*', MUST_EXIST);
$context =  get_context_instance(CONTEXT_COURSE,$course->id);

//VERIFY IF IS A ENROLLED STUDENT
if(!is_enrolled($context,$USER)){
	print_error('unspecifycourseid', 'error');
}

//VERIFY IF COURSE REQUIRES BE A LOGGED USER
require_login($course);

//SECTIONS OF THE COURSE
$sections = $DB->get_records( 'course_sections', array( 'course' => $course->id ) );
array_shift($sections);

require_once($CFG->dirroot. '/local/studyguide/template/html.inc.php');

$dataCursoInicio= date('d/m/Y',$course->startdate);
$dataCursoFim   = date('d/m/Y',$course->enddate);

$nomeSecoes = array();
foreach ($sections as $key => $section){
    $nomeSecoes[] = $section->name?:'Tópico '.$section->section;
}

echo '<link href="/moodle/local/studyguide/template/css/bootstrap.min.css" rel="stylesheet"  type="text/css" />';
echo html_writer::start_div('container');
echo html_writer::start_div('row');

echo html_writer::start_div('col-xs-12');

echo html_writer::start_tag('h4')
    ."{$course->fullname} | Início : {$dataCursoInicio} Término : {$dataCursoFim}"
    .html_writer::end_tag('h4');

echo html_writer::end_div();

    echo html_writer::start_div('col-xs-12');
    $dataInicio = DateTime::createFromFormat('d/m/Y',$_GET['i']);
    $dataFim    = DateTime::createFromFormat('d/m/Y',$_GET['f']);

    $diff=$dataFim->diff($dataInicio);

    $semanas = (int) ($diff->days/7);
    $topicosSemana = (int) (count($nomeSecoes)/$semanas);

    $dias = (int) $diff->days % 7;
    $topicosSobra = (int) (count($nomeSecoes) % $semanas);

    $periodo = "| {$semanas} semana(s)";
    $periodo = ($dias>0)?$periodo." {$dias} dia(s)":$periodo;
    echo "<h4>Período de estudo => {$dataInicio->format('d/m/Y')} - {$dataFim->format('d/m/Y')} $periodo</h4>";

    if($topicosSemana == 0){
		$semanas = count($nomeSecoes);
		$topicosSemana = (int) (count($nomeSecoes)/count($nomeSecoes));
		$topicosSobra = 0;
    }

    for($i=0; $i < $semanas; $i ++){
        $tmp = $i + 1;
        $semana = "{$tmp}º semana | " . $dataInicio->format('d/m/Y');
        $dataInicio->add(new DateInterval('P7D'));
        $semana .= ' - '.$dataInicio->format('d/m/Y');

        $painelTmp = str_replace('{{SEMANAS}}',$semana,$painel);

        $topicos = '<ol>';
        for($j=0;$j < $topicosSemana; $j++){
            $tmp = $j + ($i * $topicosSemana);
            $topicos .= "<li>{$nomeSecoes[$tmp]}</li>";
        }
        $topicos .= '</ol>';

        echo str_replace('{{TOPICOS}}',$topicos,$painelTmp);
    }

    if($topicosSobra > 0){
        $tmp = $semanas + 1;
        $semana = "{$tmp}º semana | " . $dataInicio->format('d/m/Y');

        if($dias == 0){
            $dias = 7;
        }

        $dataInicio->add(new DateInterval('P'.$dias.'D'));
        $semana .= ' - '.$dataInicio->format('d/m/Y');

        $painelTmp = str_replace('{{SEMANAS}}',$semana,$painel);

        $topicos = '<ol>';
        for($j=0;$j < $topicosSobra; $j++){
            $tmp = $j + ($semanas * $topicosSemana);
            $topicos .= "<li>{$nomeSecoes[$tmp]}</li>";
        }
        $topicos .= '</ol>';

        echo str_replace('{{TOPICOS}}',$topicos,$painelTmp);
    }

    echo $imprimir;

echo html_writer::end_div();
echo html_writer::end_div();
