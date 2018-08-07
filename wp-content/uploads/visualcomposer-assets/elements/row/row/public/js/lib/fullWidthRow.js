(function () {
  if (typeof window.vceResetFullWidthRows !== 'undefined') {
    return
  }

  var fullWidthRows = undefined;
  var headerZone = '[data-vcv-layout-zone="header"]';
  var footerZone = '[data-vcv-layout-zone="footer"]';
  var headerFooterEditor = '.vcv-editor-theme-hf';

  function getRows() {
    fullWidthRows = Array.prototype.slice.call(document.querySelectorAll('[data-vce-full-width="true"]'));
    if (fullWidthRows.length) {
      handleResize();
    }
  }

  function handleResize() {
    if (!fullWidthRows.length) {
      return;
    }
    fullWidthRows.forEach(function (row) {
      var mainBody = document.body;
      var rowHelper = row.parentElement;
      var rowContent = row.querySelector('.vce-row-content');
      var rowCustomContainer = document.querySelector('.vce-full-width-custom-container');

      var elMarginLeft = parseInt(window.getComputedStyle(row, null)['margin-left'], 10);
      var elMarginRight = parseInt(window.getComputedStyle(row, null)['margin-right'], 10);
      var offset, width;

      if (row.closest(headerZone) || row.closest(footerZone) || row.closest(headerFooterEditor)) {
        return;
      }

      if (document.body.contains(rowCustomContainer)) {
        offset = 0 - rowHelper.getBoundingClientRect().left - elMarginLeft + rowCustomContainer.getBoundingClientRect().left;
        width = rowCustomContainer.getBoundingClientRect().width;
      } else {
        offset = 0 - rowHelper.getBoundingClientRect().left - elMarginLeft;
        width = document.documentElement.getBoundingClientRect().width;
      }

      row.style.width = width + 'px';
      if (mainBody.classList.contains('rtl')) {
        row.style.right = offset + 'px';
      } else {
        row.style.left = offset + 'px';
      }

      if (!row.getAttribute('data-vce-stretch-content')) {
        var padding = -1 * offset;
        if (padding < 0) {
          padding = 0;
        }
        var paddingRight = width - padding - rowHelper.getBoundingClientRect().width + elMarginLeft + elMarginRight;
        if (paddingRight < 0) {
          paddingRight = 0;
        }
        rowContent.style['padding-left'] = padding + 'px';
        rowContent.style['padding-right'] = paddingRight + 'px';
      } else {
        rowContent.style['padding-left'] = '';
        rowContent.style['padding-right'] = '';
      }
    });
  }

  getRows();
  window.addEventListener('resize', handleResize);
  window.vceResetFullWidthRows = getRows;

  window.vcv.on('ready', function () {
    window.vceResetFullWidthRows && window.vceResetFullWidthRows()
  })
})();