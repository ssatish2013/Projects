$(function(){
	jQuery.validator.setDefaults({
		"errorElement":"span"
	});
	jQuery.validator.addMethod("conditional", function(value, element) {
		var el = $(element),
		conditional = el.data('validateRequiredConditional');
		if (PF.page.validation[conditional] && typeof PF.page.validation[conditional]==='function') {
			return PF.page.validation[conditional].apply();
		}
	}, "conditional");
	jQuery.validator.addMethod("sameTo", function(value, element) {
		var el = $(element),
		target = el.data('validateSameTo'),
		l = el.val(),
		r = $(target).val();
		if (l && r)
		{
			return l.toLowerCase() == r.toLowerCase();
		}
		else{
			return l == r;
		}
	}, "Not Same");
	jQuery.validator.addMethod("timezone", function(value, element) { 
		var el = $(element);
		var value = el.find("option:selected").val();
		return (value != "") ? true : false;
	}, "Please select a timezone");
	jQuery.validator.addMethod("mobile", function(value, element) { 
		var el = $(element);
		return /^([0-9]{1,2}\-?)?[0-9]{3}\-?[0-9]{3}\-?[0-9]{4}$/.test(el.val());
	}, "Invalid mobile phone number");
	$("form.validate").each(function(i,e){
		var el = $(e);
		el.validate({
			invalidHandler: function(e, validator){
				var errors = validator.numberOfInvalids();
				if(errors){

				}
			},
			submitHandler: function(form){
				if (!$(form).hasClass("xhr")) {
					form.submit();
				}
			},
			errorPlacement: function(error, element){
				$(element).addClass('error');
				if($(element).data('validateErrorTarget')){
					error.appendTo($($(element).data('validateErrorTarget')));
				} else {
					error.appendTo(element.parent());
				}
				window.PF.xdm.resize(true, {extraHeight: 35});
			}
		});
		el.find("[data-validate-required]").each(function(i,e){
			var el = $(e);
			el.rules("add",{required:true});
			if(el.data('validateRequiredMessage')){
				el.rules("add",{
					messages:{
						required: el.data('validateRequiredMessage')
					}
				});
			}
		});
		el.find("[data-validate-minlength]").each(function(i,e){
			var el = $(e);
			el.rules("add",{minlength:el.data('validateMinlength')});
			if(el.data('validateMinlengthMessage')){
				el.rules("add",{
					messages:{
						minlength: el.data('validateMinlengthMessage')
					}
				});
			}
		});
		el.find("[data-validate-maxlength]").each(function(i,e){
			var el = $(e);
			el.rules("add",{maxlength:el.data('validateMaxlength')});
			if(el.data('validateMaxlengthMessage')){
				el.rules("add",{
					messages:{
						maxlength: el.data('validateMaxlengthMessage')
					}
				});
			}
		});
		el.find("[data-validate-min]").each(function(i,e){
			var el = $(e);
			el.rules("add",{min:el.data('validateMin')});
			if(el.data('validateMinMessage')){
				el.rules("add",{
					messages:{
						min: el.data('validateMinMessage')
					}
				});
			}
		});
		el.find("[data-validate-max]").each(function(i,e){
			var el = $(e);
			el.rules("add",{max:el.data('validateMax')});
			if(el.data('validateMaxMessage')){
				el.rules("add",{
					messages:{
						max: el.data('validateMaxMessage')
					}
				});
			}
		});
		el.find("[data-validate-email]").each(function(i,e){
			var el = $(e);
			el.rules("add",{email:true});
			if(el.data('validateEmailMessage')){
				el.rules("add", {
					messages:{
						email: el.data('validateEmailMessage')
					}
				});
			}
		});
		el.find("[data-validate-url]").each(function(i,e){
			var el = $(e);
			el.rules("add",{url:true});
			if(el.data('validateUrlMessage')){
				el.rules("add",{
					messages:{
						url: el.data('validateUrlMessage')
					}
				});
			}
		});
		el.find("[data-validate-date]").each(function(i,e){
			var el = $(e);
			el.rules("add",{date:true});
			if(el.data('validateDateMessage')){
				el.rules("add",{
					messages:{
						date: el.data('validateDateMessage')
					}
				});
			}
		});
		el.find("[data-validate-number]").each(function(i,e){
			var el = $(e);
			el.rules("add",{number:true});
			if(el.data('validateNumberMessage')){
				el.rules("add",{
					messages:{
						number: el.data('validateNumberMessage')
					}
				});
			}
		});
		el.find("[data-validate-digits]").each(function(i,e){
			var el = $(e);
			el.rules("add",{digits:true});
			if(el.data('validateDigitsMessage')){
				el.rules("add",{
					messages:{
						digits: el.data('validateDigitsMessage')
					}
				});
			}
		});
		el.find("[data-validate-creditcard]").each(function(i,e){
			var el = $(e);
			el.rules("add",{creditcard:true});
			if(el.data('validateCreditcardMessage')){
				el.rules("add",{
					messages:{
						creditcard: el.data('validateCreditcardMessage')
					}
				});
			}
		});
		el.find("[data-validate-accept]").each(function(i,e){
			var el = $(e);
			el.rules("add",{accept:el.data('validateAccept')});
			if(el.data('validateAcceptMessage')){
				el.rules("add",{
					messages:{
						accept: el.data('validateAcceptMessage')
					}
				});
			}
		});
		el.find("[data-validate-equal-to]").each(function(i,e){
			var el = $(e);
			el.rules("add",{equalTo: el.data('validateEqualTo')});
			if(el.data('validateEqualToMessage')){
				el.rules("add",{
					messages:{
						equalTo: el.data('validateEqualToMessage')
					}
				});
			}
		});
		el.find("[data-validate-same-to]").each(function(i,e){
			var el = $(e);
			el.rules("add",{sameTo: el.data('validateSameTo')});
			if(el.data('validateSameToMessage')){
				el.rules("add",{
					messages:{
						sameTo: el.data('validateSameToMessage')
					}
				});
			}
		});
		//Only if a target element has the value equals provided value then this element is required.
		//expecting data format: a callback function in PF.page.validation['function_name']
		el.find("[data-validate-required-conditional]").each(function(i,e){
			var el = $(e);
			el.rules("add",{conditional:true});
			if(el.data('validateRequiredMessage')){
				el.rules("add",{
					messages:{
						conditional: el.data('validateRequiredMessage')
					}
				});
			}
		});
		el.find("[data-validate-mobile]").each(function(i,e){
			var el = $(e);
			el.rules("add",{mobile:true});
			if(el.data('validateMobileMessage')){
				el.rules("add",{
					messages:{
						mobile:el.data('validateMobileMessage')
					}
				});
			}
		});
		el.find("[data-validate-serverside]").each(function(i,e){
			var el = $(e);
			//to make things simple, hard code to use five parameters max
			param1 = el.data('serverParamOne'),
			param2 = el.data('serverParamTwo'),
			param3 = el.data('serverParamThree'),
			param4 = el.data('serverParamFour'),
			param5 = el.data('serverParamFive');
			//the parameter can be a global function name
			if (param1 && param1.indexOf('window.')>=0){
				param1 = param1.replace('window.','');
				param1 = window[param1]();
			}
			if (param2 && param2.indexOf('window.')>=0){
				param2 = param2.replace('window.','');
				param2 = window[param2]();
			}
			if (param3 && param3.indexOf('window.')>=0){
				param3 = param3.replace('window.','');
				param3 = window[param3]();
			}
			if (param4 && param4.indexOf('window.')>=0){
				param4 = param4.replace('window.','');
				param4 = window[param4]();
			}
			if (param5 && param5.indexOf('window.')>=0){
				param5 = param5.replace('window.','');
				param5 = window[param5]();
			}
			el.rules("add",{
				required:false,
				remote: {
					url: el.data('serverMethodName'),
					type: "post",
					data: {
						'param1': param1,
						'param2': param2,
						'param3': param3,
						'param4': param4,
						'param5': param5
					}
				}
			});
			if(el.data('validateServerMessage')){
				el.rules("add",{
					messages:{
						'remote': el.data('validateServerMessage')
					}
				});
			}
		});
	});
});
