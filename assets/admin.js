/*!
 * @package PB_Portfolio
 * @since 1.0
 */
(function($) {
	"use strict";

	// Multi Items.
	$(document).on('click', '#pb-portfolio-detail-add-button', function(e) {
		e.preventDefault();
		var $_parent = $(this).parents('#pb-portfolio-details'),
		    $_wrap   = $_parent.find('tbody'),
		    $_item   = $_wrap.find('tr'),
		    $_html   = $_item.html(),
		    $_input  = '<tr>'+ $_html +'</tr>';
		$($_input).each(function() {
			$(this).find(':input').val('');
			$(this).appendTo($_wrap);
		});
		$_wrap.pb_portfolio_detail_resetName();
		return false;
	});

	$.fn.pb_portfolio_detail_resetName = function(){
		this.find('tr').each( function (index){
			var __this = $(this).index();
			$(this).find(':input').each(function(){
				if( $(this).attr('name') !== "undefined" && $(this).attr('name') ){
					var __reset = $(this).attr('name').replace(/(.*)\[[0-9]+\]/g, "$1["+ __this +"]");
				}
				$(this).attr('name', __reset);
			});
		});
	};

	// Remove.
	$(document).on('click', '.pb-portfolio-detail-remove-button', function(e) {
		e.preventDefault();
		var $_parent = $(this).closest('tbody');
		if( $_parent.find('tr').length === 1 ) {
			$_parent.find('tr').find(':input').val('');
			return false;
		}
		$(this).closest('tr').slideUp('normal', function() {
			$(this).remove();
		});
		return false;
	});

	// Sortable.
	function pb_portfolioSortable() {
		if ( ! $.fn.sortable ) {
			return;
		};
		$('.pb-portfolio-details-table tbody').sortable({
			forcePlaceholderSize: false,
			placeholder: 'pb-portfoli-sortable-move',
			opacity: 0.6,
			handle: '',
			start: function (event, ui) {
				var el_width = ui.item.width();
				var el_height = ui.item.height();
				$('.pb-portfoli-sortable-move').width(el_width - 12);
				$('.pb-portfoli-sortable-move').height(el_height);
			},
			update: function () {
				$(this).pb_portfolio_detail_resetName();
			}

		});
	};
	pb_portfolioSortable();

	// Show/Hide Boxes as needed
	$(function() {
		var metaboxes = [
			{ 
				'handle' : $('#_pb_portfolio_display_gallery'),
				'metabox' : $('#pb-portfolio-gallery') 
			},
			{ 
				'handle' : $('#_pb_portfolio_display_video'),
				'metabox' : $('#pb-portfolio-video') 
			},
			{ 
				'handle' : $('#_pb_portfolio_display_audio'),
				'metabox' : $('#pb-portfolio-audio') 
			}
		];

		for( var i = 0 ; i < metaboxes.length ; i++ ) {
			if( metaboxes[i].handle.is(':checked') ) {
				metaboxes[i].metabox.css('display', 'block');
			} else {
				metaboxes[i].metabox.css('display', 'none');
			}

			metaboxes[i].handle.on('click', function() {
				var $this = $(this),
						metaboxId = '#' + $this.data('display-id');
						
				if( $this.is(':checked') ) {
					$(metaboxId).css('display', 'block');
				} else {
					$(metaboxId).css('display', 'none');
				}
			});
		}
	});

	// Media Manager for Galleries
	$(function() {
		var frame,
		    images = $('#_pb_portfolio_images').val(),
		    selection = loadImages(images);

		$('#pb-portfolio-gallery-upload').on('click', function(e) {
			e.preventDefault();

			// Set options for 1st frame render
			var options = {
				title: PB_Portfolio_Localize.createText,
				state: 'gallery-edit',
				frame: 'post',
				selection: selection
			};

			// Check if frame or gallery already exist
			if( frame || selection ) {
				options['title'] = PB_Portfolio_Localize.editText;
			}

			frame = wp.media(options).open();
			
			// Tweak views
			frame.menu.get('view').unset('cancel');
			frame.menu.get('view').unset('separateCancel');
			frame.menu.get('view').get('gallery-edit').el.innerHTML = PB_Portfolio_Localize.editText;
			frame.content.get('view').sidebar.unset('gallery'); // Hide Gallery Settings in sidebar

			// When we are editing a gallery
			overrideGalleryInsert();
			frame.on( 'toolbar:render:gallery-edit', function() {
				overrideGalleryInsert();
			});
			
			frame.on( 'content:render:browse', function( browser ) {
		    if ( !browser ) return;
		    // Hide Gallery Settings in sidebar
		    browser.sidebar.on('ready', function(){
	        browser.sidebar.unset('gallery');
		    });
		    // Hide filter/search as they don't work
		    browser.toolbar.on('ready', function(){
			    if(browser.toolbar.controller._state == 'gallery-library'){
		        browser.toolbar.$el.hide();
			    }
		    });
			});
			
			// All images removed
			frame.state().get('library').on( 'remove', function() {
		    var models = frame.state().get('library');
				if(models.length == 0){
			    selection = false;
					$.post( PB_Portfolio_Localize.ajax_url, { ids: '', action: 'pb_portfolio_ajax', nonce: PB_Portfolio_Localize.nonce });
				}
			});
			
			// Override insert button
			function overrideGalleryInsert() {
				frame.toolbar.get('view').set({
					insert: {
						style: 'primary',
						text: PB_Portfolio_Localize.saveText,
						click: function() {
							var models = frame.state().get('library'),
						    ids = '';

							models.each( function( attachment ) {
						    ids += attachment.id + ','
							});

							this.el.innerHTML = PB_Portfolio_Localize.savingText;
								
							$.ajax({
								type: 'POST',
								url: PB_Portfolio_Localize.ajax_url,
								data: { 
									ids: ids, 
									action: 'pb_portfolio_ajax',
									nonce: PB_Portfolio_Localize.nonce 
								},
								success: function() {
									selection = loadImages(ids);
									$('#_pb_portfolio_images').val( ids );
									frame.close();
								},
								dataType: 'html'
							}).done( function( data ) {
								$('#pb-portfolio-gallery-images').html( data );
							}); 
						}
					}
				});
			}
		});
		
		// Load images
		function loadImages(images) {
			if( images ){
		    var shortcode = new wp.shortcode({
  					tag:    'gallery',
  					attrs:   { ids: images },
  					type:   'single'
  			});

		    var attachments = wp.media.gallery.attachments( shortcode );

				var selection = new wp.media.model.Selection( attachments.models, {
  					props:    attachments.props.toJSON(),
  					multiple: true
  				}
  			);
      
				selection.gallery = attachments.gallery;
      
				// Fetch the query's attachments, and then break ties from the
				// query to allow for sorting.
				selection.more().done( function() {
					// Break ties with the query.
					selection.props.set({ query: false });
					selection.unmirror();
					selection.props.unset('orderby');
				});
      				
				return selection;
			}	
			return false;
		}
	});

})(jQuery);
