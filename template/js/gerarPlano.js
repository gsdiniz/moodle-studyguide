/**
 * Created by guilherme on 09/12/16.
 */
(function(){
    var gerarBtn = document.getElementById('gerar');
    var periodoInicio = document.getElementById('periodo-inicio');
    var periodoFinal  = document.getElementById('periodo-final');
    var hoje = new Date();
    var arrayLimit = periodoFinal.dataset.limit.split('/');
    var limit = new Date(arrayLimit[2],arrayLimit[1]-1,arrayLimit[0]);
    arrayLimit = null;

    gerarBtn.addEventListener('click',function(){

        if(periodoInicio.value.length < 10 || periodoFinal.value.length < 10){
            alert('Preencha a(s) data(s) no formato DD/MM/YYYY, por favor verificar');
            return;
        }

        var arrayPeriodoInicio = periodoInicio.value.split('/');
        var dateInicio = new Date(arrayPeriodoInicio[2],arrayPeriodoInicio[1]-1,arrayPeriodoInicio[0])
        var arrayPeriodoFinal = periodoFinal.value.split('/');
        var dateFinal = new Date(arrayPeriodoFinal[2],arrayPeriodoFinal[1]-1,arrayPeriodoFinal[0])

        if(isNaN(dateInicio.getTime()) || isNaN(dateFinal.getTime())){
            alert('Data(s) informada(s) é(são) inválida(s), por favor verificar');
            return;
        }

        if(
            parseInt(hoje.getFullYear().toString()+hoje.getMonth().toString()+hoje.getDate().toString().padStart(2,"0")) >
            parseInt(dateInicio.getFullYear().toString()+dateInicio.getMonth().toString()+dateInicio.getDate().toString().padStart(2,"0"))
        ){
            alert('Data de início deve ser maior ou igual '+hoje.toLocaleString().substr(0,10)+', por favor verificar');
            return;
        }

        if( dateInicio.getTime() > dateFinal.getTime() ){
            alert('Data de término deve ser maior ou igual '+dateInicio.toLocaleString().substr(0,10)+', por favor verificar');
            return;
        }

        if( dateFinal.getTime() > limit.getTime() ){
            alert('Data de término deve ser menor ou igual '+limit.toLocaleString().substr(0,10)+', por favor verificar');
            return;
        }

        var f = document.createElement("form");
        f.setAttribute('method',"post");
        f.setAttribute('action',"index.php?"+window.location.search.substr(1));

        var i = document.createElement("input");
        i.setAttribute('type',"hidden");
        i.setAttribute('name',"dataInicio");
        i.setAttribute('value',periodoInicio.value);

        var e = document.createElement("input");
        e.setAttribute('type',"hidden");
        e.setAttribute('name',"dataFim");
        e.setAttribute('value',periodoFinal.value);

        var checkboxes = document.getElementsByClassName('multiselect-checkbox');

        for(var tmp=0;tmp<checkboxes.length;tmp++){
            var $this = checkboxes[tmp];
            if($this.checked){
                var c = document.createElement("input");
                c.setAttribute('type',"hidden");
                c.setAttribute('name',"topicos[]")
                c.setAttribute('value',$this.value);
                f.appendChild(c);
            }
        };

        f.appendChild(i);
        f.appendChild(e);
        document.body.appendChild(f);
        f.submit();
    })

    var checkboxes = document.getElementsByClassName('multiselect-checkbox');
    for(var i=0;i<checkboxes.length;i++){
        var $this = checkboxes[i];
        $this.addEventListener('click',function(){
            var $this = this;
            if($this.checked){
                $this.parentNode.className += "multiselect-on";
                return;
            }
            $this.parentNode.className = "";
        })
    };
})();

var datepicker = new Datepickk();
function dataInicio(){
    var ontem = new Date();
    ontem.setDate(ontem.getDate() -1);
    datepicker.startDate = ontem;
    datepicker.minDate = ontem;
    datepicker.title = 'Escolha a data de início';
    datepicker.closeOnSelect = true;
    datepicker.onSelect = function(){
        document.getElementById('periodo-inicio').value = this.toLocaleDateString()
        datepicker.unselectAll();
    };
    datepicker.show();
}

function dataFim(){
    if(document.getElementById('periodo-inicio').value != ''){
        var dataMinima = document.getElementById('periodo-inicio').value.split('/');
        datepicker.startDate = new Date(dataMinima[2],dataMinima[1]-1,dataMinima[0]);
        datepicker.minDate = new Date(dataMinima[2],dataMinima[1]-1,dataMinima[0]);
        datepicker.title = 'Escolha a data de término';
        datepicker.closeOnSelect = true;
        datepicker.onSelect = function(){
            document.getElementById('periodo-final').value = this.toLocaleDateString()
            datepicker.unselectAll();
        };
        datepicker.show();
    }else{
        alert('Informe uma data de início primeiro')
    }
}
