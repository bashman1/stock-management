import{l as g,r as u,m as N,p as C,o as T,b as _,c as p,d as h,j as V,F as x,e as S,t as o,f as v,h as r,i as s,k as l,C as A}from"./index-a7b81454.js";const B={className:"card"},L={__name:"glOverView",setup(D){const f=g();u(null);const n=new A;N(),C();const m=u([]),y=()=>{n.genericRequest("get-gl-overview","post",!0,{}).then(a=>{a.status?m.value=a.data:n.showError(f,a.message)})},b=a=>{let c=0;return a.forEach(t=>{c=Number(c)+Number(t.balance)}),c};return T(()=>{y()}),(a,c)=>{const t=_("Column"),w=_("DataTable");return p(!0),h(x,null,V(m.value,(i,k)=>{var d;return p(),h("div",B,[S("h5",null,o(((d=i[0])==null?void 0:d.acct_type)+",    Total "+v(n).commaSeparator(b(i))),1),r(w,{value:i,class:"p-datatable-gridlines",dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:s(()=>[r(t,{field:"name",header:"Acct No.",style:{"min-width":"10rem"}},{body:s(({data:e})=>[l(o(e.acct_no),1)]),_:1}),r(t,{field:"name",header:"Ledger No.",style:{"min-width":"10rem"}},{body:s(({data:e})=>[l(o(e.gl_no),1)]),_:1}),r(t,{field:"name",header:"Name",style:{"min-width":"10rem"}},{body:s(({data:e})=>[l(o(e.description),1)]),_:1}),r(t,{field:"name",header:"Acct Balance",style:{"min-width":"10rem"}},{body:s(({data:e})=>[l(o(v(n).commaSeparator(e.balance)),1)]),_:1})]),_:2},1032,["value"])])}),256)}}};export{L as default};