(function () {
    var PQP_DETAILS = true;
    var PQP_HEIGHT = "tall";
    var pqp = window.pqp || {};

    pqp.changeTab = function changeTab(tab) {
        var pQp = document.getElementById('pQp');
        pqp.hideAllTabs();
        addClassName(pQp, tab, true);
    }

    pqp.hideAllTabs = function hideAllTabs() {
        var pQp = document.getElementById('pQp');
        removeClassName(pQp, 'pqp-console');
        removeClassName(pQp, 'pqp-speed');
        removeClassName(pQp, 'pqp-queries');
        removeClassName(pQp, 'pqp-memory');
        removeClassName(pQp, 'pqp-files');
        removeClassName(pQp, 'pqp-config');
        removeClassName(pQp, 'pqp-session');
        removeClassName(pQp, 'pqp-get');
        removeClassName(pQp, 'pqp-post');
    }

    pqp.toggleDetails = function toggleDetails(){
        var container = document.getElementById('pqp-container');

        if(PQP_DETAILS){
            addClassName(container, 'pqp-hideDetails', true);
            PQP_DETAILS = false;
        }
        else{
            removeClassName(container, 'pqp-hideDetails');
            PQP_DETAILS = true;
        }
    }
    pqp.toggleHeight = function toggleHeight(){
        var container = document.getElementById('pqp-container');

        if(PQP_HEIGHT == "short"){
            addClassName(container, 'pqp-tallDetails', true);
            PQP_HEIGHT = "tall";
        }
        else{
            removeClassName(container, 'pqp-tallDetails');
            PQP_HEIGHT = "short";
        }
    }
    pqp.toggleBottom = function toggleBottom(){
        var container = document.getElementById('pqp-container');
        if (container.style.position == "inherit")
        {
            container.style.position="";
        }
        else
        {
            container.style.position="inherit";
        }
    }

    pqp.toggleDisplayById = function (id) {
        var el = document.getElementById(id);

        if (el.style.display !== "block") {
            el.style.display = "block";
        }
        else {
            el.style.display = "none";
        }
    };

    pqp.openProfiler = function openProfiler()
    {
        document.getElementById("pqp-container").style.display = "block";
        document.getElementById("pqp-openProfiler").style.display = "none";
    }

    pqp.closeProfiler = function closeProfiler()
    {
        document.getElementById("pqp-container").style.display = "none";
        document.getElementById("pqp-openProfiler").style.display = "block";
    }

    //http://www.bigbold.com/snippets/posts/show/2630
    function addClassName(objElement, strClass, blnMayAlreadyExist){
       if ( objElement.className ){
          var arrList = objElement.className.split(' ');
          if ( blnMayAlreadyExist ){
             var strClassUpper = strClass.toUpperCase();
             for ( var i = 0; i < arrList.length; i++ ){
                if ( arrList[i].toUpperCase() == strClassUpper ){
                   arrList.splice(i, 1);
                   i--;
                 }
               }
          }
          arrList[arrList.length] = strClass;
          objElement.className = arrList.join(' ');
       }
       else{
          objElement.className = strClass;
          }
    }

    //http://www.bigbold.com/snippets/posts/show/2630
    function removeClassName(objElement, strClass){
       if ( objElement.className ){
          var arrList = objElement.className.split(' ');
          var strClassUpper = strClass.toUpperCase();
          for ( var i = 0; i < arrList.length; i++ ){
             if ( arrList[i].toUpperCase() == strClassUpper ){
                arrList.splice(i, 1);
                i--;
             }
          }
          objElement.className = arrList.join(' ');
       }
    }

    //http://ejohn.org/projects/flexible-javascript-events/
    function addEvent( obj, type, fn ) {
      if ( obj.attachEvent ) {
        obj["e"+type+fn] = fn;
        obj[type+fn] = function() { obj["e"+type+fn]( window.event ) };
        obj.attachEvent( "on"+type, obj[type+fn] );
      }
      else{
        obj.addEventListener( type, fn, false );
      }
    }

    function preventDefault(e) {
      e = e || window.event;
      if (e.preventDefault)
        e.preventDefault();
      e.returnValue = false;
    }

    var pqpInit = (function () {
        var called = false;

        return function()　{
            if (called) return;

            document.getElementById('pqp-console').onmousewheel = function(e){
              document.getElementById('pqp-console').scrollTop -= e.wheelDeltaY;
              preventDefault(e);
            }
            document.getElementById('pqp-speed').onmousewheel = function(e){
              document.getElementById('pqp-speed').scrollTop -= e.wheelDeltaY;
              preventDefault(e);
            }
            document.getElementById('pqp-queries').onmousewheel = function(e){
              document.getElementById('pqp-queries').scrollTop -= e.wheelDeltaY;
              preventDefault(e);
            }
            document.getElementById('pqp-memory').onmousewheel = function(e){
              document.getElementById('pqp-memory').scrollTop -= e.wheelDeltaY;
              preventDefault(e);
            }
            document.getElementById('pqp-files').onmousewheel = function(e){
              document.getElementById('pqp-files').scrollTop -= e.wheelDeltaY;
              preventDefault(e);
            }
            document.getElementById('pqp-config').onmousewheel = function(e){
              document.getElementById('pqp-config').scrollTop -= e.wheelDeltaY;
              preventDefault(e);
            }
            document.getElementById('pqp-session').onmousewheel = function(e){
              document.getElementById('pqp-session').scrollTop -= e.wheelDeltaY;
              preventDefault(e);
            }
            document.getElementById('pqp-get').onmousewheel = function(e){
              document.getElementById('pqp-get').scrollTop -= e.wheelDeltaY;
              preventDefault(e);
            }
            document.getElementById('pqp-post').onmousewheel = function(e){
              document.getElementById('pqp-post').scrollTop -= e.wheelDeltaY;
              preventDefault(e);
            }

            pqp.toggleBottom();
            called = true;
        }
    }());

    document.addEventListener("DOMContentLoaded", pqpInit, false);
    window.addEventListener("load", pqpInit, false);
    window.pqp = pqp;
}());
