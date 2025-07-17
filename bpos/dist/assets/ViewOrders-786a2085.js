import{k as V,r as g,l as Y,m as z,o as L,b as w,p as Q,c as N,d as k,e as t,h as s,i as r,C as H,j as d,t as a,f as c,q as P,F as K,s as G}from"./index-3793f17e.js";const W={className:"card"},J={class:"card p-fluid"},X={class:"grid p-fluid"},Z={class:"field col-12 md:col-12"},tt={class:"field col-6 md:col-6"},et={class:"p-float-label"},ot={for:"unitName"},lt={class:"field col-6 md:col-6"},it={class:"p-float-label"},at={for:"unitName"},st={class:"field col-6 md:col-6"},rt={class:"p-float-label"},nt={for:"unitName"},dt={class:"field col-6 md:col-6"},pt={class:"p-float-label"},ct={for:"unitName"},ut={class:"grid p-fluid"},mt={lang:"en"},gt={style:{"background-color":"#fff"}},bt={style:{"max-width":"750px","font-family":"monospace",margin:"20px auto",padding:"30px",border:"1px solid #000","box-shadow":"0 2px 8px rgba(0, 0, 0, 0.1)",background:"#fff"}},yt={style:{display:"flex","justify-content":"space-between"}},xt={style:{color:"#666"}},ht={style:{color:"#666"}},ft={style:{"text-align":"right"}},vt={style:{color:"#666"}},_t={style:{color:"#666"}},wt={style:{width:"100%","border-collapse":"collapse","margin-top":"10px"}},Ct={style:{border:"1px solid #000",padding:"8px",color:"#666"}},Nt={style:{border:"1px solid #000",padding:"8px",color:"#666"}},kt={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},St={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},Rt={style:{border:"1px solid #000",padding:"8px","text-align":"right","font-weight":"bold",color:"#666"}},Dt={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},Tt={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},Ot={__name:"ViewOrders",setup(It){const h=V(),S=g(null),R=g(!1),p=g(null),l=new H,y=g({});Y(),z();const v=g(!1),D=g(null),C=g(!1),b=g({}),T=()=>{l.genericRequest("get-orders","get",!0,{}).then(i=>{i.status?S.value=i.data:l.showError(h,i.message)})},q=i=>{y.value=i,E(i==null?void 0:i.id)},E=i=>{l.genericRequest("get-orders-details/"+i,"get",!0,{}).then(e=>{e.status?(p.value=e.data,$(!0)):l.showError(h,e.message)})},F=()=>{l.genericRequest("reverse-sale","post",!0,D.value).then(i=>{I(),i.status?(l.showSuccess(h,i.message),T()):l.showError(h,i.message)})},j=()=>{l.genericRequest("print-receipt","post",!0,{}).then(i=>{i.status?p.value=i.data:l.showError(h,i.message)})},A=i=>{v.value=!0;let e={id:i.id};D.value=e},I=()=>{v.value=!0},$=i=>{C.value=i},U=()=>{var n,u,f;const i=window.open("","","width=600,height=600");i.document.open();let e="";p.value.forEach(m=>{e+=`
      <tr>
        <td style='border: 1px solid #000; padding: 8px; color: #666;'>${m.qty}</td>
        <td style='border: 1px solid #000; padding: 8px; color: #666;'>${m.name}</td>
        <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #666;'>${l.commaSeparator(Number(m.price))}</td>
        <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #666;'>${l.commaSeparator(Number(m.price)*Number(m.qty))}</td>
      </tr>`}),i.document.write(`<!DOCTYPE html>
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
  <div style='max-width: 750px; font-family: Arial, sans-serif; margin: 20px auto; padding: 30px; border: 1px solid #000; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); background: #fff'>
    <header style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;'>
      <h1 style='color: #000; margin: 0;'>Receipt</h1>
    </header>

    <section>
      <div style='display: flex; justify-content: space-between;'>
        <div>
          <p style="color: #000;"><strong>Company Name:</strong> ${(n=b.value)==null?void 0:n.institution_name}</p>
          <p style="color: #000;"><strong>P.O.Box:</strong> ${((u=b.value)==null?void 0:u.p_o_box)+" "+((f=b.value)==null?void 0:f.address)}</p>
        </div>
        <div style='text-align: right;'>
          <p style="color: #000;"><strong>Receipt #:</strong> ${l.generateRandomFiveId()}</p>
          <p style="color: #000;"><strong>Date:</strong> ${l.formatDate(l.getCurrentTimestamp())}</p>

        </div>
      </div>
    </section>



    <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
      <thead>
        <tr>
          <th style='border: 1px solid #000; padding: 8px; text-align: left;  color: #000;'>QTY</th>
          <th style='border: 1px solid #000; padding: 8px; text-align: left;  color: #000;'>DESCRIPTION</th>
          <th style='border: 1px solid #000; padding: 8px; text-align: right;  color: #000;'>UNIT PRICE</th>
          <th style='border: 1px solid #000; padding: 8px; text-align: right;  color: #000;'>AMOUNT</th>
        </tr>
      </thead>
      <tbody>
        ${e}
      </tbody>
      <tfoot>
        <tr>
          <td colspan='3' style='border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; color: #000;'>Total</td>
          <td style='border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; color: #000;'>${p.value[0].total?l.commaSeparator(Number(p.value[0].total)):0}</td>
        </tr>
        <tr>
          <td colspan='3' style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>Amount Paid</td>
          <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>${l.commaSeparator(Number(p.value[0].amount_paid))}</td>
        </tr>
        <tr>
          <td colspan='3' style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>Change</td>
          <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>${l.commaSeparator(Number(p.value[0].amount_paid)-Number(p.value[0].total))}</td>
        </tr>

      </tfoot>
    </table>

    <div style='margin-top: 20px; border: 1px solid #000; padding: 10px;'>
      <p style="color: #000;"><strong>Other Comments:</strong></p>
      <p style="color: #000;">1. Acknowledged with thanks.</p>

    </div>

    <div style='text-align: center; margin-top: 30px;'>
      <p style='font-size: 0.9em; color: #000;'>Thank you for your business!</p>
    </div>
  </div>
</body>
</html>`),i.document.close(),i.onload=function(){i.focus(),i.print(),i.close()}},M=()=>{$(!1),U()};return L(()=>{b.value=l.getStorage().userData,T()}),(i,e)=>{const n=w("Column"),u=w("Button"),f=w("DataTable"),m=w("Dialog"),B=Q("tooltip");return N(),k("div",W,[e[18]||(e[18]=t("h5",null,"View Sales",-1)),s(f,{value:S.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:r(()=>[s(n,{field:"Ref No.",header:"Ref No.",style:{"min-width":"10rem"}},{body:r(({data:o})=>[d(a(o.ref_no),1)]),_:1}),s(n,{field:"Receipt No.",header:"Receipt No.",style:{"min-width":"10rem"}},{body:r(({data:o})=>[d(a(o.receipt_no),1)]),_:1}),s(n,{field:"name",header:"Item Count",style:{"min-width":"10rem"}},{body:r(({data:o})=>[d(a(o.item_count),1)]),_:1}),s(n,{field:"name",header:"Amount Paid",style:{"min-width":"10rem"}},{body:r(({data:o})=>[d(a(c(l).commaSeparator(o.amount_paid)),1)]),_:1}),s(n,{field:"name",header:"Cost",style:{"min-width":"10rem"}},{body:r(({data:o})=>[d(a(c(l).commaSeparator(o.total)),1)]),_:1}),s(n,{field:"name",header:"Change",style:{"min-width":"10rem"}},{body:r(({data:o})=>[d(a(c(l).commaSeparator(o.amount_paid-o.total)),1)]),_:1}),s(n,{header:"Discount",style:{"min-width":"10rem"}},{body:r(({data:o})=>[d(a(c(l).commaSeparator(o.discount)),1)]),_:1}),s(n,{header:"Status",style:{"min-width":"10rem"}},{body:r(({data:o})=>[d(a(o.payment_status),1)]),_:1}),s(n,{header:"Date",style:{"max-width":"10rem"}},{body:r(({data:o})=>[d(a(o.created_at),1)]),_:1}),s(n,{headerStyle:"min-width:10rem;"},{body:r(({data:o})=>[P(s(u,{icon:"pi pi-eye",class:"p-button-primary mr-2",onClick:_=>q(o)},null,8,["onClick"]),[[B,"View sell details"]]),P(s(u,{icon:"pi pi-trash",class:"p-button-danger mt-2",onClick:_=>A(o)},null,8,["onClick"]),[[B,"Reverse sale"]])]),_:1})]),_:1},8,["value"]),t("div",J,[s(m,{header:"Confirmation",visible:v.value,"onUpdate:visible":e[0]||(e[0]=o=>v.value=o),style:{width:"350px"},modal:!0},{footer:r(()=>[s(u,{label:"No",icon:"pi pi-times",onClick:I,class:"p-button-text"}),s(u,{label:"Yes",icon:"pi pi-check",onClick:F,class:"p-button-text",autofocus:""})]),default:r(()=>[e[4]||(e[4]=t("div",{class:"flex align-items-center justify-content-center"},[t("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),t("span",null,"Are you sure you want to proceed?")],-1))]),_:1},8,["visible"])]),s(m,{header:"Sales Details",visible:R.value,"onUpdate:visible":e[2]||(e[2]=o=>R.value=o),style:{width:"33vw"},modal:!0,class:"p-fluid"},{footer:r(()=>[s(u,{label:"Cancel",onClick:e[1]||(e[1]=o=>i.toggleTypeModal(!1)),icon:"pi pi-times",class:"p-button-outlined p-button-danger mr-2 mb-2"}),s(u,{onClick:j,icon:"pi pi-print",label:"Print",class:"p-button-outlined mr-2 mb-2"})]),default:r(()=>[t("div",X,[t("div",Z,[t("h1",null,a(y.value.institution_name),1)]),t("div",tt,[t("span",et,[t("label",ot,"Ref No.: "+a(y.value.ref_no),1)])]),t("div",lt,[t("span",it,[t("label",at,"Receipt No.: "+a(y.value.receipt_no),1)])]),t("div",st,[t("span",rt,[t("label",nt,"Item Count: "+a(y.value.item_count),1)])]),t("div",dt,[t("span",pt,[t("label",ct,"Total: "+a(c(l).commaSeparator(y.value.total)),1)])]),e[5]||(e[5]=t("hr",null,null,-1))]),t("div",ut,[s(f,{value:p.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:r(()=>[s(n,{field:"Ref No.",header:"Item",style:{"min-width":"10rem"}},{body:r(({data:o})=>[d(a(o.name),1)]),_:1}),s(n,{field:"Receipt No.",header:"Price",style:{"min-width":"10rem"}},{body:r(({data:o})=>[d(a(c(l).commaSeparator(o.price)),1)]),_:1}),s(n,{field:"name",header:"Qty",style:{"min-width":"10rem"}},{body:r(({data:o})=>[d(a(o.qty),1)]),_:1}),s(n,{field:"name",header:"Sub Total",style:{"min-width":"10rem"}},{body:r(({data:o})=>[d(a(c(l).commaSeparator(o.price*o.qty)),1)]),_:1})]),_:1},8,["value"])])]),_:1},8,["visible"]),s(m,{header:"Generate Invoice",visible:C.value,"onUpdate:visible":e[3]||(e[3]=o=>C.value=o),style:{width:"75vw"},modal:!0,class:"p-fluid"},{footer:r(()=>[s(u,{label:"print",onClick:M,icon:"pi pi-print",class:"p-button-outlined"})]),default:r(()=>{var o,_,O;return[t("html",mt,[e[17]||(e[17]=t("head",null,[t("meta",{charset:"UTF-8"}),t("meta",{name:"viewport",content:"width=device-width, initial-scale=1.0"}),t("title",null,"Invoice")],-1)),t("body",gt,[t("div",bt,[e[14]||(e[14]=t("header",{style:{display:"flex","justify-content":"space-between","align-items":"center","margin-bottom":"20px"}},[t("h1",{style:{color:"#2B5F2C",margin:"0"}},"Receipt")],-1)),t("section",null,[t("div",yt,[t("div",null,[t("p",xt,[e[6]||(e[6]=t("strong",null,"Company Name:",-1)),d(" "+a((o=b.value)==null?void 0:o.institution_name),1)]),t("p",ht,[e[7]||(e[7]=t("strong",null,"P.O.Box:",-1)),d(" "+a(((_=b.value)==null?void 0:_.p_o_box)+" "+((O=b.value)==null?void 0:O.address)),1)])]),t("div",ft,[t("p",vt,[e[8]||(e[8]=t("strong",null,"Receipt #:",-1)),d(" "+a(c(l).generateRandomFiveId()),1)]),t("p",_t,[e[9]||(e[9]=t("strong",null,"Date:",-1)),d(" "+a(c(l).formatDate(c(l).getCurrentTimestamp())),1)])])])]),t("table",wt,[e[13]||(e[13]=t("thead",null,[t("tr",null,[t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"left","background-color":"#2B5F2C",color:"#fff"}},"QTY"),t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"left","background-color":"#2B5F2C",color:"#fff"}},"DESCRIPTION"),t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"right","background-color":"#2B5F2C",color:"#fff"}},"UNIT PRICE"),t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"right","background-color":"#2B5F2C",color:"#fff"}},"AMOUNT")])],-1)),t("tbody",null,[(N(!0),k(K,null,G(p.value,(x,$t)=>(N(),k("tr",{key:x.id},[t("td",Ct,a(x.qty),1),t("td",Nt,a(x.name),1),t("td",kt,a(c(l).commaSeparator(Number(x.price))),1),t("td",St,a(c(l).commaSeparator(Number(x.price)*Number(x.qty))),1)]))),128))]),t("tfoot",null,[t("tr",null,[e[10]||(e[10]=t("td",{colspan:"3",style:{border:"1px solid #000",padding:"8px","text-align":"right","font-weight":"bold",color:"#666"}},"Total",-1)),t("td",Rt,a(p.value[0].total?c(l).commaSeparator(Number(p.value[0].total)):0),1)]),t("tr",null,[e[11]||(e[11]=t("td",{colspan:"3",style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},"Amount Paid",-1)),t("td",Dt,a(c(l).commaSeparator(Number(p.value[0].amount_paid))),1)]),t("tr",null,[e[12]||(e[12]=t("td",{colspan:"3",style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},"Change",-1)),t("td",Tt,a(c(l).commaSeparator(Number(p.value[0].amount_paid)-Number(p.value[0].total))),1)])])]),e[15]||(e[15]=t("div",{style:{"margin-top":"20px",border:"1px solid #000",padding:"10px",background:"#f2f2f2"}},[t("p",{style:{color:"#666"}},[t("strong",null,"Other Comments:")]),t("p",{style:{color:"#666"}},"1. Acknowledged with thanks.")],-1)),e[16]||(e[16]=t("div",{style:{"text-align":"center","margin-top":"30px"}},[t("p",{style:{"font-size":"0.9em",color:"#666"}},"Thank you for your business!")],-1))])])])]}),_:1},8,["visible"])])}}};export{Ot as default};
