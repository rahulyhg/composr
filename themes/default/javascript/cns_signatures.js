(function ($cms) {
    'use strict';

    $cms.functions.hookProfilesTabsEditSignatureRenderTab = function hookProfilesTabsEditSignatureRenderTab(size) {
        size = strVal(size);

        var form = document.getElementById('signature').form;
        form.addEventListener('submit', function () {
            var post = form.elements.signature;

            if ((!post.value) && (post[1])) {
                post = post[1];
            }
            if (post.value.length > size) {
                $cms.ui.alert('{!cns:SIGNATURE_TOO_BIG;^}');
                return false;
            }
        });
    };
}(window.$cms));
