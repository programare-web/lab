(function allTests($) {
    /** location of the AJAX datasource */
    var AJAX_SCRIPT_URL = 'ajaxSource.php';


    // hacking all HTMLElements' prototype to enable event checking
    // Kids, don't try this at home! Or better yet, anywhere!
    if (typeof HTMLElement === 'function' || typeof HTMLElement === 'object') {
        HTMLElement.prototype.registeredEvents = [];
        HTMLElement.prototype.decoratedAddEventListener = HTMLElement.prototype.addEventListener;
        HTMLElement.prototype.addEventListener = function (eventType, action, capturingPhase) {
            this.registeredEvents.push({ type : eventType, action : action, phase : capturingPhase});
            this.decoratedAddEventListener(eventType, action, capturingPhase);
        }
    } else {
        throw new Error('Browser does not implement the HTMLElement interface');
    }

    var eventExists = function (el, eventType) {
        var i;
        if (!el instanceof HTMLElement) {
            throw new Error("Event check on non HTML element");
        } 

        // checking for events added via addEventListener
        for (i = el.registeredEvents.length - 1; i >= 0; i--) {
            if (el.registeredEvents[i].type === eventType) {
                return true;
            }
        }
        
        // if we got here, need to check if the event was added via the attribute
        return !!el['on' + eventType];
    };

    var getConfigObject = function (type) {
        switch (type) {
        case 'input':
            return {
                name : randomString(),
                id : randomString(),
                type : 'text',
                value : randomString(5)
            };
        break;
        case 'submit':
            return {
                name : randomString(),
                id : randomString(),
                type : 'submit',
                value : randomString(5)
            }
        break;
        case 'select':
            return {
                name : randomString(),
                id : randomString(),
                type : 'select',
                value : [randomString(3), randomString(6)],
                description : randomString(7)
            }
        break;
        case 'form':
            return {
                name : randomString(),
                id : randomString(),
                method : 'GET',
                action : randomString() + '.php',
                elements : [getConfigObject('input'), getConfigObject('dropdown'), getConfigObject('input'), getConfigObject('submit')]
            }
        break;
        case 'factory':
            return {
                styles : {
                    form : randomString(),
                    input : {
                        text : randomString(),
                        submit : randomString()
                    },
                    select : randomString(),
                },
                container : {
                    type : randomValue(['div', 'p']),
                    style : ['container', randomString()]
                };
            }
        default:
        break;
        }
    };

    var randomString = function (length) {
        var alphabet = 'abcdefghijklmnopqrstuvwxyz', i, result = '';
        for (i = 0; i < (length || 4); i++) {
            result += randomValue(alphabet);
        }
    };

    var randomValue = function (values) {
        return values[Math.floor(Math.random() * values.length)];
    };

    var checkCommon = function (el, config, attributes) {
        var attrs = attributes || ['id', 'value', 'name'];
        ok(el.nodeType === 1, 'The HTML element has a correct node type');
        $.each(attr, function (idx, attr) {
            if (typeof config[attr] === 'string') {
                // assert that the configuration attribute is mirrored in the element
                ok(el.getAttribute(attr) === config[attr], 'Attribute ' + attr + ' was correctly attached.');
            }
        }
    };

    var checkStyles = function (el, styles) {
        if (typeof styles === 'string') {
            ok($(el).hasClass(style), 'CSS class ' + style + 'was correctly attached to the element');
        } else {
            // assumed array
            $.each(styles, function (idx, style) {
                checkStyles(el, style);
            }
        }
    };

    module('Preliminary Checks');
	test('Check if required functions are present', function () {
		equals(typeof(createFormFactory), 'function', 'createFormFactory function present');
		var formFactory = createFormFactory(getConfigObject('factory'));
		equals(typeof(formFactory.createForm), 'function', 'createForm function present in factory object');
		equals(typeof(formFactory.createInput), 'function', 'createInput function present in factory object');
		equals(typeof(formFactory.createCombo), 'function', 'createCombo function present in factory object');
		equals(typeof(formFactory.createSubmit), 'function', 'createCombo function present in factory object');
	});
	
	
	module('Functional checks');
	test('Check for different types of form elements', function () {
		var formFactory = createFormFactory(getConfigObject('factory')), input, dropdown, submit, form;
		
		// test createInput
		input = formFactory.createInput(getConfigObject('input'));
		ok(input instanceof HTMLElement, 'createInput returns a DOM element');
		equals(input.nodeType, 1, 'Input has correct nodeType');
		
		// test createCombo
	    dropdown = formFactory.createSelect(getConfigObject('select'));
		ok(combo instanceof HTMLElement, 'createCombo returns a DOM element');
		
		// test createSubmit
		submit = formFactory.createSubmit(getConfigObject('submit'));
		ok(submit instanceof HTMLElement, 'createSubmit returns a DOM element');
		equals(submit.nodeType, 1, 'Submit has correct nodeType');
		
		form = formFactory.createForm(getConfigObject('form'));
		ok(form instanceof HTMLElement, 'createForm returns a DOM element');
		equals(form.nodeType, 1, 'Form has correct nodeType');
	});


	test('Input Element Checks', function () {
		var formFactory = createFormFactory(getConfigObject('factory')), config = getConfigObject('input'), input;
		expect(5);
		input = formFactory.createInput(config);
        checkCommon(input, config);
		equals(input.type, 'text', 'Type attribute created successfully');		
		// TODO: test if exception is thrown when required fields are not present?
	});
	
	test('Dropdown Element Checks', function () {
		var formFactory = createFormFactory(getConfigObject('factory')), config = getConfigObject('select'), select, i;
		select = formFactory.createCombo(config);
        checkCommon(select, config);
		equals(select.childNodes.length, config.value.length, 'Correct number of values');
		for (i = select.childNodes.length - 1; i >= 0; i--) {
			equals(select.childNodes[i].tagName.toLowerCase(), 'option', 'Option element was created');
			equals(select.childNodes[i].innerHTML, config.value[i], 'Value was correctly inserted');
		}		
	});
	
	test('Submit element checks', function () {
		var formFactory = createFormFactory(getConfigObject('factory')), config = getConfigObject('submit'), submit;
		submit = formFactory.createSubmit(config);
        checkCommon(submit, config);
		equals(submit.type, 'submit', 'Type attribute created successfully');		
		
	});
	
	test('Test complete form', function () {
		var i;
		var factoryConfig = getConfigObject('factory'), 
            factory = createFormFactory(factoryConfig), 
            formConfig = getConfigObject('form'),
            form = factory.createForm(formConfig);
	
        checkCommon(form, formConfig, ['action', 'method']);
		equals(form.className, factoryConfig.styles.form);
		equals(form.childNodes.length, 3, 'Form has correct number of child nodes');
		for (i = 0; i < form.childNodes.length; i++) {
			equals(form.childNodes[i].className, factoryConfig.container.style.join(' ')); // TODO: make this work in all cases
			equals(form.childNodes[i].tagName.toLowerCase(), factoryConfig.container.type.toLowerCase());
		}
		checkStyles($(form).find('input[type=text]').get(0), factoryConfig.styles.input);
		checkStyles($(form).find('select').get(0), factoryConfig.styles.select);
		checkStyles($(form).find('input[type=submit]').get(0), factoryConfig.styles.submit);
		equals($(form).find('div > label').length, 3, 'Labels were inserted correctly for every element.');
		equals($(form).find('div > label').get(0).innerHTML, 'Input label', 'Input label was inserted correctly');
		equals($(form).find('div > label').get(1).innerHTML, 'Combo label', 'Combo label was inserted correctly');
		equals($(form).find('div > label').get(2).innerHTML, 'Submit label', 'Submit label was inserted correctly');
		equals(form.name, form_info.name, 'Form has correct name');
	});
	
	asyncTest('AJAX Tests', function () {
		var formFactory = createFormFactory(getConfigObject('factory'));
		// ajax metadata
		var ajax_info = { url: AJAX_SCRIPT_URL, event: 'change', target: 'myCombo' };
		var ajax_info_2 = { url: AJAX_SCRIPT_URL, event: 'click',	target: 'mySubmit' };
		// form elements
		var test_ajax = { name: 'inputValue', id: 'inputValue', type: 'text', ajax: ajax_info, description: 'Description text:' };
		var test_ajax_2 = {	name: 'inputValue2', id: 'inputValue2', type: 'text', ajax: ajax_info_2, description: 'Description text:' };
		var test_combo = { name:'myCombo', id: 'myCombo', type: 'combo', description: 'Description text:' };
		var test_submit = { name: 'mySubmit', id: 'mySubmit', type: 'submit' };
		// form input
		var form_info = { name: 'formName', method: 'GET', action: 'script.php', elements: [ test_ajax, test_combo, test_ajax_2, test_submit ] };
		var form = formFactory.createForm(form_info);
		$("#test-container")[0].appendChild(form);
	
        ok(eventExists($('#inputValue').get(0), ajax_info.event), 'Event handler was attached correctly');
        ok(eventExists($('#inputValue2').get(0), ajax_info_2.event), 'Event handler was attached correctly');
		
		// change value and trigger event twice
		$("#inputValue").attr('value', 'test').trigger('change').trigger('change');
		$("#inputValue2").attr('value', 'cv_1').trigger('click');
		
		// wait 1s for the ajax calls to complete
	    setTimeout(function() {
	    	equals($('#myCombo option').length, 3);
			
	    	// test population of the dropdown
	    	$.get(AJAX_SCRIPT_URL, function (data) {
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
