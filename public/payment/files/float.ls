







































var checkisfloatexist=false;

try {
var zldistouch=false; //No I18N
_ZLDSCREENNAME="zohopayments"; //No I18N
_ZLDEMBEDNAME="zohoprojects";  //No I18N
_ZLDUTSSERVER="vts.zohopublic.com";  //No I18N
_LANG = "en";  //No I18N
var _CCODE= "US"; //No I18N

function ZloadFiles()
{
  function readCookie(n){n+='=';for(var a=document.cookie.split(/;\s*/),i=a.length-1;i>=0;i--)if(!a[i].indexOf(n))return a[i].replace(n,'');}
  if(checkisfloatexist){return;}
  
  checkisfloatexist=true; 
  identifySIQParam();  //No I18N
  var floatcookie = readCookie('zld2598000000002107float');//No I18N
  var embedinfo = {//No I18N
     embedtype: 'float',//No I18N
     floatstatus: (floatcookie === "1" && "maximized") || (floatcookie === "0" && "minimized") || (floatcookie === "-1" && "closed") || "initialized" //No I18N
  };
  try{$zohosq.ready(embedinfo)}catch(e){}//No I18N
  
  var headArr = document.getElementsByTagName("head");
  if (!headArr || headArr.length == 0)
  {
          headArr = [];
          headArr[0] = document.createElement("head");
          document.insertBefore(doc.body,headArr[0]);
  }
  var ocss = document.createElement("link");
  ocss.rel ="stylesheet";
  ocss.href = "https://css.zohostatic.com/salesiq/V63_https/styles/floatsupportbtn.css";  
  headArr[0].appendChild(ocss);
  
  try
  {
  		var op = (((navigator.userAgent).indexOf("Opera")!=-1))
  		var ie = !op && /msie/i.test(navigator.userAgent);	// preventing opera to be identified as ie
		if(ie && document.compatMode!="CSS1Compat")
		{
				var headArr = document.getElementsByTagName("head");
				var ocss = document.createElement("link");
				ocss.rel ="stylesheet";
				ocss.href = "https://css.zohostatic.com/salesiq/V63_https/styles/floatiebtnfix.css";  
				headArr[0].appendChild(ocss);
		}
		
		if("ontouchstart" in document.documentElement)
		{
			zldistouch=true; //No I18N
		}
  }	
  catch(e)
  {}
  var oscript=document.createElement("script");
  oscript.defer=true;
  oscript.src="https://js.zohostatic.com/salesiq/V63_https/js/track.js";  
  headArr[0].appendChild(oscript);
  
  if(false)
  {
 		var oscript=document.createElement("script"); //No I18N
  	 	oscript.defer=true; //No I18N
     	oscript.src="https://salesiq.zohopublic.com/js/thirdparty/json.js";   //No I18N
     	headArr[0].appendChild(oscript); //No I18N
  }
}

function ZaddEvent()
{
	
     	if(window.addEventListener) 
    	{
            window.addEventListener('load',function(){ZloadFiles()}, false);
     	}
     	else  
     	{
            window.attachEvent('onload', function(){ZloadFiles()});
     	}
  
}

  	var iframeurl = "https://salesiq.zohopublic.com/zohopayments/drawchat.ls?";  	
    var _zldcpage = "https://payments.zoho.com/html/store/index.html"; 
    var _zldreferrer = document.referrer; 
    


	

		_zldreferrer = window.btoa(_zldreferrer);	//No I18N
		_zldcpage = window.btoa(_zldcpage);  //No I18N

	
  function initFloat()
  {
        var checkAndAdd = function (name, value) { //No I18N
            if (value !== undefined) {
                Float.lsobject[name] = value; //No I18N
            }
        };
		Float.i18nkeys = {"da":{"infomsg.mobile.offline":"Offline","infomsg.mobile.chatwithus":"Chat med os","float.chatwindow.closealert":"En anden chat er i gang. Vil du starte en ny chat ?","common.clicktochat":"Klik her for at chatte","settings.embed.offline":"Læg en besked","common.mobile.chat":"Chat"},"ro":{"infomsg.mobile.offline":"Offline","infomsg.mobile.chatwithus":"Discutaţi cu noi","float.chatwindow.closealert":"Se desfăşoară un alt chat. Doriţi să începeţi un chat nou?","common.clicktochat":"Faceţi clic aici pentru chat","settings.embed.offline":"Lăsaţi un mesaj","common.mobile.chat":"Chat"},"zh":{"infomsg.mobile.offline":"留言","infomsg.mobile.chatwithus":"与我们聊天","float.chatwindow.closealert":"正在进行另一聊天。是否想要启动新聊天？","common.clicktochat":"点击这里聊天","settings.embed.offline":"留言","common.mobile.chat":"聊天 "},"it":{"infomsg.mobile.offline":"Non in linea","infomsg.mobile.chatwithus":"Chatta con noi","float.chatwindow.closealert":"Un’altra chat è già in corso. Si desidera avviare una nuova chat?","common.clicktochat":"Fai clic qui per avviare la chat","settings.embed.offline":"Lascia un messaggio","common.mobile.chat":"Chat"},"tr":{"infomsg.mobile.offline":"Çevrimdışı","infomsg.mobile.chatwithus":"Bizimle sohbet edin...","float.chatwindow.closealert":"Başka bir sohbet devam ediyor. Yeni bir sohbet başlatmak istiyor musunuz?","common.clicktochat":"Sohbet için burayı tıklatın","settings.embed.offline":"Bir mesaj bırakın","common.mobile.chat":"Sohbet"},"iw":{"infomsg.mobile.offline":"לא מחובר","infomsg.mobile.chatwithus":"שוחח איתנו","float.chatwindow.closealert":"קיים צ'ט נוסף פעיל האם תרצה להתחיל צ'ט חדש?","common.clicktochat":"הקלק כאן לתחילת צ'ט","settings.embed.offline":"שלח הודעה","common.mobile.chat":"צ'אט"},"hu":{"infomsg.mobile.offline":"Kijelentkezve","infomsg.mobile.chatwithus":"Csevegjen velünk","float.chatwindow.closealert":"Egy másik csevegés van folyamatban. Kíván új csevegést indítani?","common.clicktochat":"Beszélgetéshez kattintson ide.","settings.embed.offline":"Hagyjon üzenetet.","common.mobile.chat":"Csevegés"},"ko":{"infomsg.mobile.offline":"오프라인","infomsg.mobile.chatwithus":"담당자와 채팅","float.chatwindow.closealert":"다른 채팅이 진행 중입니다. 새 채팅을 시작하시겠습니까?","common.clicktochat":"여기를 클릭하여 채팅하십시오.","settings.embed.offline":"메시지 남기기","common.mobile.chat":"채팅"},"ar":{"infomsg.mobile.offline":"غير متصل","infomsg.mobile.chatwithus":"تحدث معنا","float.chatwindow.closealert":"تدور محادثة أخرى الآن. هل ترغب في بدء محادثة جديدة؟","common.clicktochat":"انقر هنا للمحادثة","settings.embed.offline":"ترك رسالة","common.mobile.chat":"محادثة"},"he":{"infomsg.mobile.offline":"לא מחובר","infomsg.mobile.chatwithus":"שוחח איתנו","float.chatwindow.closealert":"קיים צ'ט נוסף פעיל האם תרצה להתחיל צ'ט חדש?","common.clicktochat":"הקלק כאן לתחילת צ'ט","settings.embed.offline":"שלח הודעה","common.mobile.chat":"צ'אט"},"ga":{"infomsg.mobile.offline":"As líne","infomsg.mobile.chatwithus":"Comhrá linn","float.chatwindow.closealert":"Tá comhrá eile ar siúl. Ar mhaith leat comhrá nua a thosú?","common.clicktochat":"Cliceáil anseo le haghaidh comhrá","settings.embed.offline":"Fág teachtaireacht","common.mobile.chat":"Comhrá"},"th":{"infomsg.mobile.offline":"ออฟไลน์","infomsg.mobile.chatwithus":"สนทนากับเรา ...","float.chatwindow.closealert":"มีอีกหนึ่งการสนทนาที่กำลังดำเนินอยู่ คุณต้องการเริ่มการสนทนาใหม่หรือไม่?","common.clicktochat":"คลิกที่นี่เพื่อสนทนา","settings.embed.offline":"ฝากข้อความ","common.mobile.chat":"สนทนา"},"de":{"infomsg.mobile.offline":"Offline","infomsg.mobile.chatwithus":"Chatten Sie mit uns","float.chatwindow.closealert":"Ein anderer Chat wird ausgeführt. Möchten Sie einen neuen Chat starten?","common.clicktochat":"Klicken Sie hier für den Chat","settings.embed.offline":"Eine Nachricht hinterlassen","common.mobile.chat":"Chat"},"pt_PT":{"infomsg.mobile.offline":"Offline","infomsg.mobile.chatwithus":"Converse connosco","float.chatwindow.closealert":"Está a decorrer outro chat. Gostaria de iniciar um novo chat ?","common.clicktochat":"Clique aqui para conversar","settings.embed.offline":"Deixar mensagem","common.mobile.chat":"Chat"},"nb":{"infomsg.mobile.offline":"Offline","infomsg.mobile.chatwithus":"Chat med oss","float.chatwindow.closealert":"En annen chat er i gang. Vil du starte en ny chat?","common.clicktochat":"Klikk her for å chatte","settings.embed.offline":"Legg igjen en melding","common.mobile.chat":"Chat"},"el":{"infomsg.mobile.offline":"Εκτός σύνδεσης","infomsg.mobile.chatwithus":"Συνομιλήστε μαζί μας","float.chatwindow.closealert":"Μια άλλη συνομιλία βρίσκεται σε εξέλιξη. Θα θέλατε να ξεκινήσετε νέα συνομιλία;","common.clicktochat":"Κάντε κλικ εδώ για συνομιλία","settings.embed.offline":"Αφήστε ένα μήνυμα","common.mobile.chat":"Συνομιλία"},"pl":{"infomsg.mobile.offline":"Offline","infomsg.mobile.chatwithus":"Porozmawiaj z nami","float.chatwindow.closealert":"Trwa kolejna rozmowa. Czy chciałbyś rozpocząć nową rozmowę?","common.clicktochat":"Naciśnij tutaj, aby rozpocząć rozmowę","settings.embed.offline":"Zostaw wiadomość","common.mobile.chat":"Rozmowa"},"pt":{"infomsg.mobile.offline":"Offline","infomsg.mobile.chatwithus":"Converse conosco","float.chatwindow.closealert":"Outro chat está em andamento. Quer começar outro chat?","common.clicktochat":"Clique aqui para conversar","settings.embed.offline":"Deixe uma mensagem","common.mobile.chat":"Bate-papo"},"sv":{"infomsg.mobile.offline":"Offline","infomsg.mobile.chatwithus":"Chatta med oss","float.chatwindow.closealert":"En annan chatt pågår. Vill du starta en ny chatt?","common.clicktochat":"Klicka här för att chatta","settings.embed.offline":"Lämna ett meddelande","common.mobile.chat":"Chatt"},"fr":{"infomsg.mobile.offline":"Hors connexion","infomsg.mobile.chatwithus":"Conversez avec nous","float.chatwindow.closealert":"Un autre chat est en cours. Voulez-vous commencer un nouveau chat ?","common.clicktochat":"Cliquez ici pour converser","settings.embed.offline":"Laisser un message","common.mobile.chat":"Conversation"},"en":{"infomsg.mobile.offline":"Offline","infomsg.mobile.chatwithus":"Chat with us","float.chatwindow.closealert":"Another chat is going on. Would you like start a new chat ?","common.clicktochat":"Click here to chat","settings.embed.offline":"Leave a message","common.mobile.chat":"Chat"},"ru":{"infomsg.mobile.offline":"Не в сети","infomsg.mobile.chatwithus":"Поговорите с нами в чате","float.chatwindow.closealert":"Активен другой чат. Вы хотите начать новый чат?","common.clicktochat":"Нажмите здесь, чтобы начать чат","settings.embed.offline":"Оставить сообщение","common.mobile.chat":"Чат"},"es":{"infomsg.mobile.offline":"Sin conexión","infomsg.mobile.chatwithus":"Chatee con nosotros","float.chatwindow.closealert":"Otro chat está en curso. ¿Desea comenzar un nuevo chat?","common.clicktochat":"Haga clic aquí para chatear","settings.embed.offline":"Dejar un mensaje","common.mobile.chat":"Chat"},"ja":{"infomsg.mobile.offline":"メッセージを残してください","infomsg.mobile.chatwithus":"チャットで連絡 ","float.chatwindow.closealert":"他のチャットが現在進行中です。新規チャットを開始しますか？","common.clicktochat":"クリックするとチャットできます","settings.embed.offline":"メッセージを残してください","common.mobile.chat":"チャット "},"nl":{"infomsg.mobile.offline":"Offline","infomsg.mobile.chatwithus":"Chat met ons","float.chatwindow.closealert":"Er is een andere chat bezig. Wilt u een nieuwe chat beginnen?","common.clicktochat":"Klik hier om te chatten","settings.embed.offline":"Laat een bericht achter","common.mobile.chat":"Chat"}};
        Float.lsobject={"autochat":"false","clickchat":"false","name":"","email":"","ques":"","department":"","embedname":"zohoprojects","lsid":"2598000000002107","bgcolor":"","oncontent":"Leave a message","icon":"1","buttontheme":"","oncontentcolor":"","status":"off","statustext":"Offline","serverurl":"https://salesiq.zohopublic.com","url":"https://salesiq.zohopublic.com/zohopayments/drawchat.ls?","floatposition":"bottomright","top":"0","bottom":"0","left":"0","right":"0","flipicon":"false","aligntoright":"false","floatmeasurement":"px","headercolor":"","sbicobg":"","fltsize":"small","mobileoncontent":"common.mobile.chat","ipadoncontent":"infomsg.mobile.chatwithus","mobileoffcontent":"infomsg.mobile.offline"}; 
        Float.lsobject["imgurl"] = "https://img.zohostatic.com/salesiq/V63_https";  
        Float.lsobject["attender"]="";  
		Float.displayonly = false;	  
        Float.lsobject["mismsg"]=""; 
        Float.lsobject["offmsg"]=""; 
        Float.lsobject["exid"]="";  
        Float.lsobject["waitmsg"]=""; 
        Float.lsobject["offrespmsg"]=""; 
        Float.lsobject["misrespmsg"]=""; 
        Float.lsobject["thanksmsg"]=""; 
        Float.lsobject["headlinetxt"]=""; 
        Float.lsobject["usermsgtxt"]=""; 
        Float.lsobject["classname"]=""; 
		Float.lsobject["info"]=''; 
        Float.lsobject["floattype"]='float';  
        Float.lsobject["fltbubble"]="false"; 
        Float.lsobject["isagentschat"]="false"; 
		Float.lsobject.referrer = _zldreferrer;  //No I18N
		Float.lsobject["screenname"]="zohopayments" ;
		Float.lsobject["custombubblename"]="";
		Float.lsobject["aatext"]="float.chatwindow.closealert";
		Float.lsobject["agentsemailid"]="";
		Float.lsobject["offason"]="false";
		Float.lsobject.cpage = _zldcpage;  //No I18N
		Float.lsobject["float_fkey"]="0_2598000000002107";
		Float.lsobject["css_fkey"]="0_2598000000002107";
    		Float.lsobject["embedtheme"]="blck";
    	Float._zldvfp = ""; //No I18N
    	Float.hideembed = false ;
    		
    	var apivalues = {}; //No I18N
    	try                 //No I18N
    	{
            apivalues = $zohosq.values; //No I18N
        } catch (e) {}                  //No I18N

        checkAndAdd('embedtheme', apivalues.embedtheme); //No I18N
        checkAndAdd('embedheadertheme', apivalues.embedheadertheme); //No I18N
        if (Float.lsobject.buttontheme) { //Empty if the embed status is offline && offason is disabled
            checkAndAdd('buttontheme', apivalues.buttontheme); //No I18N
        }
        checkAndAdd('bgcolor', apivalues.sbonlinebg); //No I18N
        checkAndAdd('sbicobg', apivalues.sbicobg); //No I18N

		if(true)
    		{	
     			Float.create();
     		}
     	
     	if(false)
 		{ //No I18N
 			var headArr = document.getElementsByTagName("head"); //No I18N
 			var oscript=document.createElement("script"); //No I18N
  	 		oscript.defer=true; //No I18N
     		oscript.src="https://salesiq.zohopublic.com/js/embed/uts/uts.js";   //No I18N
     		headArr[0].appendChild(oscript); //No I18N
     		
     		var oscript1=document.createElement("script"); //No I18N
  	 		oscript1.defer=true; //No I18N
     		oscript1.src="https://salesiq.zohopublic.com/js/embed/uts/utsaction.js";   //No I18N
     		headArr[0].appendChild(oscript1); //No I18N
 		}
  }
  	
	
		try
		{
		$zohosq=$zoho.livedesk || $zoho.salesiq;//no i18n
		$zohosq._callbacks={},$zcb=$zohosq._callbacks,$zv=$zohosq.values,$zlm={},$zlch={},$zla="handleAnalyticEvents";
		$zohosq.utsvalues={}; //No I18N
		$zohosq._invoke=function(type,data)
		{		
			$ZShandleEvent(type,data); //No I18N
			if($zcb[type] && typeof($zcb[type]) === "function")	
        	{
        		 if(typeof(data)!="object")
        		 {
        		 	if($zlm[type]===data)
        		 	{
        		 		return false;
        		 	}
        		 	$zlm[type]=data;
        		 }
        		 var returnvalue;
        		 if(data["visitid"])
        		 {
        		     returnvalue=$zcb[type](data["visitid"],data);
        		 }
        		 else
        		 {
        		     if (type==='visitor.trigger')
        		     {
        		       return $zcb[type](data.triggername, data.visitorinfo);//No I18N
        		     }
        		     else//No I18N
        		     {
	                   returnvalue=$zcb[type](data);
	                 }
	             }
	             returnvalue=(returnvalue!=undefined)?returnvalue:-1;
		     	 var obj={};obj[type]=returnvalue;
		     	 if($zlch[type]){$zohosq.setValue("callback",obj);}
	             $zlm[type]={}; 
        	}
        	else if(type=="custom.field")
       		{
       			$zohosq.customfield.handleCallbacks(data);
       		}
       		return false;//No I18N
     	},
        $zohosq.visitor=
    	{
           	uniqueid:function() 
           	{
           		return $zv["uvid"]; 
           	},
           	name:function(value) 
           	{
           		if(value!=null)
           		{
           			try{$UTS.DB.store("siq_name",value,60*1000);}catch(e){} //No I18N
           			$zohosq.setValue("name",value,3); //No I18N
           		}
           		return $zv["name"]; 
           	},
           	email:function(value) 
           	{
           		var regexp = /^([\w]([\w\-\.\+\'\/]*)@([\w\-\.]*)(\.[a-zA-Z]{2,22}(\.[a-zA-Z]{2}){0,2}))$/; //No I18N

			if(value == null || !regexp.test(value))
           		{
           			return;         //No I18N
           		}

        		try{$UTS.DB.store("siq_email",value,60*1000);}catch(e){} //No I18N
           		$zohosq.setValue("email",value,3); //No I18N
           		return $zv["email"];     			
           	},
           	question:function(value) 
           	{
           		if(value!=null)
           		{
           			$zohosq.setValue("question",value);
           		}
           		return $zv["question"]; 
           	},
           	contactnumber:function(value)
           	{
           		if(value!=null)
           		{
           			try{$UTS.DB.store("siq_phone",value,60*1000);}catch(e){} //No I18N
           			$zohosq.setValue("phone",value,3); //No I18N
           		}
           		return $zv["phone"];
           	},
           	info:function(object) 
           	{
           		if(object)$zv["info"]=object;
           		return $zv["info"]; 
           	},
           	authkey:function(value) 
           	{
           		if(value)$zv["authkey"]=value;
           		return $zv["authkey"]; 
           	},
           		chat:function(clbk) 
           		{
           			$zcb["visitor.chat"]=clbk; 
           		},
           		attend:function(clbk) 
           		{
           			$zcb["visitor.attend"]=clbk; 
           		},
           		missed:function(clbk) 
           		{
           			$zcb["visitor.missed"]=clbk; 
           		},
           		agentsoffline:function(clbk) 
           		{
           			$zcb["visitor.offline"]=clbk; 
           		},
           		offlineMessage:function(clbk)  //No I18N
           		{
           			$zcb["visitor.offline"]=clbk; //No I18N
           		},
           		chatmessage:function(clbk) 
           		{
           			$zcb["visitor.chatmessage"]=clbk; 
           		},
           		chatcomplete:function(clbk) 
           		{	
           			$zcb["visitor.chatcomplete"]=clbk; 
           		},
           		rating:function(clbk) 
           		{
           			$zcb["visitor.rating"]=clbk; 
           		},
           		feedback:function(clbk) 
           		{
           			$zcb["visitor.feedback"]=clbk; 
           		},
      			idleTime:function(value)
      			{
      				if(!isNaN(value))
      				{
      					$zv["idletime"]=value;
      					$ZNotifyTracking(2,value); //No I18N
      				}
      			},
      			idle:function(clbk)
      			{
      				$zcb["visitor.idle"]=clbk; 
      			},
      			active:function(clbk)
      			{
      				$zcb["visitor.active"]=clbk;
      			},
      			onNavigate:function(obj)
      			{
      				return obj;
      			},
      			trigger: function (clbk)//No I18N
      			{
      			    $zcb['visitor.trigger']=clbk;//No I18N
      			}
      		},
      		$zohosq.chat= 
      		{
      			mode:function(value) 
      			{
      				if(value!=null)
      				{
	      				$zohosq.setValue("chatmode",value);
      				}
      				return $zv["chatmode"]; 
      			},
      			sendmessage:function(value) 
      			{
      				if(Float && value)
      				{
      					Float.sendMessage(value); 
      				}
      			},
      			department:function(value) 
      			{
      				if(value!=null)
           			{
           				$zohosq.setValue("department",value); //No I18N
           			}
	      			return $zv["department"]; 
      			},
      			defaultdepartment:function(value) 
      			{
      				if(value)$zv["defaultdepartment"]=value;
      				return $zv["defaultdepartment"]; 
      			},
      			agent:function(value) 
      			{
      				if(value!=null)
           			{
           				$zohosq.setValue("agent",value); //No I18N
           			}
      				return $zv["agent"]; 
      			},
      			messages:function(object) 
      			{
      				if(object)$zv["chatmessages"]=object;
      				return $zv["chatmessages"]; 
      			},
      			systemmessages:function(object)  //No I18N
      			{
      				if(object){$zv.chatmessages=object;}
      				return $zv.chatmessages;  //No I18N
      			},
      			title:function(value) 
      			{
      				if(value)$zv["title"]=value;
      				return $zv["title"]; 
      			},
      			messagehint:function(value) //No I18N
      			{
      				if (value) $zv.messagehint = value;
      				return $zv.messagehint; //No I18N
      			},
      			online:function(clbk) 
           		{
           			$zcb["chat.online"]=clbk; 
           		},
           		offline:function(clbk) 
           		{
           			$zcb["chat.offline"]=clbk; 
           		},
           		logo:function(value)
           		{
           			if(value)$zv["clogo"]=value;
      				return $zv["clogo"];
           		},
           		waitinghandler:function(clbk)
           		{
           			 var a="chat.waitinghandler";
           		     $zcb[a]=$zlch[a]=clbk;
           		     $zv[a]=(clbk==null)?false:true;
           		},
           		start:function()
           		{
           			broadcastMessage("chatstart",{});
           		},
           		forward:function(value) //No I18N
           		{
           			if(value!=null)
           			{
           				$zohosq.setValue("forward",value); //No I18N
           			}
           			return $zv["forward"]; //No I18N
           		},
           		attend:function(clbk)  //No I18N
           		{
           			$zcb["visitor.attend"]=clbk;  //No I18N
           		},
           		agentMessage:function(clbk)  //No I18N
           		{
           			$zcb["visitor.chatmessage"]=clbk;  //No I18N
           		},
           		complete:function(clbk)  //No I18N
           		{	
           		    if (clbk)
           		    {
           			    $zcb["visitor.chatcomplete"]=clbk;  //No I18N
                    }
                    else //No I18N
                    {
                        broadcastMessage("chatend", {}); //No I18N
                    }
           		},
                theme: function (theme)                                         //No I18N
                {
                    var changeButtonTheme = function (theme) {                  //No I18N
                        if (!theme) {return false;}                             //No I18N
                        
                        $zv.buttontheme = theme + '-btn';                       //No I18N
                        $zv.sbonlinebg = '';                                    //No I18N
                        $zv.sbonlinebdr = '';                                   //No I18N
                        $zv.sbicobg = '';                                       //No I18N
                    };
                    var changeWindowTheme = function (theme) {                  //No I18N
                        if (!theme) {return false;}

                        var thememap = {                                        //No I18N
                            'black': ['blck', 'black'],                         //No I18N
                            'gray': ['gry', 'gray'],                            //No I18N
                            'blue': ['blue', 'blue'],                           //No I18N
                            'green': ['green', 'green'],                        //No I18N
                            'red': ['red', 'red'],                              //No I18N
                            'purple': ['purple', 'purple']                      //No I18N
                        };

                        if (thememap.hasOwnProperty(theme)) {
                            $zv.embedtheme = thememap[theme][0];                //No I18N
                            $zv.embedheadertheme = thememap[theme][1];          //No I18N
                        } else {                                                //No I18N
                            $zv.embedtheme = theme;                             //No I18N
                        }
                    };
                    changeButtonTheme(theme);                                   //No I18N
                    changeWindowTheme(theme);                                   //No I18N
                }
      		},
      		$zohosq.rating=
      		{
      			visible:function(value)
      			{
	      			if(value)$zv["rating.visible"]=value;
      				return $zv["rating.visible"];
      			}
      		},
      		$zohosq.feedback=
      		{
      			visible:function(value)
      			{
      				if(value)$zv["feedback.visible"]=value;
      				return $zv["feedback.visible"];
      			}
      		},
      		$zohosq.integ=
      		{
      			requestid:function(value)
      			{
      				if(value!=null)
           			{	
           				$zohosq.setValue("requestid",value);
           			}
           			return $zv["requestid"]; 
      			}
      		},
      		$zohosq.chatbubble= 
      		{
      			visible:function(value) 
      			{
      				if(value)$zv["bubblevisible"]=value;
      				return 	$zv["bubblevisible"]; 
      			},
      			animate:function(value) 
      			{
      				if(value)$zv["bubbleanimatetimer"]=value;
      				return 	$zv["bubbleanimatetimer"];
      			},
      			src:function(value)
      			{
      				if(value)$zv["bubblesrc"]=value;
      				return $zv["bubblesrc"]; 
      			},
      			close:function(clbk) 
           		{
           			$zcb["chatbubble.close"]=clbk; 
           		}
      		},
      		$zohosq.chatbutton= 
      		{
      			texts:function(object) 
      			{
      				if(object)$zv["buttontexts"]=object;
      				return $zv["buttontexts"]; 
      			},
      			icon:function(value) 
      			{
      				if(value)$zv["buttonicon"]=value;
      				return $zv["buttonicon"]; 
      			},
      			visible:function(value) 
      			{
      				if(value)$zv["buttonvisible"]=value;
      				try 
      				{
      					zhandleLiveEvent("buttonvisible",value); 
      				}
      				catch(e){} 
      				return $zv["buttonvisible"]; 
      			},
      			onlineicon: 
      			{
      					src:function(value) 
      					{
      						if(value)$zv["buttononlineicon"]=value;
      						return 	$zv["buttononlineicon"]; 
      					}
      			},
      			offlineicon: 
      			{
      					src:function(value) 
      					{
      						if(value)$zv["buttonofflineicon"]=value;
      						return $zv["buttonofflineicon"]; 
      					}
      			},
      			click:function(clbk) 
           		{
           			$zcb["chatbutton.click"]=clbk; 
           		},
           		width:function(value)
           		{
           			if(value)$zv["bwidth"]=value;
      				return $zv["bwidth"];
           		}
      		},
      		$zohosq.floatbutton= 
      		{
      			position:function(value) 
      			{
      				if(value)$zv["floatposition"]=value;
      				return $zv["floatposition"]; 
      			},
      			visible:function(value) 
      			{
      				if(value)$zv["floatvisible"]=value;
      				try 
      				{
      					if(Float)
      					{
      						Float.handleVisible(value); 
      					}
      				}catch(e){} 
      				return $zv["floatvisible"]; 
      			},
      			onlineicon: 
      			{
      					src:function(value) 
      					{
      						if(value)$zv["floatbuttononlinesrc"]=value;
      						return $zv["floatbuttononlinesrc"]; 
      					}
      			},
      			offlineicon: 
      			{
      					src:function(value) 
      					{
      						if(value)$zv["floatbuttonofflinesrc"]=value;
      						return $zv["floatbuttonofflinesrc"]; 
      					}
      			},
      			click:function(clbk) 
           		{
           			$zcb["floatbutton.click"]=clbk; 
           		}
      		},
      		$zohosq.chatwindow= 
      		{
      			visible:function(value) 
      			{
      				if(value)$zv["chatwindowvisible"]=value;
      				try 
      				{
      					zhandleLiveEvent("chatwindowvisible",value); 
      				}
      				catch(e){}
      				return $zv["chatwindowvisible"]; 
      			}
      		},
      		$zohosq.floatwindow= 
      		{
      			visible:function(value) 
      			{
      				if(value)$zv["floatwindowvisible"]=value;
      				try 
      				{
      					if(Float)
      					{
      						Float.handleWinVisible(value); 
      					}
      					
      				}
      				catch(e){} 
      				return $zv["floatwindowvisible"]; 
      			},
      			open:function(clbk)
      			{
      				if(clbk)
      				{
      					$zcb["chat.open"]=clbk;
      				}
      				else
      				{
      					this.visible("show");
      				}
      			},
      			close:function(clbk)
      			{
      				if(clbk)
      				{
      					$zcb["chat.close"]=clbk;
      				}
      				else
      				{
      					this.visible("hide");
      				}
      			},
      			minimize:function(clbk)
      			{
      				if(clbk)
      				{
      					$zcb["floatwindow.minimize"]=clbk;
      				}
      				else
      				{
      					try
      					{
      						Float.closeChat(true);
      					}
      					catch(e){}
      				}
      			}
      		},
      		$zohosq.custom=
      		{
      			html:function(container,object)
           		{
           			if(container && object)
           			{
           				$zv["customhtml"]=[container,object];
           				try
           				{
           					Float.drawFloatButtonHtml($zv["customhtml"]);
           				}
           				catch(e)
           				{	
           					try
           					{
           						zlsDrawButtonHtml($zv["customhtml"]);
           					}
           					catch(x)
           					{}
           				}
           			}
           			return $zv["floatbuttondraw"];
           		}
      		},
      		$zohosq.customfield=
      		{
      			add:function(value)
      			{
      				var a="customfield";
      				if(value)
      				{
      					var arr=$zv[a]=$zv[a]||[];
      					this._splice([value["name"]],arr);
				    	$zv[a].push(value);
				    	broadcastMessage(a,$zv[a]);
      				}
      			},
      			clear:function(value)
      			{
      				var a="customfield";
      				if(!value){$zv[a]=[];value=[];}
      				if(Object.prototype.toString.call(value) === "[object Array]")
      				{
				    	this._splice(value,$zv[a]||[]);
				   	}
      			},
      			handleCallbacks:function(data)
      			{
      				if(!data){return;}
      				var cfobj=this._getObject(data["name"]);
      				if(cfobj && cfobj["callback"])
      				{
						cfobj["callback"](data["val"]);				
      				}
      			},
      			_getObject:function(name)
      			{
      				var cfobj=$zv["customfield"];
      				if(!cfobj || cfobj.length<1){return}
      				for(var i=0;i < cfobj.length;i++)
      				{
      					if(cfobj[i]["name"]==name)
      					{
      						return cfobj[i];
      					}
      				}
      			},
      			_splice:function(value,arr)
      			{
      					for(var i=0;i< value.length;i++)
				    	{
				    		for(var j=0;j< arr.length;j++)
				    		{
				    			if(arr[j]["name"]==value[i])
				    			{
				    				arr.splice(j,1);
				    				broadcastMessage("clearcustomfield",[value[i]]);
				    				break;
				    			}
				    		}
				    	}
      			}
      		},
      		$zohosq.tracking=
      		{
      			on:function()
      			{
      				$zv["tracking"]="on";
      				$ZNotifyTracking(1,"on"); //No I18N
      			},
      			off:function()
      			{
      				$zv["tracking"]="off";
      				$ZNotifyTracking(1,"off"); //No I18N
      			},
      			domain: function (domain) //No I18N
      			{
      			   var hostname = window.location.hostname; //No I18N
      			   if (domain && hostname.indexOf(domain, hostname.length - domain.length) !== -1)
      			   {
                       $zohosq.utsvalues.trackingdomain = domain; //No I18N
      			   }
      			}
      		},
      		$zohosq.language=function(value) 
      		{
      			if(value)$zv["language"]=value;
      			return $zv["language"]; 
      		},
      		$zohosq.set=function(object) 
      		{
				for(var d in object)
				{
					try 
					{
						var s=d.split("."); 
						var func=this[s[0]]; 
						for(var i=1;i < s.length;i++)
						{
							if(s[i])
							{
								func=func[s[i]]; 
							}
						}
						if(typeof func=="function")
						{
							func(object[d]); 
						}
					 }
					 catch(e){} 
				}				      			
      		}
      		$zohosq.setValue=function(type,value,operation)
      		{
      			if(value==null || !type){return;}
      			$zv[type]=value;
      			broadcastMessage(type,value);
      			
      			if(operation)
      			{
     				var obj={};obj[type]=value;
      				$ZNotifyTracking(operation,obj);
      			}
      		}
      		
      		$zoho.ld={};
      		$zoho.ld.handle=
      		{
      			customClick:function(status)
      			{
      				var carr=$zohosq.values["customhtml"];
      				if(!carr)return false;
      				var cobj=carr[1],func=cobj[status+'.click'];
      				if(typeof func=="function")
      				{
      					func();
      				}
      				else
      				{
      					try
      					{
      						Float.openWindow(); //No I18N
      					}
      					catch(e)
      					{
      						try
      						{
      							zlsHandleCustomClick(); //No I18N
      						}
      						catch(e)
      						{}
      					}
      				}
      			}
      		}
      		}catch(e){} 
      		
function $ZNotifyTracking(operation,value)
{
	try
	{
		$UTS.Notifier.handleApiChange(operation,value); //NO I18N
	}
	catch(e)
	{
	}
}
function $ZShandleEvent(event,data)
{
	try
	{
		$zohosq.handleAnalyticEvents(event,data);
	}
	catch(e)
	{
	}
}
      		
function broadcastMessage(type,value)
{
	try{Float.broadcastMessage(type,value);}catch(e){}
	try{AgentsChat.broadcastMessage(type,value);}catch(e){}
	try{zlsWinBroadcastMessage(type,value)}catch(e){}
	try{zlsBtnBroadcastMessage(type,value)}catch(e){}
}

function identifySIQParam() 
{

    var urldecode = function (str) //No I18N
    {
        return decodeURIComponent((str + '').replace(/\+/g, '%20')); //No I18N
    };

    var removeSIQParamFromURL = function () //No I18N
    {
        var url = location.href.split('?')[0]; //No I18N
        var query = location.search;           //No I18N
        var anchor = location.hash;            //No I18N

        if (!query) 
        {
            return;     //No I18N
        }

        query = query.replace(/^\?/g, ''); //No I18N
        query = query.replace(/(^|&)siq_(name|email)=[^&]*/g, ''); //No I18N
        query = query.replace(/^&/g, '');  //No I18N

        if (query) 
        {
            var final_url = url + "?" + query + anchor; //No I18N
        } else 
        {
            var final_url = url + anchor;               //No I18N
        }

        window.history.replaceState([], '', final_url); //No I18N
    };

    try { //No I18N
        var wparams = {};                     //No I18N
        var param = window.location.search;   //No I18N

        if (!param) 
        {
            return;
        }

        var param = param.replace(/^\?/g, '');
        var params_array = param.split("&");  //No I18N
        var temp = [];                       //No I18N

        for (var i = 0; i < params_array.length; i++) 
        {
            temp = params_array[i].split("=");                 //No I18N
            wparams[urldecode(temp[0])] = urldecode(temp[1]);  //No I18N
        }

        if (wparams["siq_email"] != null) 
        {
            $zohosq.visitor.email(wparams["siq_email"]);       //No I18N
        }

        if (wparams["siq_name"] != null) 
        {
            $zohosq.visitor.name(wparams["siq_name"]);         //No I18N
        }

        removeSIQParamFromURL();

    } catch (e) {} //No I18N		
}








	

	try //No I18N
	{
        	if(document.readyState==="complete")
       	 	{
                	ZloadFiles(); //No I18N
        	}
        	else //No I18N
        	{
                	ZaddEvent(); //No I18N
        	}
	}
	catch(e) //No I18N
	{
        	ZaddEvent(); //No I18N
	}
  }catch(e){}

