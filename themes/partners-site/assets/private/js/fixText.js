function fixConjuctions(el) {
    let replaceString = el.innerHTML;
    replaceString = replaceString.replace(/(\s)(?!zł)([^\s<>=]{1,2})[\s]/g, "$1$2&nbsp;"); //  replace space to nbsp; after conjuctions
    el.innerHTML = replaceString;
}

function fixPrices(el) {
    let replaceString = el.innerHTML;
    replaceString = replaceString.replace(/([0-9])(?:[\s])([0-9]|zł|%|\*|-|\+|=|<|>|\/|\\)/g, "$1&nbsp;$2"); //  replace space to nbsp for number and price
    el.innerHTML = replaceString;
}

document.addEventListener('DOMContentLoaded', () => {
    Array.prototype.forEach.call(document.querySelectorAll('h1, h2, h3, h4, h5'), function (el, i) {
        fixConjuctions(el)
    });

    Array.prototype.forEach.call(document.querySelectorAll('p'), function (el, i) {
        fixConjuctions(el)
        fixPrices(el)
    });
})