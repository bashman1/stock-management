import{k as I,l as R,r as x,o as j,b as _,c as z,d as O,h as o,i as d,e,t as s,f as p,j as r,C as q}from"./index-e185dda2.js";const H={className:"card"},L=e("h5",null,"View Officers Collections",-1),P={class:"line-height-3 m-0"},W={class:"container"},F={class:"receipt-number"},E={class:"item"},K=e("div",{class:"item-description"},"Member Name",-1),U={class:"item-price"},$={class:"item"},G=e("div",{class:"item-description"},"Member Number",-1),J={class:"item-price"},Q={class:"item"},X=e("div",{class:"item-description"},"Amount",-1),Y={class:"item-price"},Z={class:"item"},ee=e("div",{class:"item-description"},"Amount in words",-1),te={class:"item-price"},ie={class:"item"},se=e("div",{class:"item-description"},"Transaction ID",-1),ae={class:"item-price"},oe={class:"item"},de=e("div",{class:"item-description"},"Deposited By",-1),ne={class:"item-price"},me={class:"item"},ce=e("div",{class:"item-description"},"Date",-1),re={class:"item-price"},le={class:"thank-you"},pe=e("p",{class:"thank-you"},"Have a great day.",-1),he={class:"grid p-fluid mt-3"},ve={class:"field col-12 md:col-12"},ue={class:"my-2"},ge={__name:"ViewCollections",setup(_e){I();const a=new q;R();const f=x([]),i=x(null),b=x(!1),M=()=>{a.genericRequest("get-officer-collection","get",!0,{}).then(n=>{n.status&&(f.value=n.data)})},S=n=>{a.genericRequest("get-transaction-receipt/"+n.tran_id,"get",!0,{}).then(c=>{c.status&&(i.value=c.data)}),b.value=a.toggleModal(!0)},B=()=>{b.value=a.toggleModal(!1),V()},V=()=>{var c,h,g,y,m,w,t,l,v,u;const n=window.open("","","width=600,height=600");n.document.open(),n.document.write(`
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
                <div class="receipt-number">`+a.removeLetters((c=i.value)==null?void 0:c.tran_id)+`</div>
                <h1>`+((h=i.value)==null?void 0:h.institution_name)+`</h1>

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
                    <div class="item-price">`+a.NumInWords((w=i.value)==null?void 0:w.amount)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Transaction ID</div>
                    <div class="item-price">`+((t=i.value)==null?void 0:t.tran_id)+`</div>
                </div>


                <div class="item">
                    <div class="item-description">Deposited By</div>
                    <div class="item-price">`+((l=i.value)==null?void 0:l.user_name)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Date</div>
                    <div class="item-price">`+((v=i.value)==null?void 0:v.created_at)+`</div>
                </div>

                <p class="thank-you">Thank you for saving with`+((u=i.value)==null?void 0:u.institution_name)+`</p>
                <p class="thank-you">Have a great day!</p>
            </div>
          </div>
        </body>
        </html>
      `),n.document.close(),n.print(),n.close()};return j(()=>{M()}),(n,c)=>{const h=_("Button"),g=_("Dialog"),y=_("Toolbar"),m=_("Column"),w=_("DataTable");return z(),O("div",H,[L,o(g,{header:"Receipt",visible:b.value,"onUpdate:visible":c[0]||(c[0]=t=>b.value=t),breakpoints:{"960px":"75vw"},style:{width:"30vw"},modal:!0},{footer:d(()=>[o(h,{label:"print",onClick:B,icon:"pi pi-print",class:"p-button-outlined"})]),default:d(()=>{var t,l,v,u,A,k,C,D,N,T;return[e("p",P,[e("div",W,[e("div",F,s(p(a).removeLetters((t=i.value)==null?void 0:t.tran_id)),1),e("h1",null,s((l=i.value)==null?void 0:l.institution_name),1),e("div",E,[K,e("div",U,s((v=i.value)==null?void 0:v.member_name),1)]),e("div",$,[G,e("div",J,s((u=i.value)==null?void 0:u.member_number),1)]),e("div",Q,[X,e("div",Y,s(p(a).commaSeparator((A=i.value)==null?void 0:A.amount)),1)]),e("div",Z,[ee,e("div",te,s(p(a).NumInWords((k=i.value)==null?void 0:k.amount)),1)]),e("div",ie,[se,e("div",ae,s((C=i.value)==null?void 0:C.tran_id),1)]),e("div",oe,[de,e("div",ne,s((D=i.value)==null?void 0:D.user_name),1)]),e("div",me,[ce,e("div",re,s((N=i.value)==null?void 0:N.created_at),1)]),e("p",le,"Thank you for saving with "+s((T=i.value)==null?void 0:T.institution_name),1),pe])])]}),_:1},8,["visible"]),e("div",he,[e("div",ve,[o(y,{class:"mb-4"},{start:d(()=>[e("div",ue," Amount Collected: "+s(p(a).commaSeparator(p(a).sumOfAmount(f.value))),1)]),end:d(()=>[]),_:1}),o(w,{value:f.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:d(()=>[o(m,{field:"name",header:"Name",style:{"min-width":"10rem"}},{body:d(({data:t})=>[r(s(t.member_name),1)]),_:1}),o(m,{header:"Member No.",style:{"min-width":"10rem"}},{body:d(({data:t})=>[r(s(t.member_number),1)]),_:1}),o(m,{header:"Amount",style:{"min-width":"12rem"}},{body:d(({data:t})=>[r(s(p(a).commaSeparator(t.amount)),1)]),_:1}),o(m,{header:"Status",style:{"min-width":"10rem"}},{body:d(({data:t})=>[r(s(t.status),1)]),_:1}),o(m,{header:"Date",style:{"min-width":"10rem"}},{body:d(({data:t})=>[r(s(t.tran_date),1)]),_:1}),o(m,{header:"Officer",style:{"min-width":"10rem"}},{body:d(({data:t})=>[r(s(t.officer_name),1)]),_:1}),o(m,{header:"Transaction ID",style:{"min-width":"10rem"}},{body:d(({data:t})=>[r(s(t.tran_id),1)]),_:1}),o(m,{header:"Receipt",headerStyle:"min-width:5rem;"},{body:d(({data:t})=>[o(h,{icon:"pi pi-print",class:"p-button-rounded p-button-success mr-2",onClick:l=>S(t)},null,8,["onClick"])]),_:1})]),_:1},8,["value"])])])])}}};export{ge as default};
