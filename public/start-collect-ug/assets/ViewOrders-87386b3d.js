import{k as Z,r as g,l as tt,m as et,o as ot,b as D,p as lt,c as k,d as $,e as t,h as r,i as n,C as it,j as d,t as a,f as c,q as R,s as L,F as at,v as st}from"./index-5e39ff43.js";const rt={className:"card"},nt={class:"card p-fluid"},dt={class:"grid p-fluid"},pt={class:"field col-12 md:col-12"},ct={class:"field col-6 md:col-6"},ut={class:"p-float-label"},mt={for:"unitName"},gt={class:"field col-6 md:col-6"},yt={class:"p-float-label"},bt={for:"unitName"},ht={class:"field col-6 md:col-6"},ft={class:"p-float-label"},xt={for:"unitName"},vt={class:"field col-6 md:col-6"},_t={class:"p-float-label"},wt={for:"unitName"},Ct={class:"grid p-fluid"},Nt={lang:"en"},kt={style:{"background-color":"#fff"}},St={style:{"max-width":"750px","font-family":"monospace",margin:"20px auto",padding:"30px",border:"1px solid #000","box-shadow":"0 2px 8px rgba(0, 0, 0, 0.1)",background:"#fff"}},Dt={style:{display:"flex","justify-content":"space-between"}},Rt={style:{color:"#666"}},Tt={style:{color:"#666"}},It={style:{color:"#666"}},Pt={style:{"text-align":"right"}},$t={style:{color:"#666"}},Bt={style:{color:"#666"}},Ot={style:{width:"100%","border-collapse":"collapse","margin-top":"10px"}},qt={style:{border:"1px solid #000",padding:"8px",color:"#666"}},Et={style:{border:"1px solid #000",padding:"8px",color:"#666"}},At={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},jt={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},Ft={style:{border:"1px solid #000",padding:"8px","text-align":"right","font-weight":"bold",color:"#666"}},Ut={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},Mt={style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},Lt={__name:"ViewOrders",setup(Vt){const f=Z(),B=g(null),O=g(!1),s=g(null),i=new it,x=g({});tt(),et();const S=g(!1),q=g(null),T=g(!1),E=g(null),I=g({}),A=()=>{i.genericRequest("get-orders","get",!0,{}).then(l=>{l.status?B.value=l.data:i.showError(f,l.message)})},Q=l=>{x.value=l,W(l==null?void 0:l.id)},W=l=>{i.genericRequest("get-orders-details/"+l,"get",!0,{}).then(e=>{e.status?(s.value=e.data,P(!0)):i.showError(f,e.message)})},H=()=>{i.genericRequest("reverse-sale","post",!0,q.value).then(l=>{F(),l.status?(i.showSuccess(f,l.message),A()):i.showError(f,l.message)})},j=()=>{const l={items:s.value};P(!1),i.genericRequest("print-receipt","post",!0,l).then(e=>{e.status?s.value=e.data:i.showError(f,e.message)})},K=()=>{i.genericRequest("get-printer-configs","get",!0,{}).then(l=>{l.status?E.value=l.data:i.showError(f,l.message)})},G=l=>{S.value=!0;let e={id:l.id};q.value=e},F=()=>{S.value=!0},P=l=>{T.value=l},J=()=>{var p,u,_,v,y,o,b,w,C,N;const l=window.open("","","width=600,height=600");l.document.open();let e="";s.value.forEach(m=>{e+=`
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
          <p style="color: #000;"><strong>Company Name:</strong> ${(p=I.value)==null?void 0:p.institution_name}</p>
          <p style="color: #000;"><strong>P.O.Box:</strong> ${((u=s.value[0])==null?void 0:u.p_o_box)+" "+((_=s.value[0])==null?void 0:_.institution_address)}</p>
          <p style="color: #000;"><strong>Customer:</strong> ${(v=s[0])!=null&&v.customer_name?(y=s.value[0])==null?void 0:y.customer_name:"Cash"}</p>
        </div>
        <div style='text-align: right;'>
          <p style="color: #000;"><strong>Receipt #:</strong> ${(o=s.value[0])==null?void 0:o.receipt_no}</p>
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
          <td style='border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; color: #000;'>${s.value[0].total?i.commaSeparator(Number((b=s.value[0])==null?void 0:b.total)):0}</td>
        </tr>
        <tr>
          <td colspan='3' style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>Amount Paid</td>
          <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>${i.commaSeparator(Number((w=s.value[0])==null?void 0:w.amount_paid))}</td>
        </tr>
        <tr>
          <td colspan='3' style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>Change</td>
          <td style='border: 1px solid #000; padding: 8px; text-align: right; color: #000;'>${i.commaSeparator(Number((C=s.value[0])==null?void 0:C.amount_paid)-Number((N=s.value[0])==null?void 0:N.total))}</td>
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
</html>`),l.document.close(),l.onload=function(){l.focus(),l.print(),l.close()}},X=()=>{P(!1),J()};return ot(()=>{I.value=i.getStorage().userData,A(),K()}),(l,e)=>{const p=D("Column"),u=D("Button"),_=D("DataTable"),v=D("Dialog"),y=lt("tooltip");return k(),$("div",rt,[e[19]||(e[19]=t("h5",null,"View Sales",-1)),r(_,{value:B.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:n(()=>[r(p,{field:"Ref No.",header:"Ref No.",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(o.ref_no),1)]),_:1}),r(p,{field:"Receipt No.",header:"Receipt No.",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(o.receipt_no),1)]),_:1}),r(p,{field:"name",header:"Item Count",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(o.item_count),1)]),_:1}),r(p,{field:"name",header:"Amount Paid",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(c(i).commaSeparator(o.amount_paid)),1)]),_:1}),r(p,{field:"name",header:"Cost",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(c(i).commaSeparator(o.total)),1)]),_:1}),r(p,{field:"name",header:"Change",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(c(i).commaSeparator(o.amount_paid-o.total)),1)]),_:1}),r(p,{header:"Discount",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(c(i).commaSeparator(o.discount)),1)]),_:1}),r(p,{header:"Status",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(o.payment_status),1)]),_:1}),r(p,{header:"Date",style:{"max-width":"10rem"}},{body:n(({data:o})=>[d(a(o.created_at),1)]),_:1}),r(p,{headerStyle:"min-width:10rem;"},{body:n(({data:o})=>[R(r(u,{icon:"pi pi-eye",class:"p-button-primary mr-2",onClick:b=>Q(o)},null,8,["onClick"]),[[y,"View sell details"]]),R(r(u,{icon:"pi pi-trash",class:"p-button-danger mr-2",onClick:b=>G(o)},null,8,["onClick"]),[[y,"Reverse sale"]])]),_:1})]),_:1},8,["value"]),t("div",nt,[r(v,{header:"Confirmation",visible:S.value,"onUpdate:visible":e[0]||(e[0]=o=>S.value=o),style:{width:"350px"},modal:!0},{footer:n(()=>[r(u,{label:"No",icon:"pi pi-times",onClick:F,class:"p-button-text"}),r(u,{label:"Yes",icon:"pi pi-check",onClick:H,class:"p-button-text",autofocus:""})]),default:n(()=>[e[4]||(e[4]=t("div",{class:"flex align-items-center justify-content-center"},[t("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}}),t("span",null,"Are you sure you want to proceed?")],-1))]),_:1},8,["visible"])]),r(v,{header:"Sales Details",visible:O.value,"onUpdate:visible":e[2]||(e[2]=o=>O.value=o),style:{width:"33vw"},modal:!0,class:"p-fluid"},{footer:n(()=>[r(u,{label:"Cancel",onClick:e[1]||(e[1]=o=>l.toggleTypeModal(!1)),icon:"pi pi-times",class:"p-button-outlined p-button-danger mr-2 mb-2"}),r(u,{onClick:j,icon:"pi pi-print",label:"Print",class:"p-button-outlined mr-2 mb-2"})]),default:n(()=>[t("div",dt,[t("div",pt,[t("h1",null,a(x.value.institution_name),1)]),t("div",ct,[t("span",ut,[t("label",mt,"Ref No.: "+a(x.value.ref_no),1)])]),t("div",gt,[t("span",yt,[t("label",bt,"Receipt No.: "+a(x.value.receipt_no),1)])]),t("div",ht,[t("span",ft,[t("label",xt,"Item Count: "+a(x.value.item_count),1)])]),t("div",vt,[t("span",_t,[t("label",wt,"Total: "+a(c(i).commaSeparator(x.value.total)),1)])]),e[5]||(e[5]=t("hr",null,null,-1))]),t("div",Ct,[r(_,{value:s.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:n(()=>[r(p,{field:"Ref No.",header:"Item",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(o.name),1)]),_:1}),r(p,{field:"Receipt No.",header:"Price",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(c(i).commaSeparator(o.price)),1)]),_:1}),r(p,{field:"name",header:"Qty",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(o.qty),1)]),_:1}),r(p,{field:"name",header:"Sub Total",style:{"min-width":"10rem"}},{body:n(({data:o})=>[d(a(c(i).commaSeparator(o.price*o.qty)),1)]),_:1})]),_:1},8,["value"])])]),_:1},8,["visible"]),r(v,{header:"Generate Invoice",visible:T.value,"onUpdate:visible":e[3]||(e[3]=o=>T.value=o),style:{width:"75vw"},modal:!0,class:"p-fluid"},{footer:n(()=>{var o;return[((o=E.value)==null?void 0:o.paper)=="Thermal"?R((k(),L(u,{key:0,label:"print",onClick:j,icon:"pi pi-print",class:"p-button-outlined"},null,512)),[[y,"Printing on Thermal printer 80mm Paper size"]]):R((k(),L(u,{key:1,label:"print",onClick:X,icon:"pi pi-print",class:"p-button-outlined"},null,512)),[[y,"Printing on A4 Paper size"]])]}),default:n(()=>{var o,b,w,C,N,m,U,M,V,z,Y;return[t("html",Nt,[e[18]||(e[18]=t("head",null,[t("meta",{charset:"UTF-8"}),t("meta",{name:"viewport",content:"width=device-width, initial-scale=1.0"}),t("title",null,"Invoice")],-1)),t("body",kt,[t("div",St,[e[15]||(e[15]=t("header",{style:{display:"flex","justify-content":"space-between","align-items":"center","margin-bottom":"20px"}},[t("h1",{style:{color:"#2B5F2C",margin:"0"}},"Receipt")],-1)),t("section",null,[t("div",Dt,[t("div",null,[t("p",Rt,[e[6]||(e[6]=t("strong",null,"Company Name:",-1)),d(" "+a((o=I.value)==null?void 0:o.institution_name),1)]),t("p",Tt,[e[7]||(e[7]=t("strong",null,"P.O.Box:",-1)),d(" "+a(((b=s.value[0])==null?void 0:b.p_o_box)+" "+((w=s.value[0])==null?void 0:w.institution_address)),1)]),t("p",It,[e[8]||(e[8]=t("strong",null,"Customer:",-1)),d(" "+a((C=s.value[0])!=null&&C.customer_name?(N=s.value[0])==null?void 0:N.customer_name:"Cash"),1)])]),t("div",Pt,[t("p",$t,[e[9]||(e[9]=t("strong",null,"Receipt #:",-1)),d(" "+a((m=s.value[0])==null?void 0:m.receipt_no),1)]),t("p",Bt,[e[10]||(e[10]=t("strong",null,"Date:",-1)),d(" "+a(c(i).formatDate(c(i).getCurrentTimestamp())),1)])])])]),t("table",Ot,[e[14]||(e[14]=t("thead",null,[t("tr",null,[t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"left","background-color":"#2B5F2C",color:"#fff"}},"QTY"),t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"left","background-color":"#2B5F2C",color:"#fff"}},"DESCRIPTION"),t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"right","background-color":"#2B5F2C",color:"#fff"}},"UNIT PRICE"),t("th",{style:{border:"1px solid #000",padding:"8px","text-align":"right","background-color":"#2B5F2C",color:"#fff"}},"AMOUNT")])],-1)),t("tbody",null,[(k(!0),$(at,null,st(s.value,(h,zt)=>(k(),$("tr",{key:h.id},[t("td",qt,a(h.qty+" "+h.unit),1),t("td",Et,a(h.name),1),t("td",At,a(c(i).commaSeparator(Number(h.price))),1),t("td",jt,a(c(i).commaSeparator(Number(h.price)*Number(h.qty))),1)]))),128))]),t("tfoot",null,[t("tr",null,[e[11]||(e[11]=t("td",{colspan:"3",style:{border:"1px solid #000",padding:"8px","text-align":"right","font-weight":"bold",color:"#666"}},"Total",-1)),t("td",Ft,a((U=s.value[0])!=null&&U.total?c(i).commaSeparator(Number((M=s.value[0])==null?void 0:M.total)):0),1)]),t("tr",null,[e[12]||(e[12]=t("td",{colspan:"3",style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},"Amount Paid",-1)),t("td",Ut,a(c(i).commaSeparator(Number((V=s.value[0])==null?void 0:V.amount_paid))),1)]),t("tr",null,[e[13]||(e[13]=t("td",{colspan:"3",style:{border:"1px solid #000",padding:"8px","text-align":"right",color:"#666"}},"Change",-1)),t("td",Mt,a(c(i).commaSeparator(Number((z=s.value[0])==null?void 0:z.amount_paid)-Number((Y=s.value[0])==null?void 0:Y.total))),1)])])]),e[16]||(e[16]=t("div",{style:{"margin-top":"20px",border:"1px solid #000",padding:"10px",background:"#f2f2f2"}},[t("p",{style:{color:"#666"}},[t("strong",null,"Other Comments:")]),t("p",{style:{color:"#666"}},"1. Acknowledged with thanks.")],-1)),e[17]||(e[17]=t("div",{style:{"text-align":"center","margin-top":"30px"}},[t("p",{style:{"font-size":"0.9em",color:"#666"}},"We appreciate your support")],-1))])])])]}),_:1},8,["visible"])])}}};export{Lt as default};
