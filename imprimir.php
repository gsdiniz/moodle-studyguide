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
require_once($CFG->dirroot . '/course/lib.php');

global $DB, $USER;

$param_id = optional_param('c', 0, PARAM_INT);

if ($param_id === 0) {
    print_error('unspecifycourseid', 'error');
}

//COURSE SELECTED
$course = $DB->get_record('course', array('id' => $param_id), '*', MUST_EXIST);
$context = get_context_instance(CONTEXT_COURSE, $course->id);

//VERIFY IF IS A ENROLLED STUDENT
if (!is_enrolled($context, $USER)) {
    print_error('unspecifycourseid', 'error');
}

//VERIFY IF COURSE REQUIRES BE A LOGGED USER
require_login($course);


//GET DATE START/END OF ENROL
$user_enroll_data = $DB->get_record_sql(
    'SELECT timestart,timeend FROM `mdl_user_enrolments` 
inner join mdl_enrol on mdl_user_enrolments.enrolid = mdl_enrol.id
where mdl_user_enrolments.userid = ? and mdl_enrol.courseid = ?',
    array($USER->id, $course->id)
);

//SECTIONS OF THE COURSE
$sections = $DB->get_records('course_sections', array('course' => $course->id));
array_shift($sections);

require_once($CFG->dirroot . '/local/studyguide/template/html.inc.php');

$dataCursoInicio = date('d/m/Y', $user_enroll_data->timestart);
$dataCursoFim = null;

if($user_enroll_data->timeend > 0){
    $dataCursoFim = date('d/m/Y', $user_enroll_data->timeend);
}

$nomeSecoes = array();
foreach ($sections as $key => $section) {
    $nomeSecoes[] = $section->name ?: 'Tópico ' . $section->section;
}

echo '<link href="template/css/bootstrap.min.css" rel="stylesheet"  type="text/css" />';
echo html_writer::start_div('container');
echo html_writer::start_div('row');

echo html_writer::start_div('col-xs-12');

echo html_writer::start_tag('h4');
echo $dataCursoFim != null ? "{$course->fullname} | Início : {$dataCursoInicio} Término : {$dataCursoFim}":"{$course->fullname} | Início : {$dataCursoInicio}";
echo html_writer::end_tag('h4');

echo html_writer::end_div();

echo html_writer::start_div('col-xs-12');
$dataInicio = DateTime::createFromFormat('d/m/Y', $_GET['i']);
$dataFim = DateTime::createFromFormat('d/m/Y', $_GET['f']);
$balance = false;

$diff = $dataFim->diff($dataInicio);

$semanas = (int)($diff->days / 7);
$topicosSemana = (int)(count($nomeSecoes) / $semanas);

$dias = (int)$diff->days % 7;
$topicosSobra = (int)(count($nomeSecoes) % $semanas);

$periodo = ($dias > 0) ? "| {$semanas} semana(s) {$dias} dia(s)" : "| {$semanas} semana(s)";
echo "<h4>Período de estudo => {$dataInicio->format('d/m/Y')} - {$dataFim->format('d/m/Y')} $periodo</h4>";

if ($topicosSemana == 0) {
    $semanas = count($nomeSecoes);
    $topicosSemana = (int)(count($nomeSecoes) / count($nomeSecoes));
    $topicosSobra = 0;
}

if ($topicosSobra > $dias) {
    $balance = true;
    $topicosSemana++;
    $contadorBalanceado = 0;
}

for ($i = 0; $i < $semanas; $i++) {
    $tmp = $i + 1;
    $semana = "{$tmp}º semana | " . $dataInicio->format('d/m/Y');
    $dataInicio->add(new DateInterval('P6D'));
    $semana .= ' - ' . $dataInicio->format('d/m/Y');

    $painelTmp = str_replace('{{SEMANAS}}', $semana, $painel);

    if ($balance && $topicosSobra >= $contadorBalanceado) {
        $contadorBalanceado++;
    }

    $topicos = '<ul>';
    for ($j = 0; $j < $topicosSemana; $j++) {
        $tmp = $j + ($i * $topicosSemana);

        if ($balance && $contadorBalanceado > $topicosSobra) {
            $tmp += ($contadorBalanceado -1);
        }

        $topicos .= "<li>{$nomeSecoes[$tmp]}</li>";
    }
    $topicos .= '</ul>';

    echo str_replace('{{TOPICOS}}', $topicos, $painelTmp);
    $dataInicio->add(new DateInterval('P1D'));

    if ($balance && $topicosSobra == $contadorBalanceado) {
        $topicosSemana--;
    }
}

if (!$balance && $topicosSobra > 0) {
    $tmp = $semanas + 1;
    $semana = "{$tmp}º semana | " . $dataInicio->format('d/m/Y');

    if ($dias == 0) {
        $dias = 6;
    }

    $dataInicio->add(new DateInterval('P' . $dias . 'D'));
    $semana .= ' - ' . $dataInicio->format('d/m/Y');

    $painelTmp = str_replace('{{SEMANAS}}', $semana, $painel);

    $topicos = '<ol>';
    for ($j = 0; $j < $topicosSobra; $j++) {
        $tmp = $j + ($semanas * $topicosSemana);
        $topicos .= "<li>{$nomeSecoes[$tmp]}</li>";
    }
    $topicos .= '</ol>';

    echo str_replace('{{TOPICOS}}', $topicos, $painelTmp);
}

//VARIÁVEL COM SCRIPT PARA IMPRIMIR E BOTÃO PARA IMPRIMIR
echo $imprimir;

echo html_writer::end_div();
echo html_writer::end_div();
