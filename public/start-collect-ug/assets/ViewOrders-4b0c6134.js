import{h as C,r as u,i as R,m as T,o as k,b as _,c as B,d as x,f as t,j as o,e as a,k as n,t as s,g as c,C as V}from"./index-019b6ee4.js";const q={className:"card"},I=a("h5",null,"View Sales",-1),M={style:{padding:"20px"}},P={class:"grid p-fluid"},E={class:"field col-6 md:col-6"},H={class:"p-float-label"},K={for:"unitName"},L={class:"field col-6 md:col-6"},O={class:"p-float-label"},$={for:"unitName"},j={class:"field col-6 md:col-6"},A={class:"p-float-label"},Q={for:"unitName"},U={class:"field col-6 md:col-6"},z={class:"p-float-label"},F={for:"unitName"},G=a("hr",null,null,-1),J={class:"grid p-fluid"},Z={__name:"ViewOrders",setup(W){const h=C(),f=u(null),p=u(!1),y=u(null),d=new V,m=u({});R(),T();const w=()=>{d.genericRequest("get-orders","get",!0,{}).then(l=>{l.status?f.value=l.data:d.showError(h,l.message)})},g=l=>{S(!0),m.value=l,N(l==null?void 0:l.id)},N=l=>{d.genericRequest("get-orders-details/"+l,"get",!0,{}).then(r=>{r.status?y.value=r.data:d.showError(h,r.message)})},S=l=>{p.value=l};return k(()=>{w()}),(l,r)=>{const i=_("Column"),b=_("Button"),v=_("DataTable"),D=_("Dialog");return B(),x("div",q,[I,t(v,{value:f.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:o(()=>[t(i,{field:"Ref No.",header:"Product",style:{"min-width":"10rem"}},{body:o(({data:e})=>[n(s(e.ref_no),1)]),_:1}),t(i,{field:"Receipt No.",header:"Receipt No.",style:{"min-width":"10rem"}},{body:o(({data:e})=>[n(s(e.receipt_no),1)]),_:1}),t(i,{field:"name",header:"Item Count",style:{"min-width":"10rem"}},{body:o(({data:e})=>[n(s(e.item_count),1)]),_:1}),t(i,{field:"name",header:"Cost",style:{"min-width":"10rem"}},{body:o(({data:e})=>[n(s(c(d).commaSeparator(e.total)),1)]),_:1}),t(i,{field:"name",header:"Amount Paid",style:{"min-width":"10rem"}},{body:o(({data:e})=>[n(s(c(d).commaSeparator(e.amount_paid)),1)]),_:1}),t(i,{header:"Discount",style:{"min-width":"10rem"}},{body:o(({data:e})=>[n(s(c(d).commaSeparator(e.discount)),1)]),_:1}),t(i,{header:"Status",style:{"min-width":"10rem"}},{body:o(({data:e})=>[n(s(e.payment_status),1)]),_:1}),t(i,{header:"Date",style:{"max-width":"10rem"}},{body:o(({data:e})=>[n(s(e.created_at),1)]),_:1}),t(i,{headerStyle:"min-width:10rem;"},{body:o(({data:e})=>[t(b,{icon:"pi pi-info",class:"p-button-rounded p-button-success mr-2",onClick:X=>g(e)},null,8,["onClick"])]),_:1})]),_:1},8,["value"]),t(D,{header:"Sales Details",visible:p.value,"onUpdate:visible":r[1]||(r[1]=e=>p.value=e),style:{width:"33vw"},modal:!0,class:"p-fluid"},{footer:o(()=>[t(b,{label:"Cancel",onClick:r[0]||(r[0]=e=>l.toggleTypeModal(!1)),icon:"pi pi-times",class:"p-button-outlined p-button-danger mr-2 mb-2"})]),default:o(()=>[a("p",M,[a("div",P,[a("div",E,[a("span",H,[a("label",K,"Ref No.: "+s(m.value.ref_no),1)])]),a("div",L,[a("span",O,[a("label",$,"Receipt No.: "+s(m.value.receipt_no),1)])]),a("div",j,[a("span",A,[a("label",Q,"Item Count: "+s(m.value.item_count),1)])]),a("div",U,[a("span",z,[a("label",F,"Total: "+s(c(d).commaSeparator(m.value.total)),1)])]),G]),a("div",J,[t(v,{value:y.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:o(()=>[t(i,{field:"Ref No.",header:"Item",style:{"min-width":"10rem"}},{body:o(({data:e})=>[n(s(e.name),1)]),_:1}),t(i,{field:"Receipt No.",header:"Price",style:{"min-width":"10rem"}},{body:o(({data:e})=>[n(s(c(d).commaSeparator(e.price)),1)]),_:1}),t(i,{field:"name",header:"Qty",style:{"min-width":"10rem"}},{body:o(({data:e})=>[n(s(e.qty),1)]),_:1}),t(i,{field:"name",header:"Sub Total",style:{"min-width":"10rem"}},{body:o(({data:e})=>[n(s(c(d).commaSeparator(e.price*e.qty)),1)]),_:1})]),_:1},8,["value"])])])]),_:1},8,["visible"])])}}};export{Z as default};