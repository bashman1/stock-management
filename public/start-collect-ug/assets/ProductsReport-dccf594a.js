import{l as D,m as L,r as b,o as x,b as d,s as B,c as U,d as N,e as _,h as t,i as r,k as i,t as l,f as m,v,C as V}from"./index-a7b81454.js";const q={class:"card"},A=_("h5",null,"Products report.",-1),E={class:"my-2"},I={__name:"ProductsReport",setup(F){D();const c=new V,y=L(),h=b(null),u=b(!1),w=()=>{let o={status:"Active"};c.genericRequest("get-products-report","post",!0,o).then(n=>{n.status&&(h.value=n.data)})},g=o=>{y.push("/product-inventory-report/"+o.id)},C=o=>{y.push("/products-sales-report/"+o.id)},R=()=>{try{u.value=!0,c.genericRequest("download-product-report-pdf","post",!0,{},!0).then(o=>{const n=window.URL.createObjectURL(new Blob([o])),a=document.createElement("a");a.href=n,a.setAttribute("download","product_reports.pdf"),document.body.appendChild(a),a.click()})}catch(o){console.log(o)}finally{u.value=!1}},k=()=>{try{u.value=!0,c.genericRequest("download-product-report-csv","post",!0,{},!0).then(o=>{const n=window.URL.createObjectURL(new Blob([o])),a=document.createElement("a");a.href=n,a.setAttribute("download","product_reports.csv"),document.body.appendChild(a),a.click()})}catch(o){console.log(o)}finally{u.value=!1}};return x(()=>{w()}),(o,n)=>{const a=d("Toast"),p=d("Button"),T=d("Toolbar"),s=d("Column"),P=d("DataTable"),f=B("tooltip");return U(),N("div",null,[_("div",q,[A,t(a),t(T,{class:"mb-4"},{end:r(()=>[_("div",E,[t(p,{label:"CSV",icon:"pi pi-file-excel",class:"p-button-success mr-2",onClick:n[0]||(n[0]=()=>k())}),t(p,{label:"PDF",icon:"pi pi-file-pdf",class:"p-button-danger",onClick:n[1]||(n[1]=()=>R())})])]),_:1}),t(P,{value:h.value,paginator:!0,class:"p-datatable-gridlines",rows:20,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:r(()=>[t(s,{field:"name",header:"Name",style:{"min-width":"10rem"}},{body:r(({data:e})=>[i(l(e.name),1)]),_:1}),t(s,{field:"name",header:"Type",style:{"min-width":"10rem"}},{body:r(({data:e})=>[i(l(e.type_name),1)]),_:1}),t(s,{field:"name",header:"Category",style:{"min-width":"10rem"}},{body:r(({data:e})=>[i(l(e.category_name),1)]),_:1}),t(s,{field:"name",header:"Quantity",style:{"min-width":"10rem"}},{body:r(({data:e})=>[i(l(m(c).commaSeparator(e.quantity)),1)]),_:1}),t(s,{field:"name",header:"Measurement",style:{"min-width":"10rem"}},{body:r(({data:e})=>[i(l(e.measurement_unit_name),1)]),_:1}),t(s,{field:"name",header:"Purchase Price",style:{"min-width":"10rem"}},{body:r(({data:e})=>[i(l(m(c).commaSeparator(e.purchase_price)),1)]),_:1}),t(s,{field:"name",header:"Selling Price",style:{"min-width":"10rem"}},{body:r(({data:e})=>[i(l(m(c).commaSeparator(e.selling_price)),1)]),_:1}),t(s,{field:"name",header:"Product No.",style:{"min-width":"10rem"}},{body:r(({data:e})=>[i(l(e.product_no),1)]),_:1}),t(s,{field:"name",header:"VAT Tax",style:{"min-width":"10rem"}},{body:r(({data:e})=>[i(l(e.tax_config),1)]),_:1}),t(s,{headerStyle:"min-width:10rem;"},{body:r(({data:e})=>[v(t(p,{icon:"pi pi-shopping-bag",onClick:S=>g(e),class:"p-button-primary mr-2"},null,8,["onClick"]),[[f,"Inventory report"]]),v(t(p,{icon:"pi pi-ticket",onClick:S=>C(e),class:"p-button-success mr-2"},null,8,["onClick"]),[[f,"Sales report"]])]),_:1})]),_:1},8,["value"])])])}}};export{I as default};