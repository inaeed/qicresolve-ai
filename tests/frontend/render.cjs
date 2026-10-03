const fs=require('fs'),vm=require('vm'),assert=require('assert/strict');
const roles=JSON.parse(fs.readFileSync(require('path').join(__dirname,'roles.json')));
class Element {
 constructor(tag='div'){this.tagName=tag;this.children=[];this.dataset={};this.attrs={};this.handlers={};this.value='';this._text='';this.classList={add(){},toggle(){return true}};}
 set textContent(v){this._text=String(v);this.children=[]}get textContent(){return this._text+this.children.map(x=>x.textContent).join(' ')}
 append(...c){this.children.push(...c)}prepend(...c){this.children.unshift(...c)}replaceChildren(...c){this.children=c;this._text=''}
 setAttribute(k,v){this.attrs[k]=v}removeAttribute(k){delete this.attrs[k]}addEventListener(k,f){this.handlers[k]=f}
 querySelector(){return new Element()}focus(){}close(){this.open=false;this.handlers.close?.()}showModal(){this.open=true}reportValidity(){return true}
}
const path=require('path');const base=path.resolve(__dirname,'../../resources/js/qic');const source=['state','export'].map(n=>fs.readFileSync(path.join(base,n+'.js'),'utf8').replace(/export /g,'')).join('\n')+'\n'+fs.readFileSync(path.join(base,'workspace.js'),'utf8').split('\n').filter(l=>!l.startsWith('import ')).join('\n');let count=0;
for(const [role,def] of Object.entries(roles))for(const p of def.pages){
 const ids=p[3].includes('{issue}')?['QI-001','QI-002','QI-003','QI-004','QI-005','QI-006','QI-007','QI-999']: [null];
 for(const issue of ids){
 const nodes={};for(const id of ['qic-boot','qic-content','qic-modal','qic-modal-body','qic-modal-title','qic-storage-warning','qic-toast','qic-page-actions']) nodes[id]=new Element();
 const links={};for(const [r,d] of Object.entries(roles)){links[r]={};for(const pg of d.pages)links[r][pg[0]]='/'+r+pg[3].replace('{issue}','__ISSUE__');}
 nodes['qic-boot'].textContent=JSON.stringify({role,page:p[0],screen:p[2],issue,links,user:{id:1,name:'Demo',email:'demo@example.test'},accountUrl:'/settings/profile'});
 const document={readyState:'complete',getElementById:id=>nodes[id],createElement:t=>new Element(t),createTextNode:t=>{const e=new Element('#text');e.textContent=t;return e},querySelector:()=>null,addEventListener(){}};
 const context={document,window:{sessionStorage:{getItem(){return null}},addEventListener(){}},Node:Element,structuredClone,TextEncoder,Blob,URL,console,setTimeout};
 try{vm.runInNewContext(source,context);assert.ok(nodes['qic-content'].textContent.length>0)}catch(e){throw Error(`${role}/${p[0]} ${issue}: ${e.stack}`)}count++;
 }
}
console.log(`PASS: ${count} rendering combinations across all 34 role pages (synthetic DOM, not browser).`);
