(function ($cms, $util, $dom) {
    'use strict';

    $cms.views.BlockMainSearch = BlockMainSearch;
    /**
     * @memberof $cms.views
     * @class
     * @extends $cms.View
     */
    function BlockMainSearch() {
        BlockMainSearch.base(this, 'constructor', arguments);
    }

    $util.inherits(BlockMainSearch, $cms.View, /**@lends BlockMainSearch#*/{
        events: function () {
            return {
                'submit form.js-form-submit-main-search': 'submitMainSearch',
                'keyup .js-keyup-update-ajax-search-list-with-type': 'updateAjaxSearchListWithType',
                'keyup .js-keyup-update-ajax-search-list': 'updateAjaxSearchList'
            };
        },

        submitMainSearch: function (e, form) {
            if ((form.elements.content == null) || $cms.form.checkFieldForBlankness(form.elements.content)) {
                $cms.ui.disableFormButtons(form);
            } else {
                e.preventDefault();
            }
        },

        updateAjaxSearchListWithType: function (e, input) {
            $cms.form.updateAjaxSearchList(input, e, this.params.searchType);
        },

        updateAjaxSearchList: function (e, input) {
            $cms.form.updateAjaxSearchList(input, e);
        }
    });

    $cms.views.SearchFormScreen = SearchFormScreen;
    /**
     * @memberof $cms.views
     * @class
     * @extends $cms.View
     */
    function SearchFormScreen() {
        SearchFormScreen.base(this, 'constructor', arguments);

        this.primaryFormEl = this.$('.js-form-primary-form');
        this.booleanOptionsEl = this.$('.js-el-boolean-options');
    }

    $util.inherits(SearchFormScreen, $cms.View, /**@lends SearchFormScreen#*/{
        events: function () {
            return {
                'keypress .js-keypress-enter-submit-primary-form': 'submitPrimaryForm',
                'keyup .js-keyup-update-ajax-search-list': 'updateAjaxSearchList',
                'keyup .js-keyup-update-author-list': 'updateAuthorList',
                'click .js-click-trigger-resize': 'triggerResize',
                'click .js-checkbox-click-toggle-boolean-options': 'toggleBooleanOptions'
            };
        },
        submitPrimaryForm: function (e) {
            if ($dom.keyPressed(e, 'Enter')) {
                $dom.submit(this.primaryFormEl);
            }
        },
        updateAjaxSearchList: function (e, input) {
            var params = this.params;

            if (params.searchType !== undefined) {
                $cms.form.updateAjaxSearchList(input, e, $cms.filter.nl(params.searchType));
            } else {
                $cms.form.updateAjaxSearchList(input, e);
            }
        },
        updateAuthorList: function (e, target) {
            $cms.form.updateAjaxMemberList(target, 'author', false, e);
        },
        triggerResize: function () {
            $dom.triggerResize();
        },
        toggleBooleanOptions: function (e, checkbox) {
            $dom.toggle(this.booleanOptionsEl, checkbox.checked);
        }
    });

    $cms.templates.blockTopSearch = function (params, container) {
        var searchType = $cms.filter.nl(params.searchType);

        $dom.on(container, 'submit', function (e, form) {
            if (form.elements.content === undefined) {
                $cms.ui.disableFormButtons(form);
                return;
            }

            if ($cms.form.checkFieldForBlankness(form.elements.content)) {
                $cms.ui.disableFormButtons(form);
                return;
            }

            e.preventDefault();
        });

        $dom.on(container, 'keyup', '.js-input-keyup-update-ajax-search-list', function (e, input) {
            $cms.form.updateAjaxSearchList(input, e, searchType);
        });

        $dom.on(container, 'click', '.js-click-toggle-top-search', function (e) {
            e.preventDefault();
            $cms.ui.toggleTopBox('top-search');
        });

        // Hide on click out
        document.documentElement.addEventListener('click', function (e) {
            var clickedInside = container.contains(e.target);

            if (!clickedInside) {
                $cms.ui.toggleTopBox('top-search', true);
            }
        });
    };
}(window.$cms, window.$util, window.$dom));
