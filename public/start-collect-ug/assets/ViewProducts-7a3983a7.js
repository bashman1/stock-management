import{h as p,r as b,i as w,m as f,o as g,b as i,c as C,d as v,f as t,j as a,e as B,k as n,t as r,C as D}from"./index-019b6ee4.js";const S={className:"card"},k=B("h5",null,"View Products",-1),N={__name:"ViewProducts",setup(P){const m=p(),d=b(null),c=new D,l=w();f();const u=()=>{c.genericRequest("get-products","get",!0,{}).then(s=>{s.status?d.value=s.data:c.showError(m,s.message)})},_=s=>{l.push("/get-products"+(s==null?void 0:s.id))};return g(()=>{u()}),(s,T)=>{const o=i("Column"),h=i("Button"),y=i("DataTable");return C(),v("div",S,[k,t(y,{value:d.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:a(()=>[t(o,{field:"name",header:"Product",style:{"min-width":"10rem"}},{body:a(({data:e})=>[n(r(e.name),1)]),_:1}),t(o,{field:"name",header:"Product ID",style:{"min-width":"10rem"}},{body:a(({data:e})=>[n(r(e.product_no),1)]),_:1}),t(o,{field:"name",header:"Category",style:{"min-width":"10rem"}},{body:a(({data:e})=>[n(r(e.category_name),1)]),_:1}),t(o,{field:"name",header:"Sub Category",style:{"min-width":"10rem"}},{body:a(({data:e})=>[n(r(e.sub_category_name),1)]),_:1}),t(o,{field:"name",header:"Selling Price",style:{"min-width":"10rem"}},{body:a(({data:e})=>[n(r(e.selling_price),1)]),_:1}),t(o,{field:"name",header:"Quantity",style:{"min-width":"10rem"}},{body:a(({data:e})=>[n(r(e.quantity+" "+e.unit),1)]),_:1}),t(o,{header:"Manufacturer",style:{"min-width":"10rem"}},{body:a(({data:e})=>[n(r(e.manufacturer),1)]),_:1}),t(o,{header:"Institution",style:{"min-width":"10rem"}},{body:a(({data:e})=>[n(r(e.institution_name),1)]),_:1}),t(o,{header:"Branch",style:{"min-width":"10rem"}},{body:a(({data:e})=>[n(r(e.branch_name),1)]),_:1}),t(o,{header:"Date",style:{"min-width":"10rem"}},{body:a(({data:e})=>[n(r(e.created_at),1)]),_:1}),t(o,{headerStyle:"min-width:10rem;"},{body:a(({data:e})=>[t(h,{icon:"pi pi-file-edit",class:"p-button-rounded p-button-success mr-2",onClick:V=>_(e)},null,8,["onClick"])]),_:1})]),_:1},8,["value"])])}}};export{N as default};