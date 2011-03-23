(function allTests($) {
	function initFormFactory() {
		var styles = {
				formClass:     'formStyle',
				inputClass:    'inputStyle',
				comboClass:    'comboStyle',
				submitClass:   'submitStyle',
				containerType: 'div',
				containerClass: 'containerStyle'
		}, formFactory = createFormFactory(styles);
		return formFactory;
	}
	// ================================================================ //
	module('Preliminary Checks');
	test('Check if required functions are present', function () {
		equals(typeof(createFormFactory), 'function', 'createFormFactory function present');
		var formFactory = createFormFactory();
		// TODO: see how to test object type for private constructor
		equals(typeof(formFactory.createForm), 'function', 'createForm function present in factory object');
		equals(typeof(formFactory.createInput), 'function', 'createInput function present in factory object');
		equals(typeof(formFactory.createCombo), 'function', 'createCombo function present in factory object');
		equals(typeof(formFactory.createSubmit), 'function', 'createCombo function present in factory object');
	});
	
	
	// ================================================================ //
	module('Functional checks');
	
	test('Check for different types of form elements', function () {
		var formFactory = initFormFactory();
		
		// test createInput
		var test_input = {name:'some_name', type:'text', value:'value_1', description:'Description text:'};
		var form_input = formFactory.createInput(test_input);
		ok(form_input instanceof HTMLElement, 'createInput returns a DOM element');
		equals(form_input.nodeType, 1, 'Input has correct nodeType');
		
		// test createCombo
		var test_combo = {name:'myCombo', id: 'myCombo', type:'combo', value: ['cv_1','cv_2','cv_3'], description:'Description text:'};
		var form_combo = formFactory.createCombo(test_combo);
		ok(form_combo instanceof HTMLElement, 'createCombo returns a DOM element');
		
		// test createSubmit
		var test_submit = {name: 'submit'};
		var form_submit = formFactory.createSubmit(test_submit);
		ok(form_submit instanceof HTMLElement, 'createSubmit returns a DOM element');
		equals(form_submit.nodeType, 1, 'Submit has correct nodeType');
		
		var test_form = {
			name: 'formName',
			method: 'GET',
			action: 'script.php',
			elements: [
			   {name:'name_1', type:'text', value:'value_1', description:'Description text:'},
			   {name:'name_2', type:'text', value:'value_2', description:'Description text:'},
			   {name:'name_3', type:'text', value:'value_3', description:'Description text:'},
			   {name:'ajaxInput',  id:'ajaxInput',  type:'text', description:'Description text:'},
			   {name:'comboInput', id:'comboInput', type:'text', description:'Description text:'},
			   {name:'someCombo',  id:'someCombo',  type:'combo', description:'Description text:'},
			   {name:'mySubmit', id:'mySubmit', type:'submit', value:'trimite', description:'Description text:'}
			]
		};
		var form = formFactory.createForm(test_form);
		ok(form instanceof HTMLElement, 'createForm returns a DOM element');
		equals(form.nodeType, 1, 'Form has correct nodeType');
	});


	test('Input element checks', function () {
		var formFactory = initFormFactory();
		expect(5);
		var test_input = {name:'some_name', id: 'test_input', type:'text', value:'value_1', description:'Description text:'};
		var form_input = formFactory.createInput(test_input);
		equals(form_input.nodeType, 1, 'Input has correct nodeType');
		equals(form_input.getAttribute('name'), 'some_name', 'Name attribute created successfully');
		equals(form_input.value, 'value_1', 'Value attribute created successfully');
		equals(form_input.type, 'text', 'Type attribute created successfully');		
		equals(form_input.id, 'test_input', 'Id attribute created successfully');
		// TODO: test if exception is thrown when required fields are not present?
	});
	
	test('Combo element checks', function () {
		var i;
		var formFactory = initFormFactory();
		var test_combo = {name:'myCombo', id: 'myCombo', type:'combo', value: ['cv_1','cv_2','cv_3'], description:'Description text:'};
		var form_combo = formFactory.createCombo(test_combo);
		equals(form_combo.nodeType, 1, 'Combo has correct nodeType');
		equals(form_combo.getAttribute('name'), 'myCombo', 'Name attribute created successfully');
		equals(form_combo.id, 'myCombo', 'Id attribute created successfully');
		equals(form_combo.childNodes.length, test_combo.value.length, 'Correct number of values');
		for (i = 0; i < form_combo.childNodes.length; i++) {
			equals(form_combo.childNodes[i].tagName.toLowerCase(), 'option', 'Option element was created');
			equals(form_combo.childNodes[i].innerHTML, test_combo.value[i], 'Value was correctly inserted');
		}		
	});
	
	test('Submit element checks', function () {
		var formFactory = initFormFactory();
		var test_submit = {name: 'mySubmit', id: 'mySubmit', value: 'Send', description:'Description text:'};
		var form_submit = formFactory.createSubmit(test_submit);
		equals(form_submit.nodeType, 1, 'Combo has correct nodeType');
		equals(form_submit.getAttribute('name'), 'mySubmit', 'Name attribute created successfully');
		equals(form_submit.value, 'Send', 'Value created successfully');
		equals(form_submit.type, 'submit', 'Type attribute created successfully');		
		equals(form_submit.id, 'mySubmit', 'Id attribute created successfully');
		
	});
	
	test('Test complete form', function () {
		var i;
		var formFactory = initFormFactory();
		var form_factory_styles = {
				formClass:     'formStyle',
				inputClass:    'inputStyle',
				comboClass:    'comboStyle',
				submitClass:   'submitStyle',
				containerType: 'div',
				containerClass: 'containerStyle'
		};
		
		var form_info = {
			name: 'formName',
			method: 'GET',
			action: 'script.php',
			elements: [
			   {name:'name_1', type:'text', value:'value_1', description: 'Input label'},
			   {name:'myCombo', id: 'myCombo',   type:'combo', value: ['cv_1','cv_2','cv_3'], description: 'Combo label'},
			   {name:'mySubmit', id:'mySubmit', type:'submit', value:'trimite', description: 'Submit label'}
			]
		};
		
		var form = formFactory.createForm(form_info);
		equals(form.nodeType, 1, 'Form has correct nodeType');
		equals(form.getAttribute('action'), form_info.action, 'Form has correct action');
		equals(form.getAttribute('method'), form_info.method, 'Form has correct method');
		equals(form.childNodes.length, 3, 'Form has correct number of child nodes');
		equals(form.className, form_factory_styles.formClass);
		for (i = 0; i < form.childNodes.length; i++) {
			equals(form.childNodes[i].className, form_factory_styles.containerClass);
			equals(form.childNodes[i].tagName.toLowerCase(), form_factory_styles.containerType.toLowerCase());
		}
		equals($(form).find('input[type=text]').attr('class'), form_factory_styles.inputClass, 'Input class was correctly added.');
		equals($(form).find('select').attr('class'), form_factory_styles.comboClass, 'Combo class was correctly added.');
		equals($(form).find('input[type=submit]').attr('class'), form_factory_styles.submitClass, 'Submit class was correctly added.');
		console.log($(form));
		equals($(form).find('div > label').length, 3, 'Labels were inserted correctly for every element.');
		equals($(form).find('div > label').get(0).innerHTML, 'Input label', 'Input label was inserted correctly');
		equals($(form).find('div > label').get(1).innerHTML, 'Combo label', 'Combo label was inserted correctly');
		equals($(form).find('div > label').get(2).innerHTML, 'Submit label', 'Submit label was inserted correctly');
		equals(form.name, form_info.name, 'Form has correct name');
	});
	
	
	
	asyncTest('AJAX checks', function () {
		var formFactory = initFormFactory();
		// ajax metadata
		var ajax_info = { url: 'ajaxSource.php', event: 'onchange', target: 'myCombo' };
		var ajax_info_2 = { url:'ajaxSource.php', event: 'onclick',	target: 'mySubmit' };
		// form elements
		var test_ajax = { name: 'inputValue', id: 'inputValue', type: 'text', ajax: ajax_info, description: 'Description text:' };
		var test_ajax_2 = {	name: 'inputValue2', id: 'inputValue2', type: 'text', ajax: ajax_info_2, description: 'Description text:' };
		var test_combo = { name:'myCombo', id: 'myCombo', type: 'combo', description: 'Description text:' };
		var test_submit = { name: 'mySubmit', id: 'mySubmit', type: 'submit' };
		// form input
		var form_info = { name: 'formName', method: 'GET', action: 'script.php', elements: [ test_ajax, test_combo, test_ajax_2, test_submit ] };
		var form = formFactory.createForm(form_info);
		$("#test-container")[0].appendChild(form);
		
		notEqual($("#inputValue").attr(ajax_info.event), undefined, 'Event handler was attached correctly');
		
		// trigger event
		document.getElementById("inputValue").value = "test";
		document.getElementById("inputValue").onchange();
		// trigger event again
		document.getElementById("inputValue").onchange();
		
		// trigger other event 
		document.getElementById("inputValue2").value = "cv_1";
		document.getElementById("inputValue2").onclick();
		
		// wait 1s for the ajax calls to complete
	    setTimeout(function() {
	    	equals($('#myCombo option').length, 3);
			
	    	// test population of the dropdown
	    	$.get('ajaxSource.php', function (data) {
	    		var i, pair;
	    		var values = data.split('&');
				for (i = 0; i < values.length; i++) {
					pair = values[i].split('=');
					equals(pair[0], $('#myCombo option')[i].getAttribute('name'), 'Form name was inserted correctly via AJAX');
					equals(pair[1], $('#myCombo option')[i].value, 'Form value was inserted correctly via AJAX');
				}
			});
	    	
	    	// test value of the submit
	    	$.get('ajaxSource.php?data=cv_1', function (data) {
	    		equals(data, $('#mySubmit')[0].value, 'Submit value was updated');
	    	});
	        start();
	    }, 1000);
	});
}(jQuery));