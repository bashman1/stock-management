import{D as h,l as y,r as l,u as k,m as S,B as V,b as n,c as I,d as T,e,h as t,F as B,_ as C,E as D,G as E,C as L}from"./index-c05e2d26.js";const r=o=>(D("data-v-00b3b7d6"),o=o(),E(),o),P={class:"surface-ground flex align-items-center justify-content-center min-h-screen min-w-screen overflow-hidden"},j={class:"flex flex-column align-items-center justify-content-center"},F=["src"],U={style:{"border-radius":"56px",padding:"0.3rem",background:"linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)"}},N={class:"w-full surface-card py-8 px-5 sm:px-8",style:{"border-radius":"53px"}},R=r(()=>e("div",{class:"text-center mb-5"},[e("div",{class:"text-900 text-3xl font-medium mb-3"}),e("span",{class:"text-600 font-medium"},"Sign in to continue")],-1)),q=r(()=>e("label",{for:"email1",class:"block text-900 text-xl font-medium mb-2"},"Email",-1)),G=r(()=>e("label",{for:"password1",class:"block text-900 font-medium text-xl mb-2"},"Password",-1)),M=r(()=>e("div",{class:"flex align-items-center justify-content-between mb-5 gap-5"},[e("div",{class:"flex align-items-center"}),e("a",{class:"font-medium no-underline ml-2 text-right cursor-pointer",style:{color:"var(--primary-color)"}},"Forgot password?")],-1)),$={__name:"Login",setup(o){const p=y();l([]);const c=new L;k();const _=S(),d=l(""),i=l("");l(!1);const g=V(()=>"demo/images/Smart-Collect-logo-black-removebg.png"),f=()=>{let a={email:d.value,password:i.value};c.genericRequest("user-login","post",!1,a).then(s=>{if(s.status){const m={token:s.data.token.accessToken,userData:s.data.user_data};x(m)}else c.showError(p,s.message)})},x=async a=>{await c.setStorage(a),_.push("/admin")};return(a,s)=>{const m=n("Toast"),b=n("InputText"),v=n("Password"),w=n("Button");return I(),T(B,null,[e("div",P,[e("div",j,[e("img",{src:g.value,alt:"Sakai logo",class:"mb-5 w-30rem flex-shrink-0"},null,8,F),e("div",U,[e("div",N,[t(m),R,e("div",null,[q,t(b,{id:"email1",type:"text",placeholder:"Email address",class:"w-full md:w-30rem mb-5",style:{padding:"1rem"},modelValue:d.value,"onUpdate:modelValue":s[0]||(s[0]=u=>d.value=u)},null,8,["modelValue"]),G,t(v,{id:"password1",modelValue:i.value,"onUpdate:modelValue":s[1]||(s[1]=u=>i.value=u),placeholder:"Password",feedback:!1,toggleMask:!0,class:"w-full mb-3",inputClass:"w-full",inputStyle:"padding:1rem"},null,8,["modelValue"]),M,t(w,{onClick:f,label:"Sign In",class:"w-full p-3 text-xl"})])])])])]),t(C,{simple:""})],64)}}},A=h($,[["__scopeId","data-v-00b3b7d6"]]);export{A as default};