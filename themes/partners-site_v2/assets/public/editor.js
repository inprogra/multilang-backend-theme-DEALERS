/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/private/js/editor.js":
/*!*************************************!*\
  !*** ./assets/private/js/editor.js ***!
  \*************************************/
/***/ (() => {

var unregisterCoreBlocks = function unregisterCoreBlocks() {
  var types = wp.blocks.getBlockTypes();
  var core_blocks = types.filter(function (type) {
    return type.name.startsWith('core/') || type.name.startsWith('core-embed/');
  });
  var block_names = core_blocks.map(function (type) {
    return type.name;
  });
  block_names.forEach(function (block) {
    wp.blocks.unregisterBlockType(block);
  });
};

var unregisterCustomBlocks = function unregisterCustomBlocks() {
  var blocks = ['yoast/faq-block', 'yoast/how-to-block', 'yoast-seo/breadcrumbs', 'filebird/block-filebird-gallery'];
  blocks.forEach(function (block) {
    wp.blocks.unregisterBlockType(block);
  });
};

wp.domReady(function () {
  unregisterCoreBlocks();
  unregisterCustomBlocks();
});

/***/ }),

/***/ "./assets/private/css/editor.scss":
/*!****************************************!*\
  !*** ./assets/private/css/editor.scss ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		if(__webpack_module_cache__[moduleId]) {
/******/ 			return __webpack_module_cache__[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	// startup
/******/ 	// Load entry module
/******/ 	__webpack_require__("./assets/private/js/editor.js");
/******/ 	__webpack_require__("./assets/private/css/editor.scss");
/******/ 	// This entry module used 'exports' so it can't be inlined
/******/ })()
;
//# sourceMappingURL=editor.js.map