// iFrame
document.addEventListener('DOMContentLoaded', function () {
    var iframe_data = document.getElementsByClassName('sr-external-page-content-iframe');

    // loop through all elements with class 'sr-external-page-content-iframe'
    for (var i = 0; i < iframe_data.length; i++) {
        let d = iframe_data[i];
        let url = d.getAttribute('data-url');
        let title = d.getAttribute('data-title');
        let frameborder = d.getAttribute('data-frameborder');
        let allowfullscreen = d.getAttribute('data-allowfullscreen');
        let content_id = d.getAttribute('data-content-id');

        // add click event listener to the button
        d.addEventListener(content_id, function () {
            // if the button is clicked, create an iframe element
            let iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.title = title;
            iframe.frameborder = frameborder;
            iframe.allowfullscreen = allowfullscreen;

            // always fill the iframe size to the parent div
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            if (iframe.frameborder === '0') {
                iframe.style.border = 'none';
            }

            d.appendChild(iframe);
            // show the element
            d.style.display = 'block';
        });
    }

});

// Wrapper
document.addEventListener('DOMContentLoaded', function () {
    var iframe_data = document.getElementsByClassName('sr-external-page-content');

    // loop through all elements with class 'sr-external-page-content-iframe'
    for (var i = 0; i < iframe_data.length; i++) {
        let d = iframe_data[i];
        let height = d.getAttribute('data-height');
        let width = d.getAttribute('data-width');
        let responsive = d.getAttribute('data-responsive');
        let content_id = d.getAttribute('data-content-id');
        let consent = d.getAttribute('data-consent');
        let consented = localStorage.getItem(content_id);
        if (d.getAttribute('data-consented') === '1') {
            consented = '1';
        }
        const resizer = function () {
            // parent container
            var parent = d.parentElement;
            // get the width and height of the parent container
            var rect = parent.getBoundingClientRect();
            // get the padding of the parent container
            var computed = window.getComputedStyle(parent, null);
            // calculate the available width inside the parent container

            var parent_width = rect.width
                - parseInt(computed.getPropertyValue('padding-left'))
                - parseInt(computed.getPropertyValue('padding-right'));
            // calculate the available height inside the parent container
            var parent_height = rect.height
                - parseInt(computed.getPropertyValue('padding-top'))
                - parseInt(computed.getPropertyValue('padding-bottom'));

            var aspect_ratio = width / height;
            var new_width = parent_width;
            var new_height = parent_width / aspect_ratio;

            if (new_width > parent_width) {
                new_width = parent_width;
                new_height = new_width / aspect_ratio;
            }

            if (responsive !== '1' && (new_width > width || new_height > height)) {
                new_width = width;
                new_height = height;
            }

            d.style.height = new_height + 'px';
            d.style.maxHeight = new_height + 'px';
            d.style.width = new_width + 'px';
            d.style.maxWidth = new_width + 'px';
        };

        // call the resizer function multiple times to make sure the div is resized correctly, this is a workaround for
        // the issue that the div is not resized correctly when the page is loaded since ILIAS loads quite slow
        resizer();
        setTimeout(resizer, 100);
        setTimeout(resizer, 300);
        setTimeout(resizer, 500);
        setTimeout(resizer, 700);
        setTimeout(resizer, 1000);

        if (responsive === '1') {
            // if the responsive attribute is set to true, register a resize event listener and resize the div to fill
            // the parent div but keep the aspect ratio and max width and height
            window.addEventListener('resize', resizer);
        }


        let event = new Event(content_id, {
            bubbles: true,
            cancelable: false
        });

        let content = d.getElementsByClassName('sr-external-page-loadable')[0];
        if (!content) {
            continue;
        }

        if (consent === '0' || consented === '1') {
            content.dispatchEvent(event);
            d.removeChild(d.getElementsByClassName('sr-external-page-content-info')[0]);
            continue;
        }

        // find button element inside the div
        let button = d.getElementsByClassName('sr-external-page-content-loader')[0];
        if (!button) {
            continue;
        }

        button.addEventListener('click', function (e) {
            content.dispatchEvent(event);

            // document.cookie = content_id + "=true";
            // use local storage to store the consent
            localStorage.setItem(content_id, '1');

            d.removeChild(button.parentElement);
        });
    }

});
