import{k as Y,r as g,l as z,m as L,o as Q,b as w,p as W,c as k,d as S,e as t,h as s,i as r,C as H,j as p,t as a,f as c,q,F as K,s as G}from"./index-8a97891a.js";const J={className:"card"},X={class:"card p-fluid"},Z={class:"grid p-fluid"},tt={class:"field col-12 md:col-12"},et={class:"field col-6 md:col-6"},ot={class:"p-float-label"},lt={for:"unitName"},it={class:"field col-6 md:col-6"},at={class:"p-float-label"},st={for:"unitName"},rt={class:"field col-6 md:col-6"},nt={class:"p-float-label"},dt={for:"unitName"},pt={class:"field col-6 md:col-6"},ct={class:"p-float-label"},ut={for:"unitName"},mt={class:"grid p-fluid"},gt={lang:"en"},bt={style:{"background-color":"#fff"}},yt={style:{"max-width":"750px","font-family":"monospace",margin:"20px auto",padding:"30px",border:"1px solid #000","box-shadow":"0 2px 8px rgba(0, 0, 0, 0.1)",background:"#fff"}},xt={style:{display:"flex","justify-content":"space-between"}},ht={style:{color:"#666"}},ft={style:{color:"#666"}},vt={style:{"text-align":"right"}},_t={style:{color:"#666"}},wt={style:{color:"#666"}},Ct={style:{width:"100%","border-collapse":"collapse","margin-top":"10px"}},Nt={style:{border:"1px solid #000",padding:"8px",color:"#666"}},kt={style:{border:"1px solid #000",padding:"8px",color:"#666"}},St={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},Dt={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},Rt={style:{border:"1px solid #000",padding:"8px","text-align":"right","font-weight":"bold",color:"#666"}},Tt={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},It={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},Pt={__name:"ViewOrders",setup($t){const h=Y(),D=g(null),R=g(!1),n=g(null),i=new H,y=g({});z(),L();const v=g(!1),T=g(null),C=g(!1),N=g({}),I=()=>{i.genericRequest("get-orders","get",!0,{}).then(l=>{l.status?D.value=l.data:i.showError(h,l.message)})},E=l=>{y.value=l,j(l==null?void 0:l.id)},j=l=>{i.genericRequest("get-orders-details/"+l,"get",!0,{}).then(e=>{e.status?(n.value=e.data,B(!0)):i.showError(h,e.message)})},A=()=>{i.genericRequest("reverse-sale","post",!0,T.value).then(l=>{$(),l.status?(i.showSuccess(h,l.message),I()):i.showError(h,l.message)})},F=()=>{i.genericRequest("print-receipt","post",!0,{}).then(l=>{l.status?n.value=l.data:i.showError(h,l.message)})},U=l=>{v.value=!0;let e={id:l.id};T.value=e},$=()=>{v.value=!0},B=l=>{C.value=l},M=()=>{var d,u,f,x;const l=window.open("","","width=600,height=600");l.document.open();let e="";n.value.forEach(m=>{e+=`
      <tr>
        <td style='border: 1px solid #000; padding: 8px; color: #666;'>${m.qty+" "+m.unit}</td>
        <td style='border: 1px solid #000; padding: 8px; color: #666;'>${m.name}</td>
        <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #666;'>${i.commaSeparator(Number(m.price))}</td>
        <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #666;'>${i.commaSeparator(Number(m.price)*Number(m.qty))}</td>
      </tr>`}),l.document.write(`<!DOCTYPE html>
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
          <p style="color: #000;"><strong>Company Name:</strong> ${(d=N.value)==null?void 0:d.institution_name}</p>
          <p style="color: #000;"><strong>P.O.Box:</strong> ${((u=n.value[0])==null?void 0:u.p_o_box)+" "+((f=n.value[0])==null?void 0:f.institution_address)}</p>
        </div>
        <div style='text-align: right;'>
          <p style="color: #000;"><strong>Receipt #:</strong> ${(x=n.value[0])==null?void 0:x.receipt_no}</p>
          <p style="color: #000;"><strong>Date:</strong> ${i.formatDate(i.getCurrentTimestamp())}</p>

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
          <td style='border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; color: #000;'>${n.value[0].total?i.commaSeparator(Number(n.value[0].total)):0}</td>
        </tr>
        <tr>
          <td colspan='3' style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>Amount Paid</td>
          <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>${i.commaSeparator(Number(n.value[0].amount_paid))}</td>
        </tr>
        <tr>
          <td colspan='3' style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>Change</td>
          <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>${i.commaSeparator(Number(n.value[0].amount_paid)-Number(n.value[0].total))}</td>
        </tr>

      </tfoot>
    </table>

    <div style='margin-top: 20px; border: 1px solid #000; padding: 10px;'>
      <p style="color: #000;"><strong>Other Comments:</strong></p>
      <p style="color: #000;">1. Acknowledged with thanks.</p>

    </div>

    <div style='text-align: center; margin-top: 30px;'>
      <p style='font-size: 0.9em; color: #000;'>We appreciate your support.</p>
    </div>
  </div>
</body>
</html>`),l.document.close(),l.onload=function(){l.focus(),l.print(),l.close()}},V=()=>{B(!1),M()};return Q(()=>{N.value=i.getStorage().userData,I()}),(l,e)=>{const d=w("Column"),u=w("Button"),f=w("DataTable"),x=w("Dialog"),m=W("tooltip");return k(),S("div",J,[e[18]||(e[18]=t("h5",null,"View Sales",-1)),s(f,{value:D.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:r(()=>[s(d,{field:"Ref No.",header:"Ref No.",style:{"min-width":"10rem"}},{body:r(({data:o})=>[p(a(o.ref_no),1)]),_:1}),s(d,{field:"Receipt No.",header:"Receipt No.",style:{"min-width":"10rem"}},{body:r(({data:o})=>[p(a(o.receipt_no),1)]),_:1}),s(d,{field:"name",header:"Item Count",style:{"min-width":"10rem"}},{body:r(({data:o})=>[p(a(o.item_count),1)]),_:1}),s(d,{field:"name",header:"Amount Paid",style:{"min-width":"10rem"}},{body:r(({data:o})=>[p(a(c(i).commaSeparator(o.amount_paid)),1)]),_:1}),s(d,{field:"name",header:"Cost",style:{"min-width":"10rem"}},{body:r(({data:o})=>[p(a(c(i).commaSeparator(o.total)),1)]),_:1}),s(d,{field:"name",header:"Change",style:{"min-width":"10rem"}},{body:r(({data:o})=>[p(a(c(i).commaSeparator(o.amount_paid-o.total)),1)]),_:1}),s(d,{header:"Discount",style:{"min-width":"10rem"}},{body:r(({data:o})=>[p(a(c(i).commaSeparator(o.discount)),1)]),_:1}),s(d,{header:"Status",style:{"min-width":"10rem"}},{body:r(({data:o})=>[p(a(o.payment_status),1)]),_:1}),s(d,{header:"Date",style:{"max-width":"10rem"}},{body:r(({data:o})=>[p(a(o.created_at),1)]),_:1}),s(d,{headerStyle:"min-width:10rem;"},{body:r(({data:o})=>[q(s(u,{icon:"pi pi-eye",class:"p-button-primary mr-2",onClick:_=>E(o)},null,8,["onClick"]),[[m,"View sell details"]]),q(s(u,{icon:"pi pi-trash",class:"p-button-danger mt-2",onClick:_=>U(o)},null,8,["onClick"]),[[m,"Reverse sale"]])]),_:1})]),_:1},8,["value"]),t("div",X,[s(x,{header:"Confirmation",visible:v.value,"onUpdate:visible":e[0]||(e[0]=o=>v.value=o),style:{width:"350px"},modal:!0},{footer:r(()=>[s(u,{label:"No",icon:"pi pi-times",onClick:$,class:"p-button-text"}),s(u,{label:"Yes",icon:"pi pi-check",onClick:A,class:"p-button-text",autofocus:""})]),default:r(()=>[e[4]||(e[4]=t("div",{class:"flex align-items-center justify-content-center"},[t("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),t("span",null,"Are you sure you want to proceed?")],-1))]),_:1},8,["visible"])]),s(x,{header:"Sales Details",visible:R.value,"onUpdate:visible":e[2]||(e[2]=o=>R.value=o),style:{width:"33vw"},modal:!0,class:"p-fluid"},{footer:r(()=>[s(u,{label:"Cancel",onClick:e[1]||(e[1]=o=>l.toggleTypeModal(!1)),icon:"pi pi-times",class:"p-button-outlined p-button-danger mr-2 mb-2"}),s(u,{onClick:F,icon:"pi pi-print",label:"Print",class:"p-button-outlined mr-2 mb-2"})]),default:r(()=>[t("div",Z,[t("div",tt,[t("h1",null,a(y.value.institution_name),1)]),t("div",et,[t("span",ot,[t("label",lt,"Ref No.: "+a(y.value.ref_no),1)])]),t("div",it,[t("span",at,[t("label",st,"Receipt No.: "+a(y.value.receipt_no),1)])]),t("div",rt,[t("span",nt,[t("label",dt,"Item Count: "+a(y.value.item_count),1)])]),t("div",pt,[t("span",ct,[t("label",ut,"Total: "+a(c(i).commaSeparator(y.value.total)),1)])]),e[5]||(e[5]=t("hr",null,null,-1))]),t("div",mt,[s(f,{value:n.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:r(()=>[s(d,{field:"Ref No.",header:"Item",style:{"min-width":"10rem"}},{body:r(({data:o})=>[p(a(o.name),1)]),_:1}),s(d,{field:"Receipt No.",header:"Price",style:{"min-width":"10rem"}},{body:r(({data:o})=>[p(a(c(i).commaSeparator(o.price)),1)]),_:1}),s(d,{field:"name",header:"Qty",style:{"min-width":"10rem"}},{body:r(({data:o})=>[p(a(o.qty),1)]),_:1}),s(d,{field:"name",header:"Sub Total",style:{"min-width":"10rem"}},{body:r(({data:o})=>[p(a(c(i).commaSeparator(o.price*o.qty)),1)]),_:1})]),_:1},8,["value"])])]),_:1},8,["visible"]),s(x,{header:"Generate Invoice",visible:C.value,"onUpdate:visible":e[3]||(e[3]=o=>C.value=o),style:{width:"75vw"},modal:!0,class:"p-fluid"},{footer:r(()=>[s(u,{label:"print",onClick:V,icon:"pi pi-print",class:"p-button-outlined"})]),default:r(()=>{var o,_,O,P;return[t("html",gt,[e[17]||(e[17]=t("head",null,[t("meta",{charset:"UTF-8"}),t("meta",{name:"viewport",content:"width=device-width, initial-scale=1.0"}),t("title",null,"Invoice")],-1)),t("body",bt,[t("div",yt,[e[14]||(e[14]=t("header",{style:{display:"flex","justify-content":"space-between","align-items":"center","margin-bottom":"20px"}},[t("h1",{style:{color:"#2B5F2C",margin:"0"}},"Receipt")],-1)),t("section",null,[t("div",xt,[t("div",null,[t("p",ht,[e[6]||(e[6]=t("strong",null,"Company Name:",-1)),p(" "+a((o=N.value)==null?void 0:o.institution_name),1)]),t("p",ft,[e[7]||(e[7]=t("strong",null,"P.O.Box:",-1)),p(" "+a(((_=n.value[0])==null?void 0:_.p_o_box)+" "+((O=n.value[0])==null?void 0:O.institution_address)),1)])]),t("div",vt,[t("p",_t,[e[8]||(e[8]=t("strong",null,"Receipt #:",-1)),p(" "+a((P=n.value[0])==null?void 0:P.receipt_no),1)]),t("p",wt,[e[9]||(e[9]=t("strong",null,"Date:",-1)),p(" "+a(c(i).formatDate(c(i).getCurrentTimestamp())),1)])])])]),t("table",Ct,[e[13]||(e[13]=t("thead",null,[t("tr",null,[t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"left","background-color":"#2B5F2C",color:"#fff"}},"QTY"),t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"left","background-color":"#2B5F2C",color:"#fff"}},"DESCRIPTION"),t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"right","background-color":"#2B5F2C",color:"#fff"}},"UNIT PRICE"),t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"right","background-color":"#2B5F2C",color:"#fff"}},"AMOUNT")])],-1)),t("tbody",null,[(k(!0),S(K,null,G(n.value,(b,Bt)=>(k(),S("tr",{key:b.id},[t("td",Nt,a(b.qty+" "+b.unit),1),t("td",kt,a(b.name),1),t("td",St,a(c(i).commaSeparator(Number(b.price))),1),t("td",Dt,a(c(i).commaSeparator(Number(b.price)*Number(b.qty))),1)]))),128))]),t("tfoot",null,[t("tr",null,[e[10]||(e[10]=t("td",{colspan:"3",style:{border:"1px solid #000",padding:"8px","text-align":"right","font-weight":"bold",color:"#666"}},"Total",-1)),t("td",Rt,a(n.value[0].total?c(i).commaSeparator(Number(n.value[0].total)):0),1)]),t("tr",null,[e[11]||(e[11]=t("td",{colspan:"3",style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},"Amount Paid",-1)),t("td",Tt,a(c(i).commaSeparator(Number(n.value[0].amount_paid))),1)]),t("tr",null,[e[12]||(e[12]=t("td",{colspan:"3",style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},"Change",-1)),t("td",It,a(c(i).commaSeparator(Number(n.value[0].amount_paid)-Number(n.value[0].total))),1)])])]),e[15]||(e[15]=t("div",{style:{"margin-top":"20px",border:"1px solid #000",padding:"10px",background:"#f2f2f2"}},[t("p",{style:{color:"#666"}},[t("strong",null,"Other Comments:")]),t("p",{style:{color:"#666"}},"1. Acknowledged with thanks.")],-1)),e[16]||(e[16]=t("div",{style:{"text-align":"center","margin-top":"30px"}},[t("p",{style:{"font-size":"0.9em",color:"#666"}},"We appreciate your support")],-1))])])])]}),_:1},8,["visible"])])}}};export{Pt as default};
