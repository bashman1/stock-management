import{l as S,m as T,r as D,o as P,b as i,z as N,c as B,d as R,e as p,h as t,i as o,k as r,t as s,f as m,A as h,C as x}from"./index-c05e2d26.js";const V={class:"card"},q=p("h5",null,"Products report.",-1),A={class:"my-2"},$={__name:"ProductsReport",setup(I){S();const l=new x,u=T(),_=D(null),b=()=>{let a={status:"Active"};l.genericRequest("get-products-report","post",!0,a).then(d=>{d.status&&(_.value=d.data)})},v=a=>{u.push("/product-inventory-report/"+a.id)},f=a=>{u.push("/products-sales-report/"+a.id)};return P(()=>{b()}),(a,d)=>{const C=i("Toast"),c=i("Button"),g=i("Toolbar"),n=i("Column"),w=i("DataTable"),y=N("tooltip");return B(),R("div",null,[p("div",V,[q,t(C),t(g,{class:"mb-4"},{end:o(()=>[p("div",A,[t(c,{label:"CSV",icon:"pi pi-file-excel",class:"p-button-success mr-2",onClick:a.openNew},null,8,["onClick"]),t(c,{label:"PDF",icon:"pi pi-file-pdf",class:"p-button-danger",onClick:a.confirmDeleteSelected},null,8,["onClick"])])]),_:1}),t(w,{value:_.value,paginator:!0,class:"p-datatable-gridlines",rows:20,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:o(()=>[t(n,{field:"name",header:"Name",style:{"min-width":"10rem"}},{body:o(({data:e})=>[r(s(e.name),1)]),_:1}),t(n,{field:"name",header:"Type",style:{"min-width":"10rem"}},{body:o(({data:e})=>[r(s(e.type_name),1)]),_:1}),t(n,{field:"name",header:"Category",style:{"min-width":"10rem"}},{body:o(({data:e})=>[r(s(e.category_name),1)]),_:1}),t(n,{field:"name",header:"Quantity",style:{"min-width":"10rem"}},{body:o(({data:e})=>[r(s(m(l).commaSeparator(e.quantity)),1)]),_:1}),t(n,{field:"name",header:"Measurement",style:{"min-width":"10rem"}},{body:o(({data:e})=>[r(s(e.measurement_unit_name),1)]),_:1}),t(n,{field:"name",header:"Purchase Price",style:{"min-width":"10rem"}},{body:o(({data:e})=>[r(s(m(l).commaSeparator(e.purchase_price)),1)]),_:1}),t(n,{field:"name",header:"Selling Price",style:{"min-width":"10rem"}},{body:o(({data:e})=>[r(s(m(l).commaSeparator(e.selling_price)),1)]),_:1}),t(n,{field:"name",header:"Product No.",style:{"min-width":"10rem"}},{body:o(({data:e})=>[r(s(e.product_no),1)]),_:1}),t(n,{headerStyle:"max-width:10rem;"},{body:o(({data:e})=>[h(t(c,{icon:"pi pi-shopping-bag",onClick:k=>v(e),class:"p-button-primary mr-2"},null,8,["onClick"]),[[y,"Inventory report"]]),h(t(c,{icon:"pi pi-ticket",onClick:k=>f(e),class:"p-button-success mr-2"},null,8,["onClick"]),[[y,"Sales report"]])]),_:1})]),_:1},8,["value"])])])}}};export{$ as default};