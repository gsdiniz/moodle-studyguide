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

$param_id = optional_param('id', 0, PARAM_INT);

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

$PAGE->set_url(new moodle_url('/local/studyguide/index.php', array('id' => $_GET['id'])));
$PAGE->set_title('Plano de Estudo do '. $course->fullname);
$PAGE->set_heading('Plano de Estudo do '. $course->fullname);
$PAGE->set_pagelayout('course');
//$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/local/studyguide/template/css/bootstrap.min.css'),true);
$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/local/studyguide/template/css/font-awesome.min.css'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot. '/local/studyguide/template/js/dateMask.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot. '/local/studyguide/template/js/gerarPlano.js'));

require_once($CFG->dirroot. '/local/studyguide/template/html.inc.php');

$dataCursoInicio= date('d/m/Y',$course->startdate);
$dataCursoFim   = date('d/m/Y',$course->enddate);

echo $OUTPUT->header();

echo html_writer::start_div('container');
echo html_writer::start_div('row');

echo html_writer::start_div('col-xs-12');

echo html_writer::start_tag('h4')
    ."{$course->fullname} | Início : {$dataCursoInicio} Término : {$dataCursoFim}"
    .html_writer::end_tag('h4');

echo html_writer::end_div();

echo html_writer::start_div('col-xs-6',array('id'=>'atividades'));

echo html_writer::start_tag('h4').'Atividades'.html_writer::end_tag('h4');

$nomeSecoes = array();

foreach ($sections as $key => $section){
    $nomeSecoes[] = $section->name?:'Tópico '.$section->section;
}

echo html_writer::alist($nomeSecoes);

echo html_writer::end_div();

echo html_writer::start_div('col-xs-6');

echo html_writer::start_tag('h4').'Defina um perídodo para estudo'.html_writer::end_tag('h4');

echo $periodoDeEstudo;

echo html_writer::end_div();

if(!empty($_POST)) {
    echo html_writer::start_div('col-xs-12');
    echo '<p>&nbsp;</p>';
    $dataInicio = DateTime::createFromFormat('d/m/Y',$_POST['dataInicio']);
    $dataFim    = DateTime::createFromFormat('d/m/Y',$_POST['dataFim']);

    $diff=$dataFim->diff($dataInicio);

    $semanas = (int) ($diff->days/7);
    $topicosSemana = (int) (count($nomeSecoes)/$semanas);

    $dias = (int) $diff->days % 7;
    $topicosSobra = (int) (count($nomeSecoes) % $semanas);

    $periodo = "| {$semanas} semana(s)";
    $periodo = ($dias>0)?$periodo." {$dias} dia(s)":$periodo;
    echo "<h3>Período de estudo => {$dataInicio->format('d/m/Y')} - {$dataFim->format('d/m/Y')} $periodo</h3>";
    echo '<p>&nbsp;</p>';
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
        echo '<hr/>';
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

    echo html_writer::end_div();

	echo html_writer::start_div('col-xs-12',array('style'=>'text-align:center'))
    .botaoImprimir($course->id,$_POST['dataInicio'],$_POST['dataFim'])
	.botaoSalvar($course->id,$_POST['dataInicio'],$_POST['dataFim'])
    .html_writer::end_div();
}

echo html_writer::end_div();
echo html_writer::end_div();

echo $OUTPUT->footer();