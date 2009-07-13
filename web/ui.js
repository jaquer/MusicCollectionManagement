function showAdvanced() {
  document.getElementById('show-advanced').style.display = 'none';
  document.getElementById('advanced').style.display = 'block';
}

function clickRadio(sId, sState) {
    
    /* Radio button */
    document.getElementById(sId + '-' + sState).checked = true;
    
    /* Images */
    oImgCover = document.getElementById(sId + '-cover');
    oImgAdd = document.getElementById(sId + '-img-add');
    oImgRem = document.getElementById(sId + '-img-rem');
    oImgUnd = document.getElementById(sId + '-img-und');

    if (sState == 'add') {
        oImgCover.className = 'cover cover-add';
        oImgAdd.src = 'images/add.png';
        oImgRem.src = 'images/remove-off.png';
        oImgUnd.src = 'images/undecided-off.png';
    }
    else if (sState =='rem') {
        oImgCover.className = 'cover cover-rem';
        oImgAdd.src = 'images/add-off.png';
        oImgRem.src = 'images/remove.png';
        oImgUnd.src = 'images/undecided-off.png';
    }
    else if (sState == 'und') { 
        oImgCover.className = 'cover cover-und';
        oImgAdd.src = 'images/add-off.png';
        oImgRem.src = 'images/remove-off.png';
        oImgUnd.src = 'images/undecided.png';
    }
}

// Code from http://dynamic-tools.net/toolbox/isMouseLeaveOrEnter/
function isMouseLeaveOrEnter (e, handler) { 
    if (e.type != 'mouseout' && e.type != 'mouseover')
        return false;

    var reltg = e.relatedTarget
        ? e.relatedTarget
        : e.type == 'mouseout'
            ? e.toElement
            : e.fromElement;

    while (reltg && reltg != handler)
        reltg = reltg.parentNode;

    return (reltg != handler);
}

function showOverlay(sId) {
    document.getElementById(sId + '-overlay').style.display = 'inline';
    
    sArtist = document.getElementById(sId + '-artist').innerHTML;
    sAlbum  = document.getElementById(sId + '-album').innerHTML;
   
    document.getElementById('selected-artist').innerHTML = sArtist;
    document.getElementById('selected-album').innerHTML  = sAlbum;
}

function hideOverlay(sId) {
    document.getElementById(sId + '-overlay').style.display = 'none';
    
    document.getElementById('selected-artist').innerHTML = '';
    document.getElementById('selected-album').innerHTML  = '';
}

function positionToolbar() {
    var iLeft = 0;
    var iTop  = 0;

    /* Extra padding on body for FF 3.0, 3.5 (others?) */
    if (navigator.userAgent.indexOf('Firefox') != -1)
        iLeft = (window.innerWidth - document.body.clientWidth) / 2;
        
    oToolbar = document.getElementById('toolbar');
    if (!oToolbar)
        return;
    oObj = oToolbar;
    if (oObj.offsetParent) {
        do {
            iLeft += oObj.offsetLeft;
            iTop  += oObj.offsetTop;
        } while(oObj = oObj.offsetParent);
    }
    oToolbar.style.left     = iLeft + 'px';
    oToolbar.style.top      = iTop + 'px';
    oToolbar.style.position = 'fixed';
}
