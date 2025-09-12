import{k as L,r as g,l as Q,m as W,o as H,b as w,p as K,c as k,d as S,e as t,h as s,i as n,C as G,j as d,t as a,f as c,q as j,F as J,s as X}from"./index-163291c5.js";const Z={className:"card"},tt={class:"card p-fluid"},et={class:"grid p-fluid"},ot={class:"field col-12 md:col-12"},lt={class:"field col-6 md:col-6"},it={class:"p-float-label"},at={for:"unitName"},st={class:"field col-6 md:col-6"},rt={class:"p-float-label"},nt={for:"unitName"},dt={class:"field col-6 md:col-6"},pt={class:"p-float-label"},ct={for:"unitName"},ut={class:"field col-6 md:col-6"},mt={class:"p-float-label"},gt={for:"unitName"},yt={class:"grid p-fluid"},bt={lang:"en"},xt={style:{"background-color":"#fff"}},ht={style:{"max-width":"750px","font-family":"monospace",margin:"20px auto",padding:"30px",border:"1px solid #000","box-shadow":"0 2px 8px rgba(0, 0, 0, 0.1)",background:"#fff"}},ft={style:{display:"flex","justify-content":"space-between"}},vt={style:{color:"#666"}},_t={style:{color:"#666"}},wt={style:{color:"#666"}},Ct={style:{"text-align":"right"}},Nt={style:{color:"#666"}},kt={style:{color:"#666"}},St={style:{width:"100%","border-collapse":"collapse","margin-top":"10px"}},Dt={style:{border:"1px solid #000",padding:"8px",color:"#666"}},Rt={style:{border:"1px solid #000",padding:"8px",color:"#666"}},Tt={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},It={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},$t={style:{border:"1px solid #000",padding:"8px","text-align":"right","font-weight":"bold",color:"#666"}},Bt={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},Ot={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},jt={__name:"ViewOrders",setup(Pt){const h=L(),D=g(null),R=g(!1),r=g(null),i=new G,b=g({});Q(),W();const _=g(!1),T=g(null),C=g(!1),N=g({}),I=()=>{i.genericRequest("get-orders","get",!0,{}).then(l=>{l.status?D.value=l.data:i.showError(h,l.message)})},A=l=>{b.value=l,F(l==null?void 0:l.id)},F=l=>{i.genericRequest("get-orders-details/"+l,"get",!0,{}).then(e=>{e.status?(r.value=e.data,B(!0)):i.showError(h,e.message)})},U=()=>{i.genericRequest("reverse-sale","post",!0,T.value).then(l=>{$(),l.status?(i.showSuccess(h,l.message),I()):i.showError(h,l.message)})},M=()=>{i.genericRequest("print-receipt","post",!0,{}).then(l=>{l.status?r.value=l.data:i.showError(h,l.message)})},V=l=>{_.value=!0;let e={id:l.id};T.value=e},$=()=>{_.value=!0},B=l=>{C.value=l},Y=()=>{var p,m,f,x,v,o;const l=window.open("","","width=600,height=600");l.document.open();let e="";r.value.forEach(u=>{e+=`
      <tr>
        <td style='border: 1px solid #000; padding: 8px; color: #666;'>${u.qty+" "+u.unit}</td>
        <td style='border: 1px solid #000; padding: 8px; color: #666;'>${u.name}</td>
        <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #666;'>${i.commaSeparator(Number(u.price))}</td>
        <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #666;'>${i.commaSeparator(Number(u.price)*Number(u.qty))}</td>
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
          <p style="color: #000;"><strong>Company Name:</strong> ${(p=N.value)==null?void 0:p.institution_name}</p>
          <p style="color: #000;"><strong>P.O.Box:</strong> ${((m=r.value[0])==null?void 0:m.p_o_box)+" "+((f=r.value[0])==null?void 0:f.institution_address)}</p>
          <p style="color: #000;"><strong>Customer:</strong> ${(x=r.value[0])!=null&&x.customer_name?(v=r.value[0])==null?void 0:v.customer_name:"Cash"}</p>
        </div>
        <div style='text-align: right;'>
          <p style="color: #000;"><strong>Receipt #:</strong> ${(o=r.value[0])==null?void 0:o.receipt_no}</p>
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
          <td style='border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; color: #000;'>${r.value[0].total?i.commaSeparator(Number(r.value[0].total)):0}</td>
        </tr>
        <tr>
          <td colspan='3' style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>Amount Paid</td>
          <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>${i.commaSeparator(Number(r.value[0].amount_paid))}</td>
        </tr>
        <tr>
          <td colspan='3' style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>Change</td>
          <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>${i.commaSeparator(Number(r.value[0].amount_paid)-Number(r.value[0].total))}</td>
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
</html>`),l.document.close(),l.onload=function(){l.focus(),l.print(),l.close()}},z=()=>{B(!1),Y()};return H(()=>{N.value=i.getStorage().userData,I()}),(l,e)=>{const p=w("Column"),m=w("Button"),f=w("DataTable"),x=w("Dialog"),v=K("tooltip");return k(),S("div",Z,[e[19]||(e[19]=t("h5",null,"View Sales",-1)),s(f,{value:D.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:n(()=>[s(p,{field:"Ref No.",header:"Ref No.",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(o.ref_no),1)]),_:1}),s(p,{field:"Receipt No.",header:"Receipt No.",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(o.receipt_no),1)]),_:1}),s(p,{field:"name",header:"Item Count",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(o.item_count),1)]),_:1}),s(p,{field:"name",header:"Amount Paid",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(c(i).commaSeparator(o.amount_paid)),1)]),_:1}),s(p,{field:"name",header:"Cost",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(c(i).commaSeparator(o.total)),1)]),_:1}),s(p,{field:"name",header:"Change",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(c(i).commaSeparator(o.amount_paid-o.total)),1)]),_:1}),s(p,{header:"Discount",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(c(i).commaSeparator(o.discount)),1)]),_:1}),s(p,{header:"Status",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(o.payment_status),1)]),_:1}),s(p,{header:"Date",style:{"max-width":"10rem"}},{body:n(({data:o})=>[d(a(o.created_at),1)]),_:1}),s(p,{headerStyle:"min-width:10rem;"},{body:n(({data:o})=>[j(s(m,{icon:"pi pi-eye",class:"p-button-primary mr-2",onClick:u=>A(o)},null,8,["onClick"]),[[v,"View sell details"]]),j(s(m,{icon:"pi pi-trash",class:"p-button-danger mt-2",onClick:u=>V(o)},null,8,["onClick"]),[[v,"Reverse sale"]])]),_:1})]),_:1},8,["value"]),t("div",tt,[s(x,{header:"Confirmation",visible:_.value,"onUpdate:visible":e[0]||(e[0]=o=>_.value=o),style:{width:"350px"},modal:!0},{footer:n(()=>[s(m,{label:"No",icon:"pi pi-times",onClick:$,class:"p-button-text"}),s(m,{label:"Yes",icon:"pi pi-check",onClick:U,class:"p-button-text",autofocus:""})]),default:n(()=>[e[4]||(e[4]=t("div",{class:"flex align-items-center justify-content-center"},[t("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),t("span",null,"Are you sure you want to proceed?")],-1))]),_:1},8,["visible"])]),s(x,{header:"Sales Details",visible:R.value,"onUpdate:visible":e[2]||(e[2]=o=>R.value=o),style:{width:"33vw"},modal:!0,class:"p-fluid"},{footer:n(()=>[s(m,{label:"Cancel",onClick:e[1]||(e[1]=o=>l.toggleTypeModal(!1)),icon:"pi pi-times",class:"p-button-outlined p-button-danger mr-2 mb-2"}),s(m,{onClick:M,icon:"pi pi-print",label:"Print",class:"p-button-outlined mr-2 mb-2"})]),default:n(()=>[t("div",et,[t("div",ot,[t("h1",null,a(b.value.institution_name),1)]),t("div",lt,[t("span",it,[t("label",at,"Ref No.: "+a(b.value.ref_no),1)])]),t("div",st,[t("span",rt,[t("label",nt,"Receipt No.: "+a(b.value.receipt_no),1)])]),t("div",dt,[t("span",pt,[t("label",ct,"Item Count: "+a(b.value.item_count),1)])]),t("div",ut,[t("span",mt,[t("label",gt,"Total: "+a(c(i).commaSeparator(b.value.total)),1)])]),e[5]||(e[5]=t("hr",null,null,-1))]),t("div",yt,[s(f,{value:r.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:n(()=>[s(p,{field:"Ref No.",header:"Item",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(o.name),1)]),_:1}),s(p,{field:"Receipt No.",header:"Price",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(c(i).commaSeparator(o.price)),1)]),_:1}),s(p,{field:"name",header:"Qty",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(o.qty),1)]),_:1}),s(p,{field:"name",header:"Sub Total",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(c(i).commaSeparator(o.price*o.qty)),1)]),_:1})]),_:1},8,["value"])])]),_:1},8,["visible"]),s(x,{header:"Generate Invoice",visible:C.value,"onUpdate:visible":e[3]||(e[3]=o=>C.value=o),style:{width:"75vw"},modal:!0,class:"p-fluid"},{footer:n(()=>[s(m,{label:"print",onClick:z,icon:"pi pi-print",class:"p-button-outlined"})]),default:n(()=>{var o,u,O,P,q,E;return[t("html",bt,[e[18]||(e[18]=t("head",null,[t("meta",{charset:"UTF-8"}),t("meta",{name:"viewport",content:"width=device-width, initial-scale=1.0"}),t("title",null,"Invoice")],-1)),t("body",xt,[t("div",ht,[e[15]||(e[15]=t("header",{style:{display:"flex","justify-content":"space-between","align-items":"center","margin-bottom":"20px"}},[t("h1",{style:{color:"#2B5F2C",margin:"0"}},"Receipt")],-1)),t("section",null,[t("div",ft,[t("div",null,[t("p",vt,[e[6]||(e[6]=t("strong",null,"Company Name:",-1)),d(" "+a((o=N.value)==null?void 0:o.institution_name),1)]),t("p",_t,[e[7]||(e[7]=t("strong",null,"P.O.Box:",-1)),d(" "+a(((u=r.value[0])==null?void 0:u.p_o_box)+" "+((O=r.value[0])==null?void 0:O.institution_address)),1)]),t("p",wt,[e[8]||(e[8]=t("strong",null,"Customer:",-1)),d(" "+a((P=r.value[0])!=null&&P.customer_name?(q=r.value[0])==null?void 0:q.customer_name:"Cash"),1)])]),t("div",Ct,[t("p",Nt,[e[9]||(e[9]=t("strong",null,"Receipt #:",-1)),d(" "+a((E=r.value[0])==null?void 0:E.receipt_no),1)]),t("p",kt,[e[10]||(e[10]=t("strong",null,"Date:",-1)),d(" "+a(c(i).formatDate(c(i).getCurrentTimestamp())),1)])])])]),t("table",St,[e[14]||(e[14]=t("thead",null,[t("tr",null,[t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"left","background-color":"#2B5F2C",color:"#fff"}},"QTY"),t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"left","background-color":"#2B5F2C",color:"#fff"}},"DESCRIPTION"),t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"right","background-color":"#2B5F2C",color:"#fff"}},"UNIT PRICE"),t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"right","background-color":"#2B5F2C",color:"#fff"}},"AMOUNT")])],-1)),t("tbody",null,[(k(!0),S(J,null,X(r.value,(y,qt)=>(k(),S("tr",{key:y.id},[t("td",Dt,a(y.qty+" "+y.unit),1),t("td",Rt,a(y.name),1),t("td",Tt,a(c(i).commaSeparator(Number(y.price))),1),t("td",It,a(c(i).commaSeparator(Number(y.price)*Number(y.qty))),1)]))),128))]),t("tfoot",null,[t("tr",null,[e[11]||(e[11]=t("td",{colspan:"3",style:{border:"1px solid #000",padding:"8px","text-align":"right","font-weight":"bold",color:"#666"}},"Total",-1)),t("td",$t,a(r.value[0].total?c(i).commaSeparator(Number(r.value[0].total)):0),1)]),t("tr",null,[e[12]||(e[12]=t("td",{colspan:"3",style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},"Amount Paid",-1)),t("td",Bt,a(c(i).commaSeparator(Number(r.value[0].amount_paid))),1)]),t("tr",null,[e[13]||(e[13]=t("td",{colspan:"3",style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},"Change",-1)),t("td",Ot,a(c(i).commaSeparator(Number(r.value[0].amount_paid)-Number(r.value[0].total))),1)])])]),e[16]||(e[16]=t("div",{style:{"margin-top":"20px",border:"1px solid #000",padding:"10px",background:"#f2f2f2"}},[t("p",{style:{color:"#666"}},[t("strong",null,"Other Comments:")]),t("p",{style:{color:"#666"}},"1. Acknowledged with thanks.")],-1)),e[17]||(e[17]=t("div",{style:{"text-align":"center","margin-top":"30px"}},[t("p",{style:{"font-size":"0.9em",color:"#666"}},"We appreciate your support")],-1))])])])]}),_:1},8,["visible"])])}}};export{jt as default};
