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

$param_id = optional_param('id', 0, PARAM_INT);

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
        $groupName = 'Grupo(s): '.implode(',',array_keys($groupName));
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

$PAGE->set_url(new moodle_url('/local/studyguide/index.php', array('id' => $_GET['id'])));
$PAGE->set_title('Plano de Estudo do ' . $course->fullname);
$PAGE->set_heading('Plano de Estudo do ' . $course->fullname);
$PAGE->set_pagelayout('course');
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/local/studyguide/template/css/style.css'), true);
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/local/studyguide/template/css/font-awesome.min.css'), true);
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/local/studyguide/template/css/datepickk.min.css'), true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/studyguide/template/js/dateMask.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/studyguide/template/js/datepickk.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/studyguide/template/js/gerarPlano.js'));

require_once($CFG->dirroot . '/local/studyguide/template/html.inc.php');

$dataCursoInicio = date('d/m/Y', $user_enroll_data->timestart);
$dataCursoFim = null;

if($user_enroll_data->timeend > 0){
    $dataCursoFim = date('d/m/Y', $user_enroll_data->timeend);
}

echo $OUTPUT->header();

echo html_writer::start_div('container-sg', array('id' => 'studyguide'));
echo html_writer::start_div('row-sg');

echo html_writer::start_div('col-xs-12');

echo html_writer::start_tag('h4');
echo $dataCursoFim != null ? "{$course->fullname} | Início : {$dataCursoInicio} Término : {$dataCursoFim}":"{$course->fullname} | Início : {$dataCursoInicio}";
echo html_writer::end_tag('h4');

if($groupName != null) {
    echo html_writer::start_tag('h5');
    echo $groupName;
    echo html_writer::end_tag('h5');
}

echo html_writer::end_div();

echo html_writer::start_tag('form',['onsubmit'=>'return false;','class'=>'form']);

echo html_writer::start_div('col-xs-6', array('id' => 'atividades'));

echo html_writer::start_tag('h4') . 'Atividades' . html_writer::end_tag('h4');

$nomeSecoes = array();
echo html_writer::start_div('multiselect');
foreach ($sections as $key => $section) {
    $nomesecao = $section->name ?: 'Tópico ' . $section->section;
    $checked = 'checked';
    $class = 'multiselect-on';

    if(!empty($_POST) && !in_array($section->id,array_values($_POST['topicos']))){
        $checked = '';
        $class = '';
    }

    echo "<div class='{$class}'><input {$checked} class='multiselect-checkbox' 
            type=\"checkbox\" name=\"topico[]\" value=\"{$section->id}\" />{$nomesecao}</div>";

    if(!empty($_POST) && in_array($section->id,array_values($_POST['topicos']))){
        $nomeSecoes[] = $nomesecao;
    }
}
echo html_writer::end_div();

echo html_writer::end_div();

echo html_writer::start_div('col-xs-6', array('id' => 'periodo'));

echo html_writer::start_tag('h4') . 'Defina um perídodo para estudo' . html_writer::end_tag('h4');

echo str_replace('||LIMIT||', $dataCursoFim, $periodoDeEstudo);

echo html_writer::end_tag('form');

echo html_writer::end_div();

if (!empty($_POST)) {
    echo html_writer::start_div('col-xs-12');
    echo '<p>&nbsp;</p>';
    $dataInicio = DateTime::createFromFormat('d/m/Y', $_POST['dataInicio']);
    $dataFim = DateTime::createFromFormat('d/m/Y', $_POST['dataFim']);
    $balance = false;

    $diff = $dataFim->diff($dataInicio);

    $semanas = (int)($diff->days / 7);
    $topicosSemana = (int)(count($nomeSecoes) / $semanas);

    $dias = (int)$diff->days % 7;
    $topicosSobra = (int)(count($nomeSecoes) % $semanas);

    $periodo = ($dias > 0) ? "| {$semanas} semana(s) {$dias} dia(s)" : "| {$semanas} semana(s)";
    echo "<h3>Período de estudo => {$dataInicio->format('d/m/Y')} - {$dataFim->format('d/m/Y')} $periodo</h3>";
    echo '<p>&nbsp;</p>';

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

        $topicos = '<ul>';

        if ($balance && $topicosSobra >= $contadorBalanceado) {
            $contadorBalanceado++;
        }

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
        echo '<hr/>';

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

        $topicos = '<ul>';
        for ($j = 0; $j < $topicosSobra; $j++) {
            $tmp = $j + ($semanas * $topicosSemana);
            $topicos .= "<li>{$nomeSecoes[$tmp]}</li>";
        }
        $topicos .= '</ul>';

        echo str_replace('{{TOPICOS}}', $topicos, $painelTmp);
    }

    echo html_writer::end_div();

    echo html_writer::start_div('col-xs-12', array('style' => 'text-align:center'))
        . botaoImprimir($course->id, $_POST['dataInicio'], $_POST['dataFim'], $_POST['topicos'])
        . botaoSalvar($course->id, $_POST['dataInicio'], $_POST['dataFim'], $_POST['topicos'])
        . html_writer::end_div();
}

echo html_writer::end_div();
echo html_writer::end_div();

echo $OUTPUT->footer();
