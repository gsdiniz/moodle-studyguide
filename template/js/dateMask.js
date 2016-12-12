/**
 * Created by guilherme on 09/12/16.
 */
(function(){
    var periodoInicio = document.getElementById('periodo-inicio');
    var periodoFinal  = document.getElementById('periodo-final');

    var codes = [
        48,49,50,51,52,53,56,57,58,59,60,96,97,98,99,100,101,102,103,104,105,106
    ];

    codes.inArray = function(value){
        var $value = value;
        for(var i=0;i<this.length;i++){
            if(this[i] === value)
                return true;
        }
        return false;
    };

    var dateMask = function(event){
        var key = event.keyCode || 0;
        // allow backspace, tab, delete, enter, arrows, numbers and keypad numbers ONLY
        // home, end, period, and numpad decimal

        var result = key == 8 ||
            key == 9 ||
            key == 13 ||
            key == 46 ||
            key == 110 ||
            key == 190 ||
            (key >= 35 && key <= 40) ||
            (key >= 48 && key <= 57) ||
            (key >= 96 && key <= 105);

        if(!result){
            event.preventDefault();
            return;
        }

        if(event.srcElement.value.length >= 10
            &&
            (
                (key >= 48 && key <= 57) ||
                (key >= 96 && key <= 105)
            )
        ) {
            event.preventDefault();
            return;
        }

        if( (key >= 48 && key <= 57) || (key >= 96 && key <= 105) ){
            var data = '';
            var value = event.srcElement.value.replace('/','');
            var value = value.replace('/','');

            var parte1 = value.substr(0,2);
            var parte2 = value.substr(2,2);
            var parte3 = value.substr(4);

            if(parte1.length>0)
                data += (parte1.length < 2) ? parte1 : parte1 + '/';

            if(parte2.length>0)
                data += (parte2.length < 2) ? parte2 : parte2 + '/';

            if(parte3.length>0)
                data += parte3;

            event.srcElement.value = data;
        }
    };

    periodoInicio.addEventListener('keydown',dateMask);
    periodoFinal.addEventListener('keydown',dateMask);
})();