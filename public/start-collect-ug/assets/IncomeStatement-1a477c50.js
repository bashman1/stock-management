import{l as pt,m as vt,r as m,o as St,b as i,c as gt,d as bt,e as t,h as u,i as it,t as o,f as n,C as ft}from"./index-68bb68b2.js";const It={class:"card"},Dt=t("h4",null,"Income Statement",-1),Pt={class:"grid p-fluid mt-3"},Ct={class:"field col-12 md:col-4"},kt={class:"p-float-label"},xt=t("label",{for:"calendar"},"From Date",-1),Ot={class:"field col-12 md:col-4"},Et={class:"p-float-label"},Rt=t("label",{for:"calendar"},"To Date",-1),Tt={class:"field col-12 md:col-4"},Vt={class:"my-2"},Bt={class:"income-statement"},At=t("tr",null,[t("th",{class:"bold"},[t("h5",{class:"statement-header bold"},"Particulars")]),t("td",{class:"bold"},"Amount"),t("td",{class:"bold"},"Amount")],-1),Ft=t("th",null,"Sales",-1),wt=t("td",null,null,-1),Nt=t("th",null,"Return in Wards",-1),Mt=t("td",null,null,-1),Ut=t("th",null,"Net sales",-1),yt=t("td",null,null,-1),Gt={class:"bold"},Lt=t("tr",null,[t("th",null,[t("h5",{class:"statement-header bold"},"Cost Of Sales")]),t("td"),t("td")],-1),qt=t("th",null,"opening stock",-1),Wt=t("td",null,null,-1),jt=t("th",null,"Purchases",-1),zt=t("td",null,null,-1),Ht=t("th",null,"Return in wards/ purchase return",-1),Jt=t("td",null,null,-1),Kt=t("th",null,"Passage/ carriage",-1),Qt=t("td",null,null,-1),Xt=t("th",null,"Goods available for sale",-1),Yt={class:"bold"},Zt=t("td",null,null,-1),$t=t("th",null,"Closing stock",-1),tl=t("td",null,null,-1),ll=t("th",null,"Cost of sales",-1),el=t("td",null,null,-1),ol={class:"bold"},nl=t("th",null,"Gross Profit",-1),sl=t("td",null,null,-1),al={class:"bold"},ul=t("tr",null,[t("th",null,[t("h5",{class:"statement-header bold"},"Other Incomes")]),t("td"),t("td")],-1),cl=t("tr",null,[t("th",null,"Discount received"),t("td",null,"0"),t("td")],-1),dl=t("tr",null,[t("th",null,"Profit on deposable of NCA"),t("td",null,"0"),t("td")],-1),rl=t("th",null,"Total income",-1),il=t("td",null,null,-1),_l={class:"bold"},hl=t("tr",null,[t("th",null,[t("h5",{class:"statement-header bold"},"Operating Expenses")]),t("td"),t("td")],-1),ml=t("th",null,"Total operating expenses",-1),pl=t("td",null,null,-1),vl={class:"bold"},Sl=t("th",null,"Profit before interest and tax(PBIT)",-1),gl=t("td",null,null,-1),bl={class:"bold"},fl=t("tr",null,[t("th",null,[t("h5",{class:"statement-header bold"},"Finance Costs")]),t("td"),t("td")],-1),Il=t("tr",null,[t("th",null,"Interest on external loan"),t("td",null,"0"),t("td")],-1),Dl=t("tr",null,[t("th",null,"Interest to URL"),t("td",null,"0"),t("td")],-1),Pl=t("th",null,"Total finance costs",-1),Cl=t("td",null,null,-1),kl={class:"bold"},xl=t("th",null,"Profit before tax (PBT)",-1),Ol=t("td",null,null,-1),El={class:"bold"},Vl={__name:"IncomeStatement",setup(Rl){pt();const e=new ft;vt();const l=m(null),c=m(null),d=m(null),p=s=>{e.genericRequest("get-income-statement","post",!0,s).then(a=>{a.status&&(l.value=a.data)})},_t=()=>{let s={fromDate:c.value,toDate:d.value};console.log(s),p(s)},ht=s=>{console.log(s)};return St(()=>{let s=new Date,a=new Date(s);a.setMonth(s.getMonth()-1),c.value=a,d.value=s;let _={fromDate:c.value,toDate:d.value};p(_)}),(s,a)=>{var S,g,b,f,I,D,P,C,k,x,O,E,R,T,V,B,A,F,w,N,M,U,y,G,L,q,W,j,z,H,J,K,Q,X,Y,Z,$,tt,lt,et,ot,nt,st,at,ut,ct,dt,rt;const _=i("Toast"),v=i("Calendar"),h=i("Button"),mt=i("Toolbar");return gt(),bt("div",null,[t("div",It,[Dt,u(_),u(mt,{class:"mb-4"},{start:it(()=>[t("div",Pt,[t("div",Ct,[t("span",kt,[u(v,{inputId:"calendar",modelValue:c.value,"onUpdate:modelValue":a[0]||(a[0]=r=>c.value=r)},null,8,["modelValue"]),xt])]),t("div",Ot,[t("span",Et,[u(v,{onBlur:a[1]||(a[1]=r=>ht(r)),inputId:"calendar",modelValue:d.value,"onUpdate:modelValue":a[2]||(a[2]=r=>d.value=r)},null,8,["modelValue"]),Rt])]),t("div",Tt,[t("div",null,[u(h,{label:"Search",class:"p-button-success mr-2",onClick:_t})])])])]),end:it(()=>[t("div",Vt,[u(h,{label:"CSV",icon:"pi pi-file-excel",class:"p-button-success mr-2",onClick:s.openNew},null,8,["onClick"]),u(h,{label:"PDF",icon:"pi pi-file-pdf",class:"p-button-danger",onClick:s.confirmDeleteSelected},null,8,["onClick"])])]),_:1}),t("table",Bt,[At,t("tr",null,[Ft,wt,t("td",null,o(((S=l.value)==null?void 0:S.totalSales)==0?(g=l.value)==null?void 0:g.totalSales:n(e).commaSeparator((b=l.value)==null?void 0:b.totalSales)),1)]),t("tr",null,[Nt,Mt,t("td",null,o(((f=l.value)==null?void 0:f.totalReturnIn)==0?(I=l.value)==null?void 0:I.totalReturnIn:n(e).commaSeparator((D=l.value)==null?void 0:D.totalReturnIn)),1)]),t("tr",null,[Ut,yt,t("td",Gt,o(((P=l.value)==null?void 0:P.netSales)==0?(C=l.value)==null?void 0:C.netSales:n(e).commaSeparator((k=l.value)==null?void 0:k.netSales)),1)]),Lt,t("tr",null,[qt,t("td",null,o(((x=l.value)==null?void 0:x.openingStock)==0?(O=l.value)==null?void 0:O.openingStock:n(e).commaSeparator((E=l.value)==null?void 0:E.openingStock)),1),Wt]),t("tr",null,[jt,t("td",null,o(((R=l.value)==null?void 0:R.totalPurchases)==0?(T=l.value)==null?void 0:T.totalPurchases:n(e).commaSeparator((V=l.value)==null?void 0:V.totalPurchases)),1),zt]),t("tr",null,[Ht,t("td",null,o(((B=l.value)==null?void 0:B.totalReturnIn)==0?(A=l.value)==null?void 0:A.totalReturnIn:n(e).commaSeparator((F=l.value)==null?void 0:F.totalReturnIn)),1),Jt]),t("tr",null,[Kt,t("td",null,o(((w=l.value)==null?void 0:w.totalPassage)==0?(N=l.value)==null?void 0:N.totalPassage:n(e).commaSeparator((M=l.value)==null?void 0:M.totalPassage)),1),Qt]),t("tr",null,[Xt,t("td",Yt,o(((U=l.value)==null?void 0:U.goodAvailableForSale)==0?(y=l.value)==null?void 0:y.goodAvailableForSale:n(e).commaSeparator((G=l.value)==null?void 0:G.goodAvailableForSale)),1),Zt]),t("tr",null,[$t,t("td",null,o(((L=l.value)==null?void 0:L.closingStock)==0?(q=l.value)==null?void 0:q.closingStock:n(e).commaSeparator((W=l.value)==null?void 0:W.closingStock)),1),tl]),t("tr",null,[ll,el,t("td",ol,o(((j=l.value)==null?void 0:j.costOfSales)==0?(z=l.value)==null?void 0:z.costOfSales:n(e).commaSeparator((H=l.value)==null?void 0:H.costOfSales)),1)]),t("tr",null,[nl,sl,t("td",al,o(((J=l.value)==null?void 0:J.grossProfit)==0?(K=l.value)==null?void 0:K.grossProfit:n(e).commaSeparator((Q=l.value)==null?void 0:Q.grossProfit)),1)]),ul,cl,dl,t("tr",null,[rl,il,t("td",_l,o(((X=l.value)==null?void 0:X.totalIncome)==0?(Y=l.value)==null?void 0:Y.totalIncome:n(e).commaSeparator((Z=l.value)==null?void 0:Z.totalIncome)),1)]),hl,t("tr",null,[ml,pl,t("td",vl,o((($=l.value)==null?void 0:$.totalOperatingExpenses)==0?(tt=l.value)==null?void 0:tt.totalOperatingExpenses:n(e).commaSeparator((lt=l.value)==null?void 0:lt.totalOperatingExpenses)),1)]),t("tr",null,[Sl,gl,t("td",bl,o(((et=l.value)==null?void 0:et.totalIncome)==0?(ot=l.value)==null?void 0:ot.totalIncome:n(e).commaSeparator((nt=l.value)==null?void 0:nt.totalIncome)),1)]),fl,Il,Dl,t("tr",null,[Pl,Cl,t("td",kl,o(((st=l.value)==null?void 0:st.totalOperatingExpenses)==0?(at=l.value)==null?void 0:at.totalOperatingExpenses:n(e).commaSeparator((ut=l.value)==null?void 0:ut.totalOperatingExpenses)),1)]),t("tr",null,[xl,Ol,t("td",El,o(((ct=l.value)==null?void 0:ct.totalOperatingExpenses)==0?(dt=l.value)==null?void 0:dt.totalOperatingExpenses:n(e).commaSeparator((rt=l.value)==null?void 0:rt.totalOperatingExpenses)),1)])])])])}}};export{Vl as default};