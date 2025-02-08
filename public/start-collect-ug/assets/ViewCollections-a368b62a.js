import{k as L,l as j,r as x,o as I,b as _,c as O,d as q,h as n,i as d,e,t as s,f as h,j as l,C as z}from"./index-bd98e245.js";const U={className:"card"},E=e("h5",null,"View Officers Collections",-1),H={class:"line-height-3 m-0"},P={class:"container"},W={class:"receipt-number"},F={class:"item"},K=e("div",{class:"item-description"},"Member Name",-1),$={class:"item-price"},G={class:"item"},J=e("div",{class:"item-description"},"Member Number",-1),Q={class:"item-price"},X={class:"item"},Y=e("div",{class:"item-description"},"Amount",-1),Z={class:"item-price"},ee={class:"item"},te=e("div",{class:"item-description"},"Amount in words",-1),ie={class:"item-price"},se={class:"item"},oe=e("div",{class:"item-description"},"Transaction ID",-1),ae={class:"item-price"},ne={class:"item"},de=e("div",{class:"item-description"},"Deposited By",-1),ce={class:"item-price"},me={class:"item"},re=e("div",{class:"item-description"},"Date",-1),le={class:"item-price"},pe={class:"thank-you"},he=e("p",{class:"thank-you"},"Have a great day.",-1),ue={class:"grid p-fluid mt-3"},ve={class:"field col-12 md:col-12"},_e={class:"my-2 text-900"},ye={__name:"ViewCollections",setup(be){L();const a=new z;j();const w=x([]),i=x(null),b=x(!1),S=()=>{a.genericRequest("get-officer-collection","get",!0,{}).then(o=>{o.status&&(w.value=o.data)})},M=()=>{try{a.genericRequest("download-collections-csv","post",!0,{status:"Active"},!0).then(o=>{const r=window.URL.createObjectURL(new Blob([o])),c=document.createElement("a");c.href=r,c.setAttribute("download","officer-collection.csv"),document.body.appendChild(c),c.click()})}catch(o){console.log(o)}finally{}},R=o=>{a.genericRequest("get-transaction-receipt/"+o.tran_id,"get",!0,{}).then(r=>{r.status&&(i.value=r.data)}),b.value=a.toggleModal(!0)},B=()=>{b.value=a.toggleModal(!1),V()},V=()=>{var r,c,g,y,m,f,t,p,u,v;const o=window.open("","","width=600,height=600");o.document.open(),o.document.write(`
        <html>
        <head>
          <title>Print</title>
        </head>
        <style>
        .container {
    border: 1px solid #000;
    padding: 20px;
    max-width: 600px;
    margin: 0 auto;
    font-family: "Courier New", monospace;
}
h1 {
    text-align: center;
}
.section {
    margin-bottom: 10px;
}
.section-title {
    font-weight: bold;
}
.item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
}
.item-description {
    flex: 2;
}
.item-quantity {
    flex: 1;
    text-align: right;
}
.item-price {
    flex: 1;
    text-align: right;
}
.total {
    text-align: right;
    font-weight: bold;
}
.thank-you {
    text-align: center;
}
.receipt-number {
    position: absolute;
    color: red;
    right: 40px;
}
@media print {
    body {
        font-size: 12pt; /* Adjust font size for readability */
    }
    .container {
        border: 1px solid #000;
        padding: 20px;
        max-width: 100%; /* Adjust to fit the printer width */
        margin: 0;
        font-family: "Courier New", monospace;
    }
    /* Add other print-specific styles as needed */
}

@page { margin: 0 }
body { margin: 0 }
.sheet {
  margin: 0;
  overflow: hidden;
  position: relative;
  box-sizing: border-box;
  page-break-after: always;
}

/** Paper sizes **/
body.A3           .sheet { width: 297mm; height: 419mm }
body.A3.landscape .sheet { width: 420mm; height: 296mm }
body.A4           .sheet { width: 210mm; height: 296mm }
body.A4.landscape .sheet { width: 297mm; height: 209mm }
body.A5           .sheet { width: 148mm; height: 209mm }
body.A5.landscape .sheet { width: 210mm; height: 147mm }

/** Padding area **/
.sheet.padding-4mm { padding: 4mm }
.sheet.padding-10mm { padding: 10mm }
.sheet.padding-15mm { padding: 15mm }
.sheet.padding-20mm { padding: 20mm }
.sheet.padding-25mm { padding: 25mm }

/** For screen preview **/
@media screen {
  body { background: #e0e0e0 }
  .sheet {
    background: white;
    box-shadow: 0 .5mm 2mm rgba(0,0,0,.3);
    margin: 5mm;
  }
}

/** Fix for Chrome issue #273306 **/
@media print {
           body.A3.landscape { width: 420mm }
  body.A3, body.A4.landscape { width: 297mm }
  body.A4, body.A5.landscape { width: 210mm }
  body.A5                    { width: 148mm }
}
        </style>
        <body class="A5 landscape">
          <div class="print-content">
            <div class="container">
                <div class="receipt-number">`+a.removeLetters((r=i.value)==null?void 0:r.tran_id)+`</div>
                <h1>`+((c=i.value)==null?void 0:c.institution_name)+`</h1>

                <div class="item">
                    <div class="item-description">Member Name</div>
                    <div class="item-price">`+((g=i.value)==null?void 0:g.member_name)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Member Number</div>
                    <div class="item-price">`+((y=i.value)==null?void 0:y.member_number)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Amount</div>
                    <div class="item-price">`+a.commaSeparator((m=i.value)==null?void 0:m.amount)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Amount in words</div>
                    <div class="item-price">`+a.NumInWords((f=i.value)==null?void 0:f.amount)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Transaction ID</div>
                    <div class="item-price">`+((t=i.value)==null?void 0:t.tran_id)+`</div>
                </div>


                <div class="item">
                    <div class="item-description">Deposited By</div>
                    <div class="item-price">`+((p=i.value)==null?void 0:p.user_name)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Date</div>
                    <div class="item-price">`+((u=i.value)==null?void 0:u.created_at)+`</div>
                </div>

                <p class="thank-you">Thank you for saving with`+((v=i.value)==null?void 0:v.institution_name)+`</p>
                <p class="thank-you">Have a great day!</p>
            </div>
          </div>
        </body>
        </html>
      `),o.document.close(),o.print(),o.close()};return I(()=>{S()}),(o,r)=>{const c=_("Button"),g=_("Dialog"),y=_("Toolbar"),m=_("Column"),f=_("DataTable");return O(),q("div",U,[E,n(g,{header:"Receipt",visible:b.value,"onUpdate:visible":r[0]||(r[0]=t=>b.value=t),breakpoints:{"960px":"75vw"},style:{width:"30vw"},modal:!0},{footer:d(()=>[n(c,{label:"print",onClick:B,icon:"pi pi-print",class:"p-button-outlined"})]),default:d(()=>{var t,p,u,v,A,C,k,D,N,T;return[e("p",H,[e("div",P,[e("div",W,s(h(a).removeLetters((t=i.value)==null?void 0:t.tran_id)),1),e("h1",null,s((p=i.value)==null?void 0:p.institution_name),1),e("div",F,[K,e("div",$,s((u=i.value)==null?void 0:u.member_name),1)]),e("div",G,[J,e("div",Q,s((v=i.value)==null?void 0:v.member_number),1)]),e("div",X,[Y,e("div",Z,s(h(a).commaSeparator((A=i.value)==null?void 0:A.amount)),1)]),e("div",ee,[te,e("div",ie,s(h(a).NumInWords((C=i.value)==null?void 0:C.amount)),1)]),e("div",se,[oe,e("div",ae,s((k=i.value)==null?void 0:k.tran_id),1)]),e("div",ne,[de,e("div",ce,s((D=i.value)==null?void 0:D.user_name),1)]),e("div",me,[re,e("div",le,s((N=i.value)==null?void 0:N.created_at),1)]),e("p",pe,"Thank you for saving with "+s((T=i.value)==null?void 0:T.institution_name),1),he])])]}),_:1},8,["visible"]),e("div",ue,[e("div",ve,[n(y,{class:"mb-4"},{start:d(()=>[e("div",_e," Amount Collected: "+s(h(a).commaSeparator(h(a).sumOfAmount(w.value))),1)]),end:d(()=>[n(c,{label:"Export CSV",icon:"pi pi-file-excel",class:"p-button-success",onClick:M})]),_:1}),n(f,{value:w.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:d(()=>[n(m,{field:"name",header:"Name",style:{"min-width":"10rem"}},{body:d(({data:t})=>[l(s(t.member_name),1)]),_:1}),n(m,{header:"Member No.",style:{"min-width":"10rem"}},{body:d(({data:t})=>[l(s(t.member_number),1)]),_:1}),n(m,{header:"Amount",style:{"min-width":"12rem"}},{body:d(({data:t})=>[l(s(h(a).commaSeparator(t.amount)),1)]),_:1}),n(m,{header:"Status",style:{"min-width":"10rem"}},{body:d(({data:t})=>[l(s(t.status),1)]),_:1}),n(m,{header:"Date",style:{"min-width":"10rem"}},{body:d(({data:t})=>[l(s(t.tran_date),1)]),_:1}),n(m,{header:"Officer",style:{"min-width":"10rem"}},{body:d(({data:t})=>[l(s(t.officer_name),1)]),_:1}),n(m,{header:"Transaction ID",style:{"min-width":"10rem"}},{body:d(({data:t})=>[l(s(t.tran_id),1)]),_:1}),n(m,{header:"Receipt",headerStyle:"min-width:5rem;"},{body:d(({data:t})=>[n(c,{icon:"pi pi-print",class:"p-button-rounded p-button-success mr-2",onClick:p=>R(t)},null,8,["onClick"])]),_:1})]),_:1},8,["value"])])])])}}};export{ye as default};
