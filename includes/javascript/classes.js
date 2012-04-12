var jsLanguage = {
	defines: [],
	setDateFormat: function (v){
		this.dateFormat = v;
	},
	set: function (k, v){
		this.defines[k] = v;
	},
	get: function (key){
		return this.defines[key] || '';
	},
	getDateFormat: function (type){
		return this.dateFormat[type];
	}
};

var jsConfig = {
	defines: [],
	set: function (k, v){
		this.defines[k] = v;
	},
	get: function (key){
		return this.defines[key] || 'Not Found';
	}
};

var jsCurrencies = {
	currency: {
		code: '',
		title: '',
		symbol_left: '',
		symbol_right: '',
		decimal_point: '',
		thousands_point: '',
		decimal_places: '',
		value: ''
	},
	setCode: function (val){
		this.currency.code = val;
	},
	setTitle: function (val){
		this.currency.title = val;
	},
	setSymbolLeft: function (val){
		this.currency.symbol_left = val;
	},
	setSymbolRight: function (val){
		this.currency.symbol_right = val;
	},
	setDecimalPoint: function (val){
		this.currency.decimal_point = val;
	},
	setThousandsPoint: function (val){
		this.currency.thousands_point = val;
	},
	setDecimalPlaces: function (val){
		this.currency.decimal_places = parseInt(val);
	},
	setValue: function (val){
		this.currency.value = parseFloat(val);
	},
	format: function (number){
		number = number * this.currency.value;

		return this.currency.symbol_left + number_format(
			number,
			this.currency.decimal_places,
			this.currency.decimal_point,
			this.currency.thousands_point
		) + this.currency.symbol_right;
	}
};
