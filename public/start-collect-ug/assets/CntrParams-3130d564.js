import{l as p,m as h,r as f,o as v,b as n,c as y,d as C,e as d,h as a,i as t,k as r,t as c,f as g,C as b}from"./index-05c29c6d.js";const w={class:"card"},T=d("h5",null,"Cntrl Parameter Acct.",-1),S={__name:"CntrParams",setup(N){p();const l=new b;h();const m=f(null),i=()=>{l.genericRequest("get-control-accts","get",!0,{}).then(o=>{o.status&&(m.value=o.data)})};return v(()=>{i()}),(o,x)=>{const u=n("Toast"),s=n("Column"),_=n("DataTable");return y(),C("div",null,[d("div",w,[T,a(u),a(_,{value:m.value,paginator:!0,class:"p-datatable-gridlines",rows:20,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:t(()=>[a(s,{field:"name",header:"Name",style:{"min-width":"10rem"}},{body:t(({data:e})=>[r(c(e.param_name),1)]),_:1}),a(s,{field:"name",header:"Code",style:{"min-width":"10rem"}},{body:t(({data:e})=>[r(c(g(l).commaSeparator(e.param_cd)),1)]),_:1}),a(s,{field:"name",header:"Acct No.",style:{"min-width":"10rem"}},{body:t(({data:e})=>[r(c(e.param_value),1)]),_:1})]),_:1},8,["value"])])])}}};export{S as default};