jQuery(function($) {
    var selector = '.wp-block-image:has(img), .wp-block-gallery-item:has(img)';

    var PhotoSwipe = window.PhotoSwipe,
        PhotoSwipeUI_Default = window.PhotoSwipeUI_Default;

    $('body').on('click', selector, function(e) {
        if( !PhotoSwipe || !PhotoSwipeUI_Default ) {
            return;
        }

        e.preventDefault();
        openPhotoSwipe( false, this, false, '' );
    });
    
    var parseThumbnailElements = function(link) {
        var elements = $('body').find(selector),
            galleryItems = [],
            index;
        
        elements.each(function(i) {
            var element = $(this);
            var caption = null;

            caption = element.find('figcaption').text();

            if(caption == null && lbwps_options.use_alt == '1') {
                caption = element.find('img').attr('alt');
            }

            galleryItems.push({
                src: element.attr('src'),
                w: element.attr('data-width'),
                h: element.attr('data-height'),
                title: caption,
                getThumbBoundsFn: false,
                showHideOpacity: true,
                el: element.find('img').get(0)
            });
            if( link === element.get(0) ) {
                index = i;
            }
        });
        
        return [galleryItems, parseInt(index, 10)];
    };

    var photoswipeParseHash = function() {
        var hash = window.location.hash.substring(1), params = {};

        if(hash.length < 5) {
            return params;
        }

        var vars = hash.split('&');
        for(var i = 0; i < vars.length; i++) {
            if(!vars[i]) {
                continue;
            }
            var pair = vars[i].split('=');
            if(pair.length < 2) {
                continue;
            }
            params[pair[0]] = pair[1];
        }

        if(params.gid) {
            params.gid = parseInt(params.gid, 10);
        }

        return params;
    };

    var openPhotoSwipe = function( element_index, element, fromURL, returnToUrl ) {
        var pswpElement = $('.pswp').get(0),
            gallery,
            options,
            items, index;

        items = parseThumbnailElements(element);
        if(element_index == false) {
            index = items[1];
        } else {
            index = element_index;
        }
        items = items[0];

        options = {
            index: index,
            getThumbBoundsFn: false,
            showHideOpacity: true,
            loop: true,
            tapToToggleControls: true,
            clickToCloseNonZoomable: false,
        };
		
		if(lbwps_options.close_on_click == '0') {
			options.closeElClasses = ['pspw__button--close'];
		}

        if(lbwps_options.share_facebook == '1' ||
            lbwps_options.share_twitter == '1' ||
            lbwps_options.share_pinterest == '1' ||
            lbwps_options.share_download == '1') {
            options.shareEl = true;
            options.shareButtons = [];
            if(lbwps_options.share_facebook == '1') {
                if(lbwps_options.share_direct == '1') {
                    url = 'https://www.facebook.com/sharer/sharer.php?u={{image_url}}';
                } else {
                    url = 'https://www.facebook.com/sharer/sharer.php?u={{url}}';
                }
                options.shareButtons.push( {id:'facebook', label:lbwps_options.label_facebook, url:url} );
            }
            if(lbwps_options.share_twitter == '1') {
                if(lbwps_options.share_direct == '1') {
                    url = 'https://twitter.com/intent/tweet?text={{text}}&url={{image_url}}';
                } else {
                    url = 'https://twitter.com/intent/tweet?text={{text}}&url={{url}}';
                }
                options.shareButtons.push( {id:'twitter', label:lbwps_options.label_twitter, url:url} );
            }
            if(lbwps_options.share_pinterest == '1') options.shareButtons.push( {id:'pinterest', label:lbwps_options.label_pinterest, url:'http://www.pinterest.com/pin/create/button/?url={{url}}&media={{image_url}}&description={{text}}'} );
            if(lbwps_options.share_download == '1') options.shareButtons.push( {id:'download', label:lbwps_options.label_download, url:'{{raw_image_url}}', download:true} );
        } else {
            options.shareEl = false;
        }

        if(lbwps_options.close_on_scroll == '1') options.closeOnScroll = false;
        if(lbwps_options.close_on_drag == '1') options.closeOnVerticalDrag = false;
        if(lbwps_options.history == '1') options.history = true;else options.history = false;
        if(lbwps_options.show_counter == '1') options.counterEl = true;else options.counterEl = false;
        if(lbwps_options.show_fullscreen == '1') options.fullscreenEl = true;else options.fullscreenEl = false;
        if(lbwps_options.show_zoom == '1') options.zoomEl = true;else options.zoomEl = false;
        if(lbwps_options.show_caption == '1') options.captionEl = true;else options.captionEl = false;
        if(lbwps_options.loop == '1') options.loop = true;else options.loop = false;
        if(lbwps_options.pinchtoclose == '1') options.pinchToClose = true;else options.pinchToClose = false;
        if(lbwps_options.taptotoggle == '1') options.tapToToggleControls = true; else options.tapToToggleControls = false;
        options.spacing = lbwps_options.spacing/100;

        if(fromURL == true) {
            options.index = parseInt(index, 10) - 1;
        }

        if(lbwps_options.fulldesktop == '1') {
            options.barsSize = {top: 0, bottom: 0};
        }

        gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
        gallery.listen('gettingData', function (index, item) {
            if (item.w < 1 || item.h < 1) {
                var img = new Image();
                img.onload = function () {
                    item.w = this.width;
                    item.h = this.height;
                    gallery.updateSize(true);
                };
                img.src = item.src;
            }
        });
        
        if (returnToUrl != '') {
            gallery.listen('unbindEvents', function() {
                document.location.href = returnToUrl;
            });
        }
        
        gallery.init();
    };

    var hashData = photoswipeParseHash();
    if(hashData.pid && hashData.gid) {
        if (typeof(hashData.returnurl) !== 'undefined') {
            openPhotoSwipe( hashData.pid, null, true, hashData.returnurl );
        } else {
            openPhotoSwipe( hashData.pid, null, true, '' );
        }
    }
});
