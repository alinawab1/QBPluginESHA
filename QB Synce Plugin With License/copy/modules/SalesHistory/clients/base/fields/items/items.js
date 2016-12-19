/**
 * 
 */
({
	initialize: function (options) {
		this._super('initialize', arguments);
	},
	_render: function () {
		app.view.Field.prototype._render.call(this);
	},
	format: function (value) {
		return JSON.parse(value);
	}
})