	tinyMCE.init({
		// General options
		mode : "specific_textareas",
		editor_selector : "mceEditor",		
		theme : "advanced",
		plugins : "embed,media,table,advhr,advimage,advlink,searchreplace,contextmenu,paste,visualchars,nonbreaking,xhtmlxtras",

		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,|,image,media,embed",
		theme_advanced_buttons2 : "cut,copy,paste,ppasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,cleanup,help,code,|,forecolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat|,sub,sup,|,charmap,advhr,|,styleprops,visualchars,nonbreaking",
		// theme_advanced_buttons4 : "styleprops,visualchars,nonbreaking",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Example content CSS (should be your site CSS)
		content_css : "/tinymce_style.css",
		
		relative_urls : false,
		remove_script_host : true,
		document_base_url : "/",
		
		media_strict : false, 

		// Drop lists for link/image/media/template dialogs
		/*
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",
		*/
		
		file_browser_callback : "tinyBrowser",

		table_styles : "Tabela MSS=table-mss",

		// Replace values for the template plugin
		template_replace_values : {
			username : "Some User",
			staffid : "991234"
		}
	});
