import{i as g,r as f,j as C,o as v,b as i,c as k,d as R,f as l,h as o,k as s,l as a,t as r,C as B}from"./index-00bbecd2.js";const D={class:"grid"},S={class:"col-12"},T={class:"card"},N=l("h5",null,"Roles",-1),E={__name:"ViewRoles",setup(x){const m=g(),d=f(null),c=new B,_=C(),p=t=>{alert(JSON.stringify(t))},h=()=>{c.genericRequest("get-roles","get",!0,{}).then(t=>{t.status?d.value=t.data:c.showError(m,t.message)})},y=t=>{_.push("/role-permission/"+(t==null?void 0:t.id))};return v(()=>{h()}),(t,V)=>{const n=i("Column"),u=i("Button"),b=i("DataTable");return k(),R("div",D,[l("div",S,[l("div",T,[N,o(b,{value:d.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:s(()=>[o(n,{field:"name",header:"Name",style:{"min-width":"10rem"}},{body:s(({data:e})=>[a(r(e.name),1)]),_:1}),o(n,{header:"Type",style:{"min-width":"10rem"}},{body:s(({data:e})=>[a(r(e.type),1)]),_:1}),o(n,{header:"Description",style:{"min-width":"12rem"}},{body:s(({data:e})=>[a(r(e.description),1)]),_:1}),o(n,{header:"Status",style:{"min-width":"10rem"}},{body:s(({data:e})=>[a(r(e.status),1)]),_:1}),o(n,{header:"Created By",style:{"min-width":"10rem"}},{body:s(({data:e})=>[a(r(e.created_by),1)]),_:1}),o(n,{header:"Date",style:{"min-width":"10rem"}},{body:s(({data:e})=>[a(r(e.created_at),1)]),_:1}),o(n,{headerStyle:"min-width:10rem;"},{body:s(({data:e})=>[o(u,{icon:"pi pi-pencil",class:"p-button-rounded p-button-outlined-success p-button-outlined mr-2",onClick:w=>p(e)},null,8,["onClick"]),o(u,{icon:"pi pi-arrow-right-arrow-left",class:"p-button-rounded p-button-outlined mr-2 mt-2",onClick:w=>y(e)},null,8,["onClick"])]),_:1})]),_:1},8,["value"])])])])}}};export{E as default};