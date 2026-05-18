(function ($, undefined) {

	var HasValue = acf.Condition.extend(
		{
			type: 'hasValue',
			operator: '!=empty',
			label: 'Has any value',
			fieldTypes: [ 'network_post_object' ],
			match: function ( rule, field ) {
				return (field.val() ? true : false);
			},
			choices: function ( fieldObject ) {
				return '<input type="text" disabled="" />';
			}
		}
	);

	acf.registerConditionType( HasValue );

	var GreaterThan = acf.Condition.extend(
		{
			type: 'greaterThan',
			operator: '>',
			label: 'Value is greater than',
			fieldTypes: [ 'number', 'range' ],
			match: function ( rule, field ) {
				var val = field.val();
				if ( val instanceof Array ) {
					val = val.length;
				}
				return isGreaterThan( val, rule.value );
			},
			choices: function ( fieldObject ) {
				return '<input type="number" />';
			}
		}
	);

	acf.registerConditionType( GreaterThan );

	var SelectionGreaterThan = GreaterThan.extend(
		{
			type: 'selectionGreaterThan',
			label: 'Selection is greater than',
			fieldTypes: [ 'network_post_object' ],
		}
	);

	acf.registerConditionType( SelectionGreaterThan );

	var LessThan = GreaterThan.extend(
		{
			type: 'lessThan',
			operator: '<',
			label: 'Value is less than',
			match: function ( rule, field ) {
				var val = field.val();
				if ( val instanceof Array ) {
					val = val.length;
				}
				return isLessThan( val, rule.value );
			},
			choices: function ( fieldObject ) {
				return '<input type="number" />';
			}
		}
	);

	acf.registerConditionType( LessThan );

	var SelectionLessThan = LessThan.extend(
		{
			type: 'selectionLessThan',
			label: 'Selection is less than',
			fieldTypes: [ 'network_post_object' ],
		}
	);

	acf.registerConditionType( SelectionLessThan );

	var Field = acf.models.SelectField.extend(
		{
			type: 'network_post_object',
		}
	);

	acf.registerFieldType( Field );

})( jQuery );