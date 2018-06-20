require(Prism.js);

var code= document.querySelectorAll('pre code');
    code.forEach(function (elem) {
    let cls = elem.className;
    if (!/language-/i.test(elem.className) && !/lang-/i.test(elem.className)) {
        elem.className = elem.className.replace(cls,'language-'+ cls);
    }
        
    });
    var pre = document.querySelectorAll('pre.pure-highlightjs');
    pre.forEach(function (elem) {
        let reg = /line-numbers/i;
        if (!reg.test(elem.className))
            elem.className += ' line-numbers';
    });

module.export = {
    listLanguages: function () {
        var langs = new Array();
        let i = 0;
        for (language in Prism.languages) {
            if (Object.prototype.toString.call(Prism.languages[language]) !== '[object Function]') {
                langs[i] = language;
                i++;
            }
        };
        return langs;
    }
}