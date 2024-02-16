(()=>{"use strict";var e={n:t=>{var l=t&&t.__esModule?()=>t.default:()=>t;return e.d(l,{a:l}),l},d:(t,l)=>{for(var r in l)e.o(l,r)&&!e.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:l[r]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.wp.element,l=window.wp.blocks,r=window.wp.components,s=window.wp.blockEditor,o=window.wp.i18n,n=window.wp.serverSideRender;var i=e.n(n);const a=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"llms/course-meta-info","title":"Course Meta Information","category":"llms-blocks","description":"Display all meta information for a course.","textdomain":"lifterlms","attributes":{"course_id":{"type":"integer"},"llms_visibility":{"type":"string"},"llms_visibility_in":{"type":"string"},"llms_visibility_posts":{"type":"string"}},"supports":{"align":["wide","full"]},"editorScript":"file:./index.js"}'),c=window.wp.primitives,u=window.wp.data,p=["course","lesson","llms_quiz"],m=function(e){let t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"name";const l=null==e?void 0:e.replace("llms_",""),r=l.charAt(0).toUpperCase()+l.slice(1);return"name"===t?l:r},d=function(){let e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"course";const{posts:t,currentPostType:l}=(0,u.useSelect)((t=>{var l;return{posts:t("core").getEntityRecords("postType",e),currentPostType:null===(l=t("core/editor"))||void 0===l?void 0:l.getCurrentPostType()}}),[]),r=(m(e),[]);return p.includes(l)||r.push({label:(0,o.__)("Select course","lifterlms"),value:0}),null!=t&&t.length&&t.forEach((e=>{r.push({label:e.title.rendered+" (ID: "+e.id+")",value:e.id})})),p.includes(l)&&r.unshift({label:(0,o.sprintf)(
// Translators: %s = Post type name.
(0,o.__)("Inherit from current %s","lifterlms"),m(l)),value:0}),null!=r&&r.length||r.push({label:(0,o.__)("Loading","lifterlms"),value:0}),r},w=e=>{var l,s;let{attributes:n,setAttributes:i,postType:a="course",attribute:c="course_id"}=e;const u=d(a),p=m(a),w=m(a,"title"),b=(0,o.sprintf)(
// Translators: %s = Post type name.
(0,o.__)("Select the %s to associate with this block.","lifterlms"),p);return(0,t.createElement)(r.PanelRow,null,(0,t.createElement)(r.SelectControl,{label:w,help:b,value:null!==(l=null==n?void 0:n[c])&&void 0!==l?l:null==u||null===(s=u[0])||void 0===s?void 0:s.value,options:u,onChange:e=>{i({[c]:parseInt(e,10)})}}))};(0,l.registerBlockType)(a,{icon:()=>(0,t.createElement)(c.SVG,{className:"llms-block-icon",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 512 512"},(0,t.createElement)(c.Path,{d:"M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"})),edit:e=>{const{attributes:l}=e,n=(0,s.useBlockProps)(),c=d(),u=(0,t.useMemo)((()=>{let e=(0,o.__)("No meta information available for this course. This block will not be displayed.","lifterlms");return!l.course_id&&c.length>0&&(e=(0,o.__)("No course selected. Please choose a Course from the block sidebar panel.","lifterlms")),(0,t.createElement)(i(),{block:a.name,attributes:l,LoadingResponsePlaceholder:()=>(0,t.createElement)(r.Spinner,null),ErrorResponsePlaceholder:()=>(0,t.createElement)("p",{className:"llms-block-error"},(0,o.__)("Error loading content. Please check block settings are valid. This block will not be displayed.","lifterlms")),EmptyResponsePlaceholder:()=>(0,t.createElement)("p",{className:"llms-block-empty"},e)})}),[l]);return(0,t.createElement)(t.Fragment,null,(0,t.createElement)(s.InspectorControls,null,(0,t.createElement)(r.PanelBody,{title:(0,o.__)("Course Meta Info Settings","lifterlms")},(0,t.createElement)(w,e))),(0,t.createElement)("div",n,(0,t.createElement)(r.Disabled,null,u)))}})})();