jQuery.fn.extend({
	switchClass: function(className){
		if( className && $type(className) == 'string' ){
			this.setProperty( 'class', className );
		}
		return this;
	},
	makeHovered: function(start, end){
		start	= start || 0.7;
		end	= end	|| 1;
		$(this).hover(
		function(){
			$(this).css({opacity:end});
		},
		function(){
			$(this).css({opacity:start});
		});
		return $(this).css({opacity:start});
	},
	rolloverClass: function(start, end){
		start	= start || 'not_a_class';
		end		= end || 'not_a_class';
		this.rollover = {over : 'start', out: 'end'};
		this.addEvent('mouseover', function(){
			this.removeClass( this.rollover.over ).addClass( this.rollover.out );
		}).addEvent('mouseout', function(){
			this.removeClass( this.rollover.out ).addClass( this.rollover.over );
		}).addClass( this.rollover.out );
		return this;
	}
});

var SITE = {
	floatPicHandler: 0,
	popup_image: function(url){
		if( url ){
			if( !this.floatPicHandler ){
				this.floatPicHandler = new floatPicture();
			}
			this.floatPicHandler.show( url );
		}
	}
};

$(document).ready( function(){
	var makeMenu = function(con)
	{
		var container = $(con);
		var items = $(con + 'Items');
		
		items.css('display','block').hide();
		//$( document.body ).append( items );

		var hidefunc = function(){
			items.fadeOut(500);
		}			
		var hidetimer = 0;
		
		items.find('div').hover(
			function(){
				$(this).addClass('HidenMenuItem_hover');
				window.clearTimeout(hidetimer);
				items.fadeIn(500);
			},
			function(){
				$(this).removeClass('HidenMenuItem_hover');
				hidetimer = window.setTimeout(hidefunc, 250);
			}
		).click( function(){
			var url = $(this).attr('rel');
			if( url ){
				window.location = url;
			}
		});
		
		container.hover(
			function(){
				var pos = $(this).position();
				items.css({top:pos.top + $(this).height(),left:pos.left,width:$(this).width()});
				items.fadeIn(500);
				$(this).addClass('hidenMenu_hover');
				window.clearTimeout(hidetimer);
			},
			function(){
				hidetimer = window.setTimeout(hidefunc, 250);
				$(this).removeClass('hidenMenu_hover');
			}
		)
	}
	
	makeMenu('#menuMain');	
	makeMenu('#menuFab');
	makeMenu('#menuPub');
	makeMenu('#menuOther');	
	makeMenu('#menuLang');
	
	/*$$('img').each( function(item){
		fixPNG(item);
	});*/
		
	$('img[res]').makeHovered().click(function(){
		var url = $(this).attr('res');
		if( url ){
			SITE.popup_image( url );
		}
	});
	
	$('div.fabPicture').makeHovered().click(function(){
		var url = $(this).attr('res');
		if( url ){
			SITE.popup_image( url );
		}
	});
	
	$('.hovered').makeHovered();
});