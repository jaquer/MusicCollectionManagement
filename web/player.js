var oPlayer;
var oPlaylist;

swfobject.embedSWF(
    'embed/player.swf', /* url of flash */
    'player',           /* id of object to replace */
    '0', '0',           /* width, height */
    '9.0.0',            /* min flash version */
    false,              /* url of express install */
    {config:'embed/config.xml'},    /* flashvars */
    {menu:'false'},                 /* params */
    {id:'player', name:'player'}    /* attributes */
)

function playerReady(obj) {
    oPlayer = swfobject.getObjectById('player');
    oPlayer.addControllerListener('PLAYLIST', 'playlistListener');
    oPlayer.addControllerListener('ITEM', 'itemListener');
}

function loadPlaylist(sPlaylist, sCover) {
    oPlayer.sendEvent('STOP');
    oPlayer.sendEvent('ITEM', 0)
    oPlayer.sendEvent('LOAD', sPlaylist);
    document.getElementById('player-cover').src = sCover;
    document.getElementById('player-artist').innerHTML = document.getElementById('selected-artist').innerHTML;
    document.getElementById('player-album').innerHTML = document.getElementById('selected-album').innerHTML;
}

function playlistListener(obj) {
    oPlaylist = obj.playlist;
}

function itemListener(obj) {
    iIndex = obj.index;
    document.getElementById('player-title').innerHTML = oPlaylist[iIndex].title;
    oNext = oPlaylist[iIndex + 1];
    if (oNext) {
        document.getElementById('player-next-label').innerHTML = 'Next: ';
        document.getElementById('player-next-title').innerHTML = oNext.title;
    }
    else {
        document.getElementById('player-next-label').innerHTML = '';
        document.getElementById('player-next-title').innerHTML = '';
    }

}
