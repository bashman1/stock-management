import{i as b,r as w,o as v,b as i,c as f,d as g,f as l,h as t,k as s,l as a,t as r,C}from"./index-31798102.js";const k={class:"grid"},B={class:"col-12"},D={class:"card"},N=l("h5",null,"Users",-1),x={__name:"ViewUsers",setup(S){const u=b(),d=w(null),c=new C,_=n=>{alert(JSON.stringify(n))},h=()=>{c.genericRequest("get-users","get",!0,{}).then(n=>{n.status?d.value=n.data:c.showError(u,n.message)})};return v(()=>{h()}),(n,T)=>{const o=i("Column"),m=i("Button"),p=i("DataTable");return f(),g("div",k,[l("div",B,[l("div",D,[N,t(p,{value:d.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:s(()=>[t(o,{field:"name",header:"Name",style:{"min-width":"10rem"}},{body:s(({data:e})=>[a(r(e.first_name+" "+e.last_name+" "+e.other_name),1)]),_:1}),t(o,{header:"Gender",style:{"min-width":"10rem"}},{body:s(({data:e})=>[a(r(e.gender),1)]),_:1}),t(o,{header:"Phone Number",style:{"min-width":"12rem"}},{body:s(({data:e})=>[a(r(e.phone_number),1)]),_:1}),t(o,{header:"Role",style:{"min-width":"12rem"}},{body:s(({data:e})=>[a(r(e.role),1)]),_:1}),t(o,{header:"Status",style:{"min-width":"10rem"}},{body:s(({data:e})=>[a(r(e.status),1)]),_:1}),t(o,{header:"Created By",style:{"min-width":"10rem"}},{body:s(({data:e})=>[a(r(e.created_by),1)]),_:1}),t(o,{header:"Date",style:{"min-width":"10rem"}},{body:s(({data:e})=>[a(r(e.created_at),1)]),_:1}),t(o,{headerStyle:"min-width:10rem;"},{body:s(({data:e})=>[t(m,{icon:"pi pi-pencil",class:"p-button-rounded p-button-success mr-2",onClick:y=>_(e)},null,8,["onClick"]),t(m,{icon:"pi pi-trash",class:"p-button-rounded p-button-warning mt-2",onClick:y=>n.confirmDeleteProduct(e)},null,8,["onClick"])]),_:1})]),_:1},8,["value"])])])])}}};export{x as default};