function initwmsopera(){function d(){var a={prd:getPrd(),uname:getUserName(),ticket:getITicket(),zuid:getZuid(),nname:getNickName(),config:getWmsConfig(),_uselp:_uselp,sdomain:_SDOMAIN,sstservice:_SSTSERVICE,authtype:getAuthType(),authtoken:getAuthToken(),authscope:getAuthScope()};subframeopera.contentWindow.postMessage('["register",'+JSON.stringify(a)+"]",pdomain)}if(typeof JSON=="undefined"){var a=document.createElement("script"),b=window.location.protocol,c=b==="https:"?staticdetails.staticversion+"_https":staticdetails.staticversion;a.type="text/javascript",a.src=b+"//"+staticdetails.jsstaticdomain+"/ichat/"+c+"/js/json2.min.js",document.body.appendChild(a),attachonload.call(a,d)}else d()}function attachonload(a){typeof this.readyState!="undefined"?this.onreadystatechange=function(){(this.readyState=="loaded"||this.readyState=="complete")&&a.call(this)}:this.onload=function(){a.call(this)}}function loadwms(){var a=window.location.protocol+"//"+wmsserver+"/v2/"+_HTML+".html?az=02"+window.parent.nocachefix();samedomain&&(a="/wmssrv/v2/"+_HTML+".html?az=02"+window.parent.nocachefix());if(_SSTSERVICE&&!samedomain||getAuthType()===7)a=_IAMSERVER+"/login?servicename="+_SERVICE+"&serviceurl="+encodeURIComponent(a);deactchat&&(parent._WMSCONFIG&=-2),subframeopera=document.createElement("iframe"),subframeopera.name="wms",subframeopera.src=a,attachonload.call(subframeopera,initwmsopera),document.body.appendChild(subframeopera);try{window.parent.WebMessanger.setUserConfig(_USERCONFIG),window.parent.WebMessanger.setCSRFParamName(_CFPARAMNAME),window.parent.WebMessanger.setCSRFTokenCookieName(_CFTOKENCOOKIENAME),window.parent.WebMessanger.setChatCSRFParamName(_CHATCSRFPARAMNAME),window.parent.WebMessanger.setChatCSRFCookieName(_CHATCSRFCOOKIENAME),window.parent.WebMessanger.setChatServer(_CHATSERVERURL),window.parent.WebMessanger.setCalendarServer(_CALENDARSERVERURL),window.parent.WebMessanger.setMailServer(_MAILSERVERURL),window.parent.WebMessanger.setMeetingUrl(_MEETINGSERVERURL),window.parent.WebMessanger.setPhotoServer(_PHOTOSERVERURL),window.parent.WebMessanger.setServiceDisplayName(_SERVICEDISPNAME),window.parent.WebMessanger.setBarSettingsValue(_WMSSETTINGS),window.parent.WebMessanger.documentready()}catch(b){}}function submitEvent(a){if(isWSSupported()===!0)subframeopera.contentWindow.postMessage('["submitEvent",'+JSON.stringify(a)+"]",pdomain);else{var b=a.o.split("@"),c=b[0].split("."),d;if(c[0]==="req"){var e=c[1],f=b[1].split(":")[1],g;window.XMLHttpRequest?g=new XMLHttpRequest:window.ActiveXObject&&(g=new ActiveXObject("Microsoft.XMLHTTP")),g.evid=a.i;if(typeof a.d!="undefined")if(e==="PUT")d=JSON.stringify(a.d);else{var h=[];for(var i in a.d)h.push(i+"="+a.d[i]);d=h.join("&"),e==="GET"&&(f+="?"+d)}g.open(e,"/"+f,!0);for(var j in a.h){if(j==="Cookie")continue;g.setRequestHeader(j,a.h[j])}(e==="POST"||a.h&&typeof a.h["Content-Type"]=="undefined")&&g.setRequestHeader("Content-Type","application/x-www-form-urlencoded;charset=UTF-8"),g.onreadystatechange=function(){if(this.readyState===4){var a={};a.eid=this.evid;if(this.status===200){a.rs="4";if(this.responseText!==""){var b=this.responseText;try{b=JSON.parse(b)}catch(c){}}a.res={d:b,eid:this.evid}}else a.rs="-1",a.err={c:this.status};handlePexEvent(this.evid,a)}},e==="GET"?g.send():g.send(d)}}}function triggerbind(){subframeopera.contentWindow.postMessage('["bind",{"uid":"'+getZuid()+'","rsid":"'+getRawSid()+'","sid":"'+getSid()+'"}]',pdomain)}function abortBind(){subframeopera.contentWindow.postMessage('["abortbind",{}]',pdomain)}function clearAndRegister(){var a={config:getWmsConfig()};subframeopera.contentWindow.postMessage('["clearregister",'+JSON.stringify(a)+"]",pdomain)}function pushMsg(a){try{for(i=0;i<a.length;i++)try{var b=a[i];push(b);if(b.mtype==="0"){var c=b.msg,d='["bind",{"uid" : "'+c.uid+'" , "rsid" : "'+c.rsid+'"  , "sid" : "'+c.sid+'" , "nname" : "'+c.nname+'", "binding":true }]';subframeopera.contentWindow.postMessage(d,pdomain)}}catch(e){}}catch(f){}}function isValidDomain(a){try{function b(a){return a.replace(/:\d*$/,"")}a=b(a);if(wmssubdomain&&a){if(a===window.location.protocol+"//"+wmssubdomain)return!0;if(a.split(".").length>2)return a.substring(a.length-(wmssubdomain.length+1))==="."+wmssubdomain}return!1}catch(c){return!1}}function getDomain(a){var b="";return a.domain!==undefined?b=a.domain:a.origin!==undefined&&(b=a.origin),b}var subframeopera,pdomain="*",isWSSupported,setWSSupport;push=window.parent.push,getPrd=window.parent.getPrd,getUserName=window.parent.getUserName,getWmsConfig=window.parent.getWmsConfig,getNickName=window.parent.getNickName,getZuid=window.parent.getZuid,getUserId=window.parent.getUserId,getSid=window.parent.getSid,getRawSid=window.parent.getRawSid,isReconnecting=window.parent.isReconnecting,getITicket=window.parent.getITicket,disablewms=window.parent.disablewms,goOffline=window.parent.goOffline,isdisablewms=window.parent.isdisablewms,getWmsContacts=window.parent.getWmsContacts,updateWmsContacts=window.parent.updateWmsContacts,getAuthType=window.parent._getAuthType,getAuthToken=window.parent.getAuthToken||function(){},getAuthScope=window.parent.getAuthScope||function(){},typeof window.parent.WebMessanger=="function"?(serverDown=window.parent.WebMessanger.serverdown,serverUP=window.parent.WebMessanger.serverup,updateDebugInfo=window.parent.WebMessanger.updateDebugInfo):typeof window.parent.WmsLite=="function"&&(serverDown=window.parent.WmsLite.serverdown,serverUP=window.parent.WmsLite.serverup,updateDebugInfo=window.parent.WmsLite.updateDebugInfo);try{window.parent.WebMessanger.triggerbind=triggerbind,window.parent.WebMessanger.abortBind=abortBind,window.parent.WebMessanger.clearAndRegister=clearAndRegister}catch(e){}try{window.parent.WmsLite.triggerbind=triggerbind,window.parent.WmsLite.abortBind=abortBind,window.parent.WmsLite.clearAndRegister=clearAndRegister}catch(e){}try{handlePexEvent=window.parent.PexBridge.handleEvent,window.parent.PexBridge.submitEvent=submitEvent}catch(e){}(function(){var a;setWSSupport=function(b){a=b},isWSSupported=function(){return a}})(),addEvent(window,"message",function(a){if(!isValidDomain(getDomain(a)))throw new Error("Invalid cross domain access in bridge");try{var b=JSON.parse(a.data),c=b[0],d=b[1];if(c==="push")pushMsg(d);else if(c==="disablewms")disablewms();else if(c==="goOffline")typeof goOffline=="function"&&goOffline();else if(c==="isdisablewms")subframeopera.contentWindow.postMessage('["setdisablewms",{"value" : "'+isdisablewms()+'"}]',pdomain);else if(c==="getcontacts"){var e=getWmsContacts(),f=d;try{subframeopera.contentWindow.postMessage('["updatecontacts",{"childsid" : "'+f+'" , "contacts" : '+JSON.stringify(e)+"}]",pdomain)}catch(g){}}else if(c==="updatecontacts")updateWmsContacts(d);else if(c==="requestsuccess")try{requestsuccess()}catch(h){}else c==="serverup"?serverUP():c==="serverdown"?serverDown():c==="pexevt"?handlePexEvent(d.eid,d):c==="wssupport"?setWSSupport(d):c==="debuginfo"&&updateDebugInfo(d)}catch(h){throw h}});