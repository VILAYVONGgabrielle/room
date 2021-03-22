document.addEventListener('DOMContentLoaded', function (){
/* datepicker from to in form */
console.log('coucou');

var dateFormat = "mm/dd/yy",
from = $("#date_arrivee")
    .datepicker({
        minDate: 0,
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 2,
        closeText: 'Fermer',
        prevText: 'Précédent',
        nextText: 'Suivant',
        /*dateFormat: "dd/mm/yy",*/
        monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
        dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
        dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
        weekHeader: 'Sem.',
        firstDay: 1
    })

    .on("change", function () {
        to.datepicker("option", "minDate", getDate(this));
    }),
to = $("#date_depart").datepicker({
    minDate: 0,
    defaultDate: "+1w",
    changeMonth: true,
    numberOfMonths: 2,
    closeText: 'Fermer',
    prevText: 'Précédent',
    nextText: 'Suivant',
    /*dateFormat: "dd/mm/yy",*/
    monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
    monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
    dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
    dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
    dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
    weekHeader: 'Sem.',
    firstDay: 1
})

    .on("change", function () {
        from.datepicker("option", "maxDate", getDate(this));
    });

function getDate(element) {
var date;
try {
   /* var parts = element.value.split("/");
    var dt = parseInt(parts[1]) + "/" + parseInt(parts[0]) + "/" + parseInt(parts[2]);*/
    date = $.datepicker.parseDate(dateFormat, element.value);
} catch (error) {
    date = null;
}
return date;
}

});

