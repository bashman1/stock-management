import{i as N,j as S,r as i,o as U,b as m,c as B,d as T,f as l,h as r,n as w,g as k,C as E}from"./index-31798102.js";const q={class:"card"},A=l("h5",null,"Create Role",-1),L={class:"grid p-fluid mt-3"},M={class:"field col-12 md:col-4"},$={class:"p-float-label"},j=l("label",{for:"firstName"},"Name",-1),z={class:"field col-12 md:col-4"},O={class:"p-float-label"},P=l("label",{for:"type"},"Type",-1),G={key:0,class:"field col-12 md:col-4"},H={class:"p-float-label"},J=l("label",{for:"institution"},"Institution",-1),K={class:"field col-12 md:col-12"},Q={class:"p-float-label"},W=l("label",{for:"description"},"Description",-1),X=l("div",{class:"field col-12 md:col-8"},null,-1),Y={class:"field col-12 md:col-4"},le={__name:"CreateRole",setup(Z){const f=N(),o=new E,y=S(),n=i(null),t=i(null),b=i(null),s=i(null),v=i(null),u=i({}),C=i([{label:"Admin Role",value:"Admin"},{label:"Institution Role",value:"Institution"}]),R=()=>{var p,_;if(u.value.name=o.validateFormField(n.value),u.value.type=o.validateFormField(t.value),o.validateRequiredFields(u.value)){o.showError(f,"Please fill in the missing field");return}let e={name:n==null?void 0:n.value,type:(p=t==null?void 0:t.value)==null?void 0:p.value,institution_id:s==null?void 0:s.value,description:v==null?void 0:v.value,institution_id:s.value!=null?s==null?void 0:s.value.id:null,role_type:(_=t==null?void 0:t.value)==null?void 0:_.value,status:"Active"};o.genericRequest("crate-role","post",!0,e).then(c=>{c.status?(o.showSuccess(f,c.message),o.redirect(y,"/view-roles")):o.showError(f,c.message)})},V=(d,e)=>{u.value[e]=o.validateFormField(d)},x=()=>{o.genericRequest("get-institutions","get",!0,{}).then(d=>{d.status&&(b.value=d.data)})};return U(()=>{x()}),(d,e)=>{var h,g,I;const p=m("Toast"),_=m("InputText"),c=m("Dropdown"),F=m("Textarea"),D=m("Button");return B(),T("div",null,[l("div",q,[A,l("div",L,[r(p),l("div",M,[l("span",$,[r(_,{type:"text",id:"name",modelValue:n.value,"onUpdate:modelValue":e[0]||(e[0]=a=>n.value=a),onBlur:e[1]||(e[1]=a=>V(n.value,"name")),class:w({"p-invalid":(h=u.value)==null?void 0:h.name})},null,8,["modelValue","class"]),j])]),l("div",z,[l("span",O,[r(c,{id:"type",options:C.value,modelValue:t.value,"onUpdate:modelValue":e[2]||(e[2]=a=>t.value=a),optionLabel:"label",onBlur:e[3]||(e[3]=a=>V(t.value,"type")),class:w({"p-invalid":(g=u.value)==null?void 0:g.type})},null,8,["options","modelValue","class"]),P])]),((I=t.value)==null?void 0:I.value)=="Institution"?(B(),T("div",G,[l("span",H,[r(c,{id:"institution",options:b.value,modelValue:s.value,"onUpdate:modelValue":e[4]||(e[4]=a=>s.value=a),optionLabel:"name"},null,8,["options","modelValue"]),J])])):k("",!0),l("div",K,[l("span",Q,[r(F,{inputId:"description",rows:"3",cols:"30",modelValue:v.value,"onUpdate:modelValue":e[5]||(e[5]=a=>v.value=a)},null,8,["modelValue"]),W])]),X,l("div",Y,[r(D,{onClick:R,label:"SUBMIT",class:"p-button-outlined mr-2 mb-2"})])])])])}}};export{le as default};