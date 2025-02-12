/*! For license information please see assets_js_vue_views_Records_vue.bundle.js.LICENSE.txt */
(self.webpackChunkwoo_lalamove=self.webpackChunkwoo_lalamove||[]).push([["assets_js_vue_views_Records_vue"],{"./node_modules/sortablejs-vue3/dist/sortablejs-vue3.es.js":(e,s,t)=>{"use strict";t.r(s),t.d(s,{Sortable:()=>r});var o=t("./node_modules/vue/dist/vue.esm-bundler.js"),d=t("./node_modules/sortablejs/modular/sortable.esm.js");const r=(0,o.defineComponent)({__name:"Sortable",props:{options:{type:Object,default:null,required:!1},list:{type:[Array,Object],default:[],required:!0},itemKey:{type:[String,Function],default:"",required:!0},tag:{type:String,default:"div",required:!1}},emits:["choose","unchoose","start","end","add","update","sort","remove","filter","move","clone","change"],setup(e,s){let{expose:t,emit:r}=s;const l=e,u=r,n=(0,o.useAttrs)(),a=(0,o.ref)(!1),i=(0,o.ref)(null),c=(0,o.ref)(null),v=(0,o.computed)((()=>"string"==typeof l.itemKey?e=>e[l.itemKey]:l.itemKey));return t({containerRef:i,sortable:c,isDragging:a}),(0,o.watch)(i,(e=>{e&&(c.value=new d.default(e,{...l.options,onChoose:e=>u("choose",e),onUnchoose:e=>u("unchoose",e),onStart:e=>{a.value=!0,u("start",e)},onEnd:e=>{setTimeout((()=>{a.value=!1,u("end",e)}))},onAdd:e=>u("add",e),onUpdate:e=>u("update",e),onSort:e=>u("sort",e),onRemove:e=>u("remove",e),onFilter:e=>u("filter",e),onMove:(e,s)=>"onMoveCapture"in n?n.onMoveCapture(e,s):u("move",e,s),onClone:e=>u("clone",e),onChange:e=>u("change",e)}))})),(0,o.watch)((()=>l.options),(e=>{if(e&&c?.value)for(const s in e)c.value.option(s,e[s])})),(0,o.onUnmounted)((()=>{c.value&&(c.value.destroy(),i.value=null,c.value=null)})),(s,t)=>((0,o.openBlock)(),(0,o.createBlock)((0,o.resolveDynamicComponent)(s.$props.tag),{ref_key:"containerRef",ref:i,class:(0,o.normalizeClass)(s.$props.class)},{default:(0,o.withCtx)((()=>[(0,o.renderSlot)(s.$slots,"header"),((0,o.openBlock)(!0),(0,o.createElementBlock)(o.Fragment,null,(0,o.renderList)(e.list,((e,t)=>(0,o.renderSlot)(s.$slots,"item",{key:v.value(e),element:e,index:t}))),128)),(0,o.renderSlot)(s.$slots,"footer")])),_:3},8,["class"]))}})},"./node_modules/babel-loader/lib/index.js??clonedRuleSet-2.use!./node_modules/vue-loader/dist/index.js??ruleSet[0]!./assets/js/vue/views/Records.vue?vue&type=script&setup=true&lang=js":(e,s,t)=>{"use strict";t.r(s),t.d(s,{default:()=>r});var o=t("./node_modules/vue/dist/vue.esm-bundler.js"),d=t("./node_modules/sortablejs-vue3/dist/sortablejs-vue3.es.js");const r={__name:"Records",setup(e,s){let{expose:t}=s;t();const r={records:(0,o.ref)([{id:1,name:"Record 1"},{id:2,name:"Record 2"},{id:3,name:"Record 3"}]),ref:o.ref,get Sortable(){return d.Sortable}};return Object.defineProperty(r,"__isScriptSetup",{enumerable:!1,value:!0}),r}}},"./node_modules/babel-loader/lib/index.js??clonedRuleSet-2.use!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0]!./assets/js/vue/views/Records.vue?vue&type=template&id=2bbd5940&scoped=true":(e,s,t)=>{"use strict";t.r(s),t.d(s,{render:()=>d});var o=t("./node_modules/vue/dist/vue.esm-bundler.js");function d(e,s,t,d,r,l){return(0,o.openBlock)(),(0,o.createElementBlock)("div",null,[s[0]||(s[0]=(0,o.createElementVNode)("h2",null,"Records",-1)),s[1]||(s[1]=(0,o.createElementVNode)("p",null,"View and manage your shipment records here.",-1)),(0,o.createVNode)(d.Sortable,{list:d.records,"item-key":"id",tag:"ul",options:{animation:300}},{item:(0,o.withCtx)((e=>{let{element:s}=e;return[(0,o.createElementVNode)("li",null,(0,o.toDisplayString)(s.name),1)]})),_:1},8,["list"])])}},"./node_modules/css-loader/dist/cjs.js!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-4.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0]!./assets/js/vue/views/Records.vue?vue&type=style&index=0&id=2bbd5940&scoped=true&lang=css":(e,s,t)=>{"use strict";t.r(s),t.d(s,{default:()=>u});var o=t("./node_modules/css-loader/dist/runtime/sourceMaps.js"),d=t.n(o),r=t("./node_modules/css-loader/dist/runtime/api.js"),l=t.n(r)()(d());l.push([e.id,"\n/* Add your styles here */\n","",{version:3,sources:["webpack://./assets/js/vue/views/Records.vue"],names:[],mappings:";AA6BA,yBAAyB",sourcesContent:["<template>\r\n  <div>\r\n    <h2>Records</h2>\r\n    <p>View and manage your shipment records here.</p>\r\n    <Sortable\r\n      :list=\"records\"\r\n      item-key=\"id\"\r\n      tag=\"ul\"\r\n      :options=\"{ animation: 300 }\"\r\n    >\r\n      <template #item=\"{ element }\">\r\n        <li>{{ element.name }}</li>\r\n      </template>\r\n    </Sortable>\r\n  </div>\r\n</template>\r\n\r\n<script setup>\r\nimport { ref } from 'vue';\r\nimport { Sortable } from 'sortablejs-vue3';\r\n\r\nconst records = ref([\r\n  { id: 1, name: 'Record 1' },\r\n  { id: 2, name: 'Record 2' },\r\n  { id: 3, name: 'Record 3' },\r\n]);\r\n<\/script>\r\n\r\n<style scoped>\r\n/* Add your styles here */\r\n</style>\r\n"],sourceRoot:""}]);const u=l},"./assets/js/vue/views/Records.vue":(e,s,t)=>{"use strict";t.r(s),t.d(s,{default:()=>r});var o=t("./assets/js/vue/views/Records.vue?vue&type=template&id=2bbd5940&scoped=true"),d=t("./assets/js/vue/views/Records.vue?vue&type=script&setup=true&lang=js");t("./assets/js/vue/views/Records.vue?vue&type=style&index=0&id=2bbd5940&scoped=true&lang=css");const r=(0,t("./node_modules/vue-loader/dist/exportHelper.js").default)(d.default,[["render",o.render],["__scopeId","data-v-2bbd5940"],["__file","assets/js/vue/views/Records.vue"]])},"./assets/js/vue/views/Records.vue?vue&type=script&setup=true&lang=js":(e,s,t)=>{"use strict";t.r(s),t.d(s,{default:()=>o.default});var o=t("./node_modules/babel-loader/lib/index.js??clonedRuleSet-2.use!./node_modules/vue-loader/dist/index.js??ruleSet[0]!./assets/js/vue/views/Records.vue?vue&type=script&setup=true&lang=js")},"./assets/js/vue/views/Records.vue?vue&type=template&id=2bbd5940&scoped=true":(e,s,t)=>{"use strict";t.r(s),t.d(s,{render:()=>o.render});var o=t("./node_modules/babel-loader/lib/index.js??clonedRuleSet-2.use!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0]!./assets/js/vue/views/Records.vue?vue&type=template&id=2bbd5940&scoped=true")},"./assets/js/vue/views/Records.vue?vue&type=style&index=0&id=2bbd5940&scoped=true&lang=css":(e,s,t)=>{"use strict";t.r(s);var o=t("./node_modules/vue-style-loader/index.js!./node_modules/css-loader/dist/cjs.js!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-4.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0]!./assets/js/vue/views/Records.vue?vue&type=style&index=0&id=2bbd5940&scoped=true&lang=css"),d={};for(const e in o)"default"!==e&&(d[e]=()=>o[e]);t.d(s,d)},"./node_modules/vue-style-loader/index.js!./node_modules/css-loader/dist/cjs.js!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-4.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0]!./assets/js/vue/views/Records.vue?vue&type=style&index=0&id=2bbd5940&scoped=true&lang=css":(e,s,t)=>{var o=t("./node_modules/css-loader/dist/cjs.js!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-4.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0]!./assets/js/vue/views/Records.vue?vue&type=style&index=0&id=2bbd5940&scoped=true&lang=css");o.__esModule&&(o=o.default),"string"==typeof o&&(o=[[e.id,o,""]]),o.locals&&(e.exports=o.locals);(0,t("./node_modules/vue-style-loader/lib/addStylesClient.js").default)("0e211d29",o,!1,{})}}]);
//# sourceMappingURL=assets_js_vue_views_Records_vue.bundle.js.map