/**
 * Created by stefan on 24.02.16.
 */
/* called before body is loaded */
function snk_init(){
    /* enable stylesheet that hides all termin details, doesn't work in safari */
	// TODO: fix this
    //document.getElementsByTagName('style')[0].media = 'screen';
}

/* called after DOM is complete */
function snk_finish( snk_request_url ){
    /* add drop-down behavior */
    document.getElementById("snk-auswahlbox").onchange = function(){
        location.href = (snk_request_url
                //.replace(/&upto=[^&]*/g, "")
            +'&ebenenup=')
            + document.getElementById('snk-auswahlbox')
                .options[ document.getElementById('snk-auswahlbox').selectedIndex ]
                .value;
    };

    /* replace drop-down form button by pure text */
    document.getElementById("snk-anzeigen").innerHTML = "anzeigen";

    /* hide all termin details, works in safari */
    var snk_termin_infos = document.querySelectorAll(".snk-termin-infos");
    for( var i = 0; i < snk_termin_infos.length; i++ ){
        snk_termin_infos.item(i).style.display = 'none';
    }
}

function snk_show_termin( termin_id, link ){
    var details = document.getElementById('snk-termin-'+termin_id);
    if(!this.putstyle_w3c){
        this.putstyle_w3c = 'table-row';
    }
    this.putstyle_ie = 'block';
    if( details.style.display == this.putstyle_w3c || details.style.display == this.putstyle_ie ){
        details.style.display = 'none';
        link.className = link.old_class;
    } else {
        try{
            details.style.display = this.putstyle_w3c;
        }catch(e){
            details.style.display = this.putstyle_ie;
        }
        link.old_class = link.className;
        link.className = 'snk-termin-link-opened';
    }
    return false;
}

snk_init();

document.addEventListener('DOMContentLoaded', function(){
    return snk_finish('');
}, false);
