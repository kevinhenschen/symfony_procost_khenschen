(function () {
	"use strict";

	var treeviewMenu = $('.app-menu');


	// Initialise previous SideNav State
	$(document).ready(function(){
		let state = parseInt(window.localStorage.getItem('sidenav-state')) === 1 ? 'sidenav-toggled' : '';
		$('.app, .app-sidebar, .app-content').addClass('notransition');

			if(state !== ''){
				$('.app').addClass(state);
			}else{
				$('.app').removeClass('sidenav-toggled');
			}

		setTimeout(function(){
			$('.app, .app-sidebar, .app-content').addClass('notransition');
		},500);

	});

	// Toggle Sidebar
	$('[data-toggle="sidebar"]').click(function(event) {
		event.preventDefault();
		$('.app').toggleClass('sidenav-toggled');

		// TODO : voir si sa marche bien comme il faut
		window.localStorage.setItem('sidenav-state', $('.app').hasClass('sidenav-toggled') ? '1' : '0');

	});

	// Activate sidebar treeview toggle
	$("[data-toggle='treeview']").click(function(event) {
		event.preventDefault();
		if(!$(this).parent().hasClass('is-expanded')) {
			treeviewMenu.find("[data-toggle='treeview']").parent().removeClass('is-expanded');
		}
		$(this).parent().toggleClass('is-expanded');
	});

	// Set initial active toggle
	$("[data-toggle='treeview.'].is-expanded").parent().toggleClass('is-expanded');

	//Activate bootstrip tooltips
	$("[data-toggle='tooltip']").tooltip();

})();
