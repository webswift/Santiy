
			$(document).ready(function(){
				$("#eur").click(function(){
					$("#peep").text("€12");
					$("#eur").addClass( "active" );
					$("#dollar").removeClass( "active" );
					$("#gbp").removeClass( "active" );
					
				});
				$("#dollar").click(function(){
					$("#peep").text("$14");
					$("#dollar").addClass( "active" );
					$("#eur").removeClass( "active" );
					$("#gbp").removeClass( "active" );
				});
				$("#gbp").click(function(){
					$("#peep").text("£10");
					$("#gbp").addClass( "active" );
					$("#eur").removeClass( "active" );
					$("#dollar").removeClass( "active" );
				});
			});
