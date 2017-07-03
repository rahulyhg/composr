(function ($cms) {
    'use strict';

    $cms.templates.setupWizard7 = function setupWizard7(params, container) {
        $cms.dom.on('#rules', 'click', function () {
            $cms.dom.smoothScroll($cms.dom.findPosY('#rules_set'));
        });
    };

    $cms.functions.adminSetupWizardStep5 = function () {
        var cuz = document.getElementById('collapse_user_zones');
        if (cuz) {
            cuz.addEventListener('change', cuzFunc);
            cuzFunc();
        }

        function cuzFunc() {
            var gza = document.getElementById('guest_zone_access');
            gza.disabled = cuz.checked;
            if (cuz.checked) {
                gza.checked = true;
            }
        }
    };

    $cms.functions.adminSetupWizardStep7 = function () {
        document.getElementById('rules').addEventListener('change', function () {
            var items = ['preview_box_balanced', 'preview_box_liberal', 'preview_box_corporate'];
            for (var i = 0; i < items.length; i++) {
                document.getElementById(items[i]).style.display = (this.selectedIndex != i) ? 'none' : 'block';
            }
        });
    };

    $cms.functions.adminSetupWizardStep9 = function () {
        document.getElementById('site_closed').addEventListener('change', function () {
            document.getElementById('closed').disabled = !this.checked;
        });
    };
}(window.$cms));
