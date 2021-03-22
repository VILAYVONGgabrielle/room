
    
document.addEventListener('DOMContentLoaded', function (){
    
    
    /***** Gestion range input snap *****/
        // Read value on page load
        $("#result b").html($("#rangeInputPrix").val());

        // Read value on change
        $("#rangeInputPrix").change(function(){
            $("#result b").html($(this).val());
        });
   
    

        /*********   gestion photo *******/
            // controle d'existence
    if (document.getElementById('photo')) {

        document.getElementById('photo').addEventListener('change', function (e) {
            // En jquery
            // $('#photo').on('change',function(e){
            let fichier = e.target.files;
            // console.log(fichier);
            let reader = new FileReader();

            reader.readAsDataURL(fichier[0]);

            reader.onload = function (event) {
                //  console.log(event);
                /*
                document.getElementById('preview').innerHTML = '<img src="' + event.target.result + '" alt="' + fichier[0].name + '" class="img-fluid vignette" id="placeholder">';

                $('#placeholder').on('drop', updatePhoto);
                */
               document.getElementById('placeholder').setAttribute('src', event.target.result);
               document.getElementById('placeholder').setAttribute('alt', fichier[0].name);
            }

        });
    }

    let confirmations = document.querySelectorAll('.confirm');
    // console.log(confirmations);

    for (let i = 0; i < confirmations.length; i++) {
        confirmations[i].onclick = function () {
            return (confirm('Etes-vous sûr(e) de vouloir supprimer ce produit ?'));
        }
    }

    if (document.getElementById('modaleConfirm')) {

        $('#modaleConfirm').modal('show');
    }

    // On récupère les lignes du tableau des commandes
    let lignes = document.querySelectorAll('#tabcommandes tr[data-idcmd]');

    const URL = 'http://localhost/room/';

    for (let i = 0; i < lignes.length; i++) {
        // console.log(lignes[i].dataset);
        lignes[i].style.cursor = 'pointer';
        lignes[i].addEventListener('click', function () {
            // redirection JS
            window.location.href = URL + 'admin/gestion_commandes.php?action=details&id_commande=' + this.dataset.idcmd;
        });
    }

    let selectetats = document.querySelectorAll('#tabcommandes tr[data-idcmd] td select');

    for (let i = 0; i < selectetats.length; i++) {
        selectetats[i].addEventListener('click', function (e) {
            // Je ne propage pas l'evenement click sur le parent tr de cette cellule
            // console.log(e); => MouseEvent
            e.stopPropagation();
        });
    }

    if ($('#placeholder').length > 0) {

        $('html')
            .on('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
                // console.log(e.originalEvent.pageX);
                $('#placeholder').css('border', '5px dashed orange');
            })
            .on('drop', function (e) {
                e.stopPropagation();
                e.preventDefault();
                $('#placeholder').css('border', '');
            })
            .on('dragleave', function (e) {
                if (e.originalEvent.pageX == 0 || e.originalEvent.pageY == 0) {
                    $('#placeholder').css('border', '');
                }
            });

        $('#placeholder').on('drop', updatePhoto);
    }

    function updatePhoto(e) {

        $('#placeholder').css('border', '');
        // fichier va récupérer un tableau correspondant au fichier déposé
        let fichier = e.originalEvent.dataTransfer.files;
        // je définie la propriéte files de mon input dont l'id est photo (index dans le DOM = 0)
        // console.log(fichier);
console.log(fichier);

        $('#photo')[0].files = fichier;
        // On déclenche manuellement l'evenement change

        // En jquery 
        // $('#photo').trigger('change');

        // En JS natif
        let evenement = new Event('change');
        document.getElementById('photo').dispatchEvent(evenement); // declencher addeventlister 'change' déclaré plus haut
    }


    
if ($('#prix')) {
    $('.range').next().text('10000€'); /** next élément suivant la classe range */
    $('.range').on('input', function () {
        var $set = $(this).val();
        $(this).next().text($set + '€');
    });
}

}); 
