(function ($cms, $util, $dom) {
    'use strict';

    var IN_MINIKERNEL_VERSION = document.documentElement.classList.contains('in-minikernel-version');

    /**
     * Addons will add "behaviors" under this namespace
     * @namespace $cms.behaviors
     */
    $cms.behaviors = {};

    // Implementation for [data-view]
    $cms.behaviors.initializeViews = {
        attach: function (context) {
            $util.once($dom.$$$(context, '[data-view]'), 'behavior.initializeViews').forEach(function (el) {
                var params = objVal($dom.data(el, 'viewParams')),
                    viewName = el.dataset.view,
                    viewOptions = {el: el};

                if (typeof $cms.views[viewName] !== 'function') {
                    $util.fatal('$cms.behaviors.initializeViews.attach(): Missing view constructor "' + viewName + '" for', el);
                    return;
                }

                try {
                    $dom.data(el).viewObject = new $cms.views[viewName](params, viewOptions);
                    //$util.inform('$cms.behaviors.initializeViews.attach(): Initialized view "' + el.dataset.view + '" for', el, view);
                } catch (ex) {
                    $util.fatal('$cms.behaviors.initializeViews.attach(): Exception thrown while initializing view "' + el.dataset.view + '" for', el, ex);
                }
            });
        }
    };

    // Implementation for [data-tpl]
    $cms.behaviors.initializeTemplates = {
        attach: function (context) {
            $util.once($dom.$$$(context, '[data-tpl]'), 'behavior.initializeTemplates').forEach(function (el) {
                var template = el.dataset.tpl,
                    params = objVal($dom.data(el, 'tplParams'));

                if (typeof $cms.templates[template] !== 'function') {
                    $util.fatal('$cms.behaviors.initializeTemplates.attach(): Missing template function "' + template + '" for', el);
                    return;
                }

                try {
                    $cms.templates[template].call(el, params, el);
                    //$util.inform('$cms.behaviors.initializeTemplates.attach(): Initialized template "' + template + '" for', el);
                } catch (ex) {
                    $util.fatal('$cms.behaviors.initializeTemplates.attach(): Exception thrown while calling the template function "' + template + '" for', el, ex);
                }
            });
        }
    };

    $cms.behaviors.initializeAnchors = {
        attach: function (context) {
            var anchors = $util.once($dom.$$$(context, 'a'), 'behavior.initializeAnchors'),
                hasBaseEl = Boolean(document.querySelector('base'));

            anchors.forEach(function (anchor) {
                var href = strVal(anchor.href);
                // So we can change base tag especially when on debug mode
                if (hasBaseEl && href.startsWith('#') && (href !== '#!')) {
                    anchor.href = window.location.href.replace(/#.*$/, '') + href;
                }

                if ($cms.configOption('js_overlays')) {
                    // Lightboxes
                    if (anchor.rel && anchor.rel.includes('lightbox')) {
                        anchor.title = anchor.title.replace('{!LINK_NEW_WINDOW;^}', '').trim();
                    }

                    // Convert <a> title attributes into composr tooltips
                    if (!anchor.classList.contains('no-tooltip')) {
                        convertTooltip(anchor);
                    }
                }

                if (boolVal('{$VALUE_OPTION;,js_keep_params}')) {
                    // Keep parameters need propagating
                    if (anchor.href && anchor.href.startsWith($cms.getBaseUrl() + '/')) {
                        anchor.href = $cms.addKeepStub(anchor.href);
                    }
                }
            });
        }
    };

    $cms.behaviors.initializeForms = {
        attach: function (context) {
            var forms = $util.once($dom.$$$(context, 'form'), 'behavior.initializeForms');

            forms.forEach(function (form) {
                // HTML editor
                if (window.$editing !== undefined) {
                    window.$editing.loadHtmlEdit(form);
                }

                // Remove tooltips from forms as they are for screen-reader accessibility only
                form.title = '';

                // Convert form element title attributes into composr tooltips
                if ($cms.configOption('js_overlays')) {
                    // Convert title attributes into composr tooltips
                    var elements = arrVal(form.elements), j;

                    elements = elements.concat(form.querySelectorAll('input[type="image"]')); // JS DOM does not include input[type="image"] ones in form.elements

                    for (j = 0; j < elements.length; j++) {
                        if ((elements[j].title !== undefined) && !elements[j].classList.contains('no-tooltip')) {
                            convertTooltip(elements[j]);
                        }
                    }
                }

                if (boolVal('{$VALUE_OPTION;,js_keep_params}')) {
                    /* Keep parameters need propagating */
                    if (form.action && form.action.startsWith($cms.getBaseUrl() + '/')) {
                        form.action = $cms.addKeepStub(form.action);
                    }
                }

                // This "proves" that JS is running, which is an anti-spam heuristic (bots rarely have working JS)
                if ((form.elements['csrf_token'] != null) && (form.elements['js_token'] == null)) {
                    var jsToken = document.createElement('input');
                    jsToken.type = 'hidden';
                    jsToken.name = 'js_token';
                    jsToken.value = form.elements['csrf_token'].value.split('').reverse().join(''); // Reverse the CSRF token for our JS token
                    form.appendChild(jsToken);
                }
            });
        }
    };

    $cms.behaviors.initializeTables = {
        attach: function attach(context) {
            var tables = $util.once($dom.$$$(context, 'table'), 'behavior.initializeTables');

            tables.forEach(function (table) {
                // Responsive table prep work
                if (table.classList.contains('responsive-table')) {
                    var trs = table.getElementsByTagName('tr'),
                        thsFirstRow = trs[0].cells,
                        i, tds, j, data;

                    for (i = 0; i < trs.length; i++) {
                        tds = trs[i].cells;
                        for (j = 0; j < tds.length; j++) {
                            if (!tds[j].classList.contains('responsive-table-no-prefix')) {
                                data = (thsFirstRow[j] == null) ? '' : thsFirstRow[j].textContent.replace(/^\s+/, '').replace(/\s+$/, '');
                                if (data !== '') {
                                    tds[j].setAttribute('data-th', data);
                                }
                            }
                        }
                    }
                }
            });
        }
    };

    // Implementation for [data-click-pd]
    // Prevent-default for JS-activated elements (which may have noscript fallbacks as default actions)
    $cms.behaviors.onclickPreventDefault = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-click-pd]'), 'behavior.onclickPreventDefault');
            els.forEach(function (el) {
                $dom.on(el, 'click', function (e) {
                    e.preventDefault();
                });
            });
        }
    };

    // Implementation for [data-submit-pd]
    // Prevent-default for JS-activated elements (which may have noscript fallbacks as default actions)
    $cms.behaviors.onsubmitPreventDefault = {
        attach: function (context) {
            var forms = $util.once($dom.$$$(context, '[data-submit-pd]'), 'behavior.onsubmitPreventDefault');
            forms.forEach(function (form) {
                $dom.on(form, 'submit', function (e) {
                    e.preventDefault();
                });
            });
        }
    };

    // Implementation for input[data-cms-unchecked-is-indeterminate]
    $cms.behaviors.uncheckedIsIndeterminate = {
        attach: function (context) {
            var inputs = $util.once($dom.$$$(context, 'input[data-cms-unchecked-is-indeterminate]'), 'behavior.uncheckedIsIndeterminate');

            inputs.forEach(function (input) {
                if (input.type === 'checkbox') {
                    if (!input.checked) {
                        input.indeterminate = true;
                    }

                    $dom.on(input, 'change', function uncheckedIsIndeterminate() {
                        if (!input.checked) {
                            input.indeterminate = true;
                        }
                    });
                }
            });
        }
    };

    // Implementation for [data-click-eval="<code to eval>"]
    $cms.behaviors.clickEval = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-click-eval]'), 'behavior.clickEval');

            els.forEach(function (el) {
                $dom.on(el, 'click', function clickEval() {
                    var code = strVal(el.dataset.clickEval);

                    if (code !== '') {
                        (function () {
                            eval(code); // eval() call
                        }).call(el); // Set `this` context for eval
                    }
                });
            });
        }
    };

    // Implementation for [data-click-alert]
    $cms.behaviors.onclickShowModalAlert = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-click-alert]'), 'behavior.onclickShowModalAlert');

            els.forEach(function (el) {
                $dom.on(el, 'click', function onclickShowModalAlert() {
                    var options = objVal($dom.data(el, 'clickAlert'), {}, 'notice');
                    $cms.ui.alert(options.notice);
                });
            });
        }
    };

    // Implementation for [data-keypress-alert]
    $cms.behaviors.onkeypressShowModalAlert = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-keypress-alert]'), 'behavior.onkeypressShowModalAlert');

            els.forEach(function (el) {
                $dom.on(el, 'keypress', function onkeypressShowModalAlert() {
                    var options = objVal($dom.data(el, 'keypressAlert'), {}, 'notice');
                    $cms.ui.alert(options.notice);
                });
            });
        }
    };

    // Implementation for [data-submit-on-enter]
    $cms.behaviors.submitOnEnter = {
        attach: function (context) {
            var inputs = $util.once($dom.$$$(context, '[data-submit-on-enter]'), 'behavior.submitOnEnter');

            inputs.forEach(function (input) {
                $dom.on(input, 'keypress', function submitOnEnter(e) {
                    if ($dom.keyPressed(e, 'Enter')) {
                        $dom.submit(input.form);
                        e.preventDefault();
                    }
                });
            });
        }
    };

    // Implementation for [data-mouseover-class="{ 'some-class' : 1|0 }"]
    // Toggle classes based on mouse location
    $cms.behaviors.mouseoverClass = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-mouseover-class]'), 'behavior.mouseoverClass');

            els.forEach(function (el) {
                $dom.on(el, 'mouseover', function mouseoverClass(e) {
                    var classes = objVal($dom.data(el, 'mouseoverClass')), key, bool;

                    if (!e.relatedTarget || !el.contains(e.relatedTarget)) {
                        for (key in classes) {
                            bool = Boolean(classes[key]) && (classes[key] !== '0');
                            el.classList.toggle(key, bool);
                        }
                    }
                });
            });
        }
    };

    // Implementation for [data-mouseout-class="{ 'some-class' : 1|0 }"]
    // Toggle classes based on mouse location
    $cms.behaviors.mouseoutClass = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-mouseout-class]'), 'behavior.mouseoutClass');

            els.forEach(function (el) {
                $dom.on(el, 'mouseout', function mouseoutClass(e) {
                    var classes = objVal($dom.data(el, 'mouseoutClass')), key, bool;

                    if (!e.relatedTarget || !el.contains(e.relatedTarget)) {
                        for (key in classes) {
                            bool = Boolean(classes[key]) && (classes[key] !== '0');
                            el.classList.toggle(key, bool);
                        }
                    }
                });
            });
        }
    };

    // Implementation for [data-cms-confirm-click="<Message>"]
    // Show a confirmation dialog for clicks on a link (is higher up for priority)
    var _confirmedClick;
    $cms.behaviors.confirmClick = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-cms-confirm-click]'), 'behavior.confirmClick');

            els.forEach(function (el) {
                var uid = $util.uid(el),
                    message = strVal(el.dataset.cmsConfirmClick);

                $dom.on(el, 'click', function (e) {
                    if (_confirmedClick === uid) {
                        // Confirmed, let it through
                        return;
                    }
                    e.preventDefault();
                    $cms.ui.confirm(message, function (result) {
                        if (result) {
                            _confirmedClick = uid;
                            $dom.trigger(el, 'click');
                        }
                    });
                });
            });
        }
    };

    // Implementation for form[data-submit-modsecurity-workaround]
    // mod_security workaround
    $cms.behaviors.submitModSecurityWorkaround = {
        attach: function (context) {
            var forms = $util.once($dom.$$$(context, 'form[data-submit-modsecurity-workaround]'), 'behavior.submitModSecurityWorkaround');

            forms.forEach(function (form) {
                $dom.on(form, 'submit', function (e) {
                    if ($cms.form.isModSecurityWorkaroundEnabled()) {
                        e.preventDefault();
                        $cms.form.modSecurityWorkaround(form);
                    }
                });
            });
        }
    };

    // Implementation for form[data-disable-buttons-on-submit]
    // Disable form buttons on submit
    $cms.behaviors.disableButtonsOnFormSubmit = {
        attach: function (context) {
            var forms = $util.once($dom.$$$(context, 'form[data-disable-buttons-on-submit]'), 'behavior.disableButtonsOnFormSubmit');

            forms.forEach(function (form) {
                $dom.on(form, 'submit', function () {
                    $cms.ui.disableFormButtons(form);
                });
            });
        }
    };

    $cms.behaviors.columnHeightBalancing = {
        attach: function attach(context) {
            var cols = $util.once($dom.$$$(context, '.col_balance_height'), 'behavior.columnHeightBalancing'),
                i, max, j, height;

            for (i = 0; i < cols.length; i++) {
                max = null;
                for (j = 0; j < cols.length; j++) {
                    if (cols[i].className === cols[j].className) {
                        height = cols[j].offsetHeight;
                        if ((max === null) || (height > max)) {
                            max = height;
                        }
                    }
                    cols[i].style.height = max + 'px';
                }
            }
        }
    };

    // Convert img title attributes into Composr tooltips
    $cms.behaviors.imageTooltips = {
        attach: function (context) {
            if (!$cms.configOption('js_overlays')) {
                return;
            }

            $util.once($dom.$$$(context, 'img:not([data-cms-rich-tooltip])'), 'behavior.imageTooltips').forEach(function (img) {
                convertTooltip(img);
            });
        }
    };

    // Implementation for [data-remove-if-js-enabled]
    $cms.behaviors.removeIfJsEnabled = {
        attach: function (context) {
            var els = $dom.$$$(context, '[data-remove-if-js-enabled]');

            els.forEach(function (el) {
                $dom.remove(el);
            });
        }
    };

    // Implementation for [data-js-function-calls]
    $cms.behaviors.jsFunctionCalls = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-js-function-calls]'), 'behavior.jsFunctionCalls');

            els.forEach(function (el) {
                var jsFunctionCalls = $dom.data(el, 'jsFunctionCalls');

                if (typeof jsFunctionCalls === 'string') {
                    jsFunctionCalls = [jsFunctionCalls];
                }

                if (jsFunctionCalls != null) {
                    $cms.executeJsFunctionCalls(jsFunctionCalls);
                }
            });
        }
    };

    // Implementation for [data-cms-select2]
    $cms.behaviors.select2Plugin = {
        attach: function (context) {
            if (IN_MINIKERNEL_VERSION) {
                return;
            }

            $cms.requireJavascript(['jquery', 'select2']).then(function () {
                var els = $util.once($dom.$$$(context, '[data-cms-select2]'), 'behavior.select2Plugin');

                // Select2 plugin hook
                els.forEach(function (el) {
                    var options = objVal($dom.data(el, 'cmsSelect2'));
                    if (window.jQuery && window.jQuery.fn.select2) {
                        window.jQuery(el).select2(options);
                    }
                });
            });
        }
    };

    // Implementation for img[data-gd-text]
    $cms.behaviors.gdTextImages = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, 'img[data-gd-text]'), 'behavior.gdTextImages');

            els.forEach(function (img) {
                gdImageTransform(img);
            });

            function gdImageTransform(el) {
                /* GD text maybe can do with transforms */
                var span = document.createElement('span');
                if (typeof span.style.transform === 'string') {
                    el.style.display = 'none';
                    $dom.css(span, {
                        transform: 'rotate(90deg)',
                        transformOrigin: 'bottom left',
                        top: '-1em',
                        left: '0.5em',
                        position: 'relative',
                        display: 'inline-block',
                        whiteSpace: 'nowrap',
                        paddingRight: '0.5em'
                    });

                    el.parentNode.style.textAlign = 'left';
                    el.parentNode.style.width = '1em';
                    el.parentNode.style.overflow = 'hidden'; // LEGACY Needed due to https://bugzilla.mozilla.org/show_bug.cgi?id=456497
                    el.parentNode.style.verticalAlign = 'top';
                    span.textContent = el.alt;

                    el.parentNode.insertBefore(span, el);
                    var spanProxy = span.cloneNode(true); // So we can measure width even with hidden tabs
                    spanProxy.style.position = 'absolute';
                    spanProxy.style.visibility = 'hidden';
                    document.body.appendChild(spanProxy);

                    setTimeout(function () {
                        var width = spanProxy.offsetWidth + 15;
                        spanProxy.parentNode.removeChild(spanProxy);
                        if (el.parentNode.nodeName === 'TH' || el.parentNode.nodeName === 'TD') {
                            el.parentNode.style.height = width + 'px';
                        } else {
                            el.parentNode.style.minHeight = width + 'px';
                        }
                    }, 0);
                }
            }
        }
    };

    // Implementation for [data-toggleable-tray]
    $cms.behaviors.toggleableTray = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-toggleable-tray]'), 'behavior.toggleableTray');

            els.forEach(function (el) {
                var options = $dom.data(el, 'toggleableTray') || {};

                /**
                 * @type { $cms.views.ToggleableTray }
                 */
                $dom.data(el).toggleableTrayObject = new $cms.views.ToggleableTray(options, {el: el});
            });
        }
    };

    // Implementation for [data-click-tray-toggle="<SELECTOR FOR TRAY ELEMENT>"]
    // Toggle a tray on click on an element
    $cms.behaviors.clickToggleTray = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-click-tray-toggle]'), 'behavior.clickToggleTray');

            els.forEach(function (el) {
                $dom.on(el, 'click', function () {
                    var trayId = strVal(el.dataset.clickTrayToggle),
                        trayEl = $dom.$(trayId);

                    if (!trayEl) {
                        return;
                    }

                    var ttObj = $dom.data(trayEl).toggleableTrayObject;
                    if (ttObj) {
                        ttObj.toggleTray();
                    }
                });
            });
        }
    };

    // Implementation for [data-textarea-auto-height]
    $cms.behaviors.textareaAutoHeight = {
        attach: function (context) {
            if ($cms.isMobile()) {
                return;
            }

            var textareas = $util.once($dom.$$$(context, '[data-textarea-auto-height]'), 'behavior.textareaAutoHeight');
            textareas.forEach(function (textarea) {
                $cms.manageScrollHeight(textarea);

                $dom.on(textarea, 'click input change keyup keydown', function manageScrollHeight() {
                    $cms.manageScrollHeight(textarea);
                });
            });
        }
    };

    var _invalidPatternCache = {};
    // Implementation for [data-prevent-input="<REGEX FOR DISALLOWED CHARACTERS>"]
    // Prevents input of matching characters
    $cms.behaviors.preventInput = {
        attach: function (context) {
            var inputs = $util.once($dom.$$$(context, 'data-prevent-input'), 'behavior.preventInput');

            inputs.forEach(function (input) {
                var pattern = input.dataset.preventInput, regex;

                regex = _invalidPatternCache[pattern] || (_invalidPatternCache[pattern] = new RegExp(pattern, 'g'));

                $dom.on(input, 'input keydown keypress', function (e) {
                    if (e.type === 'input') {
                        if (input.value.length === 0) {
                            input.value = ''; // value.length is also 0 if invalid value is entered for input[type=number] et al., clear that
                        } else if (input.value.search(regex) !== -1) {
                            input.value = input.value.replace(regex, '');
                        }
                    } else if ($dom.keyOutput(e, regex)) { // keydown/keypress event
                        // pattern matched, prevent input
                        e.preventDefault();
                    }
                });
            });
        }
    };

    // Implementation for [data-change-submit-form]
    // Submit form when the change event is fired on an input element
    $cms.behaviors.changeSubmitForm = {
        attach: function (context) {
            var inputs = $util.once($dom.$$$(context, '[data-change-submit-form]'), 'behavior.changeSubmitForm');

            inputs.forEach(function (input) {
                $dom.on(input, 'change', function () {
                    if (input.form != null) {
                        $dom.submit(input.form);
                    }
                });
            });
        }
    };

    // Implementation for [data-cms-btn-go-back]
    // Go back in browser history
    $cms.behaviors.btnGoBack = {
        attach: function (context) {
            var btns = $util.once($dom.$$$(context, '[data-cms-btn-go-back]'), 'behavior.btnGoBack');

            btns.forEach(function (btn) {
                $dom.on(btn, 'click', function () {
                    window.history.back();
                });
            });
        }
    };

    // Implementation for [data-click-ga-track]
    $cms.behaviors.clickGaTrack = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-click-ga-track]'), 'behavior.clickGaTrack');

            els.forEach(function (el) {
                $dom.on(el, 'click', function (e) {
                    var options = objVal($dom.data(el, 'clickGaTrack'));

                    e.preventDefault();
                    $cms.gaTrack(el, options.category, options.action);
                });
            });
        }
    };

    // Implementation for [data-click-ui-open]
    $cms.behaviors.onclickUiOpen = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-click-ui-open]'), 'behavior.onclickUiOpen');
            els.forEach(function (el) {
                $dom.on(el, 'click', function () {
                    var args = arrVal($dom.data(el, 'clickUiOpen'));
                    $cms.ui.open($util.rel($cms.maintainThemeInLink(args[0])), args[1], args[2], args[3], args[4]);
                });
            });
        }
    };

    // Implementation for [data-click-do-input]
    $cms.behaviors.onclickDoInput = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-click-do-input]'), 'behavior.onclickDoInput');

            els.forEach(function (el) {
                $dom.on(el, 'click', function () {
                    var args = arrVal($dom.data(el, 'clickDoInput')),
                        type = strVal(args[0]),
                        fieldName = strVal(args[1]),
                        tag = strVal(args[2]),
                        fnName = 'doInput' + $util.ucFirst($util.camelCase(type));

                    if (typeof window[fnName] === 'function') {
                        window[fnName](fieldName, tag);
                    } else {
                        $util.fatal('$cms.behaviors.onclickDoInput.attach(): Function not found "window.' + fnName + '()"');
                    }
                });
            });
        }
    };

    // Implementation for [data-click-toggle-checked="<SELECTOR FOR TARGET CHECKBOX(ES)>"]
    $cms.behaviors.onclickToggleCheckboxes = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-click-toggle-checked]'), 'behavior.onclickToggleCheckboxes');

            els.forEach(function (el) {
                $dom.on(el, 'click', function () {
                    var selector = strVal(el.dataset.clickToggleChecked),
                        checkboxes = $dom.$$(selector);

                    checkboxes.forEach(function (checkbox) {
                        $dom.toggleChecked(checkbox);
                    });
                });
            });
        }
    };

    // Implementation for [data-cms-rich-tooltip]
    // "Rich semantic tooltips"
    $cms.behaviors.cmsRichTooltip = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-cms-rich-tooltip]'), 'behavior.cmsRichTooltip');

            els.forEach(function (el) {
                var options = objVal($dom.data(el, 'cmsRichTooltip'));

                $dom.on(el, 'click mouseover keypress', function (e) {
                    if (el.ttitle === undefined) {
                        el.ttitle = (el.attributes['data-title'] ? el.getAttribute('data-title') : el.title);
                        el.title = '';
                    }

                    if ((e.type === 'mouseover') && options.haveLinks) {
                        return;
                    }

                    if (options.haveLinks && el.tooltipId && $dom.$id(el.tooltipId) && $dom.isDisplayed($dom.$id(el.tooltipId))) {
                        $cms.ui.deactivateTooltip(el);
                        return;
                    }

                    try {
                        //arguments: el, event, tooltip, width, pic, height, bottom, noDelay, lightsOff, forceWidth, win, haveLinks
                        $cms.ui.activateTooltip(el, e, el.ttitle, 'auto', null, null, false, true, false, false, window, true);
                    } catch (ex) {
                        $util.fatal('$cms.behaviors.cmsRichTooltip.attach(): Exception thrown by $cms.ui.activateTooltip()', ex, 'called with args:', args);
                    }
                });
            });
        }
    };

    // Implementation for [data-disable-on-click]
    // Disable button after click
    $cms.behaviors.disableOnClick = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-disable-on-click]'), 'behavior.disableOnClick');

            els.forEach(function (el) {
                $dom.on(el, 'click', function () {
                    $cms.ui.disableButton(el);
                });
            });
        }
    };

    // Implementation for [data-mouseover-activate-tooltip]
    $cms.behaviors.onmouseoverActivateTooltip = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-mouseover-activate-tooltip]'), 'behavior.onmouseoverActivateTooltip');

            els.forEach(function (el) {
                $dom.on(el, 'mouseover', function (e) {
                    if (!Array.isArray($dom.data(el, 'mouseoverActivateTooltip'))) {
                        return;
                    }

                    var args = arrVal($dom.data(el, 'mouseoverActivateTooltip'));

                    args.unshift(el, e);

                    try {
                        //arguments: el, event, tooltip, width, pic, height, bottom, no_delay, lights_off, force_width, win, haveLinks
                        $cms.ui.activateTooltip.apply(undefined, args);
                    } catch (ex) {
                        $util.fatal('$cms.behaviors.onmouseoverActivateTooltip.attach(): Exception thrown by $cms.ui.activateTooltip()', ex, 'called with args:', args);
                    }
                });
            });
        }
    };

    // Implementation for [data-focus-activate-tooltip]
    $cms.behaviors.onfocusActivateTooltip = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-focus-activate-tooltip]'), 'behavior.onfocusActivateTooltip');

            els.forEach(function (el) {
                $dom.on(el, 'focus', function (e) {
                    if (!Array.isArray($dom.data(el, 'focusActivateTooltip'))) {
                        return;
                    }

                    var args = arrVal($dom.data(el, 'focusActivateTooltip'));

                    args.unshift(el, e);

                    try {
                        //arguments: el, event, tooltip, width, pic, height, bottom, no_delay, lights_off, force_width, win, haveLinks
                        $cms.ui.activateTooltip.apply(undefined, args);
                    } catch (ex) {
                        $util.fatal('$cms.behaviors.onfocusActivateTooltip.attach(): Exception thrown by $cms.ui.activateTooltip()', ex, 'called with args:', args);
                    }
                });
            });
        }
    };

    // Implementation for [data-blur-deactivate-tooltip]
    $cms.behaviors.onblurDeactivateTooltip = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-blur-deactivate-tooltip]'), 'behavior.onblurDeactivateTooltip');

            els.forEach(function (el) {
                $dom.on(el, 'blur', function () {
                    $cms.ui.deactivateTooltip(el);
                });
            });
        }
    };

    // Implementation for [data-click-forward="{ child: '.some-selector' }"]
    $cms.behaviors.onclickForwardTo = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-click-forward]'), 'behavior.onclickForwardTo');

            els.forEach(function (el) {
                $dom.on(el, 'click', function (e) {
                    var options = objVal($dom.data(el, 'clickForward'), {}, 'child'),
                        child = strVal(options.child), // Selector for target child element
                        except = strVal(options.except), // Optional selector for excluded elements to let pass-through
                        childEl = $dom.$(el, child);

                    if (!childEl) {
                        // Nothing to do
                        return;
                    }

                    if (!childEl.contains(e.target) && (!except || !$dom.closest(e.target, except, el.parentElement))) {
                        // ^ Make sure the child isn't the current event's target already, and check for excluded elements to let pass-through
                        e.preventDefault();
                        $dom.trigger(childEl, 'click');
                    }
                });
            });
        }
    };

    // Implementation for [data-open-as-overlay]
    // Open page in overlay
    $cms.behaviors.onclickOpenOverlay = {
        attach: function (context) {
            if (!$cms.configOption('js_overlays')) {
                return;
            }

            var els = $util.once($dom.$$$(context, '[data-open-as-overlay]'), 'behavior.onclickOpenOverlay');

            els.forEach(function (el) {
                $dom.on(el, 'click', function (e) {
                    var options, url = (el.href === undefined) ? el.action : el.href;

                    if ($util.url(url).hostname !== window.location.hostname) {
                        return; // Cannot overlay, different domain
                    }

                    e.preventDefault();

                    options = objVal($dom.data(el, 'openAsOverlay'));
                    options.el = el;

                    openLinkAsOverlay(options);
                });
            });
        }
    };

    // Implementation for `click a[rel*="lightbox"]`
    // Open link in a lightbox
    $cms.behaviors.onclickOpenLightbox = {
        attach: function (context) {
            if (!($cms.configOption('js_overlays'))) {
                return;
            }

            var els = $util.once($dom.$$$(context, 'a[rel*="lightbox"]'), 'behavior.onclickOpenLightbox');

            els.forEach(function (el) {
                $dom.on(el, 'click', function (e) {
                    e.preventDefault();

                    if (el.querySelector('img, video')) {
                        openImageIntoLightbox(el);
                    } else {
                        openLinkAsOverlay({ el: el });
                    }

                    function openImageIntoLightbox(el) {
                        var hasFullButton = (el.firstElementChild == null) || (el.href !== el.firstElementChild.src);
                        $cms.ui.openImageIntoLightbox(el.href, ((el.cmsTooltipTitle !== undefined) ? el.cmsTooltipTitle : el.title), null, null, hasFullButton);
                    }
                });
            });
        }
    };

    // Implementation for [data-cms-href="<URL>"]
    // Simulated [href] for non <a> elements
    $cms.behaviors.cmsHref = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-cms-href]'), 'behavior.cmsHref');

            els.forEach(function (el) {
                $dom.on(el, 'click', function (e) {
                    var anchorClicked = Boolean($dom.closest(e.target, 'a', el));

                    // Make sure a child <a> element wasn't clicked and default wasn't prevented
                    if (!anchorClicked && !e.defaultPrevented) {
                        $util.navigate(el);
                    }
                });
            });
        }
    };
    
    // Implementation for [data-sticky-navbar="{ hideOnScroll: false|true }"]
    // Hides navbar when scrolling downwards, shows it again when scrolled upwards
    // Adds .is-scrolled class when the navbar is scrolled along
    $cms.behaviors.stickyNavbar = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-sticky-navbar]'), 'behavior.stickyNavbar');
            
            els.forEach(function (stickyNavbar) {
                var options = $dom.data(stickyNavbar, 'stickyNavbar'),
                    lastScrollY = 0,
                    navbarHeight = $dom.height(stickyNavbar),
                    movement = 0,
                    lastDirection = 0;

                window.addEventListener('scroll', function () {
                    stickyNavbar.classList.toggle('is-scrolled', window.scrollY > 0);
                    
                    if (options.hideOnScroll) {
                        var sy = window.scrollY, margin;

                        movement += sy - lastScrollY;

                        if (sy > lastScrollY) { // Scrolled down
                            if (lastDirection !== 1) {
                                movement = 0;
                            }
                            margin = -Math.min(Math.abs(movement), navbarHeight);
                            stickyNavbar.style.marginTop = margin + 'px';

                            lastDirection = 1;
                        } else { // Scrolled up
                            if (lastDirection !== -1) {
                                movement = 0;
                            }
                            margin = Math.min(Math.abs(movement), navbarHeight) - navbarHeight;
                            stickyNavbar.style.marginTop = margin + 'px';

                            lastDirection = -1;
                        }

                        lastScrollY = sy;
                    }
                });
            });
        }
    };

    // Implementation for [data-stuck-nav]
    // Pinning to top if scroll out (LEGACY: CSS is going to have a better solution to this soon)
    $cms.behaviors.stuckNav = {
        attach: function (context) {
            var els = $util.once($dom.$$$(context, '[data-stuck-nav]'), 'behavior.stuckNav');

            els.forEach(function (stuckNav) {
                window.addEventListener('scroll', $util.throttle(function () {
                    scrollListener(stuckNav);
                }, 100));
            });

            /**
             * @param { Element } stuckNav
             */
            function scrollListener(stuckNav) {
                var stuckNavHeight = (stuckNav.realHeight == null) ? $dom.contentHeight(stuckNav) : stuckNav.realHeight;

                stuckNav.realHeight = stuckNavHeight;
                var posY = $dom.findPosY(stuckNav.parentNode, true),
                    footerHeight = document.querySelector('footer') ? document.querySelector('footer').offsetHeight : 0,
                    panelBottom = $dom.$('#panel-bottom');

                if (panelBottom) {
                    footerHeight += panelBottom.offsetHeight;
                }
                panelBottom = $dom.$('#global-messages-2');
                if (panelBottom) {
                    footerHeight += panelBottom.offsetHeight;
                }
                if (stuckNavHeight < ($dom.getWindowHeight() - footerHeight)) { // If there's space in the window to make it "float" between header/footer
                    var extraHeight = (window.pageYOffset - posY);
                    if (extraHeight > 0) {
                        var width = $dom.contentWidth(stuckNav),
                            height = $dom.contentHeight(stuckNav),
                            stuckNavWidth = $dom.contentWidth(stuckNav);

                        if (!window.getComputedStyle(stuckNav).getPropertyValue('width')) { // May be centered or something, we should be careful
                            stuckNav.parentNode.style.width = width + 'px';
                        }
                        stuckNav.parentNode.style.height = height + 'px';
                        stuckNav.style.position = 'fixed';
                        stuckNav.style.top = '0px';
                        stuckNav.style.zIndex = '1000';
                        stuckNav.style.width = stuckNavWidth + 'px';
                    } else {
                        stuckNav.parentNode.style.width = '';
                        stuckNav.parentNode.style.height = '';
                        stuckNav.style.position = '';
                        stuckNav.style.top = '';
                        stuckNav.style.width = '';
                    }
                } else {
                    stuckNav.parentNode.style.width = '';
                    stuckNav.parentNode.style.height = '';
                    stuckNav.style.position = '';
                    stuckNav.style.top = '';
                    stuckNav.style.width = '';
                }
            }
        }
    };
    
    // Implementation for [data-ride="carousel"]
    // Port of Bootstrap 4 Carousel http://getbootstrap.com/docs/4.1/components/carousel/
    // Disables itself if Bootstrap version detected
    $cms.behaviors.rideCarousel = {
        attach: function (context) {
            if (window.jQuery && window.jQuery.fn.carousel) {
                // Bootstrap Carousel already loaded!
                return;
            }

            var carousels = $util.once($dom.$$$(context, '[data-ride="carousel"]'), 'behavior.rideCarousel');

            carousels.forEach(function (carousel) {
                $dom.load.then(function () {
                    $dom.Carousel._jQueryInterface.call([carousel], $dom.data(carousel));
                });
            });
        }
    };
    
    (function () {
        var VERSION                = '4.1.1';
        var DATA_KEY               = 'bs.carousel';
        var EVENT_KEY              = '.' + DATA_KEY;
        var DATA_API_KEY           = '.data-api';
        var ARROW_LEFT_KEYCODE     = 37; // KeyboardEvent.which value for left arrow key
        var ARROW_RIGHT_KEYCODE    = 39; // KeyboardEvent.which value for right arrow key
        var TOUCHEVENT_COMPAT_WAIT = 500; // Time for mouse compat events to fire after touch

        var Default = {
            interval : 5000,
            keyboard : true,
            slide    : false,
            pause    : 'hover',
            wrap     : true
        };

        var Direction = {
            NEXT     : 'next',
            PREV     : 'prev',
            LEFT     : 'left',
            RIGHT    : 'right'
        };

        var Event = {
            SLIDE          : 'slide' + EVENT_KEY,
            SLID           : 'slid' + EVENT_KEY,
            KEYDOWN        : 'keydown' + EVENT_KEY,
            MOUSEENTER     : 'mouseenter' + EVENT_KEY,
            MOUSELEAVE     : 'mouseleave' + EVENT_KEY,
            TOUCHEND       : 'touchend' + EVENT_KEY,
            LOAD_DATA_API  : 'load' + EVENT_KEY + DATA_API_KEY,
            CLICK_DATA_API : 'click' + EVENT_KEY + DATA_API_KEY,
        };

        var ClassName = {
            CAROUSEL : 'cms-carousel',
            ACTIVE   : 'active',
            SLIDE    : 'slide',
            RIGHT    : 'cms-carousel-item-right',
            LEFT     : 'cms-carousel-item-left',
            NEXT     : 'cms-carousel-item-next',
            PREV     : 'cms-carousel-item-prev',
            ITEM     : 'cms-carousel-item'
        };

        var Selector = {
            ACTIVE      : '.active',
            ACTIVE_ITEM : '.active.cms-carousel-item',
            ITEM        : '.cms-carousel-item',
            NEXT_PREV   : '.cms-carousel-item-next, .cms-carousel-item-prev',
            INDICATORS  : '.cms-carousel-indicators',
            DATA_SLIDE  : '[data-slide], [data-slide-to]',
            DATA_RIDE   : '[data-ride="carousel"]'
        };

        $dom.Carousel = Carousel;
        /**
         * @constructor Carousel
         */
        function Carousel(element, config) {
            this._items              = null;
            this._interval           = null;
            this._intervalPassed     = 0;
            this._progressInterval   = null;
            this._activeElement      = null;

            this._isPaused           = false;
            this._isSliding          = false;

            this.touchTimeout        = null;

            this._config             = this._getConfig(config);
            this._element            = element;
            this._indicatorsElement  = this._element.querySelector(Selector.INDICATORS);

            this._addEventListeners();
        }

        Carousel.VERSION = VERSION;
        Carousel.Default = Default;

        $util.properties(Carousel.prototype, /**@lends Carousel#*/{
            // Public
            next: function next() {
                if (!this._isSliding) {
                    this._slide(Direction.NEXT);
                }
            },

            nextWhenVisible: function nextWhenVisible() {
                // Don't call next when the page isn't visible
                // or the carousel or its parent isn't visible
                if (!document.hidden && ($dom.isVisible(this._element) && $dom.css(this._element, 'visibility') !== 'hidden')) {
                    this.next();
                }
            },

            prev: function prev() {
                if (!this._isSliding) {
                    this._slide(Direction.PREV);
                }
            },

            pause: function pause(event) {
                if (!event) {
                    this._isPaused = true;
                }

                if (this._element.querySelector(Selector.NEXT_PREV)) {
                    $dom.trigger(this._element, 'transitionend');
                    this.cycle(true);
                }

                this._intervalPassed = 0;
                this._element.style.removeProperty('--cms-carousel-progress-percentage');
                clearInterval(this._interval);
                clearInterval(this._progressInterval);
                this._interval = null;
                this._progressInterval = null;
            },

            cycle: function cycle(event) {
                if (!event) {
                    this._isPaused = false;
                }

                if (this._interval) {
                    clearInterval(this._interval);
                    this._interval = null;
                }

                if (this._progressInterval) {
                    clearInterval(this._progressInterval);
                    this._progressInterval = null;
                    this._element.style.removeProperty('--cms-carousel-progress-percentage');
                }
                
                if (this._config.interval && !this._isPaused) {
                    var self = this;
                    this._interval = setInterval(function () { self.nextWhenVisible(); }, this._config.interval);
                    this._progressInterval = setInterval(function () {
                        self._intervalPassed += 10;
                        var progressPercentage = self._intervalPassed / self._config.interval;
                        self._element.style.setProperty('--cms-carousel-progress-percentage', (progressPercentage * 100).toFixed(2) + '%');
                    }, 10);
                }
            },

            to: function to(index) {
                var self = this;

                this._activeElement = this._element.querySelector(Selector.ACTIVE_ITEM);

                var activeIndex = this._getItemIndex(this._activeElement);

                if (index > this._items.length - 1 || index < 0) {
                    return;
                }

                if (this._isSliding) {
                    $dom.one(this._element, Event.SLID, function () { self.to(index); });
                    return;
                }

                if (activeIndex === index) {
                    this.pause();
                    this.cycle();
                    return;
                }

                var direction = index > activeIndex ? Direction.NEXT : Direction.PREV;

                this._slide(direction, this._items[index]);
            },

            dispose: function dispose() {
                $dom.off(this._element, EVENT_KEY);
                $dom.removeData(this._element, DATA_KEY);

                this._items             = null;
                this._config            = null;
                this._element           = null;
                this._interval          = null;
                this._isPaused          = null;
                this._isSliding         = null;
                this._activeElement     = null;
                this._indicatorsElement = null;
            },

            // Private

            _getConfig: function _getConfig(config) {
                config = $util.extend({}, Default, config);
                return config;
            },

            _addEventListeners: function _addEventListeners() {
                var self = this;

                if (this._config.keyboard) {
                    $dom.on(this._element, Event.KEYDOWN, function (event) { self._keydown(event); });
                }

                if (this._config.pause === 'hover') {
                    $dom.on(this._element, Event.MOUSEENTER, function (event) { self.pause(event); });
                    $dom.on(this._element, Event.MOUSELEAVE, function (event) { self.cycle(event); });

                    if ('ontouchstart' in document.documentElement) {
                        // If it's a touch-enabled device, mouseenter/leave are fired as
                        // part of the mouse compatibility events on first tap - the carousel
                        // would stop cycling until user tapped out of it;
                        // here, we listen for touchend, explicitly pause the carousel
                        // (as if it's the second time we tap on it, mouseenter compat event
                        // is NOT fired) and after a timeout (to allow for mouse compatibility
                        // events to fire) we explicitly restart cycling
                        $dom.on(this._element, Event.TOUCHEND, function () {
                            self.pause();
                            if (self.touchTimeout) {
                                clearTimeout(self.touchTimeout);
                            }
                            self.touchTimeout = setTimeout(function (event) { self.cycle(event); }, TOUCHEVENT_COMPAT_WAIT + self._config.interval);
                        });
                    }
                }
            },

            _keydown: function _keydown(event) {
                if (/input|textarea/i.test(event.target.tagName)) {
                    return;
                }

                switch (event.which) {
                    case ARROW_LEFT_KEYCODE:
                        event.preventDefault();
                        this.prev();
                        break;
                    case ARROW_RIGHT_KEYCODE:
                        event.preventDefault();
                        this.next();
                        break;
                    default:
                }
            },

            _getItemIndex: function _getItemIndex(element) {
                this._items = element && element.parentNode ? [].slice.call(element.parentNode.querySelectorAll(Selector.ITEM)) : [];
                return this._items.indexOf(element);
            },

            _getItemByDirection: function _getItemByDirection(direction, activeElement) {
                var isNextDirection = direction === Direction.NEXT;
                var isPrevDirection = direction === Direction.PREV;
                var activeIndex     = this._getItemIndex(activeElement);
                var lastItemIndex   = this._items.length - 1;
                var isGoingToWrap   = isPrevDirection && activeIndex === 0 ||
                    isNextDirection && activeIndex === lastItemIndex;

                if (isGoingToWrap && !this._config.wrap) {
                    return activeElement;
                }

                var delta     = direction === Direction.PREV ? -1 : 1;
                var itemIndex = (activeIndex + delta) % this._items.length;

                return itemIndex === -1 ? this._items[this._items.length - 1] : this._items[itemIndex];
            },

            _triggerSlideEvent: function _triggerSlideEvent(relatedTarget, eventDirectionName) {
                var targetIndex = this._getItemIndex(relatedTarget);
                var fromIndex = this._getItemIndex(this._element.querySelector(Selector.ACTIVE_ITEM));
                var slideEvent = $dom.createEvent(Event.SLIDE, {
                    relatedTarget: relatedTarget,
                    direction: eventDirectionName,
                    from: fromIndex,
                    to: targetIndex
                });
                
                return $dom.trigger(this._element, slideEvent);
            },

            _setActiveIndicatorElement: function _setActiveIndicatorElement(element) {
                if (this._indicatorsElement) {
                    var indicators = [].slice.call(this._indicatorsElement.querySelectorAll(Selector.ACTIVE));
                    indicators.forEach(function (indicator) {
                        indicator.classList.remove(ClassName.ACTIVE);
                    });

                    var nextIndicator = this._indicatorsElement.children[this._getItemIndex(element)];

                    if (nextIndicator) {
                        nextIndicator.classList.add(ClassName.ACTIVE);
                    }
                }
            },

            _slide: function _slide(direction, element) {
                var activeElement = this._element.querySelector(Selector.ACTIVE_ITEM);
                var activeElementIndex = this._getItemIndex(activeElement);
                var nextElement   = element || activeElement && this._getItemByDirection(direction, activeElement);
                var nextElementIndex = this._getItemIndex(nextElement);
                var isCycling = Boolean(this._interval);

                var directionalClassName;
                var orderClassName;
                var eventDirectionName;

                if (direction === Direction.NEXT) {
                    directionalClassName = ClassName.LEFT;
                    orderClassName = ClassName.NEXT;
                    eventDirectionName = Direction.LEFT;
                } else {
                    directionalClassName = ClassName.RIGHT;
                    orderClassName = ClassName.PREV;
                    eventDirectionName = Direction.RIGHT;
                }

                if (nextElement && nextElement.classList.contains(ClassName.ACTIVE)) {
                    this._isSliding = false;
                    return;
                }

                var isDefaultPrevented = !this._triggerSlideEvent(nextElement, eventDirectionName);
                
                if (isDefaultPrevented) {
                    return;
                }

                if (!activeElement || !nextElement) {
                    // Some weirdness is happening, so we bail
                    return;
                }

                this._isSliding = true;

                if (isCycling) {
                    this.pause();
                }

                this._setActiveIndicatorElement(nextElement);

                var slidEvent = $dom.createEvent(Event.SLID, {
                    relatedTarget: nextElement,
                    direction: eventDirectionName,
                    from: activeElementIndex,
                    to: nextElementIndex
                });

                if (this._element.classList.contains(ClassName.SLIDE)) {
                    this._intervalPassed = 0;
                    nextElement.classList.add(orderClassName);

                    Util.reflow(nextElement);
                    activeElement.classList.add(directionalClassName);
                    nextElement.classList.add(directionalClassName);

                    var transitionDuration = Util.getTransitionDurationFromElement(activeElement);

                    var self = this;
                    $dom.one(activeElement, 'transitionend', function () {
                        nextElement.classList.remove(directionalClassName);
                        nextElement.classList.remove(orderClassName);
                        nextElement.classList.add(ClassName.ACTIVE);

                        activeElement.classList.remove(ClassName.ACTIVE);
                        activeElement.classList.remove(orderClassName);
                        activeElement.classList.remove(directionalClassName);

                        self._isSliding = false;
                        
                        setTimeout(function () { $dom.trigger(self._element, slidEvent); }, 0);
                    });

                    setTimeout(function () {
                        $dom.trigger(activeElement, 'transitionend');
                    }, transitionDuration);
                } else {
                    activeElement.classList.remove(ClassName.ACTIVE);
                    nextElement.classList.add(ClassName.ACTIVE);

                    this._isSliding = false;
                    $dom.trigger(this._element, slidEvent);
                }

                if (isCycling) {
                    this.cycle();
                }
            }
        });

        Carousel._jQueryInterface = function _jQueryInterface(config) {
            return this.forEach(function (el) {
                var data = $dom.data(el, DATA_KEY);
                var _config = $util.extend({}, Default, $dom.data(el));

                if (typeof config === 'object') {
                    _config = $util.extend({}, _config, config);
                }

                var action = typeof config === 'string' ? config : _config.slide;

                if (!data) {
                    data = new Carousel(el, _config);
                    $dom.data(el, DATA_KEY, data);
                }

                if (typeof config === 'number') {
                    data.to(config);
                } else if (typeof action === 'string') {
                    if (typeof data[action] === 'undefined') {
                        throw new TypeError('No method named "' + action + '"');
                    }
                    data[action]();
                } else if (_config.interval) {
                    data.pause();
                    data.cycle();
                }
            });
        };

        Carousel._dataApiClickHandler = function _dataApiClickHandler(event) {
            var selector = Util.getSelectorFromElement(this);

            if (!selector) {
                return;
            }

            var target = document.querySelector(selector);

            if (!target || !target.classList.contains(ClassName.CAROUSEL)) {
                return;
            }

            var config = $util.extend({}, $dom.data(target), $dom.data(this));

            var slideIndex = this.getAttribute('data-slide-to');

            if (slideIndex) {
                config.interval = false;
            }

            Carousel._jQueryInterface.call([target], config);

            if (slideIndex) {
                $dom.data(target, DATA_KEY).to(slideIndex);
            }

            event.preventDefault();
        };

        var Util = {
            getSelectorFromElement: function getSelectorFromElement(element) {
                var selector = element.getAttribute('data-target');
                if (!selector || selector === '#') {
                    selector = element.getAttribute('href') || '';
                }

                try {
                    return document.querySelector(selector) ? selector : null;
                } catch (err) {
                    return null;
                }
            },

            getTransitionDurationFromElement: function getTransitionDurationFromElement(element) {
                if (!element) {
                    return 0;
                }

                // Get transition-duration of the element
                var transitionDuration = $dom.css(element, 'transition-duration');
                var floatTransitionDuration = parseFloat(transitionDuration);

                // Return 0 if element or transition duration is not found
                if (!floatTransitionDuration) {
                    return 0;
                }

                // If multiple durations are defined, take the first
                transitionDuration = transitionDuration.split(',')[0];

                return parseFloat(transitionDuration) * 1000;
            },

            reflow: function reflow(element) {
                return element.offsetHeight;
            }
        };

        /**
         * ------------------------------------------------------------------------
         * Data Api implementation
         * ------------------------------------------------------------------------
         */

        $dom.on(document, Event.CLICK_DATA_API, Selector.DATA_SLIDE, Carousel._dataApiClickHandler);
    }());

    function openLinkAsOverlay(options) {
        options = $util.defaults({
            width: '800',
            height: 'auto',
            target: '_top',
            el: null
        }, options);

        var width = strVal(options.width);

        if (width.match(/^\d+$/)) { // Restrain width to viewport width
            width = Math.min(parseInt(width), $dom.getWindowWidth() - 60) + '';
        }

        var el = options.el,
            url = (el.href === undefined) ? el.action : el.href,
            urlStripped = url.replace(/#.*/, ''),
            newUrl = urlStripped + (!urlStripped.includes('?') ? '?' : '&') + 'wide_high=1' + url.replace(/^[^\#]+/, '');

        $cms.ui.open(newUrl, null, 'width=' + width + ';height=' + options.height, options.target);
    }

    function convertTooltip(el) {
        var title = el.title;

        if (!title || $cms.browserMatches('touch_enabled') || el.classList.contains('leave-native-tooltip') || el.dataset['mouseoverActivateTooltip']) {
            return;
        }

        // Remove old tooltip
        if ((el.localName === 'img') && !el.alt) {
            el.alt = el.title;
        }

        el.title = '';

        if (el.onmouseover || (el.firstElementChild && (el.firstElementChild.onmouseover || el.firstElementChild.title))) {
            // Only put on new tooltip if there's nothing with a tooltip inside the element
            return;
        }

        if (el.textContent) {
            var prefix = el.textContent + ': ';
            if (title.substr(0, prefix.length) === prefix) {
                title = title.substring(prefix.length, title.length);
            } else if (title === el.textContent) {
                return;
            }
        }

        // And now define nice listeners for it all...
        var global = $cms.getMainCmsWindow(true);

        el.cmsTooltipTitle = $cms.filter.html(title);

        $dom.on(el, 'mouseover.convertTooltip', function (event) {
            global.$cms.ui.activateTooltip(el, event, el.cmsTooltipTitle, 'auto', '', null, false, false, false, false, global);
        });
    }
}(window.$cms, window.$util, window.$dom));
