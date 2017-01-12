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

global $DB, $USER, $CFG;

$param_id = optional_param('c', 0, PARAM_INT);

if ($param_id === 0) {
    print_error('unspecifycourseid', 'error');
}

//COURSE SELECTED
$course = $DB->get_record('course', array('id' => $param_id), '*', MUST_EXIST);
$context = get_context_instance(CONTEXT_COURSE, $course->id);

//VERIFY IF IS A ENROLLED STUDENT
if (!is_enrolled($context, $USER)) {
    print_error('usernotincourse', 'error');
}

//VERIFY IF COURSE REQUIRES BE A LOGGED USER
require_login($course);

//GET COURSE NAME IF USER IS IN A COURSE
$groupName = null;
if(!empty($USER->groupmember)){
    foreach ($USER->groupmember as $groups){
        $grupos = implode(',',$groups);

        $groupName = $DB->get_records_sql(
            "SELECT `name` FROM  {$CFG->prefix}groups gp 
        INNER JOIN {$CFG->prefix}groups_members gpm ON gpm.groupid = gp.id
        WHERE gp.courseid = ? AND gpm.userid = ? AND gpm.groupid IN (".$grupos.")",
            array($course->id,$USER->id)
        );
        $groupName = implode(',',array_keys($groupName));
    }
}

//GET DATE START/END OF ENROL
$user_enroll_data = $DB->get_record_sql(
    'SELECT timestart,timeend FROM `'.$CFG->prefix.'user_enrolments` 
inner join '.$CFG->prefix.'enrol on '.$CFG->prefix.'user_enrolments.enrolid = '.$CFG->prefix.'enrol.id
where '.$CFG->prefix.'user_enrolments.userid = ? and '.$CFG->prefix.'enrol.courseid = ?',
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
    if(in_array($section->id,explode(',',$_GET['t']))){
        $nomeSecoes[] = $section->name ?: 'Tópico ' . $section->section;
    }
}

echo '<link href="template/css/bootstrap.min.css" rel="stylesheet"  type="text/css" />';
echo html_writer::start_div('container');
echo html_writer::start_div('row');

echo html_writer::start_div('col-xs-12');

echo html_writer::start_div('col-xs-12');
$dataInicio = DateTime::createFromFormat('d/m/Y', $_GET['i']);
$dataFim = DateTime::createFromFormat('d/m/Y', $_GET['f']);
$balance = false;

$diff = $dataFim->diff($dataInicio);

$semanas = (int)($diff->days / 7);
$topicosSemana = (int)(count($nomeSecoes) / $semanas);

$dias = (int)$diff->days % 7;
$topicosSobra = (int)(count($nomeSecoes) % $semanas);

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

echo cabecalhoImpressaoPdf(
    $course->fullname,
    $dataCursoInicio,
    $dataCursoFim,
    $groupName,
    $dataInicio->format('d/m/Y'),
    $dataFim->format('d/m/Y'),
    $semanas,
    $dias
);

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
