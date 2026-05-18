(function ($, undefined) {

	var Field = acf.Field.extend(
		{

			type: 'icon_select',

			select2: false,

			wait: 'load',

			events: {
				'removeField': 'onRemove',
				'duplicateField': 'onDuplicate'
			},

			$input: function () {
				return this.$( 'select' );
			},

			initialize: function () {

				// vars
				var $select = this.$input();

				// inherit data
				this.inherit( $select );

				// select2
				if ( this.get( 'ui' ) ) {

					// populate ajax_data (allowing custom attribute to already exist)
					var ajaxAction = this.get( 'ajax_action' );
					if ( ! ajaxAction ) {
						ajaxAction = 'acf/fields/' + this.get( 'type' ) + '/query';
					}

					// select2
					this.select2 = acf.newSelect2(
						$select,
						{
							field: this,
							ajax: this.get( 'ajax' ),
							multiple: this.get( 'multiple' ),
							placeholder: this.get( 'placeholder' ),
							allowNull: this.get( 'allow_null' ),
							ajaxAction: ajaxAction,
						}
					);

				}
			},

			onRemove: function () {
				if ( this.select2 ) {
					this.select2.destroy();
				}
			},

			onDuplicate: function ( e, $el, $duplicate ) {
				if ( this.select2 ) {
					$duplicate.find( '.select2-container' ).remove();
					$duplicate.find( 'select' ).removeClass( 'select2-hidden-accessible' );
				}
			}
		}
	);

	acf.registerFieldType( Field );

	var HasIconSelectValue = acf.Condition.extend(
		{
			type: 'HasIconSelectValue',
			operator: '!=empty',
			label: 'Has any value',
			fieldTypes: [ 'icon_select' ],
			match: function ( rule, field ) {
				return (field.val() ? true : false);
			},
			choices: function ( fieldObject ) {
				return '<input type="text" disabled="" />';
			}
		}
	);

	acf.registerConditionType( HasIconSelectValue );

	var IconSelectContains = acf.Condition.extend(
		{
			type: 'contains',
			operator: '==contains',
			label: 'Value contains',
			fieldTypes: [ 'icon_select' ],
			match: function ( rule, field ) {
				return containsString( field.val(), rule.value );
			},
			choices: function ( fieldObject ) {
				return '<input type="text" />';
			}
		}
	);

	acf.registerConditionType( IconSelectContains );

})( jQuery );