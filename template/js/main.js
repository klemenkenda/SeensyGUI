$(function() {
  	  $(".div-left").corner("right");
			$(".round").corner();
			$(".round-top").corner("top");
			$(".round-bottom").corner("bottom");
			$(".round-nested").corner("round 8px").parent().css('padding', '4px').corner("round 10px");
  	  $(".div-news-toc").corner();
			
			$("#div-newstoc-front .div-news-toc").mouseover(function() {
			  $(this).css("background", "#ded9f0")
			});
			$("#div-newstoc-front .div-news-toc").mouseout(function() {
			  $(this).css("background", "#8475bd")
			});	
			
			$("#div-newstoc-front-eng .div-news-toc").mouseover(function() {
			  $(this).css("background", "#ded9f0")
			});
			$("#div-newstoc-front-eng .div-news-toc").mouseout(function() {
			  $(this).css("background", "#8475bd")
			});	
			
			$("#div-poll-toc .div-news-toc").mouseover(function() {
			  $(this).css("background", "#ded9f0")
			});
			$("#div-poll-toc .div-news-toc").mouseout(function() {
			  $(this).css("background", "#8475bd")
			});	
			
			$("#div-newsmss-toc .div-news-toc").mouseover(function() {
			  $(this).css("background", "#ded9f0")
			});
			$("#div-newsmss-toc .div-news-toc").mouseout(function() {
			  $(this).css("background", "#8475bd")
			});			
			
			$("#div-newsinternational-toc .div-news-toc").mouseover(function() {
			  $(this).css("background", "white")
			});
			$("#div-newsinternational-toc .div-news-toc").mouseout(function() {
			  $(this).css("background", "#ded9f0")
			});		
			
			$("#div-newsslovenia-toc .div-news-toc").mouseover(function() {
			  $(this).css("background", "white")
			});
			$("#div-newsslovenia-toc .div-news-toc").mouseout(function() {
			  $(this).css("background", "#e0e0e0")
			});	
			
			// blog naslovnica
  		$("#div-blog-front").click(function() {
				  window.location = $("#span-blog-front").children("a:first").attr("href");
	 		});
				
  		$("#div-blog-front").mouseover(function() {
  	    $(this).css("cursor", "pointer");
  		})		
			
			$(".table-mss tr:odd").addClass("tr-odd");
			$(".table-mss tr:first").addClass("tr-first");
			
			// jQ - FancyBox
    	/* This is basic - uses default settings */	
    	$("a.group").fancybox({
			  'type': 'image'
			});

			/*
			div#menu li:hover>ul {
    left: -2px;
}*/
			
			$("ul.menu li").each(function(index) {
			  $(this).mouseover(function() {
				  $(this).children("ul:first").css("left", "-2px");
				});
				
				$(this).mouseout(function() {
				  $(this).children("ul:first").css("left", "-999em");
				});
				
				
			});
			
			
		});
		
		
// Javascript originally by Patrick Griffiths and Dan Webb.
// http://htmldog.com/articles/suckerfish/dropdowns/
/*
sfHover = function() {
	var sfEls = document.getElementByClass("menu").getElementsByTagName("li");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" hover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" hover\\b"), "");
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);
*/
