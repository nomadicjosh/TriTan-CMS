var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
elems.forEach(function (html) {
    var switchery = new Switchery(html, {size: 'small', secondaryColor: '#ee0000'});
});