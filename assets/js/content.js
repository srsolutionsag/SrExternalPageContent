// iFrame
document.addEventListener('DOMContentLoaded', () => {
  const iframeData = document.getElementsByClassName('sr-external-page-content-iframe');

  // loop through all elements with class 'sr-external-page-content-iframe'
  for (let i = 0; i < iframeData.length; i++) {
    const d = iframeData[i];
    const url = d.getAttribute('data-url');
    const title = d.getAttribute('data-title');
    const allowfullscreen = d.getAttribute('data-allowfullscreen');
    const contentId = d.getAttribute('data-content-id');

    // add click event listener to the button
    d.addEventListener(contentId, () => {
      // if the button is clicked, create an iframe element
      const iframe = document.createElement('iframe');
      iframe.src = url;
      iframe.title = title;
      iframe.scrolling = 'no';
      iframe.allowfullscreen = allowfullscreen;

      // always fill the iframe size to the parent div
      iframe.style.width = '100%';
      iframe.style.height = '100%';
      iframe.style.border = 'none'; // we put a boarder on the parent div using css

      d.appendChild(iframe);
      // show the element
      d.style.display = 'block';
    });
  }
});

// Wrapper
document.addEventListener('DOMContentLoaded', () => {
  const iframeData = document.getElementsByClassName('sr-external-page-content');

  const resizer = function (d) {
    const dimensions = JSON.parse(d.getAttribute('data-dimensions'));
    console.log(dimensions);

    // properties
    // eslint-disable-next-line max-len
    const mode = dimensions.mode || 1; // modes: 1: FIXED, 2: FIXED_HEIGHT, 3: ASPECT_RATIO , @DimensionMode
    const maxWidth = dimensions.width || null;
    const maxHeight = dimensions.height || null;
    const ratio = dimensions.ratio || maxWidth / maxHeight; // aspect ratio

    // parent container
    const parent = d.parentElement;
    // get the width and height of the parent container
    const rect = parent.getBoundingClientRect();
    // get the padding of the parent container
    const computed = window.getComputedStyle(parent, null);
    // calculate the available width inside the parent container
    const parentWidth = rect.width
            - parseInt(computed.getPropertyValue('padding-left'), 10)
            - parseInt(computed.getPropertyValue('padding-right'), 10);
    // calculate the available height inside the parent container
    const parentHeight = rect.height
            - parseInt(computed.getPropertyValue('padding-top'), 10)
            - parseInt(computed.getPropertyValue('padding-bottom'), 10);

    switch (mode) {
      case 1: // FIXED
        {
          console.log('FIXED');
          console.log(ratio);
          const width = Math.min(parentWidth, maxWidth);
          const height = Math.min((parentWidth / ratio), maxHeight);

          d.style.width = `${width}px`;
          d.style.height = `${height}px`;
          d.style.maxWidth = `${maxWidth}px`;
          d.style.maxHeight = `${maxHeight}px`;
        }
        break;
      case 2: // FIXED_HEIGHT
        {
          console.log('FIXED_HEIGHT');
          d.style.width = `${parentWidth}px`;
          d.style.height = `${maxHeight}px`;
        }
        break;
      case 3: // ASPECT_RATIO
        {
          console.log('ASPECT_RATIO');
          d.style.width = `${parentWidth}px`;
          d.style.height = `${( parentWidth / ratio)}px`;
          if (maxWidth) {
            d.style.maxWidth = `${maxWidth}px`;
            d.style.maxHeight = `${maxWidth / ratio}px`;
          }
        }
        break;
      default:
      {

      }
    }
  };

  // loop through all elements with class 'sr-external-page-content-iframe'
  for (let i = 0; i < iframeData.length; i++) {
    const d = iframeData[i];

    const contentId = d.getAttribute('data-content-id');
    const domain = d.getAttribute('data-domain');
    const thumbnail = d.getAttribute('data-thumbnail');
    const reset = d.getAttribute('data-reset');
    const lastReset = localStorage.getItem('last_reset');
    if (reset > lastReset) {
      localStorage.clear();
      localStorage.setItem('last_reset', reset);
    }
    let mustConsent = d.getAttribute('data-must-consent');
    let consentedId = localStorage.getItem(contentId);
    let consentedDomain = localStorage.getItem(domain);
    if (d.getAttribute('data-consented') === '1' || consentedDomain === '1') {
      consentedId = '1';
    }

    // call the resizer function multiple times to make sure the div is resized correctly, this is a workaround for
    // the issue that the div is not resized correctly when the page is loaded since ILIAS loads quite slow
    resizer(d);
    setTimeout(resizer, 100, d);
    setTimeout(resizer, 300, d);
    setTimeout(resizer, 500, d);
    setTimeout(resizer, 700, d);
    setTimeout(resizer, 1000, d);
    window.addEventListener('resize', () => {
      resizer(d);
    });

    if (thumbnail !== '') {
      d.style.backgroundImage = `url(${thumbnail})`;
      mustConsent = '1';
      consentedId = '0';
      consentedDomain = '0';
    }

    const content = d.getElementsByClassName('sr-external-page-loadable')[0];
    if (!content) {
      continue; // ?
    }

    const event = new Event(contentId, {
      bubbles: true,
      cancelable: false,
    });

    const applyConsent = function () {
      content.dispatchEvent(event);
      d.removeChild(d.getElementsByClassName('sr-external-page-content-info')[0]);
    };

    // if consent if not/no longer needed, we proceed directly
    if (
      mustConsent === '0'
            || consentedDomain === '1'
            || consentedId === '1'
    ) {
      applyConsent();
      continue; // ?
    }
    // find button element inside the div
    const button = d.getElementsByClassName('sr-external-page-content-loader')[0];
    if (!button) {
      continue; // ?
    }

    button.addEventListener('click', (e) => {
      content.dispatchEvent(event);

      // document.cookie = content_id + "=true";
      // use local storage to store the consent
      localStorage.setItem(contentId, '1');
      localStorage.setItem(domain, '1');

      d.removeChild(button.parentElement);
    });
  }
});
