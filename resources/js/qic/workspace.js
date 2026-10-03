import { STATUS, ROLE, TERMINAL, ACTION, canAct, transition, createIssue, scopedIssues, createStore, photoError, fileError } from './state.js';
import { downloadWorkbook } from './export.js';

// User-entered text is always inserted through textContent, never interpolated into HTML.
function el(tag, attrs = {}, children = []) {
    const node = document.createElement(tag);
    for (const [key, value] of Object.entries(attrs)) {
        if (value == null || value === false) continue;
        if (key === 'text') node.textContent = String(value);
        else if (key === 'class') node.className = value;
        else if (key.startsWith('on')) node.addEventListener(key.slice(2), value);
        else if (key === 'value') node.value = value;
        else node.setAttribute(key, value === true ? '' : String(value));
    }
    for (const child of [children].flat(Infinity)) if (child != null) node.append(child instanceof Node ? child : document.createTextNode(String(child)));
    return node;
}
const text = (value, cls='') => el('p',{text:value,class:cls});
const button = (label, action, cls='') => el('button',{type:'button',class:`qic-button ${cls}`,text:label,onclick:action});
const anchor = (label, href, cls='') => el('a',{href,text:label,class:cls});
const badge = issue => el('span',{class:`qic-badge ${issue.status === 'closed' ? 'success' : ['rejected','three_c_rejected','cancelled'].includes(issue.status) ? 'danger' : ['need_information','revision_requested','waiting_final'].includes(issue.status) ? 'warning' : 'info'}`,text:STATUS[issue.status]});
const time = value => value ? new Date(value).toLocaleString('id-ID',{dateStyle:'medium',timeStyle:'short'}) : '—';
const card = (title,...children) => el('section',{class:'qic-card'},[el('h2',{text:title}),children]);
const empty = (message='Belum ada data untuk ditampilkan.') => el('div',{class:'qic-empty'},[el('strong',{text:'Tidak ada data'}),text(message)]);
let serial=0;
function field(label,name,value='',kind='text',options={}) {
    const id=`qic-field-${++serial}`;
    let input;
    if(kind==='textarea') input=el('textarea',{id,name,rows:4,maxlength:3000});
    else if(kind==='select') input=el('select',{id,name},(options.choices||[]).map(choice=>el('option',{value:typeof choice==='string'?choice:choice.value,text:typeof choice==='string'?choice:choice.label})));
    else input=el('input',{id,name,type:kind,...(kind==='number'?{min:1,step:1}:{}),...(kind==='text'?{maxlength:200}:{})});
    input.value=value??'';
    if(options.required) input.required=true;
    if(options.disabled) input.disabled=true;
    const wrapper=el('div',{class:'qic-field'},[el('label',{for:id,text:label+(options.required?' *':'')}),input]);
    if(options.help) wrapper.append(text(options.help,'qic-small qic-muted'));
    return {wrapper,input};
}
function formFields(specs) {
    const form=el('form',{class:'qic-form'}), inputs={};
    const grid=el('div',{class:'qic-form-grid'});
    for(const spec of specs){const f=field(...spec);inputs[spec[1]]=f.input;if(spec[3]==='textarea') f.wrapper.classList.add('qic-wide');grid.append(f.wrapper);}
    form.append(grid);
    return {form,inputs,values:()=>Object.fromEntries(Object.entries(inputs).map(([k,v])=>[k,v.value.trim()]))};
}
function table(headers, rows) {
    const t=el('table',{},[el('thead',{},el('tr',{},headers.map(h=>el('th',{scope:'col',text:h})))),el('tbody',{},rows.map(row=>el('tr',{},row.map(c=>el('td',{},c)))))]);
    return el('div',{class:'qic-table-scroll',tabindex:'0',role:'region','aria-label':'Tabel data, dapat digeser horizontal'},t);
}
function init(){
    const bootNode=document.getElementById('qic-boot');if(!bootNode||bootNode.dataset.ready)return;bootNode.dataset.ready='true';
    const boot=JSON.parse(bootNode.textContent);let storage=null;try{storage=window.sessionStorage;}catch{}
    const store=createStore(storage,boot.user.id), role=boot.role, content=document.getElementById('qic-content');
    const modal=document.getElementById('qic-modal'),modalBody=document.getElementById('qic-modal-body');
    let cleanup=[];
    const url=(page,id=null,r=role)=>{const pattern=boot.links[r]?.[page];if(!pattern)throw new Error(`Rute belum tersedia: ${r}.${page}`);return pattern.replace('__ISSUE__',encodeURIComponent(id??''));};
    const issues=()=>scopedIssues(store.get(),role);
    const lookup=id=>issues().find(i=>i.id===id);
    const warn=()=>{const n=document.getElementById('qic-storage-warning');n.textContent=store.warning();n.hidden=!store.warning();};
    const toast=(message,error=false)=>{const n=document.getElementById('qic-toast');n.textContent=message;n.classList.toggle('error',error);n.hidden=false;n.focus();};
    const modalError=message=>{let n=modalBody.querySelector('[data-modal-error]');if(!n){n=el('p',{class:'qic-error',role:'alert','data-modal-error':'',tabindex:'-1'});modalBody.append(n);}n.textContent=message;n.focus();};
    const closeModal=()=>modal.close();
    modal.querySelector('[data-close-modal]').addEventListener('click',closeModal);
    modal.addEventListener('close',()=>{cleanup.forEach(f=>f());cleanup=[];});
    const openModal=(title,body)=>{if(modal.open)modal.close();document.getElementById('qic-modal-title').textContent=title;modalBody.replaceChildren(body);modal.showModal();};
    const persist=next=>{store.save(next);warn();};
    function act(id,action,payload={}){
        const next=structuredClone(store.get()),index=next.issues.findIndex(i=>i.id===id);
        if(index<0)throw new Error('Issue tidak ditemukan.');
        next.issues[index]=transition(next.issues[index],role,action,payload);
        persist(next);render();toast(`${ACTION[action]} berhasil pada data demo.`);
    }
    function decision(issue,action){
        const f=formFields([['Alasan keputusan Quality','reason','','textarea',{required:true}]]);
        f.form.prepend(text(`Issue ${issue.id} · ${STATUS[issue.status]}. Keputusan dan alasan akan tercatat pada riwayat demonstrasi.`));
        f.form.append(el('div',{class:'qic-actions'},[button('Batal',closeModal,'qic-secondary'),el('button',{type:'submit',class:'qic-button',text:ACTION[action]})]));
        f.form.addEventListener('submit',e=>{e.preventDefault();try{act(issue.id,action,f.values());closeModal();}catch(err){modalError(err.message);}});
        openModal(ACTION[action],f.form);
    }
    function actionBar(issue){
        const a=el('div',{class:'qic-actions'});
        if(role==='main-assy'&&['draft','need_information'].includes(issue.status))a.append(anchor('Lengkapi Complaint',url('issues.edit',issue.id),'qic-button'));
        if(role==='quality'){
            if(canAct(issue,role,'verify'))a.append(anchor('Verifikasi Issue',url('issues.verify',issue.id),'qic-button'));
            if(canAct(issue,role,'assign'))a.append(anchor('Tugaskan Auto Line',url('assignments')+'#'+issue.id,'qic-button'));
            if(canAct(issue,role,'approve'))a.append(anchor('Review 3C',url('reviews.show',issue.id),'qic-button'));
            if(canAct(issue,role,'close'))a.append(anchor('Verifikasi Akhir',url('final-verification',issue.id),'qic-button'));
            if(canAct(issue,role,'cancel'))a.append(button('Batalkan Issue',()=>decision(issue,'cancel'),'qic-secondary'));
        }
        if(role==='auto-line'){
            if(canAct(issue,role,'acknowledge'))a.append(button('Terima Penugasan',()=>{try{act(issue.id,'acknowledge');}catch(e){toast(e.message,true);}}));
            if(canAct(issue,role,'submit_response'))a.append(anchor('Susun / Revisi 3C',url(issue.versions.length?'responses.revise':'responses.create',issue.id),'qic-button'));
            if(canAct(issue,role,'submit_corrective'))a.append(anchor('Tindakan Korektif',url('corrective',issue.id),'qic-button'));
        }
        return a;
    }
    function issueTable(records){
        if(!records.length)return empty();
        return table(['ID Issue','Material','Status','Pemilik','Prioritas','Tindakan'],records.map(i=>[
            anchor(i.id,url('issues.show',i.id)),i.material,badge(i),i.owner||'Belum ditugaskan',i.priority,
            anchor('Buka detail →',url('issues.show',i.id)),
        ]));
    }
    function listPage(records,title='Daftar Issue',extra=null){
        const search=field('Cari ID / material','query','','search'),status=field('Status','status','','select',{choices:[{value:'',label:'Semua status'},...Object.entries(STATUS).map(([value,label])=>({value,label}))]});
        const results=el('div'),count=el('p',{class:'qic-small qic-muted',role:'status'});
        let visible=records;
        const update=()=>{const q=search.input.value.toLowerCase().trim();visible=records.filter(i=>(!q||`${i.id} ${i.material} ${i.description}`.toLowerCase().includes(q))&&(!status.input.value||i.status===status.input.value));count.textContent=`${visible.length} dari ${records.length} issue`;results.replaceChildren(issueTable(visible));};
        search.input.addEventListener('input',update);status.input.addEventListener('change',update);update();
        content.append(card(title,el('div',{class:'qic-filters'},[search.wrapper,status.wrapper]),count,extra,results));
        return ()=>visible;
    }
    function dashboard(){
        const all=issues(),pending=role==='quality'?all.filter(i=>['waiting_verification','waiting_review','waiting_final'].includes(i.status)).length:role==='auto-line'?all.filter(i=>['assigned','revision_requested','approved','corrective'].includes(i.status)).length:all.filter(i=>['need_information','draft'].includes(i.status)).length;
        const stats=[['Total Issue',all.length],['Memerlukan Tindakan',pending],['Dalam Penanganan',all.filter(i=>!TERMINAL.includes(i.status)).length],['Selesai',all.filter(i=>i.status==='closed').length]];
        content.append(el('section',{class:'qic-stats','aria-label':'Ringkasan issue'},stats.map(([label,n])=>el('article',{class:'qic-card qic-stat'},[text(label,'qic-muted'),el('strong',{text:n}),text('Data demonstrasi','qic-small qic-muted')]))));
        if(role==='main-assy')document.getElementById('qic-page-actions').append(anchor('+ Buat Complaint',url('issues.create'),'qic-button'));
        const focus=all.filter(i=>role==='quality'?['waiting_verification','waiting_review','waiting_final','verified'].includes(i.status):role==='auto-line'?!TERMINAL.includes(i.status):['need_information','draft'].includes(i.status));
        content.append(card('Prioritas Tindak Lanjut',focus.length?issueTable(focus):empty('Seluruh antrean telah ditangani.')));
        content.append(card('Issue Terbaru',issueTable([...all].reverse().slice(0,5))));
        const logs=all.flatMap(i=>i.audit.map(a=>({...a,issue:i.id}))).sort((a,b)=>b.at.localeCompare(a.at)).slice(0,4);
        content.append(card('Aktivitas Terbaru',timeline(logs)));
    }
    function timeline(logs){return logs.length?el('ol',{class:'qic-timeline'},logs.map(a=>el('li',{},[el('strong',{text:`${a.issue?a.issue+' · ':''}${a.action}`}),text(`${a.actor} · ${time(a.at)}`,'qic-small qic-muted'),a.note?text(a.note):null,a.to?text(STATUS[a.to],'qic-small'):null]))):empty('Riwayat belum tersedia.');}
    function evidence(names,label='Evidence'){
        const list=Array.isArray(names)?names:Object.values(names||{}).filter(Boolean);
        return card(label,list.length?el('ul',{class:'qic-evidence-list'},list.map(n=>el('li',{},[el('span',{text:n}),button('Info berkas',()=>openModal('Evidence Demonstrasi',text(`Nama berkas: ${n}. File contoh atau metadata ini tidak tersimpan di server. Isi berkas tidak tersedia setelah berpindah halaman.`)),'qic-small-button qic-secondary')]))):empty('Belum ada evidence.'),text('Demo menyimpan nama berkas saja; jangan gunakan data perusahaan rahasia.','qic-small qic-muted'));
    }
    function summary(issue){
        return card(issue.id+' · '+issue.material,el('div',{class:'qic-status-row'},[badge(issue),text(issue.priority+' priority','qic-small')]),text(issue.description),el('dl',{class:'qic-details'},[
            el('dt',{text:'Pelapor'}),el('dd',{text:issue.reporter}),el('dt',{text:'Pemilik proses'}),el('dd',{text:issue.owner||'Belum ditugaskan'}),el('dt',{text:'Jumlah reject'}),el('dd',{text:issue.quantity+' pcs'}),el('dt',{text:'Jenis defect'}),el('dd',{text:issue.defect}),el('dt',{text:'Kejadian'}),el('dd',{text:time(issue.occurredAt)}),
        ]));
    }
    function detail(issue){
        content.append(actionBar(issue),summary(issue),evidence(issue.photos,'Foto Pelaporan'));
        if(issue.investigation)content.append(card('Investigasi dan Containment',text(issue.investigation),text(issue.containment||'Tidak ada containment yang dicatat.')));
        if(issue.versions.length)content.append(versionPanel(issue));
        if(issue.corrective)content.append(card('Tindakan Korektif',text(issue.corrective.action),text(issue.corrective.result||'Hasil belum dicatat.')),evidence(issue.corrective.evidence,'Bukti Tindakan'));
        content.append(card('Riwayat Issue',timeline(issue.audit)));
    }
    function photoPicker(current,onSave){
        const draft={...current},uploads=el('div',{class:'qic-upload-grid'});
        const objectUrls=[];
        for(const [key,label] of [['defect','Foto Defect'],['label','Foto Label Material']]){
            const input=el('input',{type:'file',accept:'image/jpeg,image/png,image/webp',id:`photo-${key}`}),preview=el('img',{class:'qic-photo',alt:`Pratinjau ${label}`,hidden:true}),error=el('p',{class:'qic-error',role:'alert'}),name=text(draft[key]||'Belum dipilih','qic-small qic-muted');
            input.addEventListener('change',()=>{
                const file=input.files[0];if(!file)return;
                const problem=photoError(file);error.textContent=problem;
                if(problem){draft[key]=null;input.value='';preview.hidden=true;preview.removeAttribute('src');name.textContent='Belum dipilih';return;}
                draft[key]=file.name;name.textContent=file.name;const src=URL.createObjectURL(file);objectUrls.push(src);preview.src=src;preview.hidden=false;
            });
            uploads.append(el('section',{class:'qic-upload'},[el('label',{for:`photo-${key}`,text:label}),text(key==='defect'?'Area defect harus terlihat jelas.':'Kode material harus dapat dibaca.','qic-small'),input,name,error,preview]));
        }
        const body=el('div',{},[text('JPEG, PNG, WebP; maksimal 5 MiB/foto. Pratinjau hanya tersedia saat modal ini dibuka.'),uploads,el('div',{class:'qic-actions'},[button('Batal',closeModal,'qic-secondary'),button('Lampirkan Foto',()=>{if(!draft.defect||!draft.label){modalError('Pilih foto defect dan label material.');return;}onSave(draft);closeModal();})])]);
        openModal('Upload Issue Evidence',body);cleanup.push(()=>objectUrls.forEach(u=>URL.revokeObjectURL(u)));
    }
    function complaintForm(issue=null){
        if(issue&&!canAct(issue,role,'edit')){content.append(card('Complaint tidak dapat diubah',text('Hanya draft dan complaint yang diminta kelengkapannya dapat diedit oleh Main Assy.'),anchor('Lihat detail',url('issues.show',issue.id))));return;}
        let photos={...(issue?.photos||{})};
        const f=formFields([
            ['Material / Part Number','material',issue?.material,'text',{required:true}],['Tanggal dan waktu kejadian','occurredAt',issue?.occurredAt||'','datetime-local',{required:true}],
            ['Jenis defect','defect',issue?.defect||'','select',{choices:['','Goresan','Dimensi tidak sesuai','Penyok','Lainnya'],required:true}],
            ['Jumlah reject','quantity',issue?.quantity||1,'number',{required:true}],['Prioritas','priority',issue?.priority||'Normal','select',{choices:['Normal','Tinggi']}],
            ['Deskripsi temuan','description',issue?.description||'','textarea',{required:true}],
        ]);
        const names=text(Object.values(photos).filter(Boolean).join(' · ')||'Belum ada foto terpilih.','qic-small');
        f.form.append(card('Bukti Pelaporan',names,button('Pilih / Ganti Foto',()=>photoPicker(photos,value=>{photos=value;names.textContent=Object.values(value).join(' · ');}),'qic-secondary')));
        const result=el('p',{class:'qic-error',role:'alert',tabindex:'-1',hidden:true});f.form.append(result);
        const save=submit=>{
            if(submit&&!f.form.reportValidity())return;
            try{
                const values={...f.values(),quantity:Number(f.inputs.quantity.value),photos};
                if(issue) {act(issue.id,submit?'submit':'edit',values);content.replaceChildren(card('Complaint Diperbarui',text('Perubahan tersimpan pada demo tab ini.'),anchor('Buka issue',url('issues.show',issue.id),'qic-button')));}
                else {const next=structuredClone(store.get());const created=createIssue(next,values,submit);next.counter++;next.issues.push(created);persist(next);content.replaceChildren(card(submit?'Complaint Dikirim':'Draft Disimpan',text(`${created.id} tersimpan pada data demo tab ini. Foto hanya dicatat sebagai nama berkas.`),anchor('Buka issue',url('issues.show',created.id),'qic-button')));toast('Data demo berhasil diperbarui.');}
            }catch(e){result.hidden=false;result.textContent=e.message;result.focus();}
        };
        f.form.append(el('div',{class:'qic-actions'},[anchor('Kembali',url('issues.index'),'qic-button qic-secondary'),button(issue?'Simpan Perubahan':'Simpan Draft',()=>save(false),'qic-secondary'),el('button',{type:'submit',class:'qic-button',text:issue?'Kirim Ulang Complaint':'Kirim Complaint (Demo)'})]));
        f.form.addEventListener('submit',e=>{e.preventDefault();save(true);});
        if(issue?.status==='need_information'){const note=[...issue.audit].reverse().find(a=>a.action===ACTION.return_info);content.append(card('Permintaan Quality',text(note?.note||'Lengkapi informasi serta bukti yang diperlukan.')));}
        content.append(card(issue?'Lengkapi Laporan':'Data Complaint',f.form));
    }
    function attachments(existing=[]){
        let names=[...existing];
        const label=el('label',{text:'Tambah evidence (JPEG, PNG, WebP, atau PDF)'}),input=el('input',{type:'file',multiple:true,accept:'image/jpeg,image/png,image/webp,application/pdf'}),list=el('div'),err=el('p',{class:'qic-error',role:'alert'});
        label.append(input);
        const draw=()=>list.replaceChildren(...names.map((name,index)=>el('div',{class:'qic-file-row'},[el('span',{text:name}),button('Hapus',()=>{names.splice(index,1);draw();},'qic-small-button qic-secondary')])));
        input.addEventListener('change',()=>{err.textContent='';for(const file of input.files){const problem=fileError(file);if(problem){err.textContent=problem;continue;}if(!names.includes(file.name))names.push(file.name);}input.value='';draw();});draw();
        return {node:el('div',{class:'qic-attachments'},[label,text('Maksimal 5 MiB/berkas. Demo menyimpan nama berkas saja.','qic-small qic-muted'),list,err]),get:()=>[...names]};
    }
    function bindProcessForm(f,issue,saveAction,submitAction,build){
        const result=el('p',{class:'qic-error',role:'alert',tabindex:'-1',hidden:true});f.form.append(result);
        const run=action=>{if(!f.form.reportValidity())return;try{act(issue.id,action,build());}catch(e){result.hidden=false;result.textContent=e.message;result.focus();}};
        f.form.append(el('div',{class:'qic-actions'},[saveAction?button(ACTION[saveAction],()=>run(saveAction),'qic-secondary'):null,el('button',{type:'submit',class:'qic-button',text:ACTION[submitAction]})]));
        f.form.addEventListener('submit',e=>{e.preventDefault();run(submitAction);});
    }
    function investigation(issue){
        content.append(summary(issue),actionBar(issue),evidence(issue.photos,'Foto Pelaporan'));
        if(canAct(issue,role,'save_investigation')){
            const f=formFields([['Hasil investigasi','investigation',issue.investigation,'textarea',{required:true}],['Containment / pengendalian sementara','containment',issue.containment,'textarea']]);
            const files=attachments(issue.investigationEvidence);f.form.append(files.node);
            bindProcessForm(f,issue,null,'save_investigation',()=>({...f.values(),evidence:files.get()}));content.append(card('Investigasi dan Containment',f.form));
        }else content.append(card('Catatan Investigasi',text(issue.investigation||'Terima penugasan untuk mulai investigasi.'),text(issue.containment)));
        content.append(card('Riwayat Penanganan',timeline(issue.audit)));
    }
    function versionPanel(issue){
        const holder=el('div');
        if(!issue.versions.length)return card('Respons 3C',empty('Belum ada respons yang dikirim.'));
        const selector=field('Pilih versi 3C','version',String(issue.versions.length),'select',{choices:issue.versions.map(v=>({value:String(v.version),label:`Versi ${v.version} · ${time(v.submittedAt)}`}))});
        const paint=()=>{const v=issue.versions.find(v=>String(v.version)===selector.input.value);holder.replaceChildren(el('dl',{class:'qic-3c'},[el('dt',{text:'Concern'}),el('dd',{text:v.concern}),el('dt',{text:'Cause'}),el('dd',{text:v.cause}),el('dt',{text:'Countermeasure'}),el('dd',{text:v.countermeasure})]),text('Evidence: '+(v.evidence.join(', ')||'Belum tersedia'),'qic-small'),v.review?el('div',{class:'qic-review-note'},[el('strong',{text:ACTION[v.review.decision]}),text(v.review.reason),text(time(v.review.at),'qic-small qic-muted')]):text('Versi ini belum mendapat keputusan Quality.','qic-muted'));};selector.input.addEventListener('change',paint);paint();
        return card('Riwayat Versi 3C',selector.wrapper,holder);
    }
    function responseForm(issue){
        content.append(summary(issue));
        if(!canAct(issue,role,'submit_response')){content.append(card('Form 3C belum tersedia',text('Terima penugasan dahulu. Form dibuka pada investigasi, penyusunan, atau revisi 3C.'),actionBar(issue)),versionPanel(issue));return;}
        const last=issue.responseDraft||issue.versions.at(-1)||{};
        if(issue.versions.at(-1)?.review)content.append(card('Catatan Review Quality',text(issue.versions.at(-1).review.reason)));
        const f=formFields([['Concern · Masalah yang ditangani','concern',last.concern||issue.description,'textarea'],['Cause · Penyebab berdasarkan investigasi','cause',last.cause||'','textarea'],['Countermeasure · Tindakan terhadap penyebab','countermeasure',last.countermeasure||'','textarea']]);
        const files=attachments(last.evidence);f.form.append(files.node,text(`Pengiriman berikutnya membuat versi ${issue.versions.length+1}. Versi sebelumnya tetap disimpan.`,'qic-small qic-muted'));
        bindProcessForm(f,issue,'save_response','submit_response',()=>({...f.values(),evidence:files.get()}));content.append(card('Respons 3C',f.form));
        if(issue.versions.length)content.append(versionPanel(issue));
    }
    function verify(issue){
        content.append(summary(issue),evidence(issue.photos,'Evidence Pelaporan'));
        if(canAct(issue,role,'verify'))content.append(card('Keputusan Verifikasi',text('Periksa deskripsi dan identitas material. Semua keputusan memerlukan alasan Quality.'),el('div',{class:'qic-actions'},[['verify','Verifikasi'],['return_info','Minta Informasi'],['reject_issue','Tolak Issue']].map(([a,t])=>button(t,()=>decision(issue,a),a==='verify'?'':'qic-secondary')))));
        else content.append(card('Status Verifikasi',text('Keputusan verifikasi awal tidak tersedia pada status ini.'),actionBar(issue)));
        content.append(card('Riwayat Keputusan',timeline(issue.audit)));
    }
    function assignments(){
        const list=issues().filter(i=>i.status==='verified');
        content.append(text('Hanya issue terverifikasi yang dapat ditugaskan. Assignment memindahkan pekerjaan kepada Auto Line.','qic-muted'));
        if(!list.length)content.append(card('Menunggu Penugasan',empty('Verifikasi issue terlebih dahulu.')));
        for(const issue of list){
            const owners=store.get().master.filter(m=>m.category==='Process Owner'&&m.active).map(m=>m.name);
            const f=formFields([['Auto Line tujuan','owner','','select',{choices:[{value:'',label:'Pilih process owner'},...owners],required:true}]]);
            const result=el('p',{class:'qic-error',role:'alert'});f.form.append(result,el('button',{class:'qic-button',type:'submit',text:'Tugaskan Issue'}));
            f.form.addEventListener('submit',e=>{e.preventDefault();try{act(issue.id,'assign',f.values());}catch(err){result.textContent=err.message;}});
            const c=card(issue.id+' · '+issue.material,text(issue.description),f.form);c.id=issue.id;content.append(c);
        }
        content.append(card('Sudah Ditugaskan',issueTable(issues().filter(i=>i.owner))));
    }
    function review(issue){
        content.append(summary(issue),versionPanel(issue));
        if(canAct(issue,role,'approve'))content.append(card('Keputusan atas Versi Terbaru',text(`Keputusan diterapkan pada versi ${issue.versions.length}, walaupun versi lama sedang dibaca. Persetujuan 3C belum menutup issue.`),el('div',{class:'qic-actions'},[['approve','Setujui 3C'],['revise','Minta Revisi'],['reject_response','Tolak 3C']].map(([a,t])=>button(t,()=>decision(issue,a),a==='approve'?'':'qic-secondary')))));
        else content.append(card('Hasil Review',text('Tidak ada pengajuan 3C yang menunggu keputusan saat ini.'),actionBar(issue)));
        content.append(card('AI Recommendation · Contoh',text('Periksa apakah penyebab didukung investigasi dan apakah countermeasure mengatasi penyebab. Ini contoh panduan tetap, bukan hasil layanan AI.'),text('Quality menulis alasan keputusannya sendiri.','qic-small qic-muted')));
    }
    function corrective(issue){
        content.append(summary(issue),versionPanel(issue));
        if(!canAct(issue,role,'submit_corrective')){content.append(card('Tindakan Korektif',text('Pelaksanaan tindakan baru dibuka setelah 3C disetujui. Jika sudah dikirim, tunggu verifikasi Quality.')));return;}
        const f=formFields([['Pelaksanaan tindakan','action',issue.corrective?.action||'','textarea',{required:true}],['Hasil pemeriksaan','result',issue.corrective?.result||'','textarea']]);
        const files=attachments(issue.corrective?.evidence);f.form.append(files.node);bindProcessForm(f,issue,'save_corrective','submit_corrective',()=>({...f.values(),evidence:files.get()}));content.append(card('Pelaksanaan Tindakan Korektif',f.form));
    }
    function finalVerification(issue){
        content.append(summary(issue),versionPanel(issue));
        if(issue.corrective)content.append(card('Pelaksanaan dan Hasil',text(issue.corrective.action),text(issue.corrective.result)),evidence(issue.corrective.evidence,'Evidence Pelaksanaan'));
        if(canAct(issue,role,'close'))content.append(card('Verifikasi Akhir',text('Pastikan tindakan dan bukti memenuhi persyaratan sebelum menutup issue. Jika belum sesuai, minta perbaikan tindakan.'),el('div',{class:'qic-actions'},[button('Minta Perbaikan',()=>decision(issue,'return_corrective'),'qic-secondary'),button('Verifikasi dan Tutup Issue',()=>decision(issue,'close'))])));
        else content.append(card('Status Penyelesaian',text('Issue belum menunggu verifikasi akhir atau sudah selesai.')));
    }
    function tracking(){
        const select=field('Pilih issue','issue',issues()[0]?.id||'','select',{choices:issues().map(i=>i.id)}),holder=el('div');
        const paint=()=>{const issue=lookup(select.input.value);holder.replaceChildren();if(!issue){holder.append(empty());return;}holder.append(summary(issue),actionBar(issue),card('Timeline Penanganan',timeline(issue.audit)));};select.input.addEventListener('change',paint);content.append(select.wrapper,holder);paint();
    }
    function auditPage(){
        const logs=issues().flatMap(i=>i.audit.map(a=>({...a,issue:i.id}))).sort((a,b)=>b.at.localeCompare(a.at));
        const search=field('Cari ID / pelaku / tindakan','audit-query','','search'),holder=el('div');
        const paint=()=>{const q=search.input.value.toLowerCase();const rows=logs.filter(a=>`${a.issue} ${a.actor} ${a.action} ${a.note}`.toLowerCase().includes(q));holder.replaceChildren(rows.length?table(['Waktu','Issue','Pelaku','Tindakan','Catatan'],rows.map(a=>[time(a.at),anchor(a.issue,url('issues.show',a.issue)),a.actor,a.action,a.note||'—'])):empty());};search.input.addEventListener('input',paint);content.append(card('Riwayat Aktivitas',search.wrapper,holder));paint();
    }
    function notifications(){
        const targets=issues().filter(i=>role==='main-assy'?['need_information','closed','rejected'].includes(i.status):role==='auto-line'?['assigned','revision_requested','approved','corrective','three_c_rejected'].includes(i.status):['waiting_verification','verified','waiting_review','waiting_final'].includes(i.status));
        if(!targets.length){content.append(card('Notifikasi',empty('Tidak ada pekerjaan yang memerlukan perhatian saat ini.')));return;}
        content.append(button('Tandai semua sudah dibaca',()=>{const next=structuredClone(store.get());for(const i of targets){const key=`${role}:${i.id}:${i.updatedAt}`;if(!next.read.includes(key))next.read.push(key);}persist(next);render();toast('Notifikasi ditandai dibaca pada demo.');},'qic-secondary'));
        for(const i of targets){const key=`${role}:${i.id}:${i.updatedAt}`,read=store.get().read.includes(key);content.append(card(i.id+' · '+STATUS[i.status],text(read?'Sudah dibaca':'Belum dibaca','qic-small qic-muted'),text(i.description),anchor('Buka issue →',url('issues.show',i.id))));}
    }
    function profile(){
        const existing=store.get().profiles[role]||{name:boot.user.name,email:boot.user.email,department:ROLE[role],shift:'Normal'};
        content.append(card('Akun Laravel',text(boot.user.name),text(boot.user.email),anchor('Ubah akun / password sebenarnya',boot.accountUrl,'qic-button qic-secondary')));
        const f=formFields([['Nama tampilan demo','name',existing.name,'text',{required:true}],['Email demo','email',existing.email,'email',{required:true}],['Departemen','department',existing.department,'text',{required:true}],['Shift','shift',existing.shift,'select',{choices:['Normal','Shift 1','Shift 2']}]]);
        f.form.append(text('Form ini hanya menyimpan profil demonstrasi, tidak mengubah akun login atau role Laravel.','qic-small qic-muted'),el('button',{type:'submit',class:'qic-button',text:'Simpan Profil Demo'}));
        f.form.addEventListener('submit',e=>{e.preventDefault();const next=structuredClone(store.get());next.profiles[role]=f.values();persist(next);toast('Profil demo disimpan pada tab ini.');});content.append(card('Profil Workspace Demo',f.form));
    }
    function sla(){
        const select=field('Filter snapshot SLA','sla-filter','','select',{choices:[{value:'',label:'Semua snapshot'},{value:'pass',label:'Compliant'},{value:'fail',label:'Violation'},{value:'none',label:'Belum dihitung'}]}),holder=el('div');
        const paint=()=>{const rows=issues().filter(i=>!select.input.value||(select.input.value==='none'?!i.sla:select.input.value==='pass'?i.sla&&i.sla.elapsed<=i.sla.target:i.sla&&i.sla.elapsed>i.sla.target));holder.replaceChildren(rows.length?table(['Issue','Milestone contoh','Durasi contoh','Target','Status snapshot'],rows.map(i=>[anchor(i.id,url('issues.show',i.id)),i.sla?.stage||'—',i.sla?i.sla.elapsed+' jam kerja':'—',i.sla?i.sla.target+' jam kerja':'—',i.sla?el('span',{class:'qic-badge '+(i.sla.elapsed<=i.sla.target?'success':'danger'),text:i.sla.elapsed<=i.sla.target?'Compliant':'Violation'}):'Belum dihitung'])):empty());};select.input.addEventListener('change',paint);content.append(card('SLA Simulasi',text('Tabel memakai snapshot sintetis untuk demonstrasi visual. Durasi tidak dihitung dari tanggal, tidak berjalan real-time, dan tidak berubah ketika status issue berganti. Engine kalender kerja/shift belum terintegrasi.','qic-warning'),select.wrapper,holder));paint();
    }
    function ai(){
        content.append(card('AI Insight · Pratinjau Konsep',text('Tidak ada model AI atau API yang dipanggil. Catatan berikut merupakan panduan deterministik berdasarkan kelengkapan data demo. Sistem tidak menyetujui atau menutup issue secara otomatis.','qic-warning')));
        for(const i of issues().filter(i=>!TERMINAL.includes(i.status)).slice(0,8)){
            const notes=[];if(!i.photos?.defect||!i.photos?.label)notes.push('Evidence pelaporan belum lengkap.');if(!i.versions.length)notes.push('Belum ada 3C yang diajukan.');else if(!i.versions.at(-1).evidence.length)notes.push('Evidence respons 3C belum tersedia.');if(!notes.length)notes.push('Tinjau hubungan masalah, penyebab, countermeasure, dan bukti secara manual.');
            content.append(card(i.id+' · '+i.material,el('ul',{},notes.map(n=>el('li',{text:n}))),anchor('Tinjau issue',url('issues.show',i.id))));
        }
    }
    function reports(){
        const from=field('Tanggal dibuat mulai','from','','date'),to=field('Tanggal dibuat sampai','to','','date'),status=field('Status','report-status','','select',{choices:[{value:'',label:'Semua'},...Object.entries(STATUS).map(([value,label])=>({value,label}))]}),holder=el('div'),stats=el('div',{class:'qic-stats'}),err=el('p',{class:'qic-error',role:'alert'});let rows=[];
        const paint=()=>{
            err.textContent='';if(from.input.value&&to.input.value&&from.input.value>to.input.value){rows=[];holder.replaceChildren();stats.replaceChildren();err.textContent='Tanggal awal tidak boleh melewati tanggal akhir.';return;}
            rows=issues().filter(i=>{const date=i.createdAt.slice(0,10);return(!from.input.value||date>=from.input.value)&&(!to.input.value||date<=to.input.value)&&(!status.input.value||i.status===status.input.value);});
            const done=rows.filter(i=>i.status==='closed').length;stats.replaceChildren(...[['Jumlah Issue',rows.length],['Selesai',done],['Rasio Selesai',rows.length?Math.round(done/rows.length*100)+'%':'0%']].map(([label,n])=>el('div',{class:'qic-card qic-stat'},[text(label),el('strong',{text:n})])));
            const counts=Object.keys(STATUS).map(s=>[s,rows.filter(i=>i.status===s).length]).filter(([,n])=>n);
            holder.replaceChildren(card('Distribusi Status',counts.length?el('div',{class:'qic-bars'},counts.map(([s,n])=>el('div',{},[el('div',{class:'qic-bar-label'},[el('span',{text:STATUS[s]}),el('strong',{text:n})]),el('progress',{max:Math.max(1,rows.length),value:n,'aria-label':STATUS[s]})]))):empty()),card('Data Laporan',issueTable(rows)));
        };
        for(const f of [from,to,status])f.input.addEventListener('change',paint);
        content.append(card('Filter Laporan',el('div',{class:'qic-filters'},[from.wrapper,to.wrapper,status.wrapper]),err,button('Ekspor .xlsx (data terfilter)',()=>downloadWorkbook([['ID Issue','Material','Status','Pemilik','Prioritas','Jumlah','Dibuat','Data'],...rows.map(i=>[i.id,i.material,STATUS[i.status],i.owner||'',i.priority,i.quantity,i.createdAt,'SIMULASI'])]),'qic-secondary')),stats,holder);paint();
    }
    function master(){
        const edit=item=>{
            const f=formFields([['Kategori','category',item?.category||'Material','select',{choices:['Material','Defect','Process Owner']}],['Nama','name',item?.name||'','text',{required:true}]]);
            f.form.append(el('button',{type:'submit',class:'qic-button',text:'Simpan Data Demo'}));f.form.addEventListener('submit',e=>{
                e.preventDefault();const values=f.values();if(!values.name){modalError('Nama wajib diisi.');return;}
                const next=structuredClone(store.get());if(next.master.some(m=>m.id!==item?.id&&m.category===values.category&&m.name.toLowerCase()===values.name.toLowerCase())){modalError('Data dengan nama tersebut sudah ada.');return;}
                if(item){const index=next.master.findIndex(m=>m.id===item.id);next.master[index]={...item,...values};}else next.master.push({id:'M-'+Date.now(),...values,active:true});
                persist(next);closeModal();render();toast('Master data demo disimpan.');
            });openModal(item?'Edit Master Data':'Tambah Master Data',f.form);
        };
        content.append(card('Master Data Demonstrasi',text('Master Process Owner aktif menjadi pilihan assignment. Material dan Defect pada tabel ini merupakan contoh pengelolaan; belum terhubung ke field teks/form complaint. Menonaktifkan master tidak menghapus histori issue.','qic-small qic-muted'),button('+ Tambah Data',()=>edit(null)),table(['Kategori','Nama','Status','Tindakan'],store.get().master.map(m=>[m.category,m.name,m.active?'Aktif':'Nonaktif',el('div',{class:'qic-inline-actions'},[button('Edit',()=>edit(m),'qic-small-button qic-secondary'),button(m.active?'Nonaktifkan':'Aktifkan',()=>{const next=structuredClone(store.get());next.master.find(x=>x.id===m.id).active=!m.active;persist(next);render();},'qic-small-button qic-secondary')])]))));
    }
    function render(){
        if(!content)return;content.replaceChildren();document.getElementById('qic-page-actions').replaceChildren();warn();
        const issue=boot.issue?lookup(boot.issue):null;
        if(boot.issue&&!issue){content.append(card('Issue tidak tersedia',text('Issue tidak ditemukan atau belum menjadi penugasan workspace ini.'),anchor('Kembali ke daftar',url('issues.index'),'qic-button')));return;}
        switch(boot.screen){
            case 'dashboard':dashboard();break;
            case 'issues':listPage(issues());break;
            case 'create':complaintForm();break;
            case 'edit':complaintForm(issue);break;
            case 'detail':detail(issue);break;
            case 'tracking':tracking();break;
            case 'investigation':investigation(issue);break;
            case 'responses':{
                const rows=issues().filter(i=>i.versions.length||canAct(i,role,'submit_response'));
                content.append(card('Respons 3C',rows.length?table(['Issue','Versi dikirim','Status','Tindakan'],rows.map(i=>[anchor(i.id,url('issues.show',i.id)),i.versions.length,badge(i),el('div',{class:'qic-inline-actions'},[anchor('Detail',url('responses.show',i.id)),canAct(i,role,'submit_response')?anchor('Susun / Revisi',url(i.versions.length?'responses.revise':'responses.create',i.id)):null])])):empty()));break;
            }
            case 'response-form':responseForm(issue);break;
            case 'response-detail':content.append(summary(issue),actionBar(issue),versionPanel(issue));break;
            case 'corrective':corrective(issue);break;
            case 'history':case 'audit':auditPage();break;
            case 'verify':verify(issue);break;
            case 'assignments':assignments();break;
            case 'reviews':{
                const rows=issues().filter(i=>i.status==='waiting_review');content.append(card('Menunggu Keputusan Quality',rows.length?table(['Issue','Material','Versi','Tindakan'],rows.map(i=>[i.id,i.material,i.versions.length,anchor('Review 3C',url('reviews.show',i.id),'qic-button qic-secondary')])):empty('Tidak ada respons 3C yang menunggu tinjauan.')));break;
            }
            case 'review':review(issue);break;
            case 'final':finalVerification(issue);break;
            case 'sla':sla();break;
            case 'ai':ai();break;
            case 'reports':reports();break;
            case 'master':master();break;
            case 'notifications':notifications();break;
            case 'profile':profile();break;
            default:content.append(empty('Halaman belum dikenal.'));
        }
    }
    document.querySelector('[data-menu]')?.addEventListener('click',e=>{const open=document.getElementById('qic-sidebar').classList.toggle('is-open');e.currentTarget.setAttribute('aria-expanded',String(open));});
    document.querySelector('[data-reset]')?.addEventListener('click',()=>openModal('Reset Data Demonstrasi',el('div',{},[text('Semua complaint, revisi 3C, profil demo, dan perubahan master pada tab ini akan dikembalikan ke data contoh. Akun serta database Laravel tidak terpengaruh.'),el('div',{class:'qic-actions'},[button('Batal',closeModal,'qic-secondary'),button('Reset Demo',()=>{store.reset();closeModal();render();toast('Data demonstrasi dikembalikan ke kondisi awal.');})])])));
    window.addEventListener('pagehide',()=>{cleanup.forEach(f=>f());cleanup=[];});
    render();
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
document.addEventListener('livewire:navigated',init);
