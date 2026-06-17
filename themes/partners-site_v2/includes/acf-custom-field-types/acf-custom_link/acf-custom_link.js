(function ($, undefined) {

	var Field = acf.Field.extend(
		{
			type: 'custom_link',
			events: {
				'click a[data-name="add"]': 'onClickEdit',
				'click a[data-name="edit"]': 'onClickEdit',
				'click a[data-name="remove"]': 'onClickRemove',
				'change .link-node': 'onChange',
			},
			$control: function () {
				return this.$( '.acf-custom-link' );
			},
			$node: function () {
				return this.$( '.link-node' );
			},
			getValue: function () {
				// vars
				var $node = this.$node();

				// return false if empty
				if ( ! $node.attr( 'href' )) {
					return false;
				}

				// return
				return {
					title: $node.html(),
					url: $node.attr( 'href' ),
					target: $node.attr( 'target' )
				};
			},

			setValue: function (val) {
				// default
				val = acf.parseArgs(
					val,
					{
						title: '',
						url: '',
						target: ''
					}
				);

				// vars
				var $div  = this.$control();
				var $node = this.$node();

				// remove class
				$div.removeClass( '-value -external' );

				// add class
				if (val.url) {
					$div.addClass( '-value' );
				}
				if (val.target === '_blank') {
					$div.addClass( '-external' );
				}

				// update text
				this.$( '.link-title' ).html( val.title );
				this.$( '.link-url' ).attr( 'href', val.url ).html( val.url );

				// update node
				$node.html( val.title );
				$node.attr( 'href', val.url );
				$node.attr( 'target', val.target );

				// update inputs
				this.$( '.input-title' ).val( val.title );
				this.$( '.input-target' ).val( val.target );
				this.$( '.input-url' ).val( val.url ).trigger( 'change' );
			},

			onClickEdit: function (e, $el) {
				acf.wpCustomLink.open( this.$node() );
			},

			onClickRemove: function (e, $el) {
				this.setValue( false );
			},

			onChange: function (e, $el) {
				// get the changed value
				var val = this.getValue();

				// update inputs
				this.setValue( val );
			}
		}
	);

	acf.registerFieldType( Field );

	// manager
	acf.wpCustomLink = new acf.Model(
		{
			textSelect: {},
			getNodeValue: function () {
				var $node = this.get( 'node' );
				return {
					title: acf.decode( $node.html() ),
					url: $node.attr( 'href' ),
					target: $node.attr( 'target' )
				};
			},
			setNodeValue: function (val) {
				var $node = this.get( 'node' );
				$node.text( val.title );
				$node.attr( 'href', val.url );
				$node.attr( 'target', val.target );
				$node.trigger( 'change' );
			},
			getInputValue: function () {
				return {
					title: $( '#wp-link-text' ).val(),
					url: $( '#wp-link-url' ).val(),
					target: $( '#wp-link-target' ).prop( 'checked' ) ? '_blank' : ''
				};
			},
			setInputValue: function (val) {
				$( '#wp-link-text' ).val( val.title );
				$( '#wp-link-url' ).val( val.url );
				$( '#wp-link-target' ).prop( 'checked', val.target === '_blank' );
			},
			open: function ($node) {
				// add events
				this.on( 'wplink-open', 'onOpen' );
				this.on( 'wplink-close', 'onClose' );

				// set node
				this.set( 'node', $node );

				this.addTextSelect( $node )

				// create textarea
				var $textarea = $( '<textarea id="acf-custom-link-textarea" style="display:none;"></textarea>' );
				$( 'body' ).append( $textarea );

				// vars
				var val = this.getNodeValue();

				// open popup
				wpLink.open( 'acf-custom-link-textarea', val.url, val.title, null );
			},
			addTextSelect: function ($node) {
				var val         = this.getNodeValue();
				var structure   = $( '<label class="wp-link-select-field"><span>Tekst odnośnika</span><select style="margin-left:5px;"></select></label>' )
				this.textSelect = {
					textChoices: $node.data( 'text-choices' ).split( ',' ),
					textWrap: $( '.wp-link-text-field' ),
					selectWrap: structure,
					select: structure.find( 'select' )
				}

				// build select
				this.textSelect.textChoices.forEach(
					$.proxy(
						function (text) {
							this.textSelect.select.append( $( '<option value="' + text + '">' + text + '</option>' ) )
						},
						this
					)
				)
				this.textSelect.select.append( $( '<option value="custom">Tekst własny</option>' ) )

				// choose select option
				if (val.title !== '') {
					if (this.textSelect.textChoices.includes( val.title )) {
						this.textSelect.select.val( val.title )
					} else {
						this.textSelect.select.val( 'custom' )
					}
				}

				if (this.textSelect.select.val() !== 'custom') {
					this.textSelect.textWrap.hide()
				} else {
					$( '#wp-link-wrap' ).addClass( 'has-visible-text-select' )
				}

				this.textSelect.select.on(
					'change',
					$.proxy(
						function (e) {
							this.onSelectChange()
						},
						this
					)
				)

				this.textSelect.selectWrap.insertBefore( this.textSelect.textWrap )

				this.textSelect.textWrap.find( 'span' ).text( 'Tekst własny' )
			},
			removeTextSelect: function () {
				this.textSelect.selectWrap.remove()
				$( '#wp-link-wrap' ).removeClass( 'has-visible-text-select' )
				this.textSelect.textWrap.show()
				this.textSelect.textWrap.find( 'span' ).text( 'Tekst odnośnika' )
			},
			onSelectChange: function () {
				var title = this.textSelect.select.val()
				if (title === 'custom') {
					title = '';
					this.textSelect.textWrap.slideDown( 400 )
					$( '#wp-link-wrap' ).addClass( 'has-visible-text-select' )
				} else {
					this.textSelect.textWrap.slideUp( 400 )
					$( '#wp-link-wrap' ).removeClass( 'has-visible-text-select' )
				}
				$( '#wp-link-text' ).val( title );
			},
			onOpen: function () {
				// always show title (WP will hide title if empty)
				$( '#wp-link-wrap' ).addClass( 'has-text-field' );
				$( '#wp-link-wrap' ).addClass( 'is-custom-link-wrap' )

				// set inputs
				var val = this.getNodeValue();
				if ( ! val.title) {
					val.title = this.textSelect.textChoices[0]
				}
				this.setInputValue( val );

				// Update button text.
				if (val.url && wpLinkL10n) {
					$( '#wp-link-submit' ).val( wpLinkL10n.update );
				}
			},
			close: function () {
				wpLink.close();
			},
			onClose: function () {
				this.removeTextSelect()
				// Bail early if no node.
				// Needed due to WP triggering this event twice.
				if ( ! this.has( 'node' )) {
					return false;
				}

				// Determine context.
				var $submit  = $( '#wp-link-submit' );
				var isSubmit = ($submit.is( ':hover' ) || $submit.is( ':focus' ));

				// Set value
				if (isSubmit) {
					var val = this.getInputValue();
					this.setNodeValue( val );
				}

				// Cleanup.
				this.off( 'wplink-open' );
				this.off( 'wplink-close' );
				$( '#acf-custom-link-textarea' ).remove();
				$( '#wp-link-wrap' ).removeClass( 'is-custom-link-wrap' )
				this.set( 'node', null );
			}
		}
	);
})( jQuery );