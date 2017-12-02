(function ($cms, $util, $dom) {
    'use strict';

    $cms.views.Attachment = Attachment;
    /**
     * @memberof $cms.views
     * @class
     * @extends $cms.View
     */
    function Attachment(params) {
        Attachment.base(this, 'constructor', arguments);

        if ($cms.configOption('complex_uploader')) {
            window.$plupload.preinitFileInput("attachment_multi", "file" + params.i, params.postingFieldName, params.filter);
        }

        if (params.syndicationJson !== undefined) {
            $cms.requireJavascript('editing').then(function () {
                window.$editing.showUploadSyndicationOptions("file" + params.i, params.syndicationJson, !!params.noQuota);
            });
        }
    }

    $util.inherits(Attachment, $cms.View, /**@lends Attachment#*/{
        events: function () {
            return {
                'change .js-inp-file-change-set-attachment': 'setAttachment'
            };
        },

        setAttachment: function () {
            window.$posting.setAttachment('post', this.params.i, '');
        }
    });

    $cms.views.Carousel = Carousel;
    /**
     * @memberof $cms.views
     * @class
     * @extends $cms.View
     */
    function Carousel(params) {
        Carousel.base(this, 'constructor', arguments);

        var carouselId = strVal(params.carouselId),
            carouselNs = document.getElementById('carousel_ns_' + carouselId);

        this.mainEl = this.$('.main');

        carouselNs.parentNode.removeChild(carouselNs);
        this.mainEl.appendChild(carouselNs);
        this.el.style.display = 'block';

        var self = this;
        $dom.load.then(function () {
            self.$$('.js-btn-car-move').forEach(function (btn) {
                btn.style.height = self.mainEl.offsetHeight + 'px';    
            });

            self.createFaders();
            self.updateFaders();
        });
    }

    $util.inherits(Carousel, $cms.View, /**@lends Carousel#*/{
        events: function () {
            return {
                'mousedown .js-btn-car-move': 'move',
                'keypress .js-btn-car-move': 'move'
            };
        },

        createFaders: function () {
            var mainEl = this.mainEl;
            var left = document.createElement('img');
            left.src = $util.srl('{$IMG;,carousel/fade_left}');
            left.style.position = 'absolute';
            left.style.left = '43px';
            left.style.top = '0';
            mainEl.parentNode.appendChild(left);

            var right = document.createElement('img');
            right.src = $util.srl('{$IMG;,carousel/fade_right}');
            right.style.position = 'absolute';
            right.style.right = '43px';
            right.style.top = '0';
            mainEl.parentNode.appendChild(right);
        },

        updateFaders: function () {
            var mainEl = this.mainEl,
                imgs = mainEl.parentNode.getElementsByTagName('img'),
                left = imgs[imgs.length - 2],
                right = imgs[imgs.length - 1];

            if (left.style.position === 'absolute') { // Check it really is a fader (stops bugs in other areas making bigger weirdness)
                left.style.visibility = (mainEl.scrollLeft == 0) ? 'hidden' : 'visible';
            }

            if (right.style.position === 'absolute'){ // Ditto
                right.style.visibility = (mainEl.scrollLeft + mainEl.offsetWidth >= mainEl.scrollWidth - 1) ? 'hidden' : 'visible';
            }
        },

        move: function (e, btn) {
            var self = this,
                amount = btn.dataset.moveAmount;

            setTimeout(function () {
                self.carouselMove(amount);
            }, 10);
        },

        carouselMove: function (amount) {
            amount = +amount;

            if (amount > 0) {
                this.mainEl.scrollLeft += 3;
                amount--;
                if (amount < 0) {
                    amount = 0;
                }
            } else {
                this.mainEl.scrollLeft -= 3;
                amount++;
                if (amount > 0) {
                    amount = 0;
                }
            }

            this.updateFaders();
            var that = this;
            if (amount !== 0) {
                setTimeout(function () {
                    that.carouselMove(amount);
                }, 10);
            }
        }
    });

    $cms.views.ComcodeMediaSet = ComcodeMediaSet;
    /**
     * @memberof $cms.views
     * @class
     * @extends $cms.View
     */
    function ComcodeMediaSet(params) {
        ComcodeMediaSet.base(this, 'constructor', arguments);

        if ($cms.configOption('js_overlays')) {
            this.setup(params);
        }
    }

    $util.inherits(ComcodeMediaSet, $cms.View, /**@lends ComcodeMediaSet#*/{
        setup: function (params) {
            var imgs = window['imgs_' + params.rand] = [],
                imgsThumbs = window['imgs_thumbs_' + params.rand] = [],
                setImgWidthHeight = false,
                mediaSet = $dom.$id('media_set_' + params.rand),
                as = window.as = mediaSet.querySelectorAll('a, video'),
                containsVideo = false,
                thumbWidthConfig = $cms.configOption('thumb_width') + 'x' + $cms.configOption('thumb_width'),
                i, x;

            if ((thumbWidthConfig !== 'x') && ((params.width + 'x' + params.height) !== 'x')) {
                setImgWidthHeight = true;
            }

            x = 0;
            for (i = 0; i < as.length; i++) {
                if (as[i].localName === 'video') {
                    var span = as[i].querySelector('span'),
                        title = '';

                    if (span) {
                        title = $dom.html(span);
                        span.parentNode.removeChild(span);
                    }

                    imgs.push([$dom.html(as[i]), title, true]);
                    imgsThumbs.push(as[i].poster || '{$IMG^;,video_thumb}');

                    containsVideo = true;

                    x++;

                } else if ((as[i].children.length === 1) && (as[i].firstElementChild.localName === 'img'))  {
                    as[i].title = as[i].title.replace('{!LINK_NEW_WINDOW^;}', '').replace(/^\s+/, '');

                    imgs.push([as[i].href, (as[i].title === '') ? as[i].firstElementChild.alt : as[i].title, false]);
                    imgsThumbs.push(as[i].firstElementChild.src);

                    as[i].addEventListener('click', (function (x) {
                        openImageIntoLightbox(imgs, x);
                        return false;
                    }).bind(undefined, x));

                    if (as[i].rel) {
                        as[i].rel = as[i].rel.replace('lightbox', '');
                    }

                    x++;
                }
            }

            // If you only want a single image-based thumbnail
            if (containsVideo) { // Remove this 'if' (so it always runs) if you do not want the grid-style layout (plus remove the media_set class from the outer div
                var width = params.width ? 'style="width: ' + Number(params.width) + 'px"' : '',
                    imgWidthHeight = setImgWidthHeight ? ' width="' + Number(params.width) + '" height="' + Number(params.height) + '"' : '',
                    mediaSetHtml = /** @lang HTML */'' +
                        '<figure class="attachment" ' + width + '>' +
                        '   <figcaption>' + $util.format('{!comcode:MEDIA_SET;^}', [imgs.length]) + '</figcaption>' +
                        '   <div>' +
                        '        <div class="attachment_details">' +
                        '            <a class="js-click-open-images-into-lightbox" target="_blank" title="' + $cms.filter.html($util.format('{!comcode:MEDIA_SET^;}', [imgs.length])) + ' {!LINK_NEW_WINDOW^/}" href="#!">' +
                        '                <img ' + imgWidthHeight + ' src="' + $cms.filter.html(imgsThumbs[0]) + '">' +
                        '            </a>' +
                        '        </div>' +
                        '    </div>' +
                        '</figure>';
                $dom.html(mediaSet, mediaSetHtml);
                $dom.on(mediaSet.querySelector('.js-click-open-images-into-lightbox'), 'click', function () {
                    openImageIntoLightbox(imgs);
                });
            }

            function openImageIntoLightbox(imgs, start) {
                start = Number(start) || 0;

                var modal = $cms.ui.openImageIntoLightbox(imgs[start][0], imgs[start][1], start + 1, imgs.length, true, imgs[start][2]);
                modal.positionInSet = start;

                var previousButton = document.createElement('img');
                previousButton.className = 'previous_button';
                previousButton.src = $util.srl('{$IMG;,mediaset_previous}');
                previousButton.addEventListener('click', clickPreviousButton);
                function clickPreviousButton(e) {
                    var newPosition = modal.positionInSet - 1;
                    if (newPosition < 0) {
                        newPosition = imgs.length - 1;
                    }
                    modal.positionInSet = newPosition;
                    _openDifferentImageIntoLightbox(modal, newPosition, imgs);
                }

                modal.left = clickPreviousButton;
                modal.el.firstElementChild.appendChild(previousButton);

                var nextButton = document.createElement('img');
                nextButton.className = 'next_button';
                nextButton.src = $util.srl('{$IMG;,mediaset_next}');
                nextButton.addEventListener('click', clickNextButton);
                function clickNextButton(e) {
                    var newPosition = modal.positionInSet + 1;
                    if (newPosition >= imgs.length) {
                        newPosition = 0;
                    }
                    modal.positionInSet = newPosition;
                    _openDifferentImageIntoLightbox(modal, newPosition, imgs);
                }

                modal.right = clickNextButton;
                modal.el.firstElementChild.appendChild(nextButton);

                function _openDifferentImageIntoLightbox(modal, position, imgs) {
                    var isVideo = imgs[position][2];

                    // Load proper image
                    setTimeout(function () { // Defer execution until the HTML was parsed
                        if (isVideo) {
                            var video = document.createElement('video');
                            video.id = 'lightbox_image';
                            video.className = 'lightbox_image';
                            video.controls = 'controls';
                            video.autoplay = 'autoplay';
                            $dom.html(video, imgs[position][0]);
                            video.addEventListener('loadedmetadata', function () {
                                $cms.ui.resizeLightboxDimensionsImg(modal, video, true, true);
                            });
                        } else {
                            var img = modal.topWindow.document.createElement('img');
                            img.className = 'lightbox_image';
                            img.id = 'lightbox_image';
                            img.src = '{$IMG_INLINE;,loading}';
                            setTimeout(function () { // Defer execution until after loading is set
                                img.addEventListener('load', function () {
                                    $cms.ui.resizeLightboxDimensionsImg(modal, img, true, isVideo);
                                });
                                img.src = imgs[position][0];
                            }, 0);
                        }

                        var lightboxDescription = modal.topWindow.$dom.$id('lightbox_description'),
                            lightboxPositionInSetX = modal.topWindow.$dom.$id('lightbox_position_in_set_x');

                        if (lightboxDescription) {
                            $dom.html(lightboxDescription, imgs[position][1]);
                        }

                        if (lightboxPositionInSetX) {
                            $dom.html(lightboxPositionInSetX, position + 1);
                        }
                    });
                }

            }
        }
    });

    /**
     * @memberof $cms.views
     * @class
     * @extends $cms.View
     */
    function AttachmentsBrowser() {
        AttachmentsBrowser.base(this, 'constructor', arguments);
    }

    $util.inherits(AttachmentsBrowser, $cms.View, /**@lends AttachmentsBrowser#*/{
        events: function () {
            return {
                'click .js-click-do-attachment-and-close': 'doAttachmentAndClose'
            };
        },
        doAttachmentAndClose: function () {
            var params = this.params,
                fieldName = params.fieldName || '',
                id = params.id || '',
                description = params.description || '';

            window.$editing.doAttachment(fieldName, id, description).then(function () {
                window.fauxClose ? window.fauxClose() :  window.close();
            });
        }
    });

    $cms.functions.comcodeToolsComcodeConvertScript = function comcodeToolsComcodeConvertScript() {
        var form = $dom.$('#semihtml').form;

        form.elements['from_html'][0].addEventListener('click', refreshLockedInputs);
        form.elements['from_html'][1].addEventListener('click', refreshLockedInputs);
        form.elements['from_html'][2].addEventListener('click', refreshLockedInputs);

        function refreshLockedInputs() {
            var value = $cms.form.radioValue(form.elements['from_html']);
            $dom.$('#semihtml').disabled = (value != 0);
            $dom.$('#is_semihtml').disabled = (value != 0);
            $dom.$('#lax').disabled = (value != 0);
            $dom.$('#fix_bad_html').disabled = (value == 1);
            $dom.$('#force').disabled = (value != 1);
        }
    };

    $cms.functions.comcodeAddTryForSpecialComcodeTag = function comcodeAddTryForSpecialComcodeTag() {
        document.getElementById('framed').addEventListener('change', function () {
            if (this.checked && document.getElementById('_safe')) {
                document.getElementById('_safe').checked = false;
            }
        });
    };

    $cms.templates.comcodeMemberLink = function comcodeMemberLink(params, container) {
        $dom.on(container, 'mouseover', '.js-mouseover-comcode-member-link', activateComcodeMemberLink);
        $dom.on(container, 'focus', '.js-focus-comcode-member-link', activateComcodeMemberLink);

        function activateComcodeMemberLink(e, el) {
            el.cancelled = false;
            $cms.loadSnippet('member_tooltip&member_id=' + params.memberId, null, true).then(function (result) {
                if (!el.cancelled) {
                    $cms.ui.activateTooltip(el, e, result, 'auto', null, null, false, true);
                }
            });
        }

        $dom.on(container, 'mouseout', '.js-mouseout-comcode-member-link', function (e, el) {
            $cms.ui.deactivateTooltip(el);
            el.cancelled = true;
        });
    };

    $cms.templates.comcodeMessage = function comcodeMessage(params, container) {
        var name = strVal(params.name);

        $dom.on(container, 'click', '.js-link-click-open-emoticon-chooser-window', function (e, link) {
            var url = $cms.maintainThemeInLink(link.href);
            $cms.ui.open(url, 'field_emoticon_chooser', 'width=300,height=320,status=no,resizable=yes,scrollbars=no');
        });

        $dom.on(container, 'click', '.js-click-toggle-wysiwyg', function () {
            window.$editing.toggleWysiwyg(name);
        });
    };

    $cms.templates.comcodeTabHead = function comcodeTabHead(params, container) {
        var tabSets = $cms.filter.id(params.tabSets),
            title = $cms.filter.id(params.title);

        $dom.on(container, 'click', function () {
            $cms.ui.selectTab('g', tabSets + '_' + title);
        });
    };

    $cms.templates.attachments = function attachments(params, container) {
        window.maxAttachments = Number(params.maxAttachments) || 0;
        window.numAttachments = Number(params.numAttachments) || 0;
        
        var postingFieldName = strVal(params.postingFieldName);
        
        if ($cms.browserMatches('simplified_attachments_ui')) {
            window.attachmentTemplate = strVal(params.attachmentTemplate);
            window.numAttachments = 1;
            window.rebuildAttachmentButtonForNext = rebuildAttachmentButtonForNext;

            $dom.load.then(function () {
                var aub = document.getElementById('js-attachment-upload-button');
                if (aub && (aub.classList.contains('for_field_' + postingFieldName))) {
                    // Attach Plupload with #js-attachment-upload-button as browse button
                    window.rebuildAttachmentButtonForNext(postingFieldName, 'js-attachment-upload-button');
                }
            });
        } else {
            $dom.on(container, 'click', '.js-click-open-attachment-popup', function (e, link) {
                e.preventDefault();
                $cms.ui.open($cms.maintainThemeInLink(link.href), 'site_attachment_chooser', 'width=550,height=600,status=no,resizable=yes,scrollbars=yes');
            });
        }
        
        
        var lastAttachmentBrowseButton;

        /**
         * Bind Plupload to the specified browse button (`attachmentBrowseButton`)
         * @param _postingFieldName
         * @param attachmentBrowseButton
         */
        function rebuildAttachmentButtonForNext(_postingFieldName, attachmentBrowseButton) {
            console.log('rebuildAttachmentButtonForNext()', '_postingFieldName:', _postingFieldName, 'attachmentBrowseButton:', attachmentBrowseButton);
            
            if (_postingFieldName !== postingFieldName) {
                return;
            }

            if (attachmentBrowseButton === undefined) {
                attachmentBrowseButton = lastAttachmentBrowseButton; // Use what was used last time
            }
            
            lastAttachmentBrowseButton = attachmentBrowseButton;

            $cms.requireJavascript('plupload').then(function () {
                window.$plupload.prepareSimplifiedFileInput('attachment_multi', 'file' + window.numAttachments, postingFieldName, strVal(params.filter), attachmentBrowseButton);
            });
        }
    };

    $cms.templates.comcodeImg = function comcodeImg(params) {
        var img = this,
            refreshTime = +params.refreshTime || 0;

        if ((typeof params.rollover === 'string') && (params.rollover !== '')) {
            $cms.createRollover(img.id, params.rollover);
        }

        if (refreshTime > 0) {
            setInterval(function () {
                if (!img.timer) {
                    img.timer = 0;
                }
                img.timer += refreshTime;

                if (img.src.indexOf('?') === -1) {
                    img.src += '?time=' + img.timer;
                } else if (img.src.indexOf('time=') === -1) {
                    img.src += '&time=' + img.timer;
                } else {
                    img.src = img.src.replace(/time=\d+/, 'time=' + img.timer);
                }
            }, refreshTime);
        }
    };

    $cms.templates.comcodeEditorButton = function comcodeEditorButton(params, btn) {
        var isPostingField = Boolean(params.isPostingField),
            b = strVal(params.b),
            fieldName = strVal(params.fieldName);

        $dom.on(btn, 'click', function () {
            var mainWindow = btn.ownerDocument.defaultView;
            
            if ($cms.browserMatches('simplified_attachments_ui') && isPostingField && ((b === 'thumb') || (b === 'img'))) {
                return;
            }
            
            mainWindow['doInput' + $util.ucFirst($util.camelCase(b))](fieldName);
        });
    };

    $cms.templates.comcodeRandom = function comcodeRandom(params) {
        var rand, part, use, comcoderandom;

        rand = parseInt(Math.random() * params.max);

        for (var key in params.parts) {
            part = params.parts[key];
            use = part.val;

            if (key > rand) {
                break;
            }
        }

        comcoderandom = document.getElementById('comcoderandom' + params.randIdRandom);
        $dom.html(comcoderandom, use);
    };

    $cms.templates.comcodePulse = function (params) {
        var id = 'pulse_wave_' + params.randIdPulse;

        window[id] = [0, params.maxColor, params.minColor, params.speed, []];
        setInterval(function () {
            window.$pulse.processWave(document.getElementById(id));
        }, params.speed);
    };

    $cms.templates.comcodeShocker = function (params) {
        var id = params.randIdShocker,
            parts = params.parts || [], part,
            time = +params.time;

        window.shockerParts || (window.shockerParts = {});
        window.shockerPos || (window.shockerPos = {});

        window.shockerParts[id] = [];
        window.shockerPos[id] = 0;

        for (var i = 0, len = parts.length; i < len; i++) {
            part = parts[i];
            window.shockerParts[id].push([part.left, part.right]);
        }

        shockerTick(id, time, params.maxColor, params.minColor);
        setInterval(function () {
            shockerTick(id, time, params.maxColor, params.minColor);
        }, time);
    };
    
    $cms.views.ComcodeSectionController = ComcodeSectionController;
    /**
     * @memberof $cms.views
     * @class $cms.views.ComcodeSectionController
     * @extends $cms.View
     */
    function ComcodeSectionController(params) {
        ComcodeSectionController.base(this, 'constructor', arguments);
        
        this.passId = $cms.filter.id(params.passId);
        this.sections = params.sections.map($cms.filter.id);

        flipPage(0, this.passId, this.sections);
    }

    $util.inherits(ComcodeSectionController, $cms.View, /**@lends $cms.views.ComcodeSectionController#*/{
        events: function events() {
            return {
                'click .js-click-flip-page': 'doFlipPage'
            };
        },
        
        doFlipPage: function doFlipPage(e, clicked) {
            var flipTo = clicked.dataset.vwFlipTo;

            flipPage(flipTo, this.passId, this.sections);
        }
    });

    $cms.templates.emoticonClickCode = function emoticonClickCode(params, container) {
        var fieldName = strVal(params.fieldName);

        $dom.on(container, 'click', function (e) {
            e.preventDefault();
            window.$editing.doEmoticon(fieldName, container, false);
        });
    };

    $cms.templates.comcodeOverlay = function comcodeOverlay(params, container) {
        var id = strVal(params.id),
            timeout = Number(params.timeout),
            timein = Number(params.timein);

        $dom.on(container, 'click', '.js-click-dismiss-overlay', function () {
            var bi = document.getElementById('main_website_inner');
            if (bi) {
                bi.style.opacity = 1;
            }

            document.getElementById(params.randIdOverlay).style.display = 'none';

            if (id) {
                $cms.setCookie('og_' + id, '1', 365);
            }
        });

        if (!id || ($cms.readCookie('og_' + id) !== '1')) {
            setTimeout(function () {
                var element, bi;

                $dom.smoothScroll(0);

                element = document.getElementById(params.randIdOverlay);
                element.style.display = 'block';
                element.parentNode.removeChild(element);
                document.body.appendChild(element);

                bi = document.getElementById('main_website_inner');

                if (bi) {
                    bi.style.opacity = 0.4;
                }
                
                $dom.fadeIn(element);


                if (timeout !== -1) {
                    setTimeout(function () {
                        if (bi) {
                            bi.style.opacity = 1;
                        }

                        if (element) {
                            element.style.display = 'none';
                        }
                    }, timeout);
                }
            }, timein + 100);
        }
    };
    
    $cms.views.ComcodeBigTabsController = ComcodeBigTabsController;
    /**
     * @memberof $cms.views
     * @class $cms.views.ComcodeBigTabsController
     * @extends $cms.View
     */
    function ComcodeBigTabsController(params) {
        ComcodeBigTabsController.base(this, 'constructor', arguments);
        
        var passId = this.passId = $cms.filter.id(params.passId),
            id = this.id = passId + '_' + params.bigTabSets,
            sections = this.sections = params.tabs.map($cms.filter.id),
            switchTime = this.switchTime = params.switchTime;
        
        /* Precache images */
        new Image().src = $util.srl('{$IMG;,big_tabs_controller_button}');
        new Image().src = $util.srl('{$IMG;,big_tabs_controller_button_active}');
        new Image().src = $util.srl('{$IMG;,big_tabs_controller_button_top_active}');
        new Image().src = $util.srl('{$IMG;,big_tabs_controller_button_top}');
        
        if (switchTime !== undefined) {
            flipPage(0, id, sections, switchTime);
        }
    }

    $util.inherits(ComcodeBigTabsController, $cms.View, /**@lends $cms.views.ComcodeBigTabsController#*/{
        events: function events() {
            return {
                'click .js-onclick-flip-page': 'doFlipPage'
            };
        },

        doFlipPage: function doFlipPage(e, clicked) {
            var flipTo = clicked.dataset.vwFlipTo;
            
            flipPage(flipTo, this.id, this.sections, this.switchTime);
        }
    });
    

    $cms.templates.comcodeTabBody = function (params) {
        var title = $cms.filter.id(params.title);

        if (params.blockCallUrl) {
            window['load_tab__' + title] = function () {
                $cms.callBlock(params.blockCallUrl, '', document.getElementById('g_' + title));
            };
        }
    };

    $cms.templates.comcodeTicker = function (params, container) {
        window.tickPos || (window.tickPos = {});

        var id = 'ticker-' + $util.random();

        window.tickPos[id] = params.width;
        $dom.html(container, '<div class="ticker" style="text-indent: ' + params.width + 'px; width: ' + params.width + 'px;" id="' + id + '"><span>' +
            $cms.filter.nl(params.text) + '<\/span><\/div>'
        );

        setInterval(function () {
            tickerTick(id, params.width);
        }, 100 / params.speed);
    };

    $cms.templates.comcodeJumping = function (params, container) {
        var id = $util.random();

        window.jumperParts[id] = [];
        window.jumperPos[id] = 1;

        for (var i = 0, len = params.parts.length; i < len; i++) {
            window.jumperParts[id].push(params.parts[i].part);
        }

        $dom.html(container, '<span id="' + id + '">' + window.jumperParts[id][0] + '<\/span>');

        setInterval(function () {
            jumperTick(id);
        }, params.time);
    };


    var promiseYouTubeIframeAPIReady;
    $cms.templates.mediaYoutube = function (params, element) {
        // Tie into callback event to see when finished, for our slideshows
        // API: https://developers.google.com/youtube/iframe_api_reference
        
        if (promiseYouTubeIframeAPIReady == null) {
            promiseYouTubeIframeAPIReady = new Promise(function (resolve) {
                if ((window.YT != null) && (window.YT.Player != null)) {
                    resolve();
                } else {
                    window.onYouTubeIframeAPIReady = function onYouTubeIframeAPIReady() {
                        resolve();
                        delete window.onYouTubeIframeAPIReady;
                    };
                    $cms.requireJavascript('https://www.youtube.com/iframe_api');  
                }
            });   
        }
        
        promiseYouTubeIframeAPIReady.then(function () {
            var slideshowMode = document.getElementById('next_slide'),
                player = new window.YT.Player(element.id, {
                    width: params.width,
                    height: params.height,
                    videoId: params.remoteId,
                    events: {
                        onReady: function () {
                            if (slideshowMode) {
                                player.playVideo();
                            }
                        },
                        onStateChange: function (newState) {
                            if (slideshowMode) {
                                if (newState == 0) {
                                    window.$galleries.playerStopped();
                                }
                            }
                        }
                    }
                });
        });
    };

    // LEGACY
    $cms.templates.mediaRealmedia = function (params) {
        // Tie into callback event to see when finished, for our slideshows
        // API: http://service.real.com/help/library/guides/realone/ScriptingGuide/PDF/ScriptingGuide.pdf
        $dom.load.then(function () {
            if (document.getElementById('next_slide')) {
                window.$galleries.stopSlideshowTimer();
                setTimeout(function () {
                    document.getElementById(params.playerId).addEventListener('stateChange', function (newState) {
                        if (newState == 0) {
                            window.$galleries.playerStopped();
                        }
                    });
                    document.getElementById(params.playerId).DoPlay();
                }, 1000);
            }
        });
    };

    // LEGACY
    $cms.templates.mediaQuicktime = function (params) {
        // Tie into callback event to see when finished, for our slideshows
        // API: http://developer.apple.com/library/safari/#documentation/QuickTime/Conceptual/QTScripting_JavaScript/bQTScripting_JavaScri_Document/QuickTimeandJavaScri.html
        $dom.load.then(function () {
            if (document.getElementById('next_slide')) {
                window.$galleries.stopSlideshowTimer();
                setTimeout(function () {
                    document.getElementById(params.playerId).addEventListener('qt_ended', function () {
                        window.$galleries.playerStopped();
                    });
                    document.getElementById(params.playerId).Play();
                }, 1000);
            }
        });
    };

    // LEGACY
    $cms.templates.mediaVideoGeneral = function (params) {
        // Tie into callback event to see when finished, for our slideshows
        // API: http://developer.apple.com/library/safari/#documentation/QuickTime/Conceptual/QTScripting_JavaScript/bQTScripting_JavaScri_Document/QuickTimeandJavaScri.html
        // API: http://msdn.microsoft.com/en-us/library/windows/desktop/dd563945(v=vs.85).aspx
        $dom.load.then(function () {
            if (document.getElementById('next_slide')) {
                window.$galleries.stopSlideshowTimer();

                setTimeout(function () {
                    var player = document.getElementById(params.playerId);
                    // WMP
                    player.addEventListener('playstatechange', function (newState) {
                        if (newState == 1) {
                            window.$galleries.playerStopped();
                        }
                    });

                    // Quicktime
                    player.addEventListener('qt_ended', function () {
                        window.$galleries.playerStopped();
                    });

                    try {
                        player.Play();
                    } catch (e) {}
                    
                    try {
                        player.controls.play();
                    } catch (e) {}
                }, 1000);
            }
        });
    };

    $cms.templates.mediaVimeo = function (params) {
        // Tie into callback event to see when finished, for our slideshows
        if (document.getElementById('next_slide')) {
            window.$galleries.stopSlideshowTimer();
            setTimeout(function () {
                window.addEventListener('message', window.$galleries.playerStopped, false);

                var player = document.getElementById(params.playerId);
                player.contentWindow.postMessage(JSON.stringify({method: 'addEventListener', value: 'finish'}), 'https://player.vimeo.com/video/' + params.remoteId);
            }, 1000);
        }
    };

    // API: http://www.longtailvideo.com/support/jw-player/jw-player-for-flash-v5/12540/javascript-api-reference
    // Carefully tuned to avoid this problem: http://www.longtailvideo.com/support/forums/jw-player/setup-issues-and-embedding/8439/sound-but-no-video
    $cms.templates.mediaAudioWebsafe = function (params) {
        var playerOptions = {
            width: params.width,
            height: params.height,
            autostart: false,
            file: params.url,
            type: params.type,
            image: params.thumbUrl,
            flashplayer: params.flashplayer,
            events: {
                onComplete: function () {
                    if (document.getElementById('next_slide')) {
                        window.$galleries.playerStopped();
                    }
                },
                onReady: function () {
                    if (document.getElementById('next_slide')) {
                        window.$galleries.stopSlideshowTimer();
                        jwplayer(params.playerId).play(true);
                    }
                }
            }
        };

        if (params.duration) {
            playerOptions.duration = params.duration;
        }

        if (!($cms.configOption('show_inline_stats'))) {
            playerOptions.events.onPlay = function () {
                $cms.gaTrack(null, '{!AUDIO;^}', params.url);
            };
        }

        jwplayer(params.playerId).setup(playerOptions);
    };

    // API: http://www.longtailvideo.com/support/jw-player/jw-player-for-flash-v5/12540/javascript-api-reference
    // Carefully tuned to avoid this problem: http://www.longtailvideo.com/support/forums/jw-player/setup-issues-and-embedding/8439/sound-but-no-video
    $cms.templates.mediaVideoWebsafe = function (params) {
        var playerOptions = {
            autostart: false,
            file: params.url,
            type: params.type,
            image: params.thumbUrl,
            flashplayer: params.flashplayer,
            events: {
                onComplete: function () {
                    if (document.getElementById('next_slide')) {
                        window.$galleries.playerStopped();
                    }
                },
                onReady: function () {
                    if (document.getElementById('next_slide')) {
                        window.$galleries.stopSlideshowTimer();
                        jwplayer(params.playerId).play(true);
                    }
                }
            }
        };

        if (params.duration) {
            playerOptions.duration = params.duration;
        }

        if (params.playerWidth) {
            //playerOptions.width = params.playerWidth;
        }
        playerOptions.width = '100%';
        if (params.playerHeight) {
            //playerOptions.height = params.playerHeight;
        }
        if (params.playerWidth && params.playerHeight) {
            playerOptions = params.playerWidth + ':' + params.playerHeight;
        }

        if (!$cms.configOption('show_inline_stats')) {
            playerOptions.events.onPlay = function () {
                $cms.gaTrack(null, '{!VIDEO;^}', params.url);
            };
        }

        window.jwplayer(params.playerId).setup(playerOptions);
    };
    
    $cms.functions.comcodeAddTryForSpecialComcodeTagSpecificContentsUi = function () {
        // If we select an image we want to have good defaults for an image, but only if the defaults weren't already changed
        document.getElementById('tag_contents__b').onchange = function () {
            var ext = this.value.substring(this.value.indexOf('.') + 1);
            var isImage = ',{$CONFIG_OPTION;,valid_images}'.indexOf(',' + ext + ',') !== -1;
            if (isImage) {
                var framed = document.getElementById('framed');
                var wysiwygEditable = document.getElementById('wysiwyg_editable');
                var thumb = document.getElementById('thumb');

                if (framed.defaultChecked == framed.checked && wysiwygEditable.defaultChecked == wysiwygEditable.checked && thumb.defaultChecked == thumb.checked) {
                    framed.checked = false;
                    wysiwygEditable.checked = true;
                    thumb.checked = false;
                }
            }
        };
    };

    function shockerTick(id, time, minColor, maxColor) {
        if ((document.hidden !== undefined) && (document.hidden)) {
            return;
        }

        if (window.shockerPos[id] == window.shockerParts[id].length - 1) {
            window.shockerPos[id] = 0;
        }
        var eLeft = document.getElementById('comcodeshocker' + id + '_left');
        if (!eLeft) {
            return;
        }
        $dom.html(eLeft, window.shockerParts[id][window.shockerPos[id]][0]);
        $dom.fadeIn(eLeft);

        var eRight = document.getElementById('comcodeshocker' + id + '_right');
        if (!eRight) {
            return;
        }
        $dom.html(eRight, window.shockerParts[id][window.shockerPos[id]][1]);
        $dom.fadeIn(eRight);

        window.shockerPos[id]++;

        window['comcodeshocker' + id + '_left'] = [0, minColor, maxColor, time / 13, []];
        setInterval(function () {
            window.$pulse.processWave(eLeft);
        }, window['comcodeshocker' + id + '_left'][3]);
    }

    var _flipPageTimeouts = {};
    function flipPage(to, id, sections, switchTime) {
        var i, currentPos = 0, section;
        
        if (_flipPageTimeouts[id]) {
            clearTimeout(_flipPageTimeouts[id]);
            delete _flipPageTimeouts[id];
        }

        if ($util.isNumeric(to)) {
            to = Number(to);
            
            for (i = 0; i < sections.length; i++) {
                section = document.getElementById(id + '_section_' + sections[i]);
                if (section) {
                    if ((section.style.display === 'block') && (section.style.position !== 'absolute')) {
                        currentPos = i;
                        break;
                    }
                }
            }

            currentPos += to;
        } else {
            for (i = 0; i < sections.length; i++) {
                if (sections[i] === to) {
                    currentPos = i;
                    break;
                }
            }
        }

        // Previous/next updates
        var el;
        el = document.getElementById(id + '_has_next_yes');
        if (el) {
            el.style.display = (currentPos === (sections.length - 1)) ? 'none' : 'inline-block';
        }
        el = document.getElementById(id + '_has_next_no');
        if (el) {
            el.style.display = (currentPos === (sections.length - 1)) ? 'inline-block' : 'none';
        }
        el = document.getElementById(id + '_has_previous_yes');
        if (el) {
            el.style.display = (currentPos === 0) ? 'none' : 'inline-block';
        }
        el = document.getElementById(id + '_has_previous_no');
        if (el) {
            el.style.display = (currentPos === 0) ? 'inline-block' : 'none';
        }

        // We make our forthcoming one instantly visible to stop the browser possibly scrolling up if there is a tiny time interval when none are visible
        el = document.getElementById(id + '_section_' + sections[i]);
        if (el) {
            el.style.display = 'block';
        }

        for (i = 0; i < sections.length; i++) {
            el = document.getElementById(id + '_goto_' + sections[i]);
            if (el) {
                el.style.display = (i === currentPos) ? 'none' : 'inline-block';
            }
            el = document.getElementById(id + '_btgoto_' + sections[i]);
            if (el) {
                el.classList.toggle('big-tab-active', (i === currentPos));
                el.classList.toggle('big-tab-inactive', (i !== currentPos));
            }
            el = document.getElementById(id + '_isat_' + sections[i]);
            if (el) {
                el.style.display = (i === currentPos) ? 'inline-block' : 'none';
            }
            el = document.getElementById(id + '_section_' + sections[i]);

            if (el) {
                if (el.classList.contains('comcode-big-tab')) {
                    if (i === currentPos) {
                        el.style.width = '';
                        el.style.position = 'static';
                        el.style.zIndex = 10;
                        el.style.opacity = 1;
                    } else {
                        el.style.opacity = (el.style.position !== 'static') ? 0 : 1;
                        el.style.width = (el.offsetWidth - 24) + 'px'; // 24=lhs padding+rhs padding+lhs border+rhs border
                        el.style.position = 'absolute';
                        el.style.zIndex = -10;
                        el.style.top = '0';
                        el.parentNode.style.position = 'relative';

                        $dom.fadeOut(el);
                    }
                    el.style.display = 'block';
                } else {
                    el.style.display = (i === currentPos) ? 'block' : 'none';

                    if (i === currentPos) {
                        $dom.fadeIn(el);
                    }
                }
            }
        }
        
        if (switchTime) {
            _flipPageTimeouts[id] = setTimeout(function () {
                var nextPage = 0, i, el;

                for (i = 0; i < sections.length; i++) {
                    el = document.getElementById(id + '_section_' + sections[i]);
                    if ((el.style.display === 'block') && (el.style.position !== 'absolute')) {
                        nextPage = i + 1;
                    }
                }

                if (nextPage === sections.length) {
                    nextPage = 0;
                }

                flipPage(sections[nextPage], id, sections, switchTime);
            }, switchTime);
        }

        return false;
    }

    // =======
    // COMCODE
    // =======

    window.countdown = countdown;
    function countdown(id, direction, tailing) {
        var countdown = (typeof id === 'object') ? id : document.getElementById(id), i;
        var inside = $dom.html(countdown);
        var multiples = [];
        if (tailing >= 4) {
            multiples.push(365);
        }
        if (tailing >= 3) {
            multiples.push(24);
        }
        if (tailing >= 2) {
            multiples.push(60);
        }
        if (tailing >= 1) {
            multiples.push(60);
        }
        multiples.push(1);
        var us = inside.match(/\d+/g);
        var total = 0, multiplier = 1;

        while (multiples.length > us.length) {
            us.push('0');
        }

        for (i = us.length - 1; i >= 0; i--) {
            multiplier *= multiples[i];
            total += parseInt(us[i]) * multiplier;
        }

        if (total > 0) {
            total += Number(direction);
            inside = inside.replace(/\d+/g, '!!!');

            if (total === 0) {
                countdown.classList.add('red_alert');
            }

            for (i = 0; i < us.length; i++) {
                us[i] = Math.floor(total / multiplier);
                total -= us[i] * multiplier;
                multiplier /= multiples[i];
                inside = inside.replace('!!!', us[i]);
            }

            $dom.html(countdown, inside);
        }
    }

    window.tickPos || (window.tickPos = {});

    window.tickerTick = tickerTick;
    function tickerTick(id, width) {
        if (document.hidden === true) {
            return;
        }

        var el = document.getElementById(id);
        if (!el || $dom.$('#' + id + ':hover')) {
            return;
        }

        el.style.textIndent = window.tickPos[id] + 'px';
        window.tickPos[id]--;
        if (window.tickPos[id] < -1.1 * el.children[0].offsetWidth) {
            window.tickPos[id] = width;
        }
    }

    window.jumperPos || (window.jumperPos = []);
    window.jumperParts || (window.jumperParts = []);

    window.jumperTick = jumperTick;
    function jumperTick(id) {
        if (document.hidden === true) {
            return;
        }

        if (window.jumperPos[id] === (window.jumperParts[id].length - 1)) {
            window.jumperPos[id] = 0;
        }
        var el = document.getElementById(id);
        if (!el) {
            return;
        }
        $dom.html(el, window.jumperParts[id][window.jumperPos[id]]);
        window.jumperPos[id]++;
    }

    window.crazyTick = crazyTick;
    function crazyTick() {
        if (window.currentMouseX == null) {
            return;
        }
        if (window.currentMouseY == null) {
            return;
        }

        var e, i, sWidth, biasx, biasy;
        for (i = 0; i < window.crazyCriters.length; i++) {
            e = document.getElementById(window.crazyCriters[i]);
            sWidth = e.clientWidth;

            biasx = window.currentMouseX - e.offsetLeft;
            if (biasx > 0) {
                biasx = 2;
            } else {
                biasx = -1;
            }

            if (Math.random() * 4 < 1) {
                biasx = 0;
            }

            biasy = window.currentMouseY - e.offsetTop;
            if (biasy > 0) {
                biasy = 2;
            } else {
                biasy = -1;
            }

            if (Math.random() * 4 < 1) {
                biasy = 0;
            }

            if (sWidth < 100) {
                e.style.width = (sWidth + 1) + 'px';
            }

            e.style.left = (e.offsetLeft + (Math.random() * 2 - 1 + biasx) * 30) + 'px';
            e.style.top = (e.offsetTop + (Math.random() * 2 - 1 + biasy) * 30) + 'px';
            e.style.position = 'absolute';
        }
    }
}(window.$cms, window.$util, window.$dom));
