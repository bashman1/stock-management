import{k as L,l as j,r as x,o as I,b,c as O,d as q,e,h as d,i as m,t as o,f as u,j as c,C as z}from"./index-54b662c4.js";const U={className:"card"},E={class:"line-height-3 m-0"},H={class:"container"},P={class:"receipt-number"},W={class:"item"},F={class:"item-price"},K={class:"item"},$={class:"item-price"},G={class:"item"},J={class:"item-price"},Q={class:"item"},X={class:"item-price"},Y={class:"item"},Z={class:"item-price"},ee={class:"item"},te={class:"item-price"},ie={class:"item"},se={class:"item-price"},oe={class:"thank-you"},ae={class:"grid p-fluid mt-3"},ne={class:"field col-12 md:col-12"},de={class:"my-2 text-900"},le={__name:"ViewCollections",setup(me){L();const n=new z;j();const w=x([]),s=x(null),_=x(!1),S=()=>{n.genericRequest("get-officer-collection","get",!0,{}).then(a=>{a.status&&(w.value=a.data)})},M=()=>{try{n.genericRequest("download-collections-csv","post",!0,{status:"Active"},!0).then(a=>{const t=window.URL.createObjectURL(new Blob([a])),r=document.createElement("a");r.href=t,r.setAttribute("download","officer-collection.csv"),document.body.appendChild(r),r.click()})}catch(a){console.log(a)}finally{}},R=a=>{n.genericRequest("get-transaction-receipt/"+a.tran_id,"get",!0,{}).then(t=>{t.status&&(s.value=t.data)}),_.value=n.toggleModal(!0)},B=()=>{_.value=n.toggleModal(!1),V()},V=()=>{var t,r,g,y,l,f,i,p,v,h;const a=window.open("","","width=600,height=600");a.document.open(),a.document.write(`
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
                <div class="receipt-number">`+n.removeLetters((t=s.value)==null?void 0:t.tran_id)+`</div>
                <h1>`+((r=s.value)==null?void 0:r.institution_name)+`</h1>

                <div class="item">
                    <div class="item-description">Member Name</div>
                    <div class="item-price">`+((g=s.value)==null?void 0:g.member_name)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Member Number</div>
                    <div class="item-price">`+((y=s.value)==null?void 0:y.member_number)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Amount</div>
                    <div class="item-price">`+n.commaSeparator((l=s.value)==null?void 0:l.amount)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Amount in words</div>
                    <div class="item-price">`+n.NumInWords((f=s.value)==null?void 0:f.amount)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Transaction ID</div>
                    <div class="item-price">`+((i=s.value)==null?void 0:i.tran_id)+`</div>
                </div>


                <div class="item">
                    <div class="item-description">Deposited By</div>
                    <div class="item-price">`+((p=s.value)==null?void 0:p.user_name)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Date</div>
                    <div class="item-price">`+((v=s.value)==null?void 0:v.created_at)+`</div>
                </div>

                <p class="thank-you">Thank you for saving with`+((h=s.value)==null?void 0:h.institution_name)+`</p>
                <p class="thank-you">Have a great day!</p>
            </div>
          </div>
        </body>
        </html>
      `),a.document.close(),a.print(),a.close()};return I(()=>{S()}),(a,t)=>{const r=b("Button"),g=b("Dialog"),y=b("Toolbar"),l=b("Column"),f=b("DataTable");return O(),q("div",U,[t[9]||(t[9]=e("h5",null,"View Officers Collections",-1)),d(g,{header:"Receipt",visible:_.value,"onUpdate:visible":t[0]||(t[0]=i=>_.value=i),breakpoints:{"960px":"75vw"},style:{width:"30vw"},modal:!0},{footer:m(()=>[d(r,{label:"print",onClick:B,icon:"pi pi-print",class:"p-button-outlined"})]),default:m(()=>{var i,p,v,h,A,C,k,D,N,T;return[e("p",E,[e("div",H,[e("div",P,o(u(n).removeLetters((i=s.value)==null?void 0:i.tran_id)),1),e("h1",null,o((p=s.value)==null?void 0:p.institution_name),1),e("div",W,[t[1]||(t[1]=e("div",{class:"item-description"},"Member Name",-1)),e("div",F,o((v=s.value)==null?void 0:v.member_name),1)]),e("div",K,[t[2]||(t[2]=e("div",{class:"item-description"},"Member Number",-1)),e("div",$,o((h=s.value)==null?void 0:h.member_number),1)]),e("div",G,[t[3]||(t[3]=e("div",{class:"item-description"},"Amount",-1)),e("div",J,o(u(n).commaSeparator((A=s.value)==null?void 0:A.amount)),1)]),e("div",Q,[t[4]||(t[4]=e("div",{class:"item-description"},"Amount in words",-1)),e("div",X,o(u(n).NumInWords((C=s.value)==null?void 0:C.amount)),1)]),e("div",Y,[t[5]||(t[5]=e("div",{class:"item-description"},"Transaction ID",-1)),e("div",Z,o((k=s.value)==null?void 0:k.tran_id),1)]),e("div",ee,[t[6]||(t[6]=e("div",{class:"item-description"},"Deposited By",-1)),e("div",te,o((D=s.value)==null?void 0:D.user_name),1)]),e("div",ie,[t[7]||(t[7]=e("div",{class:"item-description"},"Date",-1)),e("div",se,o((N=s.value)==null?void 0:N.created_at),1)]),e("p",oe,"Thank you for saving with "+o((T=s.value)==null?void 0:T.institution_name),1),t[8]||(t[8]=e("p",{class:"thank-you"},"Have a great day.",-1))])])]}),_:1},8,["visible"]),e("div",ae,[e("div",ne,[d(y,{class:"mb-4"},{start:m(()=>[e("div",de," Amount Collected: "+o(u(n).commaSeparator(u(n).sumOfAmount(w.value))),1)]),end:m(()=>[d(r,{label:"Export CSV",icon:"pi pi-file-excel",class:"p-button-success",onClick:M})]),_:1}),d(f,{value:w.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:m(()=>[d(l,{field:"name",header:"Name",style:{"min-width":"10rem"}},{body:m(({data:i})=>[c(o(i.member_name),1)]),_:1}),d(l,{header:"Member No.",style:{"min-width":"10rem"}},{body:m(({data:i})=>[c(o(i.member_number),1)]),_:1}),d(l,{header:"Amount",style:{"min-width":"12rem"}},{body:m(({data:i})=>[c(o(u(n).commaSeparator(i.amount)),1)]),_:1}),d(l,{header:"Status",style:{"min-width":"10rem"}},{body:m(({data:i})=>[c(o(i.status),1)]),_:1}),d(l,{header:"Date",style:{"min-width":"10rem"}},{body:m(({data:i})=>[c(o(i.tran_date),1)]),_:1}),d(l,{header:"Officer",style:{"min-width":"10rem"}},{body:m(({data:i})=>[c(o(i.officer_name),1)]),_:1}),d(l,{header:"Transaction ID",style:{"min-width":"10rem"}},{body:m(({data:i})=>[c(o(i.tran_id),1)]),_:1}),d(l,{header:"Receipt",headerStyle:"min-width:5rem;"},{body:m(({data:i})=>[d(r,{icon:"pi pi-print",class:"p-button-rounded p-button-success mr-2",onClick:p=>R(i)},null,8,["onClick"])]),_:1})]),_:1},8,["value"])])])])}}};export{le as default};
