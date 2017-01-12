<?php

$periodoDeEstudo = <<<EOD
<div>
    <div class="col-md-6">
        De: 
        <div class="input-group">
            <span class="input-group-addon" id="basic-addon1" onclick="dataInicio()">
                <i class="fa fa-calendar" aria-hidden="true"></i>
            </span>
            <input type="text" id="periodo-inicio" class="form-control">
        </div>
    </div>
    <div class="col-md-6">
        Até: 
        <div class="input-group">
            <span class="input-group-addon" id="basic-addon1" onclick="dataFim()">
                <i class="fa fa-calendar" aria-hidden="true"></i>
            </span>
            <input type="text" id="periodo-final" data-limit="||LIMIT||" class="form-control">
        </div>
    </div>
    <div>
        <br/>
        <button id='gerar' class="text-center btn btn-block btn-primary">Gerar</button>
    </div>
</div>
EOD;

$painel = <<<EOD
<div class="panel panel-info"> 
    <div class="panel-heading"> 
        <h3 class="panel-title">
            {{SEMANAS}}
        </h3> 
    </div> 
    <div class="panel-body"> 
        {{TOPICOS}} 
    </div> 
</div>
EOD;

$imprimir = <<<EOD
<div class="text-center hidden-print"> 
     <button onclick="print()">Imprimir</button>
     <script>window.addEventListener('load',function(){print()});</script>
</div>
EOD;

function botaoImprimir($courseId,$dataInicio,$dataFim,$topicos){
	$dataInicio = urlencode($dataInicio);
	$dataFim = urlencode($dataFim);
    $topicos = urlencode(implode(',',$topicos));
	return <<<EOD
        <a href='imprimir.php?c=$courseId&i=$dataInicio&f=$dataFim&t=$topicos' target="_blank" class="text-center btn btn-info">Imprimir</a>
EOD;
}

function botaoSalvar($courseId,$dataInicio,$dataFim,$topicos){
	$dataInicio = urlencode($dataInicio);
	$dataFim = urlencode($dataFim);
    $topicos = urlencode(implode(',',$topicos));
	return <<<EOD
        <a href='salvar.php?c=$courseId&i=$dataInicio&f=$dataFim&t=$topicos' target="_blank" class="text-center btn btn-primary">Salvar</a>
EOD;
}

function cabecalhoImpressaoPdf($curso,$inicioCurso,$fimCurso,$grupos,$periodoInicio,$periodoFim,$semanas,$fim){
    return <<<EOD
    <table class='table table-bordered table-condensed'>
        <tbody>
            <tr>
                <th colspan='4'><img src="template/logomarca.jpg" /></th>
            </tr>
            <tr>
                <th>Curso</th>
                <td colspan='3'>$curso</td>
            </tr>
            <tr>
                <th>Início do Curso</th>
                <td>$inicioCurso</td>
                <th>Fim do Curso</th>
                <td>$fimCurso</td>
            </tr>
            <tr>
                <th>Grupos de estudo</th>
                <td colspan='3'>$grupos</td>
            </tr>
            <tr>
                <th>Período de estudo</th>
                <td colspan='3'>
                    <table style='margin:0;' class='table table-bordered table-condensed'>
                        <tbody>
                            <tr>
                                <th>Início</th>
                                <td>$periodoInicio</td>
                                <th>Fim</th>
                                <td>$periodoFim</td>
                                <th>Semanas</th>
                                <td>$semanas</td>
                                <th>Dias</th>
                                <td>$fim</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
EOD;
}