import{k as V,r as g,l as Y,m as z,o as L,b as w,p as Q,c as N,d as k,e as t,h as a,i as s,C as H,j as n,t as i,f as c,q as P,F as K,s as G}from"./index-51be1d16.js";const W={className:"card"},J={class:"card p-fluid"},X={class:"grid p-fluid"},Z={class:"field col-12 md:col-12"},tt={class:"field col-6 md:col-6"},et={class:"p-float-label"},ot={for:"unitName"},lt={class:"field col-6 md:col-6"},dt={class:"p-float-label"},it={for:"unitName"},at={class:"field col-6 md:col-6"},st={class:"p-float-label"},rt={for:"unitName"},nt={class:"field col-6 md:col-6"},pt={class:"p-float-label"},ct={for:"unitName"},ut={class:"grid p-fluid"},mt={lang:"en"},gt={style:{"background-color":"#fff"}},bt={style:{"max-width":"750px","font-family":"monospace",margin:"20px auto",padding:"30px",border:"1px solid #ddd","box-shadow":"0 2px 8px rgba(0, 0, 0, 0.1)",background:"#fff"}},ft={style:{display:"flex","justify-content":"space-between"}},yt={style:{color:"#666"}},xt={style:{color:"#666"}},ht={style:{"text-align":"right"}},vt={style:{color:"#666"}},_t={style:{color:"#666"}},wt={style:{width:"100%","border-collapse":"collapse","margin-top":"10px"}},Ct={style:{border:"1px solid #ddd",padding:"8px",color:"#666"}},Nt={style:{border:"1px solid #ddd",padding:"8px",color:"#666"}},kt={style:{border:"1px solid #ddd",padding:"8px","text-align":"right",color:"#666"}},St={style:{border:"1px solid #ddd",padding:"8px","text-align":"right",color:"#666"}},Rt={style:{border:"1px solid #ddd",padding:"8px","text-align":"right","font-weight":"bold",color:"#666"}},Dt={style:{border:"1px solid #ddd",padding:"8px","text-align":"right",color:"#666"}},Tt={style:{border:"1px solid #ddd",padding:"8px","text-align":"right",color:"#666"}},Ot={__name:"ViewOrders",setup(It){const x=V(),S=g(null),R=g(!1),p=g(null),l=new H,f=g({});Y(),z();const v=g(!1),D=g(null),C=g(!1),b=g({}),T=()=>{l.genericRequest("get-orders","get",!0,{}).then(d=>{d.status?S.value=d.data:l.showError(x,d.message)})},F=d=>{f.value=d,q(d==null?void 0:d.id)},q=d=>{l.genericRequest("get-orders-details/"+d,"get",!0,{}).then(e=>{e.status?(p.value=e.data,$(!0)):l.showError(x,e.message)})},E=()=>{l.genericRequest("reverse-sale","post",!0,D.value).then(d=>{I(),d.status?(l.showSuccess(x,d.message),T()):l.showError(x,d.message)})},j=()=>{l.genericRequest("print-receipt","post",!0,{}).then(d=>{d.status?p.value=d.data:l.showError(x,d.message)})},A=d=>{v.value=!0;let e={id:d.id};D.value=e},I=()=>{v.value=!0},$=d=>{C.value=d},U=()=>{var r,u,h;const d=window.open("","","width=600,height=600");d.document.open();let e="";p.value.forEach(m=>{e+=`
      <tr>
        <td style='border: 1px solid #ddd; padding: 8px; color: #666;'>${m.qty}</td>
        <td style='border: 1px solid #ddd; padding: 8px; color: #666;'>${m.name}</td>
        <td style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>${l.commaSeparator(Number(m.price))}</td>
        <td style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>${l.commaSeparator(Number(m.price)*Number(m.qty))}</td>
      </tr>`}),d.document.write(`<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Receipt</title>
  <style>
  @media print {
    body {
      background: #fff !important;
      color: #000;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
      font-family: Arial, sans-serif;
    }

    th, td {
      color: #000 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    * {
      box-shadow: none !important;
    }
  }
</style>
</head>
<body style="background-color: #fff;">
  <div style='max-width: 750px; font-family: monospace; margin: 20px auto; padding: 30px; border: 1px solid #ddd; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); background: #fff'>
    <header style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;'>
      <h1 style='color: #2B5F2C; margin: 0;'>Receipt</h1>
    </header>

    <section>
      <div style='display: flex; justify-content: space-between;'>
        <div>
          <p style="color: #666;"><strong>Company Name:</strong> ${(r=b.value)==null?void 0:r.institution_name}</p>
          <p style="color: #666;"><strong>P.O.Box:</strong> ${((u=b.value)==null?void 0:u.p_o_box)+" "+((h=b.value)==null?void 0:h.address)}</p>
        </div>
        <div style='text-align: right;'>
          <p style="color: #666;"><strong>Receipt #:</strong> ${l.generateRandomFiveId()}</p>
          <p style="color: #666;"><strong>Date:</strong> ${l.formatDate(l.getCurrentTimestamp())}</p>

        </div>
      </div>
    </section>



    <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
      <thead>
        <tr>
          <th style='border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #002060; color: #fff;'>QTY</th>
          <th style='border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #002060; color: #fff;'>DESCRIPTION</th>
          <th style='border: 1px solid #ddd; padding: 8px; text-align: right; background-color: #002060; color: #fff;'>UNIT PRICE</th>
          <th style='border: 1px solid #ddd; padding: 8px; text-align: right; background-color: #002060; color: #fff;'>AMOUNT</th>
        </tr>
      </thead>
      <tbody>
        ${e}
      </tbody>
      <tfoot>
        <tr>
          <td colspan='3' style='border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold; color: #666;'>Total</td>
          <td style='border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold; color: #666;'>${p.value[0].total?l.commaSeparator(Number(p.value[0].total)):0}</td>
        </tr>
        <tr>
          <td colspan='3' style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>Amount Paid</td>
          <td style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>${l.commaSeparator(Number(p.value[0].amount_paid))}</td>
        </tr>
        <tr>
          <td colspan='3' style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>Change</td>
          <td style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>${l.commaSeparator(Number(p.value[0].amount_paid)-Number(p.value[0].total))}</td>
        </tr>

      </tfoot>
    </table>

    <div style='margin-top: 20px; border: 1px solid #ddd; padding: 10px; background: #f2f2f2;'>
      <p style="color: #666;"><strong>Other Comments:</strong></p>
      <p style="color: #666;">1. Acknowledged with thanks.</p>

    </div>

    <div style='text-align: center; margin-top: 30px;'>
      <p style='font-size: 0.9em; color: #666;'>Thank you for your business!</p>
    </div>
  </div>
</body>
</html>`),d.document.close(),d.onload=function(){d.focus(),d.print(),d.close()}},M=()=>{$(!1),U()};return L(()=>{b.value=l.getStorage().userData,T()}),(d,e)=>{const r=w("Column"),u=w("Button"),h=w("DataTable"),m=w("Dialog"),B=Q("tooltip");return N(),k("div",W,[e[18]||(e[18]=t("h5",null,"View Sales",-1)),a(h,{value:S.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:s(()=>[a(r,{field:"Ref No.",header:"Ref No.",style:{"min-width":"10rem"}},{body:s(({data:o})=>[n(i(o.ref_no),1)]),_:1}),a(r,{field:"Receipt No.",header:"Receipt No.",style:{"min-width":"10rem"}},{body:s(({data:o})=>[n(i(o.receipt_no),1)]),_:1}),a(r,{field:"name",header:"Item Count",style:{"min-width":"10rem"}},{body:s(({data:o})=>[n(i(o.item_count),1)]),_:1}),a(r,{field:"name",header:"Amount Paid",style:{"min-width":"10rem"}},{body:s(({data:o})=>[n(i(c(l).commaSeparator(o.amount_paid)),1)]),_:1}),a(r,{field:"name",header:"Cost",style:{"min-width":"10rem"}},{body:s(({data:o})=>[n(i(c(l).commaSeparator(o.total)),1)]),_:1}),a(r,{field:"name",header:"Change",style:{"min-width":"10rem"}},{body:s(({data:o})=>[n(i(c(l).commaSeparator(o.amount_paid-o.total)),1)]),_:1}),a(r,{header:"Discount",style:{"min-width":"10rem"}},{body:s(({data:o})=>[n(i(c(l).commaSeparator(o.discount)),1)]),_:1}),a(r,{header:"Status",style:{"min-width":"10rem"}},{body:s(({data:o})=>[n(i(o.payment_status),1)]),_:1}),a(r,{header:"Date",style:{"max-width":"10rem"}},{body:s(({data:o})=>[n(i(o.created_at),1)]),_:1}),a(r,{headerStyle:"min-width:10rem;"},{body:s(({data:o})=>[P(a(u,{icon:"pi pi-eye",class:"p-button-primary mr-2",onClick:_=>F(o)},null,8,["onClick"]),[[B,"View sell details"]]),P(a(u,{icon:"pi pi-trash",class:"p-button-danger mt-2",onClick:_=>A(o)},null,8,["onClick"]),[[B,"Reverse sale"]])]),_:1})]),_:1},8,["value"]),t("div",J,[a(m,{header:"Confirmation",visible:v.value,"onUpdate:visible":e[0]||(e[0]=o=>v.value=o),style:{width:"350px"},modal:!0},{footer:s(()=>[a(u,{label:"No",icon:"pi pi-times",onClick:I,class:"p-button-text"}),a(u,{label:"Yes",icon:"pi pi-check",onClick:E,class:"p-button-text",autofocus:""})]),default:s(()=>[e[4]||(e[4]=t("div",{class:"flex align-items-center justify-content-center"},[t("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),t("span",null,"Are you sure you want to proceed?")],-1))]),_:1},8,["visible"])]),a(m,{header:"Sales Details",visible:R.value,"onUpdate:visible":e[2]||(e[2]=o=>R.value=o),style:{width:"33vw"},modal:!0,class:"p-fluid"},{footer:s(()=>[a(u,{label:"Cancel",onClick:e[1]||(e[1]=o=>d.toggleTypeModal(!1)),icon:"pi pi-times",class:"p-button-outlined p-button-danger mr-2 mb-2"}),a(u,{onClick:j,icon:"pi pi-print",label:"Print",class:"p-button-outlined mr-2 mb-2"})]),default:s(()=>[t("div",X,[t("div",Z,[t("h1",null,i(f.value.institution_name),1)]),t("div",tt,[t("span",et,[t("label",ot,"Ref No.: "+i(f.value.ref_no),1)])]),t("div",lt,[t("span",dt,[t("label",it,"Receipt No.: "+i(f.value.receipt_no),1)])]),t("div",at,[t("span",st,[t("label",rt,"Item Count: "+i(f.value.item_count),1)])]),t("div",nt,[t("span",pt,[t("label",ct,"Total: "+i(c(l).commaSeparator(f.value.total)),1)])]),e[5]||(e[5]=t("hr",null,null,-1))]),t("div",ut,[a(h,{value:p.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:s(()=>[a(r,{field:"Ref No.",header:"Item",style:{"min-width":"10rem"}},{body:s(({data:o})=>[n(i(o.name),1)]),_:1}),a(r,{field:"Receipt No.",header:"Price",style:{"min-width":"10rem"}},{body:s(({data:o})=>[n(i(c(l).commaSeparator(o.price)),1)]),_:1}),a(r,{field:"name",header:"Qty",style:{"min-width":"10rem"}},{body:s(({data:o})=>[n(i(o.qty),1)]),_:1}),a(r,{field:"name",header:"Sub Total",style:{"min-width":"10rem"}},{body:s(({data:o})=>[n(i(c(l).commaSeparator(o.price*o.qty)),1)]),_:1})]),_:1},8,["value"])])]),_:1},8,["visible"]),a(m,{header:"Generate Invoice",visible:C.value,"onUpdate:visible":e[3]||(e[3]=o=>C.value=o),style:{width:"75vw"},modal:!0,class:"p-fluid"},{footer:s(()=>[a(u,{label:"print",onClick:M,icon:"pi pi-print",class:"p-button-outlined"})]),default:s(()=>{var o,_,O;return[t("html",mt,[e[17]||(e[17]=t("head",null,[t("meta",{charset:"UTF-8"}),t("meta",{name:"viewport",content:"width=device-width, initial-scale=1.0"}),t("title",null,"Invoice")],-1)),t("body",gt,[t("div",bt,[e[14]||(e[14]=t("header",{style:{display:"flex","justify-content":"space-between","align-items":"center","margin-bottom":"20px"}},[t("h1",{style:{color:"#2B5F2C",margin:"0"}},"Receipt")],-1)),t("section",null,[t("div",ft,[t("div",null,[t("p",yt,[e[6]||(e[6]=t("strong",null,"Company Name:",-1)),n(" "+i((o=b.value)==null?void 0:o.institution_name),1)]),t("p",xt,[e[7]||(e[7]=t("strong",null,"P.O.Box:",-1)),n(" "+i(((_=b.value)==null?void 0:_.p_o_box)+" "+((O=b.value)==null?void 0:O.address)),1)])]),t("div",ht,[t("p",vt,[e[8]||(e[8]=t("strong",null,"Receipt #:",-1)),n(" "+i(c(l).generateRandomFiveId()),1)]),t("p",_t,[e[9]||(e[9]=t("strong",null,"Date:",-1)),n(" "+i(c(l).formatDate(c(l).getCurrentTimestamp())),1)])])])]),t("table",wt,[e[13]||(e[13]=t("thead",null,[t("tr",null,[t("th",{style:{border:"1px solid #ddd",padding:"8px","text-align":"left","background-color":"#2B5F2C",color:"#fff"}},"QTY"),t("th",{style:{border:"1px solid #ddd",padding:"8px","text-align":"left","background-color":"#2B5F2C",color:"#fff"}},"DESCRIPTION"),t("th",{style:{border:"1px solid #ddd",padding:"8px","text-align":"right","background-color":"#2B5F2C",color:"#fff"}},"UNIT PRICE"),t("th",{style:{border:"1px solid #ddd",padding:"8px","text-align":"right","background-color":"#2B5F2C",color:"#fff"}},"AMOUNT")])],-1)),t("tbody",null,[(N(!0),k(K,null,G(p.value,(y,$t)=>(N(),k("tr",{key:y.id},[t("td",Ct,i(y.qty),1),t("td",Nt,i(y.name),1),t("td",kt,i(c(l).commaSeparator(Number(y.price))),1),t("td",St,i(c(l).commaSeparator(Number(y.price)*Number(y.qty))),1)]))),128))]),t("tfoot",null,[t("tr",null,[e[10]||(e[10]=t("td",{colspan:"3",style:{border:"1px solid #ddd",padding:"8px","text-align":"right","font-weight":"bold",color:"#666"}},"Total",-1)),t("td",Rt,i(p.value[0].total?c(l).commaSeparator(Number(p.value[0].total)):0),1)]),t("tr",null,[e[11]||(e[11]=t("td",{colspan:"3",style:{border:"1px solid #ddd",padding:"8px","text-align":"right",color:"#666"}},"Amount Paid",-1)),t("td",Dt,i(c(l).commaSeparator(Number(p.value[0].amount_paid))),1)]),t("tr",null,[e[12]||(e[12]=t("td",{colspan:"3",style:{border:"1px solid #ddd",padding:"8px","text-align":"right",color:"#666"}},"Change",-1)),t("td",Tt,i(c(l).commaSeparator(Number(p.value[0].amount_paid)-Number(p.value[0].total))),1)])])]),e[15]||(e[15]=t("div",{style:{"margin-top":"20px",border:"1px solid #ddd",padding:"10px",background:"#f2f2f2"}},[t("p",{style:{color:"#666"}},[t("strong",null,"Other Comments:")]),t("p",{style:{color:"#666"}},"1. Acknowledged with thanks.")],-1)),e[16]||(e[16]=t("div",{style:{"text-align":"center","margin-top":"30px"}},[t("p",{style:{"font-size":"0.9em",color:"#666"}},"Thank you for your business!")],-1))])])])]}),_:1},8,["visible"])])}}};export{Ot as default};
