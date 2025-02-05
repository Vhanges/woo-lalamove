(self.webpackChunkwoo_lalamove=self.webpackChunkwoo_lalamove||[]).push([[78],{192:(e,r,n)=>{"use strict";n.r(r),n.d(r,{default:()=>o});var a=n(858),t=n.n(a),s=n(818),d=n.n(s)()(t());d.push([e.id,"\n.map-container[data-v-9cffed50] {\n  height: 100%;\n  width: 100%;\n}\n#map[data-v-9cffed50] {\n  height: 100%;\n}\n","",{version:3,sources:["webpack://./assets/js/vue/components/PlaceOrder/PlaceOrderMap.vue"],names:[],mappings:";AAgDA;EACE,YAAY;EACZ,WAAW;AACb;AACA;EACE,YAAY;AACd",sourcesContent:['<template>\r\n  <div class="map-container">\r\n    <div id="map" ref="mapContainer"></div>\r\n  </div>\r\n</template>\r\n\r\n<script>\r\nimport { ref, onMounted } from "vue";\r\nimport L from "leaflet";\r\nimport "leaflet/dist/leaflet.css";\r\n\r\nexport default {\r\n  name: "PlaceOrderMap",\r\n  setup() {\r\n    const map = ref(null);\r\n    const mapContainer = ref(null);\r\n\r\n    const initMap = () => {\r\n      if (!mapContainer.value) return;\r\n\r\n      map.value = L.map(mapContainer.value).setView([14.5995, 120.9842], 12); // Manila\r\n\r\n      // Add OpenStreetMap tile layer\r\n      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {\r\n        attribution:\r\n          \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\',\r\n      }).addTo(map.value);\r\n\r\n      // Add a marker\r\n      L.marker([14.5995, 120.9842])\r\n        .addTo(map.value)\r\n        .bindPopup("This is Manila!")\r\n        .openPopup();\r\n    };\r\n\r\n    // Run initMap() after the component is mounted\r\n    onMounted(() => {\r\n      initMap();\r\n    });\r\n\r\n    return {\r\n      mapContainer,\r\n    };\r\n  },\r\n};\r\n<\/script>\r\n\r\n<style scoped>\r\n.map-container {\r\n  height: 100%;\r\n  width: 100%;\r\n}\r\n#map {\r\n  height: 100%;\r\n}\r\n</style>\r\n'],sourceRoot:""}]);const o=d},191:(e,r,n)=>{"use strict";n.r(r),n.d(r,{default:()=>o});var a=n(858),t=n.n(a),s=n(818),d=n.n(s)()(t());d.push([e.id,"\n.wrapper[data-v-0e8d4bdc]{\n  flex: 1;\n  display: grid;\n  grid-template-columns: 40% 60%;\n  grid-template-rows: 1fr;\n  grid-template-areas: 'drawer map';\n  height: 100%;\n  width: 100%;\n  margin: 0;\n}\n","",{version:3,sources:["webpack://./assets/js/vue/views/PlaceOrder.vue"],names:[],mappings:";AAsBA;EACE,OAAO;EACP,aAAa;EACb,8BAA8B;EAC9B,uBAAuB;EACvB,iCAAiC;EACjC,YAAY;EACZ,WAAW;EACX,SAAS;AACX",sourcesContent:["<template>\r\n  <div class=\"wrapper\">\r\n    <StepOne />\r\n    <PlaceOrderMap />\r\n  </div>\r\n</template>\r\n\r\n<script>\r\nimport StepOne from '../components/PlaceOrder/Drawers/StepOne.vue';\r\nimport PlaceOrderMap from '../components/PlaceOrder/PlaceOrderMap.vue';\r\n\r\n\r\nexport default {\r\n  name: 'PlaceOrder',\r\n  components: {\r\n    PlaceOrderMap,\r\n    StepOne\r\n  },\r\n};\r\n<\/script>\r\n\r\n<style scoped>\r\n.wrapper{\r\n  flex: 1;\r\n  display: grid;\r\n  grid-template-columns: 40% 60%;\r\n  grid-template-rows: 1fr;\r\n  grid-template-areas: 'drawer map';\r\n  height: 100%;\r\n  width: 100%;\r\n  margin: 0;\r\n}\r\n</style>"],sourceRoot:""}]);const o=d},358:(e,r,n)=>{"use strict";n.r(r),n.d(r,{default:()=>o});var a=n(858),t=n.n(a),s=n(818),d=n.n(s)()(t());d.push([e.id,".wrapper[data-v-54e5c4f2]{display:flex !important;flex-direction:column;justify-content:start;align-items:center;box-sizing:border-box;padding:5%;height:100%;width:100%}.address-wrapper[data-v-54e5c4f2]{height:30%;width:100%;border:1px solid #05b32b;border-radius:5px}.text-container[data-v-54e5c4f2]{display:flex;align-items:center;margin-bottom:10px}.drag-handle[data-v-54e5c4f2]{cursor:grab;margin-right:10px}.text-box[data-v-54e5c4f2]{width:100%;padding:10px;border:1px solid #05b32b;border-radius:5px}","",{version:3,sources:["webpack://./assets/js/vue/components/PlaceOrder/Drawers/StepOne.vue"],names:[],mappings:"AAGA,0BACE,uBAAA,CACA,qBAAA,CACA,qBAAA,CACA,kBAAA,CACA,qBAAA,CACA,UAAA,CACA,WAAA,CACA,UAAA,CAEF,kCACE,UAAA,CACA,UAAA,CACA,wBAAA,CACA,iBAAA,CAEF,iCACE,YAAA,CACA,kBAAA,CACA,kBAAA,CAEF,8BACE,WAAA,CACA,iBAAA,CAEF,2BACE,UAAA,CACA,YAAA,CACA,wBAAA,CACA,iBAAA",sourcesContent:["\r\n@use '@/css/scss/_variables.scss' as *;\r\n\r\n.wrapper {\r\n  display: flex !important;\r\n  flex-direction: column;\r\n  justify-content: start;\r\n  align-items: center;\r\n  box-sizing: border-box;\r\n  padding: 5%;\r\n  height: 100%;\r\n  width: 100%;\r\n}\r\n.address-wrapper {\r\n  height: 30%;\r\n  width: 100%;\r\n  border: 1px solid $txt-success;\r\n  border-radius: 5px;\r\n}\r\n.text-container {\r\n  display: flex;\r\n  align-items: center;\r\n  margin-bottom: 10px;\r\n}\r\n.drag-handle {\r\n  cursor: grab;\r\n  margin-right: 10px;\r\n}\r\n.text-box {\r\n  width: 100%;\r\n  padding: 10px;\r\n  border: 1px solid $txt-success;\r\n  border-radius: 5px;\r\n}\r\n"],sourceRoot:""}]);const o=d},78:(e,r,n)=>{"use strict";n.r(r),n.d(r,{default:()=>h});var a=n(955);const t={class:"wrapper"};const s={class:"wrapper"},d={class:"address-wrapper"},o={action:""},p=["id","placeholder","onUpdate:modelValue"];var i=n(174);const l=(0,a.pM)({name:"StepOne",components:{Draggable:i.H},setup:()=>({addresses:(0,a.KR)([{id:"pickup",placeholder:"Add Pick up address",value:""},{id:"drop-off",placeholder:"Add Drop off address",value:""}])})});n(197);var A=n(406);const c=(0,A.A)(l,[["render",function(e,r,n,t,i,l){const A=(0,a.g2)("Draggable");return(0,a.uX)(),(0,a.CE)("div",s,[(0,a.Lk)("div",d,[(0,a.Lk)("form",o,[(0,a.bF)(A,{modelValue:e.addresses,"onUpdate:modelValue":r[0]||(r[0]=r=>e.addresses=r),class:"list-group","item-key":"id",options:{handle:".drag-handle"}},{default:(0,a.k6)((()=>[((0,a.uX)(!0),(0,a.CE)(a.FK,null,(0,a.pI)(e.addresses,(e=>((0,a.uX)(),(0,a.CE)("div",{key:e.id,class:"text-container"},[r[1]||(r[1]=(0,a.Lk)("span",{class:"drag-handle"},"☰",-1)),(0,a.Q3)(" Drag handle icon "),(0,a.bo)((0,a.Lk)("input",{type:"text",id:e.id,class:"text-box",placeholder:e.placeholder,"onUpdate:modelValue":r=>e.value=r},null,8,p),[[a.Jo,e.value]])])))),128))])),_:1},8,["modelValue"])])])])}],["__scopeId","data-v-54e5c4f2"]]),u={class:"map-container"},m={id:"map",ref:"mapContainer"};var C=n(777),g=n.n(C);n(916);const f={name:"PlaceOrderMap",setup(){const e=(0,a.KR)(null),r=(0,a.KR)(null);return(0,a.sV)((()=>{r.value&&(e.value=g().map(r.value).setView([14.5995,120.9842],12),g().tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'}).addTo(e.value),g().marker([14.5995,120.9842]).addTo(e.value).bindPopup("This is Manila!").openPopup())})),{mapContainer:r}}};n(104);const v={name:"PlaceOrder",components:{PlaceOrderMap:(0,A.A)(f,[["render",function(e,r,n,t,s,d){return(0,a.uX)(),(0,a.CE)("div",u,[(0,a.Lk)("div",m,null,512)])}],["__scopeId","data-v-9cffed50"]]),StepOne:c}};n(570);const h=(0,A.A)(v,[["render",function(e,r,n,s,d,o){const p=(0,a.g2)("StepOne"),i=(0,a.g2)("PlaceOrderMap");return(0,a.uX)(),(0,a.CE)("div",t,[(0,a.bF)(p),(0,a.bF)(i)])}],["__scopeId","data-v-0e8d4bdc"]])},104:(e,r,n)=>{var a=n(192);a.__esModule&&(a=a.default),"string"==typeof a&&(a=[[e.id,a,""]]),a.locals&&(e.exports=a.locals);(0,n(997).A)("2fa6e82e",a,!1,{})},570:(e,r,n)=>{var a=n(191);a.__esModule&&(a=a.default),"string"==typeof a&&(a=[[e.id,a,""]]),a.locals&&(e.exports=a.locals);(0,n(997).A)("b1ac0a32",a,!1,{})},197:(e,r,n)=>{var a=n(358);a.__esModule&&(a=a.default),"string"==typeof a&&(a=[[e.id,a,""]]),a.locals&&(e.exports=a.locals);(0,n(997).A)("06bc4817",a,!1,{})}}]);
//# sourceMappingURL=78.bundle.js.map