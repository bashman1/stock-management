import{h as I,i as R,r as x,o as j,b as h,c as O,d as q,f as a,j as n,e,t as s,g as v,k as l,C as H}from"./index-e85b5bbd.js";const L={className:"card"},W=e("h5",null,"View Officers Collections",-1),z={class:"line-height-3 m-0"},E={class:"container"},K={class:"receipt-number"},P={class:"item"},U=e("div",{class:"item-description"},"Member Name",-1),$={class:"item-price"},F={class:"item"},G=e("div",{class:"item-description"},"Member Number",-1),J={class:"item-price"},Q={class:"item"},X=e("div",{class:"item-description"},"Amount",-1),Y={class:"item-price"},Z={class:"item"},ee=e("div",{class:"item-description"},"Amount in words",-1),te={class:"item-price"},ie={class:"item"},se=e("div",{class:"item-description"},"Transaction ID",-1),oe={class:"item-price"},ae={class:"item"},ne=e("div",{class:"item-description"},"Deposited By",-1),de={class:"item-price"},ce={class:"item"},re=e("div",{class:"item-description"},"Date",-1),le={class:"item-price"},me={class:"thank-you"},ve=e("p",{class:"thank-you"},"Have a great day!",-1),ue={class:"grid p-fluid mt-3"},_e={class:"field col-12 md:col-12"},pe={class:"my-2"},ye={__name:"ViewCollections",setup(he){I();const o=new H;R();const w=x([]),i=x(null),b=x(!1),M=()=>{o.genericRequest("get-officer-collection","get",!0,{}).then(d=>{d.status&&(w.value=d.data)})},S=d=>{o.genericRequest("get-transaction-receipt/"+d.tran_id,"get",!0,{}).then(r=>{r.status&&(i.value=r.data)}),b.value=o.toggleModal(!0)},B=()=>{b.value=o.toggleModal(!1),V()},V=()=>{var r,u,y,f,c,g,t,m,_,p;const d=window.open("","","width=600,height=600");d.document.open(),d.document.write(`
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
        </style>
        <body>
          <div class="print-content">
            <div class="container">
                <div class="receipt-number">`+o.removeLetters((r=i.value)==null?void 0:r.tran_id)+`</div>
                <h1>`+((u=i.value)==null?void 0:u.institution_name)+`</h1>

                <div class="item">
                    <div class="item-description">Member Name</div>
                    <div class="item-price">`+((y=i.value)==null?void 0:y.member_name)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Member Number</div>
                    <div class="item-price">`+((f=i.value)==null?void 0:f.member_number)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Amount</div>
                    <div class="item-price">`+o.commaSeparator((c=i.value)==null?void 0:c.amount)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Amount in words</div>
                    <div class="item-price">`+o.NumInWords((g=i.value)==null?void 0:g.amount)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Transaction ID</div>
                    <div class="item-price">`+((t=i.value)==null?void 0:t.tran_id)+`</div>
                </div>


                <div class="item">
                    <div class="item-description">Deposited By</div>
                    <div class="item-price">`+((m=i.value)==null?void 0:m.user_name)+`</div>
                </div>

                <div class="item">
                    <div class="item-description">Date</div>
                    <div class="item-price">`+((_=i.value)==null?void 0:_.created_at)+`</div>
                </div>

                <p class="thank-you">Thank you for saving with`+((p=i.value)==null?void 0:p.institution_name)+`</p>
                <p class="thank-you">Have a great day!</p>
            </div>
          </div>
        </body>
        </html>
      `),d.document.close(),d.print(),d.close()};return j(()=>{M()}),(d,r)=>{const u=h("Button"),y=h("Dialog"),f=h("Toolbar"),c=h("Column"),g=h("DataTable");return O(),q("div",L,[W,a(y,{header:"Receipt",visible:b.value,"onUpdate:visible":r[0]||(r[0]=t=>b.value=t),breakpoints:{"960px":"75vw"},style:{width:"30vw"},modal:!0},{footer:n(()=>[a(u,{label:"print",onClick:B,icon:"pi pi-print",class:"p-button-outlined"})]),default:n(()=>{var t,m,_,p,C,k,D,N,T,A;return[e("p",z,[e("div",E,[e("div",K,s(v(o).removeLetters((t=i.value)==null?void 0:t.tran_id)),1),e("h1",null,s((m=i.value)==null?void 0:m.institution_name),1),e("div",P,[U,e("div",$,s((_=i.value)==null?void 0:_.member_name),1)]),e("div",F,[G,e("div",J,s((p=i.value)==null?void 0:p.member_number),1)]),e("div",Q,[X,e("div",Y,s(v(o).commaSeparator((C=i.value)==null?void 0:C.amount)),1)]),e("div",Z,[ee,e("div",te,s(v(o).NumInWords((k=i.value)==null?void 0:k.amount)),1)]),e("div",ie,[se,e("div",oe,s((D=i.value)==null?void 0:D.tran_id),1)]),e("div",ae,[ne,e("div",de,s((N=i.value)==null?void 0:N.user_name),1)]),e("div",ce,[re,e("div",le,s((T=i.value)==null?void 0:T.created_at),1)]),e("p",me,"Thank you for saving with "+s((A=i.value)==null?void 0:A.institution_name),1),ve])])]}),_:1},8,["visible"]),e("div",ue,[e("div",_e,[a(f,{class:"mb-4"},{start:n(()=>[e("div",pe," Amount Collected: "+s(v(o).commaSeparator(v(o).sumOfAmount(w.value))),1)]),end:n(()=>[]),_:1}),a(g,{value:w.value,paginator:!0,class:"p-datatable-gridlines",rows:10,dataKey:"id",rowHover:!0,filterDisplay:"menu",responsiveLayout:"scroll"},{default:n(()=>[a(c,{field:"name",header:"Name",style:{"min-width":"10rem"}},{body:n(({data:t})=>[l(s(t.member_name),1)]),_:1}),a(c,{header:"Member No.",style:{"min-width":"10rem"}},{body:n(({data:t})=>[l(s(t.member_number),1)]),_:1}),a(c,{header:"Amount",style:{"min-width":"12rem"}},{body:n(({data:t})=>[l(s(v(o).commaSeparator(t.amount)),1)]),_:1}),a(c,{header:"Status",style:{"min-width":"10rem"}},{body:n(({data:t})=>[l(s(t.status),1)]),_:1}),a(c,{header:"Date",style:{"min-width":"10rem"}},{body:n(({data:t})=>[l(s(t.tran_date),1)]),_:1}),a(c,{header:"Officer",style:{"min-width":"10rem"}},{body:n(({data:t})=>[l(s(t.officer_name),1)]),_:1}),a(c,{header:"Transaction ID",style:{"min-width":"10rem"}},{body:n(({data:t})=>[l(s(t.tran_id),1)]),_:1}),a(c,{header:"Receipt",headerStyle:"min-width:5rem;"},{body:n(({data:t})=>[a(u,{icon:"pi pi-print",class:"p-button-rounded p-button-success mr-2",onClick:m=>S(t)},null,8,["onClick"])]),_:1})]),_:1},8,["value"])])])])}}};export{ye as default};
