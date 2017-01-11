<?php

$periodoDeEstudo = <<<EOD
<div>
    <div class="col-md-6">
        De: 
        <div class="input-group">
            <span class="input-group-addon" id="basic-addon1">
                <i class="fa fa-calendar" aria-hidden="true"></i>
            </span>
            <input type="text" id="periodo-inicio" class="form-control">
        </div>
    </div>
    <div class="col-md-6">
        Até: 
        <div class="input-group">
            <span class="input-group-addon" id="basic-addon1">
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

function botaoImprimir($courseId,$dataInicio,$dataFim){
	$dataInicio = urlencode($dataInicio);
	$dataFim = urlencode($dataFim);
	return <<<EOD
        <a href='imprimir.php?c=$courseId&i=$dataInicio&f=$dataFim' target="_blank" class="text-center btn btn-info">Imprimir</a>
EOD;
}

function botaoSalvar($courseId,$dataInicio,$dataFim){
	$dataInicio = urlencode($dataInicio);
	$dataFim = urlencode($dataFim);
	return <<<EOD
        <a href='salvar.php?c=$courseId&i=$dataInicio&f=$dataFim' target="_blank" class="text-center btn btn-primary">Salvar</a>
EOD;
}
