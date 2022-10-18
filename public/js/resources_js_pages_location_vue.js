"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_pages_location_vue"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/pages/location.vue?vue&type=script&lang=js&":
/*!**********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/pages/location.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _router__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../router */ "./resources/js/router.js");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  name: "location",
  data: function data() {
    return {
      labelCol: {
        span: 4
      },
      wrapperCol: {
        span: 14
      },
      form: {},
      status: 0,
      message: ''
    };
  },
  methods: {
    home: function home() {
      window.location.href = '/';
    },
    onSubmit: function onSubmit() {
      var _this = this;

      axios({
        method: 'post',
        url: "/api/auth/check_password",
        data: this.form
      }).then(function (response) {
        if (response.data.code != 0) {
          return _this.$message.error(response.data.message);
        }

        window.location.href = response.data.result.link;
      });
    },
    getCode: function getCode() {
      var _this2 = this;

      axios({
        method: 'get',
        url: "/api/auth/get_code/" + this.$route.params.code
      }).then(function (response) {
        if (response.data.code == 0) {
          if (response.data.result.status == 0) {
            _this2.status = response.data.result.status;
            window.location.href = response.data.result.link;
          }

          _this2.status = response.data.result.status;
          _this2.message = response.data.result.message;
        } else {
          _router__WEBPACK_IMPORTED_MODULE_0__["default"].push('/pages/404');
        }
      });
    }
  },
  mounted: function mounted() {
    this.form.code = this.$route.params.code;
    this.getCode();
  }
});

/***/ }),

/***/ "./resources/js/pages/location.vue":
/*!*****************************************!*\
  !*** ./resources/js/pages/location.vue ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _location_vue_vue_type_template_id_e26118c4_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./location.vue?vue&type=template&id=e26118c4&scoped=true& */ "./resources/js/pages/location.vue?vue&type=template&id=e26118c4&scoped=true&");
/* harmony import */ var _location_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./location.vue?vue&type=script&lang=js& */ "./resources/js/pages/location.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _location_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _location_vue_vue_type_template_id_e26118c4_scoped_true___WEBPACK_IMPORTED_MODULE_0__.render,
  _location_vue_vue_type_template_id_e26118c4_scoped_true___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  "e26118c4",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/pages/location.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/pages/location.vue?vue&type=script&lang=js&":
/*!******************************************************************!*\
  !*** ./resources/js/pages/location.vue?vue&type=script&lang=js& ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_location_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./location.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/pages/location.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_location_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/pages/location.vue?vue&type=template&id=e26118c4&scoped=true&":
/*!************************************************************************************!*\
  !*** ./resources/js/pages/location.vue?vue&type=template&id=e26118c4&scoped=true& ***!
  \************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_location_vue_vue_type_template_id_e26118c4_scoped_true___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_location_vue_vue_type_template_id_e26118c4_scoped_true___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_location_vue_vue_type_template_id_e26118c4_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./location.vue?vue&type=template&id=e26118c4&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/pages/location.vue?vue&type=template&id=e26118c4&scoped=true&");


/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/pages/location.vue?vue&type=template&id=e26118c4&scoped=true&":
/*!***************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/pages/location.vue?vue&type=template&id=e26118c4&scoped=true& ***!
  \***************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function () {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", [
    _vm.status == 0
      ? _c(
          "div",
          [
            _c(
              "a-row",
              { attrs: { type: "flex", justify: "center" } },
              [
                _c(
                  "a-col",
                  { attrs: { xs: 24, sm: 24, md: 14, lg: 14, xl: 14 } },
                  [
                    _c(
                      "div",
                      { staticStyle: { "padding-top": "100px" } },
                      [
                        _c("a-spin", { attrs: { tip: "Loading..." } }, [
                          _vm._v("\n            正在跳转。。。\n          "),
                        ]),
                      ],
                      1
                    ),
                  ]
                ),
              ],
              1
            ),
          ],
          1
        )
      : _vm._e(),
    _vm._v(" "),
    _vm.status == 404 || _vm.status == 500
      ? _c(
          "div",
          [
            _c("a-result", {
              attrs: {
                status: _vm.status,
                title: _vm.status,
                "sub-title": _vm.message,
              },
              scopedSlots: _vm._u(
                [
                  {
                    key: "extra",
                    fn: function () {
                      return [
                        _c(
                          "a-button",
                          {
                            attrs: { type: "primary" },
                            on: {
                              click: function ($event) {
                                return _vm.home()
                              },
                            },
                          },
                          [_vm._v("\n          回到主页\n        ")]
                        ),
                      ]
                    },
                    proxy: true,
                  },
                ],
                null,
                false,
                3511385566
              ),
            }),
          ],
          1
        )
      : _vm._e(),
    _vm._v(" "),
    _vm.status == 401
      ? _c(
          "div",
          [
            _c(
              "a-row",
              { attrs: { type: "flex", justify: "center" } },
              [
                _c(
                  "a-col",
                  { attrs: { xs: 24, sm: 24, md: 14, lg: 14, xl: 14 } },
                  [
                    _c(
                      "a-card",
                      { attrs: { title: "请输入密码" } },
                      [
                        _c(
                          "a-form-model",
                          {
                            attrs: {
                              model: _vm.form,
                              "label-col": _vm.labelCol,
                              "wrapper-col": _vm.wrapperCol,
                            },
                          },
                          [
                            _c(
                              "a-form-model-item",
                              { attrs: { label: "输入密码" } },
                              [
                                _c("a-input", {
                                  model: {
                                    value: _vm.form.password,
                                    callback: function ($$v) {
                                      _vm.$set(_vm.form, "password", $$v)
                                    },
                                    expression: "form.password",
                                  },
                                }),
                              ],
                              1
                            ),
                            _vm._v(" "),
                            _c(
                              "a-form-model-item",
                              {
                                attrs: {
                                  "wrapper-col": { span: 14, offset: 4 },
                                },
                              },
                              [
                                _c(
                                  "a-button",
                                  {
                                    attrs: { type: "primary" },
                                    on: { click: _vm.onSubmit },
                                  },
                                  [
                                    _vm._v(
                                      "\n                确定\n              "
                                    ),
                                  ]
                                ),
                              ],
                              1
                            ),
                          ],
                          1
                        ),
                      ],
                      1
                    ),
                  ],
                  1
                ),
              ],
              1
            ),
          ],
          1
        )
      : _vm._e(),
  ])
}
var staticRenderFns = []
render._withStripped = true



/***/ })

}]);