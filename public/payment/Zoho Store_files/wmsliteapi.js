function WMSSessionConfig(){}function push(a){WmsLite.push(a)}function getPrd(){return WmsLite.prd}function getWmsConfig(){return WM_C}function getUserName(){return WmsLite.uname}function getNickName(){return WmsLite.nname}function getZuid(){return WmsLite.zuid}function getUserId(){return WmsLite.uid}function getSid(){return WmsLite.sid}function getRawSid(){return WmsLite.rsid}function isReconnecting(){return WmsLite.reconnecting}function disablewms(){WmsLite.disable()}function isdisablewms(){return WmsLite.disablewms}function goOffline(){}function getWmsContacts(){return new Object}function updateWmsContacts(a){}function WmsLite(){}function _getAuthType(){return WmsLite.authtype}function getAuthToken(){return _WMSAUTHTOKEN}function getAuthScope(){return _WMSAUTHSCOPE}function nocachefix(){return"&nocache="+(new Date).getTime()}function getITicket(){var a=WM_TICKET,b=document.cookie.indexOf(a),c="";if(b!=-1){var d=a.length;beginIndex=b+d,endIndex=document.cookie.indexOf(";"),c=document.cookie.substr(beginIndex+1,endIndex-1),c.indexOf(";")!=-1&&(c=c.substring(0,c.indexOf(";")))}return c}function WmsliteImpl(){}WMSSessionConfig.CHAT=1,WMSSessionConfig.CHAT_PRESENCE=2,WMSSessionConfig.PRESENCE_PERSONAL=4,WMSSessionConfig.PRESENCE_ORG=8,WMSSessionConfig.LOADBALANCED=16,WMSSessionConfig.MP=32,WMSSessionConfig.CROSS_PRD=64,WMSSessionConfig.MULTI_DISPATCH=128,WMSSessionConfig.REUSE_SESSION=256;var WM_TICKET="IAMAGENTTICKET",WM_D="zoho.com",WM_SAMED=!1,WM_FD=!1,WM_C="15",_WMSCONT="wms",wms_op=navigator.userAgent.indexOf("Opera")!=-1,wms_sf=navigator.userAgent.indexOf("Safari")!=-1,wms_ie=!wms_op&&/msie/i.test(navigator.userAgent),lfromstatic=!1,wmsjsversion="v60",_WMS_NODOMAINCHANGE=!1,_WMSSST=!1,_RETRYREGINTERVAL=1e4,isregmonrunning=!1,retryregistertimer=null,_WMSAUTHTOKEN,_WMSAUTHSCOPE,wmsdebuginfo,_WMS_RETRY_COUNT=0;WmsLite.jsstaticdomain="js.zohostatic.com",WmsLite.getDebugInfo=function(){return wmsdebuginfo},WmsLite.updateDebugInfo=function(a){wmsdebuginfo=a},WmsLite.init=function(a){_WMS_NODOMAINCHANGE||!wms_op&&!WM_SAMED&&(!wms_ie||wms_ie&&WM_FD)&&(document.domain=WM_D);if(typeof document.querySelector!="undefined"){var b=document.querySelector('script[src*="wmslite"]');b&&(WmsLite.jsstaticdomain=b.src.split("/")[2])}this.lastconnect=-1,this.disablewms=!1,WmsLite.offline=!1,WmsLite.initcountdown=!0},WmsLite.reconnect=function(a,b,c){c!=null&&c==1&&(this.reconnecting=!1);if(this.reconnecting==1)return;this.reconnecting=!0,this.retry==undefined&&(this.retry=0),this.retry++,setTimeout(function(){WmsLite.registerWms(WmsLite.prd,WmsLite.zuid,WmsLite.uname,null,!0,!0)},a),this.serverdown()},WmsLite.registerZuid=function(a,b,c,d){this.registerWms(a,b,c,null,d)},WmsLite.setIamTicketName=function(a){WM_TICKET=a},WmsLite.setConfig=function(a){WM_C=a},WmsLite.setDomain=function(a){WM_D=a},WmsLite.useSameDomain=function(){WM_SAMED=!0},WmsLite.forceDomainChange=function(){WM_FD=!0},WmsLite.setNoDomainChange=function(){_WMS_NODOMAINCHANGE=!0},WmsLite.register=function(a,b,c,d,e,f){function j(){_WMSAUTHTOKEN=e,_WMSAUTHSCOPE=f,_WMSAUTHTOKEN&&_WMSAUTHSCOPE&&(WmsLite.authtype=1),WmsLite.registerWms(a,b,null,c,d)}if(typeof JSON=="undefined"){var g=document.createElement("script"),h=window.location.protocol,i=h==="https:"?wmsjsversion+"_https":wmsjsversion;g.type="text/javascript",g.src=h+"//"+this.jsstaticdomain+"/ichat/"+i+"/js/json2.min.js",document.body.appendChild(g),WmsLite.attachonload.call(g,j)}else j()},WmsLite.attachonload=function(a){typeof this.readyState!="undefined"?this.onreadystatechange=function(){(this.readyState=="loaded"||this.readyState=="complete")&&a.call(this)}:this.onload=function(){a.call(this)}},WmsLite.registerWms=function(a,b,c,d,e,f){this.prd=a,this.uname=c!=null&&c!=undefined?c:"",this.zuid=b!=null&&b!=undefined?b:"",typeof this.nname=="undefined"&&(this.nname=d!=null&&d!=undefined?d:""),f==undefined&&this.init(e!=undefined?e:!1);var g=document.getElementById("pconnect");if(!g){var h=document.createElement("iframe");h.name="wmspconnect",h.id="pconnect",h.style.display="none",document.body.appendChild(h),g=document.getElementById("pconnect")}if((new Date).getTime()-this.lastconnect>1e4){this.lastconnect=(new Date).getTime();var i=this.zuid!=""?this.zuid:this.uname;_WMS_RETRY_COUNT++;var j="/"+_WMSCONT+"/pconnect.sas?prd="+this.prd+"&uname="+i+"&opera="+wms_op+"&samedomain="+WM_SAMED+"&ie="+(WM_FD?!1:wms_ie)+"&safari="+wms_sf+nocachefix()+"&config="+WM_C+"&wmscont="+_WMSCONT+"&nodomainchange="+_WMS_NODOMAINCHANGE+"&retrycount="+_WMS_RETRY_COUNT;lfromstatic&&(j+="&staticdomain="+WmsLite.jsstaticdomain+"&staticversion="+wmsjsversion),_WMSSST&&(j+="&sst=true"),g.src=j,isregmonrunning||(isregmonrunning=!0,clearTimeout(retryregistertimer),retryregistertimer=setTimeout(function(){WmsLite.registerMonitor()},_RETRYREGINTERVAL*6))}};var registertimer=null;WmsLite.registerMonitor=function(){WmsLite.offline=!0,WmsLite.isReuseSession()?WmsLite.retryRegister():WmsLite.initReconnect()},WmsLite.retryRegister=function(){WmsLite.isReuseSession()?(op||!top.WmsLite.offline)&&WmsLite.reconnect(0,"Retry Register",!0):WmsLite.reconnect(0,"Retry Register",!0),clearTimeout(retryregistertimer),retryregistertimer=setTimeout(function(){WmsLite.registerMonitor()},_RETRYREGINTERVAL)},WmsLite.initReconnect=function(){WmsLite.offline=!0;var a=WmsLite.initcountdown?!0:!1;WmsLite.initcountdown=!1,isregmonrunning?WmsLite.reconnectTimer(a,WmsLite.retryRegister):WmsLite.reconnectTimer(a,WmsLite.triggerbind)},WmsLite.retryConnection=function(){isregmonrunning?WmsLite.forceRegister():WmsLite.forceReconnect()},WmsLite.forceRegister=function(){WmsLite.initcountdown=!0,WmsLite.retryRegister()},WmsLite.forceReconnect=function(){WmsLite.initcountdown=!0,WmsLite.triggerbind()},WmsLite.reconnectTimer=function(){function c(a,b){return Math.floor(Math.random()*(b-a+1)+a)}var a=[30,60,90,120,300],b=0;return function(d,e){d?(b=0,a[0]=c(5,30)):b<a.length-1&&b++;var f=a[b];WmsLite.countDown(f,e)}}();var wmsCountDownTimer;WmsLite.countDown=function(a,b){function c(){setTimeout(function(){typeof WmsliteImpl.reconnectionCountDown=="function"&&WmsliteImpl.reconnectionCountDown(a)},1);if(a===0){clearTimeout(wmsCountDownTimer),b();return}a--,wmsCountDownTimer=setTimeout(c,1e3)}clearTimeout(wmsCountDownTimer),c()},WmsLite.isReuseSession=function(){return(WM_C&WMSSessionConfig.REUSE_SESSION)==WMSSessionConfig.REUSE_SESSION},WmsLite.clearRegisterMonitor=function(){clearTimeout(retryregistertimer),WmsLite.offline=!1,WmsLite.initcountdown=!0,isregmonrunning=!1},WmsLite.setWmsContext=function(a){_WMSCONT=a},WmsLite.disable=function(){this.disablewms=!0,this.abortBind(),this.clearRegisterMonitor()},WmsLite.push=function(a){if(a.mtype==0){var b=a.msg;this.uid=b.uid,this.nname=b.nname,this.sid=b.sid,this.rsid=b.rsid,this.zuid=b.zuid,this.retry=0,this.reconnecting=!1,WmsLite.clearRegisterMonitor(),this.serverup(a.msg)}else if(a.mtype==-1)this.reconnect(10,"psdown",!0);else if(a.mtype==-2){this.disable();var c=a.msg;try{WmsliteImpl.handleLogout(c.reason)}catch(d){}}else if(a.mtype==-7)try{WmsliteImpl.handleServiceMessage(a.msg)}catch(d){}else if(a.mtype==-5){var c=a.msg;this.disable();try{WmsliteImpl.handleAccountDisabled(c.reason)}catch(d){}}else try{WmsliteImpl.handleMessage(a.mtype,a.msg)}catch(d){}},WmsLite.serverup=function(a){WmsLite.initcountdown=!0,clearTimeout(wmsCountDownTimer),setTimeout(function(){try{WmsliteImpl.serverup(a)}catch(b){}},100)},WmsLite.serverdown=function(){isregmonrunning||WmsLite.initReconnect(),setTimeout(function(){try{WmsliteImpl.serverdown()}catch(a){}},100)},WmsLite.setJSStaticDomain=function(a){WmsLite.jsstaticdomain=a},WmsLite.enableSST=function(){_WMSSST=!0,this.authtype=3},WmsLite.setAuthType=function(a){this.authtype=a};var wmsjsversion="v108";WmsliteImpl.serverdown=function(){},WmsliteImpl.serverup=function(){},WmsliteImpl.handleLogout=function(a){},WmsliteImpl.handleMessage=function(a,b){},WmsliteImpl.handleAccountDisabled=function(a){},WmsliteImpl.handleServiceMessage=function(a){},WmsliteImpl.reconnectionCountDown=function(a){};var lfromstatic=!0;