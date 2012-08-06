setTimeout(function() {
    window.onresize = function(event) {
        var winW = 630, winH = 460;
        if (document.body && document.body.offsetWidth) {
            winW = document.body.offsetWidth;
            winH = document.body.offsetHeight;
        }
        if (document.compatMode=='CSS1Compat' &&
            document.documentElement &&
            document.documentElement.offsetWidth ) {
            winW = document.documentElement.offsetWidth;
            winH = document.documentElement.offsetHeight;
        }
        if (window.innerWidth && window.innerHeight) {
            winW = window.innerWidth;
            winH = window.innerHeight;
        }
        var iframe = document.getElementById('control_panel');
        iframe.height = winH-100;
    }
    window.onresize();
    if (document.getElementsByClassName == null) {
        document.getElementsByClassName = function(className){
            var itemsfound = new Array;
            var elements = document.getElementsByTagName('*');
            for(var i=0;i<elements.length;i++){
                if(elements[i].className == className){
                    itemsfound.push(elements[i]);
                }
            }
            return itemsfound;
        }
    }

    var update_nag = document.getElementsByClassName('update-nag')[0];
    if (update_nag) {
        update_nag.style.display = 'none';
    }

    var footer = document.getElementById('footer');
    if (footer) {
        footer.style.display = 'none';
    }

    var dolly = document.getElementById('dolly');
    if (dolly) {
        dolly.style.display = 'none';
    }
    var content = document.getElementById('wpbody-content');
    if (content) {
        content.style.paddingBottom = '0px';
    }
}, 200);